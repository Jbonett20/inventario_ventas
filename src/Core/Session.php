<?php
namespace SIG\Core;

/**
 * Clase Session - Manejo seguro de sesiones
 * 
 * Configura sesiones con cookies seguras, regeneración de ID,
 * timeout automático y protección contra fijación de sesión.
 */
class Session
{
    private static ?Session $instance = null;
    private array $config;
    private bool $started = false;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../config/app.php';
    }

    /**
     * Obtener instancia única
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Iniciar la sesión con configuración segura
     */
    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        $sessionConfig = $this->config['session'];

        // Configurar parámetros de la cookie de sesión
        session_set_cookie_params([
            'lifetime' => $sessionConfig['timeout'] * 60,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $sessionConfig['secure'],
            'httponly' => $sessionConfig['http_only'],
            'samesite' => $sessionConfig['same_site'],
        ]);

        session_name($sessionConfig['name']);
        session_start();

        $this->started = true;

        // Validar timeout y regenerar ID periódicamente
        $this->validateTimeout();
        $this->regenerateIfNeeded();
    }

    /**
     * Validar timeout de sesión
     */
    private function validateTimeout(): void
    {
        $timeout = $this->config['session']['timeout'] * 60;

        if (isset($_SESSION['_last_activity'])) {
            $inactive = time() - $_SESSION['_last_activity'];
            if ($inactive > $timeout) {
                $this->destroy();
                $this->start();
                $_SESSION['_expired'] = true;
            }
        }

        $_SESSION['_last_activity'] = time();
    }

    /**
     * Regenerar ID de sesión periódicamente (cada 5 minutos)
     */
    private function regenerateIfNeeded(): void
    {
        if (!isset($_SESSION['_regenerated'])) {
            $_SESSION['_regenerated'] = time();
            return;
        }

        if (time() - $_SESSION['_regenerated'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_regenerated'] = time();
        }
    }

    /**
     * Establecer un valor en sesión
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener un valor de sesión
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe una clave en sesión
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar un valor de sesión
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Obtener mensajes flash y eliminarlos
     */
    public function flash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Verificar si hay mensajes flash
     */
    public function hasFlash(string $type): bool
    {
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Obtener todos los mensajes flash
     */
    public function getAllFlash(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !isset($_SESSION['_expired']);
    }

    /**
     * Obtener el ID del usuario autenticado
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtener el tipo/rol del usuario
     */
    public function getUserType(): ?int
    {
        return $_SESSION['user_tipo'] ?? null;
    }

    /**
     * Autenticar al usuario (setear datos en sesión)
     */
    public function authenticate(int $userId, string $username, int $tipo, string $imagen = ''): void
    {
        $this->regenerateId();
        
        $_SESSION['user_id']       = $userId;
        $_SESSION['username']      = $username;
        $_SESSION['user_tipo']     = $tipo;
        $_SESSION['user_imagen']   = $imagen ?: 'assets/img/user.png';
        $_SESSION['user_ip']       = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent']    = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['auth_time']     = time();
        $_SESSION['_last_activity'] = time();
    }

    /**
     * Regenerar ID de sesión (usar después de login)
     */
    public function regenerateId(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['_regenerated'] = time();
        }
    }

    /**
     * Destruir la sesión completamente
     */
    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->started = false;
    }

    /**
     * Cerrar sesión (alias de destroy)
     */
    public function logout(): void
    {
        $this->destroy();
    }

    /**
     * Verificar si la sesión expiró por inactividad
     */
    public function isExpired(): bool
    {
        return isset($_SESSION['_expired']);
    }
}
