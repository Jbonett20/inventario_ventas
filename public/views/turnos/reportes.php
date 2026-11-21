<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-cash-register text-primary me-2"></i>Reporte de turnos de caja</h4>
                <p class="text-muted mb-0">Turnos abiertos y cerrados, con sus ventas y diferencias de caja</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Agrupar por</label>
        <select class="form-select" id="filtroAgrupacion">
            <option value="dia">Día</option>
            <option value="mes" selected>Mes</option>
            <option value="anio">Año</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Desde</label>
        <input type="date" class="form-control" id="filtroInicio" value="<?= date('Y-01-01') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Hasta</label>
        <input type="date" class="form-control" id="filtroFin" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary flex-fill" type="button" onclick="cargarReporteTurnos()">
            <i class="fas fa-search me-2"></i>Consultar
        </button>
        <button class="btn btn-outline-secondary" type="button" onclick="imprimirReporteTurnos()" title="Imprimir">
            <i class="fas fa-print"></i>
        </button>
    </div>
</div>

<!-- Tarjetas resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <small class="text-muted">Turnos en el período</small>
            <h3 class="fw-bold mb-0" id="rptTurnos">0</h3>
            <small class="text-muted" id="rptTurnosDetalle">0 abiertos · 0 cerrados</small>
        </div></div>
    </div>
    <div class="col-md-4 col-6">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <small class="text-muted">Ventas del período</small>
            <h3 class="fw-bold mb-0 text-primary" id="rptVentas">$0</h3>
            <small class="text-muted" id="rptVentasDetalle">0 facturas</small>
        </div></div>
    </div>
    <div class="col-md-4 col-12">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <small class="text-muted">Diferencia de caja (turnos cerrados)</small>
            <h3 class="fw-bold mb-0" id="rptDiferencia">$0</h3>
            <small class="text-muted" id="rptContadoDetalle">Contado: $0</small>
        </div></div>
    </div>
</div>

<!-- Resumen agrupado -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-chart-column me-2"></i>Resumen por <span id="tituloAgrupacion">mes</span></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Período</th>
                                <th class="text-center">Turnos</th>
                                <th class="text-center">Abiertos</th>
                                <th class="text-center">Cerrados</th>
                                <th class="text-center">Facturas</th>
                                <th class="text-end">Ventas</th>
                                <th class="text-end">Devoluciones</th>
                                <th class="text-end">Egresos</th>
                                <th class="text-end">Contado</th>
                                <th class="text-end">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyGrupos">
                            <tr><td colspan="10" class="text-center text-muted py-4">Consultando...</td></tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td class="text-center" id="totTurnos">0</td>
                                <td class="text-center" id="totAbiertos">0</td>
                                <td class="text-center" id="totCerrados">0</td>
                                <td class="text-center" id="totFacturas">0</td>
                                <td class="text-end" id="totVentas">$0</td>
                                <td class="text-end" id="totDevoluciones">$0</td>
                                <td class="text-end" id="totEgresos">$0</td>
                                <td class="text-end" id="totContado">$0</td>
                                <td class="text-end" id="totDiferencia">$0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalle de turnos -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0"><i class="fas fa-list me-2"></i>Detalle de turnos</h5>
                <select class="form-select form-select-sm" id="filtroEstadoTurno" style="max-width:200px" onchange="filtrarTurnos()">
                    <option value="">Todos los estados</option>
                    <option value="ABIERTO">Solo abiertos</option>
                    <option value="CERRADO">Solo cerrados</option>
                </select>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cajero</th>
                                <th>Apertura</th>
                                <th>Cierre</th>
                                <th class="text-center">Facturas</th>
                                <th class="text-end">Ventas</th>
                                <th class="text-end">Devoluciones</th>
                                <th class="text-end">Egresos</th>
                                <th class="text-end">Esperado</th>
                                <th class="text-end">Contado</th>
                                <th class="text-end">Diferencia</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTurnos">
                            <tr><td colspan="14" class="text-center text-muted py-4">Consultando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var reporteTurnos = null;

function escTurno(s) { if (s === null || s === undefined) return ''; return $('<div>').text(s).html(); }
function moneyTurno(n) { return '$' + parseFloat(n || 0).toLocaleString('es-CO', {minimumFractionDigits:0, maximumFractionDigits:0}); }
function fechaHoraTurno(v) {
    if (!v) return '<span class="text-muted">—</span>';
    var partes = String(v).replace('T', ' ').split(' ');
    var f = partes[0].split('-');
    return (f.length === 3 ? f[2] + '/' + f[1] + '/' + f[0] : partes[0]) + (partes[1] ? ' ' + partes[1].substring(0, 5) : '');
}
function etiquetaPeriodo(periodo, agrupacion) {
    var meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    var p = String(periodo).split('-');
    if (agrupacion === 'dia' && p.length === 3) return p[2] + '/' + p[1] + '/' + p[0];
    if (agrupacion === 'mes' && p.length >= 2) return meses[parseInt(p[1], 10) - 1] + ' ' + p[0];
    return p[0];
}
function claseDiferencia(v) {
    if (v === null || v === undefined) return '';
    if (Math.abs(v) < 0.01) return 'text-success';
    return v < 0 ? 'text-danger' : 'text-warning';
}

