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

        // Validar que si es por cajas, al menos haya cantidad
        $tipoCant = $data['tipo_cantidad'] ?? 'UNIDAD';
        if ($tipoCant === 'CAJA' && $cantU <= 0) {
            Response::error('Debe ingresar al menos una caja', 422);
        }

        try {
            $id = $this->model->crear($data, $this->session->getUserId());
            Response::success(['id' => $id], 'Ingreso registrado correctamente');
        } catch (\Exception $e) {
            Response::error('Error al registrar ingreso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Ingreso rápido por código (solo código + cantidad)
     */
    public function rapido(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['codigo'])) {
            Response::error('Debe ingresar un código de producto', 422);
        }

        // Buscar producto por código o código de barras
        $db = \SIG\Core\Database::getInstance();
        $producto = $db->fetchOne(
            "SELECT p.*, inv.unidad AS stock_actual, inv.fraccion AS stock_frac
             FROM vb_productos p
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.activo = 1 AND (p.codigo = :cod OR p.codigo_barras_1 = :cod2 OR p.codigo_barras_2 = :cod3 OR p.codigo_barras_3 = :cod4)",
            ['cod' => $data['codigo'], 'cod2' => $data['codigo'], 'cod3' => $data['codigo'], 'cod4' => $data['codigo']]
        );

        if (!$producto) {
            Response::error('Producto no encontrado con ese código', 404);
        }

        $data['id_producto'] = $producto['id_producto'];
        $data['tipo_cantidad'] = $data['tipo_cantidad'] ?? 'UNIDAD';

        try {
            $id = $this->model->crear($data, $this->session->getUserId());
            $nuevoStock = $db->fetchOne(
                "SELECT unidad, fraccion FROM vb_inventario WHERE id_producto = :id",
                ['id' => $producto['id_producto']]
            );
            Response::success([
                'id_ingreso' => $id,
                'producto'   => $producto['descripcion'],
                'codigo'     => $producto['codigo'],
                'stock_anterior' => $producto['stock_actual'] ?? 0,
                'stock_nuevo'    => $nuevoStock['unidad'] ?? 0,
            ], 'Ingreso rápido registrado correctamente');
        } catch (\Exception $e) {
            Response::error('Error en ingreso rápido: ' . $e->getMessage(), 500);
        }
    }

    public function buscarProductos(Request $r): void
    {
        $q = $r->get('q', '');
        Response::success($this->model->buscarProductos($q));
    }
}
