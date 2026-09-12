<?php
/**
 * Vista: Vendedores
 *
 * Tab 1: catálogo de vendedores (código único autogenerado, cédula, comisión)
 * Tab 2: reporte de ventas por vendedor en un rango de fechas + bono por %
 */
$puedeEditar = $puedeEditar ?? false;
$siguienteCodigo = $siguienteCodigo ?? '';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-user-tie text-primary me-2"></i>Vendedores</h4>
                <p class="text-muted mb-0">Registro de vendedores y control de ventas por vendedor</p>
            </div>
        </div>
    </div>
</div>

<!-- Pestañas del módulo -->
<ul class="nav nav-pills flex-wrap gap-2 mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabVend" type="button">
        <i class="fas fa-users me-1"></i>Vendedores</button></li>
    <li class="nav-item"><button class="nav-link rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabVendVentas" type="button" onclick="cargarReporteVend()">
        <i class="fas fa-chart-bar me-1"></i>Ventas por Vendedor</button></li>
</ul>

<div class="tab-content">

<!-- ===================== TAB 1: VENDEDORES ===================== -->
<div class="tab-pane fade show active" id="tabVend" role="tabpanel">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0"><i class="fas fa-list me-2"></i>Listado de Vendedores
                    <span class="badge bg-secondary ms-1" id="vendTotal">0</span>
                </h6>
                <small class="text-muted">El código se genera automáticamente (V0001, V0002...) y no se repite</small>
            </div>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" id="vendBuscar" placeholder="Buscar por nombre, cédula o código..." onkeyup="buscarVendDebounce()" style="width:250px;">
                <div class="form-check form-switch d-flex align-items-center ms-2" style="white-space:nowrap;">
                    <input class="form-check-input me-2" type="checkbox" id="vendSoloActivos" onchange="cargarVendedores()">
                    <label class="form-check-label small" for="vendSoloActivos">Solo activos</label>
                </div>
                <?php if ($puedeEditar): ?>
                <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="abrirModalVend()">
                    <i class="fas fa-plus me-1"></i>Nuevo Vendedor
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:100px">Código</th>
                            <th style="width:130px">Cédula</th>
                            <th>Nombre</th>
                            <th style="width:130px">Teléfono</th>
                            <th class="text-center" style="width:100px">Comisión</th>
                            <th class="text-center" style="width:90px">Ventas</th>
                            <th class="text-end" style="width:140px">Total Vendido</th>
                            <th class="text-center" style="width:100px">Estado</th>
                            <?php if ($puedeEditar): ?>
                            <th class="text-center" style="width:120px">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tbodyVend">
                        <tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== TAB 2: VENTAS POR VENDEDOR ===================== -->
<div class="tab-pane fade" id="tabVendVentas" role="tabpanel">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fecha Inicial</label>
                    <input type="date" class="form-control" id="rv_desde" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fecha Final</label>
                    <input type="date" class="form-control" id="rv_hasta" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Bono % <small class="text-muted">(opcional)</small></label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="rv_comision" min="0" max="100" step="0.5" placeholder="Según vendedor">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="rv_solo_ventas" checked onchange="cargarReporteVend()">
                        <label class="form-check-label small" for="rv_solo_ventas">Solo con ventas</label>
                    </div>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" onclick="cargarReporteVend()"><i class="fas fa-search me-2"></i>Calcular</button>
                    <button class="btn btn-outline-secondary" onclick="exportarReporteVend()" title="Exportar"><i class="fas fa-file-excel"></i></button>
                </div>
            </div>
            <div class="alert alert-info mt-3 mb-0 py-2">
                <i class="fas fa-info-circle me-1"></i>
                Si deja el <strong>Bono %</strong> vacío se usa la comisión configurada en cada vendedor.
                El bono se calcula sobre el <strong>total vendido</strong> (facturas activas).
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Vendedores con ventas</h6>
                    <h3 class="fw-bold mb-0" id="rvk_vendedores">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Facturas</h6>
                    <h3 class="fw-bold mb-0" id="rvk_ventas">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Vendido</h6>
                    <h3 class="fw-bold mb-0 text-primary" id="rvk_total">$0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Bonos</h6>
                    <h3 class="fw-bold mb-0 text-success" id="rvk_bono">$0</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2"></i>Ventas por vendedor en el rango</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vendedor</th>
                            <th style="width:100px">Código</th>
                            <th style="width:120px">Cédula</th>
                            <th class="text-center" style="width:90px">Facturas</th>
                            <th class="text-end" style="width:130px">Total Vendido</th>
                            <th class="text-end" style="width:120px">Promedio</th>
                            <th class="text-center" style="width:100px">% Bono</th>
                            <th class="text-end" style="width:130px">Bono</th>
                            <th class="text-center" style="width:110px">Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyRv">
                        <tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-chart-bar fa-2x d-block mb-2"></i>Seleccione el rango y pulse Calcular</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div><!-- /tab-content -->