function cargarReporteTurnos() {
    var agrupacion = $('#filtroAgrupacion').val();
    var inicio = $('#filtroInicio').val();
    var fin = $('#filtroFin').val();
    if (!inicio || !fin) {
        PNotify.error({ text: 'Selecciona el rango de fechas del reporte.' });
        return;
    }
    if (inicio > fin) {
        PNotify.error({ text: 'La fecha inicial no puede ser posterior a la fecha final.' });
        return;
    }

    $('#tituloAgrupacion').text(agrupacion === 'dia' ? 'día' : (agrupacion === 'mes' ? 'mes' : 'año'));
    $('#tbodyGrupos').html('<tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-1"></i>Cargando...</td></tr>');
    $('#tbodyTurnos').html('<tr><td colspan="14" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-1"></i>Cargando...</td></tr>');

    $.getJSON(BASE_URL + '/turnos/reportes/datos', {agrupacion: agrupacion, inicio: inicio, fin: fin})
        .done(function(r) {
            if (!r.success) {
                PNotify.error({ text: r.message || 'No se pudo consultar el reporte de turnos.' });
                return;
            }
            reporteTurnos = r.data;
            renderResumenTurnos(reporteTurnos);
            renderGruposTurnos(reporteTurnos);
            filtrarTurnos();
        })
        .fail(function(xhr) {
            var mensaje = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'No se pudo consultar el reporte de turnos.';
            $('#tbodyGrupos').html('<tr><td colspan="10" class="text-center text-danger py-4">' + escTurno(mensaje) + '</td></tr>');
            $('#tbodyTurnos').html('<tr><td colspan="14" class="text-center text-danger py-4">' + escTurno(mensaje) + '</td></tr>');
            PNotify.error({ text: mensaje });
        });
}

function renderResumenTurnos(data) {
    var t = data.totales;
    $('#rptTurnos').text(t.turnos);
    $('#rptTurnosDetalle').text(t.turnos_abiertos + ' abiertos · ' + t.turnos_cerrados + ' cerrados');
    $('#rptVentas').text(moneyTurno(t.ventas_total));
    $('#rptVentasDetalle').text(t.ventas_cantidad + ' facturas');
    $('#rptDiferencia')
        .text(moneyTurno(t.diferencia))
        .removeClass('text-success text-danger text-warning')
        .addClass(claseDiferencia(t.diferencia));
    $('#rptContadoDetalle').text('Contado: ' + moneyTurno(t.efectivo_contado));
}

function renderGruposTurnos(data) {
    var html = '';
    if (!data.grupos.length) {
        html = '<tr><td colspan="10" class="text-center text-muted py-4">No hay turnos en el período seleccionado.</td></tr>';
    } else {
        $.each(data.grupos, function(i, g) {
            html += '<tr>' +
                '<td class="fw-semibold">' + escTurno(etiquetaPeriodo(g.periodo, data.agrupacion)) + '</td>' +
                '<td class="text-center">' + g.turnos + '</td>' +
                '<td class="text-center">' + g.turnos_abiertos + '</td>' +
                '<td class="text-center">' + g.turnos_cerrados + '</td>' +
                '<td class="text-center">' + g.ventas_cantidad + '</td>' +
                '<td class="text-end">' + moneyTurno(g.ventas_total) + '</td>' +
                '<td class="text-end">' + moneyTurno(g.devoluciones_total) + '</td>' +
                '<td class="text-end">' + moneyTurno(g.egresos_total) + '</td>' +
                '<td class="text-end">' + moneyTurno(g.efectivo_contado) + '</td>' +
                '<td class="text-end ' + claseDiferencia(g.diferencia) + '">' + moneyTurno(g.diferencia) + '</td>' +
                '</tr>';
        });
    }
    $('#tbodyGrupos').html(html);

    var t = data.totales;
    $('#totTurnos').text(t.turnos);
    $('#totAbiertos').text(t.turnos_abiertos);
    $('#totCerrados').text(t.turnos_cerrados);
    $('#totFacturas').text(t.ventas_cantidad);
    $('#totVentas').text(moneyTurno(t.ventas_total));
    $('#totDevoluciones').text(moneyTurno(t.devoluciones_total));
    $('#totEgresos').text(moneyTurno(t.egresos_total));
    $('#totContado').text(moneyTurno(t.efectivo_contado));
    $('#totDiferencia').text(moneyTurno(t.diferencia)).removeClass('text-success text-danger text-warning').addClass(claseDiferencia(t.diferencia));
}

