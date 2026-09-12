<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Core\Database;
use SIG\Models\Factura;
use SIG\Models\Producto;
use SIG\Middleware\RoleMiddleware;

class FacturaController
{
    private Factura $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Factura();
        $this->session = Session::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();

        // Si la URL es /facturacion/historial, mostrar historial
        if ($r->getUri() === '/facturacion/historial' || $r->get('tab') === 'historial') {
            return $view->render('facturacion/historial', [
                'title' => 'Historial de Facturas',
            ]);
        }

        // Obtener base diaria del usuario para hoy
        $db = Database::getInstance();
        $userId = $this->session->getUserId();
        $baseDiaria = $db->fetchOne(
            "SELECT id_base, base FROM vb_base_diaria WHERE id_usuario = :uid AND fecha = CURDATE()",
            ['uid' => $userId]
        );

        // Otherwise show the POS
        $prodModel = new Producto();
        // Verificar si la empresa tiene habilitada la facturación electrónica en POS
        $empresa = $db->fetchOne("SELECT mostrar_fe FROM vb_empresa WHERE id_empresa = 1");
        $mostrarFE = ($empresa['mostrar_fe'] ?? 1) && RoleMiddleware::hasPermission('facturacion.electronica');

        // Datos del usuario logueado (cobrador) para la opción "yo mismo"
        $usuario = $db->fetchOne(
            "SELECT nombre_usuario, COALESCE(NULLIF(nombre_completo, ''), nombre_usuario) AS nombre_completo
             FROM vb_usuarios WHERE id_usuario = :id",
            ['id' => $userId]
        );