<?php if ($puedeEditar): ?>
<!-- Modal Vendedor -->
<div class="modal fade" id="modalVend" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i><span id="vendModalTitle">Nuevo Vendedor</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formVend">
                <input type="hidden" id="v_id" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Código</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="v_codigo" maxlength="20" autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" onclick="generarCodigoVend()" title="Generar código"><i class="fas fa-sync"></i></button>
                            </div>
                            <small class="text-muted">Único. Se genera automáticamente si lo deja vacío.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cédula</label>
                            <input type="text" class="form-control" id="v_cedula" maxlength="20" autocomplete="off" placeholder="Documento del vendedor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="v_nombre" maxlength="200" required autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" class="form-control" id="v_telefono" maxlength="30" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="v_email" maxlength="100" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Comisión (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="v_comision" min="0" max="100" step="0.5" value="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Dirección</label>
                            <input type="text" class="form-control" id="v_direccion" maxlength="200" autocomplete="off">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="v_activo" checked>
                                <label class="form-check-label fw-semibold" for="v_activo">Activo</label>
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
<?php endif; ?>

<!-- Modal Detalle ventas del vendedor -->
<div class="modal fade" id="modalVendDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-file-invoice me-2"></i><span id="vd_titulo">Facturas del vendedor</span></h5>
                    <small class="text-muted" id="vd_subtitulo"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height:520px;overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light" style="position:sticky;top:0;">
                            <tr>
                                <th>Fecha</th><th># Factura</th><th>Cliente</th>
                                <th class="text-center">Tipo</th><th>Pago</th>
                                <th class="text-end">Total</th><th class="text-end">Ganancia</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyVendDetalle"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <strong class="me-auto" id="vd_total"></strong>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
var puedeEditarVend = <?= $puedeEditar ? 'true' : 'false' ?>;
var vendedoresDatos = [];
var timerVendBusq;

function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }

// ============================================================
// TAB 1: CATÁLOGO DE VENDEDORES
// ============================================================
function cargarVendedores() {
    var buscar = $('#vendBuscar').val() || '';
    var activos = $('#vendSoloActivos').is(':checked') ? 1 : 0;
    $('#tbodyVend').html('<tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/vendedores/listar', { buscar: buscar, activos: activos }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        vendedoresDatos = r.data || [];
        pintarVendedores(vendedoresDatos);
    });
}

