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
                        <th>Equivalencia</th>
                        <th>P. Und Interna</th>
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
                            <label class="form-label fw-semibold">
                                Código
                                <i class="fas fa-magic text-muted" title="El sistema asigna un número consecutivo automáticamente. No se puede cambiar."></i>
                            </label>
                            <input type="text" class="form-control bg-light" name="codigo" id="p_codigo" readonly
                                   inputmode="numeric" placeholder="Se asigna automáticamente">
                            <small class="text-muted" id="p_codigo_ayuda">Automático (solo números)</small>
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Forma de Venta
                                <a href="#" class="text-primary ms-1" data-bs-toggle="modal" data-bs-target="#modalAyudaProducto" title="Ver ayuda de formas de venta">
                                    <i class="fas fa-circle-question"></i>
                                </a>
                            </label>
                            <select class="form-select" name="tipo_venta" id="p_tipo_venta" onchange="toggleTipoVenta()">
                                <option value="UNIDAD">Unidad (entera)</option>
                                <option value="CAJA">Caja completa</option>
                                <option value="CAJA_UNIDAD">Caja + Unidad</option>
                                <option value="CAJA_INTERMEDIO">Caja + Intermedio + Unidad</option>
                                <option value="FRACCION_DECIMAL">Fracción decimal</option>
                            </select>
                            <small class="text-muted" id="p_tipo_venta_ayuda">Se vende por unidades sueltas</small>
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

                        <!-- Unidad cerrada (solo formas con caja) -->
                        <input type="hidden" name="fraccion" id="p_fraccion" value="0">
                        <div class="col-md-3" id="div_unidad_cerrada" style="display:none;">
                            <label class="form-label fw-semibold">Unidades por caja <i class="fas fa-question-circle text-muted" title="¿Cuántas unidades internas trae una caja cerrada? Ej: 24 si una caja trae 24 latas."></i></label>
                            <input type="number" class="form-control" name="unidad_cerrada" id="p_unidad_cerrada" value="1" min="1" onchange="actualizarAyudaEquivalencia()">
                            <small class="text-muted" id="p_unidad_cerrada_ayuda">1 caja = N unidades internas</small>
                        </div>

                        <!-- Nivel intermedio (solo Caja + Intermedio + Unidad) -->
                        <div class="col-12" id="div_nivel_intermedio" style="display:none;">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-bold mb-2"><i class="fas fa-layer-group text-primary me-2"></i>Nivel intermedio</h6>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold small">Nombre del nivel intermedio</label>
                                        <input type="text" class="form-control" name="nombre_intermedio" id="p_nombre_intermedio" placeholder="Sobre" maxlength="30" onchange="actualizarAyudaEquivalencia()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold small" id="p_unidades_intermedias_label">Niveles intermedios por caja <i class="fas fa-question-circle text-muted" title="Cuántos niveles intermedios (sobres, ampollas) trae una caja cerrada."></i></label>
                                        <input type="number" class="form-control" name="unidades_intermedias" id="p_unidades_intermedias" min="0" value="0" onchange="actualizarAyudaEquivalencia()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold small" id="p_unidades_por_intermedio_label">Unidades mínimas por nivel <i class="fas fa-question-circle text-muted" title="Cuántas unidades mínimas (pastillas) trae cada nivel intermedio."></i></label>
                                        <input type="number" class="form-control" name="unidades_por_intermedio" id="p_unidades_por_intermedio" min="0" value="1" onchange="actualizarAyudaEquivalencia()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold small" id="p_valor_intermedio_label">Precio Venta (Sobre)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control form-control-sm" name="valor_intermedio" id="p_valor_intermedio" step="0.01" min="0">
                                        </div>
                                        <small class="text-muted" id="p_valor_intermedio_ayuda">Precio de venta de un sobre suelto</small>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted" id="p_intermedio_ayuda"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Precios -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" id="p_compra_label">Precio Compra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_compra" id="p_compra" step="0.01" min="0" onchange="calcularRentabilidad()">
                            </div>
                            <small class="text-muted" id="p_compra_ayuda">Costo de una unidad</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" id="p_venta_label">Precio Venta</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_venta" id="p_venta" step="0.01" min="0" onchange="calcularRentabilidad()">
                            </div>
                            <small class="text-muted" id="p_venta_ayuda"></small>
                        </div>

                        <!-- Precio unidad interna / Stock mínimo -->
                        <div class="col-md-3" id="div_valor_unidad" style="display:none;">
                            <label class="form-label fw-semibold" id="p_valor_unidad_label">Precio Unidad Interna <i class="fas fa-question-circle text-muted" title="Precio de una unidad interna suelta. Ej: si la caja trae 24 latas, este es el precio de una lata."></i></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_unidad" id="p_valor_unidad" step="0.01" min="0">
                            </div>
                            <small class="text-muted" id="p_valor_unidad_ayuda">Precio de una unidad suelta</small>
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

