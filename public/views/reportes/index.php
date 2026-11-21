<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-chart-bar text-primary me-2"></i>Reportes</h4>
                <p class="text-muted mb-0">Análisis de ventas y estadísticas</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtro de fechas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Fecha Inicio</label>
        <input type="date" class="form-control" id="filtroInicio" value="<?= date('Y-m-01') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold small">Fecha Fin</label>
        <input type="date" class="form-control" id="filtroFin" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100" onclick="cargarReportes()"><i class="fas fa-search me-2"></i>Filtrar</button>
    </div>
</div>

<!-- Tarjetas resumen -->
<div class="row g-3 mb-4" id="resumenCards">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <small class="text-muted">Ventas</small>
            <h3 class="fw-bold mb-0" id="rptCantidad">0</h3>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <small class="text-muted">Total Vendido</small>
            <h3 class="fw-bold mb-0 text-primary" id="rptTotal">$0</h3>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <small class="text-muted">Ganancia</small>
            <h3 class="fw-bold mb-0 text-success" id="rptGanancia">$0</h3>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <small class="text-muted">Ticket Promedio</small>
            <h3 class="fw-bold mb-0 text-info" id="rptTicket">$0</h3>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <!-- Gráfico ventas diarias -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3"><h5 class="fw-bold mb-0"><i class="fas fa-chart-area me-2"></i>Ventas por Día</h5></div>
            <div class="card-body"><canvas id="chartVentas" height="250"></canvas></div>
        </div>
    </div>
    <!-- Métodos de pago -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3"><h5 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2"></i>Métodos de Pago</h5></div>
            <div class="card-body"><canvas id="chartPago" height="250"></canvas></div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0"><i class="fas fa-cash-register me-2"></i>Ventas diarias por cajero</h5>
                <div class="d-flex flex-wrap gap-2">
                    <input type="date" class="form-control form-control-sm" id="filtroFechaCajero" value="<?= date('Y-m-d') ?>">
                    <button class="btn btn-primary btn-sm text-nowrap" type="button" onclick="cargarVentasPorCajero()">
                        <i class="fas fa-search me-1"></i>Consultar
                    </button>
                    <button class="btn btn-outline-danger btn-sm text-nowrap" type="button" onclick="imprimirVentasPorCajero()">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Cajero</th><th class="text-center">Facturas activas</th><th class="text-end">Total vendido</th></tr>
                        </thead>
                        <tbody id="tbodyVentasCajero">
                            <tr><td colspan="3" class="text-center text-muted py-4">Selecciona una fecha para consultar.</td></tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr><td>Total general</td><td class="text-center" id="cantidadVentasCajero">0</td><td class="text-end" id="totalVentasCajero">$0</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top productos -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3"><h5 class="fw-bold mb-0"><i class="fas fa-trophy me-2"></i>Productos Más Vendidos</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Producto</th><th>Und</th><th>Total</th><th>Veces</th></tr>
                        </thead>
                        <tbody id="tbodyTop"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let chartVentas = null, chartPago = null;

function cargarVentasPorCajero() {
    var fecha = $('#filtroFechaCajero').val();
    $('#tbodyVentasCajero').html('<tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-1"></i>Cargando...</td></tr>');
    $('#cantidadVentasCajero').text('0');
    $('#totalVentasCajero').text('$0');
    $.getJSON(BASE_URL + '/reportes/ventas-por-cajero', {fecha: fecha})
        .done(function(r) {
            if (!r.success) {
                PNotify.error({ text: r.message || 'No se pudo consultar el reporte por cajero.' });
                return;
            }

            var html = '';
            if (!r.data.cajeros.length) {
                html = '<tr><td colspan="3" class="text-center text-muted py-4">No hay ventas activas para esta fecha.</td></tr>';
            } else {
                $.each(r.data.cajeros, function(i, cajero) {
                    html += '<tr><td>' + escHtml(cajero.cajero) + '</td>' +
                        '<td class="text-center">' + cajero.cantidad + '</td>' +
                        '<td class="text-end fw-semibold">$' + formatoNumero(cajero.total) + '</td></tr>';
                });
            }
            $('#tbodyVentasCajero').html(html);
            $('#cantidadVentasCajero').text(r.data.cantidad);
            $('#totalVentasCajero').text('$' + formatoNumero(r.data.total));
        })
        .fail(function(xhr) {
            $('#tbodyVentasCajero').html('<tr><td colspan="3" class="text-center text-danger py-4">No se pudo cargar el reporte.</td></tr>');
            PNotify.error({
                text: xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'No se pudo consultar el reporte por cajero.'
            });
        });
}

