<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-credit-card text-primary me-2"></i>Créditos</h4>
                <p class="text-muted mb-0">Gestión de ventas a crédito</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT c.codigo AS Codigo, cl.nombre AS Cliente, c.valor_total AS Total, c.saldo_pendiente AS Saldo, c.fecha_inicio AS Inicio, c.fecha_fin AS Fin, c.estado AS Estado FROM vb_creditos c JOIN vb_clientes cl ON c.id_cliente=cl.id_cliente ORDER BY c.created_at DESC') ?>&nombre=creditos&titulo=Creditos" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT c.codigo AS Codigo, cl.nombre AS Cliente, c.valor_total AS Total, c.saldo_pendiente AS Saldo, c.estado AS Estado FROM vb_creditos c JOIN vb_clientes cl ON c.id_cliente=cl.id_cliente ORDER BY c.created_at DESC') ?>&nombre=creditos&titulo=Creditos" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCredito"><i class="fas fa-plus me-2"></i>Nuevo Crédito</button>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de cartera (los inhabilitados NO suman cartera) -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block">Cartera por cobrar</small>
                <h4 class="fw-bold mb-0 text-danger" id="cred_cartera">$0</h4>
                <small class="text-muted">activos + vencidos</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block">Activos</small>
                <h4 class="fw-bold mb-0 text-warning" id="cred_activos">0</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block">Vencidos</small>
                <h4 class="fw-bold mb-0 text-danger" id="cred_vencidos">0</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <small class="text-muted d-block">Inhabilitados</small>
                <h4 class="fw-bold mb-0 text-secondary" id="cred_inhabilitados">0</h4>
                <small class="text-muted">no suman cartera</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-end p-3 pb-0">
            <div class="input-group input-group-sm" style="max-width:230px;">
                <span class="input-group-text"><i class="fas fa-filter"></i></span>
                <select class="form-select" id="filtroEstadoCred" onchange="cargarCred(1)">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO">Activos</option>
                    <option value="VENCIDO">Vencidos</option>
                    <option value="PAGADO">Pagados</option>
                    <option value="INHABILITADOS">Inhabilitados</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Código</th><th>Cliente</th><th>Total</th><th>Saldo</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody id="tbodyCred">
                    <tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagCred"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagCred"></ul></nav>
</div>

<!-- Modal Nuevo Crédito -->
<div class="modal fade" id="modalCredito" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-credit-card me-2"></i>Nuevo Crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCredito">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_cliente" required>
                                <option value="">Seleccione un cliente...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Valor Total</label>
                            <div class="input-group"><span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_total" step="100" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Aumento</label>
                            <div class="input-group"><span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_aumento" value="0" step="100" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin">
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

<!-- Modal Abono -->
<div class="modal fade" id="modalAbono" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-usd me-2"></i>Registrar Abono</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAbono">
                <input type="hidden" name="id_credito" id="ab_id">
                <div class="modal-body">
                    <p class="fw-bold" id="ab_info"></p>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" name="valor" id="ab_valor" step="100" min="100" required autofocus>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check me-2"></i>Abonar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Inhabilitar Crédito -->
<div class="modal fade" id="modalInhabilitar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-ban me-2"></i>Inhabilitar Crédito</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formInhabilitar">
                <input type="hidden" name="id_credito" id="inh_id">
                <div class="modal-body">
                    <p class="fw-bold" id="inh_info"></p>
                    <div class="alert alert-warning py-2 small">
                        <i class="fas fa-info-circle me-1"></i>El crédito deja de contar en la cartera y no permite abonos.
                        No se borra: se puede reactivar cuando quieras.
                    </div>
                    <label class="form-label fw-semibold">Motivo <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="motivo" id="inh_motivo" rows="3" required
                              placeholder="Ej: cliente no localizado / crédito duplicado / error de digitación"></textarea>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark px-4"><i class="fas fa-ban me-2"></i>Inhabilitar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let pc = 1;
