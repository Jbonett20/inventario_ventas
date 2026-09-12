<?php
/**
 * Configuración General de la Aplicación
 */
return [
    'name'       => 'Sistema Integral de Gestión',
    'version'    => '1.0.0',
    'env'        => getenv('APP_ENV') ?: 'development',
    'debug'      => filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
    'url'        => getenv('APP_URL') ?: 'http://localhost/misproyectos/entorno/inventario_ventas/public',
    'timezone'   => 'America/Bogota',
    'locale'     => 'es_CO',
    
    // Sesión
    'session'    => [
        'name'            => getenv('SESSION_NAME') ?: 'SIGSESSID',
        'timeout'         => (int)(getenv('SESSION_TIMEOUT') ?: 30), // minutos
        'secure'          => false, // true en producción con HTTPS
        'http_only'       => true,
        'same_site'       => 'Lax',
    ],
    
    // Seguridad
    'security'   => [
        'csrf_expiry'       => (int)(getenv('CSRF_EXPIRY') ?: 3600),
        'max_login_attempts'=> (int)(getenv('MAX_LOGIN_ATTEMPTS') ?: 5),
        'lockout_time'      => (int)(getenv('LOCKOUT_TIME') ?: 900), // 15 min
    ],
    
    // Rutas
    'paths'      => [
        'views'   => __DIR__ . '/../../public/views/',
        'uploads' => __DIR__ . '/../../public/uploads/',
        'logs'    => __DIR__ . '/../../var/logs/',
    ],
];
