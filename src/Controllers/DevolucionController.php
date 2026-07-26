<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Devolucion;
use SIG\Models\Factura;

class DevolucionController
{
    private Devolucion $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Devolucion();
        $this->session = Session::getInstance();
    }

    /**
     * Vista de historial de devoluciones
     */
    public function index(Request $r): string
    {
        $view = new View();
        return $view->render('devoluciones/index', [
            'title' => 'Devoluciones',
        ]);
    }

    /**
     * API: Procesar devolución
     */
    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['id_factura'])) {
            Response::error('Debe especificar la factura', 422);
        }
        if (empty($data['detalles']) || !is_array($data['detalles'])) {
            Response::error('Debe seleccionar al menos un producto', 422);
        }
        if (empty($data['motivo'])) {
            Response::error('El motivo de la devolución es obligatorio', 422);
        }

        try {
            $id = $this->model->crear($data, $this->session->getUserId());
            Response::success(['id_devolucion' => $id], 'Devolución procesada correctamente');
        } catch (\Exception $e) {
            Response::error('Error al procesar devolución: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Listar devoluciones
     */
    public function listar(Request $r): void
    {
        $page   = (int)$r->get('page', 1);
        $search = $r->get('search', '');
        Response::success($this->model->listar($page, 25, $search));
    }

    /**
     * API: Obtener detalle de devolución
     */
    public function obtener(Request $r): void
    {
        $id = (int)$r->param('id');
        $dev = $this->model->obtenerConDetalle($id);
        if (!$dev) {
            Response::error('Devolución no encontrada', 404);
        }
        Response::success($dev);
    }

    /**
     * API: Obtener factura con sus detalles para hacer devolución
     */
    public function obtenerFactura(Request $r): void
    {
        $id = (int)$r->param('id');
        $facturaModel = new Factura();
        $fac = $facturaModel->obtenerConDetalle($id);
        if (!$fac) {
            Response::error('Factura no encontrada', 404);
        }
        if ($fac['estado'] !== 'ACTIVA') {
            Response::error('La factura no está activa', 400);
        }
        if ($fac['tipo'] === 'ELECTRONICA') {
            Response::error(
                'Las facturas electrónicas no admiten devoluciones por este medio. ' .
                'Debe generar una Nota Crédito desde el módulo de facturación electrónica.',
                400
            );
        }
        Response::success($fac);
    }
}
