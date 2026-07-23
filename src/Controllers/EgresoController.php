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
            'meses' => $this->model->meses(),
        ]);
    }

    public function listar(Request $r): void
    {
        Response::success($this->model->listar((int)$r->get('page',1), 25, $r->get('mes','')));
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        if (empty($data['id_tipo_egreso']) || empty($data['valor'])) {
            Response::error('Tipo de egreso y valor requeridos', 422);
        }
        $this->model->crear($data, $this->session->getUserId());
        Response::success(null, 'Egreso registrado');
    }
}
