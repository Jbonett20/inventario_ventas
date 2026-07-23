<?php
/**
 * API de Facturación Electrónica
 * Endpoint para enviar facturas a la DIAN 
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use SIG\Core\Database;
use SIG\Core\Session;
use SIG\Services\FacturacionElectronicaService;

// Iniciar sesión
$session = Session::getInstance();
$session->start();

// Verificar autenticación
if (!$session->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? ($input['action'] ?? 'enviar');

    $feService = new FacturacionElectronicaService();

    switch ($action) {
        case 'enviar':
            $idFactura = (int)($input['id_factura'] ?? 0);
            if ($idFactura <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de factura requerido']);
                exit;
            }
            $result = $feService->enviarFactura($idFactura);
            echo json_encode($result);
            break;

        case 'test':
            $result = $feService->testConexion();
            echo json_encode($result);
            break;

        case 'sincronizar':
            $idProducto = (int)($input['id_producto'] ?? 0);
            $result = $feService->sincronizarProducto($idProducto);
            echo json_encode($result);
            break;

        case 'config':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $feService->guardarConfiguracion($input);
                echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
            } else {
                echo json_encode(['success' => true, 'data' => $feService->getConfiguracion()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
