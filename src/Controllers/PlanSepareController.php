<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\PlanSepare;

class PlanSepareController
{
    private PlanSepare $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new PlanSepare();
        $this->session = Session::getInstance();
    }

    /**
     * Vista principal de Plan Separe
     */
    public function index(Request $r): string
    {
        $view = new View();
        return $view->render('plan-separe/index', [
            'title' => 'Plan Separe',
        ]);
    }

    /**
     * API: Listar planes separe paginados
     */
    public function listar(Request $r): void
    {
        $page = (int)$r->get('page', 1);
        Response::success($this->model->listar($page));
    }

    /**
     * API: Crear un nuevo plan separe
     */
    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['id_cliente'])) {
            Response::error('Debe seleccionar un cliente', 422);
        }
        if (empty($data['valor_total']) || (float)$data['valor_total'] <= 0) {
            Response::error('El valor total debe ser mayor a cero', 422);
        }

        try {
            $id = $this->model->crear($data, $this->session->getUserId());
            Response::success(['id' => $id], 'Plan Separe creado correctamente');
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            Response::error('Error al crear el Plan Separe: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Obtener un plan separe
     */
    public function obtener(Request $r): void
    {
        $id = (int)$r->param('id');
        $plan = $this->model->obtenerPorId($id);
        if (!$plan) {
            Response::error('Plan Separe no encontrado', 404);
        }
        Response::success($plan);
    }

    /**
     * API: Obtener plan separe con detalle de productos y abonos
     */
    public function obtenerConDetalle(Request $r): void
    {
        $id = (int)$r->param('id');
        $plan = $this->model->obtenerConDetalle($id);
        if (!$plan) {
            Response::error('Plan Separe no encontrado', 404);
        }
        Response::success($plan);
    }

    /**
     * API: Buscar productos para agregar al plan separe
     */
    public function buscarProductos(Request $r): void
    {
        $q = $r->get('q', '');
        $db = \SIG\Core\Database::getInstance();
        $productos = $db->select(
            "SELECT p.id_producto, p.codigo, p.descripcion, p.valor_venta, p.codigo_barras_1,
                    COALESCE(i.unidad, 0) AS stock
             FROM vb_productos p
             LEFT JOIN vb_inventario i ON p.id_producto = i.id_producto
             WHERE p.activo = 1
               AND (p.descripcion LIKE :q1 OR p.codigo LIKE :q2
                    OR p.codigo_barras_1 LIKE :q3 OR p.codigo_barras_2 LIKE :q4
                    OR p.codigo_barras_3 LIKE :q5)
             ORDER BY p.descripcion ASC LIMIT 20",
            ['q1' => "%{$q}%", 'q2' => "%{$q}%", 'q3' => "%{$q}%", 'q4' => "%{$q}%", 'q5' => "%{$q}%"]
        );
        Response::success($productos);
    }

    /**
     * API: Registrar abono
     */
    public function abonar(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_plan_separe'] ?? 0);
        $valor = (float)($data['valor'] ?? 0);

        if ($id <= 0) {
            Response::error('Plan Separe inválido', 422);
        }
        if ($valor <= 0) {
            Response::error('El valor del abono debe ser mayor a cero', 422);
        }

        try {
            $this->model->abonar($id, $valor, $this->session->getUserId());
            Response::success(null, 'Abono registrado correctamente');
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Error al registrar abono: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Cancelar plan separe
     */
    public function cancelar(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_plan_separe'] ?? 0);

        if ($id <= 0) {
            Response::error('Plan Separe inválido', 422);
        }

        try {
            $this->model->cancelar($id);
            Response::success(null, 'Plan Separe cancelado');
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Error al cancelar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Obtener abonos de un plan separe
     */
    public function abonos(Request $r): void
    {
        $id = (int)$r->param('id');
        Response::success($this->model->abonos($id));
    }
}
