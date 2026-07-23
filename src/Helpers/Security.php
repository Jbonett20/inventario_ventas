<?php
namespace SIG\Helpers;

/**
 * Helper Security - Funciones de seguridad
 * 
 * Sanitización, escape, generación de tokens,
 * hashing y validación de datos.
 */
class Security
{
    /**
     * Hash de contraseña con bcrypt
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Verificar contraseña contra un hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Verificar si la contraseña necesita rehash
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Generar un token aleatorio seguro
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generar un ID único
     */
    public static function generateUUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Escapar para HTML (XSS prevention)
     */
    public static function esc(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapar para URL
     */
    public static function escUrl(string $value): string
    {
        return urlencode($value);
    }

    /**
     * Escapar para atributo HTML
     */
    public static function escAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Escapar para JavaScript (evitar XSS en contexto JS)
     */
    public static function escJs(string $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Sanitizar un string (eliminar tags HTML)
     */
    public static function stripTags(string $value): string
    {
        return strip_tags($value);
    }

    /**
     * Validar que sea un entero positivo
     */
    public static function validateInt(mixed $value): ?int
    {
        if (is_numeric($value) && (int)$value > 0) {
            return (int)$value;
        }
        return null;
    }

    /**
     * Validar email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar URL
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validar que sea un número de teléfono colombiano
     */
    public static function validatePhone(string $phone): bool
    {
        return preg_match('/^3\d{9}$/', $phone) === 1 || preg_match('/^\d{7,10}$/', $phone) === 1;
    }

    /**
     * Validar documento (CC o NIT colombiano)
     */
    public static function validateDocument(string $document): bool
    {
        return preg_match('/^\d{5,15}$/', $document) === 1;
    }

    /**
     * Limpiar la entrada de un input para base de datos
     */
    public static function sanitizeInput(string $value): string
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        return $value;
    }

    /**
     * Verificar si la solicitud es HTTPS
     */
    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }

    /**
     * Obtener la IP real del cliente
     */
    public static function getClientIp(): string
    {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        return '0.0.0.0';
    }

    /**
     * Verificar que el request tenga un token CSRF válido
     */
    public static function checkCsrf(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (empty($token)) {
            return false;
        }

        return \SIG\Core\Request::validateCsrfToken($token);
    }
}
