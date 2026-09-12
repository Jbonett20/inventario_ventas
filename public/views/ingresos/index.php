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
                <button class="btn btn-outline-secondary rounded-pill px-3" onclick="recargarIngresos()" title="Recargar datos"><i class="fas fa-sync"></i></button>
                <button class="btn btn-info rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalIngresoRapido"><i class="fas fa-bolt me-2"></i>Ingreso Rápido</button>
            </div>
        </div>
    </div>
</div>

<!-- Pestañas del módulo -->
<ul class="nav nav-pills flex-wrap gap-2 mb-3" id="tabsIngresos" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-3" id="pill-crear" data-bs-toggle="pill" data-bs-target="#tabCrear" type="button" role="tab">
            <i class="fas fa-plus-square me-1"></i>Crear Ingreso
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3" id="pill-factura" data-bs-toggle="pill" data-bs-target="#tabFactura" type="button" role="tab"
                onclick="cargarFacturas()">
            <i class="fas fa-file-invoice me-1"></i>Ingresos Por Factura
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3" id="pill-lista" data-bs-toggle="pill" data-bs-target="#tabLista" type="button" role="tab"
                onclick="cargarIng(ping)">
            <i class="fas fa-list me-1"></i>Lista de Ingresos
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3" id="pill-fecha" data-bs-toggle="pill" data-bs-target="#tabFecha" type="button" role="tab"
                onclick="cargarPorFecha()">
            <i class="fas fa-calendar-alt me-1"></i>Ingresos Por Fecha
        </button>
    </li>
</ul>

<div class="tab-content" id="tabsIngresosContent">

<!-- ===================== TAB 1: CREAR INGRESO ===================== -->
<div class="tab-pane fade show active" id="tabCrear" role="tabpanel">
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-plus-square text-success me-2"></i>Crear Ingreso</h5>
                </div>
                <div class="card-body">
                    <form id="formIngreso" onsubmit="return false;">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Producto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="hidden" name="id_producto" id="ing_id_producto">
                                    <input type="text" class="form-control" id="ing_buscar" placeholder="Buscar producto por código o nombre..." autocomplete="off">
                                    <button class="btn btn-outline-primary" type="button" id="ing_btn_buscar"><i class="fas fa-search"></i></button>
                                </div>
                                <div id="ing_resultados" class="list-group mt-2" style="max-height:220px;overflow-y:auto;display:none;"></div>
                                <div id="ing_producto_seleccionado" class="alert alert-info mt-2 py-2 d-none">
                                    <strong id="ing_prod_nombre"></strong> <small class="text-muted" id="ing_prod_codigo"></small>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Tipo de cantidad</label>
                                <div class="d-flex gap-3">
                                    <label class="btn btn-outline-primary btn-sm rounded-pill active" id="lblTipoUnidad">
                                        <input type="radio" name="tipo_cantidad" value="UNIDAD" checked class="d-none"
                                               onchange="$(this).closest('label').addClass('active').siblings().removeClass('active');cambiarTipoCantIng()">
                                        <i class="fas fa-cube me-1"></i>Unidad
                                    </label>
                                    <label class="btn btn-outline-primary btn-sm rounded-pill" id="lblTipoCaja">
                                        <input type="radio" name="tipo_cantidad" value="CAJA" class="d-none"
                                               onchange="$(this).closest('label').addClass('active').siblings().removeClass('active');cambiarTipoCantIng()">
                                        <i class="fas fa-box me-1"></i>Caja
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" id="lblCantUnd">Cantidad Unidades</label>
                                <input type="number" class="form-control form-control-lg" name="cantidad_unidad" id="ing_cant_und" min="0" value="0">
                                <small class="text-muted" id="ing_info_conversion" style="display:none;"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fracciones (Und sueltas)</label>
                                <input type="number" class="form-control form-control-lg" name="cantidad_fraccion" id="ing_cant_frac" min="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nuevo Precio Compra <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="valor_compra" id="ing_precio" min="0" step="100" placeholder="Vacío = mismo precio">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Observación</label>
                                <input type="text" class="form-control" name="observacion" id="ing_obs" placeholder="Ej: Compra mostrador">
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted fw-semibold"><i class="fas fa-calculator me-1"></i>Efecto sobre el precio</small>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-auto" id="ing_btn_historial" style="display:none;">
                                        <i class="fas fa-history me-1"></i>Historial de precios
                                    </button>
                                </div>
                                <div id="ing_preview" class="border rounded-3 p-3 bg-light" style="display:none;"></div>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-success px-4"><i class="fas fa-save me-2"></i>Registrar Ingreso</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetCrearIngreso()">Limpiar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Últimos ingresos registrados</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Fecha</th><th>Producto</th><th class="text-center">Cant.</th></tr></thead>
                            <tbody id="tbodyIngMini">
                                <tr><td colspan="3" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================== TAB 2: INGRESOS POR FACTURA ===================== -->
