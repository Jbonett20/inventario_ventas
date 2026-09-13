<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Database;

class ReporteController
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function index(Request $r): string
    {
        $session = \SIG\Core\Session::getInstance();

        return (new View())->render('reportes/index', [
            'title'      => 'Reportes',
            'username'   => $session->get('username'),
            'userTipo'   => $session->getUserType(),
            'userImagen' => $session->get('user_imagen'),
        ]);
    }

    /**
     * API: Ventas por período
     */
    public function ventas(Request $r): void
    {
        $inicio = $r->get('inicio', date('Y-m-01'));
        $fin    = $r->get('fin', date('Y-m-d'));

        $ventas = $this->db->select(
            "SELECT f.*, c.nombre AS cliente_nombre, u.nombre_usuario AS vendedor
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             LEFT JOIN vb_usuarios u ON f.id_vendedor = u.id_usuario
             WHERE f.fecha BETWEEN :ini AND :fin AND f.estado = 'ACTIVA'
             ORDER BY f.fecha DESC",
            ['ini' => $inicio, 'fin' => $fin]
        );

        $resumen = $this->db->fetchOne(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(total),0) AS total, 
                    COALESCE(SUM(ganancia),0) AS ganancia
             FROM vb_facturas WHERE fecha BETWEEN :ini AND :fin AND estado = 'ACTIVA'",
            ['ini' => $inicio, 'fin' => $fin]
        );

        Response::success([
            'ventas'  => $ventas,
            'resumen' => $resumen,
        ]);
    }

    /**
     * API: Productos más vendidos
     */
    public function topProductos(Request $r): void
    {
        $inicio = $r->get('inicio', date('Y-m-01'));
        $fin    = $r->get('fin', date('Y-m-d'));

        $data = $this->db->select(
            "SELECT p.id_producto, p.codigo, p.descripcion, p.presentacion,
                    SUM(d.cantidad_unidad) AS total_unidad,
                    SUM(d.cantidad_fraccion) AS total_fraccion,
                    SUM(d.total) AS total_vendido,
                    COUNT(DISTINCT d.id_factura) AS veces_vendido
             FROM vb_detalle_facturas d
             JOIN vb_facturas f ON d.id_factura = f.id_factura
             JOIN vb_productos p ON d.id_producto = p.id_producto
             WHERE f.fecha BETWEEN :ini AND :fin AND f.estado = 'ACTIVA'
             GROUP BY p.id_producto
             ORDER BY total_vendido DESC
             LIMIT 20",
            ['ini' => $inicio, 'fin' => $fin]
        );

        Response::success($data);
    }

    /**
     * API: Resumen por método de pago
     */
    public function metodosPago(Request $r): void
    {
        $inicio = $r->get('inicio', date('Y-m-01'));
        $fin    = $r->get('fin', date('Y-m-d'));

        // Se agrupa por el DETALLE de pagos: así una venta pagada con parte en
        // efectivo y parte en Nequi reparte su valor entre los dos métodos.
        $data = $this->db->select(
            "SELECT pg.metodo AS tipo_pago,
                    COUNT(DISTINCT pg.id_factura) AS cantidad,
                    COALESCE(SUM(pg.monto), 0) AS total
             FROM vb_facturas_pagos pg
             JOIN vb_facturas f ON f.id_factura = pg.id_factura
             WHERE f.fecha BETWEEN :ini AND :fin AND f.estado = 'ACTIVA'
             GROUP BY pg.metodo ORDER BY total DESC",
            ['ini' => $inicio, 'fin' => $fin]
        );

        Response::success($data);
    }

    /**
     * API: Ventas por día (para gráficos)
     */
    public function ventasDiarias(Request $r): void
    {
        $inicio = $r->get('inicio', date('Y-m-01'));
        $fin    = $r->get('fin', date('Y-m-d'));

        $data = $this->db->select(
            "SELECT fecha, COUNT(*) AS cantidad, COALESCE(SUM(total),0) AS total
             FROM vb_facturas
             WHERE fecha BETWEEN :ini AND :fin AND estado = 'ACTIVA'
             GROUP BY fecha ORDER BY fecha ASC",
            ['ini' => $inicio, 'fin' => $fin]
        );

        Response::success($data);
    }
}