<!-- Modal Ayuda: formas de registrar un producto -->
<div class="modal fade" id="modalAyudaProducto" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-circle-question me-2 text-primary"></i>Cómo registrar un producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <p class="mb-3">El <strong>Forma de Venta</strong> decide qué campos se muestran, cómo se cobra y cómo se descuenta el inventario. El inventario <strong>siempre se lleva en unidades mínimas</strong>: la unidad más pequeña que se puede vender (una lata, un sobre, una pastilla, un litro...).</p>

                <h6 class="fw-bold">1. Formas de venta</h6>
                <table class="table table-sm table-bordered align-middle mb-3">
                    <thead class="table-light"><tr><th style="width:42mm">Forma de Venta</th><th>Cuándo usarla</th><th style="width:42mm">En el POS se escribe</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Unidad (entera)</strong></td><td>Se vende de a una unidad (una lata, un jabón, un cepillo).</td><td>Unidades en la columna <em>Und</em>.</td></tr>
                        <tr><td><strong>Caja completa</strong></td><td>Solo se vende la caja cerrada; no se abren cajas.</td><td>Cajas en la columna <em>Cant. caja</em>. La columna <em>Und</em> queda bloqueada.</td></tr>
                        <tr><td><strong>Caja + Unidad</strong></td><td>Se vende la caja completa o sus unidades sueltas (caja de 24 latas y también latas sueltas).</td><td>Cajas en <em>Cant. caja</em> y unidades sueltas en <em>Und</em>.</td></tr>
                        <tr><td><strong>Caja + Intermedio + Unidad</strong></td><td>Empaque de tres niveles: caja → sobre → pastilla. Cada nivel tiene su precio.</td><td>Cajas, sobres y pastillas en tres columnas separadas.</td></tr>
                        <tr><td><strong>Fracción decimal</strong></td><td>Se vende por medida: 0,5 litro, 1,25 kg, 3 metros.</td><td>Cantidad decimal (0.25, 0.5, 1.75...).</td></tr>
                    </tbody>
                </table>

                <h6 class="fw-bold">2. Campos del formulario</h6>
                <table class="table table-sm table-bordered align-middle mb-3">
                    <thead class="table-light"><tr><th style="width:48mm">Campo</th><th>Qué significa</th><th style="width:34mm">Ejemplo</th></tr></thead>
                    <tbody>
                        <tr><td>Descripción</td><td>Nombre del producto. Obligatorio.</td><td>Jarabe infantil caja x 10 sobres</td></tr>
                        <tr><td>Código</td><td>Lo asigna el sistema (consecutivo numérico). No se digita.</td><td>1000034</td></tr>
                        <tr><td>Código Barras #1, #2, #3</td><td>Hasta tres códigos de barras para buscar el producto en el POS.</td><td>7701234567890</td></tr>
                        <tr><td>Presentación</td><td>Texto libre. En fracción decimal se genera sola (ej: X1 LITRO).</td><td>X30ML</td></tr>
                        <tr><td>Marca, Proveedor, Categoría, IVA, Sección</td><td>Datos de clasificación. Se usan para filtrar y para calcular el IVA en la venta.</td><td>—</td></tr>
                        <tr><td>Imagen del Producto / URL de imagen</td><td>Foto del producto (PNG, JPG o WEBP, máx 2MB) o una dirección web.</td><td>—</td></tr>
                        <tr><td>Unidades por caja</td><td>Cuántas unidades mínimas trae una caja cerrada. Solo en formas con caja; mínimo 2.</td><td>Caja de 12 sobres → 12</td></tr>
                        <tr><td>Nombre del nivel intermedio</td><td>Nombre de la presentación intermedia (solo Caja + Intermedio + Unidad).</td><td>Sobre</td></tr>
                        <tr><td>Niveles intermedios por caja</td><td>Cuántos niveles intermedios trae la caja.</td><td>50 sobres</td></tr>
                        <tr><td>Unidades mínimas por nivel</td><td>Cuántas unidades mínimas trae cada nivel intermedio.</td><td>10 pastillas por sobre</td></tr>
                        <tr><td>Unidad de Medida / Cantidad x Unidad</td><td>Solo fracción decimal: en qué se mide y cuánto equivale una unidad de venta.</td><td>LITRO / 1</td></tr>
                        <tr><td>Precio Compra</td><td>Costo de una unidad mínima. El costo de la caja se calcula multiplicando.</td><td>$800 por sobre</td></tr>
                        <tr><td>Precio Venta</td><td>Precio de la presentación principal: la caja si hay caja, o la unidad/medida si no.</td><td>$12.000 la caja</td></tr>
                        <tr><td>Precio Venta (nivel intermedio)</td><td>Precio de un sobre suelto (solo Caja + Intermedio + Unidad).</td><td>$1.200 el sobre</td></tr>
                        <tr><td>Precio Venta (unidad suelta / unidad mínima)</td><td>Precio de una unidad mínima suelta. Obligatorio si se venden sueltas.</td><td>$150 la pastilla</td></tr>
                        <tr><td>Stock Mínimo</td><td>Cantidad a partir de la cual el sistema avisa que hay que reabastecer.</td><td>10</td></tr>
                        <tr><td>Rentabilidad</td><td>Se calcula sola: (venta − costo de la caja) ÷ venta.</td><td>—</td></tr>
                        <tr><td>Precio Máximo Regulado</td><td>Tope legal de venta para medicamentos regulados. Vacío si no aplica.</td><td>—</td></tr>
                    </tbody>
                </table>

                <h6 class="fw-bold">3. Reglas y validaciones del sistema</h6>
                <ul class="mb-3">
                    <li><strong>Fracción decimal:</strong> exige <em>Unidad de medida</em> y <em>Cantidad x Unidad</em> mayor que cero.</li>
                    <li><strong>Caja completa:</strong> exige al menos 2 unidades mínimas por caja. Si no hay caja, use <em>Unidad (entera)</em>.</li>
                    <li><strong>Unidades sueltas:</strong> si la caja se abre, el sistema exige el precio de la unidad suelta (o el del nivel intermedio).</li>
                    <li><strong>Nivel intermedio:</strong> exige nombre, cantidad de niveles, unidades por nivel y precio del nivel.</li>
                    <li><strong>Unidades por caja:</strong> con nivel intermedio se calcula sola (niveles × unidades por nivel) y el campo queda bloqueado.</li>
                    <li>El <strong>código</strong> es siempre numérico y consecutivo; si se intenta repetir, el sistema lo rechaza.</li>
                </ul>

                <h6 class="fw-bold">4. Ejemplos completos</h6>
                <p class="mb-1"><strong>A) Caja de 10 sobres que también se vende por sobre</strong> — Forma: <em>Caja + Unidad</em>; Unidades por caja: 10; Precio Compra: $800 (por sobre); Precio Venta: $12.000 (caja); Precio Venta (unidad suelta): $1.500 (sobre).</p>
                <p class="mb-1"><strong>B) Caja sellada de 12 unidades</strong> — Forma: <em>Caja completa</em>; Unidades por caja: 12; Precio Venta: $36.000. En el POS solo se digitan cajas.</p>
                <p class="mb-1"><strong>C) Caja × 50 sobres × 10 pastillas</strong> — Forma: <em>Caja + Intermedio + Unidad</em>; Nombre: Sobre; Niveles por caja: 50; Unidades por nivel: 10 (el sistema calcula 500 unidades por caja); Precio Venta: precio de la caja; Precio Venta (Sobre): precio del sobre; Precio Venta (unidad mínima): precio de la pastilla.</p>
                <p class="mb-1"><strong>D) Thinner por litro</strong> — Forma: <em>Fracción decimal</em>; Unidad de Medida: LITRO; Cantidad x Unidad: 1; Precio Venta: $9.000 (por litro). La presentación queda "X1 LITRO".</p>
                <p class="mb-3"><strong>E) Cepillo de dientes</strong> — Forma: <em>Unidad (entera)</em>; Precio Venta: $3.500.</p>

                <h6 class="fw-bold">5. Cómo se cobra y se descuenta el inventario en el POS</h6>
                <p class="mb-2">El stock se lleva en <strong>unidades mínimas</strong>. Al agregar el producto al carrito se crea una línea con su configuración; según la forma de venta aparecen las columnas <em>Cant. caja</em>, <em>Sobre</em> y <em>Und</em>:</p>
                <table class="table table-sm table-bordered align-middle mb-3">
                    <thead class="table-light"><tr><th style="width:50mm">Lo que pide el cliente</th><th>Qué hacer en el POS</th><th style="width:50mm">Qué cobra y descuenta</th></tr></thead>
                    <tbody>
                        <tr><td>Una caja completa</td><td>Cant. caja = 1, Und = 0</td><td>Precio de la caja; descuenta todas las unidades mínimas de la caja.</td></tr>
                        <tr><td>Un sobre / una unidad suelta</td><td>Cant. caja = 0, Und = 1 (o el botón <i class="fas fa-plus-circle text-primary"></i>)</td><td>Precio de la unidad suelta; descuenta 1 unidad mínima.</td></tr>
                        <tr><td>Dos cajas y tres sobres</td><td>Cant. caja = 2, Und = 3</td><td>2 × precio caja + 3 × precio suelto.</td></tr>
                        <tr><td>Caja + sobres + pastillas</td><td>Escriba cada nivel en su columna</td><td>Cada nivel se cobra a su precio; el inventario descuenta el total de unidades mínimas.</td></tr>
                        <tr><td>Medio litro</td><td>Cantidad = 0.5</td><td>0.5 × precio por litro; descuenta 0.5 de la medida.</td></tr>
                    </tbody>
                </table>
                <p class="mb-0">El POS no permite vender más que el stock disponible: si la línea supera el stock, la cantidad se revierte y se muestra el disponible en unidades mínimas.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
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
    // El costo es por unidad interna y el precio de venta es por caja:
    // el costo de la caja se obtiene multiplicando por las unidades que trae.
    var porCaja = parseInt($('#p_unidad_cerrada').val(), 10) || 1;
    var tipo = $('#p_tipo_venta').val();
    var costoVenta = compra;
    if (tipo !== 'FRACCION_DECIMAL' && porCaja > 1) {
        costoVenta = compra * porCaja;
    }
    if (compra > 0 && venta > 0) {
        var rent = ((venta - costoVenta) / venta) * 100;
        $('#p_rentabilidad').val(rent.toFixed(2));
    } else {
        $('#p_rentabilidad').val('');
    }
}

