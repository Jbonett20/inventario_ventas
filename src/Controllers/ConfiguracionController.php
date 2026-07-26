<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Core\Database;
use SIG\Helpers\Security;
use SIG\Services\FacturacionElectronicaService;

class ConfiguracionController
{
    private Database $db;
    private Session $session;

    public function __construct()
    {
        $this->db      = Database::getInstance();
        $this->session = Session::getInstance();
    }

    /**
     * Página principal de configuración
     */
    public function index(Request $r): string
    {
        $tab = $r->get('tab', '');

        // Determinar primer tab disponible según permisos
        $tabsPermisos = [
            'empresa'  => 'configuracion.empresa',
            'fe'       => 'configuracion.fe.ver',
            'rangos'   => 'configuracion.rangos.ver',
            'usuarios' => 'configuracion.usuarios',
        ];

        // Si el tab solicitado no tiene permiso, buscar el primero disponible
        if (empty($tab) || !isset($tabsPermisos[$tab]) || !\SIG\Middleware\RoleMiddleware::hasPermission($tabsPermisos[$tab])) {
            foreach ($tabsPermisos as $t => $p) {
                if (\SIG\Middleware\RoleMiddleware::hasPermission($p)) {
                    $tab = $t;
                    break;
                }
            }
            // Fallback por si acaso
            if (empty($tab)) $tab = 'empresa';
        }

        $data = [
            'title'  => 'Configuración',
            'tab'    => $tab,
        ];

        // Cargar datos según pestaña
        switch ($tab) {
            case 'empresa':
                $data['empresa'] = $this->db->fetchOne("SELECT * FROM vb_empresa WHERE id_empresa = 1");
                break;
            case 'fe':
                $feService = new FacturacionElectronicaService();
                $data['fe_config'] = $feService->getConfiguracion();
                break;
            case 'rangos':
                $data['rangos'] = $this->db->select("SELECT * FROM vb_rangos_facturacion ORDER BY tipo");
                break;
            case 'usuarios':
                $data['usuarios'] = $this->db->select(
                    "SELECT id_usuario, nombre_usuario, nombre_completo, email, tipo, 
                            estado, fecha_inicial, fecha_final, ultimo_login, intentos_fallidos
                     FROM vb_usuarios ORDER BY nombre_usuario"
                );
                break;
        }

        $view = new View();
        return $view->render('configuracion/index', $data);
    }

    /**
     * Guardar datos de la empresa
     */
    public function guardarEmpresa(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        $this->db->executeAffected(
            "UPDATE vb_empresa SET 
                nit = :nit, nombre = :nombre, direccion = :dir,
                telefono = :tel, email = :email,
                mostrar_fe = :mostrar_fe
             WHERE id_empresa = 1",
            [
                'nit'         => $data['nit'] ?? '',
                'nombre'      => $data['nombre'] ?? '',
                'dir'         => $data['direccion'] ?? '',
                'tel'         => $data['telefono'] ?? '',
                'email'       => $data['email'] ?? '',
                'mostrar_fe'  => !empty($data['mostrar_fe']) ? 1 : 0,
            ]
        );

        Response::success(null, 'Empresa actualizada correctamente');
    }

    /**
     * Guardar configuración de Facturación Electrónica
     */
    public function guardarFE(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $feService = new FacturacionElectronicaService();
        $feService->guardarConfiguracion($data);
        Response::success(null, 'Configuración de Facturación Electrónica guardada');
    }

    /**
     * Probar conexión con Factin
     */
    public function testFE(Request $r): void
    {
        $feService = new FacturacionElectronicaService();
        $result = $feService->testConexion();
        if ($result['success']) {
            Response::success($result, 'Conexión exitosa');
        } else {
            Response::error($result['message'], 400);
        }
    }