<div class="tab-pane fade" id="tabFactura" role="tabpanel">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-medical text-primary me-2"></i>Nueva Factura de Compra</h6>
                </div>
                <div class="card-body">
                    <form id="formFactura" onsubmit="return false;">
                        <input type="hidden" id="fac_id" value="0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre / N° Factura <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fac_nombre" placeholder="Ej: FV-1234" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Proveedor</label>
                            <select class="form-select" id="fac_proveedor">
                                <option value="">-- Sin proveedor --</option>
                                <?php foreach (($proveedores ?? []) as $prov): ?>
                                <option value="<?= (int)$prov['id_proveedor'] ?>"><?= \SIG\Core\View::esc($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha</label>
                            <input type="date" class="form-control" id="fac_fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary px-4" onclick="guardarFactura()">
                                <i class="fas fa-save me-2"></i><span id="fac_btn_txt">Crear Factura</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="nuevaFactura()">Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-list-ul me-2"></i>Facturas de Compra</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="fac_filtro" style="width:auto;" onchange="cargarFacturas()">
                            <option value="TODAS">Todas</option>
                            <option value="PENDIENTES">Pendientes</option>
                            <option value="APLICADAS">En inventario</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" id="fac_buscar" placeholder="Buscar factura..." onkeyup="buscarFacturaDebounce()">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:330px;overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Factura</th><th>Proveedor</th><th>Fecha</th>
                                    <th class="text-center">Prod.</th><th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyFac">
                                <tr><td colspan="6" class="text-center text-muted py-4">Seleccione el proveedor y cree una factura para empezar</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de la factura seleccionada -->
    <div class="card border-0 shadow-sm mt-3 d-none" id="cardDetalleFac">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Factura: <span id="det_fac_nombre"></span>
                    <span class="badge bg-secondary ms-2" id="det_fac_estado"></span>
                </h6>
                <small class="text-muted" id="det_fac_info"></small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm" id="btnPasarInv" onclick="pasarFacturaAInventario()">
                    <i class="fas fa-arrow-right me-1"></i>Pasar a Inventario
                </button>
                <button class="btn btn-warning btn-sm d-none" id="btnRevertirFac" onclick="revertirFactura()">
                    <i class="fas fa-undo me-1"></i>Revertir
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="eliminarFactura()">
                    <i class="fas fa-trash me-1"></i>Eliminar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-5">
                    <label class="form-label fw-semibold">Agregar producto a la factura</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="fac_prod_buscar" placeholder="Código o nombre..." autocomplete="off">
                        <button class="btn btn-outline-primary" type="button" onclick="buscarProductoFactura()"><i class="fas fa-search"></i></button>
                    </div>
                    <div id="fac_prod_resultados" class="list-group mt-2" style="max-height:200px;overflow-y:auto;display:none;"></div>
                    <div class="alert alert-info mt-2 py-2 d-none" id="fac_prod_sel">
                        <strong id="fac_prod_nombre"></strong><br>
                        <small class="text-muted" id="fac_prod_extra"></small>
                    </div>
                    <div class="row g-2 mt-2" id="fac_prod_cant" style="display:none;">
                        <div class="col-4">
                            <label class="form-label small fw-semibold mb-1">Unidades</label>
                            <input type="number" class="form-control" id="fac_cant_u" min="0" value="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold mb-1">Fracciones</label>
                            <input type="number" class="form-control" id="fac_cant_f" min="0" value="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold mb-1">Precio compra <i class="fas fa-question-circle text-muted" title="Lo que le cobra el proveedor por unidad cerrada (caja)"></i></label>
                            <input type="number" class="form-control" id="fac_precio" min="0" step="0.01" placeholder="$ / unidad">
                        </div>
                        <div class="col-12">
                            <small class="text-muted" id="fac_precio_info"></small>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success btn-sm w-100" onclick="agregarProductoFactura()">
                                <i class="fas fa-plus me-1"></i>Agregar a la factura
                            </button>
                        </div>
                    </div>
                    <div id="fac_bloqueo" class="alert alert-warning mt-3 py-2 d-none">
                        <i class="fas fa-lock me-1"></i>Esta factura ya está en inventario. Use <strong>Revertir</strong> para poder editarla.
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Und</th>
                                    <th class="text-center">Frac</th>
                                    <th class="text-end">V. Compra</th>
                                    <th class="text-end">Inversión</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetFac"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Totales</th>
                                    <th class="text-end" id="det_fac_inversion">$0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================== TAB 3: LISTA DE INGRESOS ===================== -->
<div class="tab-pane fade" id="tabLista" role="tabpanel">

<!-- Historial de ingresos -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-history me-2"></i>Historial de Ingresos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Producto</th><th>Código</th><th class="text-center">Cajas</th><th class="text-center">Und</th><th>Tipo</th><th>Usuario</th><th>Observación</th><th class="text-center">Acciones</th></tr>
                </thead>
                <tbody id="tbodyIng">
                    <tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagIng"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagIng"></ul></nav>
</div>

</div><!-- /tabLista -->

<!-- ===================== TAB 4: INGRESOS POR FECHA ===================== -->
<div class="tab-pane fade" id="tabFecha" role="tabpanel">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Inicial</label>
                    <input type="date" class="form-control" id="pf_desde" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Final</label>
                    <input type="date" class="form-control" id="pf_hasta" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tipo</label>
                    <select class="form-select" id="pf_tipo">
                        <option value="TODOS">Todos</option>
                        <option value="MANUAL">Manual / Rápido</option>
                        <option value="FACTURA_COMPRA">Por Factura</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" onclick="cargarPorFecha()"><i class="fas fa-search me-2"></i>Buscar</button>
                    <button class="btn btn-outline-success" onclick="exportarPorFecha('csv')" title="Exportar Excel"><i class="fas fa-file-excel"></i></button>
                    <button class="btn btn-outline-danger" onclick="exportarPorFecha('pdf')" title="Exportar PDF"><i class="fas fa-file-pdf"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Registros</h6>
                    <h3 class="fw-bold mb-0" id="pfk_registros">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Unidades / Fracciones</h6>
                    <h3 class="fw-bold mb-0" id="pfk_unidades">0 / 0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Inversión</h6>
                    <h3 class="fw-bold mb-0 text-primary" id="pfk_inversion">$0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Ganancia Estimada</h6>
                    <h3 class="fw-bold mb-0 text-success" id="pfk_ganancia">$0</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2"></i>Ingresos en el rango seleccionado</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Producto</th><th>Código</th>
                            <th class="text-center">Und</th><th class="text-center">Frac</th>
                            <th>Tipo</th><th class="text-end">Inversión</th><th class="text-end">Venta</th>
                            <th class="text-end">Ganancia</th><th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPF">
                        <tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-calendar-alt fa-2x d-block mb-2"></i>Seleccione un rango de fechas y pulse Buscar</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div><!-- /tab-content -->

<!-- Modal Detalle de Ingreso -->
<div class="modal fade" id="modalDetalleIng" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-search-plus me-2"></i>Detalle del Ingreso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleIngBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ingreso Rápido -->
<div class="modal fade" id="modalIngresoRapido" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-bolt me-2"></i>Ingreso Rápido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Código / Código de Barras</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" class="form-control form-control-lg" id="rap_codigo" placeholder="Escanee o digite el código..." autocomplete="off" autofocus>
                    </div>
                    <div id="rap_info_producto" class="alert alert-info mt-2 py-2 d-none">
                        <strong id="rap_prod_nombre"></strong><br>
                        <small class="text-muted">Stock actual: <span id="rap_stock_actual">0</span></small>
                    </div>
                    <div id="rap_no_encontrado" class="alert alert-warning mt-2 py-2 d-none">
                        <i class="fas fa-exclamation-triangle me-1"></i>Producto no encontrado.
                        <a href="<?= $basePath ?>/productos">Crear producto</a>
                    </div>
                </div>
                <div id="rap_campos" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo</label>
                        <div class="d-flex gap-2">
                            <label class="btn btn-outline-primary btn-sm rounded-pill active">
                                <input type="radio" name="rap_tipo" value="UNIDAD" checked class="d-none"
                                       onchange="$(this).closest('label').addClass('active').siblings().removeClass('active')">
                                Unidad
                            </label>
                            <label class="btn btn-outline-primary btn-sm rounded-pill">
                                <input type="radio" name="rap_tipo" value="CAJA" class="d-none"
                                       onchange="$(this).closest('label').addClass('active').siblings().removeClass('active')">
                                Caja
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cantidad</label>
                        <input type="number" class="form-control form-control-lg" id="rap_cantidad" min="1" value="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nuevo Precio Compra <small class="text-muted">(opcional)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="rap_precio" min="0" step="100" placeholder="Dejar vacío = mismo precio">
                        </div>
                        <small class="text-muted" id="rap_info_precio"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info px-4" id="btnRapido" onclick="procesarIngresoRapido()" disabled>
                    <i class="fas fa-check me-2"></i>Registrar Ingreso
                </button>
            </div>
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
        if (!d.data.length) { html = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay ingresos</td></tr>'; }
        else {
            $.each(d.data, function(i, ing) {
                var esFactura = ing.tipo === 'FACTURA_COMPRA';
                html += '<tr><td>' + ing.fecha + '</td><td><strong>' + escHtml(ing.descripcion) + '</strong></td><td>' + escHtml(ing.codigo) + '</td>' +
                    '<td class="text-center fw-bold">' + (ing.cantidad_unidad||0) + '</td><td class="text-center">' + (ing.cantidad_fraccion||0) + '</td>' +
                    '<td><span class="badge ' + (esFactura ? 'bg-primary' : 'bg-info') + '">' + escHtml(ing.tipo) + '</span></td>' +
                    '<td>' + escHtml(ing.nombre_usuario || '-') + '</td><td><small>' + escHtml(ing.observacion || '-') + '</small></td>' +
                    '<td class="text-center text-nowrap">' +
                        '<button class="btn btn-sm btn-outline-primary me-1" onclick="verDetalleIngreso(' + ing.id_ingreso + ')" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
                        (esFactura ? '' : '<button class="btn btn-sm btn-outline-danger" onclick="borrarIngreso(' + ing.id_ingreso + ')" title="Eliminar"><i class="fas fa-trash"></i></button>') +
                    '</td></tr>';
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

var undPorCajaActual = 1;

function cambiarTipoCantIng() {
    var tipo = $('input[name="tipo_cantidad"]:checked').val();
    if (tipo === 'CAJA') {
        $('#lblCantUnd').text('Cantidad Cajas');
        $('#ing_info_conversion').show();
        actualizarInfoConversion();
    } else {
        $('#lblCantUnd').text('Cantidad Unidades');
        $('#ing_info_conversion').hide();
    }
}

function actualizarInfoConversion() {
    var cajas = parseInt($('#ing_cant_und').val()) || 0;
    var total = cajas * undPorCajaActual;
    $('#ing_info_conversion').text(cajas + ' caja(s) × ' + undPorCajaActual + ' und/caja = ' + total + ' unidades');
}

$('#ing_cant_und').on('keyup change', function() {
    if ($('input[name="tipo_cantidad"]:checked').val() === 'CAJA') {
        actualizarInfoConversion();
    }
});

function seleccionarProductoIng(id, nombre, codigo) {
    $('#ing_id_producto').val(id);
    $('#ing_prod_nombre').text(nombre);
    $('#ing_prod_codigo').text('Cód: ' + codigo);
    $('#ing_producto_seleccionado').removeClass('d-none');
    $('#ing_resultados').hide();
    $('#ing_buscar').val(nombre);
    $('#ing_btn_historial').show();
    // Obtener unidad_cerrada del producto
    $.getJSON(BASE_URL + '/productos/obtener/' + id, function(r) {
        if (r.success && r.data) {
            undPorCajaActual = parseInt(r.data.unidad_cerrada) || 1;
            actualizarInfoConversion();
            $('#ing_info_conversion').show();
        }
    });
    programarPreview();
    $('#ing_cant_und').focus();
}

// ============================================================
// PREVISUALIZACIÓN: qué pasará con el costo y el precio de venta
// (no guarda nada, solo consulta al servidor)
// ============================================================
var timerPrev = null;

function programarPreview() {
    clearTimeout(timerPrev);
    timerPrev = setTimeout(previsualizarIngreso, 350);
}

function previsualizarIngreso() {
    var id = $('#ing_id_producto').val();
    if (!id) { $('#ing_preview').hide(); return; }

    var cant = parseInt($('#ing_cant_und').val()) || 0;
    if (cant <= 0) { $('#ing_preview').hide(); return; }

    var tipo   = $('input[name="tipo_cantidad"]:checked').val() || 'UNIDAD';
    var precio = $('#ing_precio').val();

    $.getJSON(BASE_URL + '/ingresos/previsualizar', {
        id_producto: id, cantidad_unidad: cant, tipo_cantidad: tipo, valor_compra: precio
    }, function(r) {
        if (!r.success) { $('#ing_preview').hide(); return; }

        var c = r.data.calculo;
        var h = '<div class="row g-2">';
        h += '<div class="col-sm-6"><small class="text-muted d-block">Costo promedio</small>' +
             '<span class="text-muted text-decoration-line-through">$' + formatoNumero(c.costo_actual) + '</span>' +
             ' <i class="fas fa-arrow-right mx-1 text-muted"></i> ' +
             '<strong class="' + (c.cambia_costo ? 'text-primary' : 'text-muted') + '">$' + formatoNumero(c.costo_nuevo) + '</strong></div>';
        h += '<div class="col-sm-6"><small class="text-muted d-block">Precio de venta</small>' +
             '<span class="text-muted text-decoration-line-through">$' + formatoNumero(c.venta_actual) + '</span>' +
             ' <i class="fas fa-arrow-right mx-1 text-muted"></i> ' +
             '<strong class="' + (c.cambia_venta ? (c.venta_nueva > c.venta_actual ? 'text-danger' : 'text-success') : 'text-muted') + '">$' + formatoNumero(c.venta_nueva) + '</strong></div>';
        h += '</div>';

        h += '<div class="mt-2 small text-muted">' +
             '<i class="fas fa-boxes me-1"></i>Stock: <strong>' + c.stock_antes + '</strong> + <strong>' + c.cantidad + '</strong> = <strong>' + (c.stock_antes + c.cantidad) + '</strong>' +
             ' &nbsp;|&nbsp; <i class="fas fa-percent me-1"></i>Rentabilidad: <strong>' + c.rentabilidad_final + '%</strong>' +
             ' &nbsp;|&nbsp; ' + escHtml(r.data.descripcion_cantidad) + '</div>';

        if (c.unidad_actual !== null && c.unidad_nueva !== null && c.unidad_nueva != c.unidad_actual) {
            h += '<div class="mt-1 small text-muted"><i class="fas fa-tablets me-1"></i>Precio unidad suelta: $' + formatoNumero(c.unidad_actual) +
                 ' <i class="fas fa-arrow-right mx-1"></i><strong>$' + formatoNumero(c.unidad_nueva) + '</strong></div>';
        }
        if (c.tope_regulado) {
            h += '<div class="mt-1 small text-muted"><i class="fas fa-balance-scale me-1"></i>Precio máximo regulado: <strong>$' + formatoNumero(c.tope_regulado) + '</strong></div>';
        }
        if (c.aviso) {
            h += '<div class="alert alert-warning mt-2 mb-0 py-2 small"><i class="fas fa-exclamation-triangle me-1"></i>' + escHtml(c.aviso) + '</div>';
        }
        $('#ing_preview').html(h).show();
    });
}

$(document).on('keyup change input', '#ing_cant_und, #ing_cant_frac, #ing_precio', programarPreview);
$(document).on('change', 'input[name="tipo_cantidad"]', programarPreview);

// ============================================================
// HISTORIAL DE PRECIOS Y COMPRAS (ver partials/historial_precios.php)
// ============================================================
$('#ing_btn_historial').on('click', function() {
    var id = $('#ing_id_producto').val();
    if (!id) { PNotify.error({ text: 'Seleccione un producto' }); return; }
    verHistorialPrecios(id);
});


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
    if (!data.valor_compra || parseFloat(data.valor_compra) <= 0) { delete data.valor_compra; }
    $.ajax({
        url: BASE_URL + '/ingresos/guardar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                resetCrearIngreso();
                cargarIng(1);
                cargarIngresosMini();
                PNotify.success({ text: r.message });
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: xhr.responseJSON?.message || 'Error' }); }
    });
});

