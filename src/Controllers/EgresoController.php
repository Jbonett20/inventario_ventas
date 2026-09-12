<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Egreso;

class EgresoController
{
    private Egreso $model;
    private Session $session;
    public function __construct() { $this->model = new Egreso(); $this->session = Session::getInstance(); }

    public function index(Request $r): string
    {
        return (new View())->render('egresos/index', [
            'title' => 'Egresos',
            'tipos' => $this->model->tipos(),
            'tiposTodos' => $this->model->tiposTodos(),
            'meses' => $this->model->meses(),
            'username'   => $this->session->get('username'),
            'userTipo'   => $this->session->getUserType(),
            'userImagen' => $this->session->get('user_imagen'),
        ]);
    }

    public function listar(Request $r): void
    {
        Response::success($this->model->listar(
            (int)$r->get('page', 1),
            25,
            (string)$r->get('mes', ''),
            (string)$r->get('desde', ''),
            (string)$r->get('hasta', '')
        ));
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        if (empty($data['id_tipo_egreso']) || empty($data['valor'])) {
            Response::error('Tipo de egreso y valor requeridos', 422);
        }
        if ((float)$data['valor'] <= 0) {
            Response::error('El valor debe ser mayor que cero', 422);
        }
        $this->model->crear($data, $this->session->getUserId());
        Response::success(null, 'Egreso registrado');
    }

    /**
     * POST /egresos/eliminar
     */
    public function delete(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_egreso'] ?? 0);

        if ($id <= 0) {
            Response::error('Egreso no válido', 422);
        }

        try {
            $this->model->eliminar($id);
            Response::success(null, 'Egreso eliminado correctamente');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * GET /egresos/tipos
     */
    public function tiposListar(Request $r): void
    {
        Response::success($this->model->tiposTodos());
    }

    /**
     * POST /egresos/tipos/guardar
     */
    public function tiposGuardar(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        try {
            $id = $this->model->guardarTipo($data);
            Response::success(['id_tipo_egreso' => $id], 'Tipo de egreso guardado correctamente');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * POST /egresos/tipos/eliminar
     */
    public function tiposEliminar(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_tipo_egreso'] ?? 0);

        if ($id <= 0) {
            Response::error('Tipo de egreso no válido', 422);
        }

        try {
            $this->model->eliminarTipo($id);
            Response::success(null, 'Tipo de egreso eliminado correctamente');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }
}
