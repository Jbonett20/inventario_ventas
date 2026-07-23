<?php
/**
 * Autoloader Manual (fallback si no está disponible Composer)
 * 
 * Carga automáticamente las clases del namespace SIG\
 * desde la carpeta src/
 */

spl_autoload_register(function (string $class) {
    // Solo procesar clases del namespace SIG
    $prefix = 'SIG\\';
    $baseDir = __DIR__ . '/../';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // Quitar el prefijo y convertir a ruta de archivo
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
