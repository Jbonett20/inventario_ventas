<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Core\Database;

/**
 * Controlador del Dashboard
 * 
 * Página principal con resumen de ventas, inventario y KPIs.
 */
class DashboardController
{
    private Session $session;
    private Database $db;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->db      = Database::getInstance();
    }

    /**
     * Mostrar el dashboard principal
     */
    public function index(Request $request): string
    {
        $view = new View();

        $data = [
            'title'      => 'Dashboard',
            'username'   => $this->session->get('username'),
            'userTipo'   => $this->session->getUserType(),
            'userImagen' => $this->session->get('user_imagen'),
        ];

        // Según el rol, mostrar vista diferente
        $viewName = 'dashboard/index';
        if ($this->session->getUserType() === 2) {
            $viewName = 'inventario/index';
        }

        return $view->render($viewName, $data);
    }

    /**
     * API: Resumen de datos para el dashboard
     */
    public function resumen(Request $request): void
    {
        $hoy = date('Y-m-d');

        $ventasHoy = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total), 0) AS total FROM vb_facturas WHERE fecha = :hoy AND estado = 'ACTIVA'",
            ['hoy' => $hoy]
        );

        $totalProductos = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_productos WHERE activo = 1");
        $totalClientes  = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_clientes WHERE activo = 1");

        $capitalTotal = $this->db->fetchOne(
            "SELECT COALESCE(SUM(p.valor_venta * inv.unidad), 0) AS total 
             FROM vb_inventario inv 
             JOIN vb_productos p ON inv.id_producto = p.id_producto"
        );

        Response::success([
            'ventas_hoy'      => (float)($ventasHoy['total'] ?? 0),
            'total_productos' => (int)($totalProductos['total'] ?? 0),
            'total_clientes'  => (int)($totalClientes['total'] ?? 0),
            'capital_total'   => (float)($capitalTotal['total'] ?? 0),
        ]);
    }

    /**
     * API: Últimas ventas registradas
     * GET /api/dashboard/ultimas-ventas?limite=5
     */
    public function ultimasVentas(Request $request): void
    {
        $limite = (int)$request->get('limite', 5);
        $limite = ($limite > 0 && $limite <= 20) ? $limite : 5;

        $facturaModel = new \SIG\Models\Factura();
        $resultado = $facturaModel->listar(['page' => 1, 'perPage' => $limite]);

        $ventas = [];
        foreach ($resultado['data'] as $f) {
            $ventas[] = [
                'id_factura' => (int)$f['id_factura'],
                'codigo'     => $f['codigo'],
                'cliente'    => $f['cliente_nombre'] ?: 'Consumidor final',
                'total'      => (float)$f['total'],
                'tipo_pago'  => $f['tipo_pago'],
                'tipo'       => $f['tipo'],
                'fecha'      => $f['fecha'],
                'hora'       => $f['hora'],
                'estado'     => $f['estado'],
            ];
        }

        Response::success(['ventas' => $ventas, 'total' => $resultado['total']]);
    }
}