function resetCrearIngreso() {
    $('#formIngreso')[0].reset();
    $('#ing_id_producto').val('');
    $('#ing_producto_seleccionado').addClass('d-none');
    $('#ing_resultados').hide();
    $('#ing_info_conversion').hide();
    $('#lblCantUnd').text('Cantidad Unidades');
    $('#ing_preview').hide().html('');
    $('#ing_btn_historial').hide();
    undPorCajaActual = 1;
}

// ===== INGRESO RÁPIDO =====
var rapIdProducto = 0;
var rapStockActual = 0;

$('#rap_codigo').on('keyup', function() {
    var cod = $(this).val().trim();
    if (cod.length < 2) {
        $('#rap_info_producto').addClass('d-none');
        $('#rap_no_encontrado').addClass('d-none');
        $('#rap_campos').hide();
        $('#btnRapido').prop('disabled', true);
        return;
    }
    clearTimeout(window.rapTimer);
    window.rapTimer = setTimeout(function() {
        $.getJSON(BASE_URL + '/ingresos/buscar-productos', { q: cod }, function(r) {
            if (r.success && r.data.length === 1) {
                var p = r.data[0];
                rapIdProducto = p.id_producto;
                rapStockActual = parseInt(p.unidad) || 0;
                $('#rap_prod_nombre').text(p.descripcion + ' (' + p.codigo + ')');
                $('#rap_stock_actual').text(rapStockActual);
                $('#rap_info_producto').removeClass('d-none');
                $('#rap_no_encontrado').addClass('d-none');
                $('#rap_campos').show();
                $('#btnRapido').prop('disabled', false);
                $('#rap_cantidad').focus().select();
                // Mostrar precio actual
                $('#rap_info_precio').text('Precio compra actual: $' + formatoNumero(p.valor_compra));
            } else if (r.success && r.data.length > 1) {
                $('#rap_info_producto').addClass('d-none');
                $('#rap_no_encontrado').removeClass('d-none');
                $('#rap_no_encontrado').html('<i class="fas fa-exclamation-triangle me-1"></i>Múltiples productos. Sea más específico.');
                $('#rap_campos').hide();
                $('#btnRapido').prop('disabled', true);
            } else {
                $('#rap_info_producto').addClass('d-none');
                $('#rap_no_encontrado').removeClass('d-none');
                $('#rap_no_encontrado').html('<i class="fas fa-exclamation-triangle me-1"></i>Producto no encontrado. <a href="' + BASE_URL + '/productos">Crear producto</a>');
                $('#rap_campos').hide();
                $('#btnRapido').prop('disabled', true);
            }
        });
    }, 300);
});

