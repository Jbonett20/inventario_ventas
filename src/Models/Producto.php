<?php
namespace SIG\Models;

use SIG\Core\Database;

/**
 * Modelo de Productos
 */
class Producto
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Listar todos los productos (con paginación)
     */
    public function listar(int $page = 1, int $perPage = 25, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;

        $where = 'WHERE p.activo = 1';
        $params = [];

        if (!empty($search)) {
            $where .= " AND (p.codigo LIKE :search 
                          OR p.descripcion LIKE :search2 
                          OR p.codigo_barras_1 LIKE :search3
                          OR p.codigo_barras_2 LIKE :search4)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
        }

        $sql = "SELECT p.*, prov.nombre AS proveedor_nombre, 
                       cat.nombre AS categoria_nombre, i.iva AS iva_valor
                FROM vb_productos p
                LEFT JOIN vb_proveedores prov ON p.id_proveedor = prov.id_proveedor
                LEFT JOIN vb_categorias cat ON p.id_categoria = cat.id_categoria
                LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
                {$where}
                ORDER BY p.descripcion ASC
                LIMIT :limit OFFSET :offset";

        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $productos = $this->db->select($sql, $params);

        // Total de registros
        $countSql = "SELECT COUNT(*) as total FROM vb_productos p {$where}";
        $totalParams = $params;
        unset($totalParams['limit'], $totalParams['offset']);
        $total = $this->db->fetchOne($countSql, $totalParams);

        return [
            'data'       => $productos,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Buscar productos para autocomplete (facturación)
     */
    public function buscar(string $query): array
    {
        $sql = "SELECT p.*, i.iva AS iva_valor,
                       inv.unidad, inv.fraccion AS stock_fraccion
                FROM vb_productos p
                LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
                LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
                WHERE p.activo = 1
                  AND (p.codigo LIKE :q1 OR p.descripcion LIKE :q2 
                       OR p.codigo_barras_1 LIKE :q3 OR p.codigo_barras_2 LIKE :q4)
                ORDER BY p.descripcion ASC
                LIMIT 20";

        $params = [
            'q1' => "%{$query}%",
            'q2' => "%{$query}%",
            'q3' => "%{$query}%",
            'q4' => "%{$query}%",
        ];

        return $this->db->select($sql, $params);
    }

    /**
     * Obtener un producto por ID
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT p.*, prov.nombre AS proveedor_nombre,
                       cat.nombre AS categoria_nombre, i.iva AS iva_valor
                FROM vb_productos p
                LEFT JOIN vb_proveedores prov ON p.id_proveedor = prov.id_proveedor
                LEFT JOIN vb_categorias cat ON p.id_categoria = cat.id_categoria
                LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
                WHERE p.id_producto = :id";

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Crear un nuevo producto
     */
    public function crear(array $data): int
    {
        $productoId = $this->db->insert(
            "INSERT INTO vb_productos 
                (codigo, codigo_barras_1, codigo_barras_2, codigo_barras_3,
                 descripcion, presentacion, marca, id_proveedor, id_categoria,
                 id_iva, id_seccion, unidad_cerrada, fraccion,
                 valor_compra, valor_venta, valor_unidad, rentabilidad, stock_minimo)
             VALUES 
                (:codigo, :barras1, :barras2, :barras3,
                 :descripcion, :presentacion, :marca, :proveedor, :categoria,
                 :iva, :seccion, :unidad_cerrada, :fraccion,
                 :compra, :venta, :unidad, :rentabilidad, :stock_minimo)",
            [
                'codigo'          => $data['codigo'],
                'barras1'         => $data['codigo_barras_1'] ?? '',
                'barras2'         => $data['codigo_barras_2'] ?? '',
                'barras3'         => $data['codigo_barras_3'] ?? '',
                'descripcion'     => $data['descripcion'],
                'presentacion'    => $data['presentacion'] ?? '',
                'marca'           => $data['marca'] ?? '',
                'proveedor'       => $data['id_proveedor'] ?: null,
                'categoria'       => $data['id_categoria'] ?: null,
                'iva'             => $data['id_iva'] ?: null,
                'seccion'         => $data['id_seccion'] ?: null,
                'unidad_cerrada'  => (int)($data['unidad_cerrada'] ?? 1),
                'fraccion'        => (int)($data['fraccion'] ?? 0),
                'compra'          => $data['valor_compra'] ?? 0,
                'venta'           => $data['valor_venta'] ?? 0,
                'unidad'          => $data['valor_unidad'] ?? 0,
                'rentabilidad'    => $this->calcularRentabilidad($data['valor_compra'] ?? 0, $data['valor_venta'] ?? 0),
                'stock_minimo'    => (int)($data['stock_minimo'] ?? 1),
            ]
        );

        // Crear registro en inventario
        $this->db->insert(
            "INSERT INTO vb_inventario (id_producto, unidad, fraccion, created_at) 
             VALUES (:id, 0, 0, NOW())",
            ['id' => $productoId]
        );

        return $productoId;
    }

    /**
     * Actualizar un producto
     */
    public function actualizar(int $id, array $data): int
    {
        $sql = "UPDATE vb_productos SET 
                    codigo = :codigo,
                    codigo_barras_1 = :barras1,
                    codigo_barras_2 = :barras2,
                    codigo_barras_3 = :barras3,
                    descripcion = :descripcion,
                    presentacion = :presentacion,
                    marca = :marca,
                    id_proveedor = :proveedor,
                    id_categoria = :categoria,
                    id_iva = :iva,
                    id_seccion = :seccion,
                    unidad_cerrada = :unidad_cerrada,
                    fraccion = :fraccion,
                    valor_compra = :compra,
                    valor_venta = :venta,
                    valor_unidad = :unidad,
                    rentabilidad = :rentabilidad,
                    stock_minimo = :stock_minimo
                WHERE id_producto = :id";

        return $this->db->executeAffected($sql, [
            'id'              => $id,
            'codigo'          => $data['codigo'],
            'barras1'         => $data['codigo_barras_1'] ?? '',
            'barras2'         => $data['codigo_barras_2'] ?? '',
            'barras3'         => $data['codigo_barras_3'] ?? '',
            'descripcion'     => $data['descripcion'],
            'presentacion'    => $data['presentacion'] ?? '',
            'marca'           => $data['marca'] ?? '',
            'proveedor'       => $data['id_proveedor'] ?: null,
            'categoria'       => $data['id_categoria'] ?: null,
            'iva'             => $data['id_iva'] ?: null,
            'seccion'         => $data['id_seccion'] ?: null,
            'unidad_cerrada'  => (int)($data['unidad_cerrada'] ?? 1),
            'fraccion'        => (int)($data['fraccion'] ?? 0),
            'compra'          => $data['valor_compra'] ?? 0,
            'venta'           => $data['valor_venta'] ?? 0,
            'unidad'          => $data['valor_unidad'] ?? 0,
            'rentabilidad'    => $this->calcularRentabilidad($data['valor_compra'] ?? 0, $data['valor_venta'] ?? 0),
            'stock_minimo'    => (int)($data['stock_minimo'] ?? 1),
        ]);
    }

    /**
     * Eliminar (desactivar) un producto
     */
    public function eliminar(int $id): int
    {
        return $this->db->executeAffected(
            "UPDATE vb_productos SET activo = 0 WHERE id_producto = :id",
            ['id' => $id]
        );
    }

    /**
     * Obtener listas para selects (categorías, proveedores, IVAs, secciones)
     */
    public function obtenerCatalogos(): array
    {
        return [
            'categorias'  => $this->db->select("SELECT id_categoria, nombre FROM vb_categorias WHERE activo = 1 ORDER BY nombre"),
            'proveedores' => $this->db->select("SELECT id_proveedor, nombre FROM vb_proveedores WHERE estado = 1 ORDER BY nombre"),
            'ivas'        => $this->db->select("SELECT id_iva, iva, nombre FROM vb_ivas WHERE activo = 1"),
            'secciones'   => $this->db->select("SELECT id_seccion, nombre FROM vb_secciones WHERE activo = 1"),
        ];
    }

    /**
     * Calcular rentabilidad
     */
    private function calcularRentabilidad(float $compra, float $venta): float
    {
        if ($compra <= 0 || $venta <= 0) return 0;
        return round((($venta - $compra) / $venta) * 100, 2);
    }
}