function toggleTipoVenta() {
    var tipo = $('#p_tipo_venta').val();
    var conCaja = (tipo === 'CAJA' || tipo === 'CAJA_UNIDAD' || tipo === 'CAJA_INTERMEDIO');
    var conIntermedio = (tipo === 'CAJA_INTERMEDIO');
    var permiteSueltas = (tipo === 'CAJA_UNIDAD' || tipo === 'CAJA_INTERMEDIO' || tipo === 'UNIDAD');

    // Campos que solo aplican a fracción decimal
    $('#div_unidad_medida').toggle(tipo === 'FRACCION_DECIMAL');
    $('#div_cantidad_por_unidad').toggle(tipo === 'FRACCION_DECIMAL');

    // Caja y nivel intermedio
    $('#div_unidad_cerrada').toggle(conCaja);
    $('#div_nivel_intermedio').toggle(conIntermedio);
    $('#div_valor_unidad').toggle(permiteSueltas && tipo !== 'FRACCION_DECIMAL');

    // `fraccion` ya no se pregunta: se deduce de la forma de venta
    $('#p_fraccion').val(permiteSueltas && tipo !== 'FRACCION_DECIMAL' ? 1 : 0);

    // Etiquetas según la forma de venta
    var ayuda = {
        'UNIDAD': 'Se vende por unidades sueltas',
        'CAJA': 'Solo se vende la caja completa',
        'CAJA_UNIDAD': 'Se vende la caja completa o sus unidades sueltas',
        'CAJA_INTERMEDIO': 'Caja → nivel intermedio → unidad mínima',
        'FRACCION_DECIMAL': 'Se vende por fracción (litros, kilos, metros...)'
    };
    $('#p_tipo_venta_ayuda').text(ayuda[tipo] || '');

    if (tipo === 'FRACCION_DECIMAL') {
        $('#p_venta_label').text('Precio Venta (' + ($('#p_unidad_medida').val() || 'medida') + ')');
        $('#p_compra_label').text('Precio Compra (' + ($('#p_unidad_medida').val() || 'medida') + ')');
        $('#p_compra_ayuda').text('');
        $('#p_venta_ayuda').text('');
        actualizarPreviewPresentacion();
    } else if (tipo === 'CAJA') {
        $('#p_compra_label').text('Precio Compra (por unidad interna)');
        $('#p_compra_ayuda').text('Costo de una unidad interna; el de la caja se calcula');
        $('#p_venta_label').text('Precio Venta (caja cerrada)');
        $('#p_venta_ayuda').text('Precio de la caja completa');
        actualizarAyudaEquivalencia();
    } else if (tipo === 'CAJA_UNIDAD') {
        $('#p_compra_label').text('Precio Compra (por unidad interna)');
        $('#p_compra_ayuda').text('Costo de una unidad interna');
        $('#p_venta_label').text('Precio Venta (caja)');
        $('#p_venta_ayuda').text('Precio de la caja completa');
        $('#p_valor_unidad_label').text('Precio Venta (unidad suelta)');
        $('#p_valor_unidad_ayuda').text('Precio de una unidad suelta');
        actualizarAyudaEquivalencia();
    } else if (tipo === 'CAJA_INTERMEDIO') {
        $('#p_compra_label').text('Precio Compra (por unidad mínima)');
        $('#p_compra_ayuda').text('Costo de la unidad más pequeña');
        $('#p_venta_label').text('Precio Venta (caja)');
        $('#p_venta_ayuda').text('Precio de la caja completa');
        $('#p_valor_unidad_label').text('Precio Venta (unidad mínima)');
        $('#p_valor_unidad_ayuda').text('Precio de la unidad más pequeña');
        actualizarAyudaEquivalencia();
    } else {
        $('#p_compra_label').text('Precio Compra');
        $('#p_compra_ayuda').text('Costo de una unidad');
        $('#p_venta_label').text('Precio Venta (unidad)');
        $('#p_venta_ayuda').text('Precio de venta de la unidad');
    }
    calcularRentabilidad();
}

