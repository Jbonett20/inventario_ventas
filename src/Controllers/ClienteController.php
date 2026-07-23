<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Models\Cliente;

class ClienteController
{
    private Cliente $model;

    public function __construct() { $this->model = new Cliente(); }

    public function index(Request $request): string
    {
        $view = new View();
        return $view->render('clientes/index', ['title' => 'Clientes']);
    }

    public function listar(Request $request): void
    {
        $page   = (int)$request->get('page', 1);
        $search = $request->get('search', '');
        Response::success($this->model->listar($page, 25, $search));
    }

    public function search(Request $request): void
    {
        $q = $request->get('q', '');
        Response::success($this->model->buscar($q));
    }

    public function obtener(Request $request): void
    {
        $id = (int)$request->param('id');
        $c = $this->model->obtenerPorId($id);
        if (!$c) { Response::error('Cliente no encontrado', 404); }
        Response::success($c);
    }

    public function store(Request $request): void
    {
        $data = $request->json() ?: $request->all();
        $id   = (int)($data['id_cliente'] ?? 0);

        if (empty($data['documento']) || empty($data['nombre'])) {
            Response::error('Documento y nombre son requeridos', 422);
        }

        try {
            if ($id > 0) {
                $this->model->actualizar($id, $data);
                Response::success(null, 'Cliente actualizado');
            } else {
                $newId = $this->model->crear($data);
                Response::success(['id' => $newId], 'Cliente creado');
            }
        } catch (\Exception $e) {
            Response::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function delete(Request $request): void
    {
        $id = (int)($request->json()['id_cliente'] ?? 0);
        if ($id <= 0) { Response::error('ID inválido'); }
        $this->model->eliminar($id);
        Response::success(null, 'Cliente eliminado');
    }
}
