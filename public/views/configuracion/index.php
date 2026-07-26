<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-cog text-primary me-2"></i>Configuración</h4>
                <p class="text-muted mb-0">Administración del sistema</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="configTabs">
    <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('configuracion.empresa')): ?>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'empresa' ? 'active' : '' ?>" href="<?= $basePath ?>/configuracion?tab=empresa">
            <i class="fas fa-building me-2"></i>Empresa
        </a>
    </li>
    <?php endif; ?>
    <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('configuracion.fe.ver')): ?>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'fe' ? 'active' : '' ?>" href="<?= $basePath ?>/configuracion?tab=fe">
            <i class="fas fa-cloud-upload-alt me-2"></i>Fact. Electrónica
        </a>
    </li>
    <?php endif; ?>
    <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('configuracion.rangos.ver')): ?>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'rangos' ? 'active' : '' ?>" href="<?= $basePath ?>/configuracion?tab=rangos">
            <i class="fas fa-sort-numeric-up me-2"></i>Rangos
        </a>
    </li>
    <?php endif; ?>
    <?php if (\SIG\Middleware\RoleMiddleware::hasPermission('configuracion.usuarios')): ?>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'usuarios' ? 'active' : '' ?>" href="<?= $basePath ?>/configuracion?tab=usuarios">
            <i class="fas fa-users-cog me-2"></i>Usuarios
        </a>
    </li>
    <?php endif; ?>
</ul>

