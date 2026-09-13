<?php
namespace SIG\Models;

use SIG\Core\Database;
use RuntimeException;

class Devolucion
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Procesar una devolución con reversión de inventario
     * Soporta devoluciones parciales múltiples
     */
    public function crear(array $data, int $idUsuario): int
    {
        $idFactura = (int)($data['id_factura'] ?? 0);
        $motivo = $data['motivo'] ?? '';
        $detalles = $data['detalles'] ?? [];

        if (empty($motivo)) throw new RuntimeException('El motivo es obligatorio');
        if (empty($detalles)) throw new RuntimeException('Debe seleccionar al menos un producto');

        // Obtener factura
        $factura = $this->db->fetchOne(
            "SELECT * FROM vb_facturas WHERE id_factura = :id AND estado IN ('ACTIVA','ACTIVO')",
            ['id' => $idFactura]
        );
        if (!$factura) throw new RuntimeException('Factura no encontrada o no está activa');

        // No permitir devoluciones en facturas electrónicas (requieren Nota Crédito DIAN)
        if ($factura['tipo'] === 'ELECTRONICA') {
            throw new RuntimeException(
                'Las facturas electrónicas no admiten devoluciones por este medio. ' .
                'Debe generar una Nota Crédito desde el módulo de facturación electrónica.'
            );
        }

        $this->db->beginTransaction();
        try {
            // Generar código de devolución
            $codigo = 'DEV-' . $factura['codigo'] . '-' . date('YmdHis');

            // Calcular total
            $total = 0;
            foreach ($detalles as &$det) {
                $det['subtotal'] = ($det['cantidad_unidad'] ?? 0) * ($det['valor_unitario'] ?? 0);
                $total += $det['subtotal'];
            }
            unset($det);

            // Cómo se le devuelve la plata al cliente (puede ser solo una forma
            // o varias: una parte en efectivo y otra por Nequi, por ejemplo)
            $infoPagos = $this->procesarPagosDevolucion($data, (float)$total);

            // Insertar devolución
            $idDev = $this->db->insert(
                "INSERT INTO vb_devoluciones (codigo, id_factura, id_cliente, motivo, total, tipo_pago, id_usuario, fecha)
                 VALUES (:cod, :fac, :cli, :mot, :tot, :tpago, :uid, CURDATE())",
                [
                    'cod'   => $codigo,
                    'fac'   => $idFactura,
                    'cli'   => $factura['id_cliente'],
                    'mot'   => $motivo,
                    'tot'   => $total,
                    'tpago' => $infoPagos['resumen'],
                    'uid'   => $idUsuario,
                ]
            );

            // Con qué se le devolvió la plata al cliente
            foreach ($infoPagos['pagos'] as $pg) {
                $this->db->insert(
                    "INSERT INTO vb_devoluciones_pagos (id_devolucion, metodo, monto, referencia, id_usuario, created_at)
                     VALUES (:dev, :metodo, :monto, :ref, :uid, NOW())",
                    [
                        'dev'    => $idDev,
                        'metodo' => $pg['metodo'],
                        'monto'  => $pg['monto'],
                        'ref'    => $pg['referencia'],
                        'uid'    => $idUsuario,
                    ]
                );
            }

            // Insertar detalle y revertir inventario
            foreach ($detalles as $det) {
                $cantU = (int)($det['cantidad_unidad'] ?? 0);
                $cantF = (int)($det['cantidad_fraccion'] ?? 0);
                if ($cantU <= 0 && $cantF <= 0) continue;

                $this->db->insert(
                    "INSERT INTO vb_devoluciones_detalle (id_devolucion, id_producto, cantidad_unidad, cantidad_fraccion, valor_unitario, subtotal)
                     VALUES (:dev, :prod, :cu, :cf, :vu, :sub)",
                    [
                        'dev'  => $idDev,
                        'prod' => $det['id_producto'],
                        'cu'   => $cantU,
                        'cf'   => $cantF,
                        'vu'   => $det['valor_unitario'] ?? 0,
                        'sub'  => $det['subtotal'] ?? 0,
                    ]
                );

                // Revertir inventario
                $this->db->executeAffected(
                    "UPDATE vb_inventario SET unidad = unidad + :u, fraccion = fraccion + :f WHERE id_producto = :id",
                    ['u' => $cantU, 'f' => $cantF, 'id' => $det['id_producto']]
                );

                // Registrar movimiento
                $this->db->insert(
                    "INSERT INTO vb_movimientos_inventario (id_producto, tipo, unidad, fraccion, observacion, id_usuario, created_at)
                     VALUES (:id, 'DEVOLUCION', :u, :f, :obs, :uid, NOW())",
                    [
                        'id'  => $det['id_producto'],
                        'u'   => $cantU,
                        'f'   => $cantF,
                        'obs' => "Devolución #{$codigo} - {$motivo}",
                        'uid' => $idUsuario,
                    ]
                );
            }

            // Verificar si TODOS los productos de la factura han sido devueltos en su totalidad
            $productosFactura = $this->db->select(
                "SELECT df.id_producto, df.cantidad_unidad AS fact_u, df.cantidad_fraccion AS fact_f,
                        COALESCE(SUM(dd.cantidad_unidad), 0) AS dev_u,
                        COALESCE(SUM(dd.cantidad_fraccion), 0) AS dev_f
                 FROM vb_detalle_facturas df
                 LEFT JOIN vb_devoluciones_detalle dd ON df.id_producto = dd.id_producto
                    AND dd.id_devolucion IN (SELECT id_devolucion FROM vb_devoluciones WHERE id_factura = :fac)
                 WHERE df.id_factura = :fac2
                 GROUP BY df.id_producto, df.cantidad_unidad, df.cantidad_fraccion",
                ['fac' => $idFactura, 'fac2' => $idFactura]
            );

            $todosCompletos = true;
            foreach ($productosFactura as $pf) {
                $restaU = (int)$pf['fact_u'] - (int)$pf['dev_u'];
                $restaF = (int)$pf['fact_f'] - (int)$pf['dev_f'];
                if ($restaU > 0 || $restaF > 0) {
                    $todosCompletos = false;
                    break;
                }
            }

            if ($todosCompletos) {
                $this->db->executeAffected(
                    "UPDATE vb_facturas SET estado = 'ANULADA' WHERE id_factura = :id",
                    ['id' => $idFactura]
                );
            }

            $this->db->commit();
            return $idDev;

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Normaliza y valida con qué se le devuelve la plata al cliente.
     *
     * Acepta varias formas en la misma devolución:
     *   $data['pagos'] = [ ['metodo' => 'EFECTIVO', 'monto' => 10000], ... ]
     *
     * Si no llega el arreglo, se asume una sola forma ($data['tipo_pago']).
     * Aquí NO hay cambio: la suma debe dar exactamente el total devuelto,
     * porque lo que sale de caja es justo lo que se le entrega al cliente.
     *
     * @return array ['pagos' => array, 'resumen' => string]
     */
    private function procesarPagosDevolucion(array $data, float $total): array
    {
        $pagos  = [];
        $brutos = $data['pagos'] ?? null;

        if (is_array($brutos) && count($brutos)) {
            foreach ($brutos as $p) {
                if (!is_array($p)) continue;

                $metodo = strtoupper(trim((string)($p['metodo'] ?? '')));
                $monto  = round((float)($p['monto'] ?? 0), 2);

                if ($metodo === '' || $monto <= 0) continue;

                $ref = isset($p['referencia']) ? trim((string)$p['referencia']) : '';

                $pagos[] = [
                    'metodo'     => mb_substr($metodo, 0, 50),
                    'monto'      => $monto,
                    'referencia' => $ref !== '' ? mb_substr($ref, 0, 100) : null,
                ];
            }
        }

        // Compatibilidad: sin arreglo, una sola forma de devolución
        if (!$pagos) {
            $metodo = strtoupper(trim((string)($data['tipo_pago'] ?? ''))) ?: 'EFECTIVO';
            $pagos[] = [
                'metodo'     => mb_substr($metodo, 0, 50),
                'monto'      => round($total, 2),
                'referencia' => null,
            ];
        }

        $suma = round(array_sum(array_column($pagos, 'monto')), 2);

        if (abs($suma - round($total, 2)) > 0.01) {
            throw new RuntimeException(
                'Lo que se devuelve ($' . number_format($suma, 0, ',', '.') . ') no coincide con el total de la devolución ($' .
                number_format($total, 0, ',', '.') . '). Ajuste los montos.'
            );
        }

        $metodos = array_values(array_unique(array_column($pagos, 'metodo')));

        return [
            'pagos'   => $pagos,
            'resumen' => count($metodos) > 1 ? 'MIXTO' : $metodos[0],
        ];
    }

    /**
     * Listar devoluciones con paginación
     */
    public function listar(int $page = 1, int $perPage = 25, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $where .= " AND (d.codigo LIKE :s1 OR f.codigo LIKE :s2 OR c.nombre LIKE :s3)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
            $params['s3'] = "%{$search}%";
        }

        $data = $this->db->select(
            "SELECT d.*, f.codigo AS factura_codigo, c.nombre AS cliente_nombre, u.nombre_usuario
             FROM vb_devoluciones d
             LEFT JOIN vb_facturas f ON d.id_factura = f.id_factura
             LEFT JOIN vb_clientes c ON d.id_cliente = c.id_cliente
             LEFT JOIN vb_usuarios u ON d.id_usuario = u.id_usuario
             {$where}
             ORDER BY d.fecha DESC, d.created_at DESC
             LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_devoluciones d
             LEFT JOIN vb_facturas f ON d.id_factura = f.id_factura
             LEFT JOIN vb_clientes c ON d.id_cliente = c.id_cliente
             {$where}", $params
        );

        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => max(1, ceil(($total['total'] ?? 0) / $perPage)),
        ];
    }

    /**
     * Obtener detalles de una devolución
     */
    public function obtenerConDetalle(int $id): ?array
    {
        $dev = $this->db->fetchOne(
            "SELECT d.*, f.codigo AS factura_codigo, c.nombre AS cliente_nombre, u.nombre_usuario
             FROM vb_devoluciones d
             LEFT JOIN vb_facturas f ON d.id_factura = f.id_factura
             LEFT JOIN vb_clientes c ON d.id_cliente = c.id_cliente
             LEFT JOIN vb_usuarios u ON d.id_usuario = u.id_usuario
             WHERE d.id_devolucion = :id",
            ['id' => $id]
        );

        if (!$dev) return null;

        $dev['detalles'] = $this->db->select(
            "SELECT dd.*, p.codigo, p.descripcion
             FROM vb_devoluciones_detalle dd
             LEFT JOIN vb_productos p ON dd.id_producto = p.id_producto
             WHERE dd.id_devolucion = :id",
            ['id' => $id]
        );

        // Con qué se le devolvió la plata al cliente
        $dev['pagos'] = $this->db->select(
            "SELECT id_pago_devolucion, metodo, monto, referencia, created_at
             FROM vb_devoluciones_pagos
             WHERE id_devolucion = :id
             ORDER BY id_pago_devolucion ASC",
            ['id' => $id]
        );

        return $dev;
    }

    /**
     * Resumen de devoluciones por método en un rango de fechas.
     * Sirve para el cuadre de caja y para el cierre de inventario.
     */
    public function resumenPorMetodo(string $desde, string $hasta): array
    {
        return $this->db->select(
            "SELECT pg.metodo,
                    COUNT(DISTINCT pg.id_devolucion) AS devoluciones,
                    COALESCE(SUM(pg.monto), 0) AS total
             FROM vb_devoluciones_pagos pg
             JOIN vb_devoluciones d ON d.id_devolucion = pg.id_devolucion
             WHERE d.fecha BETWEEN :desde AND :hasta
             GROUP BY pg.metodo
             ORDER BY total DESC",
            ['desde' => $desde, 'hasta' => $hasta]
        );
    }
}
