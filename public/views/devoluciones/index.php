<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-undo-alt text-warning me-2"></i>Devoluciones</h4>
                <p class="text-muted mb-0">Historial de devoluciones de productos</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" id="searchDev" placeholder="Buscar por código, factura o cliente...">
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="cargarDev(1)"><i class="fas fa-search me-1"></i>Filtrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th><th>Factura</th><th>Fecha</th><th>Cliente</th>
                        <th>Motivo</th><th>Total</th><th>Usuario</th><th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody id="tbodyDev">
                    <tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagDev"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagDev"></ul></nav>
</div>

<!-- Modal Detalle Devolución -->
<div class="modal fade" id="modalDetalleDev" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-undo-alt me-2"></i>Detalle de Devolución</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleDevBody">
                <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let pd = 1;

function cargarDev(page) {
    page = page || 1; pd = page;
    var s = $('#searchDev').val();
    $('#tbodyDev').html('<tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/devoluciones/listar', { page, search: s }, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay devoluciones</td></tr>';
        } else {
            $.each(d.data, function(i, dev) {
                html += '<tr>' +
                    '<td class="fw-bold">' + escHtml(dev.codigo) + '</td>' +
                    '<td>' + (dev.factura_codigo || '-') + '</td>' +
                    '<td><small>' + dev.fecha + '</small></td>' +
                    '<td>' + escHtml(dev.cliente_nombre || '-') + '</td>' +
                    '<td><small>' + escHtml((dev.motivo||'').substring(0, 40)) + '</small></td>' +
                    '<td class="fw-bold text-danger">$' + formatoNumero(dev.total) + '</td>' +
                    '<td><small>' + escHtml(dev.nombre_usuario || '-') + '</small></td>' +
                    '<td><button class="btn btn-sm btn-outline-info" onclick="verDetalleDev(' + dev.id_devolucion + ')" title="Ver detalle"><i class="fas fa-eye"></i></button></td></tr>';
            });
        }
        $('#tbodyDev').html(html);
        $('#infoPagDev').text('Mostrando ' + d.data.length + ' de ' + d.total);
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarDev(' + i + ');return false;">' + i + '</a></li>'; }
        $('#pagDev').html(ph);
    });
}

function verDetalleDev(id) {
    $('#detalleDevBody').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#modalDetalleDev').modal('show');
    $.getJSON(BASE_URL + '/devoluciones/obtener/' + id, function(r) {
        if (!r.success) { $('#detalleDevBody').html('<p class="text-danger">Error al cargar</p>'); return; }
        var dev = r.data;
        var html = '<div class="row g-3 mb-3">' +
            '<div class="col-md-6"><strong>Código:</strong> ' + escHtml(dev.codigo) + '</div>' +
            '<div class="col-md-6"><strong>Factura:</strong> #' + escHtml(dev.factura_codigo) + '</div>' +
            '<div class="col-md-6"><strong>Fecha:</strong> ' + dev.fecha + '</div>' +
            '<div class="col-md-6"><strong>Cliente:</strong> ' + escHtml(dev.cliente_nombre || '-') + '</div>' +
            '<div class="col-12"><strong>Motivo:</strong> ' + escHtml(dev.motivo) + '</div>' +
            '<div class="col-12"><strong>Registrado por:</strong> ' + escHtml(dev.nombre_usuario || '-') + '</div>' +
            '</div><hr><h6 class="fw-bold">Productos devueltos</h6>' +
            '<table class="table table-sm"><thead><tr><th>#</th><th>Producto</th><th>Cant.</th><th>Valor Und.</th><th>Subtotal</th></tr></thead><tbody>';
        $.each(dev.detalles, function(i, det) {
            html += '<tr><td>' + (i+1) + '</td><td>' + escHtml(det.descripcion || 'Producto #' + det.id_producto) + '</td>' +
                '<td>' + (det.cantidad_unidad||0) + '</td>' +
                '<td>$' + formatoNumero(det.valor_unitario) + '</td>' +
                '<td class="fw-bold text-danger">$' + formatoNumero(det.subtotal) + '</td></tr>';
        });
        html += '</tbody></table><hr><div class="text-end"><strong>Total devuelto: <span class="text-danger fw-bold fs-5">$' + formatoNumero(dev.total) + '</span></strong></div>';

        // Con qué se le devolvió la plata al cliente
        var pagos = dev.pagos || [];
        if (pagos.length) {
            html += '<hr><h6 class="fw-bold"><i class="fas fa-hand-holding-usd me-1"></i>Forma de devolución</h6>';
            html += '<div class="table-responsive"><table class="table table-sm mb-2"><thead class="table-light">' +
                    '<tr><th>Método</th><th class="text-end">Monto</th><th>Referencia</th></tr></thead><tbody>';
            $.each(pagos, function(i, p) {
                html += '<tr><td><span class="badge bg-warning text-dark">' + escHtml(p.metodo) + '</span></td>' +
                        '<td class="text-end fw-semibold">$' + formatoNumero(p.monto) + '</td>' +
                        '<td><small class="text-muted">' + escHtml(p.referencia || '-') + '</small></td></tr>';
            });
            html += '</tbody></table></div>';
            if (dev.tipo_pago === 'MIXTO') {
                html += '<div class="alert alert-info py-1 small mb-0"><i class="fas fa-info-circle me-1"></i>Devolución mixta: ' + pagos.length + ' formas de pago.</div>';
            }
        }

        $('#detalleDevBody').html(html);
    });
}

$(function() { cargarDev(1); });
</script>