function pintarVendedores(lista) {
    var colspan = puedeEditarVend ? 9 : 8;
    var html = '';

    if (!lista.length) {
        html = '<tr><td colspan="' + colspan + '" class="text-center text-muted py-5"><i class="fas fa-user-tie fa-2x d-block mb-2"></i>No hay vendedores registrados</td></tr>';
    } else {
        $.each(lista, function(i, v) {
            var nombre = escHtml(v.nombre).replace(/'/g, "\\'");
            html += '<tr>' +
                '<td><span class="badge bg-primary">' + escHtml(v.codigo) + '</span></td>' +
                '<td>' + escHtml(v.cedula || '-') + '</td>' +
                '<td><strong>' + escHtml(v.nombre) + '</strong>' +
                    (v.email ? '<br><small class="text-muted"><i class="fas fa-envelope me-1"></i>' + escHtml(v.email) + '</small>' : '') + '</td>' +
                '<td>' + escHtml(v.telefono || '-') + '</td>' +
                '<td class="text-center"><span class="badge bg-light text-dark">' + formatoNumero(v.comision) + ' %</span></td>' +
                '<td class="text-center">' + (v.ventas || 0) + '</td>' +
                '<td class="text-end fw-bold">$' + formatoNumero(v.total_vendido || 0) + '</td>' +
                '<td class="text-center"><span class="badge ' + (parseInt(v.activo) === 1 ? 'bg-success' : 'bg-secondary') + '">' +
                    (parseInt(v.activo) === 1 ? 'Activo' : 'Inactivo') + '</span></td>';

            if (puedeEditarVend) {
                html += '<td class="text-center text-nowrap">' +
                    '<button class="btn btn-sm btn-outline-primary me-1" onclick="editarVend(' + v.id_vendedor + ')" title="Editar"><i class="fas fa-edit"></i></button>' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="eliminarVend(' + v.id_vendedor + ',\'' + nombre + '\')" title="Eliminar"><i class="fas fa-trash"></i></button>' +
                    '</td>';
            }
            html += '</tr>';
        });
    }

    $('#tbodyVend').html(html);
    $('#vendTotal').text(lista.length);
}

function buscarVendDebounce() {
    clearTimeout(timerVendBusq);
    timerVendBusq = setTimeout(cargarVendedores, 350);
}

function generarCodigoVend() {
    $.getJSON(BASE_URL + '/vendedores/siguiente-codigo', function(r) {
        if (r.success) $('#v_codigo').val(r.data.codigo);
    });
}

function abrirModalVend() {
    $('#v_id').val(0);
    $('#v_codigo').val('');
    $('#v_cedula').val('');
    $('#v_nombre').val('');
    $('#v_telefono').val('');
    $('#v_email').val('');
    $('#v_direccion').val('');
    $('#v_comision').val(0);
    $('#v_activo').prop('checked', true);
    $('#vendModalTitle').text('Nuevo Vendedor');
    // Traer el siguiente código real (el del render puede quedar obsoleto)
    generarCodigoVend();
    $('#modalVend').modal('show');
}

function editarVend(id) {
    var v = null;
    $.each(vendedoresDatos, function(i, x) { if (parseInt(x.id_vendedor) === parseInt(id)) { v = x; return false; } });
    if (!v) { PNotify.error({ text: 'No se encontró el vendedor' }); return; }

    $('#v_id').val(id);
    $('#v_codigo').val(v.codigo || '');
    $('#v_cedula').val(v.cedula || '');
    $('#v_nombre').val(v.nombre || '');
    $('#v_telefono').val(v.telefono || '');
    $('#v_email').val(v.email || '');
    $('#v_direccion').val(v.direccion || '');
    $('#v_comision').val(v.comision || 0);
    $('#v_activo').prop('checked', parseInt(v.activo) === 1);
    $('#vendModalTitle').text('Editar Vendedor');
    $('#modalVend').modal('show');
}

$('#formVend').on('submit', function(e) {
    e.preventDefault();
    var data = {
        id_vendedor: parseInt($('#v_id').val()) || 0,
        codigo: ($('#v_codigo').val() || '').trim(),
        cedula: ($('#v_cedula').val() || '').trim(),
        nombre: ($('#v_nombre').val() || '').trim(),
        telefono: $('#v_telefono').val(),
        email: $('#v_email').val(),
        direccion: $('#v_direccion').val(),
        comision: parseFloat($('#v_comision').val()) || 0,
        activo: $('#v_activo').is(':checked') ? 1 : 0
    };
    if (!data.nombre) { PNotify.error({ text: 'El nombre es obligatorio' }); return; }

    $.ajax({
        url: BASE_URL + '/vendedores/guardar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalVend').modal('hide');
                PNotify.success({ text: r.message + (r.data && r.data.codigo ? ' (' + r.data.codigo + ')' : '') });
                cargarVendedores();
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar' }); }
    });
});

function eliminarVend(id, nombre) {
    if (!confirm('¿Eliminar el vendedor "' + nombre + '"?')) return;
    $.ajax({
        url: BASE_URL + '/vendedores/eliminar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_vendedor: id }),
        success: function(r) {
            if (r.success) { PNotify.success({ text: r.message }); cargarVendedores(); }
            else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al eliminar' }); }
    });
}

// ============================================================
// TAB 2: REPORTE DE VENTAS POR VENDEDOR
// ============================================================
function cargarReporteVend() {
    var desde = $('#rv_desde').val(), hasta = $('#rv_hasta').val();
    if (!desde || !hasta) { PNotify.error({ text: 'Seleccione las dos fechas' }); return; }

    var comision = $('#rv_comision').val();
    var soloVentas = $('#rv_solo_ventas').is(':checked') ? 1 : 0;

    $('#tbodyRv').html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');

    $.getJSON(BASE_URL + '/vendedores/ventas', { desde: desde, hasta: hasta, comision: comision, solo_con_ventas: soloVentas }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }

        var d = r.data, html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay ventas en ese rango de fechas</td></tr>';
        } else {
            $.each(d.data, function(i, v) {
                var esc = escHtml(v.nombre).replace(/'/g, "\\'");
                var tipoBadge = (v.tipo === 'REGISTRADO')
                    ? '<span class="badge bg-primary ms-1">Registrado</span>'
                    : '<span class="badge bg-secondary ms-1">Usuario</span>';
                html += '<tr>' +
                    '<td><strong>' + escHtml(v.nombre) + '</strong> ' + tipoBadge + '</td>' +
                    '<td><span class="badge bg-light text-dark">' + escHtml(v.codigo) + '</span></td>' +
                    '<td>' + escHtml(v.cedula || '-') + '</td>' +
                    '<td class="text-center">' + v.ventas + '</td>' +
                    '<td class="text-end fw-bold">$' + formatoNumero(v.total) + '</td>' +
                    '<td class="text-end">$' + formatoNumero(v.promedio) + '</td>' +
                    '<td class="text-center">' + formatoNumero(v.comision_aplicada) + ' %</td>' +
                    '<td class="text-end text-success fw-bold">$' + formatoNumero(v.bono) + '</td>' +
                    '<td class="text-center">' +
                        '<button class="btn btn-sm btn-outline-primary" onclick="verDetalleVend(\'' + v.tipo + '\',' + v.id + ',\'' + esc + '\')">' +
                            '<i class="fas fa-eye"></i></button>' +
                    '</td></tr>';
            });
        }
        $('#tbodyRv').html(html);

        $('#rvk_vendedores').text(d.totales.vendedores);
        $('#rvk_ventas').text(d.totales.ventas);
        $('#rvk_total').text('$' + formatoNumero(d.totales.total_vendido));
        $('#rvk_bono').text('$' + formatoNumero(d.totales.total_bono));
    });
}

