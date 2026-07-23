<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-boxes text-primary me-2"></i>Ingresos</h4>
                <p class="text-muted mb-0">Registro de entrada de productos al inventario</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT i.fecha AS Fecha, p.codigo AS Codigo, p.descripcion AS Producto, i.cantidad_unidad AS Cajas, i.cantidad_fraccion AS Fracciones, i.tipo AS Tipo, i.observacion AS Observacion FROM vb_ingresos i JOIN vb_productos p ON i.id_producto=p.id_producto ORDER BY i.fecha DESC') ?>&nombre=ingresos&titulo=Ingresos" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT i.fecha AS Fecha, p.codigo AS Codigo, p.descripcion AS Producto, i.cantidad_unidad AS Cajas, i.cantidad_fraccion AS Fracciones, i.tipo AS Tipo FROM vb_ingresos i JOIN vb_productos p ON i.id_producto=p.id_producto ORDER BY i.fecha DESC') ?>&nombre=ingresos&titulo=Ingresos" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalIngreso"><i class="fas fa-plus me-2"></i>Nuevo Ingreso</button>
            </div>
        </div>
    </div>
</div>

<!-- Historial de ingresos -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-history me-2"></i>Historial de Ingresos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Producto</th><th>Código</th><th>Cajas</th><th>Und</th><th>Tipo</th><th>Usuario</th><th>Observación</th></tr>
                </thead>
                <tbody id="tbodyIng">
                    <tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagIng"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagIng"></ul></nav>
</div>

<!-- Modal Nuevo Ingreso -->
<div class="modal fade" id="modalIngreso" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Nuevo Ingreso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formIngreso">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Producto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" name="id_producto" id="ing_id_producto">
                                <input type="text" class="form-control" id="ing_buscar" placeholder="Buscar producto por código o nombre..." autocomplete="off">
                                <button class="btn btn-outline-primary" type="button" id="ing_btn_buscar">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="ing_resultados" class="list-group mt-2" style="max-height:200px;overflow-y:auto;display:none;"></div>
                            <div id="ing_producto_seleccionado" class="alert alert-info mt-2 py-2 d-none">
                                <strong id="ing_prod_nombre"></strong> <small class="text-muted" id="ing_prod_codigo"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cantidad Cajas</label>
                            <input type="number" class="form-control form-control-lg" name="cantidad_unidad" id="ing_cant_und" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fracciones (Und sueltas)</label>
                            <input type="number" class="form-control form-control-lg" name="cantidad_fraccion" id="ing_cant_frac" min="0" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observación</label>
                            <input type="text" class="form-control" name="observacion" id="ing_obs" placeholder="Ej: Factura #1234">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-save me-2"></i>Registrar Ingreso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let ping = 1;
function cargarIng(page) {
    page = page || 1; ping = page;
    $('#tbodyIng').html('<tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/ingresos/listar', { page }, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        if (!d.data.length) { html = '<tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay ingresos</td></tr>'; }
        else {
            $.each(d.data, function(i, ing) {
                html += '<tr><td>' + ing.fecha + '</td><td><strong>' + escHtml(ing.descripcion) + '</strong></td><td>' + escHtml(ing.codigo) + '</td>' +
                    '<td class="text-center fw-bold">' + (ing.cantidad_unidad||0) + '</td><td class="text-center">' + (ing.cantidad_fraccion||0) + '</td>' +
                    '<td><span class="badge bg-info">' + ing.tipo + '</span></td><td>' + escHtml(ing.nombre_usuario || '-') + '</td><td><small>' + escHtml(ing.observacion || '-') + '</small></td></tr>';
            });
        }
        $('#tbodyIng').html(html);
        $('#infoPagIng').text('Mostrando ' + d.data.length + ' de ' + d.total);
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarIng(' + i + ');return false;">' + i + '</a></li>'; }
        $('#pagIng').html(ph);
    });
}

// Búsqueda de productos para ingreso
var timerBusq;
$('#ing_buscar').on('keyup', function() {
    clearTimeout(timerBusq);
    var q = $(this).val();
    if (q.length < 2) { $('#ing_resultados').hide(); return; }
    timerBusq = setTimeout(function() {
        $.getJSON(BASE_URL + '/ingresos/buscar-productos', { q }, function(r) {
            if (!r.success || !r.data.length) { $('#ing_resultados').hide(); return; }
            var html = '';
            $.each(r.data, function(i, p) {
                html += '<button type="button" class="list-group-item list-group-item-action" onclick="seleccionarProductoIng(' + p.id_producto + ',\'' + escHtml(p.descripcion).replace(/'/g,"\\'") + '\',\'' + escHtml(p.codigo) + '\')">' +
                    '<strong>' + escHtml(p.codigo) + '</strong> - ' + escHtml(p.descripcion) +
                    ' <small class="text-muted">Stock: ' + (p.unidad||0) + ' cajas / ' + (p.stock_fraccion||0) + ' und</small></button>';
            });
            $('#ing_resultados').html(html).show();
        });
    }, 300);
});

function seleccionarProductoIng(id, nombre, codigo) {
    $('#ing_id_producto').val(id);
    $('#ing_prod_nombre').text(nombre);
    $('#ing_prod_codigo').text('Cód: ' + codigo);
    $('#ing_producto_seleccionado').removeClass('d-none');
    $('#ing_resultados').hide();
    $('#ing_buscar').val(nombre);
    $('#ing_cant_und').focus();
}

$('#formIngreso').on('submit', function(e) {
    e.preventDefault();
    if (!$('#ing_id_producto').val()) { PNotify.error({ text: 'Debe seleccionar un producto' }); return; }
    var data = $(this).serializeArray().reduce(function(o, i) { o[i.name] = i.value; return o; }, {});
    data.id_producto = parseInt(data.id_producto);
    data.cantidad_unidad = parseInt(data.cantidad_unidad) || 0;
    data.cantidad_fraccion = parseInt(data.cantidad_fraccion) || 0;
    if (data.cantidad_unidad === 0 && data.cantidad_fraccion === 0) {
        PNotify.error({ text: 'Debe ingresar al menos una cantidad' }); return;
    }
    $.ajax({
        url: BASE_URL + '/ingresos/guardar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalIngreso').modal('hide'); cargarIng(1);
                PNotify.success({ text: r.message });
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: xhr.responseJSON?.message || 'Error' }); }
    });
});

$('#modalIngreso').on('hidden.bs.modal', function() {
    $('#formIngreso')[0].reset();
    $('#ing_id_producto').val('');
    $('#ing_producto_seleccionado').addClass('d-none');
    $('#ing_resultados').hide();
});

$(function() { cargarIng(1); });
</script>