function procesarIngresoRapido() {
    if (!rapIdProducto) { PNotify.error({ text: 'Producto no válido' }); return; }
    var cant = parseInt($('#rap_cantidad').val()) || 0;
    if (cant <= 0) { PNotify.error({ text: 'Debe ingresar una cantidad' }); return; }
    var tipo = $('input[name="rap_tipo"]:checked').val() || 'UNIDAD';
    var precio = parseFloat($('#rap_precio').val()) || 0;

    var data = {
        codigo: $('#rap_codigo').val().trim(),
        cantidad_unidad: cant,
        cantidad_fraccion: 0,
        tipo_cantidad: tipo,
        observacion: 'Ingreso rápido'
    };
    if (precio > 0) data.valor_compra = precio;

    $('#btnRapido').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');

    $.ajax({
        url: BASE_URL + '/ingresos/rapido', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalIngresoRapido').modal('hide');
                cargarIng(1);
                PNotify.success({ text: r.message + ' | Stock: ' + r.data.stock_anterior + ' → ' + r.data.stock_nuevo });
            } else {
                PNotify.error({ text: r.message });
            }
            $('#btnRapido').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Registrar Ingreso');
        },
        error: function(xhr) {
            PNotify.error({ text: xhr.responseJSON?.message || 'Error' });
            $('#btnRapido').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Registrar Ingreso');
        }
    });
}