function verDetalleVend(tipo, id, nombre) {
    var desde = $('#rv_desde').val(), hasta = $('#rv_hasta').val();
    $('#vd_titulo').text('Facturas de ' + nombre);
    $('#vd_subtitulo').text('Del ' + desde + ' al ' + hasta);
    $('#tbodyVendDetalle').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');
    $('#modalVendDetalle').modal('show');

    $.getJSON(BASE_URL + '/vendedores/detalle', { desde: desde, hasta: hasta, tipo: tipo, id: id }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }

        var html = '', total = 0;
        if (!r.data.length) {
            html = '<tr><td colspan="8" class="text-center text-muted py-4">Sin facturas en el rango</td></tr>';
        } else {
            $.each(r.data, function(i, f) {
                total += parseFloat(f.total) || 0;
                html += '<tr>' +
                    '<td><small>' + f.fecha + '<br>' + (f.hora || '') + '</small></td>' +
                    '<td><strong>' + escHtml(String(f.codigo)) + '</strong></td>' +
                    '<td>' + escHtml(f.cliente_nombre || 'Consumidor final') + '<br><small class="text-muted">' + escHtml(f.cliente_documento || '') + '</small></td>' +
                    '<td class="text-center"><span class="badge bg-light text-dark">' + escHtml(f.tipo) + '</span></td>' +
                    '<td><small>' + escHtml(f.tipo_pago) + '</small></td>' +
                    '<td class="text-end fw-bold">$' + formatoNumero(f.total) + '</td>' +
                    '<td class="text-end">$' + formatoNumero(f.ganancia) + '</td>' +
                    '<td class="text-center"><span class="badge ' + (f.estado === 'ACTIVA' ? 'bg-success' : 'bg-danger') + '">' + escHtml(f.estado) + '</span></td>' +
                    '</tr>';
            });
        }
        $('#tbodyVendDetalle').html(html);
        $('#vd_total').text('Total: $' + formatoNumero(total) + ' (' + r.data.length + ' factura(s))');
    });
}

function exportarReporteVend() {
    var desde = $('#rv_desde').val(), hasta = $('#rv_hasta').val();
    if (!desde || !hasta) { PNotify.error({ text: 'Seleccione las dos fechas' }); return; }

    var sql = "SELECT COALESCE(v.nombre, u.nombre_completo) AS Vendedor, " +
              "COALESCE(v.codigo, u.nombre_usuario) AS Codigo, v.cedula AS Cedula, " +
              "COUNT(f.id_factura) AS Facturas, COALESCE(SUM(f.total),0) AS Total " +
              "FROM vb_facturas f " +
              "LEFT JOIN vb_vendedores v ON f.id_vendedor_registrado = v.id_vendedor " +
              "LEFT JOIN vb_usuarios u ON f.id_vendedor = u.id_usuario " +
              "WHERE f.estado = 'ACTIVA' AND f.fecha BETWEEN '" + desde + "' AND '" + hasta + "' " +
              "GROUP BY Vendedor, Codigo, Cedula ORDER BY Total DESC";

    window.open(BASE_URL + '/exportar/csv?sql=' + encodeURIComponent(sql) +
                '&nombre=ventas_por_vendedor&titulo=Ventas por vendedor', '_blank');
}

$(function() { cargarVendedores(); });
</script>
