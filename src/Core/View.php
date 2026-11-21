<?php
namespace SIG\Core;

use RuntimeException;

/**
 * Clase View - Renderizado de plantillas PHP
 * 
 * Sistema simple de templates con layouts,
 * secciones y escape automático de variables.
 */
class View
{
    private string $viewsPath;
    /** @var array<string, string> */
    private array $sections = [];
    private string $currentSection = '';
    /** @var array<string, mixed> */
    private static array $globalStaticData = [];

    public function __construct()
    {
        $config = require __DIR__ . '/../config/app.php';
        $this->viewsPath = $config['paths']['views'];
    }

    /**
     * Asignar una variable global (estática - accesible desde cualquier instancia)
     */
    public static function setGlobal(string $key, mixed $value): void
    {
        self::$globalStaticData[$key] = $value;
    }

    /**
     * Asignar una variable global para esta instancia
     */
    public function assign(string $key, mixed $value): void
    {
        // Usar el array estático para consistencia
        self::$globalStaticData[$key] = $value;
    }

    /**
     * Renderizar una vista con layout
     * 
     * @param string $view   Nombre de la vista (ej: 'facturacion/index')
     * @param array<string, mixed> $data Datos para la vista
     * @param string $layout Nombre del layout (null = sin layout)
     * @return string HTML renderizado
     */
    public function render(string $view, array $data = [], ?string $layout = 'main'): string
    {
        // Combinar datos globales estáticos con datos locales
        $data = array_merge(self::$globalStaticData, $data);
        
        // Extraer variables para la vista
        extract($data, EXTR_SKIP);

        // Renderizar el contenido de la vista
        $content = $this->renderView($view, $data);

        // Si no hay layout, devolver solo el contenido
        if ($layout === null) {
            return $content;
        }

        // Renderizar con layout
        ob_start();
        require $this->getPath("layouts/{$layout}");
        return ob_get_clean();
    }

    /**
     * Renderizar una vista parcial (sin layout)
     *
     * @param array<string, mixed> $data
     */
    public function partial(string $view, array $data = []): string
    {
        return $this->renderView($view, $data);
    }

    /**
     * Renderizar el contenido de una vista
     *
     * @param array<string, mixed> $data
     */
    private function renderView(string $view, array $data): string
    {
        extract($data, EXTR_SKIP);
        
        $path = $this->getPath($view);
        
        if (!file_exists($path)) {
            throw new RuntimeException("Vista no encontrada: {$view} (ruta: {$path})");
        }

        ob_start();
        require $path;
        return ob_get_clean();
    }

    /**
     * Obtener la ruta completa de una vista
     */
    private function getPath(string $view): string
    {
        return $this->viewsPath . str_replace('.', '/', $view) . '.php';
    }

    /**
     * Iniciar una sección (para usar en layouts)
     */
    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    /**
     * Finalizar una sección
     */
    public function endSection(): void
    {
        if ($this->currentSection) {
            $content = ob_get_clean();
            $this->sections[$this->currentSection] = $content === false ? '' : $content;
            $this->currentSection = '';
        }
    }

    /**
     * Renderizar una sección (para usar en layouts)
     */
    public function renderSection(string $name): void
    {
        echo $this->sections[$name] ?? '';
    }

    /**
     * Escapar HTML seguro (evitar XSS)
     */
    public static function esc(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Formatear número como moneda
     *
     * @param mixed $value
     */
    public static function money($value): string
    {
        return '$' . number_format((float)$value, 0, ',', '.');
    }

    /**
     * Formatear fecha
     */
    public static function date(string $date, string $format = 'd/m/Y'): string
    {
        if (empty($date)) return '';
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '';
    }

    /**
     * Verificar si el usuario tiene permiso
     */
    public static function checkPermiso(string $permiso): bool
    {
        $roles = require __DIR__ . '/../config/roles.php';
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $tipo = $_SESSION['user_tipo'] ?? 99;
        
        return in_array($tipo, $roles['permisos'][$permiso] ?? []);
    }
}
