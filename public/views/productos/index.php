<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fas fa-capsules text-primary me-2"></i>Productos
                </h4>
                <p class="text-muted mb-0">Gestión del catálogo de productos</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT p.codigo AS Codigo, p.descripcion AS Descripcion, p.presentacion AS Presentacion, p.valor_compra AS Compra, p.valor_venta AS Venta, p.stock_minimo AS StockMinimo FROM vb_productos p WHERE p.activo=1') ?>&nombre=productos&titulo=Productos" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT p.codigo AS Codigo, p.descripcion AS Descripcion, p.presentacion AS Presentacion, p.valor_compra AS Compra, p.valor_venta AS Venta, p.stock_minimo AS StockMinimo FROM vb_productos p WHERE p.activo=1') ?>&nombre=productos&titulo=Productos" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalProducto"><i class="fas fa-plus me-2"></i>Nuevo Producto</button>
            </div>
        </div>
    </div>
</div>

<!-- Filtro y búsqueda -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control" id="searchProducto" 
                   placeholder="Buscar por código, descripción o código de barras..." 
                   value="<?= \SIG\Core\View::esc($search ?? '') ?>">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="filtroCategoria">
            <option value="">Todas las categorías</option>
            <?php foreach ($catalogos['categorias'] ?? [] as $cat): ?>
            <option value="<?= $cat['id_categoria'] ?>"><?= \SIG\Core\View::esc($cat['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="filtroProveedor">
            <option value="">Todos los proveedores</option>
            <?php foreach ($catalogos['proveedores'] ?? [] as $prov): ?>
            <option value="<?= $prov['id_proveedor'] ?>"><?= \SIG\Core\View::esc($prov['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Tabla -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaProductos">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Presentación</th>
                        <th>P.Compra</th>
                        <th>P.Venta</th>
                        <th>IVA</th>
                        <th>Und</th>
                        <th>Fracc</th>
                        <th>Stock Mín</th>
                        <th style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyProductos">
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>
                            Cargando productos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Paginación -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPaginacion"></small>
    <nav>
        <ul class="pagination pagination-sm mb-0" id="paginacion"></ul>
    </nav>
</div>

<!-- Modal Crear/Editar Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalProductoLabel">
                    <i class="fas fa-capsules me-2"></i>Nuevo Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProducto">
                <input type="hidden" name="id_producto" id="id_producto" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Código -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="codigo" id="p_codigo" required>
                        </div>
                        <!-- Códigos de barras -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Código Barras #1</label>
                            <input type="text" class="form-control" name="codigo_barras_1" id="p_barras1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">#2</label>
                            <input type="text" class="form-control" name="codigo_barras_2" id="p_barras2">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">#3</label>
                            <input type="text" class="form-control" name="codigo_barras_3" id="p_barras3">
                        </div>

                        <!-- Descripción -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="descripcion" id="p_descripcion" rows="1" required></textarea>
                        </div>
                        <!-- Presentación -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Presentación</label>
                            <input type="text" class="form-control" name="presentacion" id="p_presentacion" placeholder="Ej: X30ML">
                        </div>

                        <!-- Marca -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Marca</label>
                            <input type="text" class="form-control" name="marca" id="p_marca">
                        </div>
                        <!-- Proveedor -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Proveedor</label>
                            <select class="form-select" name="id_proveedor" id="p_proveedor">
                                <option value="">Seleccione...</option>
                                <?php foreach ($catalogos['proveedores'] ?? [] as $prov): ?>
                                <option value="<?= $prov['id_proveedor'] ?>"><?= \SIG\Core\View::esc($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Categoría -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select class="form-select" name="id_categoria" id="p_categoria">
                                <option value="">Seleccione...</option>
                                <?php foreach ($catalogos['categorias'] ?? [] as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"><?= \SIG\Core\View::esc($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- IVA -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">IVA</label>
                            <select class="form-select" name="id_iva" id="p_iva">
                                <option value="">Seleccione...</option>
                                <?php foreach ($catalogos['ivas'] ?? [] as $iva): ?>
                                <option value="<?= $iva['id_iva'] ?>"><?= \SIG\Core\View::esc($iva['nombre'] ?: $iva['iva'] . '%') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Sección -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sección</label>
                            <select class="form-select" name="id_seccion" id="p_seccion">
                                <option value="">Seleccione...</option>
                                <?php foreach ($catalogos['secciones'] ?? [] as $sec): ?>
                                <option value="<?= $sec['id_seccion'] ?>"><?= \SIG\Core\View::esc($sec['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <!-- Unidad cerrada / Fracción -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Unidad Cerrada</label>
                            <input type="number" class="form-control" name="unidad_cerrada" id="p_unidad_cerrada" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fracción</label>
                            <input type="number" class="form-control" name="fraccion" id="p_fraccion" value="0" min="0" placeholder="0=no fraccionable">
                        </div>

                        <!-- Precios -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Precio Compra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_compra" id="p_compra" step="0.01" min="0" onchange="calcularRentabilidad()">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Precio Venta</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_venta" id="p_venta" step="0.01" min="0" onchange="calcularRentabilidad()">
                            </div>
                        </div>

                        <!-- Precio unidad / Stock mínimo -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Precio x Unidad</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_unidad" id="p_valor_unidad" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Stock Mínimo</label>
                            <input type="number" class="form-control" name="stock_minimo" id="p_stock_minimo" value="1" min="0">
                        </div>

                        <!-- Rentabilidad (auto) -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Rentabilidad</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="p_rentabilidad" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                <h5 class="fw-bold">¿Eliminar Producto?</h5>
                <p class="text-muted mb-0" id="eliminarTexto">Se desactivará el producto seleccionado.</p>
                <input type="hidden" id="eliminarId" value="0">
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger px-4" id="btnConfirmarEliminar">
                    <i class="fas fa-trash me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;

function calcularRentabilidad() {
    var compra = parseFloat($('#p_compra').val()) || 0;
    var venta = parseFloat($('#p_venta').val()) || 0;
    if (compra > 0 && venta > 0) {
        var rent = ((venta - compra) / venta) * 100;
        $('#p_rentabilidad').val(rent.toFixed(2));
    } else {
        $('#p_rentabilidad').val('');
    }
}

function cargarProductos(page, search) {
    page = page || 1;
    search = search || $('#searchProducto').val();

    $('#tbodyProductos').html('<tr><td colspan="10" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');

    $.getJSON(BASE_URL + '/productos/listar', { page: page, search: search }, function(res) {
        if (!res.success) return;

        var d = res.data;
        var html = '';

        if (d.data.length === 0) {
            html = '<tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay productos</td></tr>';
        } else {
            $.each(d.data, function(i, p) {
                var rentClass = 'text-success';
                var rentVal = parseFloat(p.rentabilidad || 0);
                if (rentVal < 10) rentClass = 'text-danger';
                else if (rentVal < 20) rentClass = 'text-warning';

                html += '<tr>' +
                    '<td><span class="badge bg-light text-dark">' + escHtml(p.codigo) + '</span></td>' +
                    '<td><strong>' + escHtml(p.descripcion) + '</strong>' + (p.presentacion ? '<br><small class="text-muted">' + escHtml(p.presentacion) + '</small>' : '') + '</td>' +
                    '<td>' + escHtml(p.presentacion || '-') + '</td>' +
                    '<td>$' + formatoNumero(p.valor_compra) + '</td>' +
                    '<td><strong>$' + formatoNumero(p.valor_venta) + '</strong></td>' +
                    '<td>' + (p.iva_valor ? p.iva_valor + '%' : '-') + '</td>' +
                    '<td>' + (p.unidad_cerrada || 1) + '</td>' +
                    '<td>' + (p.fraccion || 0) + '</td>' +
                    '<td>' + (p.stock_minimo || 1) + '</td>' +
                    '<td>' +
                        '<button class="btn btn-sm btn-outline-primary me-1" onclick="editarProducto(' + p.id_producto + ')" title="Editar"><i class="fas fa-edit"></i></button>' +
                        '<button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar(' + p.id_producto + ')" title="Eliminar"><i class="fas fa-trash"></i></button>' +
                    '</td>' +
                    '</tr>';
            });
        }

        $('#tbodyProductos').html(html);
        $('#infoPaginacion').text('Mostrando ' + d.data.length + ' de ' + d.total + ' productos');

        // Paginación
        var pagHtml = '';
        for (var i = 1; i <= d.totalPages; i++) {
            pagHtml += '<li class="page-item ' + (i === d.page ? 'active' : '') + '">' +
                '<a class="page-link" href="#" onclick="cargarProductos(' + i + '); return false;">' + i + '</a></li>';
        }
        $('#paginacion').html(pagHtml);
    });
}

function escHtml(str) {
    if (!str) return '';
    return $('<div>').text(str).html();
}

function formatoNumero(n) {
    return parseFloat(n || 0).toLocaleString('es-CO', { minimumFractionDigits: 0 });
}

// Guardar producto
$('#formProducto').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
    }, {});

    $.ajax({
        url: BASE_URL + '/productos/guardar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(res) {
            if (res.success) {
                $('#modalProducto').modal('hide');
                cargarProductos(currentPage);
                PNotify.success({ text: res.message });
            } else {
                PNotify.error({ text: res.message });
            }
        },
        error: function(xhr) {
            var res = xhr.responseJSON;
            PNotify.error({ text: res?.message || 'Error al guardar' });
        }
    });
});

// Editar producto
function editarProducto(id) {
    $.getJSON(BASE_URL + '/productos/obtener/' + id, function(res) {
        if (!res.success) return;
        var p = res.data;

        $('#modalProductoLabel').html('<i class="fas fa-edit me-2"></i>Editar Producto');
        $('#id_producto').val(p.id_producto);
        $('#p_codigo').val(p.codigo);
        $('#p_barras1').val(p.codigo_barras_1);
        $('#p_barras2').val(p.codigo_barras_2);
        $('#p_barras3').val(p.codigo_barras_3);
        $('#p_descripcion').val(p.descripcion);
        $('#p_presentacion').val(p.presentacion);
        $('#p_marca').val(p.marca);
        $('#p_proveedor').val(p.id_proveedor || '');
        $('#p_categoria').val(p.id_categoria || '');
        $('#p_iva').val(p.id_iva || '');
        $('#p_seccion').val(p.id_seccion || '');
        $('#p_unidad_cerrada').val(p.unidad_cerrada || 1);
        $('#p_fraccion').val(p.fraccion || 0);
        $('#p_compra').val(p.valor_compra);
        $('#p_venta').val(p.valor_venta);
        $('#p_valor_unidad').val(p.valor_unidad);
        $('#p_stock_minimo').val(p.stock_minimo);
        $('#p_rentabilidad').val(p.rentabilidad);

        $('#modalProducto').modal('show');
    });
}

// Reset modal al abrir nuevo
$('#modalProducto').on('show.bs.modal', function(e) {
    if (!$(this).find('#id_producto').val()) {
        $('#modalProductoLabel').html('<i class="fas fa-capsules me-2"></i>Nuevo Producto');
        $('#formProducto')[0].reset();
        $('#id_producto').val(0);
        $('#p_unidad_cerrada').val(1);
        $('#p_stock_minimo').val(1);
    }
});

// Confirmar eliminar
function confirmarEliminar(id) {
    $('#eliminarId').val(id);
    $('#modalEliminar').modal('show');
}

$('#btnConfirmarEliminar').on('click', function() {
    var id = $('#eliminarId').val();
    $.ajax({
        url: BASE_URL + '/productos/eliminar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ id_producto: parseInt(id) }),
        success: function(res) {
            $('#modalEliminar').modal('hide');
            if (res.success) {
                cargarProductos(currentPage);
                PNotify.success({ text: res.message });
            } else {
                PNotify.error({ text: res.message });
            }
        }
    });
});

// Búsqueda con debounce
var searchTimer;
$('#searchProducto').on('keyup', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        currentPage = 1;
        cargarProductos(1);
    }, 400);
});

// Cargar al inicio
$(function() {
    cargarProductos(1);
});
</script>