$('#modalIngresoRapido').on('hidden.bs.modal', function() {
    $('#rap_codigo').val('');
    $('#rap_info_producto').addClass('d-none');
    $('#rap_no_encontrado').addClass('d-none');
    $('#rap_campos').hide();
    $('#rap_cantidad').val(1);
    $('#rap_precio').val('');
    rapIdProducto = 0;
    $('#btnRapido').prop('disabled', true);
});

// ============================================================
// TAB 2: INGRESOS POR FACTURA
// ============================================================
var facActual = null;
var detalleFac = [];
var facProdSel = null;
var timerFacBusq;

function cargarFacturas() {
    var filtro = $('#fac_filtro').val() || 'TODAS';
    var buscar = $('#fac_buscar').val() || '';
    $.getJSON(BASE_URL + '/ingresos/facturas', { filtro: filtro, buscar: buscar }, function(r) {
        if (!r.success) return;
        var html = '';
        if (!r.data.length) {
            html = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay facturas registradas</td></tr>';
        } else {
            $.each(r.data, function(i, f) {
                var aplicada = parseInt(f.estado) === 1;
                var activa = facActual && parseInt(facActual.id_ingreso_factura) === parseInt(f.id_ingreso_factura);
                html += '<tr class="' + (activa ? 'table-active' : '') + '">' +
                    '<td><a href="#" onclick="seleccionarFactura(' + f.id_ingreso_factura + ');return false;"><strong>' + escHtml(f.nombre_factura) + '</strong></a></td>' +
                    '<td><small>' + escHtml(f.proveedor_nombre || '-') + '</small></td>' +
                    '<td><small>' + f.fecha + '</small></td>' +
                    '<td class="text-center">' + (f.total_productos || 0) + '</td>' +
                    '<td class="text-center"><span class="badge ' + (aplicada ? 'bg-success' : 'bg-warning text-dark') + '">' + (aplicada ? 'En inventario' : 'Pendiente') + '</span></td>' +
                    '<td class="text-center text-nowrap">' +
                        '<button class="btn btn-sm btn-outline-primary me-1" onclick="seleccionarFactura(' + f.id_ingreso_factura + ')" title="Abrir"><i class="fas fa-folder-open"></i></button>' +
                        '<button class="btn btn-sm btn-outline-danger" onclick="borrarFactura(' + f.id_ingreso_factura + ')" title="Eliminar"><i class="fas fa-trash"></i></button>' +
                    '</td></tr>';
            });
        }
        $('#tbodyFac').html(html);
    });
}

function buscarFacturaDebounce() {
    clearTimeout(timerFacBusq);
    timerFacBusq = setTimeout(cargarFacturas, 350);
}

function nuevaFactura() {
    $('#fac_id').val(0);
    $('#fac_nombre').val('');
    $('#fac_proveedor').val('');
    $('#fac_fecha').val(new Date().toISOString().slice(0, 10));
    $('#fac_btn_txt').text('Crear Factura');
    $('#cardDetalleFac').addClass('d-none');
    facActual = null;
    cargarFacturas();
}

function guardarFactura() {
    var nombre = ($('#fac_nombre').val() || '').trim();
    if (!nombre) { PNotify.error({ text: 'Indique el nombre o número de la factura' }); return; }

    var id = parseInt($('#fac_id').val()) || 0;
    var data = {
        nombre_factura: nombre,
        id_proveedor: $('#fac_proveedor').val() || null,
        fecha: $('#fac_fecha').val()
    };
    var url = BASE_URL + '/ingresos/factura/crear';
    if (id > 0) { data.id_ingreso_factura = id; url = BASE_URL + '/ingresos/factura/actualizar'; }

    $.ajax({
        url: url, method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message });
                var nuevoId = (r.data && r.data.id_ingreso_factura) ? r.data.id_ingreso_factura : id;
                $('#fac_id').val(nuevoId);
                $('#fac_btn_txt').text('Actualizar Factura');
                seleccionarFactura(nuevoId);
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar la factura' }); }
    });
}

