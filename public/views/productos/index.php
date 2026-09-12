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
                        <th style="width:50px"></th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Presentación</th>
                        <th>P.Compra</th>
                        <th>P.Venta</th>
                        <th>IVA</th>
                        <th>Tipo Venta</th>
                        <th>Und/Med</th>
                        <th>Stock Mín</th>
                        <th style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyProductos">
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5">
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

                        <!-- Imagen del producto -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Imagen del Producto</label>
                            <div class="d-flex align-items-center gap-3">
                                <div id="p_imagen_preview" style="width:80px;height:80px;border:2px dashed #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fafafa;flex-shrink:0">
                                    <i class="fas fa-camera text-muted" style="font-size:24px"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control form-control-sm" name="imagen" id="p_imagen" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewImagen(this)">
                                    <small class="text-muted">PNG, JPG o WEBP. Máx 2MB</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">O URL de imagen</label>
                            <input type="text" class="form-control" name="imagen_url" id="p_imagen_url" placeholder="https://ejemplo.com/imagen.jpg" onchange="previewUrl(this.value)">
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <!-- Tipo de venta -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tipo de Venta <i class="fas fa-question-circle text-muted" title="UNIDAD: se vende en unidades enteras | CAJA: solo por caja completa | FRACCIÓN DECIMAL: se puede vender por fracción (ej: 0.5 varilla, 0.25 thinner)"></i></label>
                            <select class="form-select" name="tipo_venta" id="p_tipo_venta" onchange="toggleTipoVenta()">
                                <option value="UNIDAD">Unidad (entera)</option>
                                <option value="CAJA">Caja completa</option>
                                <option value="FRACCION_DECIMAL">Fracción decimal</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="div_unidad_medida" style="display:none;">
                            <label class="form-label fw-semibold">Unidad de Medida <i class="fas fa-question-circle text-muted" title="Ej: METRO, LITRO, KILO, VARILLA, BOTELLA, LIBRA"></i></label>
                            <select class="form-select" name="unidad_medida" id="p_unidad_medida">
                                <option value="">Seleccione...</option>
                                <option value="METRO">Metro (m)</option>
                                <option value="CENTIMETRO">Centímetro (cm)</option>
                                <option value="LITRO">Litro (L)</option>
                                <option value="MILILITRO">Mililitro (mL)</option>
                                <option value="KILO">Kilo (kg)</option>
                                <option value="LIBRA">Libra (lb)</option>
                                <option value="GRAMO">Gramo (g)</option>
                                <option value="VARILLA">Varilla</option>
                                <option value="BOTELLA">Botella</option>
                                <option value="GALON">Galón</option>
                                <option value="UNIDAD">Unidad</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="div_cantidad_por_unidad" style="display:none;">
                            <label class="form-label fw-semibold">Cantidad x Unidad <i class="fas fa-question-circle text-muted" title="¿Cuánto equivale 1 unidad? Ej: 1 varilla = 6 metros, 1 thinner = 1 litro"></i></label>
                            <input type="number" class="form-control" name="cantidad_por_unidad" id="p_cantidad_por_unidad" value="1.0000" min="0.01" step="0.01">
                            <small class="text-muted">1 unidad = esta cantidad</small>
                        </div>

                        <!-- Unidad cerrada / Fracción (para UNIDAD y CAJA) -->
                        <div class="col-md-3" id="div_unidad_cerrada">
                            <label class="form-label fw-semibold">Und por Caja <i class="fas fa-question-circle text-muted" title="¿Cuántas unidades vienen en una caja completa? Ej: 12 si cada caja trae 12 botellas"></i></label>
                            <input type="number" class="form-control" name="unidad_cerrada" id="p_unidad_cerrada" value="1" min="1">
                            <small class="text-muted">Cantidad de unidades que trae una caja</small>
                        </div>
                        <div class="col-md-3" id="div_fraccion">
                            <label class="form-label fw-semibold">Fracción x Und <i class="fas fa-question-circle text-muted" title="¿Se puede vender en fracciones? Ej: 4 = 1 unidad se divide en 4 partes. 0 = no fraccionable"></i></label>
                            <input type="number" class="form-control" name="fraccion" id="p_fraccion" value="0" min="0" placeholder="0 = no fraccionable">
                            <small class="text-muted">0 = no fraccionable | 4 = ¼ por unidad</small>
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

                        <!-- Precio unidad suelta / Stock mínimo -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Precio x Und Suelta <i class="fas fa-question-circle text-muted" title="Precio para la venta de una unidad suelta (fracción). Solo aplica si el producto es fraccionable"></i></label>
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

                        <!-- Precio máximo regulado (medicamentos) -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Precio Máximo Regulado
                                <i class="fas fa-balance-scale text-muted" title="Tope legal de venta. Aplica a medicamentos con precio regulado (Comisión Nacional de Precios de Medicamentos). Déjelo vacío si el producto no tiene tope."></i>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="precio_maximo_regulado" id="p_tope" step="0.01" min="0" placeholder="Sin tope">
                            </div>
                            <small class="text-muted">Vacío = el producto no tiene precio regulado</small>
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