<?php if ($tab === 'empresa'): ?>
<!-- ====== TAB EMPRESA ====== -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-building me-2"></i>Datos de la Empresa</h5>
    </div>
    <div class="card-body">
        <form id="formEmpresa">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">NIT</label>
                    <input type="text" class="form-control" name="nit" value="<?= \SIG\Core\View::esc($empresa['nit'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Razón Social</label>
                    <input type="text" class="form-control" name="nombre" value="<?= \SIG\Core\View::esc($empresa['nombre'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Dirección</label>
                    <input type="text" class="form-control" name="direccion" value="<?= \SIG\Core\View::esc($empresa['direccion'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" value="<?= \SIG\Core\View::esc($empresa['telefono'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= \SIG\Core\View::esc($empresa['email'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <hr class="my-2">
                    <div class="form-check form-switch form-check-lg">
                        <input class="form-check-input" type="checkbox" name="mostrar_fe" id="chkMostrarFE" value="1" <?= ($empresa['mostrar_fe'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="chkMostrarFE">
                            <i class="fas fa-cloud-upload-alt text-info me-1"></i>
                            Mostrar botón de <strong>Factura Electrónica</strong> en el POS
                        </label>
                        <br><small class="text-muted">Si desactivas esta opción, los usuarios no verán el botón de factura electrónica al facturar. Solo aplica si el negocio no requiere facturación electrónica DIAN.</small>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($tab === 'fe'): ?>
<!-- ====== TAB FACTURACIÓN ELECTRÓNICA ====== -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="fas fa-cloud-upload-alt me-2"></i>Facturación Electrónica (DIAN)</h5>
        <button class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="testConexionFE()">
            <i class="fas fa-plug me-1"></i>Probar Conexión
        </button>
    </div>
    <div class="card-body">
        <form id="formFE">
            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-certificate me-2"></i>Datos DIAN</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NIT Empresa</label>
                    <input type="text" class="form-control" name="nit" value="<?= \SIG\Core\View::esc($fe_config['nit'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Software ID</label>
                    <input type="text" class="form-control" name="software_id" value="<?= \SIG\Core\View::esc($fe_config['software_id'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Clave Certificado</label>
                    <input type="password" class="form-control" name="clave_certificado" value="<?= \SIG\Core\View::esc($fe_config['clave_certificado'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Llave Envío</label>
                    <input type="password" class="form-control" name="llave_envio" value="<?= \SIG\Core\View::esc($fe_config['llave_envio'] ?? '') ?>">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-key me-2"></i>API</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Usuario API</label>
                    <input type="text" class="form-control" name="usuario_api" value="<?= \SIG\Core\View::esc($fe_config['usuario_api'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Llave API</label>
                    <input type="password" class="form-control" name="llave_api" value="<?= \SIG\Core\View::esc($fe_config['llave_api'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">URL API</label>
                    <input type="text" class="form-control" name="url_api" value="<?= \SIG\Core\View::esc($fe_config['url_api'] ?? '') ?>">
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-file-invoice me-2"></i>Resolución DIAN</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Prefijo</label>
                    <input type="text" class="form-control" name="prefijo" value="<?= \SIG\Core\View::esc($fe_config['prefijo'] ?? 'SD30') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Resolución</label>
                    <input type="text" class="form-control" name="resolucion" value="<?= \SIG\Core\View::esc($fe_config['resolucion'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Rango Inicial</label>
                    <input type="number" class="form-control" name="rango_inicio" value="<?= \SIG\Core\View::esc($fe_config['rango_inicio'] ?? '1') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Rango Final</label>
                    <input type="number" class="form-control" name="rango_fin" value="<?= \SIG\Core\View::esc($fe_config['rango_fin'] ?? '2000') ?>">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar Configuración FE</button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($tab === 'rangos'): ?>
<!-- ====== TAB RANGOS ====== -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="fas fa-sort-numeric-up me-2"></i>Rangos de Facturación</h5>
        <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="abrirModalRango()"><i class="fas fa-plus me-1"></i>Nuevo Rango</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Tipo</th><th>Prefijo</th><th>Inicio</th><th>Fin</th><th>Resolución</th><th>Activo</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($rangos)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay rangos configurados</td></tr>
                    <?php else: foreach ($rangos as $r): ?>
                    <tr>
                        <td><span class="badge bg-<?= $r['tipo'] === 'ELECTRONICA' ? 'info' : 'secondary' ?>"><?= $r['tipo'] ?></span></td>
                        <td><?= \SIG\Core\View::esc($r['prefijo'] ?: '-') ?></td>
                        <td class="fw-bold"><?= $r['inicio'] ?></td>
                        <td class="fw-bold"><?= $r['fin'] ?></td>
                        <td><small><?= \SIG\Core\View::esc($r['resolucion'] ?: '-') ?></small></td>
                        <td><?= $r['activo'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="abrirModalRango(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Rangos -->
<div class="modal fade" id="modalRango" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalRangoTitle"><i class="fas fa-sort-numeric-up me-2"></i>Nuevo Rango</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRango">
                <input type="hidden" name="id_rango" id="r_id" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo" id="r_tipo" required>
                                <option value="NORMAL">Normal</option>
                                <option value="ELECTRONICA">Electrónica</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Activo</label>
                            <select class="form-select" name="activo" id="r_activo">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Prefijo</label>
                            <input type="text" class="form-control" name="prefijo" id="r_prefijo" placeholder="Ej: SD30">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Inicio <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="inicio" id="r_inicio" required min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fin <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="fin" id="r_fin" required min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Resolución DIAN</label>
                            <input type="text" class="form-control" name="resolucion" id="r_resolucion" placeholder="Número de resolución">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar Rango</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php elseif ($tab === 'usuarios'): ?>
<!-- ====== TAB USUARIOS ====== -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0"><i class="fas fa-users-cog me-2"></i>Usuarios del Sistema</h5>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i class="fas fa-user-plus me-1"></i>Nuevo Usuario
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Vigencia</th><th>Último Login</th><th style="width:80px">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios ?? [] as $u): 
                        $roles = [0 => 'Admin', 1 => 'Cajero', 2 => 'Inventario', 3 => 'Superadmin'];
                        $rolClass = [0 => 'primary', 1 => 'info', 2 => 'secondary', 3 => 'dark'];
                        $estados = [0 => 'Activo', 1 => 'Inactivo', 2 => 'Bloqueado'];
                        $estadoClass = [0 => 'success', 1 => 'warning', 2 => 'danger'];
                    ?>
                    <tr>
                        <td><strong><?= \SIG\Core\View::esc($u['nombre_usuario']) ?></strong></td>
                        <td><?= \SIG\Core\View::esc($u['nombre_completo'] ?: '-') ?></td>
                        <td><?= \SIG\Core\View::esc($u['email'] ?: '-') ?></td>
                        <td><span class="badge bg-<?= $rolClass[$u['tipo']] ?? 'secondary' ?>"><?= $roles[$u['tipo']] ?? '?' ?></span></td>
                        <td><span class="badge bg-<?= $estadoClass[$u['estado']] ?? 'secondary' ?>"><?= $estados[$u['estado']] ?? '?' ?></span></td>
                        <td><small><?= $u['fecha_inicial'] ?> → <?= $u['fecha_final'] ?></small></td>
                        <td><small class="text-muted"><?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca' ?></small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-user me-2"></i><span id="modalUserTitle">Nuevo Usuario</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario">
                <input type="hidden" name="id_usuario" id="u_id" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre_usuario" id="u_nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rol</label>
                            <select class="form-select" name="tipo" id="u_tipo">
                                <?php if (\SIG\Middleware\RoleMiddleware::hasRole(3)): ?>
                                <option value="3">Superadministrador</option>
                                <option value="0">Administrador</option>
                                <?php endif; ?>
                                <option value="1" selected>Cajero</option>
                                <option value="2">Inventario</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre Completo</label>
                            <input type="text" class="form-control" name="nombre_completo" id="u_nc">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" id="u_email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control" name="password" id="u_pass" placeholder="Dejar vacío para mantener">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Inicial</label>
                            <input type="date" class="form-control" name="fecha_inicial" id="u_fi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Final</label>
                            <input type="date" class="form-control" name="fecha_final" id="u_ff">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <select class="form-select" name="estado" id="u_estado">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                                <option value="2">Bloqueado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// ===== EMPRESA =====
$('#formEmpresa').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    $.ajax({
        url: '<?= $basePath ?>/configuracion/empresa/guardar', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: function(r) { if (r.success) PNotify.success({text: r.message}); else PNotify.error({text: r.message}); }
    });
});

