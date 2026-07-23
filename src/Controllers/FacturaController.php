<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Factura;
use SIG\Models\Producto;
use SIG\Middleware\RoleMiddleware;

class FacturaController
{
    private Factura $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Factura();
        $this->session = Session::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();

        // Si la URL es /facturacion/historial, mostrar historial
        if ($r->getUri() === '/facturacion/historial' || $r->get('tab') === 'historial') {
            return $view->render('facturacion/historial', [
                'title' => 'Historial de Facturas',
            ]);
        }

        // Otherwise show the POS
        $prodModel = new Producto();
        return $view->render('facturacion/index', [
            'title'       => 'Facturación',
            'productos'   => $prodModel->buscar(''),
            'catalogos'   => $prodModel->obtenerCatalogos(),
            'isAdmin'     => RoleMiddleware::isAdmin(),
            'isCajero'    => RoleMiddleware::hasRole('cajero'),
            'puedeFE'     => RoleMiddleware::hasPermission('facturacion.electronica'),
        ]);
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['detalles']) || !is_array($data['detalles'])) {
            Response::error('Debe agregar al menos un producto', 422);
        }

        try {
            $result = $this->model->crear(
                [
                    'id_cliente'   => (int)($data['id_cliente'] ?? 1),
                    'tipo_pago'    => $data['tipo_pago'] ?? 'EFECTIVO',
                    'descuento'    => (float)($data['descuento'] ?? 0),
                    'pago_recibido'=> (float)($data['pago_recibido'] ?? 0),
                    'tipo'         => $data['tipo'] ?? 'NORMAL',
                ],
                $data['detalles'],
                $this->session->getUserId()
            );

            // Si es factura electrónica, enviar a DIAN
            if (($data['tipo'] ?? 'NORMAL') === 'ELECTRONICA') {
                $result['fe_pendiente'] = true;
            }

            Response::success($result, 'Factura creada exitosamente');

        } catch (\Exception $e) {
            Response::error('Error al crear factura: ' . $e->getMessage(), 500);
        }
    }

    public function listar(Request $r): void
    {
        $filtros = [
            'page'      => (int)$r->get('page', 1),
            'perPage'   => (int)$r->get('perPage', 25),
            'search'    => $r->get('search', ''),
            'tipo'      => $r->get('tipo', ''),
            'tipo_pago' => $r->get('tipo_pago', ''),
            'estado'    => $r->get('estado', ''),
            'fecha_ini' => $r->get('fecha_ini', ''),
            'fecha_fin' => $r->get('fecha_fin', ''),
            'codigo'    => $r->get('codigo', ''),
            'id_cliente'=> $r->get('id_cliente', ''),
        ];
        Response::success($this->model->listar($filtros));
    }

    public function obtener(Request $r): void
    {
        $id = (int)$r->param('id');
        $fac = $this->model->obtenerConDetalle($id);
        if (!$fac) { Response::error('Factura no encontrada', 404); }
        Response::success($fac);
    }

    public function anular(Request $r): void
    {
        $id = (int)(($r->json() ?: $r->all())['id_factura'] ?? 0);
        if ($id <= 0) Response::error('ID inválido');

        try {
            $this->model->anular($id);
            Response::success(null, 'Factura anulada. Productos devueltos al inventario.');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
