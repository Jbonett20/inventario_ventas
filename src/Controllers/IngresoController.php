<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Ingreso;

class IngresoController
{
    private Ingreso $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Ingreso();
        $this->session = Session::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();
        return $view->render('ingresos/index', [
            'title' => 'Ingresos',
            'facturas' => $this->model->listarFacturasCompra(),
        ]);
    }

    public function listar(Request $r): void
    {
        Response::success($this->model->listar((int)$r->get('page', 1)));
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['id_producto'])) {
            Response::error('Debe seleccionar un producto', 422);
        }

        $cantU = (int)($data['cantidad_unidad'] ?? 0);
        $cantF = (int)($data['cantidad_fraccion'] ?? 0);

        if ($cantU <= 0 && $cantF <= 0) {
            Response::error('Debe ingresar al menos una cantidad', 422);
        }

        try {
            $id = $this->model->crear($data, $this->session->getUserId());
            Response::success(['id' => $id], 'Ingreso registrado correctamente');
        } catch (\Exception $e) {
            Response::error('Error al registrar ingreso: ' . $e->getMessage(), 500);
        }
    }

    public function buscarProductos(Request $r): void
    {
        $q = $r->get('q', '');
        Response::success($this->model->buscarProductos($q));
    }
}