function seleccionarFactura(id) {
    $.getJSON(BASE_URL + '/ingresos/factura/detalle', { id: id }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        facActual = r.data.factura;
        detalleFac = r.data.detalle || [];

        var aplicada = parseInt(facActual.estado) === 1;

        $('#cardDetalleFac').removeClass('d-none');
        $('#det_fac_nombre').text(facActual.nombre_factura);
        $('#det_fac_info').text('Proveedor: ' + (facActual.proveedor_nombre || 'Sin proveedor') + '  |  Fecha: ' + facActual.fecha);
        $('#det_fac_estado')
            .text(aplicada ? 'En inventario' : 'Pendiente')
            .removeClass('bg-success bg-warning bg-secondary bg-warning text-dark')
            .addClass(aplicada ? 'bg-success' : 'bg-warning text-dark');

        $('#btnPasarInv').toggleClass('d-none', aplicada);
        $('#btnRevertirFac').toggleClass('d-none', !aplicada);
        $('#fac_bloqueo').toggleClass('d-none', !aplicada);

        $('#fac_id').val(facActual.id_ingreso_factura);
        $('#fac_nombre').val(facActual.nombre_factura);
        $('#fac_proveedor').val(facActual.id_proveedor || '');
        $('#fac_fecha').val(facActual.fecha);
        $('#fac_btn_txt').text('Actualizar Factura');

        pintarDetalleFac();
        cargarFacturas();
    });
}

function pintarDetalleFac() {
    var html = '', inversion = 0;
    if (!detalleFac.length) {
        html = '<tr><td colspan="6" class="text-center text-muted py-4">Sin productos. Agregue productos a esta factura.</td></tr>';
    } else {
        $.each(detalleFac, function(i, d) {
            var cantU = parseInt(d.cantidad_unidad) || 0;
            var cantF = parseInt(d.cantidad_fraccion) || 0;
            // Precio REAL de esta factura; si la linea no lo trae, se muestra el costo actual del producto
            var valor = parseFloat(d.precio_compra) || 0;
            var costoActual = parseFloat(d.costo_promedio) || 0;
            var esPrecioPropio = valor > 0;
            if (!esPrecioPropio) valor = costoActual;

            var subtotal = cantU * valor;
            inversion += subtotal;

            // Aviso si el precio de la factura cambia respecto al costo que tiene el producto hoy
            var aviso = '';
            if (esPrecioPropio && costoActual > 0 && Math.abs(valor - costoActual) >= 0.01) {
                var sube = valor > costoActual;
                aviso = ' <i class="fas fa-arrow-' + (sube ? 'up text-danger' : 'down text-success') +
                        '" title="El costo promedio del producto hoy es $' + formatoNumero(costoActual) + '"></i>';
            }

            html += '<tr>' +
                '<td><strong>' + escHtml(d.descripcion) + '</strong><br><small class="text-muted">' + escHtml(d.codigo) + '</small></td>' +
                '<td class="text-center">' + cantU + '</td>' +
                '<td class="text-center">' + cantF + '</td>' +
                '<td class="text-end">$' + formatoNumero(valor) + aviso + '</td>' +
                '<td class="text-end">$' + formatoNumero(subtotal) + '</td>' +
                '<td class="text-center"><button class="btn btn-sm btn-outline-danger" onclick="quitarProductoFactura(' + d.id_detalle + ')" title="Quitar"><i class="fas fa-times"></i></button></td>' +
                '</tr>';
        });
    }
    $('#tbodyDetFac').html(html);
    $('#det_fac_inversion').text('$' + formatoNumero(inversion));
}

