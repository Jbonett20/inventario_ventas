<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-balance-scale text-primary me-2"></i>Balance Diario</h4>
                <p class="text-muted mb-0">Resumen financiero del día</p>
            </div>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width:200px">
                    <span class="input-group-text bg-white"><i class="fas fa-calendar"></i></span>
                    <input type="date" class="form-control" id="filtroFechaBalance" value="<?= date('Y-m-d') ?>">
                </div>
                <button class="btn btn-outline-success btn-sm rounded-pill" onclick="exportarCsv()"><i class="fas fa-file-excel me-1"></i>CSV</button>
                <button class="btn btn-outline-danger btn-sm rounded-pill" onclick="exportarPdf()"><i class="fas fa-file-pdf me-1"></i>PDF</button>
                <button class="btn btn-warning btn-sm rounded-pill" onclick="cerrarDia()" id="btnCerrarDia"><i class="fas fa-lock me-1"></i>Cerrar Día</button>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de resumen -->
<div class="row g-3 mb-4" id="tarjetasBalance">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <small class="text-muted d-block">Base Diaria</small>
                <strong class="fs-5" id="balBase">$0</strong>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <small class="text-muted d-block">Ventas</small>
                <strong class="fs-5 text-success" id="balVentas">$0</strong>
                <small class="text-muted d-block" id="balVentasCant">0 facturas</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <small class="text-muted d-block">Vueltas (Cambio)</small>
                <strong class="fs-5 text-warning" id="balCambio">$0</strong>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <small class="text-muted d-block">Devoluciones</small>
                <strong class="fs-5 text-danger" id="balDevoluciones">$0</strong>
                <small class="text-muted d-block" id="balDevCant">0</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <small class="text-muted d-block">Egresos</small>
                <strong class="fs-5 text-danger" id="balEgresos">$0</strong>
                <small class="text-muted d-block" id="balEgrCant">0</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <small class="text-muted d-block">Saldo Esperado</small>
                <strong class="fs-5 text-primary" id="balSaldo">$0</strong>
                <span class="badge bg-secondary" id="balEstado" style="font-size:9px">Abierto</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabs de detalle -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-2">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link active" href="#" onclick="cargarDetalle('facturas');return false;" id="tabFacturas">Facturas</a></li>
            <li class="nav-item"><a class="nav-link" href="#" onclick="cargarDetalle('devoluciones');return false;" id="tabDevoluciones">Devoluciones</a></li>
            <li class="nav-item"><a class="nav-link" href="#" onclick="cargarDetalle('egresos');return false;" id="tabEgresos">Egresos</a></li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaDetalle">
                <thead class="table-light" id="theadDetalle"></thead>
                <tbody id="tbodyDetalle">
                    <tr><td colspan="10" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
var tipoDetalle = 'facturas';

function cargarBalance() {
    var fecha = $('#filtroFechaBalance').val();
    $.getJSON(BASE_URL + '/api/balance-diario/resumen', { fecha }, function(r) {
        if (!r.success) return;
        var b = r.data;
        $('#balBase').text('$' + formatoNumero(b.base_diaria));
        $('#balVentas').text('$' + formatoNumero(b.ventas.total));
        $('#balVentasCant').text(b.ventas.cantidad + ' facturas');
        $('#balCambio').text('$' + formatoNumero(b.ventas.total_cambio));
        $('#balDevoluciones').text('$' + formatoNumero(b.devoluciones.total));
        $('#balDevCant').text(b.devoluciones.cantidad + ' dev');
        $('#balEgresos').text('$' + formatoNumero(b.egresos.total));
        $('#balEgrCant').text(b.egresos.cantidad + ' egresos');
        $('#balSaldo').text('$' + formatoNumero(b.saldo_esperado));

        if (b.cerrado) {
            $('#balEstado').text('Cerrado').removeClass('bg-secondary').addClass('bg-success');
            $('#btnCerrarDia').prop('disabled', true).html('<i class="fas fa-check me-1"></i>Día Cerrado');
        } else {
            $('#balEstado').text('Abierto').removeClass('bg-success').addClass('bg-secondary');
            $('#btnCerrarDia').prop('disabled', false).html('<i class="fas fa-lock me-1"></i>Cerrar Día');
        }
    });
    cargarDetalle(tipoDetalle);
}

