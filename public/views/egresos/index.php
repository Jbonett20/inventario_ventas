<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-money-bill-wave text-primary me-2"></i>Egresos</h4>
                <p class="text-muted mb-0">Registro de gastos</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT e.fecha AS Fecha, t.nombre AS Tipo, e.pagado_a AS PagadoA, e.valor AS Valor, e.observacion AS Observacion FROM vb_egresos e JOIN vb_tipos_egreso t ON e.id_tipo_egreso=t.id_tipo_egreso ORDER BY e.fecha DESC') ?>&nombre=egresos&titulo=Egresos" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT e.fecha AS Fecha, t.nombre AS Tipo, e.pagado_a AS PagadoA, e.valor AS Valor FROM vb_egresos e JOIN vb_tipos_egreso t ON e.id_tipo_egreso=t.id_tipo_egreso ORDER BY e.fecha DESC') ?>&nombre=egresos&titulo=Egresos" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalEgreso"><i class="fas fa-plus me-2"></i>Nuevo Egreso</button>
            </div>
        </div>
    </div>
</div>

<!-- Pestañas del módulo -->
<ul class="nav nav-pills flex-wrap gap-2 mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabEgr" type="button">
        <i class="fas fa-money-bill-wave me-1"></i>Egresos</button></li>
    <li class="nav-item"><button class="nav-link rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabTiposEgr" type="button" onclick="cargarTiposEgr()">
        <i class="fas fa-tags me-1"></i>Tipos de Egreso</button></li>
</ul>

<div class="tab-content">

<!-- ===================== TAB 1: EGRESOS ===================== -->
<div class="tab-pane fade show active" id="tabEgr" role="tabpanel">