function imprimirVentasPorCajero() {
    var fecha = $('#filtroFechaCajero').val();
    if (!fecha) {
        PNotify.error({ text: 'Selecciona la fecha del reporte.' });
        return;
    }

    window.open(
        BASE_URL + '/reportes/ventas-por-cajero/imprimir?fecha=' + encodeURIComponent(fecha),
        '_blank',
        'noopener'
    );
}

function cargarReportes() {
    var inicio = $('#filtroInicio').val();
    var fin = $('#filtroFin').val();

    // Resumen ventas
    $.getJSON(BASE_URL + '/reportes/ventas', {inicio, fin}, function(r) {
        if (!r.success) return;
        var res = r.data.resumen;
        $('#rptCantidad').text(res?.cantidad || 0);
        $('#rptTotal').text('$' + formatoNumero(res?.total || 0));
        $('#rptGanancia').text('$' + formatoNumero(res?.ganancia || 0));
        var ticket = res?.cantidad > 0 ? (res.total / res.cantidad) : 0;
        $('#rptTicket').text('$' + formatoNumero(ticket));
    });

    // Top productos
    $.getJSON(BASE_URL + '/reportes/top-productos', {inicio, fin}, function(r) {
        if (!r.success) return;
        var html = '';
        if (!r.data.length) { html = '<tr><td colspan="5" class="text-center text-muted py-4">Sin datos</td></tr>'; }
        else { $.each(r.data, function(i, p) {
            html += '<tr><td>' + (i+1) + '</td><td><strong>' + escHtml(p.descripcion) + '</strong></td><td>' + (p.total_unidad||0) + '</td><td class="fw-bold">$' + formatoNumero(p.total_vendido) + '</td><td>' + p.veces_vendido + '</td></tr>';
        }); }
        $('#tbodyTop').html(html);
    });

    // Ventas diarias (gráfico)
    $.getJSON(BASE_URL + '/reportes/ventas-diarias', {inicio, fin}, function(r) {
        if (!r.success) return;
        var labels = [], data = [];
        $.each(r.data, function(i, d) { labels.push(d.fecha); data.push(parseFloat(d.total)); });

        if (chartVentas) chartVentas.destroy();
        var ctx = document.getElementById('chartVentas')?.getContext('2d');
        if (ctx) {
            chartVentas = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ventas',
                        data: data,
                        backgroundColor: 'rgba(118,75,162,0.2)',
                        borderColor: '#764ba2',
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toLocaleString(); } } } }
                }
            });
        }
    });

    // Métodos de pago (gráfico)
    $.getJSON(BASE_URL + '/reportes/metodos-pago', {inicio, fin}, function(r) {
        if (!r.success) return;
        var labels = [], data = [], colors = ['#764ba2','#4facfe','#f5576c','#a8e063','#f093fb','#ffd700'];
        $.each(r.data, function(i, m) { labels.push(m.tipo_pago); data.push(parseFloat(m.total)); });

        if (chartPago) chartPago.destroy();
        var ctx = document.getElementById('chartPago')?.getContext('2d');
        if (ctx) {
            chartPago = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: labels, datasets: [{ data: data, backgroundColor: colors }] },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } }
                    }
                }
            });
        }
    });
}

$(function() { cargarReportes(); cargarVentasPorCajero(); });
</script>
