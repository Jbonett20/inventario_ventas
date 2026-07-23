<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-file-invoice text-primary me-2"></i>Historial de Facturas</h4>
                <p class="text-muted mb-0">Consulta y gestión de todas las facturas</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT f.codigo AS Codigo, f.fecha AS Fecha, c.nombre AS Cliente, f.tipo AS Tipo, f.tipo_pago AS Pago, f.total AS Total, f.estado AS Estado, f.cufe AS CUFE FROM vb_facturas f LEFT JOIN vb_clientes c ON f.id_cliente=c.id_cliente ORDER BY f.id_factura DESC') ?>&nombre=facturas&titulo=Facturas" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT f.codigo AS Codigo, f.fecha AS Fecha, c.nombre AS Cliente, f.tipo AS Tipo, f.tipo_pago AS Pago, f.total AS Total, f.estado AS Estado FROM vb_facturas f LEFT JOIN vb_clientes c ON f.id_cliente=c.id_cliente ORDER BY f.id_factura DESC') ?>&nombre=facturas&titulo=Facturas" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <a href="<?= $basePath ?>/facturacion" class="btn btn-primary rounded-pill px-3"><i class="fas fa-plus me-1"></i>Nueva Factura</a>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Tipo</label>
                <select class="form-select form-select-sm" id="filtroTipo">
                    <option value="">Todas</option>
                    <option value="NORMAL">Normales</option>
                    <option value="ELECTRONICA">Electrónicas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Pago</label>
                <select class="form-select form-select-sm" id="filtroPago">
                    <option value="">Todos</option>
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="NEQUI">Nequi</option>
                    <option value="DAVIPLATA">Daviplata</option>
                    <option value="ADDI">ADDI</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="OTROS">Otros</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Estado</label>
                <select class="form-select form-select-sm" id="filtroEstado">
                    <option value="">Todas</option>
                    <option value="ACTIVA">Activas</option>
                    <option value="ANULADA">Anuladas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Fecha Inicio</label>
                <input type="date" class="form-control form-control-sm" id="filtroFechaIni" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Fecha Fin</label>
                <input type="date" class="form-control form-control-sm" id="filtroFechaFin" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-primary btn-sm w-100" onclick="cargarFacturas()"><i class="fas fa-search me-1"></i>Filtrar</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="limpiarFiltros()"><i class="fas fa-undo"></i></button>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" id="filtroSearch" placeholder="Buscar por código, cliente o documento...">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm" id="filtroCodigo" placeholder="Código exacto">
            </div>
        </div>
    </div>
</div>

<!-- Resumen -->
<div class="row g-2 mb-4" id="resumenFacturas">
    <div class="col-md-2 col-4">
        <div class="card border-0 shadow-sm text-center p-2"><small class="text-muted">Total</small><strong id="resTotal">$0</strong></div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card border-0 shadow-sm text-center p-2"><small class="text-muted">Facturas</small><strong id="resCantidad">0</strong></div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card border-0 shadow-sm text-center p-2"><small class="text-muted">Electrónicas</small><strong id="resFE">0</strong></div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card border-0 shadow-sm text-center p-2"><small class="text-muted">Efectivo</small><strong id="resEfectivo">$0</strong></div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card border-0 shadow-sm text-center p-2"><small class="text-muted">Ganancia</small><strong id="resGanancia" class="text-success">$0</strong></div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card border-0 shadow-sm text-center p-2"><small class="text-muted">Anuladas</small><strong id="resAnuladas" class="text-danger">0</strong></div>
    </div>
</div>

<!-- Tabla -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaFacturas">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Fecha</th><th>Cliente</th><th>Tipo</th><th>Pago</th>
                        <th>Total</th><th>Estado</th><th>CUFE</th><th style="width:90px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyFacturas">
                    <tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagFact"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagFact"></ul></nav>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Detalle de Factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleBody">
                <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
let pf = 1;

// Funciones helper
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }

function getFiltros() {
    return {
        page: pf,
        perPage: 25,
        search: $('#filtroSearch').val(),
        tipo: $('#filtroTipo').val(),
        tipo_pago: $('#filtroPago').val(),
        estado: $('#filtroEstado').val(),
        fecha_ini: $('#filtroFechaIni').val(),
        fecha_fin: $('#filtroFechaFin').val(),
        codigo: $('#filtroCodigo').val(),
    };
}

function cargarFacturas() {
    pf = 1;
    var f = getFiltros();
    $('#tbodyFacturas').html('<tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');

    $.getJSON('<?= $basePath ?>/facturacion/listar', f, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        var total = 0, cantFE = 0, totalEf = 0, ganancia = 0, anuladas = 0;

        if (!d.data.length) {
            html = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay facturas</td></tr>';
        } else {
            $.each(d.data, function(i, fac) {
                total += parseFloat(fac.total) || 0;
                ganancia += parseFloat(fac.ganancia) || 0;
                if (fac.tipo === 'ELECTRONICA') cantFE++;
                if (fac.tipo_pago === 'EFECTIVO') totalEf += parseFloat(fac.total) || 0;
                if (fac.estado === 'ANULADA') anuladas++;

                var tipoBadge = fac.tipo === 'ELECTRONICA' ? 'bg-info' : 'bg-secondary';
                var estBadge = fac.estado === 'ACTIVA' ? 'bg-success' : 'bg-danger';
                var cufe = fac.cufe ? '<small title="' + escHtml(fac.cufe) + '">' + fac.cufe.substring(0, 10) + '...</small>' : '-';

                html += '<tr class="' + (fac.estado === 'ANULADA' ? 'text-muted' : '') + '">' +
                    '<td class="fw-bold">' + (fac.codigo||'') + '</td>' +
                    '<td><small>' + fac.fecha + '<br>' + (fac.hora ? fac.hora.substring(0,5) : '') + '</small></td>' +
                    '<td><small>' + escHtml(fac.cliente_nombre || '') + '</small></td>' +
                    '<td><span class="badge ' + tipoBadge + '" style="font-size:10px">' + fac.tipo + '</span></td>' +
                    '<td><small>' + fac.tipo_pago + '</small></td>' +
                    '<td class="fw-bold">$' + formatoNumero(fac.total) + '</td>' +
                    '<td><span class="badge ' + estBadge + '" style="font-size:10px">' + fac.estado + '</span></td>' +
                    '<td>' + cufe + '</td>' +
                    '<td>' +
                        '<button class="btn btn-sm btn-outline-info me-1" onclick="verDetalle(' + fac.id_factura + ')" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
                        (fac.estado === 'ACTIVA' ? '<button class="btn btn-sm btn-outline-danger" onclick="anularFactura(' + fac.id_factura + ',' + (fac.codigo||0) + ')" title="Anular"><i class="fas fa-ban"></i></button>' : '') +
                    '</td></tr>';
            });
        }
        $('#tbodyFacturas').html(html);

        $('#resTotal').text('$' + formatoNumero(total));
        $('#resCantidad').text(d.data.length);
        $('#resFE').text(cantFE);
        $('#resEfectivo').text('$' + formatoNumero(totalEf));
        $('#resGanancia').text('$' + formatoNumero(ganancia));
        $('#resAnuladas').text(anuladas);

        $('#infoPagFact').text('Mostrando ' + d.data.length + ' de ' + d.total);
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="pf=' + i + ';cargarFacturas();return false;">' + i + '</a></li>'; }
        $('#pagFact').html(ph);
    });
}

function limpiarFiltros() {
    $('#filtroTipo').val(''); $('#filtroPago').val(''); $('#filtroEstado').val('');
    $('#filtroFechaIni').val('<?= date('Y-m-01') ?>'); $('#filtroFechaFin').val('<?= date('Y-m-d') ?>');
    $('#filtroSearch').val(''); $('#filtroCodigo').val('');
    cargarFacturas();
}

