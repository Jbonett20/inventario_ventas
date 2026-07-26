<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \SIG\Core\View::esc($title ?? 'SIG') ?> - <?= \SIG\Core\View::esc($appName ?? 'Sistema') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #8e24aa; --primary-dark: #6a1b9a; --primary-light: #e1bee7; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:#f0f2f5; padding:20px;
        }
        .login-wrapper {
            width:100%; max-width:1000px; background:#fff; border-radius:24px;
            box-shadow:0 25px 80px rgba(0,0,0,0.15); overflow:hidden;
            display:flex; min-height:600px; animation:fadeIn 0.6s ease;
        }
        @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* Panel izquierdo */
        .left-panel {
            flex:1; background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);
            padding:50px 45px; display:flex; flex-direction:column; justify-content:center;
            position:relative; overflow:hidden;
        }
        .left-panel::before {
            content:''; position:absolute; top:-50%; left:-50%; width:200%; height:200%;
            background:radial-gradient(circle at 30% 50%,rgba(142,36,170,0.1) 0%,transparent 60%);
            animation:float 8s ease-in-out infinite;
        }
        @keyframes float { 0%,100% { transform:translate(0,0); } 50% { transform:translate(-20px,20px); } }
        .left-panel .content { position:relative; z-index:1; color:#fff; }
        .left-panel .brand { display:flex; align-items:center; gap:12px; margin-bottom:35px; }
        .left-panel .brand .logo-icon {
            width:55px; height:55px; background:linear-gradient(135deg,var(--primary),var(--primary-dark));
            border-radius:16px; display:flex; align-items:center; justify-content:center;
            font-size:26px; color:#fff; box-shadow:0 8px 25px rgba(142,36,170,0.3);
        }
        .left-panel .brand .logo-text h2 { font-size:22px; font-weight:700; margin:0; }
        .left-panel .brand .logo-text span { font-size:12px; color:rgba(255,255,255,0.6); letter-spacing:2px; text-transform:uppercase; }
        .left-panel h1 { font-size:32px; font-weight:800; margin-bottom:15px; line-height:1.2; }
        .left-panel h1 .highlight { background:linear-gradient(135deg,#e1bee7,#ce93d8); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .left-panel p { font-size:15px; color:rgba(255,255,255,0.75); line-height:1.7; margin-bottom:30px; }
        .left-panel .features { display:flex; flex-direction:column; gap:14px; }
        .left-panel .features .feature-item { display:flex; align-items:center; gap:14px; font-size:14px; color:rgba(255,255,255,0.85); }
        .left-panel .features .feature-item .fi-icon {
            width:38px; height:38px; background:rgba(255,255,255,0.08); border-radius:10px;
            display:flex; align-items:center; justify-content:center; font-size:16px; color:#ce93d8; flex-shrink:0;
        }
        .left-panel .features .feature-item span { font-weight:500; }

        /* Panel derecho */
        .right-panel { flex:1; padding:50px 45px; display:flex; flex-direction:column; justify-content:center; background:#fff; }
        .right-panel .login-header { text-align:center; margin-bottom:30px; }
        .right-panel .login-header .icon {
            width:70px; height:70px; background:linear-gradient(135deg,var(--primary),var(--primary-dark));
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            margin:0 auto 18px; font-size:30px; color:#fff; box-shadow:0 8px 25px rgba(142,36,170,0.25);
        }
        .right-panel .login-header h3 { font-size:24px; font-weight:700; color:#1a1a2e; margin-bottom:5px; }
        .right-panel .login-header p { color:#999; font-size:14px; }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:#555; margin-bottom:6px; }
        .input-group-custom { position:relative; }
        .input-group-custom .input-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#bbb; font-size:16px; z-index:1; }
        .input-group-custom input {
            width:100%; padding:13px 14px 13px 44px; border:2px solid #e8e8e8; border-radius:12px;
            font-size:14px; transition:all 0.3s; background:#f8f9fa;
        }
        .input-group-custom input[type="password"] { padding-right:44px; }
        .input-group-custom input:focus { border-color:var(--primary); background:#fff; box-shadow:0 0 0 4px var(--primary-light); outline:none; }
        .toggle-password {
            position:absolute; right:14px; top:50%; transform:translateY(-50%);
            cursor:pointer; color:#bbb; font-size:18px; z-index:1; transition:color 0.3s; background:none; border:none; padding:0; line-height:1;
        }
        .toggle-password:hover { color:var(--primary); }
        .btn-login {
            width:100%; padding:14px; background:linear-gradient(135deg,var(--primary),var(--primary-dark));
            color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:600;
            cursor:pointer; transition:all 0.3s; margin-top:5px;
        }
        .btn-login:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(142,36,170,0.35); }
        .btn-login:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
        .alert-error {
            background:#fef2f2; border:1px solid #fecaca; color:#dc2626;
            padding:12px 16px; border-radius:12px; font-size:14px; margin-bottom:20px; display:flex; align-items:center; gap:10px;
        }
        .alert-error i { font-size:18px; }
        .login-footer { text-align:center; margin-top:25px; padding-top:20px; border-top:1px solid #f0f0f0; }
        .login-footer p { color:#bbb; font-size:12px; }
        .spinner { display:none; width:20px; height:20px; border:3px solid rgba(255,255,255,0.3); border-top-color:#fff; border-radius:50%; animation:spin 0.8s linear infinite; margin:0 auto; }
        @keyframes spin { to { transform:rotate(360deg); } }
        @media (max-width:768px) {
            .login-wrapper { flex-direction:column; max-width:450px; min-height:auto; }
            .left-panel { padding:35px 30px; }
            .left-panel h1 { font-size:24px; }
            .right-panel { padding:35px 30px; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- IZQUIERDA: Descripción -->
        <div class="left-panel">
            <div class="content">
                <div class="brand">
                    <div class="logo-icon"><i class="fas fa-cubes"></i></div>
                    <div class="logo-text">
                        <h2>SIG</h2>
                        <span>Sistema Integral de Gestión</span>
                    </div>
                </div>
                <h1>Control total de tu <span class="highlight">negocio</span></h1>
                <p>Administra ventas, inventario, facturación electrónica DIAN y más desde un solo lugar. Optimiza la gestión de tu empresa o emprendimiento con reportes en tiempo real y control de stock.</p>
                <div class="features">
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fas fa-cash-register"></i></div>
                        <div><span>Punto de Venta</span><br><small style="color:rgba(255,255,255,0.5)">Facturación rápida, múltiples métodos de pago</small></div>
                    </div>
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div><span>Facturación Electrónica DIAN</span><br><small style="color:rgba(255,255,255,0.5)">Emite facturas con CUFE y QR</small></div>
                    </div>
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fas fa-warehouse"></i></div>
                        <div><span>Inventario en Tiempo Real</span><br><small style="color:rgba(255,255,255,0.5)">Stock, lotes y alertas de mínimo</small></div>
                    </div>
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fas fa-chart-line"></i></div>
                        <div><span>Reportes y Estadísticas</span><br><small style="color:rgba(255,255,255,0.5)">Ventas, ganancias, productos top</small></div>
                    </div>
                    <div class="feature-item">
                        <div class="fi-icon"><i class="fas fa-shield-alt"></i></div>
                        <div><span>Seguro y Confiable</span><br><small style="color:rgba(255,255,255,0.5)">Datos protegidos, accesos por roles</small></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DERECHA: Login -->
        <div class="right-panel">
            <div class="login-header">
                <div class="icon"><i class="fas fa-user-lock"></i></div>
                <h3>Bienvenido</h3>
                <p>Ingrese sus credenciales para acceder</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= \SIG\Core\View::esc($error) ?></span>
            </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="<?= ($basePath ?? '') ?>/login" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= \SIG\Core\Request::generateCsrfToken() ?>">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <div class="input-group-custom">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="form-group">
                    <label for="clave">Contraseña</label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="clave" name="clave" placeholder="Ingrese su contraseña" required autocomplete="current-password">
                        <button type="button" class="toggle-password" id="toggleClave" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-login" id="btnLogin">
                    <span id="btnText">Ingresar al Sistema</span>
                    <div class="spinner" id="spinner"></div>
                </button>
            </form>
            <div class="login-footer">
                <p>&copy; <?= date('Y') ?> SIG v1.0 - Todos los derechos reservados</p>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(function() {
        $('#usuario').focus();
        /* Toggle visibilidad de contraseña */
        $('#toggleClave').on('click', function() {
            var $input = $('#clave');
            var $icon = $(this).find('i');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                $(this).attr('aria-label', 'Ocultar contraseña');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                $(this).attr('aria-label', 'Mostrar contraseña');
            }
        });
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            var $btn=$('#btnLogin'),$spin=$('#spinner'),$text=$('#btnText');
            $btn.prop('disabled',true);$text.hide();$spin.show();
            $.ajax({
                url:'<?= ($basePath ?? '') ?>/login', method:'POST',
                data:$(this).serialize(), dataType:'json',
                success:function(r){if(r.success&&r.data?.redirect)window.location.href=r.data.redirect;else location.reload();},
                error:function(){location.reload();}
            });
        });
        $(document).on('keypress',function(e){if(e.key==='Enter')$('#loginForm').submit();});
    });
    </script>
</body>
</html>