function buscarProductoFactura() {
    var q = ($('#fac_prod_buscar').val() || '').trim();
    if (q.length < 2) { $('#fac_prod_resultados').hide(); return; }
    $.getJSON(BASE_URL + '/ingresos/buscar-productos', { q: q }, function(r) {
        if (!r.success || !r.data.length) {
            $('#fac_prod_resultados').html('<div class="list-group-item text-muted">Sin resultados</div>').show();
            return;
        }
        var html = '';
        $.each(r.data, function(i, p) {
            html += '<button type="button" class="list-group-item list-group-item-action" onclick="elegirProductoFactura(' + p.id_producto + ',\'' +
                escHtml(p.descripcion).replace(/'/g, "\\'") + '\',\'' + escHtml(p.codigo) + '\',' + (parseFloat(p.valor_compra) || 0) + ')">' +
                '<strong>' + escHtml(p.codigo) + '</strong> - ' + escHtml(p.descripcion) +
                ' <small class="text-muted">Stock: ' + (p.unidad || 0) + ' / ' + (p.stock_fraccion || 0) + '</small></button>';
        });
        $('#fac_prod_resultados').html(html).show();
    });
}

function elegirProductoFactura(id, nombre, codigo, valorCompra) {
    facProdSel = { id: id, nombre: nombre, codigo: codigo, costoActual: valorCompra };
    $('#fac_prod_resultados').hide();
    $('#fac_prod_buscar').val(nombre);
    $('#fac_prod_nombre').text(nombre);
    $('#fac_prod_extra').text('Cód: ' + codigo + '  |  Costo promedio actual: $' + formatoNumero(valorCompra));
    $('#fac_prod_sel').removeClass('d-none');
    $('#fac_prod_cant').show();
    $('#fac_cant_u').val(0).focus();
    $('#fac_cant_f').val(0);
    // Se precarga el costo actual; el usuario lo cambia si el proveedor cobró otro precio
    $('#fac_precio').val(valorCompra > 0 ? valorCompra : '');
    actualizarInfoPrecioFactura();
}

function actualizarInfoPrecioFactura() {
    if (!facProdSel) { $('#fac_precio_info').text(''); return; }
    var precio = parseFloat($('#fac_precio').val()) || 0;
    var cu = parseInt($('#fac_cant_u').val()) || 0;
    var cf = parseInt($('#fac_cant_f').val()) || 0;

    if (precio <= 0) {
        $('#fac_precio_info').html('<i class="fas fa-info-circle me-1"></i>Sin precio: se usará el costo promedio actual ($' + formatoNumero(facProdSel.costoActual) + ')');
        return;
    }

    var texto = 'Total: ' + cu + ' und × $' + formatoNumero(precio) + ' = <strong>$' + formatoNumero(cu * precio) + '</strong>';
    if (cf > 0) texto += ' <span class="text-muted">(+ ' + cf + ' fracciones, sin costo asignado)</span>';
    if (facProdSel.costoActual > 0 && Math.abs(precio - facProdSel.costoActual) >= 0.01) {
        texto += '<br><i class="fas fa-exclamation-triangle text-warning me-1"></i>Distinto al costo actual ($' + formatoNumero(facProdSel.costoActual) + '): al pasar la factura a inventario se recalculará el promedio.';
    }
    $('#fac_precio_info').html(texto);
}

$(document).on('keyup change input', '#fac_cant_u, #fac_cant_f, #fac_precio', actualizarInfoPrecioFactura);

function agregarProductoFactura() {
    if (!facActual) { PNotify.error({ text: 'Seleccione una factura primero' }); return; }
    if (!facProdSel) { PNotify.error({ text: 'Seleccione un producto' }); return; }

    var cu = parseInt($('#fac_cant_u').val()) || 0;
    var cf = parseInt($('#fac_cant_f').val()) || 0;
    var precio = parseFloat($('#fac_precio').val()) || 0;
    if (cu <= 0 && cf <= 0) { PNotify.error({ text: 'Ingrese al menos una cantidad' }); return; }

    $.ajax({
        url: BASE_URL + '/ingresos/factura/agregar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({
            id_ingreso_factura: facActual.id_ingreso_factura,
            id_producto: facProdSel.id,
            cantidad_unidad: cu,
            cantidad_fraccion: cf,
            valor_compra: precio > 0 ? precio : null
        }),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message });
                facProdSel = null;
                $('#fac_prod_sel').addClass('d-none');
                $('#fac_prod_cant').hide();
                $('#fac_prod_buscar').val('');
                $('#fac_precio').val('');
                $('#fac_precio_info').text('');
                seleccionarFactura(facActual.id_ingreso_factura);
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al agregar el producto' }); }
    });
}

function quitarProductoFactura(idDetalle) {
    $.ajax({
        url: BASE_URL + '/ingresos/factura/quitar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_detalle: idDetalle }),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message });
                if (facActual) seleccionarFactura(facActual.id_ingreso_factura);
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error' }); }
    });
}

function pasarFacturaAInventario() {
    if (!facActual) return;
    if (!confirm('¿Pasar la factura "' + facActual.nombre_factura + '" al inventario?\n\nSe sumará el stock de todos sus productos.')) return;
    $.ajax({
        url: BASE_URL + '/ingresos/factura/pasar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_ingreso_factura: facActual.id_ingreso_factura }),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message });
                seleccionarFactura(facActual.id_ingreso_factura);
                cargarIng(ping); cargarIngresosMini();
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al pasar a inventario' }); }
    });
}

function revertirFactura() {
    if (!facActual) return;
    if (!confirm('¿Revertir esta factura?\n\nSe devolverá el stock que ingresó.')) return;
    $.ajax({
        url: BASE_URL + '/ingresos/factura/revertir', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_ingreso_factura: facActual.id_ingreso_factura }),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message });
                seleccionarFactura(facActual.id_ingreso_factura);
                cargarIng(ping); cargarIngresosMini();
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al revertir' }); }
    });
}

function eliminarFactura() {
    if (!facActual) return;
    borrarFactura(facActual.id_ingreso_factura, true);
}

function borrarFactura(id, cerrarDetalle) {
    if (!confirm('¿Eliminar esta factura de compra?\n\nSi ya está en inventario, primero se revierte el stock.')) return;
    $.ajax({
        url: BASE_URL + '/ingresos/factura/eliminar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_ingreso_factura: id }),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message });
                if (cerrarDetalle || (facActual && parseInt(facActual.id_ingreso_factura) === parseInt(id))) {
                    facActual = null;
                    $('#cardDetalleFac').addClass('d-none');
                }
                nuevaFactura();
                cargarIng(ping); cargarIngresosMini();
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al eliminar' }); }
    });
}

