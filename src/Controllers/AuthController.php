<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\Session;
use SIG\Core\Database;
use SIG\Helpers\Security;
use SIG\Middleware\RoleMiddleware;

/**
 * Controlador de Autenticación
 * 
 * Maneja el login, logout y verificación de sesión.
 */
class AuthController
{
    private Database $db;
    private Session $session;

    public function __construct()
    {
        $this->db      = Database::getInstance();
        $this->session = Session::getInstance();
    }

    /**
     * Mostrar formulario de login
     */
    public function loginForm(Request $request): string
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->session->isAuthenticated()) {
            Response::redirect('/');
        }

        $error = $this->session->flash('error');

        $view = new \SIG\Core\View();
        return $view->render('login', [
            'title' => 'Iniciar Sesión',
            'error' => $error,
            'appName' => 'Sistema Integral de Gestión',
        ], null); // Sin layout
    }

    /**
     * Procesar el login
     */
    public function login(Request $request): void
    {
        $username = trim($request->post('usuario', ''));
        $password = $request->post('clave', '');

        // Validar campos vacíos
        if (empty($username) || empty($password)) {
            if ($request->isAjax()) {
                Response::error('Usuario y contraseña son requeridos');
            }
            $this->session->flash('error', 'Usuario y contraseña son requeridos');
            Response::redirect('/login');
        }

        // Buscar usuario en BD
        $user = $this->db->fetchOne(
            "SELECT id_usuario, nombre_usuario, password_hash, tipo, estado, 
                    intentos_fallidos, imagen, fecha_inicial, fecha_final
             FROM vb_usuarios 
             WHERE nombre_usuario = :usuario",
            ['usuario' => $username]
        );

        // Usuario no existe
        if (!$user) {
            $this->session->flash('error', 'Usuario o contraseña incorrectos');
            Response::redirect('/login');
        }

        // Verificar si la cuenta está bloqueada
        if ((int)$user['intentos_fallidos'] >= 5) {
            $this->session->flash('error', 'Cuenta bloqueada por múltiples intentos fallidos. Contacte al administrador.');
            Response::redirect('/login');
        }

        // Verificar estado
        if ((int)$user['estado'] === 1) {
            $this->session->flash('error', 'Esta cuenta está inactiva. Contacte al administrador.');
            Response::redirect('/login');
        }
        if ((int)$user['estado'] === 2) {
            $this->session->flash('error', 'Esta cuenta ha sido bloqueada.');
            Response::redirect('/login');
        }

        // Verificar vigencia de fechas
        $hoy = date('Y-m-d');
        if (($user['fecha_inicial'] && $hoy < $user['fecha_inicial']) ||
            ($user['fecha_final'] && $hoy > $user['fecha_final'])) {
            $this->session->flash('error', 'Los permisos de esta cuenta han caducado.');
            
            // Registrar intento fallido
            $this->db->executeAffected(
                "UPDATE vb_usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id_usuario = :id",
                ['id' => $user['id_usuario']]
            );
            
            Response::redirect('/login');
        }

        // Verificar contraseña
        if (!Security::verifyPassword($password, $user['password_hash'])) {
            // Incrementar intentos fallidos
            $this->db->executeAffected(
                "UPDATE vb_usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id_usuario = :id",
                ['id' => $user['id_usuario']]
            );

            $this->session->flash('error', 'Usuario o contraseña incorrectos');
            Response::redirect('/login');
        }

        // Verificar si el hash necesita rehash
        if (Security::needsRehash($user['password_hash'])) {
            $this->db->executeAffected(
                "UPDATE vb_usuarios SET password_hash = :hash WHERE id_usuario = :id",
                [
                    'hash' => Security::hashPassword($password),
                    'id'   => $user['id_usuario'],
                ]
            );
        }

        // Login exitoso: resetear intentos y actualizar último login
        $this->db->executeAffected(
            "UPDATE vb_usuarios SET intentos_fallidos = 0, ultimo_login = NOW() WHERE id_usuario = :id",
            ['id' => $user['id_usuario']]
        );

        // Autenticar en sesión
        $this->session->authenticate(
            (int)$user['id_usuario'],
            $user['nombre_usuario'],
            (int)$user['tipo'],
            $user['imagen'] ?? ''
        );

        // Redirigir según el rol
        if ($request->isAjax()) {
            $basePath = $request->getBasePath();
            $redirectTo = ($user['tipo'] == 2) ? $basePath . '/inventario' : $basePath . '/';
            Response::success(['redirect' => $redirectTo], 'Login exitoso');
        }

        // Redirección normal
        if ((int)$user['tipo'] === 2) {
            Response::redirect('/inventario');
        }
        
        Response::redirect('/');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): void
    {
        $this->session->logout();
        Response::redirect('/login');
    }

    /**
     * Verificar estado de la sesión (para AJAX)
     */
    public function checkSession(Request $request): void
    {
        if ($this->session->isAuthenticated()) {
            Response::success([
                'user_id'  => $this->session->getUserId(),
                'username' => $this->session->get('username'),
                'tipo'     => $this->session->getUserType(),
                'rol'      => RoleMiddleware::getRoleName(),
            ]);
        } else {
            Response::error('Sesión no iniciada', 401);
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request): void
    {
        if (!$this->session->isAuthenticated()) {
            Response::error('No autorizado', 401);
        }

        $currentPwd  = $request->post('current_password', '');
        $newPwd      = $request->post('new_password', '');
        $confirmPwd  = $request->post('confirm_password', '');

        // Validaciones
        if (empty($currentPwd) || empty($newPwd) || empty($confirmPwd)) {
            Response::error('Todos los campos son requeridos');
        }

        if ($newPwd !== $confirmPwd) {
            Response::error('Las contraseñas nuevas no coinciden');
        }

        if (strlen($newPwd) < 6) {
            Response::error('La contraseña debe tener al menos 6 caracteres');
        }

        // Verificar contraseña actual
        $user = $this->db->fetchOne(
            "SELECT password_hash FROM vb_usuarios WHERE id_usuario = :id",
            ['id' => $this->session->getUserId()]
        );

        if (!$user || !Security::verifyPassword($currentPwd, $user['password_hash'])) {
            Response::error('La contraseña actual es incorrecta');
        }

        // Actualizar contraseña
        $this->db->executeAffected(
            "UPDATE vb_usuarios SET password_hash = :hash WHERE id_usuario = :id",
            [
                'hash' => Security::hashPassword($newPwd),
                'id'   => $this->session->getUserId(),
            ]
        );

        Response::success(null, 'Contraseña actualizada exitosamente');
    }
}