    /**
     * Guardar rangos de facturación
     */
    public function guardarRango(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id = (int)($data['id_rango'] ?? 0);

        if ($id > 0) {
            $this->db->executeAffected(
                "UPDATE vb_rangos_facturacion SET 
                    tipo = :tipo, prefijo = :prefijo, inicio = :inicio, 
                    fin = :fin, resolucion = :resolucion, activo = :activo
                 WHERE id_rango = :id",
                [
                    'tipo'     => $data['tipo'] ?? 'NORMAL',
                    'prefijo'  => $data['prefijo'] ?? '',
                    'inicio'   => (int)($data['inicio'] ?? 0),
                    'fin'      => (int)($data['fin'] ?? 0),
                    'resolucion' => $data['resolucion'] ?? '',
                    'activo'   => (int)($data['activo'] ?? 1),
                    'id'       => $id,
                ]
            );
        } else {
            $this->db->insert(
                "INSERT INTO vb_rangos_facturacion (tipo, prefijo, inicio, fin, resolucion, activo)
                 VALUES (:tipo, :prefijo, :inicio, :fin, :resolucion, :activo)",
                [
                    'tipo'     => $data['tipo'] ?? 'NORMAL',
                    'prefijo'  => $data['prefijo'] ?? '',
                    'inicio'   => (int)($data['inicio'] ?? 0),
                    'fin'      => (int)($data['fin'] ?? 0),
                    'resolucion' => $data['resolucion'] ?? '',
                    'activo'   => (int)($data['activo'] ?? 1),
                ]
            );
        }

        Response::success(null, 'Rango guardado correctamente');
    }

    /**
     * Crear usuario
     */
    public function guardarUsuario(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id = (int)($data['id_usuario'] ?? 0);

        if (empty($data['nombre_usuario'])) {
            Response::error('El nombre de usuario es requerido', 422);
        }

        $userTipo = (int)($data['tipo'] ?? 1);
        $currentUserTipo = $this->session->getUserType();

        // Solo superadmin (tipo 3) puede crear/editar superadmins o admins
        if ($currentUserTipo !== 3 && ($userTipo === 0 || $userTipo === 3)) {
            Response::error('No tienes permisos para crear o modificar usuarios con este rol', 403);
        }

        if ($id > 0) {
            // Actualizar
            $sql = "UPDATE vb_usuarios SET 
                        nombre_completo = :nc, email = :email, tipo = :tipo,
                        estado = :estado, fecha_inicial = :fi, fecha_final = :ff
                    WHERE id_usuario = :id";
            $params = [
                'nc'    => $data['nombre_completo'] ?? '',
                'email' => $data['email'] ?? '',
                'tipo'  => (int)($data['tipo'] ?? 1),
                'estado'=> (int)($data['estado'] ?? 1),
                'fi'    => $data['fecha_inicial'] ?? null,
                'ff'    => $data['fecha_final'] ?? null,
                'id'    => $id,
            ];

            if (!empty($data['password'])) {
                $sql = "UPDATE vb_usuarios SET 
                            nombre_completo = :nc, email = :email, tipo = :tipo,
                            password_hash = :ph, estado = :estado,
                            fecha_inicial = :fi, fecha_final = :ff
                        WHERE id_usuario = :id";
                $params['ph'] = Security::hashPassword($data['password']);
            }

            $this->db->executeAffected($sql, $params);
            Response::success(null, 'Usuario actualizado');

        } else {
            // Crear
            if (empty($data['password'])) {
                Response::error('La contraseña es requerida para nuevos usuarios', 422);
            }

            $existe = $this->db->fetchOne(
                "SELECT id_usuario FROM vb_usuarios WHERE nombre_usuario = :nu",
                ['nu' => $data['nombre_usuario']]
            );
            if ($existe) {
                Response::error('El nombre de usuario ya existe', 422);
            }

            $this->db->insert(
                "INSERT INTO vb_usuarios 
                    (nombre_usuario, password_hash, nombre_completo, email, tipo, estado, fecha_inicial, fecha_final)
                 VALUES 
                    (:nu, :ph, :nc, :email, :tipo, :estado, :fi, :ff)",
                [
                    'nu'    => $data['nombre_usuario'],
                    'ph'    => Security::hashPassword($data['password']),
                    'nc'    => $data['nombre_completo'] ?? '',
                    'email' => $data['email'] ?? '',
                    'tipo'  => (int)($data['tipo'] ?? 1),
                    'estado'=> (int)($data['estado'] ?? 1),
                    'fi'    => $data['fecha_inicial'] ?? null,
                    'ff'    => $data['fecha_final'] ?? null,
                ]
            );

            Response::success(null, 'Usuario creado correctamente');
        }
    }
}
