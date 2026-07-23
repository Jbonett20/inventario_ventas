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

    public function movimientos(Request $r): void
    {
        $id = (int)$r->param('id');
        Response::success($this->model->movimientos($id));
    }
}