function verDetalle(id) {
    $('#modalDetalle').modal('show');
    $('#detalleBody').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

    $.getJSON('<?= $basePath ?>/facturacion/obtener/' + id, function(r) {
        if (!r.success) { $('#detalleBody').html('<div class="alert alert-danger">Error al cargar</div>'); return; }
        var f = r.data;
        var detHtml = '';
        $.each(f.detalles, function(i, d) {
            detHtml += '<tr><td>' + (i+1) + '</td><td>' + escHtml(d.descripcion || '') + '</td><td>' + (d.cantidad_unidad||0) + '</td><td>' + (d.cantidad_fraccion||0) + '</td><td>$' + formatoNumero(d.precio_unitario) + '</td><td>' + (d.iva||0) + '%</td><td class="fw-bold">$' + formatoNumero(d.total) + '</td></tr>';
        });

        $('#detalleBody').html(
            '<div class="row mb-3">' +
                '<div class="col-md-6"><strong>Factura #:</strong> ' + (f.codigo||'') + '</div>' +
                '<div class="col-md-6 text-md-end"><strong>Fecha:</strong> ' + f.fecha + ' ' + (f.hora ? f.hora.substring(0,5) : '') + '</div>' +
                '<div class="col-md-6"><strong>Cliente:</strong> ' + escHtml(f.cliente_nombre||'') + '</div>' +
                '<div class="col-md-6 text-md-end"><strong>Doc:</strong> ' + escHtml(f.cliente_documento||'') + '</div>' +
                '<div class="col-md-6"><strong>Tipo:</strong> ' + (f.tipo||'') + '</div>' +
                '<div class="col-md-6 text-md-end"><strong>Pago:</strong> ' + (f.tipo_pago||'') + '</div>' +
                (f.cufe ? '<div class="col-12"><small><strong>CUFE:</strong> ' + escHtml(f.cufe) + '</small></div>' : '') +
                (f.qr ? '<div class="col-12"><small><strong>QR:</strong> <a href="' + escHtml(f.qr) + '" target="_blank">Ver QR</a></small></div>' : '') +
            '</div>' +
            '<hr>' +
            '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>#</th><th>Producto</th><th>Cajas</th><th>Und</th><th>P.Unit</th><th>IVA</th><th>Total</th></tr></thead><tbody>' + detHtml + '</tbody></table></div>' +
            '<hr>' +
            '<div class="row"><div class="col-md-6"><strong>Subtotal:</strong> $' + formatoNumero(f.subtotal) + '</div>' +
                '<div class="col-md-6 text-md-end"><strong>IVA:</strong> $' + formatoNumero(f.total_iva) + '</div>' +
                (parseFloat(f.descuento||0) > 0 ? '<div class="col-md-6"><strong class="text-danger">Descuento:</strong> -$' + formatoNumero(f.descuento) + '</div>' : '') +
                '<div class="col-md-6 text-md-end"><strong class="fs-5">TOTAL:</strong> <span class="fs-5 fw-bold text-primary">$' + formatoNumero(f.total) + '</span></div>' +
                (parseFloat(f.ganancia||0) > 0 ? '<div class="col-12 text-success"><strong>Ganancia:</strong> $' + formatoNumero(f.ganancia) + '</div>' : '') +
            '</div>'
        );
    });
}

function anularFactura(id, codigo) {
    SIG_confirmar('¿Anular factura #' + codigo + '? Se devolverán los productos al inventario.','Anular Factura',function(){
    $.ajax({
        url: '<?= $basePath ?>/facturacion/anular', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_factura: id }),
        success: function(r) {
            if (r.success) { PNotify.success({text: r.message}); cargarFacturas(); }
            else PNotify.error({text: r.message});
        }
    });
    });
}

// Auto-búsqueda con debounce
var stF;
$('#filtroSearch, #filtroCodigo').on('keyup', function() { clearTimeout(stF); stF = setTimeout(cargarFacturas, 400); });
$('#filtroTipo, #filtroPago, #filtroEstado').on('change', cargarFacturas);

$(function() { cargarFacturas(); });
</script>
