<?php
namespace SIG\Models;

use SIG\Core\Database;

class Ingreso
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Listar ingresos
     */
    public function listar(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $data = $this->db->select(
            "SELECT i.*, p.codigo, p.descripcion, p.presentacion, u.nombre_usuario
             FROM vb_ingresos i
             JOIN vb_productos p ON i.id_producto = p.id_producto
             LEFT JOIN vb_usuarios u ON i.id_usuario = u.id_usuario
             ORDER BY i.fecha DESC, i.created_at DESC
             LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );

        $total = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_ingresos");

        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Crear un ingreso y actualizar inventario automáticamente (usando triggers)
     */
    public function crear(array $data, int $idUsuario): int
    {
        $id = $this->db->insert(
            "INSERT INTO vb_ingresos (id_producto, cantidad_unidad, cantidad_fraccion, tipo, observacion, id_usuario, fecha)
             VALUES (:prod, :und, :frac, :tipo, :obs, :uid, :fecha)",
            [
                'prod'  => $data['id_producto'],
                'und'   => (int)($data['cantidad_unidad'] ?? 0),
                'frac'  => (int)($data['cantidad_fraccion'] ?? 0),
                'tipo'  => $data['tipo'] ?? 'MANUAL',
                'obs'   => $data['observacion'] ?? '',
                'uid'   => $idUsuario,
                'fecha' => $data['fecha'] ?? date('Y-m-d'),
            ]
        );

        // Asegurar que existe registro en inventario
        $inv = $this->db->fetchOne(
            "SELECT id_inventario FROM vb_inventario WHERE id_producto = :id",
            ['id' => $data['id_producto']]
        );

        if (!$inv) {
            $this->db->insert(
                "INSERT INTO vb_inventario (id_producto, unidad, fraccion) VALUES (:id, 0, 0)",
                ['id' => $data['id_producto']]
            );
        }

        return $id;
    }

    /**
     * Buscar productos para ingreso
     */
    public function buscarProductos(string $q): array
    {
        return $this->db->select(
            "SELECT p.*, inv.unidad, inv.fraccion AS stock_fraccion
             FROM vb_productos p
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.activo = 1 AND (p.codigo LIKE :q1 OR p.descripcion LIKE :q2)
             ORDER BY p.descripcion ASC LIMIT 20",
            ['q1' => "%{$q}%", 'q2' => "%{$q}%"]
        );
    }

    /**
     * Listar facturas de compra (ingreso por factura)
     */
    public function listarFacturasCompra(): array
    {
        return $this->db->select(
            "SELECT f.*, prov.nombre AS proveedor_nombre
             FROM vb_ingresos_factura f
             LEFT JOIN vb_proveedores prov ON f.id_proveedor = prov.id_proveedor
             ORDER BY f.fecha DESC LIMIT 20"
        );
    }
}
