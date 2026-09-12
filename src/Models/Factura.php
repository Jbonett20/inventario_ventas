<?php
namespace SIG\Models;

use SIG\Core\Database;
use SIG\Core\Session;

class Factura
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Obtener el siguiente código de factura según el rango
     */
    public function siguienteCodigo(string $tipo = 'NORMAL'): int
    {
        $rango = $this->db->fetchOne(
            "SELECT inicio, fin FROM vb_rangos_facturacion WHERE tipo = :tipo AND activo = 1 LIMIT 1",
            ['tipo' => $tipo]
        );

        if (!$rango) throw new \RuntimeException("No hay rango activo para facturas {$tipo}");

        $ultimo = $this->db->fetchOne(
            "SELECT MAX(codigo) AS ultimo FROM vb_facturas WHERE tipo = :tipo AND codigo BETWEEN :ini AND :fin",
            ['tipo' => $tipo, 'ini' => $rango['inicio'], 'fin' => $rango['fin']]
        );

        // OJO: $ultimo es un array, por lo que siempre es "verdadero".
        // Hay que comprobar el valor, no el array, para estrenar el rango.
        $ultimoCodigo = $ultimo['ultimo'] ?? null;
        $siguiente = ($ultimoCodigo !== null)
            ? (int)$ultimoCodigo + 1
            : (int)$rango['inicio'];

        if ($siguiente > (int)$rango['fin']) {
            throw new \RuntimeException("Rango de facturación {$tipo} agotado");
        }

        return $siguiente;
    }

    /**
     * Crear factura (cabecera + detalle) en transacción
     */
    public function crear(array $data, array $detalles, int $idVendedor): array
    {
        $this->db->beginTransaction();

        try {
            $tipo = $data['tipo'] ?? 'NORMAL';
            $codigo = $this->siguienteCodigo($tipo);

            // Calcular totales
            $subtotal = 0;
            $totalIva = 0;
            $totalGanancia = 0;

            foreach ($detalles as &$det) {
                $producto = $this->db->fetchOne(
                    "SELECT valor_compra, rentabilidad, tipo_venta, cantidad_por_unidad FROM vb_productos WHERE id_producto = :id",
                    ['id' => $det['id_producto']]
                );

                $tipoVenta = $producto['tipo_venta'] ?? 'UNIDAD';
                $det['tipo_venta'] = $tipoVenta;

                if ($tipoVenta === 'FRACCION_DECIMAL') {
                    // Venta por fracción decimal: precio * cantidad decimal
                    $cantDec = (float)($det['cantidad_decimal'] ?? 0);
                    $det['subtotal'] = $det['precio_unitario'] * $cantDec;
                    $det['ganancia'] = $producto ? ($det['precio_unitario'] - $producto['valor_compra']) * $cantDec : 0;
                } else {
                    $det['subtotal'] = $det['precio_unitario'] * ($det['cantidad_unidad'] + ($det['cantidad_fraccion'] / max($det['fraccion'] ?? 1, 1)));
                    $totalVendido = $det['cantidad_unidad'] + (($det['fraccion'] ?? 0) > 0 ? 0 : ($det['cantidad_fraccion'] ?? 0));
                    $det['ganancia'] = $producto ? ($det['precio_unitario'] - $producto['valor_compra']) * $totalVendido : 0;
                }
                $det['iva_valor'] = $det['subtotal'] * ($det['iva'] / 100);
                $det['total'] = $det['subtotal'] + $det['iva_valor'];

                $subtotal += $det['subtotal'];
                $totalIva += $det['iva_valor'];
                $totalGanancia += $det['ganancia'];
            }
            unset($det);

            $total = $subtotal + $totalIva;
            $descuento = (float)($data['descuento'] ?? 0);
            $totalPagar = $total - $descuento;
            $pagoRecibido = (float)($data['pago_recibido'] ?? $totalPagar);
            $cambio = max(0, $pagoRecibido - $totalPagar);

            // Insertar factura
            $idFactura = $this->db->insert(
                "INSERT INTO vb_facturas 
                    (uuid, codigo, id_empresa, id_cliente, id_vendedor, id_vendedor_registrado,
                     fecha, hora, tipo, tipo_pago,
                     subtotal, total_iva, total, pago_recibido, cambio, descuento, ganancia, estado)
                 VALUES 
                    (:uuid, :cod, 1, :cli, :vend, :vendreg,
                     :fecha, :hora, :tipo, :tpago,
                     :sub, :iva, :total, :pago, :cambio, :desc, :gan, 'ACTIVA')",
                [
                    'uuid'  => \SIG\Helpers\Security::generateUUID(),
                    'cod'   => $codigo,
                    'cli'   => $data['id_cliente'] ?? 1,
                    'vend'  => $idVendedor,
                    'vendreg' => !empty($data['id_vendedor_registrado']) ? (int)$data['id_vendedor_registrado'] : null,
                    'fecha' => date('Y-m-d'),
                    'hora'  => date('H:i:s'),
                    'tipo'  => $tipo,
                    'tpago' => $data['tipo_pago'] ?? 'EFECTIVO',
                    'sub'   => $subtotal,
                    'iva'   => $totalIva,
                    'total' => $totalPagar,
                    'pago'  => $pagoRecibido,
                    'cambio'=> $cambio,
                    'desc'  => $descuento,
                    'gan'   => $totalGanancia,
                ]
            );

            // Insertar detalle
            foreach ($detalles as $det) {
                $this->db->insert(
                    "INSERT INTO vb_detalle_facturas
                        (id_factura, id_producto, descripcion, cantidad_unidad, cantidad_fraccion,
                         precio_unitario, iva, iva_valor, subtotal, total, cantidad_decimal)
                     VALUES
                        (:fac, :prod, :desc, :cu, :cf, :pu, :iva, :ivav, :sub, :tot, :cd)",
                    [
                        'fac'  => $idFactura,
                        'prod' => $det['id_producto'],
                        'desc' => $det['descripcion'] ?? '',
                        'cu'   => $det['cantidad_unidad'],
                        'cf'   => $det['cantidad_fraccion'] ?? 0,
                        'pu'   => $det['precio_unitario'],
                        'iva'  => $det['iva'],
                        'ivav' => $det['iva_valor'],
                        'sub'  => $det['subtotal'],
                        'tot'  => $det['total'],
                        'cd'   => (float)($det['cantidad_decimal'] ?? 0),
                    ]
                );

                // Descontar del inventario
                $inv = $this->db->fetchOne(
                    "SELECT * FROM vb_inventario WHERE id_producto = :id",
                    ['id' => $det['id_producto']]
                );

                if ($inv) {
                    // Obtener configuración de fracción del producto
                    $prodInv = $this->db->fetchOne(
                        "SELECT fraccion, tipo_venta, cantidad_por_unidad FROM vb_productos WHERE id_producto = :id",
                        ['id' => $det['id_producto']]
                    );
                    $tipoVentaInv = $prodInv['tipo_venta'] ?? 'UNIDAD';
                    $fracPorUnd = (int)($prodInv['fraccion'] ?? 0);
                    $cantUnd = (int)$det['cantidad_unidad'];
                    $cantFrac = (int)($det['cantidad_fraccion'] ?? 0);
                    $cantDec = (float)($det['cantidad_decimal'] ?? 0);

                    if ($tipoVentaInv === 'FRACCION_DECIMAL') {
                        // Producto con venta por fracción decimal
                        $nuevaFrac = (float)$inv['fraccion'] - $cantDec;
                        $nuevaUnd = (int)$inv['unidad'];

                        // Si fracción queda negativa, convertir desde unidades
                        if ($nuevaFrac < 0) {
                            $unidadesNecesarias = ceil(abs($nuevaFrac));
                            $nuevaUnd = max(0, $nuevaUnd - $unidadesNecesarias);
                            $nuevaFrac = max(0, $nuevaFrac + $unidadesNecesarias);
                        }

                        $nuevaUnd = max(0, $nuevaUnd);
                        $nuevaFrac = max(0, $nuevaFrac);
                        $movUnd = 0;
                        $movFrac = -$cantDec;
                    } elseif ($fracPorUnd === 0) {
                        // Producto NO fraccionable: sumar todo como unidades completas
                        $totalUnd = $cantUnd + $cantFrac;
                        $nuevaUnd = max(0, (int)$inv['unidad'] - $totalUnd);
                        $nuevaFrac = 0;
                        $movUnd = -$totalUnd;
                        $movFrac = 0;
                    } else {
                        // Producto fraccionable (unidades sueltas enteras): descontar normalmente
                        $nuevaUnd = (int)$inv['unidad'] - $cantUnd;
                        $nuevaFrac = (int)$inv['fraccion'] - $cantFrac;

                        // Si fracción queda negativa, convertir una unidad en fracciones
                        if ($nuevaFrac < 0 && $fracPorUnd > 0) {
                            $unidadesADescontar = ceil(abs($nuevaFrac) / $fracPorUnd);
                            $nuevaUnd -= $unidadesADescontar;
                            $nuevaFrac = $fracPorUnd - (abs($nuevaFrac) % $fracPorUnd);
                            if ($nuevaFrac >= $fracPorUnd) $nuevaFrac = 0;
                        }

                        $nuevaUnd = max(0, $nuevaUnd);
                        $nuevaFrac = max(0, $nuevaFrac);
                        $movUnd = -$cantUnd;
                        $movFrac = -$cantFrac;
                    }

                    $this->db->executeAffected(
                        "UPDATE vb_inventario SET unidad = :u, fraccion = :f, ultimo_egreso = NOW() WHERE id_producto = :id",
                        ['u' => $nuevaUnd, 'f' => $nuevaFrac, 'id' => $det['id_producto']]
                    );

                    // Registrar movimiento de egreso
                    $this->db->insert(
                        "INSERT INTO vb_movimientos_inventario
                            (id_producto, tipo, unidad, fraccion, unidad_resultante, fraccion_resultante, id_usuario, created_at)
                         VALUES (:id, 'EGRESO', :du, :df, :ur, :fr, :uid, NOW())",
                        [
                            'id'  => $det['id_producto'],
                            'du'  => $movUnd,
                            'df'  => $movFrac,
                            'ur'  => $nuevaUnd,
                            'fr'  => $nuevaFrac,
                            'uid' => $idVendedor,
                        ]
                    );
                }
            }

            $this->db->commit();

            return [
                'success'   => true,
                'id_factura'=> $idFactura,
                'codigo'    => $codigo,
                'total'     => $totalPagar,
                'cambio'    => $cambio,
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Obtener factura con detalles (incluye cantidades restantes para devolución)
     */
    public function obtenerConDetalle(int $id): ?array
    {
        $factura = $this->db->fetchOne(
            "SELECT f.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento,
                    c.direccion AS cliente_direccion, u.nombre_usuario AS vendedor_nombre,
                    e.nombre AS empresa_nombre, e.nit AS empresa_nit
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             LEFT JOIN vb_usuarios u ON f.id_vendedor = u.id_usuario
             LEFT JOIN vb_empresa e ON f.id_empresa = e.id_empresa
             WHERE f.id_factura = :id",
            ['id' => $id]
        );

        if (!$factura) return null;

        $detalles = $this->db->select(
            "SELECT d.*, p.codigo, p.presentacion, p.fraccion, p.valor_unidad,
                    GREATEST(0, d.cantidad_unidad - IFNULL(
                        (SELECT SUM(dd.cantidad_unidad) FROM vb_devoluciones_detalle dd
                         JOIN vb_devoluciones dev ON dd.id_devolucion = dev.id_devolucion
                         WHERE dev.id_factura = d.id_factura AND dd.id_producto = d.id_producto), 0
                    )) AS restante_unidad,
                    GREATEST(0, d.cantidad_fraccion - IFNULL(
                        (SELECT SUM(dd.cantidad_fraccion) FROM vb_devoluciones_detalle dd
                         JOIN vb_devoluciones dev ON dd.id_devolucion = dev.id_devolucion
                         WHERE dev.id_factura = d.id_factura AND dd.id_producto = d.id_producto), 0
                    )) AS restante_fraccion
             FROM vb_detalle_facturas d
             LEFT JOIN vb_productos p ON d.id_producto = p.id_producto
             WHERE d.id_factura = :id
             ORDER BY d.id_detalle ASC",
            ['id' => $id]
        );

        $factura['detalles'] = $detalles;
        return $factura;
    }

    /**
     * Listar facturas con filtros avanzados
     */
    public function listar(array $filtros = []): array
    {
        $page    = (int)($filtros['page'] ?? 1);
        $perPage = (int)($filtros['perPage'] ?? 25);
        $offset  = ($page - 1) * $perPage;

        $where  = 'WHERE 1=1';
        $params = [];

        // Búsqueda general
        if (!empty($filtros['search'])) {
            $where .= " AND (f.codigo LIKE :s1 OR c.nombre LIKE :s2 OR c.documento LIKE :s3)";
            $params['s1'] = "%{$filtros['search']}%";
            $params['s2'] = "%{$filtros['search']}%";
            $params['s3'] = "%{$filtros['search']}%";
        }

        // Por tipo (NORMAL / ELECTRONICA)
        if (!empty($filtros['tipo'])) {
            $where .= " AND f.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }

        // Por método de pago
        if (!empty($filtros['tipo_pago'])) {
            $where .= " AND f.tipo_pago = :tp";
            $params['tp'] = $filtros['tipo_pago'];
        }

        // Por estado (ACTIVA / ANULADA)
        if (!empty($filtros['estado'])) {
            $where .= " AND f.estado = :est";
            $params['est'] = $filtros['estado'];
        }

        // Por rango de fechas
        if (!empty($filtros['fecha_ini'])) {
            $where .= " AND f.fecha >= :fini";
            $params['fini'] = $filtros['fecha_ini'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $where .= " AND f.fecha <= :ffin";
            $params['ffin'] = $filtros['fecha_fin'];
        }

        // Por código exacto
        if (!empty($filtros['codigo'])) {
            $where .= " AND f.codigo = :cod";
            $params['cod'] = (int)$filtros['codigo'];
        }

        // Por cliente
        if (!empty($filtros['id_cliente'])) {
            $where .= " AND f.id_cliente = :cli";
            $params['cli'] = (int)$filtros['id_cliente'];
        }

        $data = $this->db->select(
            "SELECT f.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento,
                    COALESCE(v.nombre, NULLIF(u.nombre_completo, ''), u.nombre_usuario) AS vendedor_nombre,
                    v.codigo AS vendedor_codigo,
                    (v.id_vendedor IS NOT NULL) AS vendedor_es_registrado,
                    (SELECT COUNT(*) FROM vb_devoluciones dv WHERE dv.id_factura = f.id_factura) AS tiene_devoluciones
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             LEFT JOIN vb_vendedores v ON f.id_vendedor_registrado = v.id_vendedor
             LEFT JOIN vb_usuarios u ON f.id_vendedor = u.id_usuario
             {$where}
             ORDER BY f.id_factura DESC
             LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_facturas f {$where}", $params
        );

        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Anular una factura (devuelve productos al inventario)
     * Para facturas ELECTRONICA se requiere generar Nota Crédito ante la DIAN.
     */
    public function anular(int $id): void
    {
        $factura = $this->db->fetchOne(
            "SELECT * FROM vb_facturas WHERE id_factura = :id AND estado = 'ACTIVA'",
            ['id' => $id]
        );
        if (!$factura) throw new \RuntimeException('Factura no encontrada o ya anulada');

        // Validar factura electrónica
        if ($factura['tipo'] === 'ELECTRONICA') {
            throw new \RuntimeException(
                'Las facturas electrónicas no se pueden anular directamente. ' .
                'Debe generar una Nota Crédito desde el módulo de facturación electrónica.'
            );
        }

        $detalles = $this->db->select(
            "SELECT * FROM vb_detalle_facturas WHERE id_factura = :id", ['id' => $id]
        );

        $this->db->beginTransaction();
        try {
            foreach ($detalles as $det) {
                $cantDec = (float)($det['cantidad_decimal'] ?? 0);
                if ($cantDec > 0) {
                    // Producto con fracción decimal: devolver al stock fraccionado
                    $this->db->executeAffected(
                        "UPDATE vb_inventario SET fraccion = fraccion + :f WHERE id_producto = :id",
                        ['f' => $cantDec, 'id' => $det['id_producto']]
                    );
                } else {
                    $this->db->executeAffected(
                        "UPDATE vb_inventario SET unidad = unidad + :u, fraccion = fraccion + :f WHERE id_producto = :id",
                        ['u' => $det['cantidad_unidad'], 'f' => $det['cantidad_fraccion'], 'id' => $det['id_producto']]
                    );
                }
            }

            $this->db->executeAffected(
                "UPDATE vb_facturas SET estado = 'ANULADA' WHERE id_factura = :id", ['id' => $id]
            );

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