function cargarCred(page) {
    page = page || 1; pc = page;
    var estado = $('#filtroEstadoCred').val() || '';
    $('#tbodyCred').html('<tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/creditos/listar', {page, estado}, function(r) {
        if (!r.success) return; var d = r.data, html = '';
        if (!d.data.length) { html = '<tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay créditos</td></tr>'; }
        else { $.each(d.data, function(i, c) {
            var estClass = 'secondary';
            if (c.estado === 'ACTIVO') estClass = 'warning';
            else if (c.estado === 'PAGADO') estClass = 'success';
            else if (c.estado === 'VENCIDO') estClass = 'danger';

            var inhabilitado = (c.estado === 'INHABILITADO');
            var motivoTxt = inhabilitado
                ? ' title="Motivo: ' + escHtml(c.motivo_inhabilitado || 'sin motivo') + ' (' + (c.fecha_inhabilitado || '') + (c.usuario_inhabilito ? ' - ' + escHtml(c.usuario_inhabilito) : '') + ')"'
                : '';

            // Botón de abono: bloqueado si está inhabilitado o ya pagado
            var btnAbono = (inhabilitado || c.estado === 'PAGADO')
                ? '<button class="btn btn-sm btn-outline-secondary" disabled title="No disponible"><i class="fas fa-hand-holding-usd"></i></button>'
                : '<button class="btn btn-sm btn-success" onclick="abrirAbono(' + c.id_credito + ',\'' + escHtml(c.codigo) + '\',' + c.saldo_pendiente + ')" title="Registrar abono"><i class="fas fa-hand-holding-usd"></i></button>';

            // Botón inhabilitar / reactivar
            var btnEstado = inhabilitado
                ? '<button class="btn btn-sm btn-outline-primary" onclick="reactivarCredito(' + c.id_credito + ',\'' + escHtml(c.codigo) + '\')" title="Reactivar crédito"><i class="fas fa-undo"></i></button>'
                : ((c.estado === 'PAGADO')
                    ? ''
                    : '<button class="btn btn-sm btn-outline-dark" onclick="abrirInhabilitar(' + c.id_credito + ',\'' + escHtml(c.codigo) + '\',' + c.saldo_pendiente + ')" title="Inhabilitar crédito"><i class="fas fa-ban"></i></button>');

            html += '<tr' + (inhabilitado ? ' class="table-secondary"' : '') + '>' +
                '<td><strong>' + escHtml(c.codigo) + '</strong></td><td>' + escHtml(c.cliente_nombre) + '</td>' +
                '<td class="fw-bold">$' + formatoNumero(c.valor_total) + '</td>' +
                '<td class="fw-bold ' + (c.saldo_pendiente > 0 ? 'text-danger' : 'text-success') + '">$' + formatoNumero(c.saldo_pendiente) + '</td>' +
                '<td>' + c.fecha_inicio + '</td><td>' + (c.fecha_fin || '-') + '</td>' +
                '<td><span class="badge bg-' + estClass + '"' + motivoTxt + '>' + c.estado + '</span></td>' +
                '<td class="text-nowrap">' + btnAbono + ' ' + btnEstado + '</td></tr>';
        }); }
        $('#tbodyCred').html(html);
        $('#infoPagCred').text(d.data.length + ' de ' + d.total);

        // Resumen de cartera
        if (d.resumen) {
            $('#cred_cartera').text('$' + formatoNumero(d.resumen.cartera));
            $('#cred_activos').text(d.resumen.activos);
            $('#cred_vencidos').text(d.resumen.vencidos);
            $('#cred_inhabilitados').text(d.resumen.inhabilitados);
        }

        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarCred(' + i + ');return false;">' + i + '</a></li>'; }
        $('#pagCred').html(ph);
    });
    // Cargar clientes en select
    if (!$('#formCredito select[name="id_cliente"] option').length > 1) {
        $.getJSON(BASE_URL + '/clientes/listar?page=1&perPage=200', function(r) {
            if (!r.success) return;
            var opts = '<option value="">Seleccione un cliente...</option>';
            $.each(r.data.data, function(i, c) { opts += '<option value="' + c.id_cliente + '">' + escHtml(c.nombre) + ' (' + c.documento + ')</option>'; });
            $('#formCredito select[name="id_cliente"]').html(opts);
        });
    }
}

$('#formCredito').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    $.ajax({url: BASE_URL + '/creditos/guardar', method:'POST', contentType:'application/json', data:JSON.stringify(data),
        success:function(r){if(r.success){$('#modalCredito').modal('hide');cargarCred(1);PNotify.success({text:r.message});}}});
});

function abrirAbono(id, codigo, saldo) {
    $('#ab_id').val(id); $('#ab_info').text(codigo + ' - Saldo: $' + formatoNumero(saldo)); $('#ab_valor').val(Math.min(1000, saldo));
    $('#modalAbono').modal('show');
}
$('#formAbono').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    $.ajax({url: BASE_URL + '/creditos/abonar', method:'POST', contentType:'application/json', data:JSON.stringify(data),
        success:function(r){if(r.success){$('#modalAbono').modal('hide');cargarCred(pc);PNotify.success({text:r.message});}
                            else{PNotify.error({text:r.message});}},
        error:function(xhr){PNotify.error({text:(xhr.responseJSON&&xhr.responseJSON.message)||'Error al abonar'});}});
});

// ===== INHABILITAR / REACTIVAR =====
function abrirInhabilitar(id, codigo, saldo) {
    $('#inh_id').val(id);
    $('#inh_info').text(codigo + ' - Saldo: $' + formatoNumero(saldo));
    $('#inh_motivo').val('');
    $('#modalInhabilitar').modal('show');
}

$('#formInhabilitar').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    if (!data.motivo || !data.motivo.trim()) { PNotify.error({ text: 'Escriba el motivo' }); return; }
    $.ajax({url: BASE_URL + '/creditos/inhabilitar', method:'POST', contentType:'application/json', data:JSON.stringify(data),
        success:function(r){if(r.success){$('#modalInhabilitar').modal('hide');cargarCred(pc);PNotify.success({text:r.message});}
                            else{PNotify.error({text:r.message});}},
        error:function(xhr){PNotify.error({text:(xhr.responseJSON&&xhr.responseJSON.message)||'Error'});}});
});

function reactivarCredito(id, codigo) {
    if (!confirm('¿Reactivar el crédito ' + codigo + '?\n\nVolverá a contar en la cartera y permitirá abonos.')) return;
    $.ajax({url: BASE_URL + '/creditos/reactivar', method:'POST', contentType:'application/json',
        data: JSON.stringify({ id_credito: id }),
        success:function(r){if(r.success){cargarCred(pc);PNotify.success({text:r.message});}
                            else{PNotify.error({text:r.message});}},
        error:function(xhr){PNotify.error({text:(xhr.responseJSON&&xhr.responseJSON.message)||'Error'});}});
}

$(function(){cargarCred(1);});
</script>
