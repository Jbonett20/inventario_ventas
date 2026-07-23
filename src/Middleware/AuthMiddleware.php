<?php
namespace SIG\Middleware;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\Session;

/**
 * Middleware de Autenticación
 * 
 * Verifica que el usuario tenga una sesión activa.
 * Si no está autenticado, redirige al login o devuelve error 401.
 */
class AuthMiddleware
{
    /**
     * Manejar la solicitud
     * 
     * @param Request $request
     * @return bool true si continúa, false si se detiene
     */
    public function handle(Request $request): bool
    {
        $session = Session::getInstance();

        // Verificar si el usuario está autenticado y la sesión no expiró
        if (!$session->isAuthenticated()) {
            if ($request->isAjax()) {
                Response::error('Sesión no iniciada', 401);
            }

            // Redirigir al login
            Response::redirect('/login');
            return false;
        }

        // Verificar si la sesión expiró por inactividad
        if ($session->isExpired()) {
            $session->remove('_expired');

            if ($request->isAjax()) {
                Response::error('Sesión expirada', 401);
            }

            $session->flash('error', 'Su sesión ha expirado por inactividad. Por favor inicie sesión nuevamente.');
            Response::redirect('/login');
            return false;
        }

        return true;
    }
}
