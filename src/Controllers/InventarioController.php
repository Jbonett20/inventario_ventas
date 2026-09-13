<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Inventario;

class InventarioController
{
    private Inventario $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Inventario();
        $this->session = Session::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();
        return $view->render('inventario/index', [
            'title' => 'Inventario',
            'resumen' => $this->model->resumen(),
            'stockBajo' => $this->model->stockBajo(),
            'puedeVerCierre' => \SIG\Middleware\RoleMiddleware::hasPermission('inventario.ver_cierre'),
            'puedeCerrar'    => \SIG\Middleware\RoleMiddleware::hasPermission('inventario.cerrar'),
            'username'   => $this->session->get('username'),
            'userTipo'   => $this->session->getUserType(),
            'userImagen' => $this->session->get('user_imagen'),
        ]);
    }

    public function listar(Request $r): void
    {
        Response::success($this->model->listar(
            (int)$r->get('page', 1), 25, $r->get('search', '')
        ));
    }

    public function adjust(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $idProducto = (int)($data['id_producto'] ?? 0);
        $unidad     = (int)($data['unidad'] ?? 0);
        $fraccion   = (int)($data['fraccion'] ?? 0);
        $observacion = $data['observacion'] ?? 'Ajuste manual';

        if ($idProducto <= 0 || $unidad < 0 || $fraccion < 0) {
            Response::error('Datos inválidos para el ajuste', 422);
        }

        $this->model->ajustar($idProducto, $unidad, $fraccion, $observacion, $this->session->getUserId());
        Response::success(null, 'Inventario ajustado correctamente');
    }

    public function resumen(Request $r): void
    {
        Response::success($this->model->resumen());
    }

    public function stockBajo(Request $r): void
    {
        Response::success($this->model->stockBajo());
    }

    public function sugerenciasReorden(Request $r): void
    {
        Response::success($this->model->sugerenciasReorden());
    }

    public function movimientos(Request $r): void
    {
        $id = (int)$r->param('id');
        $limite = (int)$r->get('limite', 20);
        $limite = ($limite > 0 && $limite <= 500) ? $limite : 20;

        Response::success($this->model->movimientos($id, $limite));
    }

    /**
     * Movimientos de inventario en un rango de fechas
     * GET /inventario/movimientos-por-fecha?desde=&hasta=&tipo=&id_producto=
     */
    public function movimientosPorFecha(Request $r): void
    {
        $desde = (string)$r->get('desde', date('Y-m-01'));
        $hasta = (string)$r->get('hasta', date('Y-m-d'));
        $tipo  = (string)$r->get('tipo', 'TODOS');
        $idProducto = (int)$r->get('id_producto', 0);

        if (!$this->fechaValida($desde) || !$this->fechaValida($hasta)) {
            Response::error('Las fechas no son válidas', 422);
        }
        if ($desde > $hasta) {
            Response::error('La fecha inicial no puede ser mayor que la final', 422);
        }

        Response::success($this->model->movimientosPorFecha($desde, $hasta, $tipo, $idProducto));
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
