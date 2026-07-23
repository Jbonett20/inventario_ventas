<?php
/**
 * SISTEMA INTEGRAL DE GESTIÓN (SIG)
 * Front Controller - Punto de entrada único
 * 
 * Todas las solicitudes pasan por aquí gracias al .htaccess
 * 
 * @version 1.0.0
 * @date    2026-07-23
 */

// ============================================================
// 1. CONFIGURACIÓN INICIAL
// ============================================================

// Zona horaria Colombia
date_default_timezone_set('America/Bogota');

// Forzar Content-Type UTF-8 para todas las respuestas
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Reportar errores solo en desarrollo
$env = getenv('APP_ENV') ?: 'development';
if ($env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================
// 2. AUTOLOADER (Composer o manual)
// ============================================================

$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',         // Composer
    __DIR__ . '/../src/Core/Autoloader.php',     // Manual fallback
];

$autoloadLoaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadLoaded = true;
        break;
    }
}

if (!$autoloadLoaded) {
    die('Error: No se encontró el autoloader. Ejecute "composer install"');
}

// ============================================================
// 3. CARGAR VARIABLES DE ENTORNO
// ============================================================

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// ============================================================
// 4. INICIALIZAR COMPONENTES CORE
// ============================================================

use SIG\Core\Router;
use SIG\Core\Request;
use SIG\Core\Session;
use SIG\Core\Response;

// Iniciar sesión segura
$session = Session::getInstance();
$session->start();

// Crear request
$request = new Request();

// Crear router
$router = new Router();

// Pasar basePath a las vistas globalmente
$basePath = $request->getBasePath();
\SIG\Core\View::setGlobal('basePath', $basePath);

// ============================================================
// 5. REGISTRAR RUTAS
// ============================================================

// ---- Autenticación ----
$router->get('/login',                'AuthController@loginForm');
$router->post('/login',               'AuthController@login');
$router->get('/logout',               'AuthController@logout');
$router->get('/api/auth/check',       'AuthController@checkSession');
$router->post('/api/auth/change-password', 'AuthController@changePassword', ['SIG\Middleware\AuthMiddleware']);

// ---- Dashboard ----
$router->get('/',                     'DashboardController@index', ['SIG\Middleware\AuthMiddleware']);