        return $view->render('facturacion/index', [
            'title'       => 'Facturación',
            'productos'   => $prodModel->buscar(''),
            'catalogos'   => $prodModel->obtenerCatalogos(),
            'isAdmin'     => RoleMiddleware::isAdmin(),
            'isCajero'    => RoleMiddleware::hasRole('cajero'),
            'puedeFE'     => $mostrarFE,
            'baseDiaria'  => $baseDiaria,
            'userId'      => $userId,
            'usuarioNombre' => $usuario['nombre_completo'] ?? $this->session->get('username'),
            'username'    => $this->session->get('username'),
            'userTipo'    => $this->session->getUserType(),
            'userImagen'  => $this->session->get('user_imagen'),
        ]);
    }

    /**
     * API: Registrar base diaria
     */
    public function guardarBaseDiaria(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $base = (float)($data['base'] ?? 0);

        if ($base < 0) {
            Response::error('La base no puede ser negativa');
        }

        $db = Database::getInstance();
        $userId = $this->session->getUserId();

        // Verificar si ya existe base para hoy
        $existe = $db->fetchOne(
            "SELECT id_base FROM vb_base_diaria WHERE id_usuario = :uid AND fecha = CURDATE()",
            ['uid' => $userId]
        );

        try {
            if ($existe) {
                $db->executeAffected(
                    "UPDATE vb_base_diaria SET base = :base WHERE id_base = :id",
                    ['base' => $base, 'id' => $existe['id_base']]
                );
                Response::success(['id_base' => $existe['id_base'], 'base' => $base], 'Base diaria actualizada');
            } else {
                $id = $db->insert(
                    "INSERT INTO vb_base_diaria (base, id_usuario, fecha) VALUES (:base, :uid, CURDATE())",
                    ['base' => $base, 'uid' => $userId]
                );
                Response::success(['id_base' => $id, 'base' => $base], 'Base diaria registrada');
            }
        } catch (\Exception $e) {
            Response::error('Error al guardar base diaria: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Obtener base diaria del usuario
     */
    public function obtenerBaseDiaria(Request $r): void
    {
        $db = Database::getInstance();
        $userId = $this->session->getUserId();

        $base = $db->fetchOne(
            "SELECT id_base, base, fecha FROM vb_base_diaria WHERE id_usuario = :uid AND fecha = CURDATE()",
            ['uid' => $userId]
        );

        Response::success($base ?: null);
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['detalles']) || !is_array($data['detalles'])) {
            Response::error('Debe agregar al menos un producto', 422);
        }

        try {
            $result = $this->model->crear(
                [
                    'id_cliente'   => (int)($data['id_cliente'] ?? 1),
                    'tipo_pago'    => $data['tipo_pago'] ?? 'EFECTIVO',
                    'descuento'    => (float)($data['descuento'] ?? 0),
                    'pago_recibido'=> (float)($data['pago_recibido'] ?? 0),
                    'tipo'         => $data['tipo'] ?? 'NORMAL',
                    'id_vendedor_registrado' => (int)($data['id_vendedor_registrado'] ?? 0) ?: null,
                ],
                $data['detalles'],
                $this->session->getUserId()
            );

            // Si es factura electrónica, enviar a DIAN
            if (($data['tipo'] ?? 'NORMAL') === 'ELECTRONICA') {
                $result['fe_pendiente'] = true;
            }

            Response::success($result, 'Factura creada exitosamente');

        } catch (\Exception $e) {
            Response::error('Error al crear factura: ' . $e->getMessage(), 500);
        }
    }

    public function listar(Request $r): void
    {
        $filtros = [
            'page'      => (int)$r->get('page', 1),
            'perPage'   => (int)$r->get('perPage', 25),
            'search'    => $r->get('search', ''),
            'tipo'      => $r->get('tipo', ''),
            'tipo_pago' => $r->get('tipo_pago', ''),
            'estado'    => $r->get('estado', ''),
            'fecha_ini' => $r->get('fecha_ini', ''),
            'fecha_fin' => $r->get('fecha_fin', ''),
            'codigo'    => $r->get('codigo', ''),
            'id_cliente'=> $r->get('id_cliente', ''),
        ];
        Response::success($this->model->listar($filtros));
    }

    public function obtener(Request $r): void
    {
        $id = (int)$r->param('id');
        $fac = $this->model->obtenerConDetalle($id);
        if (!$fac) { Response::error('Factura no encontrada', 404); }
        Response::success($fac);
    }

    public function pdf(Request $r): void
    {
        $id = (int)$r->param('id');
        $factura = $this->model->obtenerConDetalle($id);

        if (!$factura) {
            Response::error('Factura no encontrada', 404);
        }

        // Datos de la empresa
        $config = require __DIR__ . '/../config/app.php';

        // Generar HTML de la factura (formato ticket 80mm)
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<style>';
        $html .= 'body{font-family:"Courier New",monospace;font-size:9px;color:#000;margin:0;padding:0;width:72mm}';
        $html .= '.center{text-align:center}';
        $html .= '.header{border-bottom:1px dashed #000;padding-bottom:6px;margin-bottom:6px}';
        $html .= '.header h1{font-size:12px;margin:0;text-transform:uppercase}';
        $html .= '.header p{margin:1px 0;font-size:8px}';
        $html .= '.divider{border-bottom:1px dashed #000;margin:4px 0}';
        $html .= 'table.items{width:100%;border-collapse:collapse}';
        $html .= 'table.items td{padding:1px 0;font-size:9px}';
        $html .= 'table.items .prod-desc{font-size:8px}';
        $html .= '.totals{width:100%;margin-top:4px}';
        $html .= '.totals td{padding:1px 0;font-size:9px}';
        $html .= '.total-row td{font-weight:bold;font-size:11px}';
        $html .= '.footer{text-align:center;font-size:7px;margin-top:8px;padding-top:4px;border-top:1px dashed #000}';
        $html .= '.cufe-block{font-size:7px;word-break:break-all;margin-top:6px;padding:4px;border-top:1px dashed #000}';
        $html .= '</style></head><body>';

        $empresa = htmlspecialchars($factura['empresa_nombre'] ?? 'SISTEMA INTEGRAL DE GESTIÓN');
        $nit = htmlspecialchars($factura['empresa_nit'] ?? '');

        $html .= '<div class="header center">';
        $html .= '<h1>' . $empresa . '</h1>';
        if ($nit) $html .= '<p>NIT: ' . $nit . '</p>';
        $html .= '<p style="margin-top:2px"><strong>FACTURA No. ' . htmlspecialchars($factura['codigo'] ?? '') . '</strong></p>';
        $html .= '<p>' . htmlspecialchars($factura['fecha'] ?? '') . ' ' . htmlspecialchars(substr($factura['hora'] ?? '', 0, 5)) . ' | ' . htmlspecialchars($factura['tipo'] ?? '') . '</p>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<strong>Cliente:</strong> ' . htmlspecialchars($factura['cliente_nombre'] ?? 'CLIENTE GENERAL') . '<br>';
        $html .= '<strong>Doc:</strong> ' . htmlspecialchars($factura['cliente_documento'] ?? '') . '<br>';
        if (!empty($factura['cliente_direccion'])) {
            $html .= '<strong>Dir:</strong> ' . htmlspecialchars($factura['cliente_direccion']) . '<br>';
        }
        $html .= '<strong>Vendedor:</strong> ' . htmlspecialchars($factura['vendedor_nombre'] ?? '') . '<br>';
        $html .= '<strong>Pago:</strong> ' . htmlspecialchars($factura['tipo_pago'] ?? '');
        $html .= '</div>';

        $html .= '<div class="divider"></div>';

        // Tabla de productos
        $html .= '<table class="items">';
        $detalles = $factura['detalles'] ?? [];
        foreach ($detalles as $i => $det) {
            $cantidad = '';
            $cantDec = (float)($det['cantidad_decimal'] ?? 0);
            if ($cantDec > 0) {
                $cantidad = number_format($cantDec, 2, ',', '.') . ' und';
            } else {
                $cantidad = (int)($det['cantidad_unidad'] ?? 0);
                if (($det['cantidad_fraccion'] ?? 0) > 0) {
                    $cantidad .= '+' . (int)$det['cantidad_fraccion'] . '/u';
                }
            }
            $total = number_format($det['total'] ?? 0, 0, ',', '.');
            $pUnit = number_format($det['precio_unitario'] ?? 0, 0, ',', '.');
            $html .= '<tr>';
            $html .= '<td style="width:20px">' . ($i + 1) . '</td>';
            $html .= '<td class="prod-desc">' . htmlspecialchars($det['descripcion'] ?? '') . '</td>';
            $html .= '<td style="text-align:right;width:55px">$' . $total . '</td>';
            $html .= '</tr>';
            $html .= '<tr><td></td><td style="font-size:7px;color:#555">' . $cantidad . ' x $' . $pUnit . ' | IVA ' . ($det['iva'] ?? 0) . '%</td><td></td></tr>';
        }
        $html .= '</table>';

        $html .= '<div class="divider"></div>';

        // Totales
        $html .= '<table class="totals">';
        $html .= '<tr><td>Subtotal:</td><td style="text-align:right">$' . number_format($factura['subtotal'] ?? 0, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td>IVA:</td><td style="text-align:right">$' . number_format($factura['total_iva'] ?? 0, 0, ',', '.') . '</td></tr>';
        if (($factura['descuento'] ?? 0) > 0) {
            $html .= '<tr><td style="color:#c00">Descuento:</td><td style="text-align:right;color:#c00">-$' . number_format($factura['descuento'], 0, ',', '.') . '</td></tr>';
        }
        $html .= '<tr class="total-row"><td>TOTAL:</td><td style="text-align:right">$' . number_format($factura['total'] ?? 0, 0, ',', '.') . '</td></tr>';
        if (($factura['pago_recibido'] ?? 0) > 0) {
            $html .= '<tr><td>Recibido:</td><td style="text-align:right">$' . number_format($factura['pago_recibido'] ?? 0, 0, ',', '.') . '</td></tr>';
            $html .= '<tr><td>Cambio:</td><td style="text-align:right">$' . number_format($factura['cambio'] ?? 0, 0, ',', '.') . '</td></tr>';
        }
        $html .= '</table>';

        // CUFE / Notas
        if (!empty($factura['cufe'])) {
            $html .= '<div class="cufe-block center">';
            $html .= '<strong>CUFE</strong><br>' . htmlspecialchars($factura['cufe']);
            $html .= '</div>';
        }

        $html .= '<div class="footer">';
        $html .= $empresa . ' | NIT: ' . ($nit ?: 'N/A') . '<br>';
        $html .= 'Documento generado electrónicamente<br>' . date('d/m/Y H:i');
        $html .= '</div>';

        $html .= '</body></html>';

        // Generar PDF con mpdf (tamaño ticket 80mm)
        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => [80, 297],
                'margin_left'   => 2,
                'margin_right'  => 2,
                'margin_top'    => 2,
                'margin_bottom' => 2,
                'tempDir'       => sys_get_temp_dir() . '/mpdf',
            ]);
            $mpdf->SetTitle('Factura #' . ($factura['codigo'] ?? ''));
            $mpdf->WriteHTML($html);
            $mpdf->Output('Factura_' . ($factura['codigo'] ?? '') . '.pdf', 'I');
        } catch (\Exception $e) {
            Response::error('Error al generar PDF: ' . $e->getMessage(), 500);
        }
        exit;
    }

    /**
     * POST /api/facturacion-electronica/enviar
     * Emite la factura electrónica ante la DIAN vía el servicio de FE
     */
    public function enviarElectronica(Request $r): void
    {
        if (!RoleMiddleware::hasPermission('facturacion.electronica')) {
            Response::error('No tiene permisos para emitir facturas electrónicas', 403);
        }

        $data = $r->json() ?: $r->all();
        $idFactura = (int)($data['id_factura'] ?? 0);

        if ($idFactura <= 0) {
            Response::error('Debe indicar la factura a enviar', 422);
        }

        try {
            $service = new \SIG\Services\FacturacionElectronicaService();

            // Verificar configuración mínima antes de intentar conectar
            $cfg = $service->getConfiguracion();
            $requeridos = [
                'nit'         => 'NIT',
                'software_id' => 'Software ID',
                'llave_api'   => 'Llave API',
                'usuario_api' => 'Usuario API',
            ];
            $faltantes = [];
            foreach ($requeridos as $clave => $etiqueta) {
                if (empty($cfg[$clave])) {
                    $faltantes[] = $etiqueta;
                }
            }

            if (!empty($faltantes)) {
                Response::error(
                    'Falta configurar la facturación electrónica: ' . implode(', ', $faltantes) .
                    '. Complete los datos en Configuración → Facturación Electrónica.',
                    422
                );
            }

            $res = $service->enviarFactura($idFactura);

            if (empty($res['success'])) {
                Response::error($res['message'] ?? 'Error al emitir la factura electrónica', 422);
            }

            Response::success([
                'id_factura' => $idFactura,
                'cufe'       => $res['cufe'] ?? null,
                'qr'         => $res['qr'] ?? null,
            ], $res['message'] ?? 'Factura electrónica emitida exitosamente');
        } catch (\Exception $e) {
            Response::error('Error al emitir la factura electrónica: ' . $e->getMessage(), 500);
        }
    }

    public function anular(Request $r): void
    {
        $id = (int)(($r->json() ?: $r->all())['id_factura'] ?? 0);
        if ($id <= 0) Response::error('ID inválido');

        try {
            $this->model->anular($id);
            Response::success(null, 'Factura anulada. Productos devueltos al inventario.');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