function filtrarTurnos() {
    if (!reporteTurnos) return;
    var estado = $('#filtroEstadoTurno').val();
    var html = '';
    var mostrados = 0;
    $.each(reporteTurnos.turnos, function(i, t) {
        if (estado && t.estado !== estado) return;
        mostrados++;
        var cerrado = t.estado === 'CERRADO';
        var badge = cerrado
            ? '<span class="badge bg-success bg-opacity-10 text-success">Cerrado</span>'
            : '<span class="badge bg-warning bg-opacity-10 text-warning">Abierto</span>';
        var comprobante = cerrado
            ? '<a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="' +
              BASE_URL + '/facturacion/turno/' + encodeURIComponent(t.id_turno) + '/imprimir" title="Comprobante de cierre">' +
              '<i class="fas fa-file-pdf"></i></a>'
            : '<span class="text-muted">—</span>';
        html += '<tr>' +
            '<td class="fw-semibold">' + t.id_turno + '</td>' +
            '<td>' + escTurno(fechaHoraTurno(t.fecha)) + '</td>' +
            '<td>' + escTurno(t.cajero_nombre) + '</td>' +
            '<td>' + fechaHoraTurno(t.abierto_en) + '</td>' +
            '<td>' + fechaHoraTurno(t.cerrado_en) + '</td>' +
            '<td class="text-center">' + t.ventas_cantidad + '</td>' +
            '<td class="text-end">' + moneyTurno(t.ventas_total) + '</td>' +
            '<td class="text-end">' + moneyTurno(t.devoluciones_total) + '</td>' +
            '<td class="text-end">' + moneyTurno(t.egresos_total) + '</td>' +
            '<td class="text-end">' + (cerrado ? moneyTurno(t.efectivo_esperado) : '<span class="text-muted">—</span>') + '</td>' +
            '<td class="text-end">' + (cerrado ? moneyTurno(t.efectivo_contado) : '<span class="text-muted">—</span>') + '</td>' +
            '<td class="text-end ' + claseDiferencia(t.diferencia) + '">' + (cerrado ? moneyTurno(t.diferencia) : '<span class="text-muted">—</span>') + '</td>' +
            '<td class="text-center">' + badge + '</td>' +
            '<td class="text-center">' + comprobante + '</td>' +
            '</tr>';
    });
    if (!mostrados) {
        html = '<tr><td colspan="14" class="text-center text-muted py-4">No hay turnos con el filtro seleccionado.</td></tr>';
    }
    $('#tbodyTurnos').html(html);
}

