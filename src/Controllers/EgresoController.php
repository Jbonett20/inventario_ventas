<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Egreso;
use DomainException;
use InvalidArgumentException;

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
            'metodosPago' => Egreso::METODOS_PAGO,
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

        $metodoPago = strtoupper(trim((string)($data['metodo_pago'] ?? 'EFECTIVO')));
        if (!in_array($metodoPago, Egreso::METODOS_PAGO, true)) {
            Response::error('El método de pago del egreso no es válido', 422);
        }
        $data['metodo_pago'] = $metodoPago;

        $userId = $this->session->getUserId();
        if ($userId === null) {
            Response::error('La sesión no tiene un usuario válido.', 401);
            return;
        }

        try {
            $id = $this->model->crear($data, $userId);
            Response::success(['id_egreso' => $id], 'Egreso registrado');
        } catch (DomainException $e) {
            Response::error($e->getMessage(), 409);
        } catch (InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        }
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