function nivelIntermedioActivo() {
    return $('#p_tipo_venta').val() === 'CAJA_INTERMEDIO'
        && (parseInt($('#p_unidades_intermedias').val(), 10) || 0) > 0;
}

// Valida que la forma de venta tenga los datos mínimos
function validarTipoVentaCaja() {
    var tipo = $('#p_tipo_venta').val();
    var conCaja = (tipo === 'CAJA' || tipo === 'CAJA_UNIDAD' || tipo === 'CAJA_INTERMEDIO');
    if (!conCaja) return true;

    var porCaja = parseInt($('#p_unidad_cerrada').val(), 10) || 0;
    if (porCaja < 2) {
        PNotify.error({ text: 'Indique cuántas unidades internas trae la caja (mínimo 2).' });
        $('#p_unidad_cerrada').focus();
        return false;
    }

    if (tipo === 'CAJA_INTERMEDIO') {
        var intermedios = parseInt($('#p_unidades_intermedias').val(), 10) || 0;
        var porIntermedio = parseInt($('#p_unidades_por_intermedio').val(), 10) || 0;
        if (intermedios < 1) {
            PNotify.error({ text: 'Indique cuántos niveles intermedios trae la caja.' });
            $('#p_unidades_intermedias').focus();
            return false;
        }
        if (porIntermedio < 1) {
            PNotify.error({ text: 'Indique cuántas unidades mínimas trae cada nivel intermedio.' });
            $('#p_unidades_por_intermedio').focus();
            return false;
        }
        if (!($('#p_nombre_intermedio').val() || '').trim()) {
            PNotify.error({ text: 'Indique el nombre del nivel intermedio (ej: sobre).' });
            $('#p_nombre_intermedio').focus();
            return false;
        }
    }
    return true;
}

