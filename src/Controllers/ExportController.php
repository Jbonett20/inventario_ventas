<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\Database;

class ExportController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Exportar a CSV y descargar
     */
    public function csv(Request $r): void
    {
        $tabla  = $r->get('tabla', '');
        $sql    = $r->get('sql', '');
        $nombre = $r->get('nombre', 'export');

        if (empty($sql)) {
            Response::error('Consulta no válida');
        }

        $data = $this->db->select($sql);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '_' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        // Cabeceras
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]), ';');
        }

        // Datos
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Exportar a PDF vía mpdf
     */
    public function pdf(Request $r): void
    {
        $tabla  = $r->get('tabla', '');
        $sql    = $r->get('sql', '');
        $nombre = $r->get('nombre', 'export');
        $titulo = $r->get('titulo', 'Reporte');

        if (empty($sql)) {
            Response::error('Consulta no válida');
        }

        $data = $this->db->select($sql);

        // Generar HTML de la tabla
        $html = '<h2 style="text-align:center;color:#333;font-family:sans-serif">' . htmlspecialchars($titulo) . '</h2>';
        $html .= '<p style="text-align:center;color:#999;font-size:12px">Generado: ' . date('d/m/Y H:i') . '</p>';
        $html .= '<hr>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;font-family:sans-serif;font-size:12px">';

        if (!empty($data)) {
            $html .= '<thead><tr style="background:#764ba2;color:#fff">';
            foreach (array_keys($data[0]) as $col) {
                $html .= '<th>' . htmlspecialchars($col) . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $val) {
                    $html .= '<td>' . htmlspecialchars((string)$val) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        } else {
            $html .= '<tr><td style="text-align:center;padding:30px;color:#999">No hay datos</td></tr>';
        }

        $html .= '</table>';

        // Generar PDF con mpdf
        $mpdf = new \Mpdf\Mpdf([
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'format'        => 'A4-L', // Landscape
        ]);
        $mpdf->SetTitle($titulo);
        $mpdf->WriteHTML($html);
        $mpdf->Output($nombre . '_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    /**
     * Helper: Botón de exportación para vistas
     * Devuelve HTML con los botones
     */
    public static function botones(string $sql, string $nombre, string $titulo): string
    {
        $sql64 = base64_encode($sql);
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';

        return '<div class="btn-group btn-group-sm">
            <a href="' . $basePath . '/exportar/csv?sql=' . urlencode($sql64) . '&nombre=' . urlencode($nombre) . '&tabla=' . urlencode($nombre) . '"
               class="btn btn-outline-success rounded-start" title="Exportar Excel">
                <i class="fas fa-file-excel me-1"></i>Excel
            </a>
            <a href="' . $basePath . '/exportar/pdf?sql=' . urlencode($sql64) . '&nombre=' . urlencode($nombre) . '&titulo=' . urlencode($titulo) . '&tabla=' . urlencode($nombre) . '"
               class="btn btn-outline-danger rounded-end" title="Exportar PDF">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
        </div>';
    }
}
