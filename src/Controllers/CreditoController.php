<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Credito;

class CreditoController
{
    private Credito $model;
    private Session $session;
    public function __construct() { $this->model = new Credito(); $this->session = Session::getInstance(); }

    public function index(Request $r): string
    {
        return (new View())->render('creditos/index', ['title' => 'Créditos']);
    }

    public function listar(Request $r): void
    {
        Response::success($this->model->listar((int)$r->get('page', 1)));
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        if (empty($data['id_cliente']) || empty($data['valor_total'])) {
            Response::error('Cliente y valor total requeridos', 422);
        }
        $id = $this->model->crear($data, $this->session->getUserId());
        Response::success(['id' => $id], 'Crédito creado');
    }

    public function abonar(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id = (int)($data['id_credito'] ?? 0);
        $valor = (float)($data['valor'] ?? 0);
        if ($id <= 0 || $valor <= 0) Response::error('Datos inválidos', 422);
        $this->model->abonar($id, $valor, $this->session->getUserId());
        Response::success(null, 'Abono registrado');
    }

    public function abonos(Request $r): void
    {
        $id = (int)$r->param('id');
        Response::success($this->model->abonos($id));
    }
}