function actualizarAyudaEquivalencia() {
    var porCaja = parseInt($('#p_unidad_cerrada').val(), 10) || 1;
    var sueltas = parseInt($('#p_fraccion').val(), 10) > 0;
    var intermedios = parseInt($('#p_unidades_intermedias').val(), 10) || 0;
    var porIntermedio = parseInt($('#p_unidades_por_intermedio').val(), 10) || 1;
    var nombre = ($('#p_nombre_intermedio').val() || 'nivel intermedio').trim();

    if (nivelIntermedioActivo()) {
        var total = intermedios * porIntermedio;
        $('#p_unidad_cerrada').val(total);
        $('#p_unidad_cerrada').prop('readonly', true);
        $('#p_unidad_cerrada_ayuda').text('1 caja = ' + intermedios + ' ' + nombre + '(s) × ' + porIntermedio + ' = ' + total + ' unidades mínimas');
        $('#p_intermedio_ayuda').text('1 caja = ' + intermedios + ' ' + nombre + '(s) · cada ' + nombre + ' = ' + porIntermedio + ' unidades mínimas · inventario en unidades mínimas (' + total + ' por caja)');
        $('#p_valor_intermedio_label').text('Precio Venta (' + nombre + ')');
        $('#p_valor_intermedio_ayuda').text('Precio de venta de un ' + nombre + ' suelto');
        $('#p_valor_unidad_label').text('Precio Venta (unidad mínima)');
        $('#p_valor_unidad_ayuda').text('Precio de la unidad más pequeña');
        return;
    }

    $('#p_unidad_cerrada').prop('readonly', false);
    $('#p_intermedio_ayuda').text('');
    $('#p_unidad_cerrada_ayuda').text(porCaja > 1
        ? '1 caja = ' + porCaja + ' unidades internas'
        : 'Indique cuántas unidades internas trae la caja');
    $('#p_valor_unidad_label').text('Precio Venta (unidad suelta)');
    $('#p_valor_unidad_ayuda').text('Precio de una unidad suelta');
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
$(document).on('change', '#p_fraccion', actualizarAyudaEquivalencia);
$(document).on('change input', '#p_unidad_cerrada, #p_compra, #p_venta, #p_tipo_venta', calcularRentabilidad);
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
                    '<td>' + equivalenciaProducto(p) + '</td>' +
                    '<td>' + (parseFloat(p.valor_unidad || 0) > 0 ? '$' + formatoNumero(p.valor_unidad) : '-') + '</td>' +
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

function equivalenciaProducto(p) {
    var tipo = p.tipo_venta || 'UNIDAD';
    if (tipo === 'FRACCION_DECIMAL') {
        var cant = parseFloat(p.cantidad_por_unidad || 1);
        return escHtml(cant + ' ' + (p.unidad_medida || 'und') + ' x und');
    }
    var porCaja = parseInt(p.unidad_cerrada || 1, 10);
    var intermedios = parseInt(p.unidades_intermedias || 0, 10);
    if (intermedios > 0) {
        var porIntermedio = parseInt(p.unidades_por_intermedio || 1, 10);
        var nombre = escHtml(p.nombre_intermedio || 'nivel');
        return '1 caja = ' + intermedios + ' ' + nombre + '(s) × ' + porIntermedio + ' = ' + porCaja + ' und mín.';
    }
    return porCaja > 1 ? porCaja + ' und internas x caja' : '1 unidad';
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
    if (!validarTipoVentaCaja()) return;
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
        // La forma de venta se deduce de la configuración guardada. El nivel
        // intermedio solo cuenta si el producto realmente lo vende (tiene
        // precio): si se cambió a "Caja completa" o "Caja + Unidad", los datos
        // viejos del nivel intermedio no deben reaparecer en el formulario.
        var intermedios = parseInt(p.unidades_intermedias, 10) || 0;
        var valorIntermedio = parseFloat(p.valor_intermedio) || 0;
        var porCaja = parseInt(p.unidad_cerrada, 10) || 1;
        var formaVenta = p.tipo_venta || 'UNIDAD';
        if (formaVenta !== 'FRACCION_DECIMAL') {
            if (intermedios > 0 && valorIntermedio > 0 && porCaja > 1) {
                formaVenta = 'CAJA_INTERMEDIO';
            } else if (porCaja > 1) {
                formaVenta = parseInt(p.fraccion, 10) > 0 ? 'CAJA_UNIDAD' : 'CAJA';
            } else {
                formaVenta = 'UNIDAD';
            }
        }
        $('#p_tipo_venta').val(formaVenta);
        $('#p_unidad_medida').val(p.unidad_medida || '');
        $('#p_cantidad_por_unidad').val(p.cantidad_por_unidad || 1);
        $('#p_unidad_cerrada').val(p.unidad_cerrada || 1);
        $('#p_fraccion').val(p.fraccion || 0);
        $('#p_unidades_intermedias').val(formaVenta === 'CAJA_INTERMEDIO' ? intermedios : 0);
        $('#p_unidades_por_intermedio').val(formaVenta === 'CAJA_INTERMEDIO' ? (parseInt(p.unidades_por_intermedio, 10) || 1) : 1);
        $('#p_nombre_intermedio').val(formaVenta === 'CAJA_INTERMEDIO' ? (p.nombre_intermedio || '') : '');
        $('#p_valor_intermedio').val(formaVenta === 'CAJA_INTERMEDIO' ? valorIntermedio : '');
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
        calcularRentabilidad();

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
        $('#p_tipo_venta').val('UNIDAD');
        $('#p_unidad_cerrada').val(1);
        $('#p_unidad_cerrada').prop('readonly', false);
        $('#p_unidades_intermedias').val(0);
        $('#p_unidades_por_intermedio').val(1);
        $('#p_nombre_intermedio').val('');
        $('#p_valor_intermedio').val('');
        $('#p_fraccion').val(0);
        toggleTipoVenta();
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
