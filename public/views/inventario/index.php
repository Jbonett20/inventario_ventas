<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-warehouse text-primary me-2"></i>Inventario</h4>
                <p class="text-muted mb-0">Control de stock y ajustes</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT p.codigo AS Codigo, p.descripcion AS Descripcion, p.presentacion AS Presentacion, inv.unidad AS Unidad, inv.fraccion AS Fraccion, p.valor_compra AS Compra, p.valor_venta AS Venta, p.stock_minimo AS StockMin FROM vb_productos p JOIN vb_inventario inv ON p.id_producto=inv.id_producto WHERE p.activo=1') ?>&nombre=inventario&titulo=Inventario" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT p.codigo AS Codigo, p.descripcion AS Descripcion, p.presentacion AS Presentacion, inv.unidad AS Unidad, inv.fraccion AS Fraccion, p.valor_compra AS Compra, p.valor_venta AS Venta FROM vb_productos p JOIN vb_inventario inv ON p.id_producto=inv.id_producto WHERE p.activo=1') ?>&nombre=inventario&titulo=Inventario" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de resumen -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-3 p-3" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                            <i class="fas fa-boxes text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Capital Invertido</h6>
                        <h4 class="fw-bold mb-0" id="capInvertido">$0</h4>
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
                        <div class="rounded-3 p-3" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
                            <i class="fas fa-chart-line text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Capital Total</h6>
                        <h4 class="fw-bold mb-0" id="capTotal">$0</h4>
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
                        <div class="rounded-3 p-3" style="background:linear-gradient(135deg,#a8e063,#56ab2f);">
                            <i class="fas fa-coins text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Utilidad</h6>
                        <h4 class="fw-bold mb-0" id="utilidad">$0</h4>
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
                        <div class="rounded-3 p-3" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                            <i class="fas fa-exclamation-triangle text-white fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Stock Bajo</h6>
                        <h4 class="fw-bold mb-0" id="stockBajoCount">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Productos por reabastecer -->
<div class="card border-0 shadow-sm mb-4" id="cardReorden" style="display:none;">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Productos por reabastecer</h5>
        <span class="badge bg-warning rounded-pill" id="reordenCount">0</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th><th>Producto</th><th>Stock actual</th>
                        <th>Stock mín</th><th>Sugerencia</th><th>Cajas</th><th></th>
                    </tr>
                </thead>
                <tbody id="tbodyReorden"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Búsqueda -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control" id="searchInv" placeholder="Buscar producto...">
        </div>
    </div>
</div>

<!-- Tabla de inventario -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th><th>Producto</th>
                        <th style="width:80px">Unidad</th><th style="width:80px">Fracción</th>
                        <th style="width:80px">Dif U</th><th style="width:80px">Dif F</th>
                        <th style="width:100px">Precio Venta</th><th style="width:80px">Stock Mín</th>
                        <th style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyInv">
                    <tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagInv"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagInv"></ul></nav>
</div>

<!-- Modal Ajuste -->
<div class="modal fade" id="modalAjuste" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-balance-scale me-2"></i>Ajustar Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAjuste">
                <input type="hidden" name="id_producto" id="aj_id">
                <div class="modal-body">
                    <p class="fw-bold" id="aj_producto_nombre"></p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unidades (Cajas)</label>
                            <input type="number" class="form-control form-control-lg" name="unidad" id="aj_unidad" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fracciones (Unidades sueltas)</label>
                            <input type="number" class="form-control form-control-lg" name="fraccion" id="aj_fraccion" min="0" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observación</label>
                            <input type="text" class="form-control" name="observacion" id="aj_obs" placeholder="Motivo del ajuste">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4"><i class="fas fa-check me-2"></i>Ajustar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let pi = 1;
