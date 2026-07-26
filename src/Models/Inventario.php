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
            $where .= " AND (p.codigo LIKE :s1 OR p.descripcion LIKE :s2 OR p.codigo_barras_1 LIKE :s3)";
            $params['s1'] = "%{$search}%"; $params['s2'] = "%{$search}%"; $params['s3'] = "%{$search}%";
        }

        $sql = "SELECT p.id_producto, p.codigo, p.descripcion, p.presentacion, p.fraccion,
                       p.valor_compra, p.valor_venta, p.valor_unidad, p.stock_minimo, p.unidad_cerrada,
                       inv.id_inventario, inv.unidad, inv.fraccion AS stock_fraccion,
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
}