<div class="row g-3 mb-4 align-items-end">
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Mes</label>
        <select class="form-select" id="filtroMes" onchange="cargarEgr(1)">
            <option value="">Todos los meses</option>
            <?php foreach ($meses as $m): ?>
            <option value="<?= $m['mes'] ?>"><?= $m['mes'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Desde</label>
        <input type="date" class="form-control" id="egr_desde">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Hasta</label>
        <input type="date" class="form-control" id="egr_hasta">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary flex-grow-1" onclick="cargarEgr(1)"><i class="fas fa-search me-1"></i>Buscar</button>
        <button class="btn btn-outline-secondary" onclick="limpiarFiltroEgr()" title="Limpiar filtros"><i class="fas fa-eraser"></i></button>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-warning bg-opacity-10 border-0 p-3 text-center">
            <small class="text-muted">Total filtrado</small>
            <strong id="totalEgr" class="text-warning fs-4">$0</strong>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Tipo</th><th>Pagado A</th><th>Valor</th><th>Observación</th><th>Usuario</th><th class="text-center">Acciones</th></tr>
                </thead>
                <tbody id="tbodyEgr">
                    <tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagEgr"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagEgr"></ul></nav>
</div>

</div><!-- /tabEgr -->

<!-- ===================== TAB 2: TIPOS DE EGRESO ===================== -->
<div class="tab-pane fade" id="tabTiposEgr" role="tabpanel">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0"><i class="fas fa-tags me-2"></i>Tipos de Egreso</h6>
                <small class="text-muted">Categorías para clasificar los gastos</small>
            </div>
            <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="abrirModalTipoEgr()">
                <i class="fas fa-plus me-1"></i>Nuevo Tipo
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:120px">Código</th>
                            <th>Nombre</th>
                            <th>Concepto</th>
                            <th class="text-center" style="width:110px">Estado</th>
                            <th class="text-center" style="width:130px">Egresos</th>
                            <th class="text-end" style="width:150px">Total</th>
                            <th class="text-center" style="width:120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTiposEgr">
                        <tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-warning mt-3 py-2 mb-0">
        <i class="fas fa-exclamation-triangle me-1"></i>
        Un tipo con egresos registrados <strong>no se puede eliminar</strong> (se perdería el historial). Desactívelo.
    </div>
</div>

</div><!-- /tab-content -->

<!-- Modal Tipos de Egreso -->
<div class="modal fade" id="modalTipoEgr" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-tag me-2"></i><span id="tipoEgrTitle">Nuevo Tipo de Egreso</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTipoEgr">
                <input type="hidden" id="te_id" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="te_codigo" maxlength="20" required autocomplete="off">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="te_nombre" maxlength="200" required autocomplete="off">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Concepto</label>
                            <textarea class="form-control" id="te_concepto" rows="2" placeholder="Descripción del tipo de gasto"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="te_activo" checked>
                                <label class="form-check-label fw-semibold" for="te_activo">Activo</label>
                            </div>
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

<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Nuevo Egreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEgreso">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_tipo_egreso" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id_tipo_egreso'] ?>"><?= \SIG\Core\View::esc($t['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Valor <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor" step="100" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pagado A</label>
                            <input type="text" class="form-control" name="pagado_a" placeholder="Nombre o entidad">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha</label>
                            <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observación</label>
                            <input type="text" class="form-control" name="observacion">
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

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let pe = 1;
function cargarEgr(page) {
    page = page || 1; pe = page;
    var mes = $('#filtroMes').val();
    var desde = $('#egr_desde').val();
    var hasta = $('#egr_hasta').val();
    $('#tbodyEgr').html('<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/egresos/listar', { page: page, mes: mes, desde: desde, hasta: hasta }, function(r) {
        if (!r.success) return; var d = r.data, html = '';
        if (!d.data.length) { html = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Sin egresos</td></tr>'; }
        else { $.each(d.data, function(i, e) {
            html += '<tr><td>' + e.fecha + '</td><td><span class="badge bg-secondary">' + escHtml(e.tipo_nombre) + '</span></td><td>' + escHtml(e.pagado_a||'-') + '</td><td class="fw-bold text-danger">-$' + formatoNumero(e.valor) + '</td><td><small>' + escHtml(e.observacion||'-') + '</small></td><td><small class="text-muted">' + escHtml(e.nombre_usuario||'-') + '</small></td>' +
                '<td class="text-center"><button class="btn btn-sm btn-outline-danger" onclick="borrarEgreso(' + e.id_egreso + ')" title="Eliminar"><i class="fas fa-trash"></i></button></td></tr>';
        }); }
        $('#tbodyEgr').html(html);
        $('#totalEgr').text('$' + formatoNumero(d.suma||0));
        $('#infoPagEgr').text(d.data.length + ' de ' + d.total + ' | Total: $' + formatoNumero(d.suma||0));
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarEgr(' + i + ');return false;">' + i + '</a></li>'; }
        $('#pagEgr').html(ph);
    });
}

function limpiarFiltroEgr() {
    $('#filtroMes').val('');
    $('#egr_desde').val('');
    $('#egr_hasta').val('');
    cargarEgr(1);
}

function borrarEgreso(id) {
    if (!confirm('¿Eliminar este egreso?\n\nEsta acción no se puede deshacer.')) return;
    $.ajax({
        url: BASE_URL + '/egresos/eliminar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_egreso: id }),
        success: function(r) {
            if (r.success) { PNotify.success({ text: r.message }); cargarEgr(pe); }
            else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al eliminar' }); }
    });
}

$('#formEgreso').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    $.ajax({url: BASE_URL + '/egresos/guardar', method:'POST', contentType:'application/json', data:JSON.stringify(data),
        success:function(r){if(r.success){$('#modalEgreso').modal('hide');cargarEgr(1);PNotify.success({text:r.message});}
            else{PNotify.error({text:r.message});}},
        error:function(xhr){PNotify.error({text:(xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar'});}});
});

// ============================================================
// TAB 2: TIPOS DE EGRESO
// ============================================================
var tiposEgrDatos = [];

function cargarTiposEgr() {
    $('#tbodyTiposEgr').html('<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/egresos/tipos', function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        tiposEgrDatos = r.data || [];
        var html = '';
        if (!tiposEgrDatos.length) {
            html = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay tipos de egreso</td></tr>';
        } else {
            $.each(tiposEgrDatos, function(i, t) {
                var nombre = escHtml(t.nombre).replace(/'/g, "\\'");
                var enUso = parseInt(t.egresos) > 0;
                html += '<tr>' +
                    '<td><span class="badge bg-light text-dark">' + escHtml(t.codigo) + '</span></td>' +
                    '<td><strong>' + escHtml(t.nombre) + '</strong></td>' +
                    '<td><small class="text-muted">' + escHtml(t.concepto || '-') + '</small></td>' +
                    '<td class="text-center"><span class="badge ' + (parseInt(t.activo) === 1 ? 'bg-success' : 'bg-secondary') + '">' +
                        (parseInt(t.activo) === 1 ? 'Activo' : 'Inactivo') + '</span></td>' +
                    '<td class="text-center">' + (t.egresos || 0) + '</td>' +
                    '<td class="text-end">$' + formatoNumero(t.total || 0) + '</td>' +
                    '<td class="text-center text-nowrap">' +
                        '<button class="btn btn-sm btn-outline-primary me-1" onclick="editarTipoEgr(' + t.id_tipo_egreso + ')" title="Editar"><i class="fas fa-edit"></i></button>' +
                        (enUso
                            ? '<button class="btn btn-sm btn-outline-secondary" disabled title="Tiene egresos: desactívelo en su lugar"><i class="fas fa-lock"></i></button>'
                            : '<button class="btn btn-sm btn-outline-danger" onclick="eliminarTipoEgr(' + t.id_tipo_egreso + ',\'' + nombre + '\')" title="Eliminar"><i class="fas fa-trash"></i></button>') +
                    '</td></tr>';
            });
        }
        $('#tbodyTiposEgr').html(html);
    });
}

function abrirModalTipoEgr() {
    $('#te_id').val(0);
    $('#te_codigo').val('');
    $('#te_nombre').val('');
    $('#te_concepto').val('');
    $('#te_activo').prop('checked', true);
    $('#tipoEgrTitle').text('Nuevo Tipo de Egreso');
    $('#modalTipoEgr').modal('show');
}

function editarTipoEgr(id) {
    var t = null;
    $.each(tiposEgrDatos, function(i, x) { if (parseInt(x.id_tipo_egreso) === parseInt(id)) { t = x; return false; } });
    if (!t) { PNotify.error({ text: 'No se encontró el tipo de egreso' }); return; }

    $('#te_id').val(id);
    $('#te_codigo').val(t.codigo || '');
    $('#te_nombre').val(t.nombre || '');
    $('#te_concepto').val(t.concepto || '');
    $('#te_activo').prop('checked', parseInt(t.activo) === 1);
    $('#tipoEgrTitle').text('Editar Tipo de Egreso');
    $('#modalTipoEgr').modal('show');
}

$('#formTipoEgr').on('submit', function(e) {
    e.preventDefault();
    var data = {
        id_tipo_egreso: parseInt($('#te_id').val()) || 0,
        codigo: ($('#te_codigo').val() || '').trim(),
        nombre: ($('#te_nombre').val() || '').trim(),
        concepto: $('#te_concepto').val(),
        activo: $('#te_activo').is(':checked') ? 1 : 0
    };
    if (!data.codigo || !data.nombre) { PNotify.error({ text: 'Código y nombre son obligatorios' }); return; }

    $.ajax({
        url: BASE_URL + '/egresos/tipos/guardar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalTipoEgr').modal('hide');
                PNotify.success({ text: r.message });
                cargarTiposEgr();
                recargarSelectTipos();
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar' }); }
    });
});

function eliminarTipoEgr(id, nombre) {
    if (!confirm('¿Eliminar el tipo de egreso "' + nombre + '"?')) return;
    $.ajax({
        url: BASE_URL + '/egresos/tipos/eliminar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_tipo_egreso: id }),
        success: function(r) {
            if (r.success) { PNotify.success({ text: r.message }); cargarTiposEgr(); recargarSelectTipos(); }
            else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al eliminar' }); }
    });
}

/** Refresca el <select> de tipos del modal "Nuevo Egreso" */
function recargarSelectTipos() {
    $.getJSON(BASE_URL + '/egresos/tipos', function(r) {
        if (!r.success) return;
        var sel = $('#formEgreso select[name="id_tipo_egreso"]');
        var actual = sel.val();
        var html = '<option value="">Seleccione...</option>';
        $.each(r.data, function(i, t) {
            if (parseInt(t.activo) === 1) {
                html += '<option value="' + t.id_tipo_egreso + '">' + escHtml(t.nombre) + '</option>';
            }
        });
        sel.html(html);
        if (actual) sel.val(actual);
    });
}

$(function(){ cargarEgr(1); });
</script>