function imprimirReporteTurnos() {
    if (!reporteTurnos) {
        PNotify.error({ text: 'Primero consulta el reporte.' });
        return;
    }

    var data = reporteTurnos;
    var t = data.totales;
    var esc = escTurno;
    var filasGrupos = '';
    $.each(data.grupos, function(i, g) {
        filasGrupos += '<tr><td>' + esc(etiquetaPeriodo(g.periodo, data.agrupacion)) + '</td>' +
            '<td class="center">' + g.turnos + '</td><td class="center">' + g.turnos_abiertos + '</td><td class="center">' + g.turnos_cerrados + '</td>' +
            '<td class="center">' + g.ventas_cantidad + '</td><td class="right">' + moneyTurno(g.ventas_total) + '</td>' +
            '<td class="right">' + moneyTurno(g.devoluciones_total) + '</td><td class="right">' + moneyTurno(g.egresos_total) + '</td>' +
            '<td class="right">' + moneyTurno(g.efectivo_contado) + '</td><td class="right">' + moneyTurno(g.diferencia) + '</td></tr>';
    });
    if (!filasGrupos) {
        filasGrupos = '<tr><td colspan="10" class="center">Sin turnos en el período.</td></tr>';
    }

    var filasTurnos = '';
    $.each(data.turnos, function(i, x) {
        var cerrado = x.estado === 'CERRADO';
        filasTurnos += '<tr><td>' + x.id_turno + '</td><td>' + esc(fechaHoraTurno(x.fecha)) + '</td><td>' + esc(x.cajero_nombre) + '</td>' +
            '<td>' + fechaHoraTurno(x.abierto_en) + '</td><td>' + fechaHoraTurno(x.cerrado_en) + '</td>' +
            '<td class="center">' + x.ventas_cantidad + '</td><td class="right">' + moneyTurno(x.ventas_total) + '</td>' +
            '<td class="right">' + moneyTurno(x.devoluciones_total) + '</td><td class="right">' + moneyTurno(x.egresos_total) + '</td>' +
            '<td class="right">' + (cerrado ? moneyTurno(x.efectivo_esperado) : '-') + '</td>' +
            '<td class="right">' + (cerrado ? moneyTurno(x.efectivo_contado) : '-') + '</td>' +
            '<td class="right">' + (cerrado ? moneyTurno(x.diferencia) : '-') + '</td>' +
            '<td class="center">' + (cerrado ? 'Cerrado' : 'Abierto') + '</td></tr>';
    });
    if (!filasTurnos) {
        filasTurnos = '<tr><td colspan="13" class="center">Sin turnos en el período.</td></tr>';
    }

    var html = '<!doctype html><html><head><meta charset="utf-8"><title>Reporte de turnos</title><style>' +
        'body{font-family:sans-serif;font-size:11px;color:#222}' +
        'h1{text-align:center;font-size:18px;margin:0 0 4px}h2{font-size:13px;margin:18px 0 6px}' +
        '.subtitle{text-align:center;color:#666;margin:0 0 16px}' +
        'table{width:100%;border-collapse:collapse;margin-bottom:10px}' +
        'th{background:#eee;text-align:left}th,td{padding:5px;border:1px solid #ccc}' +
        '.right{text-align:right}.center{text-align:center}.total{font-weight:bold;background:#f5f5f5}' +
        '</style></head><body>';
    html += '<h1>Reporte de turnos de caja</h1>';
    html += '<p class="subtitle">Del ' + esc(data.inicio) + ' al ' + esc(data.fin) + ' · Agrupado por ' +
        (data.agrupacion === 'dia' ? 'día' : (data.agrupacion === 'mes' ? 'mes' : 'año')) + '</p>';
    html += '<p><strong>Turnos:</strong> ' + t.turnos + ' (' + t.turnos_abiertos + ' abiertos, ' + t.turnos_cerrados + ' cerrados) · ' +
        '<strong>Ventas:</strong> ' + moneyTurno(t.ventas_total) + ' en ' + t.ventas_cantidad + ' facturas · ' +
        '<strong>Diferencia de caja:</strong> ' + moneyTurno(t.diferencia) + '</p>';
    html += '<h2>Resumen por período</h2>';
    html += '<table><thead><tr><th>Período</th><th class="center">Turnos</th><th class="center">Abiertos</th><th class="center">Cerrados</th>' +
        '<th class="center">Facturas</th><th class="right">Ventas</th><th class="right">Devoluciones</th><th class="right">Egresos</th>' +
        '<th class="right">Contado</th><th class="right">Diferencia</th></tr></thead><tbody>' + filasGrupos + '</tbody>' +
        '<tfoot><tr class="total"><td>Total</td><td class="center">' + t.turnos + '</td><td class="center">' + t.turnos_abiertos + '</td>' +
        '<td class="center">' + t.turnos_cerrados + '</td><td class="center">' + t.ventas_cantidad + '</td>' +
        '<td class="right">' + moneyTurno(t.ventas_total) + '</td><td class="right">' + moneyTurno(t.devoluciones_total) + '</td>' +
        '<td class="right">' + moneyTurno(t.egresos_total) + '</td><td class="right">' + moneyTurno(t.efectivo_contado) + '</td>' +
        '<td class="right">' + moneyTurno(t.diferencia) + '</td></tr></tfoot></table>';
    html += '<h2>Detalle de turnos</h2>';
    html += '<table><thead><tr><th>#</th><th>Fecha</th><th>Cajero</th><th>Apertura</th><th>Cierre</th><th class="center">Facturas</th>' +
        '<th class="right">Ventas</th><th class="right">Devoluciones</th><th class="right">Egresos</th><th class="right">Esperado</th>' +
        '<th class="right">Contado</th><th class="right">Diferencia</th><th class="center">Estado</th></tr></thead><tbody>' + filasTurnos + '</tbody></table>';
    html += '<p class="subtitle">Generado el ' + new Date().toLocaleString('es-CO') + '</p></body></html>';

    var ventana = window.open('', '_blank');
    if (!ventana) {
        PNotify.error({ text: 'Permite las ventanas emergentes para imprimir el reporte.' });
        return;
    }
    ventana.document.open();
    ventana.document.write(html);
    ventana.document.close();
    ventana.focus();
    ventana.print();
}

$(function() { cargarReporteTurnos(); });
</script>
