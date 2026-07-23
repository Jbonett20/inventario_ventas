<?php
namespace SIG\Middleware;

use SIG\Core\Request;
use SIG\Core\Response;

/**
 * Middleware CSRF
 * 
 * Protege contra Cross-Site Request Forgery.
 * Valida que cada solicitud POST incluya un token CSRF válido.
 */
class CsrfMiddleware
{
    /**
     * Lista de rutas que no requieren validación CSRF
     */
    private array $excludedRoutes = [
        '/api/webhook',
        '/api/facturacion-electronica',
    ];

    /**
     * Manejar la solicitud
     * 
     * @param Request $request
     * @return bool
     */
    public function handle(Request $request): bool
    {
        // Solo validar en métodos de escritura
        if (!in_array($request->getRealMethod(), ['POST', 'PUT', 'DELETE'])) {
            return true;
        }

        // Excluir rutas específicas (webhooks, APIs externas)
        if (in_array($request->getUri(), $this->excludedRoutes)) {
            return true;
        }

        // Obtener token de varias fuentes posibles
        $token = $request->post('csrf_token')
               ?? $request->get('csrf_token')
               ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (empty($token) || !Request::validateCsrfToken($token)) {
            if ($request->isAjax()) {
                Response::error('Token CSRF inválido o expirado. Recargue la página.', 403);
            }

            // Para formularios normales, mostrar error
            $config = require __DIR__ . '/../config/app.php';
            $debug = $config['debug'];

            $message = 'Token de seguridad inválido. Por favor recargue la página e intente nuevamente.';
            
            if ($debug) {
                $message .= ' (CSRF validation failed)';
            }

            $_SESSION['flash']['error'] = $message;
            
            // Redirigir de vuelta
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            Response::redirect($referer);
            return false;
        }

        return true;
    }
}
