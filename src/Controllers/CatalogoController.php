<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Catalogo;
use SIG\Middleware\RoleMiddleware;

/**
 * Controlador de Catálogos
 *
 * Gestiona las tablas simples usadas por el formulario de productos:
 * categorías, secciones y tipos de IVA.
 */
class CatalogoController
{
    private Catalogo $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Catalogo();
        $this->session = Session::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();
        return $view->render('catalogos/index', [
            'title'       => 'Catálogos',
            'puedeEditar' => RoleMiddleware::hasPermission('catalogos.gestion'),
            'username'    => $this->session->get('username'),
            'userTipo'    => $this->session->getUserType(),
            'userImagen'  => $this->session->get('user_imagen'),
        ]);
    }

    /**
     * GET /catalogos/listar?tipo=categorias
     */
    public function listar(Request $r): void
    {
        $tipo = (string)$r->get('tipo', 'categorias');

        try {
            Response::success($this->model->listar($tipo));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * POST /catalogos/guardar
     */
    public function guardar(Request $r): void
    {
        if (!RoleMiddleware::hasPermission('catalogos.gestion')) {
            Response::error('No tiene permisos para gestionar catálogos', 403);
        }

        $data = $r->json() ?: $r->all();
        $tipo = (string)($data['tipo'] ?? '');

        try {
            $id = $this->model->guardar($tipo, $data);
            Response::success(['id' => $id], 'Registro guardado correctamente');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * POST /catalogos/eliminar
     */
    public function eliminar(Request $r): void
    {
        if (!RoleMiddleware::hasPermission('catalogos.gestion')) {
            Response::error('No tiene permisos para gestionar catálogos', 403);
        }

        $data = $r->json() ?: $r->all();
        $tipo = (string)($data['tipo'] ?? '');
        $id   = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            Response::error('Registro no válido', 422);
        }

        try {
            $res = $this->model->eliminar($tipo, $id);
            $msg = 'Registro eliminado';
            if ($res['productos_afectados'] > 0) {
                $msg .= ' (' . $res['productos_afectados'] . ' producto(s) quedaron sin este dato)';
            }
            Response::success($res, $msg);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }
}