// ===== FACTURACIÓN ELECTRÓNICA =====
$('#formFE').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    $.ajax({
        url: '<?= $basePath ?>/configuracion/fe/guardar', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: function(r) {
            if (r.success) PNotify.success({text: r.message});
            else PNotify.error({text: r.message});
        }
    });
});

function testConexionFE() {
    $.ajax({
        url: '<?= $basePath ?>/configuracion/fe/test', method: 'POST', contentType: 'application/json',
        success: function(r) {
            if (r.success) {
                PNotify.success({text: '✅ Conexión exitosa - ' + (r.data?.message || 'API responde correctamente')});
            } else {
                PNotify.error({text: '❌ ' + r.message});
            }
        },
        error: function() {
            PNotify.error({text: '❌ Error de conexión con la API'});
        }
    });
}

// ===== RANGOS (Modal) =====
function abrirModalRango(r) {
    if (r && r.id_rango) {
        // Editar
        $('#modalRangoTitle').html('<i class="fas fa-edit me-2"></i>Editar Rango');
        $('#r_id').val(r.id_rango);
        $('#r_tipo').val(r.tipo);
        $('#r_activo').val(r.activo);
        $('#r_prefijo').val(r.prefijo || '');
        $('#r_inicio').val(r.inicio);
        $('#r_fin').val(r.fin);
        $('#r_resolucion').val(r.resolucion || '');
    } else {
        // Nuevo
        $('#modalRangoTitle').html('<i class="fas fa-plus me-2"></i>Nuevo Rango');
        $('#formRango')[0].reset();
        $('#r_id').val(0);
        $('#r_tipo').val('NORMAL');
        $('#r_activo').val(1);
    }
    $('#modalRango').modal('show');
}

$('#formRango').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    data.inicio = parseInt(data.inicio);
    data.fin = parseInt(data.fin);
    data.activo = parseInt(data.activo);

    $.ajax({
        url: '<?= $basePath ?>/configuracion/rangos/guardar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalRango').modal('hide');
                PNotify.success({text: r.message});
                setTimeout(function(){ location.reload(); }, 1000);
            } else {
                PNotify.error({text: r.message});
            }
        }
    });
});

// ===== USUARIOS =====
$('#formUsuario').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    $.ajax({
        url: '<?= $basePath ?>/configuracion/usuarios/guardar', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: function(r) {
            if (r.success) { $('#modalUsuario').modal('hide'); PNotify.success({text: r.message}); setTimeout(function(){location.reload();},1000); }
            else PNotify.error({text: r.message});
        }
    });
});

function editarUsuario(u) {
    $('#modalUserTitle').text('Editar Usuario');
    $('#u_id').val(u.id_usuario);
    $('#u_nombre').val(u.nombre_usuario);
    $('#u_nc').val(u.nombre_completo || '');
    $('#u_email').val(u.email || '');
    $('#u_tipo').val(u.tipo);
    $('#u_estado').val(u.estado);
    $('#u_fi').val(u.fecha_inicial || '');
    $('#u_ff').val(u.fecha_final || '');
    $('#u_pass').val('').attr('placeholder', 'Dejar vacío para mantener');
    $('#modalUsuario').modal('show');
}

$('#modalUsuario').on('show.bs.modal', function() {
    if (!$('#u_id').val()) {
        $('#modalUserTitle').text('Nuevo Usuario');
        $('#formUsuario')[0].reset();
        $('#u_id').val(0);
        $('#u_pass').attr('placeholder', 'Requerido para nuevo usuario');
    }
});
$('#modalUsuario').on('hidden.bs.modal', function() { $('#formUsuario')[0].reset(); $('#u_id').val(0); });
</script>
