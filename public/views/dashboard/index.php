<div class="row">
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold">¡Bienvenido, <?= \SIG\Core\View::esc($username ?? 'Usuario') ?>!</h4>
                <p class="text-muted mb-0">Resumen general del sistema</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary bg-opacity-10 text-primary p-2 px-3 rounded-pill">
                    <i class="fas fa-calendar me-1"></i> <?= date('d/m/Y') ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de KPIs -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-shopping-cart text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Ventas Hoy</h6>
                        <h3 class="fw-bold mb-0" id="ventasHoy">$0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                            <i class="fas fa-boxes text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Productos</h6>
                        <h3 class="fw-bold mb-0" id="totalProductos">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                            <i class="fas fa-users text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Clientes</h6>
                        <h3 class="fw-bold mb-0" id="totalClientes">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-3 p-3" style="background: linear-gradient(135deg, #a8e063, #56ab2f);">
                            <i class="fas fa-chart-line text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Capital Total</h6>
                        <h3 class="fw-bold mb-0" id="capitalTotal">$0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-4 mb-4">
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-chart-area text-primary me-2"></i>Ventas de la Semana</h5>
            </div>
            <div class="card-body">
                <canvas id="ventasSemana" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Métodos de Pago</h5>
            </div>
            <div class="card-body">
                <canvas id="metodosPago" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Últimas ventas -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="fas fa-receipt text-primary me-2"></i>Últimas Ventas</h5>
                <a href="<?= $basePath ?>/facturacion" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i>Nueva Venta
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaUltimasVentas">
                        <thead class="table-light">
                            <tr>
                                <th># Factura</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyUltimasVentas">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x d-block mb-2"></i>
                                    Cargando datos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    // ===== GRÁFICO VENTAS SEMANA =====
    var ctxSemana = document.getElementById('ventasSemana')?.getContext('2d');
    if (ctxSemana) {
        new Chart(ctxSemana, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#764ba2'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(v) { return '$' + v.toLocaleString(); } }
                    }
                }
            }
        });
    }

    // ===== GRÁFICO MÉTODOS DE PAGO =====
    var ctxPago = document.getElementById('metodosPago')?.getContext('2d');
    if (ctxPago) {
        new Chart(ctxPago, {
            type: 'doughnut',
            data: {
                labels: ['Efectivo', 'Nequi', 'Davivienda', 'Transferencia', 'ADDI', 'Otros'],
                datasets: [{
                    data: [0, 0, 0, 0, 0, 0],
                    backgroundColor: ['#764ba2', '#4facfe', '#f5576c', '#a8e063', '#f093fb', '#ffd700']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                    }
                }
            }
        });
    }

    // ===== CARGAR DATOS DEL DASHBOARD =====
    $.ajax({
        url: '<?= $basePath ?>/api/dashboard/resumen',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var d = response.data;
                $('#ventasHoy').text('$' + (d.ventas_hoy || 0).toLocaleString());
                $('#totalProductos').text(d.total_productos || 0);
                $('#totalClientes').text(d.total_clientes || 0);
                $('#capitalTotal').text('$' + (d.capital_total || 0).toLocaleString());
            }
        }
    });

    // ===== ÚLTIMAS VENTAS =====
    function escHtmlDash(s) { if (!s) return ''; return $('<div>').text(s).html(); }
    function formatoNumeroDash(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }

    $.ajax({
        url: '<?= $basePath ?>/api/dashboard/ultimas-ventas',
        method: 'GET',
        dataType: 'json',
        data: { limite: 5 },
        success: function(response) {
            if (!response.success || !response.data) return;
            var ventas = response.data.ventas || [];
            var html = '';

            if (!ventas.length) {
                html = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay ventas registradas</td></tr>';
            } else {
                $.each(ventas, function(i, v) {
                    var estado = (v.estado === 'ANULADA')
                        ? '<span class="badge bg-danger">Anulada</span>'
                        : '<span class="badge bg-success">Activa</span>';
                    var fe = (v.tipo === 'ELECTRONICA') ? ' <i class="fas fa-file-invoice text-primary" title="Factura electrónica"></i>' : '';

                    html += '<tr>' +
                        '<td><a href="<?= $basePath ?>/facturacion/historial" class="fw-bold">' + escHtmlDash(String(v.codigo)) + fe + '</a></td>' +
                        '<td>' + escHtmlDash(v.cliente) + '</td>' +
                        '<td class="fw-bold">$' + formatoNumeroDash(v.total) + '</td>' +
                        '<td><span class="badge bg-light text-dark">' + escHtmlDash(v.tipo_pago) + '</span></td>' +
                        '<td><small>' + v.fecha + '<br>' + (v.hora || '') + '</small></td>' +
                        '<td>' + estado + '</td>' +
                        '</tr>';
                });
            }

            $('#tbodyUltimasVentas').html(html);
        },
        error: function() {
            $('#tbodyUltimasVentas').html('<tr><td colspan="6" class="text-center text-muted py-4">No se pudieron cargar las ventas</td></tr>');
        }
    });
});
</script>