<!-- Modal Ver Imagen Grande -->
<div class="modal fade" id="modalVerImagen" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="text-end mb-2">
                <button type="button" class="btn btn-sm btn-dark rounded-circle" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-center">
                <img id="imgGrande" src="" style="max-width:100%;max-height:80vh;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.3);background:#fff;padding:10px;" alt="Imagen del producto">
            </div>
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

function toggleTipoVenta() {
    var tipo = $('#p_tipo_venta').val();
    if (tipo === 'FRACCION_DECIMAL') {
        $('#div_unidad_medida').show();
        $('#div_cantidad_por_unidad').show();
        $('#div_unidad_cerrada').hide();
        $('#div_fraccion').hide();
        $('#p_valor_unidad').closest('.col-md-3').hide();
        actualizarPreviewPresentacion();
    } else {
        $('#div_unidad_medida').hide();
        $('#div_cantidad_por_unidad').hide();
        $('#div_unidad_cerrada').show();
        $('#div_fraccion').show();
        $('#p_valor_unidad').closest('.col-md-3').show();
    }
}

function actualizarPreviewPresentacion() {
    var tipo = $('#p_tipo_venta').val();
    if (tipo !== 'FRACCION_DECIMAL') return;
    var und = $('#p_unidad_medida').val();
    var cant = parseFloat($('#p_cantidad_por_unidad').val()) || 1;
    if (und) {
        var preview = 'X' + cant.toString().replace(/\.?0+$/, '') + ' ' + und;
        var $pres = $('#p_presentacion');
        if (!$pres.val() || $pres.data('auto') === '1') {
            $pres.val(preview);
            $pres.data('auto', '1');
        }
        if (!$('#previewPresentacion').length) {
            $pres.after('<small id="previewPresentacion" class="text-muted d-block"></small>');
        }
        $('#previewPresentacion').html('Auto: <strong>' + preview + '</strong>');
    }
}

$(document).on('change', '#p_unidad_medida', actualizarPreviewPresentacion);
$(document).on('keyup', '#p_cantidad_por_unidad', actualizarPreviewPresentacion);
$(document).on('change', '#p_cantidad_por_unidad', actualizarPreviewPresentacion);
// Si el usuario escribe manualmente en presentación, desactivar auto
$(document).on('keyup', '#p_presentacion', function() {
    $(this).data('auto', '0');
    $('#previewPresentacion').hide();
});

