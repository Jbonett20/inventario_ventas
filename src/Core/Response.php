<?php
namespace SIG\Core;

/**
 * Clase Response - Manejo de respuestas HTTP
 * 
 * Envía respuestas JSON, redirecciones, y establece
 * cabeceras de seguridad automáticamente.
 */
class Response
{
    private array $headers = [];
    private int $statusCode = 200;

    /**
     * Enviar respuesta JSON
     */
    public static function json(mixed $data, int $status = 200, array $headers = []): void
    {
        self::setSecurityHeaders();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Enviar respuesta JSON de éxito
     */
    public static function success(mixed $data = null, string $message = 'Operación exitosa', int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Enviar respuesta JSON de error
     */
    public static function error(string $message, int $status = 400, mixed $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        self::json($response, $status);
    }

    /**
     * Redireccionar a una URL
     */
    public static function redirect(string $url, int $status = 302): void
    {
        // Si la URL empieza con /, anteponer la ruta base
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $basePath = '';
            if (isset($_SERVER['SCRIPT_NAME'])) {
                $basePath = dirname($_SERVER['SCRIPT_NAME']);
                $basePath = ($basePath === '/' || $basePath === '\\') ? '' : $basePath;
            }
            $url = $basePath . $url;
        }

        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    /**
     * Redireccionar con mensaje flash en sesión
     */
    public static function redirectWith(string $url, string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][$type] = $message;
        self::redirect($url);
    }

    /**
     * Renderizar una vista
     */
    public static function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewInstance = new View();
        echo $viewInstance->render($view, $data, $layout);
        exit;
    }

    /**
     * Descargar un archivo
     */
    public static function download(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            self::error('Archivo no encontrado', 404);
        }

        $filename = $filename ?? basename($filePath);
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        
        readfile($filePath);
        exit;
    }

    /**
     * Establecer cabeceras de seguridad
     */
    public static function setSecurityHeaders(): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
    }

    /**
     * Establecer un código de estado HTTP
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Establecer una cabecera
     */
    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }
}