function cargarInv(page, s) {
    page = page || 1; s = s || $('#searchInv').val();
    $('#tbodyInv').html('<tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/inventario/listar', { page, search: s }, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Sin resultados</td></tr>';
        } else {
            $.each(d.data, function(i, p) {
                var rowClass = p.unidad <= p.stock_minimo ? 'table-danger' : '';
                var fracBadge = p.fraccion > 0 ? '<br><small class="text-muted">x' + p.fraccion + ' und</small>' : '';
                html += '<tr class="' + rowClass + '">' +
                    '<td><span class="badge bg-light text-dark">' + escHtml(p.codigo) + '</span></td>' +
                    '<td><strong>' + escHtml(p.descripcion) + '</strong>' + fracBadge + '</td>' +
                    '<td class="text-center fw-bold fs-5">' + (p.unidad||0) + '</td>' +
                    '<td class="text-center">' + (p.stock_fraccion||0) + '</td>' +
                    '<td class="text-center ' + (p.diferenciaU < 0 ? 'text-danger' : 'text-success') + '">' + (p.diferenciaU||0) + '</td>' +
                    '<td class="text-center ' + (p.diferenciaF < 0 ? 'text-danger' : 'text-success') + '">' + (p.diferenciaF||0) + '</td>' +
                    '<td class="fw-bold">$' + formatoNumero(p.valor_venta) + '</td>' +
                    '<td class="text-center ' + (p.unidad <= p.stock_minimo ? 'text-danger fw-bold' : '') + '">' + (p.stock_minimo||0) + '</td>' +
                    '<td><button class="btn btn-sm btn-warning" onclick="ajustarInv(' + p.id_producto + ',\'' + escHtml(p.descripcion).replace(/'/g,"\\'") + '\',' + (p.unidad||0) + ',' + (p.stock_fraccion||0) + ')"><i class="fas fa-edit me-1"></i>Ajustar</button></td>' +
                    '</tr>';
            });
        }
        $('#tbodyInv').html(html);
        $('#infoPagInv').text('Mostrando ' + d.data.length + ' de ' + d.total);
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarInv(' + i + ');return false;">' + i + '</a></li>'; }
        $('#pagInv').html(ph);
    });
}

function ajustarInv(id, nombre, u, f) {
    $('#aj_id').val(id);
    $('#aj_producto_nombre').text(nombre);
    $('#aj_unidad').val(u);
    $('#aj_fraccion').val(f);
    $('#aj_obs').val('');
    $('#modalAjuste').modal('show');
}

$('#formAjuste').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o, i) { o[i.name] = i.value; return o; }, {});
    data.id_producto = parseInt(data.id_producto);
    data.unidad = parseInt(data.unidad);
    data.fraccion = parseInt(data.fraccion) || 0;
    $.ajax({
        url: BASE_URL + '/inventario/ajustar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) { $('#modalAjuste').modal('hide'); cargarInv(pi); PNotify.success({ text: r.message }); }
            else { PNotify.error({ text: r.message }); }
        }
    });
});

function cargarReorden() {
    $.getJSON(BASE_URL + '/inventario/sugerencias-reorden', function(r) {
        if (!r.success || !r.data.length) { $('#cardReorden').hide(); return; }
        var html = '';
        $.each(r.data, function(i, p) {
            var urgClass = p.unidad <= 0 ? 'table-danger' : (p.unidad <= p.stock_minimo ? 'table-warning' : '');
            html += '<tr class="' + urgClass + '">' +
                '<td><small>' + escHtml(p.codigo) + '</small></td>' +
                '<td><strong>' + escHtml(p.descripcion) + '</strong></td>' +
                '<td class="fw-bold ' + (p.unidad <= 0 ? 'text-danger' : '') + '">' + (p.unidad||0) + '</td>' +
                '<td>' + (p.stock_minimo||0) + '</td>' +
                '<td class="fw-bold text-primary">' + p.sugerido + ' und</td>' +
                '<td><span class="badge bg-info">' + p.cajas_sugeridas + ' caja(s)</span></td>' +
                '<td><a href="' + BASE_URL + '/ingresos" class="btn btn-sm btn-outline-primary" title="Ir a ingresos"><i class="fas fa-plus"></i></a></td></tr>';
        });
        $('#tbodyReorden').html(html);
        $('#reordenCount').text(r.data.length);
        $('#cardReorden').show();
    });
}

// Cargar resumen
$.getJSON(BASE_URL + '/inventario/resumen', function(r) {
    if (r.success && r.data) {
        $('#capInvertido').text('$' + formatoNumero(r.data.capital_invertido));
        $('#capTotal').text('$' + formatoNumero(r.data.capital_total));
        $('#utilidad').text('$' + formatoNumero(r.data.utilidad));
    }
});
$.getJSON(BASE_URL + '/inventario/stock-bajo', function(r) {
    if (r.success) $('#stockBajoCount').text(r.data.length);
});

var st;
$('#searchInv').on('keyup', function() { clearTimeout(st); st = setTimeout(function() { pi = 1; cargarInv(1); }, 400); });
$(function() { cargarInv(1); cargarReorden(); });
</script>
