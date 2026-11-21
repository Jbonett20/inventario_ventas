<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Database;
use SIG\Middleware\RoleMiddleware;

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

    /**
     * API: Ventas activas agrupadas por cajero para una fecha.
     */
    public function ventasPorCajero(Request $r): void
    {
        if (!$this->tienePermisoReportes()) {
            return;
        }

        $fecha = $this->validarFechaSolicitud($r);
        if ($fecha === null) {
            return;
        }

        Response::success($this->obtenerReporteVentasPorCajero($fecha));
    }

    public function imprimirVentasPorCajero(Request $r): void
    {
        if (!$this->tienePermisoReportes()) {
            return;
        }

        $fecha = $this->validarFechaSolicitud($r);
        if ($fecha === null) {
            return;
        }

        $reporte = $this->obtenerReporteVentasPorCajero($fecha);
        $escape = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $dinero = static fn(mixed $value): string => number_format((float)$value, 2, ',', '.');

        $html = '<!doctype html><html><head><meta charset="utf-8"><style>
            body{font-family:sans-serif;font-size:12px;color:#222}
            h1{text-align:center;font-size:18px;margin:0 0 6px}
            .subtitle{text-align:center;color:#666;margin:0 0 18px}
            table{width:100%;border-collapse:collapse}
            th{background:#eee;text-align:left}
            th,td{padding:8px;border:1px solid #ccc}
            .right{text-align:right}
            .center{text-align:center}
            .total{font-weight:bold;background:#f5f5f5}
        </style></head><body>';
        $html .= '<h1>Ventas diarias por cajero</h1>';
        $html .= '<p class="subtitle">Fecha: ' . $escape($reporte['fecha']) . '</p>';
        $html .= '<table><thead><tr><th>Cajero</th><th class="center">Facturas activas</th><th class="right">Total vendido</th></tr></thead><tbody>';
        foreach ($reporte['cajeros'] as $cajero) {
            $html .= '<tr><td>' . $escape($cajero['cajero']) . '</td>';
            $html .= '<td class="center">' . (int)$cajero['cantidad'] . '</td>';
            $html .= '<td class="right">$' . $dinero($cajero['total']) . '</td></tr>';
        }
        if (!$reporte['cajeros']) {
            $html .= '<tr><td colspan="3" class="center">No hay ventas activas para esta fecha.</td></tr>';
        }
        $html .= '</tbody><tfoot><tr class="total"><td>Total general</td>';
        $html .= '<td class="center">' . (int)$reporte['cantidad'] . '</td>';
        $html .= '<td class="right">$' . $dinero($reporte['total']) . '</td></tr></tfoot></table>';
        $html .= '<p class="subtitle" style="margin-top:18px">Generado el ' . date('d/m/Y H:i') . '</p>';
        $html .= '</body></html>';

        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => 10,
                'margin_bottom' => 12,
                'tempDir'       => sys_get_temp_dir() . '/mpdf',
            ]);
            $mpdf->SetTitle('Ventas por cajero ' . $fecha);
            $mpdf->WriteHTML($html);
            $mpdf->Output('Ventas_por_cajero_' . $fecha . '.pdf', 'I');
        } catch (\Mpdf\MpdfException $e) {
            error_log('No se pudo generar el reporte diario por cajero: ' . $e->getMessage());
            Response::error('Error al generar el reporte diario por cajero.', 500);
        }
        exit;
    }

    /**
     * @return array{fecha: string, cajeros: list<array{id_cajero: int, cajero: string, cantidad: int, total: float}>, cantidad: int, total: float}
     */
    private function obtenerReporteVentasPorCajero(string $fecha): array
    {
        $filas = $this->db->select(
            "SELECT f.id_vendedor AS id_cajero,
                    COALESCE(NULLIF(u.nombre_completo, ''), u.nombre_usuario, 'Sin identificar') AS cajero,
                    COUNT(*) AS cantidad,
                    COALESCE(SUM(f.total), 0) AS total
             FROM vb_facturas f
             JOIN vb_usuarios u ON u.id_usuario = f.id_vendedor
             WHERE f.fecha = :fecha AND f.estado IN ('ACTIVA', 'ACTIVO')
             GROUP BY f.id_vendedor, u.nombre_completo, u.nombre_usuario
             ORDER BY cajero ASC",
            ['fecha' => $fecha]
        );

        $cajeros = [];
        $cantidad = 0;
        $total = 0.0;
        foreach ($filas as $fila) {
            $cantidadCajero = (int)$fila['cantidad'];
            $totalCajero = (float)$fila['total'];
            $cajeros[] = [
                'id_cajero' => (int)$fila['id_cajero'],
                'cajero'    => (string)$fila['cajero'],
                'cantidad'  => $cantidadCajero,
                'total'     => $totalCajero,
            ];
            $cantidad += $cantidadCajero;
            $total += $totalCajero;
        }

        return [
            'fecha'    => $fecha,
            'cajeros'  => $cajeros,
            'cantidad' => $cantidad,
            'total'    => round($total, 2),
        ];
    }

    private function validarFechaSolicitud(Request $request): ?string
    {
        $fecha = $request->get('fecha', date('Y-m-d'));
        if (!is_string($fecha) || !$this->fechaValida($fecha)) {
            Response::error('La fecha del reporte no es válida.', 422);
            return null;
        }

        return $fecha;
    }

    private function tienePermisoReportes(): bool
    {
        if (!RoleMiddleware::hasPermission('reportes.ver')) {
            Response::error('No tienes permiso para consultar reportes administrativos.', 403);
            return false;
        }

        return true;
    }

    private function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $fecha)) {
            return false;
        }

        $partes = explode('-', $fecha);
        return checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
    }
}
