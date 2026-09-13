<?php
namespace SIG\Models;

use SIG\Core\Database;

class Inventario
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Listar inventario con datos de productos
     */
    public function listar(int $page = 1, int $perPage = 25, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE p.activo = 1';
        $params = [];

        if (!empty($search)) {
            $where .= " AND (p.codigo LIKE :s1 OR p.descripcion LIKE :s2
                          OR p.codigo_barras_1 LIKE :s4 OR p.codigo_barras_2 LIKE :s5
                          OR p.codigo_barras_3 LIKE :s6)";
            $params['s1'] = "%{$search}%"; $params['s2'] = "%{$search}%";
            $params['s4'] = "%{$search}%"; $params['s5'] = "%{$search}%"; $params['s6'] = "%{$search}%";
        }

        $sql = "SELECT p.id_producto, p.codigo, p.descripcion, p.presentacion, p.fraccion,
                       p.valor_compra, p.valor_venta, p.valor_unidad, p.stock_minimo, p.unidad_cerrada,
                       p.precio_maximo_regulado,
                       inv.id_inventario, inv.unidad, inv.fraccion AS stock_fraccion,
                       (SELECT i.valor_compra FROM vb_ingresos i
                         WHERE i.id_producto = p.id_producto AND i.valor_compra IS NOT NULL AND i.valor_compra > 0
                         ORDER BY i.fecha ASC, i.id_ingreso ASC LIMIT 1) AS costo_primera_compra,
                       (SELECT i.valor_compra FROM vb_ingresos i
                         WHERE i.id_producto = p.id_producto AND i.valor_compra IS NOT NULL AND i.valor_compra > 0
                         ORDER BY i.fecha DESC, i.id_ingreso DESC LIMIT 1) AS costo_ultima_compra,
                       (inv.unidad - CASE WHEN inv.fraccion >= p.fraccion AND p.fraccion > 0 
                            THEN FLOOR(inv.fraccion / p.fraccion) ELSE 0 END) AS diferenciaU,
                       CASE WHEN p.fraccion > 0 THEN inv.fraccion % p.fraccion ELSE 0 END AS diferenciaF
                FROM vb_productos p
                LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
                {$where}
                ORDER BY p.descripcion ASC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $data = $this->db->select($sql, $params);

        $totalParams = $params;
        unset($totalParams['limit'], $totalParams['offset']);
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_productos p {$where}", $totalParams
        );

        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Ajustar inventario
     */
    public function ajustar(int $idProducto, int $unidad, int $fraccion, string $observacion, int $idUsuario): array
    {
        // Obtener inventario actual
        $inv = $this->db->fetchOne(
            "SELECT * FROM vb_inventario WHERE id_producto = :id", ['id' => $idProducto]
        );

        if (!$inv) {
            // Crear registro si no existe
            $this->db->insert(
                "INSERT INTO vb_inventario (id_producto, unidad, fraccion) VALUES (:id, :u, :f)",
                ['id' => $idProducto, 'u' => $unidad, 'f' => $fraccion]
            );
        } else {
            // Guardar valores anteriores para el movimiento
            $unAnt = (int)$inv['unidad'];
            $fracAnt = (int)$inv['fraccion'];

            $this->db->executeAffected(
                "UPDATE vb_inventario SET unidad = :u, fraccion = :f, ultimo_egreso = NOW() WHERE id_producto = :id",
                ['u' => $unidad, 'f' => $fraccion, 'id' => $idProducto]
            );

            // Registrar movimiento
            $difU = $unidad - $unAnt;
            $difF = $fraccion - $fracAnt;

            $this->db->insert(
                "INSERT INTO vb_movimientos_inventario 
                    (id_producto, tipo, unidad, fraccion, unidad_resultante, fraccion_resultante, observacion, id_usuario, created_at)
                 VALUES (:id, 'AJUSTE', :du, :df, :ur, :fr, :obs, :uid, NOW())",
                [
                    'id'  => $idProducto,
                    'du'  => $difU,
                    'df'  => $difF,
                    'ur'  => $unidad,
                    'fr'  => $fraccion,
                    'obs' => $observacion,
                    'uid' => $idUsuario,
                ]
            );
        }

        return ['success' => true];
    }

    /**
     * Obtener resumen del inventario (totales)
     */
    public function resumen(): array
    {
        $data = $this->db->fetchOne(
            "SELECT 
                COALESCE(SUM(inv.unidad), 0) AS total_unidad,
                COALESCE(SUM(inv.fraccion), 0) AS total_fraccion,
                COALESCE(SUM(p.valor_compra * inv.unidad), 0) AS capital_invertido,
                COALESCE(SUM(p.valor_venta * inv.unidad), 0) AS capital_total
             FROM vb_inventario inv
             JOIN vb_productos p ON inv.id_producto = p.id_producto
             WHERE p.activo = 1"
        );

        $data['utilidad'] = ($data['capital_total'] ?? 0) - ($data['capital_invertido'] ?? 0);
        return $data;
    }

    /**
     * Productos con stock bajo (con sugerencia de reorden)
     */
    public function stockBajo(): array
    {
        $data = $this->db->select(
            "SELECT p.*, inv.unidad, inv.fraccion AS stock_fraccion
             FROM vb_productos p
             JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.activo = 1 AND inv.unidad <= p.stock_minimo
             ORDER BY (inv.unidad - p.stock_minimo) ASC
             LIMIT 20"
        );

        // Agregar sugerencia de reorden a cada producto
        foreach ($data as &$item) {
            $stockMin = (int)($item['stock_minimo'] ?? 1);
            $unidad = (int)($item['unidad'] ?? 0);
            $undCerrada = (int)($item['unidad_cerrada'] ?? 1);
            $sugerido = max($stockMin * 3 - $unidad, $stockMin);
            $item['sugerencia_reorden'] = $sugerido;
            $item['sugerencia_cajas'] = $undCerrada > 0 ? ceil($sugerido / $undCerrada) : $sugerido;
        }
        unset($item);

        return $data;
    }

    /**
     * Sugerencias de reorden para todos los productos (para vista de inventario)
     */
    public function sugerenciasReorden(): array
    {
        return $this->db->select(
            "SELECT p.id_producto, p.codigo, p.descripcion, p.presentacion,
                    p.stock_minimo, p.unidad_cerrada,
                    inv.unidad, inv.fraccion AS stock_fraccion,
                    GREATEST(p.stock_minimo * 3 - COALESCE(inv.unidad, 0), p.stock_minimo) AS sugerido,
                    CEIL(GREATEST(p.stock_minimo * 3 - COALESCE(inv.unidad, 0), p.stock_minimo) / GREATEST(p.unidad_cerrada, 1)) AS cajas_sugeridas
             FROM vb_productos p
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.activo = 1 AND COALESCE(inv.unidad, 0) <= p.stock_minimo
             ORDER BY (COALESCE(inv.unidad, 0) - p.stock_minimo) ASC"
        );
    }

    /**
     * Historial de movimientos de un producto
     */
    public function movimientos(int $idProducto, int $limit = 20): array
    {
        return $this->db->select(
            "SELECT m.*, u.nombre_usuario
             FROM vb_movimientos_inventario m
             LEFT JOIN vb_usuarios u ON m.id_usuario = u.id_usuario
             WHERE m.id_producto = :id
             ORDER BY m.created_at DESC
             LIMIT :lim",
            ['id' => $idProducto, 'lim' => $limit]
        );
    }

    /**
     * Movimientos de inventario en un rango de fechas, con totales
     *
     * @param string $tipo TODOS | INGRESO | EGRESO | AJUSTE | DEVOLUCION
     */
    public function movimientosPorFecha(string $desde, string $hasta, string $tipo = 'TODOS', int $idProducto = 0): array
    {
        $where  = ['DATE(m.created_at) BETWEEN :desde AND :hasta'];
        $params = ['desde' => $desde, 'hasta' => $hasta];

        if (in_array($tipo, ['INGRESO', 'EGRESO', 'AJUSTE', 'DEVOLUCION'], true)) {
            $where[] = 'm.tipo = :tipo';
            $params['tipo'] = $tipo;
        }

        if ($idProducto > 0) {
            $where[] = 'm.id_producto = :idp';
            $params['idp'] = $idProducto;
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);

        $rows = $this->db->select(
            "SELECT m.*, p.codigo, p.descripcion, p.presentacion, p.unidad_cerrada,
                    p.valor_compra, p.valor_venta, u.nombre_usuario
             FROM vb_movimientos_inventario m
             JOIN vb_productos p ON m.id_producto = p.id_producto
             LEFT JOIN vb_usuarios u ON m.id_usuario = u.id_usuario
             {$sqlWhere}
             ORDER BY m.created_at DESC, m.id_movimiento DESC
             LIMIT 1000",
            $params
        );

        $totales = [
            'registros' => count($rows),
            'unidad'    => 0,
            'fraccion'  => 0,
            'valor'     => 0.0,
            'porTipo'   => [],
        ];

        foreach ($rows as $r) {
            $u = (int)$r['unidad'];
            $f = (int)$r['fraccion'];
            $undCerrada = max(1, (int)($r['unidad_cerrada'] ?? 1));
            $valorCompra = (float)($r['valor_compra'] ?? 0);

            $totales['unidad']   += $u;
            $totales['fraccion'] += $f;
            $totales['valor']    += $u * $valorCompra + $f * ($valorCompra / $undCerrada);

            $t = (string)$r['tipo'];
            if (!isset($totales['porTipo'][$t])) {
                $totales['porTipo'][$t] = ['registros' => 0, 'unidad' => 0, 'fraccion' => 0];
            }
            $totales['porTipo'][$t]['registros']++;
            $totales['porTipo'][$t]['unidad']   += $u;
            $totales['porTipo'][$t]['fraccion'] += $f;
        }

        $totales['valor'] = round($totales['valor'], 2);

        return [
            'data'    => $rows,
            'desde'   => $desde,
            'hasta'   => $hasta,
            'totales' => $totales,
        ];
    }
}
