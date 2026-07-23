<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Models\Producto;

/**
 * Controlador de Productos
 */
class ProductoController
{
    private Producto $productoModel;

    public function __construct()
    {
        $this->productoModel = new Producto();
    }

    /**
     * Listado de productos
     */
    public function index(Request $request): string
    {
        $page   = (int)($request->get('page', 1));
        $search = $request->get('search', '');
        $catalogos = $this->productoModel->obtenerCatalogos();

        $view = new View();
        return $view->render('productos/index', [
            'title'      => 'Productos',
            'catalogos'  => $catalogos,
            'search'     => $search,
            'page'       => $page,
        ]);
    }

    /**
     * API: Listar productos (JSON)
     */
    public function listar(Request $request): void
    {
        $page   = (int)($request->get('page', 1));
        $search = $request->get('search', '');
        $result = $this->productoModel->listar($page, 25, $search);

        Response::success($result);
    }

    /**
     * API: Buscar productos (autocomplete)
     */
    public function search(Request $request): void
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            Response::success([]);
        }

        $productos = $this->productoModel->buscar($query);
        Response::success($productos);
    }

    /**
     * API: Obtener un producto
     */
    public function obtener(Request $request): void
    {
        $id = (int)$request->param('id');
        $producto = $this->productoModel->obtenerPorId($id);

        if (!$producto) {
            Response::error('Producto no encontrado', 404);
        }

        Response::success($producto);
    }

    /**
     * API: Guardar producto (crear o actualizar)
     */
    public function store(Request $request): void
    {
        $data = $request->json() ?: $request->all();
        $id   = (int)($data['id_producto'] ?? 0);

        // Validaciones
        $errors = $this->validar($data);
        if (!empty($errors)) {
            Response::error('Error de validación', 422, $errors);
        }

        try {
            if ($id > 0) {
                $this->productoModel->actualizar($id, $data);
                Response::success(null, 'Producto actualizado exitosamente');
            } else {
                $newId = $this->productoModel->crear($data);
                Response::success(['id' => $newId], 'Producto creado exitosamente');
            }
        } catch (\Exception $e) {
            Response::error('Error al guardar el producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Eliminar producto
     */
    public function delete(Request $request): void
    {
        $id = (int)($request->json()['id_producto'] ?? $request->post('id_producto', 0));

        if ($id <= 0) {
            Response::error('ID de producto inválido');
        }

        $this->productoModel->eliminar($id);
        Response::success(null, 'Producto eliminado exitosamente');
    }

    /**
     * Validar datos del producto
     */
    private function validar(array $data): array
    {
        $errors = [];

        if (empty($data['codigo'])) {
            $errors[] = 'El código del producto es requerido';
        }
        if (empty($data['descripcion'])) {
            $errors[] = 'La descripción del producto es requerida';
        }
        if (isset($data['valor_compra']) && $data['valor_compra'] < 0) {
            $errors[] = 'El valor de compra no puede ser negativo';
        }
        if (isset($data['valor_venta']) && $data['valor_venta'] < 0) {
            $errors[] = 'El valor de venta no puede ser negativo';
        }

        return $errors;
    }

    /**
     * API: Obtener catálogos para formularios
     */
    public function catalogos(Request $request): void
    {
        $catalogos = $this->productoModel->obtenerCatalogos();
        Response::success($catalogos);
    }
}
