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

            // Insertar devolución
            $idDev = $this->db->insert(
                "INSERT INTO vb_devoluciones (codigo, id_factura, id_cliente, motivo, total, id_usuario, fecha)
                 VALUES (:cod, :fac, :cli, :mot, :tot, :uid, CURDATE())",
                [
                    'cod' => $codigo,
                    'fac' => $idFactura,
                    'cli' => $factura['id_cliente'],
                    'mot' => $motivo,
                    'tot' => $total,
                    'uid' => $idUsuario,
                ]
            );

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

        return $dev;
    }
}
