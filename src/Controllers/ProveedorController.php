<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Models\Proveedor;

class ProveedorController
{
    private Proveedor $model;
    public function __construct() { $this->model = new Proveedor(); }

    public function index(Request $r): string
    {
        return (new View())->render('proveedores/index', ['title' => 'Proveedores']);
    }

    public function listar(Request $r): void
    {
        Response::success($this->model->listar((int)$r->get('page',1), 25, $r->get('search','')));
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        if (empty($data['codigo']) || empty($data['nombre'])) {
            Response::error('Código y nombre requeridos', 422);
        }
        $this->model->crear($data);
        Response::success(null, 'Proveedor creado');
    }

    public function delete(Request $r): void
    {
        $id = (int)($r->json()['id_proveedor'] ?? 0);
        if ($id <= 0) Response::error('ID inválido');
        $this->model->eliminar($id);
        Response::success(null, 'Proveedor eliminado');
    }
}