// ============================================================
// TAB 4: INGRESOS POR FECHA
// ============================================================
function cargarPorFecha() {
    var desde = $('#pf_desde').val(), hasta = $('#pf_hasta').val(), tipo = $('#pf_tipo').val() || 'TODOS';
    if (!desde || !hasta) { PNotify.error({ text: 'Seleccione las dos fechas' }); return; }

    $('#tbodyPF').html('<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');

    $.getJSON(BASE_URL + '/ingresos/por-fecha', { desde: desde, hasta: hasta, tipo: tipo }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        var d = r.data, html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay ingresos en ese rango de fechas</td></tr>';
        } else {
            $.each(d.data, function(i, x) {
                html += '<tr>' +
                    '<td>' + x.fecha + '</td>' +
                    '<td><strong>' + escHtml(x.descripcion) + '</strong></td>' +
                    '<td>' + escHtml(x.codigo) + '</td>' +
                    '<td class="text-center">' + (x.cantidad_unidad || 0) + '</td>' +
                    '<td class="text-center">' + (x.cantidad_fraccion || 0) + '</td>' +
                    '<td><span class="badge ' + (x.tipo === 'FACTURA_COMPRA' ? 'bg-primary' : 'bg-info') + '">' + escHtml(x.tipo) + '</span></td>' +
                    '<td class="text-end">$' + formatoNumero(x.inversion) + '</td>' +
                    '<td class="text-end">$' + formatoNumero(x.venta) + '</td>' +
                    '<td class="text-end ' + (x.ganancia >= 0 ? 'text-success' : 'text-danger') + '">$' + formatoNumero(x.ganancia) + '</td>' +
                    '<td>' + escHtml(x.nombre_usuario || '-') + '</td>' +
                    '</tr>';
            });
        }
        $('#tbodyPF').html(html);
        $('#pfk_registros').text(d.totales.registros);
        $('#pfk_unidades').text(d.totales.unidad + ' / ' + d.totales.fraccion);
        $('#pfk_inversion').text('$' + formatoNumero(d.totales.inversion));
        $('#pfk_ganancia').text('$' + formatoNumero(d.totales.ganancia));
    });
}

function exportarPorFecha(formato) {
    var desde = $('#pf_desde').val(), hasta = $('#pf_hasta').val();
    if (!desde || !hasta) { PNotify.error({ text: 'Seleccione las dos fechas' }); return; }
    var sql = "SELECT i.fecha AS Fecha, p.codigo AS Codigo, p.descripcion AS Producto, i.cantidad_unidad AS Unidades, " +
              "i.cantidad_fraccion AS Fracciones, i.tipo AS Tipo, i.observacion AS Observacion " +
              "FROM vb_ingresos i JOIN vb_productos p ON i.id_producto=p.id_producto " +
              "WHERE i.fecha BETWEEN '" + desde + "' AND '" + hasta + "' ORDER BY i.fecha DESC";
    window.open(BASE_URL + '/exportar/' + formato + '?sql=' + encodeURIComponent(sql) +
                '&nombre=ingresos_por_fecha&titulo=Ingresos por fecha', '_blank');
}

// ============================================================
// DETALLE / ELIMINAR INGRESO
// ============================================================
function verDetalleIngreso(id) {
    $('#detalleIngBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#modalDetalleIng').modal('show');
    $.getJSON(BASE_URL + '/ingresos/obtener/' + id, function(r) {
        if (!r.success || !r.data) {
            $('#detalleIngBody').html('<div class="alert alert-danger mb-0">' + escHtml(r.message || 'No se pudo cargar el ingreso') + '</div>');
            return;
        }
        var x = r.data;
        var html = '<table class="table table-sm mb-0">';
        html += '<tr><th class="text-muted">Producto</th><td><strong>' + escHtml(x.descripcion) + '</strong> <small class="text-muted">(' + escHtml(x.codigo) + ')</small></td></tr>';
        html += '<tr><th class="text-muted">Presentación</th><td>' + escHtml(x.presentacion || '-') + '</td></tr>';
        html += '<tr><th class="text-muted">Fecha</th><td>' + x.fecha + '</td></tr>';
        html += '<tr><th class="text-muted">Tipo</th><td><span class="badge bg-info">' + escHtml(x.tipo) + '</span></td></tr>';
        html += '<tr><th class="text-muted">Unidades</th><td>' + (x.cantidad_unidad || 0) + '</td></tr>';
        html += '<tr><th class="text-muted">Fracciones</th><td>' + (x.cantidad_fraccion || 0) + '</td></tr>';
        html += '<tr><th class="text-muted">Factura</th><td>' + escHtml(x.nombre_factura || '-') + '</td></tr>';
        html += '<tr><th class="text-muted">Usuario</th><td>' + escHtml(x.nombre_usuario || '-') + '</td></tr>';
        html += '<tr><th class="text-muted">Observación</th><td>' + escHtml(x.observacion || '-') + '</td></tr>';
        html += '</table>';
        $('#detalleIngBody').html(html);
    });
}

function borrarIngreso(id) {
    if (!confirm('¿Eliminar este ingreso?\n\nSe descontará del stock.')) return;
    $.ajax({
        url: BASE_URL + '/ingresos/eliminar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_ingreso: id }),
        success: function(r) {
            if (r.success) { PNotify.success({ text: r.message }); cargarIng(ping); cargarIngresosMini(); }
            else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al eliminar' }); }
    });
}

function cargarIngresosMini() {
    $.getJSON(BASE_URL + '/ingresos/listar', { page: 1 }, function(r) {
        if (!r.success) return;
        var html = '';
        if (!r.data.data.length) {
            html = '<tr><td colspan="3" class="text-center text-muted py-4">Sin ingresos registrados</td></tr>';
        } else {
            $.each(r.data.data.slice(0, 15), function(i, ing) {
                html += '<tr><td><small>' + ing.fecha + '</small></td><td><small>' + escHtml(ing.descripcion) + '</small></td>' +
                    '<td class="text-center">' + (ing.cantidad_unidad || 0) + '</td></tr>';
            });
        }
        $('#tbodyIngMini').html(html);
    });
}

function recargarIngresos() {
    cargarIng(ping);
    cargarIngresosMini();
    cargarFacturas();
    PNotify.success({ text: 'Datos actualizados' });
}

$(function() {
    cargarIng(1);
    cargarIngresosMini();

    // El botón de búsqueda y la tecla Enter disparan la búsqueda de producto
    $('#ing_btn_buscar').on('click', function() { $('#ing_buscar').trigger('keyup'); });
    $('#ing_buscar').on('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); $(this).trigger('keyup'); }
    });

    // Enter en el buscador de productos de la factura
    $('#fac_prod_buscar').on('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarProductoFactura(); }
    });
});
</script>

<?php include __DIR__ . '/../partials/historial_precios.php'; ?>
