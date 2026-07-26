<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \SIG\Core\View::esc($title ?? 'SIG') ?> - Sistema Integral de Gestión</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- PNotify -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pnotify/3.2.1/pnotify.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pnotify/3.2.1/pnotify.buttons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pnotify/3.2.1/pnotify.nonblock.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/app.css">
    <!-- jQuery (cargar antes que cualquier script embebido) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>var BASE_URL = '<?= $basePath ?>';</script>

    <style>
        :root {
            --sidebar-bg: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            --sidebar-width: 250px;
            --sidebar-collapsed: 70px;
            --primary: #8e24aa;
            --primary-dark: #6a1b9a;
            --primary-light: #e1bee7;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #fff;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar .logo .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .sidebar .logo .logo-text {
            font-size: 16px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .logo-text { opacity: 0; width: 0; }

        /* Perfil de usuario */
        .sidebar .user-profile {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .user-profile .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
            flex-shrink: 0;
        }

        .sidebar .user-profile .user-info {
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .user-info { opacity: 0; width: 0; }

        .sidebar .user-profile .user-info .name {
            font-size: 14px;
            font-weight: 600;
        }

        .sidebar .user-profile .user-info .role {
            font-size: 11px;
            color: rgba(255,255,255,0.6);
        }

        /* Menú de navegación */
        .sidebar .nav-menu { padding: 10px 0; }

        .sidebar .nav-menu .nav-item {
            list-style: none;
        }

        .sidebar .nav-menu .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            gap: 12px;
            border-left: 3px solid transparent;
            white-space: nowrap;
        }

        .sidebar .nav-menu .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .sidebar .nav-menu .nav-link.active {
            background: rgba(142, 36, 170, 0.2);
            color: #fff;
            border-left-color: var(--primary);
        }

        .sidebar .nav-menu .nav-link i {
            width: 22px;
            text-align: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .sidebar .nav-menu .nav-link .nav-text {
            font-size: 14px;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .nav-text { opacity: 0; width: 0; }

        .sidebar .nav-menu .nav-link .badge {
            margin-left: auto;
            font-size: 10px;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .badge { opacity: 0; }

        /* Separador */
        .sidebar .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.08);
            margin: 10px 20px;
        }

        .sidebar .nav-section-title {
            padding: 8px 20px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.35);
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .nav-section-title { opacity: 0; height: 0; padding: 0; overflow: hidden; }

        /* ===== CONTENIDO PRINCIPAL ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .sidebar.collapsed ~ .main-content,
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        /* Navbar superior */
        .top-navbar {
            background: #fff;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .top-navbar .left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-navbar .toggle-btn {
            background: none;
            border: none;
            font-size: 22px;
            color: #555;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .top-navbar .toggle-btn:hover {
            background: #f0f0f0;
            color: var(--primary);
        }

        .top-navbar .page-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .top-navbar .right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-navbar .right .btn-logout {
            padding: 8px 16px;
            background: none;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            color: #666;
            text-decoration: none;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }

        .top-navbar .right .btn-logout:hover {
            background: #fee2e2;
            border-color: #fecaca;
            color: #dc2626;
        }

        /* Contenedor de contenido */
        .page-content {
            padding: 25px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                left: calc(var(--sidebar-width) * -1);
            }

            .sidebar.mobile-open {
                left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .top-navbar .page-title {
                font-size: 15px;
            }

            .page-content {
                padding: 15px;
            }
        }

        /* Scrollbar personalizada */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
    </style>
</head>
<body>

    <!-- Overlay para móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <span class="logo-text">SIG</span>
        </div>

        <!-- Perfil de usuario -->
        <div class="user-profile">
            <img src="<?= \SIG\Core\View::esc($userImagen ?? ($basePath . '/assets/img/user.png')) ?>" 
                 alt="Avatar" class="avatar" 
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($username ?? 'U') ?>&background=8e24aa&color=fff'">
            <div class="user-info">
                <div class="name"><?= \SIG\Core\View::esc($username ?? 'Usuario') ?></div>
                <div class="role">
                    <?php
                    $roles = [0 => 'Administrador', 1 => 'Cajero', 2 => 'Inventario', 3 => 'Superadministrador'];
                    echo $roles[$userTipo ?? 99] ?? 'Desconocido';
                    ?>
                </div>
            </div>
        </div>

        <!-- Menú -->
        <ul class="nav-menu">
            <li class="nav-section-title">PRINCIPAL</li>
            <li class="nav-item">
                <a href="<?= $basePath ?>/" class="nav-link <?= $currentRoute === '/' ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-divider"></li>
            <li class="nav-section-title">VENTAS</li>
            
            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('facturacion.crear')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/facturacion" class="nav-link">
                    <i class="fas fa-cash-register"></i>
                    <span class="nav-text">Facturación</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $basePath ?>/facturacion/historial" class="nav-link">
                    <i class="fas fa-file-invoice"></i>
                    <span class="nav-text">Historial Facturas</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('clientes.crear')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/clientes" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Clientes</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('creditos.gestion')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/creditos" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Créditos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('plan_separe.gestion')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/plan-separe" class="nav-link <?= $currentRoute === '/plan-separe' ? 'active' : '' ?>">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span class="nav-text">Plan Separe</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-divider"></li>
            <li class="nav-section-title">INVENTARIO</li>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('inventario.ver')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/inventario" class="nav-link">
                    <i class="fas fa-warehouse"></i>
                    <span class="nav-text">Inventario</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('inventario.ingresar')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/ingresos" class="nav-link">
                    <i class="fas fa-boxes"></i>
                    <span class="nav-text">Ingresos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('productos.crear')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/productos" class="nav-link">
                    <i class="fas fa-capsules"></i>
                    <span class="nav-text">Productos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('proveedores.gestion')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/proveedores" class="nav-link">
                    <i class="fas fa-truck"></i>
                    <span class="nav-text">Proveedores</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-divider"></li>
            <li class="nav-section-title">ADMINISTRACIÓN</li>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('configuracion.ver')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/configuracion" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Configuración</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('egresos.gestion')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/egresos" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <span class="nav-text">Egresos</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('reportes.ver')): ?>
            <li class="nav-item">
                <a href="<?= $basePath ?>/reportes" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-section-title">ADMINISTRACIÓN</li>
            <li class="nav-item">
                <a href="<?= $basePath ?>/balance-diario" class="nav-link">
                    <i class="fas fa-balance-scale"></i>
                    <span class="nav-text">Balance Diario</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $basePath ?>/devoluciones" class="nav-link">
                    <i class="fas fa-undo-alt"></i>
                    <span class="nav-text">Devoluciones</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <div class="main-content <?= isset($_COOKIE['sidebar']) && $_COOKIE['sidebar'] === 'collapsed' ? 'expanded' : '' ?>" id="mainContent">
        
        <!-- Navbar Superior -->
        <nav class="top-navbar">
            <div class="left">
                <button class="toggle-btn" id="toggleSidebar" title="Menú">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="page-title"><?= \SIG\Core\View::esc($title ?? 'Dashboard') ?></span>
            </div>
            <div class="right">
                <a href="<?= $basePath ?>/logout" class="btn-logout" title="Cerrar sesión">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="d-none d-sm-inline">Salir</span>
                </a>
            </div>
        </nav>

        <!-- Contenido de la página -->
        <div class="page-content">
            <?= $content ?? '' ?>
        </div>
    </div>

    <!-- Modal Confirmación Global -->
    <div class="modal fade" id="modalConfirmar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px">
                <div class="modal-body text-center py-4">
                    <div class="mb-3" id="confirmIcon">
                        <div style="width:60px;height:60px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size:26px"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2" id="confirmTitle">¿Confirmar acción?</h5>
                    <p class="text-muted mb-0" id="confirmMessage" style="font-size:14px">¿Está seguro de realizar esta acción?</p>
                    <input type="hidden" id="confirmCallback" value="">
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal" id="btnConfirmNo">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4 rounded-pill" id="btnConfirmSi">
                        <i class="fas fa-check me-1"></i> Sí, continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- PNotify JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pnotify/3.2.1/pnotify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pnotify/3.2.1/pnotify.buttons.js"></script>
    <script>
    PNotify.prototype.options.styling = 'bootstrap3';
    PNotify.prototype.options.delay = 3000;

    // Shortcuts para PNotify (compatibilidad con vistas)
    PNotify.success = function(opts) {
        return new PNotify($.extend({ type: 'success', icon: 'fas fa-check-circle', text: opts.text || '' }, opts));
    };
    PNotify.error = function(opts) {
        return new PNotify($.extend({ type: 'error', icon: 'fas fa-exclamation-circle', text: opts.text || '' }, opts));
    };
    PNotify.info = function(opts) {
        return new PNotify($.extend({ type: 'info', icon: 'fas fa-info-circle', text: opts.text || '' }, opts));
    };
    </script>
    <script src="<?= $basePath ?>/assets/js/offline-sync.js"></script>

    <script>
    $(function() {
        // ===== TOGGLE SIDEBAR =====
        var $sidebar = $('#sidebar');
        var $mainContent = $('#mainContent');
        var $overlay = $('#sidebarOverlay');
        var isMobile = window.innerWidth <= 768;

        function toggleSidebar() {
            if (isMobile) {
                $sidebar.toggleClass('mobile-open');
                $overlay.toggleClass('show');
            } else {
                $sidebar.toggleClass('collapsed');
                $mainContent.toggleClass('expanded');
                
                // Guardar preferencia en cookie
                var state = $sidebar.hasClass('collapsed') ? 'collapsed' : 'expanded';
                document.cookie = 'sidebar=' + state + '; path=/; max-age=' + (365 * 24 * 60 * 60);
            }
        }

        $('#toggleSidebar').on('click', toggleSidebar);
        $overlay.on('click', toggleSidebar);

        // Restaurar estado del sidebar
        if (!isMobile) {
            var sidebarState = document.cookie.replace(/(?:(?:^|.*;\s*)sidebar\s*\=\s*([^;]*).*$)|^.*$/, '$1');
            if (sidebarState === 'collapsed') {
                $sidebar.addClass('collapsed');
                $mainContent.addClass('expanded');
            }
        }

        // ===== WINDOW RESIZE =====
        $(window).on('resize', function() {
            isMobile = window.innerWidth <= 768;
            if (!isMobile) {
                $sidebar.removeClass('mobile-open');
                $overlay.removeClass('show');
            }
        });

        // ===== OFFLINE SYNC =====
        if (typeof SIG_OfflineSync !== 'undefined') {
            SIG_OfflineSync.init({ debug: false });

            SIG_OfflineSync.on('onOffline', function() {
                console.log('📡 Modo offline activado');
            });

            SIG_OfflineSync.on('onOnline', function() {
                console.log('🌐 Conexión restaurada');
            });

            SIG_OfflineSync.on('onSyncComplete', function(data) {
                if (data.synced > 0) {
                    console.log('✅ Sincronizados', data.synced, 'elementos');
                }
            });
        }

        // ===== ACTIVAR LINK ACTUAL EN EL MENÚ =====
        var currentPath = window.location.pathname;
        $('.nav-link').each(function() {
            if ($(this).attr('href') === currentPath) {
                $(this).addClass('active');
            }
        });
    });

    // ===== MODAL DE CONFIRMACIÓN GLOBAL (fuera del $().ready para ser global) =====
    let _confirmCallback = null;

    function SIG_confirmar(mensaje, titulo, callback) {
        $('#confirmTitle').text(titulo || '¿Confirmar acción?');
        $('#confirmMessage').text(mensaje || '¿Está seguro de realizar esta acción?');
        _confirmCallback = callback;
        $('#modalConfirmar').modal('show');
    }

    $('#btnConfirmSi').on('click', function() {
        $('#modalConfirmar').modal('hide');
        if (typeof _confirmCallback === 'function') {
            _confirmCallback();
            _confirmCallback = null;
        }
    });
    </script>
</body>
</html>
