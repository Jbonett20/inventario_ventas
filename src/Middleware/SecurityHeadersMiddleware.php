<?php
namespace SIG\Middleware;

use SIG\Core\Request;

/**
 * Middleware de Headers de Seguridad
 * 
 * Establece cabeceras HTTP de seguridad en todas las respuestas
 * para proteger contra vulnerabilidades comunes.
 */
class SecurityHeadersMiddleware
{
    /**
     * Headers de seguridad a aplicar
     */
    private array $headers = [
        'X-Content-Type-Options'  => 'nosniff',
        'X-Frame-Options'         => 'DENY',
        'X-XSS-Protection'        => '1; mode=block',
        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
        'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=()',
    ];

    /**
     * Content-Security-Policy (configurable)
     */
    private string $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://cdn.datatables.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.datatables.net; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self'";

    /**
     * Manejar la solicitud
     * 
     * @param Request $request
     * @return bool
     */
    public function handle(Request $request): bool
    {
        if (!headers_sent()) {
            $this->applyHeaders();
        }

        return true;
    }

    /**
     * Aplicar todos los headers de seguridad
     */
    private function applyHeaders(): void
    {
        // Headers básicos
        foreach ($this->headers as $header => $value) {
            header("{$header}: {$value}");
        }

        // CSP
        header("Content-Security-Policy: {$this->csp}");

        // HSTS solo si está en HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Cache control para APIs
        if ($this->isApiRequest()) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }

    /**
     * Verificar si es una solicitud a la API
     */
    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api/');
    }
}
