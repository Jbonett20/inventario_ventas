<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Core\Database;

class BalanceController
{
    private Session $session;
    private Database $db;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->db      = Database::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();
        return $view->render('balance/index', [
            'title' => 'Balance Diario',
        ]);
    }

    /**
     * API: Resumen del balance para una fecha
     */
    public function resumen(Request $r): void
    {
        $fecha = $r->get('fecha', date('Y-m-d'));

        // Base diaria
        $base = $this->db->fetchOne(
            "SELECT COALESCE(SUM(base), 0) AS total FROM vb_base_diaria WHERE fecha = :fecha",
            ['fecha' => $fecha]
        );

        // Ventas del día
        $ventas = $this->db->fetchOne(
            "SELECT COALESCE(COUNT(*), 0) AS cantidad, COALESCE(SUM(total), 0) AS total,
                    COALESCE(SUM(cambio), 0) AS total_cambio, COALESCE(SUM(ganancia), 0) AS total_ganancia
             FROM vb_facturas WHERE fecha = :fecha AND estado = 'ACTIVA'",
            ['fecha' => $fecha]
        );

        // Devoluciones del día
        $devoluciones = $this->db->fetchOne(
            "SELECT COALESCE(COUNT(*), 0) AS cantidad, COALESCE(SUM(total), 0) AS total
             FROM vb_devoluciones WHERE fecha = :fecha",
            ['fecha' => $fecha]
        );

        // Egresos del día
        $egresos = $this->db->fetchOne(
            "SELECT COALESCE(COUNT(*), 0) AS cantidad, COALESCE(SUM(valor), 0) AS total
             FROM vb_egresos WHERE fecha = :fecha",
            ['fecha' => $fecha]
        );

        // Facturas anuladas del día
        $anuladas = $this->db->fetchOne(
            "SELECT COALESCE(COUNT(*), 0) AS cantidad, COALESCE(SUM(total), 0) AS total
             FROM vb_facturas WHERE fecha = :fecha AND estado = 'ANULADA'",
            ['fecha' => $fecha]
        );

        // Cierre del día (si existe)
        $cierre = $this->db->fetchOne(
            "SELECT * FROM vb_cierres_inventario WHERE fecha_cierre = :fecha",
            ['fecha' => $fecha]
        );

        $totalBase = (float)($base['total'] ?? 0);
        $totalVentas = (float)($ventas['total'] ?? 0);
        $totalCambio = (float)($ventas['total_cambio'] ?? 0);
        $totalGanancia = (float)($ventas['total_ganancia'] ?? 0);
        $totalDevoluciones = (float)($devoluciones['total'] ?? 0);
        $totalEgresos = (float)($egresos['total'] ?? 0);
        $totalAnuladas = (float)($anuladas['total'] ?? 0);

        // Saldo esperado: base + ventas - cambio - devoluciones - egresos
        $saldoEsperado = $totalBase + $totalVentas - $totalCambio - $totalDevoluciones - $totalEgresos;

        Response::success([
            'fecha'          => $fecha,
            'base_diaria'    => $totalBase,
            'ventas'         => [
                'cantidad'     => (int)($ventas['cantidad'] ?? 0),
                'total'        => $totalVentas,
                'total_cambio' => $totalCambio,
                'ganancia'     => $totalGanancia,
            ],
            'devoluciones'   => [
                'cantidad' => (int)($devoluciones['cantidad'] ?? 0),
                'total'    => $totalDevoluciones,
            ],
            'egresos'        => [
                'cantidad' => (int)($egresos['cantidad'] ?? 0),
                'total'    => $totalEgresos,
            ],
            'anuladas'       => [
                'cantidad' => (int)($anuladas['cantidad'] ?? 0),
                'total'    => $totalAnuladas,
            ],
            'saldo_esperado' => $saldoEsperado,
            'cerrado'        => $cierre ? true : false,
            'cierre'         => $cierre,
        ]);
    }

    /**
     * API: Detalle de transacciones del día
     */
    public function detalle(Request $r): void
    {
        $fecha = $r->get('fecha', date('Y-m-d'));
        $tipo  = $r->get('tipo', 'facturas'); // facturas, devoluciones, egresos

        switch ($tipo) {
            case 'facturas':
                $data = $this->db->select(
                    "SELECT f.id_factura, f.codigo, f.fecha, f.hora, f.tipo, f.tipo_pago,
                            f.total, f.cambio, f.descuento, f.estado,
                            c.nombre AS cliente_nombre
                     FROM vb_facturas f
                     LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
                     WHERE f.fecha = :fecha
                     ORDER BY f.hora ASC",
                    ['fecha' => $fecha]
                );
                break;
            case 'devoluciones':
                $data = $this->db->select(
                    "SELECT d.*, f.codigo AS factura_codigo, c.nombre AS cliente_nombre
                     FROM vb_devoluciones d
                     LEFT JOIN vb_facturas f ON d.id_factura = f.id_factura
                     LEFT JOIN vb_clientes c ON d.id_cliente = c.id_cliente
                     WHERE d.fecha = :fecha
                     ORDER BY d.created_at ASC",
                    ['fecha' => $fecha]
                );
                break;
            case 'egresos':
                $data = $this->db->select(
                    "SELECT e.*, te.nombre AS tipo_egreso_nombre
                     FROM vb_egresos e
                     LEFT JOIN vb_tipos_egreso te ON e.id_tipo_egreso = te.id_tipo_egreso
                     WHERE e.fecha = :fecha
                     ORDER BY e.created_at ASC",
                    ['fecha' => $fecha]
                );
                break;
            default:
                Response::error('Tipo inválido', 422);
                return;
        }

        Response::success($data);
    }

    /**
     * API: Cerrar el día
     */
    public function cerrar(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $fecha = $data['fecha'] ?? date('Y-m-d');

        // Verificar si ya está cerrado
        $existe = $this->db->fetchOne(
            "SELECT id_cierre FROM vb_cierres_inventario WHERE fecha_cierre = :fecha",
            ['fecha' => $fecha]
        );
        if ($existe) {
            Response::error('El día ya está cerrado', 400);
        }

        // Obtener resumen del inventario
        $inventario = $this->db->fetchOne(
            "SELECT 
                COALESCE(SUM(inv.unidad), 0) AS total_unidad,
                COALESCE(SUM(inv.fraccion), 0) AS total_fraccion,
                COALESCE(SUM(p.valor_compra * inv.unidad), 0) AS capital_invertido,
                COALESCE(SUM(p.valor_venta * inv.unidad), 0) AS capital_total
             FROM vb_inventario inv
             JOIN vb_productos p ON inv.id_producto = p.id_producto
             WHERE p.activo = 1"
        );

        $capInvertido = (float)($inventario['capital_invertido'] ?? 0);
        $capTotal = (float)($inventario['capital_total'] ?? 0);

        $id = $this->db->insert(
            "INSERT INTO vb_cierres_inventario (total_invertido, total_capital, total_utilidad, total_unidad, total_fraccion, fecha_cierre)
             VALUES (:inv, :cap, :util, :und, :frac, :fecha)",
            [
                'inv'   => $capInvertido,
                'cap'   => $capTotal,
                'util'  => $capTotal - $capInvertido,
                'und'   => (int)($inventario['total_unidad'] ?? 0),
                'frac'  => (int)($inventario['total_fraccion'] ?? 0),
                'fecha' => $fecha,
            ]
        );

        Response::success(['id_cierre' => $id], 'Día cerrado correctamente');
    }

    /**
     * Exportar balance a PDF
     */
    public function exportarPdf(Request $r): void
    {
        $fecha = $r->get('fecha', date('Y-m-d'));

        // Obtener datos del balance
        $resumen = $this->db->fetchOne(
            "SELECT 
                (SELECT COALESCE(SUM(base), 0) FROM vb_base_diaria WHERE fecha = :f1) AS base_diaria,
                (SELECT COALESCE(COUNT(*), 0) FROM vb_facturas WHERE fecha = :f2 AND estado = 'ACTIVA') AS ventas_cant,
                (SELECT COALESCE(SUM(total), 0) FROM vb_facturas WHERE fecha = :f3 AND estado = 'ACTIVA') AS ventas_total,
                (SELECT COALESCE(SUM(cambio), 0) FROM vb_facturas WHERE fecha = :f4 AND estado = 'ACTIVA') AS ventas_cambio,
                (SELECT COALESCE(COUNT(*), 0) FROM vb_devoluciones WHERE fecha = :f5) AS devoluciones_cant,
                (SELECT COALESCE(SUM(total), 0) FROM vb_devoluciones WHERE fecha = :f6) AS devoluciones_total,
                (SELECT COALESCE(COUNT(*), 0) FROM vb_egresos WHERE fecha = :f7) AS egresos_cant,
                (SELECT COALESCE(SUM(valor), 0) FROM vb_egresos WHERE fecha = :f8) AS egresos_total
            ",
            ['f1' => $fecha, 'f2' => $fecha, 'f3' => $fecha, 'f4' => $fecha,
             'f5' => $fecha, 'f6' => $fecha, 'f7' => $fecha, 'f8' => $fecha]
        );

        $ventas = (float)($resumen['ventas_total'] ?? 0);
        $cambio = (float)($resumen['ventas_cambio'] ?? 0);
        $devoluciones = (float)($resumen['devoluciones_total'] ?? 0);
        $egresos = (float)($resumen['egresos_total'] ?? 0);
        $base = (float)($resumen['base_diaria'] ?? 0);
        $saldo = $base + $ventas - $cambio - $devoluciones - $egresos;

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<style>
            body{font-family:sans-serif;font-size:12px;color:#333;margin:20px}
            h1{text-align:center;color:#764ba2;font-size:18px}
            .fecha{text-align:center;color:#999;font-size:12px;margin-bottom:20px}
            table{width:100%;border-collapse:collapse;margin-bottom:15px}
            th{background:#764ba2;color:#fff;padding:8px;text-align:left;font-size:11px}
            td{padding:6px 8px;border-bottom:1px solid #ddd;font-size:11px}
            .total-row td{font-weight:bold;font-size:13px;color:#764ba2}
            .label{color:#666;width:200px}
            .right{text-align:right}
            hr{border:1px dashed #ddd;margin:15px 0}
        </style></head><body>';
        $html .= '<h1>Balance Diario</h1>';
        $html .= '<p class="fecha">' . $fecha . '</p>';
        $html .= '<hr>';
        $html .= '<table><tr><td class="label">Base diaria:</td><td class="right">$' . number_format($base, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="label">Ventas (' . (int)($resumen['ventas_cant'] ?? 0) . '):</td><td class="right">$' . number_format($ventas, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="label">Vueltas (cambio):</td><td class="right">-$' . number_format($cambio, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="label">Devoluciones (' . (int)($resumen['devoluciones_cant'] ?? 0) . '):</td><td class="right">-$' . number_format($devoluciones, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="label">Egresos (' . (int)($resumen['egresos_cant'] ?? 0) . '):</td><td class="right">-$' . number_format($egresos, 0, ',', '.') . '</td></tr>';
        $html .= '<tr class="total-row"><td>SALDO ESPERADO:</td><td class="right">$' . number_format($saldo, 0, ',', '.') . '</td></tr>';
        $html .= '</table>';
        $html .= '<hr>';
        $html .= '<p style="text-align:center;font-size:10px;color:#999">Generado el ' . date('d/m/Y H:i') . '</p>';
        $html .= '</body></html>';

        try {
            $mpdf = new \Mpdf\Mpdf([
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => 10,
                'margin_bottom' => 15,
                'format'        => 'A4',
            ]);
            $mpdf->SetTitle('Balance Diario ' . $fecha);
            $mpdf->WriteHTML($html);
            $mpdf->Output('Balance_' . $fecha . '.pdf', 'D');
        } catch (\Exception $e) {
            Response::error('Error al generar PDF: ' . $e->getMessage(), 500);
        }
        exit;
    }

    /**
     * Exportar balance a CSV
     */
    public function exportarCsv(Request $r): void
    {
        $fecha = $r->get('fecha', date('Y-m-d'));

        // Facturas del día
        $facturas = $this->db->select(
            "SELECT f.codigo AS Codigo, f.fecha AS Fecha, f.hora AS Hora,
                    c.nombre AS Cliente, f.tipo AS Tipo, f.tipo_pago AS Pago,
                    f.total AS Total, f.cambio AS Cambio, f.estado AS Estado
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             WHERE f.fecha = :fecha
             ORDER BY f.hora ASC",
            ['fecha' => $fecha]
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="balance_' . $fecha . '.csv"');
        header('Cache-Control: no-cache');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['BALANCE DIARIO - ' . $fecha], ';');
        fputcsv($output, [], ';');

        if (!empty($facturas)) {
            fputcsv($output, array_keys($facturas[0]), ';');
            foreach ($facturas as $row) {
                fputcsv($output, $row, ';');
            }
        }

        fclose($output);
        exit;
    }
}
