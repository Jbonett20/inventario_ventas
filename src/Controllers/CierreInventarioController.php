<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Middleware\RoleMiddleware;
use SIG\Models\CierreInventario;

/**
 * Cierre de inventario por periodos (diario, semanal, quincenal, mensual...).
 *
 * Se usa desde la pestaña "Cierre de Inventario" del módulo Inventario.
 * El cierre es información contable: solo admin/superadmin pueden cerrarlo,
 * anularlo o ejecutar uno programado.
 */
class CierreInventarioController
{
    private CierreInventario $model;

    public function __construct()
    {
        $this->model = new CierreInventario();
    }

    private function puedeVer(): bool
    {
        return RoleMiddleware::hasPermission('inventario.ver_cierre');
    }

    private function puedeCerrar(): bool
    {
        return RoleMiddleware::hasPermission('inventario.cerrar');
    }

    /**
     * Historial de cierres
     * GET /inventario/cierres?page=1&estado=
     */
    public function listar(Request $r): void
    {
        if (!$this->puedeVer()) {
            Response::error('No tiene permiso para ver los cierres de inventario', 403);
        }

        Response::success($this->model->listar(
            (int)$r->get('page', 1),
            25,
            (string)$r->get('estado', '')
        ));
    }

    /**
     * Rango sugerido para el próximo cierre según el período
     * GET /inventario/cierres/rango?periodo=SEMANAL
     */
    public function rango(Request $r): void
    {
        if (!$this->puedeVer()) {
            Response::error('No tiene permiso', 403);
        }

        Response::success($this->model->rangoSugerido((string)$r->get('periodo', 'DIARIO')));
    }

    /**
     * Previsualizar el cierre (no guarda nada)
     * GET /inventario/cierres/calcular?desde=&hasta=
     */
    public function calcular(Request $r): void
    {
        if (!$this->puedeVer()) {
            Response::error('No tiene permiso', 403);
        }

        try {
            $desde = (string)$r->get('desde', '');
            $hasta = (string)$r->get('hasta', '');

            $calc  = $this->model->calcular($desde, $hasta);
            $solape = $this->model->buscarSolape($desde, $hasta);

            Response::success([
                'calculo'       => $calc,
                'solape'        => $solape,
                'puede_cerrar'  => $this->puedeCerrar(),
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Ejecutar el cierre de un período
     * POST /inventario/cierres/cerrar
     */
    public function cerrar(Request $r): void
    {
        if (!$this->puedeCerrar()) {
            Response::error('Solo un administrador puede cerrar el inventario', 403);
        }

        $data = $r->json() ?: $r->all();

        try {
            $res = $this->model->cerrar(
                (string)($data['desde'] ?? ''),
                (string)($data['hasta'] ?? ''),
                (string)($data['periodo'] ?? 'DIARIO'),
                \SIG\Core\Session::getInstance()->getUserId(),
                (string)($data['observacion'] ?? '')
            );

            $calc = $res['calculo'];
            Response::success($res, 'Cierre registrado | Ventas: $' . number_format($calc['ventas']['total'], 0, ',', '.')
                . ' | Utilidad neta: $' . number_format($calc['utilidad_neta'], 0, ',', '.'));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Dejar un cierre agendado
     * POST /inventario/cierres/programar
     */
    public function programar(Request $r): void
    {
        if (!$this->puedeCerrar()) {
            Response::error('Solo un administrador puede programar cierres', 403);
        }

        $data = $r->json() ?: $r->all();

        try {
            $id = $this->model->programar(
                (string)($data['desde'] ?? ''),
                (string)($data['hasta'] ?? ''),
                (string)($data['periodo'] ?? 'DIARIO'),
                \SIG\Core\Session::getInstance()->getUserId(),
                (string)($data['observacion'] ?? '')
            );

            Response::success(['id_cierre' => $id], 'Cierre programado');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Ejecutar un cierre programado que ya venció
     * POST /inventario/cierres/ejecutar
     */
    public function ejecutar(Request $r): void
    {
        if (!$this->puedeCerrar()) {
            Response::error('Solo un administrador puede ejecutar cierres', 403);
        }

        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_cierre'] ?? 0);

        if ($id <= 0) {
            Response::error('Cierre no válido', 422);
        }

        try {
            $res  = $this->model->ejecutarProgramado($id, \SIG\Core\Session::getInstance()->getUserId());
            $calc = $res['calculo'];
            Response::success($res, 'Cierre ejecutado | Ventas: $' . number_format($calc['ventas']['total'], 0, ',', '.')
                . ' | Utilidad neta: $' . number_format($calc['utilidad_neta'], 0, ',', '.'));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Anular un cierre
     * POST /inventario/cierres/anular
     */
    public function anular(Request $r): void
    {
        if (!$this->puedeCerrar()) {
            Response::error('Solo un administrador puede anular cierres', 403);
        }

        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_cierre'] ?? 0);

        if ($id <= 0) {
            Response::error('Cierre no válido', 422);
        }

        try {
            $this->model->anular($id, \SIG\Core\Session::getInstance()->getUserId(), (string)($data['motivo'] ?? ''));
            Response::success(null, 'Cierre anulado');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Detalle del período de un cierre
     * GET /inventario/cierres/detalle?id_cierre=
     */
    public function detalle(Request $r): void
    {
        if (!$this->puedeVer()) {
            Response::error('No tiene permiso', 403);
        }

        $id = (int)$r->get('id_cierre', 0);
        if ($id <= 0) {
            Response::error('Cierre no válido', 422);
        }

        try {
            Response::success($this->model->detallePeriodo($id));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }
}
