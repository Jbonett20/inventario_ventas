<?php
namespace SIG\Controllers;

use DomainException;
use InvalidArgumentException;
use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\Session;
use SIG\Core\View;
use SIG\Middleware\RoleMiddleware;
use SIG\Models\TurnoCaja;

class TurnoCajaController
{
    private TurnoCaja $model;
    private Session $session;

    public function __construct()
    {
        $this->model = new TurnoCaja();
        $this->session = Session::getInstance();
    }

    public function abrir(Request $request): void
    {
        $userId = $this->session->getUserId();
        if ($userId === null) {
            Response::error('La sesión no tiene un cajero válido.', 401);
            return;
        }

        $data = $request->json() ?: $request->all();
        $baseInicial = $data['base_inicial'] ?? null;
        if (!is_numeric($baseInicial) || !is_finite((float)$baseInicial)) {
            Response::error('Ingresa una base inicial válida.', 422);
            return;
        }

        try {
            $idTurno = $this->model->abrir($userId, (float)$baseInicial);
            Response::success(
                ['id_turno' => $idTurno, 'turno' => $this->model->obtenerAbierto($userId)],
                'Turno abierto correctamente.'
            );
        } catch (InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 409);
        }
    }

    public function resumenActual(Request $request): void
    {
        $userId = $this->session->getUserId();
        if ($userId === null) {
            Response::error('La sesión no tiene un cajero válido.', 401);
            return;
        }

        $turno = $this->model->obtenerAbierto($userId);
        if ($turno === null) {
            Response::error('No tienes un turno de caja abierto.', 409);
            return;
        }

        Response::success($this->model->resumen((int)$turno['id_turno']));
    }

    public function imprimir(Request $request): void
    {
        $idTurno = filter_var($request->param('id'), FILTER_VALIDATE_INT);
        if ($idTurno === false || $idTurno <= 0) {
            Response::error('El turno seleccionado no es válido.', 422);
            return;
        }

        $turno = $this->model->obtener($idTurno);
        if ($turno === null) {
            Response::error('No se encontró el turno solicitado.', 404);
            return;
        }

        $userId = $this->session->getUserId();
        if ($userId === null || (!RoleMiddleware::isAdmin() && (int)$turno['id_usuario'] !== $userId)) {
            Response::error('No tienes permiso para consultar este cierre de caja.', 403);
            return;
        }
        if ($turno['estado'] !== 'CERRADO') {
            Response::error('El comprobante solo está disponible para turnos cerrados.', 409);
            return;
        }

        $resumen = $this->model->resumen($idTurno);
        $escape = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $dinero = static fn(mixed $value): string => number_format((float)$value, 2, ',', '.');
        $metodos = array_unique(array_merge(
            array_keys($resumen['ventas_por_metodo']),
            array_keys($resumen['devoluciones_por_metodo']),
            array_keys($resumen['egresos_por_metodo'])
        ));
        sort($metodos);

        $html = '<!doctype html><html><head><meta charset="utf-8"><style>
            body{font-family:sans-serif;font-size:11px;color:#222}
            h1{text-align:center;font-size:18px;margin:0 0 4px}
            .subtitle{text-align:center;color:#666;margin:0 0 18px}
            table{width:100%;border-collapse:collapse;margin:10px 0 16px}
            th{background:#eee;text-align:left}
            th,td{padding:7px;border:1px solid #ccc}
            .right{text-align:right}
            .total{font-weight:bold;background:#f5f5f5}
            .summary td:first-child{width:65%}
            .difference{font-weight:bold;font-size:13px}
        </style></head><body>';
        $html .= '<h1>Comprobante de cierre de caja</h1>';
        $html .= '<p class="subtitle">Turno #' . (int)$turno['id_turno'] . ' · ' . $escape($turno['fecha']) . '</p>';
        $html .= '<table class="summary"><tbody>';
        $html .= '<tr><td>Cajero</td><td>' . $escape($turno['cajero_nombre']) . '</td></tr>';
        $html .= '<tr><td>Apertura</td><td>' . $escape($turno['abierto_en']) . '</td></tr>';
        $html .= '<tr><td>Cierre</td><td>' . $escape($turno['cerrado_en']) . '</td></tr>';
        $html .= '<tr><td>Base inicial</td><td class="right">$' . $dinero($turno['base_inicial']) . '</td></tr>';
        $html .= '</tbody></table>';
        $html .= '<table><thead><tr><th>Método</th><th class="right">Ventas</th><th class="right">Devoluciones</th><th class="right">Egresos</th></tr></thead><tbody>';
        foreach ($metodos as $metodo) {
            $html .= '<tr><td>' . $escape($metodo) . '</td>';
            $html .= '<td class="right">$' . $dinero($resumen['ventas_por_metodo'][$metodo] ?? 0) . '</td>';
            $html .= '<td class="right">$' . $dinero($resumen['devoluciones_por_metodo'][$metodo] ?? 0) . '</td>';
            $html .= '<td class="right">$' . $dinero($resumen['egresos_por_metodo'][$metodo] ?? 0) . '</td></tr>';
        }
        if (!$metodos) {
            $html .= '<tr><td colspan="4" class="right">Sin movimientos</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<table class="summary"><tbody>';
        $html .= '<tr><td>Cambio entregado (ya descontado de pagos)</td><td class="right">$' . $dinero($resumen['cambio_total']) . '</td></tr>';
        $html .= '<tr class="total"><td>Efectivo esperado</td><td class="right">$' . $dinero($turno['efectivo_esperado']) . '</td></tr>';
        $html .= '<tr><td>Efectivo contado</td><td class="right">$' . $dinero($turno['efectivo_contado']) . '</td></tr>';
        $html .= '<tr class="difference"><td>Diferencia (contado - esperado)</td><td class="right">$' . $dinero($turno['diferencia']) . '</td></tr>';
        $html .= '</tbody></table>';
        $html .= '<p class="subtitle">Generado el ' . date('d/m/Y H:i') . '</p></body></html>';

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
            $mpdf->SetTitle('Cierre de caja - Turno ' . $idTurno);
            $mpdf->WriteHTML($html);
            $mpdf->Output('Cierre_turno_' . $idTurno . '.pdf', 'I');
        } catch (\Mpdf\MpdfException $e) {
            error_log('No se pudo generar el comprobante del turno ' . $idTurno . ': ' . $e->getMessage());
            Response::error('Error al generar el comprobante del turno.', 500);
        }
        exit;
    }

    public function reportes(Request $request): string
    {
        $session = $this->session;
        if (!RoleMiddleware::hasPermission('reportes.ver')) {
            Response::redirect('/');
        }
        return (new View())->render('turnos/reportes', [
            'title'      => 'Reporte de turnos',
            'username'   => $session->get('username'),
            'userTipo'   => $session->getUserType(),
            'userImagen' => $session->get('user_imagen'),
        ]);
    }

    public function reporte(Request $request): void
    {
        if (!RoleMiddleware::hasPermission('reportes.ver')) {
            Response::error('No tienes permiso para consultar reportes administrativos.', 403);
            return;
        }

        $agrupacion = (string)$request->get('agrupacion', 'dia');
        $inicio = (string)$request->get('inicio', date('Y-m-01'));
        $fin = (string)$request->get('fin', date('Y-m-d'));

        try {
            Response::success($this->model->reporte($agrupacion, $inicio, $fin));
        } catch (InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    public function cerrar(Request $request): void
    {        $userId = $this->session->getUserId();
        if ($userId === null) {
            Response::error('La sesión no tiene un cajero válido.', 401);
            return;
        }

        $data = $request->json() ?: $request->all();
        $idTurno = filter_var($data['id_turno'] ?? null, FILTER_VALIDATE_INT);
        $efectivoContado = $data['efectivo_contado'] ?? null;
        if ($idTurno === false || $idTurno <= 0) {
            Response::error('El turno seleccionado no es válido.', 422);
            return;
        }
        if (!is_numeric($efectivoContado) || !is_finite((float)$efectivoContado)) {
            Response::error('Ingresa el efectivo contado para cerrar el turno.', 422);
            return;
        }

        try {
            $turno = $this->model->cerrar($userId, $idTurno, (float)$efectivoContado);
            Response::success($turno, 'Turno cerrado correctamente.');
        } catch (InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 409);
        }
    }
}