function cargarDetalle(tipo) {
    tipoDetalle = tipo;
    var fecha = $('#filtroFechaBalance').val();

    // Activar tab
    $('.nav-tabs .nav-link').removeClass('active');
    $('#tab' + tipo.charAt(0).toUpperCase() + tipo.slice(1)).addClass('active');

    $('#tbodyDetalle').html('<tr><td colspan="10" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');

    $.getJSON(BASE_URL + '/api/balance-diario/detalle', { fecha, tipo }, function(r) {
        if (!r.success) return;
        var data = r.data;
        var thead = '', html = '';

        if (!data.length) {
            html = '<tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay registros</td></tr>';
        }

        if (tipo === 'facturas') {
            thead = '<tr><th>#</th><th>Hora</th><th>Cliente</th><th>Tipo</th><th>Pago</th><th>Total</th><th>Cambio</th><th>Descuento</th><th>Estado</th></tr>';
            if (data.length) {
                $.each(data, function(i, f) {
                    var estBadge = f.estado === 'ACTIVA' ? 'bg-success' : 'bg-danger';
                    html += '<tr class="' + (f.estado === 'ANULADA' ? 'text-muted' : '') + '">' +
                        '<td class="fw-bold">' + (f.codigo||'') + '</td>' +
                        '<td>' + (f.hora ? f.hora.substring(0,5) : '') + '</td>' +
                        '<td>' + escHtml(f.cliente_nombre || '') + '</td>' +
                        '<td><span class="badge bg-secondary" style="font-size:10px">' + f.tipo + '</span></td>' +
                        '<td><small>' + f.tipo_pago + '</small></td>' +
                        '<td class="fw-bold">$' + formatoNumero(f.total) + '</td>' +
                        '<td>$' + formatoNumero(f.cambio) + '</td>' +
                        '<td>' + (parseFloat(f.descuento||0) > 0 ? '-$' + formatoNumero(f.descuento) : '-') + '</td>' +
                        '<td><span class="badge ' + estBadge + '" style="font-size:10px">' + f.estado + '</span></td></tr>';
                });
            }
        } else if (tipo === 'devoluciones') {
            thead = '<tr><th>Código</th><th>Factura</th><th>Cliente</th><th>Motivo</th><th>Total</th></tr>';
            if (data.length) {
                $.each(data, function(i, d) {
                    html += '<tr><td class="fw-bold">' + escHtml(d.codigo) + '</td>' +
                        '<td>#' + (d.factura_codigo || '-') + '</td>' +
                        '<td>' + escHtml(d.cliente_nombre || '-') + '</td>' +
                        '<td><small>' + escHtml((d.motivo||'').substring(0,50)) + '</small></td>' +
                        '<td class="fw-bold text-danger">$' + formatoNumero(d.total) + '</td></tr>';
                });
            }
        } else if (tipo === 'egresos') {
            thead = '<tr><th>Tipo</th><th>Pagado a</th><th>Valor</th><th>Observación</th></tr>';
            if (data.length) {
                $.each(data, function(i, e) {
                    html += '<tr><td>' + escHtml(e.tipo_egreso_nombre || '-') + '</td>' +
                        '<td>' + escHtml(e.pagado_a || '-') + '</td>' +
                        '<td class="fw-bold text-danger">$' + formatoNumero(e.valor) + '</td>' +
                        '<td><small>' + escHtml(e.observacion || '-') + '</small></td></tr>';
                });
            }
        }

        $('#theadDetalle').html(thead);
        $('#tbodyDetalle').html(html);
    });
}

function cerrarDia() {
    var fecha = $('#filtroFechaBalance').val();
    if (!confirm('¿Está seguro de cerrar el día ' + fecha + '? Una vez cerrado no podrá modificarse.')) return;
    $('#btnCerrarDia').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Cerrando...');
    $.ajax({
        url: BASE_URL + '/balance-diario/cerrar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ fecha }),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message });
                cargarBalance();
            } else {
                PNotify.error({ text: r.message });
                $('#btnCerrarDia').prop('disabled', false).html('<i class="fas fa-lock me-1"></i>Cerrar Día');
            }
        },
        error: function() {
            PNotify.error({ text: 'Error al cerrar el día' });
            $('#btnCerrarDia').prop('disabled', false).html('<i class="fas fa-lock me-1"></i>Cerrar Día');
        }
    });
}

function exportarPdf() {
    var fecha = $('#filtroFechaBalance').val();
    window.open(BASE_URL + '/balance-diario/exportar/pdf?fecha=' + fecha, '_blank');
}

function exportarCsv() {
    var fecha = $('#filtroFechaBalance').val();
    window.open(BASE_URL + '/balance-diario/exportar/csv?fecha=' + fecha, '_blank');
}

$('#filtroFechaBalance').on('change', cargarBalance);

$(function() { cargarBalance(); });
</script>