// ---- Productos ----
$router->get('/productos',            'ProductoController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/productos/listar',     'ProductoController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->get('/productos/buscar',     'ProductoController@search', ['SIG\Middleware\AuthMiddleware']);
$router->get('/productos/obtener/{id}','ProductoController@obtener', ['SIG\Middleware\AuthMiddleware']);
$router->post('/productos/guardar',   'ProductoController@store', ['SIG\Middleware\AuthMiddleware']);
$router->post('/productos/eliminar',  'ProductoController@delete', ['SIG\Middleware\AuthMiddleware']);
$router->get('/productos/catalogos',  'ProductoController@catalogos', ['SIG\Middleware\AuthMiddleware']);

// ---- Clientes ----
$router->get('/clientes',             'ClienteController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/clientes/listar',      'ClienteController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->get('/clientes/buscar',      'ClienteController@search', ['SIG\Middleware\AuthMiddleware']);
$router->get('/clientes/obtener/{id}','ClienteController@obtener', ['SIG\Middleware\AuthMiddleware']);
$router->post('/clientes/guardar',    'ClienteController@store', ['SIG\Middleware\AuthMiddleware']);
$router->post('/clientes/eliminar',   'ClienteController@delete', ['SIG\Middleware\AuthMiddleware']);

// ---- Proveedores ----
$router->get('/proveedores',          'ProveedorController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/proveedores/listar',   'ProveedorController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->post('/proveedores/guardar', 'ProveedorController@store', ['SIG\Middleware\AuthMiddleware']);
$router->post('/proveedores/eliminar','ProveedorController@delete', ['SIG\Middleware\AuthMiddleware']);

// ---- Inventario ----
$router->get('/inventario',           'InventarioController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/inventario/listar',    'InventarioController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->post('/inventario/ajustar',  'InventarioController@adjust', ['SIG\Middleware\AuthMiddleware']);
$router->get('/inventario/resumen',   'InventarioController@resumen', ['SIG\Middleware\AuthMiddleware']);
$router->get('/inventario/stock-bajo','InventarioController@stockBajo', ['SIG\Middleware\AuthMiddleware']);
$router->get('/inventario/movimientos/{id}','InventarioController@movimientos', ['SIG\Middleware\AuthMiddleware']);

// ---- Ingresos ----
$router->get('/ingresos',             'IngresoController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/ingresos/listar',      'IngresoController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->post('/ingresos/guardar',    'IngresoController@store', ['SIG\Middleware\AuthMiddleware']);
$router->get('/ingresos/buscar-productos','IngresoController@buscarProductos', ['SIG\Middleware\AuthMiddleware']);

// ---- Facturación ----
$router->get('/facturacion/historial','FacturaController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/facturacion',          'FacturaController@index', ['SIG\Middleware\AuthMiddleware']);
$router->post('/facturacion/guardar', 'FacturaController@store', ['SIG\Middleware\AuthMiddleware']);
$router->get('/facturacion/listar',   'FacturaController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->get('/facturacion/obtener/{id}','FacturaController@obtener', ['SIG\Middleware\AuthMiddleware']);
$router->post('/facturacion/anular',  'FacturaController@anular', ['SIG\Middleware\AuthMiddleware']);

// ---- Facturación Electrónica (API directa) ----
// La ruta /api/facturacion-electronica se maneja desde app/api/

// ---- Créditos ----
$router->get('/creditos',             'CreditoController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/creditos/listar',      'CreditoController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->post('/creditos/guardar',    'CreditoController@store', ['SIG\Middleware\AuthMiddleware']);
$router->post('/creditos/abonar',     'CreditoController@abonar', ['SIG\Middleware\AuthMiddleware']);
$router->get('/creditos/abonos/{id}', 'CreditoController@abonos', ['SIG\Middleware\AuthMiddleware']);

// ---- Egresos ----
$router->get('/egresos',              'EgresoController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/egresos/listar',       'EgresoController@listar', ['SIG\Middleware\AuthMiddleware']);
$router->post('/egresos/guardar',     'EgresoController@store', ['SIG\Middleware\AuthMiddleware']);

// ---- Reportes ----
$router->get('/reportes',             'ReporteController@index', ['SIG\Middleware\AuthMiddleware']);
$router->get('/reportes/ventas',      'ReporteController@ventas', ['SIG\Middleware\AuthMiddleware']);
$router->get('/reportes/top-productos','ReporteController@topProductos', ['SIG\Middleware\AuthMiddleware']);
$router->get('/reportes/metodos-pago','ReporteController@metodosPago', ['SIG\Middleware\AuthMiddleware']);
$router->get('/reportes/ventas-diarias','ReporteController@ventasDiarias', ['SIG\Middleware\AuthMiddleware']);
$router->get('/configuracion',        'ConfiguracionController@index', ['SIG\Middleware\AuthMiddleware']);
$router->post('/configuracion/empresa/guardar', 'ConfiguracionController@guardarEmpresa', ['SIG\Middleware\AuthMiddleware']);
$router->post('/configuracion/fe/guardar', 'ConfiguracionController@guardarFE', ['SIG\Middleware\AuthMiddleware']);
$router->post('/configuracion/fe/test', 'ConfiguracionController@testFE', ['SIG\Middleware\AuthMiddleware']);
$router->post('/configuracion/rangos/guardar', 'ConfiguracionController@guardarRango', ['SIG\Middleware\AuthMiddleware']);
$router->post('/configuracion/usuarios/guardar', 'ConfiguracionController@guardarUsuario', ['SIG\Middleware\AuthMiddleware']);

// ---- Exportación CSV/PDF ----
$router->get('/exportar/csv', 'ExportController@csv', ['SIG\Middleware\AuthMiddleware']);
$router->get('/exportar/pdf', 'ExportController@pdf', ['SIG\Middleware\AuthMiddleware']);

// ---- API Dashboard ----
$router->get('/api/dashboard/resumen','DashboardController@resumen', ['SIG\Middleware\AuthMiddleware']);

// ============================================================
// 6. EJECUTAR ROUTER
// ============================================================

try {
    $output = $router->resolve($request);
    
    if ($output !== null) {
        echo $output;
    }
} catch (\Exception $e) {
    if ($env === 'development') {
        Response::error($e->getMessage(), 500, [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    } else {
        // Loggear el error
        error_log('SIG Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        Response::error('Error interno del servidor', 500);
    }
}
