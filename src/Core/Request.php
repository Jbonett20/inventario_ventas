<?php
namespace SIG\Core;

/**
 * Clase Request - Manejo de la solicitud HTTP
 * 
 * Sanitiza entradas, maneja CSRF tokens y provee
 * acceso seguro a datos GET, POST, SESSION y FILES.
 */
class Request
{
    private array $params    = [];
    private array $query     = [];
    private array $body      = [];
    private array $files     = [];
    private array $server    = [];
    private ?string $rawBody = null;
    private string $basePath = '';

    public function __construct()
    {
        $this->query  = $this->sanitizeArray($_GET);
        $this->body   = $this->sanitizeArray($_POST);
        $this->files  = $_FILES;
        $this->server = $_SERVER;
        $this->basePath = $this->detectBasePath();
    }

    /**
     * Detectar la ruta base del proyecto (para subdirectorios)
     */
    private function detectBasePath(): string
    {
        $scriptName = $this->server['SCRIPT_NAME'] ?? '';
        // Ej: /misproyectos/inventario_ventas/public/index.php
        $dir = dirname($scriptName);
        return ($dir === '/' || $dir === '\\') ? '' : $dir;
    }

    /**
     * Obtener la ruta base
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Obtener el método HTTP
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Obtener la URI solicitada (sin la ruta base)
     */
    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        // Strip query string y base path
        if (!empty($this->basePath) && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Obtener un valor GET sanitizado
     */
    public function get(string $key, $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Obtener un valor POST sanitizado
     */
    public function post(string $key, $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Obtener todo el body POST
     */
    public function all(): array
    {
        return $this->body;
    }

    /**
     * Obtener el body JSON (para API requests)
     */
    public function json(): ?array
    {
        if ($this->rawBody === null) {
            $this->rawBody = file_get_contents('php://input');
        }

        $data = json_decode($this->rawBody, true);
        return is_array($data) ? $this->sanitizeArray($data) : null;
    }

    /**
     * Verificar si es una solicitud AJAX
     */
    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || (isset($this->server['HTTP_ACCEPT']) && str_contains($this->server['HTTP_ACCEPT'], 'application/json'));
    }

    /**
     * Establecer parámetros de ruta (usado por el Router)
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Obtener un parámetro de ruta
     */
    public function param(string $key, $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Obtener un archivo subido
     */
    public function file(string $key): ?array
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK
            ? $this->files[$key]
            : null;
    }

    /**
     * Obtener la IP del cliente
     */
    public function getClientIp(): string
    {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($this->server[$header])) {
                $ips = explode(',', $this->server[$header]);
                return trim($ips[0]);
            }
        }
        return '0.0.0.0';
    }

    /**
     * Obtener User-Agent
     */
    public function getUserAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Generar un token CSRF
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Validar un token CSRF
     */
    public static function validateCsrfToken(string $token, int $expiry = 3600): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Verificar expiración
        if (time() - $_SESSION['csrf_token_time'] > $expiry) {
            unset($_SESSION['csrf_token']);
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitizar un string (prevenir XSS básico)
     */
    public static function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }
        return $value;
    }

    /**
     * Sanitizar un array completo
     */
    private function sanitizeArray(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = self::sanitize($value);
        }
        return $result;
    }

    /**
     * Obtener el método HTTP real (soporta _method para formularios)
     */
    public function getRealMethod(): string
    {
        $method = $this->getMethod();
        
        if ($method === 'POST' && isset($this->body['_method'])) {
            return strtoupper($this->body['_method']);
        }
        
        return $method;
    }
}
