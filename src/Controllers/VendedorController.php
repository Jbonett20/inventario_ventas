<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Vendedor;
use SIG\Middleware\RoleMiddleware;

/**
 * Controlador de Vendedores
 *
 * Administra el catálogo de vendedores y el reporte de ventas por vendedor
 * (con cálculo de bono/comisión por rango de fechas).
 */
class VendedorController
{
    private Vendedor $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Vendedor();
        $this->session = Session::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();
        return $view->render('vendedores/index', [
            'title'         => 'Vendedores',
            'siguienteCodigo' => $this->model->siguienteCodigo(),
            'puedeEditar'   => RoleMiddleware::hasPermission('vendedores.gestion'),
            'username'      => $this->session->get('username'),
            'userTipo'      => $this->session->getUserType(),
            'userImagen'    => $this->session->get('user_imagen'),
        ]);
    }

    /**
     * GET /vendedores/listar?buscar=&activos=1
     */
    public function listar(Request $r): void
    {
        $buscar = trim((string)$r->get('buscar', ''));
        $activos = ((int)$r->get('activos', 0) === 1);

        Response::success($this->model->listar($activos, $buscar));
    }

    /**
     * GET /vendedores/buscar?q=  (usado por el POS)
     */
    public function buscar(Request $r): void
    {
        $q = trim((string)$r->get('q', ''));
        if ($q === '') {
            Response::success([]);
        }
        Response::success($this->model->buscar($q));
    }

    /**
     * GET /vendedores/siguiente-codigo
     */
    public function siguienteCodigo(Request $r): void
    {
        Response::success(['codigo' => $this->model->siguienteCodigo()]);
    }

    /**
     * POST /vendedores/guardar
     */
    public function guardar(Request $r): void
    {
        if (!RoleMiddleware::hasPermission('vendedores.gestion')) {
            Response::error('No tiene permisos para gestionar vendedores', 403);
        }

        $data = $r->json() ?: $r->all();

        try {
            $id = $this->model->guardar($data);
            $vendedor = $this->model->obtener($id);

            Response::success([
                'id_vendedor' => $id,
                'codigo'      => $vendedor['codigo'] ?? null,
            ], 'Vendedor guardado correctamente');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * POST /vendedores/eliminar
     */
    public function eliminar(Request $r): void
    {
        if (!RoleMiddleware::hasPermission('vendedores.gestion')) {
            Response::error('No tiene permisos para gestionar vendedores', 403);
        }

        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_vendedor'] ?? 0);

        if ($id <= 0) {
            Response::error('Vendedor no válido', 422);
        }

        try {
            $res = $this->model->eliminar($id);
            Response::success($res, 'Vendedor eliminado correctamente');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * GET /vendedores/ventas?desde=&hasta=&comision=&solo_con_ventas=
     *
     * Reporte de cuánto vendió cada vendedor en el rango, con el bono
     * calculado según el porcentaje indicado (o el configurado por vendedor).
     */
    public function ventas(Request $r): void
    {
        $desde = (string)$r->get('desde', date('Y-m-01'));
        $hasta = (string)$r->get('hasta', date('Y-m-d'));

        if (!$this->fechaValida($desde) || !$this->fechaValida($hasta)) {
            Response::error('Las fechas no son válidas', 422);
        }
        if ($desde > $hasta) {
            Response::error('La fecha inicial no puede ser mayor que la final', 422);
        }

        $comisionParam = $r->get('comision', null);
        $comisionOverride = null;
        if ($comisionParam !== null && $comisionParam !== '') {
            $comisionOverride = (float)$comisionParam;
            if ($comisionOverride < 0 || $comisionOverride > 100) {
                Response::error('El porcentaje de bono debe estar entre 0 y 100', 422);
            }
        }

        $soloConVentas = ((int)$r->get('solo_con_ventas', 1) === 1);

        $filas = $this->model->ventasPorVendedor($desde, $hasta, $soloConVentas);

        $totalVendido = 0.0;
        $totalBono = 0.0;
        $totalVentas = 0;

        foreach ($filas as &$f) {
            $pct = ($comisionOverride !== null) ? $comisionOverride : $f['comision'];
            $f['comision_aplicada'] = round($pct, 2);
            $f['bono'] = round($f['total'] * $pct / 100, 2);

            $totalVendido += $f['total'];
            $totalBono += $f['bono'];
            $totalVentas += $f['ventas'];
        }
        unset($f);

        Response::success([
            'data'    => $filas,
            'desde'   => $desde,
            'hasta'   => $hasta,
            'totales' => [
                'vendedores'    => count($filas),
                'ventas'        => $totalVentas,
                'total_vendido' => round($totalVendido, 2),
                'total_bono'    => round($totalBono, 2),
                'comision_usada' => $comisionOverride,
            ],
        ]);
    }

    /**
     * GET /vendedores/detalle?desde=&hasta=&tipo=&id=
     * Facturas del vendedor en el rango
     */
    public function detalle(Request $r): void
    {
        $desde = (string)$r->get('desde', date('Y-m-01'));
        $hasta = (string)$r->get('hasta', date('Y-m-d'));
        $tipo  = ((string)$r->get('tipo', 'REGISTRADO') === 'USUARIO') ? 'USUARIO' : 'REGISTRADO';
        $id    = (int)$r->get('id', 0);

        if ($id <= 0) {
            Response::error('Vendedor no válido', 422);
        }
        if (!$this->fechaValida($desde) || !$this->fechaValida($hasta)) {
            Response::error('Las fechas no son válidas', 422);
        }

        Response::success($this->model->detalleVentas($desde, $hasta, $tipo, $id));
    }

    /**
     * Validar formato de fecha AAAA-MM-DD
     */
    private function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return false;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $fecha));
        return checkdate($m, $d, $y);
    }
}
