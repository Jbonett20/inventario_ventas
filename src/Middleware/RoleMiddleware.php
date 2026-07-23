<?php
namespace SIG\Middleware;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\Session;

/**
 * Middleware de Roles y Permisos
 * 
 * Verifica que el usuario autenticado tenga el rol/perfil necesario
 * para acceder a una ruta o recurso específico.
 * 
 * Uso en Router:
 *   $router->get('/configuracion', 'ConfigController@index', ['SIG\Middleware\RoleMiddleware:admin']);
 *   $router->get('/facturar', 'FacturaController@index', ['SIG\Middleware\RoleMiddleware:cajero|admin']);
 */
class RoleMiddleware
{
    private const ROLE_MAP = [
        'admin'        => 0,
        'cajero'       => 1,
        'inventario'   => 2,
    ];

    /**
     * Manejar la solicitud
     * 
     * @param Request $request
     * @return bool
     */
    public function handle(Request $request): bool
    {
        $session = Session::getInstance();
        $userTipo = $session->getUserType();

        // Si no hay sesión, denegar
        if ($userTipo === null) {
            if ($request->isAjax()) {
                Response::error('No autorizado', 403);
            }
            Response::redirect('/login');
            return false;
        }

        return true;
    }

    /**
     * Verificar un permiso específico
     * 
     * @param string $permiso Nombre del permiso (ej: 'facturacion.crear')
     * @return bool
     */
    public static function hasPermission(string $permiso): bool
    {
        $roles = require __DIR__ . '/../config/roles.php';
        $session = Session::getInstance();
        $userTipo = $session->getUserType();

        if ($userTipo === null) {
            return false;
        }

        $permisos = $roles['permisos'][$permiso] ?? [];

        return in_array($userTipo, $permisos);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin(): bool
    {
        $session = Session::getInstance();
        return $session->getUserType() === 0;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string|int $role Nombre o ID del rol
     * @return bool
     */
    public static function hasRole(string|int $role): bool
    {
        $session = Session::getInstance();
        
        if (is_string($role)) {
            $role = self::ROLE_MAP[$role] ?? -1;
        }

        return $session->getUserType() === $role;
    }

    /**
     * Obtener el nombre del rol del usuario actual
     */
    public static function getRoleName(): string
    {
        $roles = require __DIR__ . '/../config/roles.php';
        $session = Session::getInstance();
        $userTipo = $session->getUserType();

        return $roles['roles'][$userTipo]['nombre'] ?? 'Desconocido';
    }
}