function cargarProductos(page, search) {
    page = page || 1;
    search = search || $('#searchProducto').val();

    $('#tbodyProductos').html('<tr><td colspan="11" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');

    $.getJSON(BASE_URL + '/productos/listar', { page: page, search: search }, function(res) {
        if (!res.success) return;

        var d = res.data;
        var html = '';

        if (d.data.length === 0) {
            html = '<tr><td colspan="11" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay productos</td></tr>';
        } else {
            $.each(d.data, function(i, p) {
                var rentClass = 'text-success';
                var rentVal = parseFloat(p.rentabilidad || 0);
                if (rentVal < 10) rentClass = 'text-danger';
                else if (rentVal < 20) rentClass = 'text-warning';

                var imgHtml = '';
                if (p.imagen) {
                    var imgSrc = p.imagen.startsWith('http') ? p.imagen : BASE_URL + '/uploads/productos/' + p.imagen;
                    imgHtml = '<a href="javascript:void(0)" onclick="verImagenGrande(\'' + imgSrc + '\')"><img src="' + imgSrc + '" style="width:35px;height:35px;object-fit:cover;border-radius:6px;cursor:pointer" onerror="this.style.display=\'none\'" title="Click para ver grande"></a>';
                } else {
                    imgHtml = '<div style="width:35px;height:35px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center"><i class="fas fa-box text-muted" style="font-size:14px"></i></div>';
                }
                html += '<tr>' +
                    '<td class="text-center">' + imgHtml + '</td>' +
                    '<td><span class="badge bg-light text-dark">' + escHtml(p.codigo) + '</span></td>' +
                    '<td><strong>' + escHtml(p.descripcion) + '</strong>' + (p.presentacion ? '<br><small class="text-muted">' + escHtml(p.presentacion) + '</small>' : '') + '</td>' +
                    '<td>' + escHtml(p.presentacion || '-') + '</td>' +
                    '<td>$' + formatoNumero(p.valor_compra) + '</td>' +
                    '<td><strong>$' + formatoNumero(p.valor_venta) + '</strong></td>' +
                    '<td>' + (p.iva_valor ? p.iva_valor + '%' : '-') + '</td>' +
                    '<td>' + getTipoVentaLabel(p.tipo_venta || 'UNIDAD') + '</td>' +
                    '<td>' + (p.unidad_medida ? escHtml(p.unidad_medida) : (p.unidad_cerrada || 1) + ' ud') + '</td>' +
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

function verImagenGrande(src) {
    $('#imgGrande').attr('src', src);
    $('#modalVerImagen').modal('show');
}

function formatoNumero(n) {
    return parseFloat(n || 0).toLocaleString('es-CO', { minimumFractionDigits: 0 });
}

function getTipoVentaLabel(tipo) {
    var labels = {
        'UNIDAD': '<span class="badge bg-info">Unidad</span>',
        'CAJA': '<span class="badge bg-secondary">Caja</span>',
        'FRACCION_DECIMAL': '<span class="badge bg-warning text-dark">Fracción</span>'
    };
    return labels[tipo] || '<span class="badge bg-light text-dark">' + tipo + '</span>';
}

// Vista previa de imagen
function previewImagen(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#p_imagen_preview').html('<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover">');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function previewUrl(url) {
    if (url.trim()) {
        $('#p_imagen_preview').html('<img src="' + url + '" style="width:100%;height:100%;object-fit:cover" onerror="this.parentElement.innerHTML=\'<i class=\\\\\'fas fa-camera text-muted\\\\\' style=\\\\\'font-size:24px\\\\\'></i>\'">');
    }
}

// Inicializar formulario al abrir modal para nuevo producto
$('#modalProducto').on('show.bs.modal', function() {
    $('#p_presentacion').data('auto', '0').removeAttr('data-auto');
    $('#previewPresentacion').remove();
    if ($('#id_producto').val() === '0') {
        $('#p_tipo_venta').val('UNIDAD');
        toggleTipoVenta();
    }
});

// Guardar producto (con imagen)
$('#formProducto').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    // Si hay URL de imagen y no hay archivo, usarla
    var urlImg = $('#p_imagen_url').val().trim();
    if (urlImg && !$('#p_imagen')[0].files.length) {
        formData.set('imagen_url', urlImg);
    }

    $.ajax({
        url: BASE_URL + '/productos/guardar',
        method: 'POST',
        contentType: false,
        processData: false,
        data: formData,
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
        $('#p_tipo_venta').val(p.tipo_venta || 'UNIDAD');
        $('#p_unidad_medida').val(p.unidad_medida || '');
        $('#p_cantidad_por_unidad').val(p.cantidad_por_unidad || 1);
        $('#p_unidad_cerrada').val(p.unidad_cerrada || 1);
        $('#p_fraccion').val(p.fraccion || 0);
        $('#p_compra').val(p.valor_compra);
        $('#p_venta').val(p.valor_venta);
        $('#p_valor_unidad').val(p.valor_unidad);
        $('#p_stock_minimo').val(p.stock_minimo);
        $('#p_rentabilidad').val(p.rentabilidad);
        $('#p_tope').val(p.precio_maximo_regulado || '');
        // Detectar si la presentación fue auto-generada
        var autoPres = 'X' + (parseFloat(p.cantidad_por_unidad||1)).toString().replace(/\.?0+$/, '') + ' ' + (p.unidad_medida||'');
        if (p.presentacion === autoPres) {
            $('#p_presentacion').data('auto', '1');
        } else {
            $('#p_presentacion').data('auto', '0');
        }
        toggleTipoVenta();

        // Mostrar imagen actual
        if (p.imagen) {
            var imgUrl = p.imagen.startsWith('http') ? p.imagen : BASE_URL + '/uploads/productos/' + p.imagen;
            $('#p_imagen_preview').html('<img src="' + imgUrl + '" style="width:100%;height:100%;object-fit:cover">');
            $('#p_imagen_url').val(p.imagen);
        } else {
            $('#p_imagen_preview').html('<i class="fas fa-camera text-muted" style="font-size:24px"></i>');
        }

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
        $('#p_imagen_preview').html('<i class="fas fa-camera text-muted" style="font-size:24px"></i>');
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
