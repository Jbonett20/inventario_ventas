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
     * Crear un ingreso con precio promedio ponderado
     */
    private function aplicarPrecioPromedio(int $idProducto, float $nuevoValorCompra, int $cantidadIngresada): void
    {
        $producto = $this->db->fetchOne(
            "SELECT p.valor_compra, p.rentabilidad, p.valor_venta, inv.unidad
             FROM vb_productos p
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.id_producto = :id",
            ['id' => $idProducto]
        );

        if (!$producto) return;

        $stockActual = (int)($producto['unidad'] ?? 0);
        $costoActual = (float)($producto['valor_compra'] ?? 0);

        // Solo recalcular si el nuevo precio es diferente al actual Y hay stock
        if (abs($nuevoValorCompra - $costoActual) < 0.01) return;

        $totalStock = $stockActual + $cantidadIngresada;
        if ($totalStock <= 0) return;

        // Fórmula del precio promedio ponderado
        $nuevoCosto = (($stockActual * $costoActual) + ($cantidadIngresada * $nuevoValorCompra)) / $totalStock;
        $nuevoCosto = round($nuevoCosto, 2);

        // Calcular nuevo precio de venta según rentabilidad
        $rentabilidad = (float)($producto['rentabilidad'] ?? 0);
        $nuevoVenta = $rentabilidad > 0 ? round($nuevoCosto / (1 - $rentabilidad / 100), 2) : 0;

        $this->db->executeAffected(
            "UPDATE vb_productos SET valor_compra = :compra, valor_venta = :venta WHERE id_producto = :id",
            ['compra' => $nuevoCosto, 'venta' => $nuevoVenta, 'id' => $idProducto]
        );
    }

    /**
     * Crear un ingreso y actualizar inventario automáticamente (usando triggers)
     * Soporta conversión cajas ↔ unidades según tipo_cantidad
     * Aplica precio promedio ponderado si el precio de compra cambia
     */
    public function crear(array $data, int $idUsuario): int
    {
        $cantUnd = (int)($data['cantidad_unidad'] ?? 0);
        $cantFrac = (int)($data['cantidad_fraccion'] ?? 0);
        $tipoCant = $data['tipo_cantidad'] ?? 'UNIDAD';
        $obs = $data['observacion'] ?? '';
        $nuevoValorCompra = isset($data['valor_compra']) ? (float)$data['valor_compra'] : null;

        // Si es ingreso por cajas, convertir a unidades y obtener valor_compra del producto
        if ($tipoCant === 'CAJA' && $cantUnd > 0) {
            $prod = $this->db->fetchOne(
                "SELECT unidad_cerrada, valor_compra FROM vb_productos WHERE id_producto = :id",
                ['id' => $data['id_producto']]
            );
            $undPorCaja = (int)($prod['unidad_cerrada'] ?? 1);
            $totalUnd = $cantUnd * $undPorCaja;
            if ($nuevoValorCompra === null) $nuevoValorCompra = (float)($prod['valor_compra'] ?? 0);
            $obs = ($obs ? $obs . ' | ' : '') . "Ingreso por cajas: {$cantUnd} cajas × {$undPorCaja} und = {$totalUnd} und";
            $cantUnd = $totalUnd;
        } elseif ($tipoCant === 'CAJA') {
            $obs = ($obs ? $obs . ' | ' : '') . 'Ingreso por cajas: 0 cajas';
            if ($nuevoValorCompra === null) {
                $prod = $this->db->fetchOne("SELECT valor_compra FROM vb_productos WHERE id_producto = :id", ['id' => $data['id_producto']]);
                $nuevoValorCompra = (float)($prod['valor_compra'] ?? 0);
            }
        } else {
            $obs = ($obs ? $obs . ' | ' : '') . "Ingreso por unidades: {$cantUnd} und" . ($cantFrac > 0 ? " + {$cantFrac} frac" : '');
            if ($nuevoValorCompra === null) {
                $prod = $this->db->fetchOne("SELECT valor_compra FROM vb_productos WHERE id_producto = :id", ['id' => $data['id_producto']]);
                $nuevoValorCompra = (float)($prod['valor_compra'] ?? 0);
            }
        }

        $id = $this->db->insert(
            "INSERT INTO vb_ingresos (id_producto, cantidad_unidad, cantidad_fraccion, tipo, observacion, id_usuario, fecha)
             VALUES (:prod, :und, :frac, :tipo, :obs, :uid, :fecha)",
            [
                'prod'  => $data['id_producto'],
                'und'   => $cantUnd,
                'frac'  => $cantFrac,
                'tipo'  => $data['tipo'] ?? 'MANUAL',
                'obs'   => $obs,
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

        // Aplicar precio promedio ponderado si el valor de compra cambió
        if ($nuevoValorCompra !== null && $cantUnd > 0) {
            $this->aplicarPrecioPromedio($data['id_producto'], $nuevoValorCompra, $cantUnd);
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
