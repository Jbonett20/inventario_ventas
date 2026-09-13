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

<!-- Pestañas del módulo -->
<ul class="nav nav-pills flex-wrap gap-2 mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabInv" type="button">
        <i class="fas fa-warehouse me-1"></i>Inventario</button></li>
    <li class="nav-item"><button class="nav-link rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabMov" type="button">
        <i class="fas fa-exchange-alt me-1"></i>Movimiento del Producto</button></li>
    <li class="nav-item"><button class="nav-link rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabBajo" type="button" onclick="cargarReorden()">
        <i class="fas fa-exclamation-triangle me-1"></i>Stock Bajo</button></li>
    <li class="nav-item"><button class="nav-link rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabInvFecha" type="button" onclick="cargarMovPorFecha()">
        <i class="fas fa-calendar-alt me-1"></i>Por Fecha</button></li>
<?php if (!empty($puedeVerCierre)): ?>
    <li class="nav-item"><button class="nav-link rounded-pill px-3" data-bs-toggle="pill" data-bs-target="#tabCierre" type="button" onclick="initCierre()">
        <i class="fas fa-file-invoice-dollar me-1"></i>Cierre de Inventario
        <span class="badge bg-danger ms-1 d-none" id="badgeCierrePend">0</span></button></li>
<?php endif; ?>
</ul>

<div class="tab-content">

<!-- ===================== TAB 1: INVENTARIO ===================== -->
<div class="tab-pane fade show active" id="tabInv" role="tabpanel">

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
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>Código</th><th>Producto</th>
                        <th style="width:70px">Unidad</th><th style="width:70px">Fracción</th>
                        <th style="width:60px">Dif U</th><th style="width:60px">Dif F</th>
                        <th style="width:100px" class="text-end" title="Precio al que se compró por primera vez">Costo 1ª compra</th>
                        <th style="width:100px" class="text-end" title="Precio de la compra más reciente">Última compra</th>
                        <th style="width:105px" class="text-end" title="Costo promedio ponderado: es el que usa el sistema para calcular el precio">Costo promedio</th>
                        <th style="width:100px" class="text-end">Precio Venta</th>
                        <th style="width:80px" class="text-center" title="Margen sobre el precio de venta">Margen</th>
                        <th style="width:75px" class="text-center">Stock Mín</th>
                        <th style="width:150px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyInv">
                    <tr><td colspan="13" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagInv"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagInv"></ul></nav>
</div>

</div><!-- /tabInv -->

<!-- ===================== TAB 2: MOVIMIENTO DEL PRODUCTO ===================== -->
<div class="tab-pane fade" id="tabMov" role="tabpanel">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Producto</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="mov_buscar" placeholder="Buscar producto por código o nombre..." autocomplete="off">
                        <button class="btn btn-outline-primary" type="button" onclick="buscarProductoMov()"><i class="fas fa-search"></i></button>
                    </div>
                    <div id="mov_resultados" class="list-group mt-2" style="max-height:220px;overflow-y:auto;display:none;"></div>
                    <div id="mov_seleccionado" class="alert alert-info mt-2 py-2 d-none">
                        <strong id="mov_prod_nombre"></strong> <small class="text-muted" id="mov_prod_codigo"></small>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="limpiarMovProducto()">Cambiar</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Últimos</label>
                    <select class="form-select" id="mov_limite" onchange="cargarMovimientos()">
                        <option value="20">20 movimientos</option>
                        <option value="50">50 movimientos</option>
                        <option value="100">100 movimientos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="cargarMovimientos()"><i class="fas fa-sync me-2"></i>Actualizar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-exchange-alt me-2"></i>Movimiento del Producto</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Tipo</th>
                            <th class="text-center">Movimiento Und</th><th class="text-center">Movimiento Frac</th>
                            <th class="text-center">Stock Und</th><th class="text-center">Stock Frac</th>
                            <th>Observación</th><th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMov">
                        <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-search fa-2x d-block mb-2"></i>Seleccione un producto para ver su historial de movimientos</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== TAB 3: STOCK BAJO ===================== -->
<div class="tab-pane fade" id="tabBajo" role="tabpanel">
    <div class="card border-0 shadow-sm" id="cardReorden">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Productos por reabastecer</h5>
                <small class="text-muted">Productos cuyo stock (unidades) es igual o menor al stock mínimo</small>
            </div>
            <span class="badge bg-warning rounded-pill" id="reordenCount">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th><th>Producto</th><th class="text-center">Stock actual</th>
                            <th class="text-center">Stock mín</th><th class="text-center">Fracciones</th>
                            <th class="text-center">Sugerencia</th><th class="text-center">Cajas</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyReorden"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================== TAB 4: MOVIMIENTOS POR FECHA ===================== -->
<div class="tab-pane fade" id="tabInvFecha" role="tabpanel">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Inicial</label>
                    <input type="date" class="form-control" id="mf_desde" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Final</label>
                    <input type="date" class="form-control" id="mf_hasta" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tipo</label>
                    <select class="form-select" id="mf_tipo">
                        <option value="TODOS">Todos</option>
                        <option value="INGRESO">Ingresos</option>
                        <option value="EGRESO">Egresos</option>
                        <option value="AJUSTE">Ajustes</option>
                        <option value="DEVOLUCION">Devoluciones</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" onclick="cargarMovPorFecha()"><i class="fas fa-search me-2"></i>Buscar movimientos</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Movimientos</h6>
                    <h3 class="fw-bold mb-0" id="mfk_registros">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Ingresos (Und / Frac)</h6>
                    <h3 class="fw-bold mb-0 text-success" id="mfk_ingresos">0 / 0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Salidas / Ajustes (Und / Frac)</h6>
                    <h3 class="fw-bold mb-0 text-danger" id="mfk_salidas">0 / 0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Valor Movido</h6>
                    <h3 class="fw-bold mb-0 text-primary" id="mfk_valor">$0</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2"></i>Movimientos en el rango seleccionado</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Producto</th><th>Código</th><th>Tipo</th>
                            <th class="text-center">Und</th><th class="text-center">Frac</th>
                            <th class="text-center">Stock Und</th><th class="text-center">Stock Frac</th>
                            <th>Observación</th><th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMF">
                        <tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-calendar-alt fa-2x d-block mb-2"></i>Seleccione un rango de fechas y pulse Buscar</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($puedeVerCierre)): ?>
<!-- ===================== TAB 5: CIERRE DE INVENTARIO ===================== -->
<div class="tab-pane fade" id="tabCierre" role="tabpanel">

    <!-- Configurar el cierre -->
    <div class="card border-0 shadow-sm mb-3" id="cardCierreConfig">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Cierre de inventario</h6>
                <small class="text-muted">Cierre el período y vea las ventas, devoluciones, ingresos y egresos de ese rango</small>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="verHistorialCierres()">
                <i class="fas fa-history me-1"></i>Historial
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cada cuánto</label>
                    <select class="form-select" id="cie_periodo" onchange="sugerirRangoCierre()">
                        <option value="DIARIO">Diario</option>
                        <option value="SEMANAL" selected>Semanal</option>
                        <option value="QUINCENAL">Quincenal</option>
                        <option value="MENSUAL">Mensual</option>
                        <option value="PERSONALIZADO">Personalizado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Desde</label>
                    <input type="date" class="form-control" id="cie_desde" onchange="calcularCierre()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Hasta</label>
                    <input type="date" class="form-control" id="cie_hasta" onchange="calcularCierre()">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary w-100" onclick="calcularCierre()">
                        <i class="fas fa-calculator me-2"></i>Ver cálculo
                    </button>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">Observación <small class="text-muted">(opcional)</small></label>
                    <input type="text" class="form-control" id="cie_obs" placeholder="Ej: cierre de la semana del 8 al 14">
                </div>
                <?php if (!empty($puedeCerrar)): ?>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-success flex-fill" onclick="cerrarInventario()" id="btnCerrarInv">
                            <i class="fas fa-lock me-2"></i>Cerrar
                        </button>
                        <button class="btn btn-outline-dark" onclick="programarCierre()" id="btnProgramarCierre" title="Dejarlo agendado">
                            <i class="fas fa-clock"></i>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div id="cie_solape" class="alert alert-warning mt-3 mb-0 py-2 d-none"></div>

            <!-- Previsualización -->
            <div id="cie_preview" class="mt-3" style="display:none;">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Ventas</small>
                            <h5 class="fw-bold mb-0 text-primary" id="ciek_ventas">$0</h5>
                            <small class="text-muted" id="ciek_facturas">0 facturas</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Devoluciones</small>
                            <h5 class="fw-bold mb-0 text-warning" id="ciek_devol">$0</h5>
                            <small class="text-muted" id="ciek_devol_n">0</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Mercancía que entró</small>
                            <h5 class="fw-bold mb-0 text-info" id="ciek_ingresos">$0</h5>
                            <small class="text-muted" id="ciek_ingresos_n">0 ingresos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Egresos</small>
                            <h5 class="fw-bold mb-0 text-danger" id="ciek_egresos">$0</h5>
                            <small class="text-muted" id="ciek_egresos_n">0 gastos</small>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <small class="text-muted d-block">Ganancia en ventas</small>
                            <h5 class="fw-bold mb-0 text-success" id="ciek_ganancia">$0</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <small class="text-muted d-block">Utilidad neta</small>
                            <h5 class="fw-bold mb-0" id="ciek_neta">$0</h5>
                            <small class="text-muted">ganancia − egresos − devoluciones</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Capital invertido</small>
                            <h5 class="fw-bold mb-0" id="ciek_invertido">$0</h5>
                            <small class="text-muted">inventario a costo</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <small class="text-muted d-block">Capital a la venta</small>
                            <h5 class="fw-bold mb-0" id="ciek_capital">$0</h5>
                            <small class="text-muted" id="ciek_utilidad_pot">0</small>
                        </div>
                    </div>
                </div>
                <p class="small text-muted mt-2 mb-0" id="cie_rango_info"></p>
            </div>
        </div>
    </div>

    <!-- Historial de cierres -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fas fa-list-ol me-2"></i>Cierres registrados</h6>
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm" id="cie_filtro" onchange="cargarCierres(1)" style="width:auto;">
                    <option value="">Todos</option>
                    <option value="CERRADO">Cerrados</option>
                    <option value="PROGRAMADO">Programados</option>
                    <option value="ANULADO">Anulados</option>
                </select>
                <a class="btn btn-outline-success btn-sm rounded-pill" id="cie_export_csv" href="#"><i class="fas fa-file-excel"></i></a>
                <a class="btn btn-outline-danger btn-sm rounded-pill" id="cie_export_pdf" href="#"><i class="fas fa-file-pdf"></i></a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Período</th><th>Desde</th><th>Hasta</th>
                            <th class="text-end">Ventas</th><th class="text-end">Devoluciones</th>
                            <th class="text-end">Ingresos</th><th class="text-end">Egresos</th>
                            <th class="text-end">Utilidad neta</th>
                            <th class="text-center">Estado</th><th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCierres">
                        <tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Aún no hay cierres registrados</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

</div><!-- /tab-content -->

<!-- Modal Detalle del Cierre -->
<div class="modal fade" id="modalDetalleCierre" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-list me-2"></i>Detalle del cierre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleCierreBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer bg-light">
                <a class="btn btn-outline-danger btn-sm" id="cie_detalle_pdf" href="#" target="_blank"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
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
    $('#tbodyInv').html('<tr><td colspan="13" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/inventario/listar', { page, search: s }, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="13" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Sin resultados</td></tr>';
        } else {
            $.each(d.data, function(i, p) {
                var rowClass = p.unidad <= p.stock_minimo ? 'table-danger' : '';
                var fracBadge = p.fraccion > 0 ? '<br><small class="text-muted">x' + p.fraccion + ' und</small>' : '';

                // Costo promedio y margen
                var costoProm = parseFloat(p.valor_compra) || 0;
                var precioVenta = parseFloat(p.valor_venta) || 0;
                var margen = precioVenta > 0 ? ((precioVenta - costoProm) / precioVenta) * 100 : 0;
                var margenClase = margen <= 0 ? 'text-danger fw-bold' : (margen < 15 ? 'text-warning fw-bold' : 'text-success');

                // Costos de compra (pueden no existir todavía)
                var costo1 = p.costo_primera_compra !== null && p.costo_primera_compra !== undefined
                    ? '$' + formatoNumero(p.costo_primera_compra) : '<span class="text-muted">-</span>';
                var costoUlt = p.costo_ultima_compra !== null && p.costo_ultima_compra !== undefined
                    ? '$' + formatoNumero(p.costo_ultima_compra) : '<span class="text-muted">-</span>';

                // Aviso si la última compra subió respecto al costo promedio
                var alertaSubida = '';
                if (costoProm > 0 && parseFloat(p.costo_ultima_compra) > costoProm * 1.001) {
                    alertaSubida = ' <i class="fas fa-arrow-up text-danger" title="La última compra costó más que el promedio: revisá el precio de venta"></i>';
                }

                // Aviso de tope regulado
                var alertaTope = '';
                if (p.precio_maximo_regulado && precioVenta >= parseFloat(p.precio_maximo_regulado)) {
                    alertaTope = ' <i class="fas fa-balance-scale text-info" title="Precio máximo regulado: $' + formatoNumero(p.precio_maximo_regulado) + '"></i>';
                }

                html += '<tr class="' + rowClass + '">' +
                    '<td><span class="badge bg-light text-dark">' + escHtml(p.codigo) + '</span></td>' +
                    '<td><strong>' + escHtml(p.descripcion) + '</strong>' + fracBadge + '</td>' +
                    '<td class="text-center fw-bold fs-5">' + (p.unidad||0) + '</td>' +
                    '<td class="text-center">' + (p.stock_fraccion||0) + '</td>' +
                    '<td class="text-center ' + (p.diferenciaU < 0 ? 'text-danger' : 'text-success') + '">' + (p.diferenciaU||0) + '</td>' +
                    '<td class="text-center ' + (p.diferenciaF < 0 ? 'text-danger' : 'text-success') + '">' + (p.diferenciaF||0) + '</td>' +
                    '<td class="text-end small">' + costo1 + '</td>' +
                    '<td class="text-end small">' + costoUlt + alertaSubida + '</td>' +
                    '<td class="text-end fw-semibold">$' + formatoNumero(costoProm) + '</td>' +
                    '<td class="text-end fw-bold">$' + formatoNumero(precioVenta) + alertaTope + '</td>' +
                    '<td class="text-center ' + margenClase + '">' + margen.toFixed(1) + '%</td>' +
                    '<td class="text-center ' + (p.unidad <= p.stock_minimo ? 'text-danger fw-bold' : '') + '">' + (p.stock_minimo||0) + '</td>' +
                    '<td>' +
                        '<button class="btn btn-sm btn-outline-dark me-1" title="Historial de precios y compras" onclick="verHistorialPrecios(' + p.id_producto + ')"><i class="fas fa-history"></i></button>' +
                        '<button class="btn btn-sm btn-warning" onclick="ajustarInv(' + p.id_producto + ',\'' + escHtml(p.descripcion).replace(/'/g,"\\'") + '\',' + (p.unidad||0) + ',' + (p.stock_fraccion||0) + ')"><i class="fas fa-edit me-1"></i>Ajustar</button>' +
                    '</td>' +
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
    $('#tbodyReorden').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');
    $.getJSON(BASE_URL + '/inventario/sugerencias-reorden', function(r) {
        if (!r.success) return;
        if (!r.data.length) {
            $('#tbodyReorden').html('<tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-check-circle fa-2x d-block mb-2 text-success"></i>Ningún producto está por debajo del stock mínimo</td></tr>');
            $('#reordenCount').text(0);
            return;
        }
        var html = '';
        $.each(r.data, function(i, p) {
            var urgClass = (p.unidad <= 0) ? 'table-danger' : 'table-warning';
            var nombre = escHtml(p.descripcion).replace(/'/g, "\\'");
            html += '<tr class="' + urgClass + '">' +
                '<td><small>' + escHtml(p.codigo) + '</small></td>' +
                '<td><strong>' + escHtml(p.descripcion) + '</strong>' + (p.presentacion ? '<br><small class="text-muted">' + escHtml(p.presentacion) + '</small>' : '') + '</td>' +
                '<td class="text-center fw-bold ' + (p.unidad <= 0 ? 'text-danger' : '') + '">' + (p.unidad||0) + '</td>' +
                '<td class="text-center">' + (p.stock_minimo||0) + '</td>' +
                '<td class="text-center">' + (p.stock_fraccion||0) + '</td>' +
                '<td class="text-center fw-bold text-primary">' + p.sugerido + ' und</td>' +
                '<td class="text-center"><span class="badge bg-info">' + p.cajas_sugeridas + ' caja(s)</span></td>' +
                '<td class="text-center text-nowrap">' +
                    '<button class="btn btn-sm btn-warning me-1" onclick="ajustarInv(' + p.id_producto + ',\'' + nombre + '\',' + (p.unidad||0) + ',' + (p.stock_fraccion||0) + ')" title="Ajustar stock"><i class="fas fa-balance-scale"></i></button>' +
                    '<a href="' + BASE_URL + '/ingresos" class="btn btn-sm btn-outline-primary" title="Registrar ingreso"><i class="fas fa-plus"></i></a>' +
                '</td></tr>';
        });
        $('#tbodyReorden').html(html);
        $('#reordenCount').text(r.data.length);
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

// ============================================================
// TAB 2: MOVIMIENTO DEL PRODUCTO
// ============================================================
var movProdSel = null;
var timerMovBusq;

function buscarProductoMov() {
    var q = ($('#mov_buscar').val() || '').trim();
    if (q.length < 2) { PNotify.error({ text: 'Escriba al menos 2 caracteres' }); return; }
    $.getJSON(BASE_URL + '/ingresos/buscar-productos', { q: q }, function(r) {
        if (!r.success || !r.data.length) {
            $('#mov_resultados').html('<div class="list-group-item text-muted">Sin resultados</div>').show();
            return;
        }
        var html = '';
        $.each(r.data, function(i, p) {
            html += '<button type="button" class="list-group-item list-group-item-action" onclick="elegirProductoMov(' + p.id_producto + ',\'' +
                escHtml(p.descripcion).replace(/'/g, "\\'") + '\',\'' + escHtml(p.codigo) + '\')">' +
                '<strong>' + escHtml(p.codigo) + '</strong> - ' + escHtml(p.descripcion) +
                ' <small class="text-muted">Stock: ' + (p.unidad||0) + ' / ' + (p.stock_fraccion||0) + '</small></button>';
        });
        $('#mov_resultados').html(html).show();
    });
}

function elegirProductoMov(id, nombre, codigo) {
    movProdSel = { id: id, nombre: nombre, codigo: codigo };
    $('#mov_resultados').hide();
    $('#mov_buscar').val('');
    $('#mov_prod_nombre').text(nombre);
    $('#mov_prod_codigo').text('Cód: ' + codigo);
    $('#mov_seleccionado').removeClass('d-none');
    cargarMovimientos();
}

function limpiarMovProducto() {
    movProdSel = null;
    $('#mov_seleccionado').addClass('d-none');
    $('#tbodyMov').html('<tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-search fa-2x d-block mb-2"></i>Seleccione un producto para ver su historial de movimientos</td></tr>');
}

function cargarMovimientos() {
    if (!movProdSel) { PNotify.error({ text: 'Seleccione un producto primero' }); return; }
    var limite = $('#mov_limite').val() || 20;
    $('#tbodyMov').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');
    $.getJSON(BASE_URL + '/inventario/movimientos/' + movProdSel.id, { limite: limite }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        var html = '';
        if (!r.data.length) {
            html = '<tr><td colspan="8" class="text-center text-muted py-5">Este producto no tiene movimientos registrados</td></tr>';
        } else {
            $.each(r.data, function(i, m) {
                var color = m.tipo === 'INGRESO' ? 'bg-success' : (m.tipo === 'EGRESO' ? 'bg-danger' : (m.tipo === 'DEVOLUCION' ? 'bg-warning text-dark' : 'bg-info'));
                html += '<tr>' +
                    '<td><small>' + m.created_at + '</small></td>' +
                    '<td><span class="badge ' + color + '">' + escHtml(m.tipo) + '</span></td>' +
                    '<td class="text-center fw-bold ' + (m.unidad > 0 ? 'text-success' : (m.unidad < 0 ? 'text-danger' : '')) + '">' + (m.unidad > 0 ? '+' : '') + (m.unidad||0) + '</td>' +
                    '<td class="text-center ' + (m.fraccion > 0 ? 'text-success' : (m.fraccion < 0 ? 'text-danger' : '')) + '">' + (m.fraccion > 0 ? '+' : '') + (m.fraccion||0) + '</td>' +
                    '<td class="text-center fw-bold">' + (m.unidad_resultante||0) + '</td>' +
                    '<td class="text-center">' + (m.fraccion_resultante||0) + '</td>' +
                    '<td><small>' + escHtml(m.observacion || '-') + '</small></td>' +
                    '<td><small>' + escHtml(m.nombre_usuario || '-') + '</small></td>' +
                    '</tr>';
            });
        }
        $('#tbodyMov').html(html);
    });
}

// ============================================================
// TAB 4: MOVIMIENTOS POR FECHA
// ============================================================
function cargarMovPorFecha() {
    var desde = $('#mf_desde').val(), hasta = $('#mf_hasta').val(), tipo = $('#mf_tipo').val() || 'TODOS';
    if (!desde || !hasta) { PNotify.error({ text: 'Seleccione las dos fechas' }); return; }

    $('#tbodyMF').html('<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');

    $.getJSON(BASE_URL + '/inventario/movimientos-por-fecha', { desde: desde, hasta: hasta, tipo: tipo }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        var d = r.data, html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay movimientos en ese rango de fechas</td></tr>';
        } else {
            $.each(d.data, function(i, m) {
                var color = m.tipo === 'INGRESO' ? 'bg-success' : (m.tipo === 'EGRESO' ? 'bg-danger' : (m.tipo === 'DEVOLUCION' ? 'bg-warning text-dark' : 'bg-info'));
                html += '<tr>' +
                    '<td><small>' + m.created_at + '</small></td>' +
                    '<td><strong>' + escHtml(m.descripcion) + '</strong></td>' +
                    '<td>' + escHtml(m.codigo) + '</td>' +
                    '<td><span class="badge ' + color + '">' + escHtml(m.tipo) + '</span></td>' +
                    '<td class="text-center ' + (m.unidad > 0 ? 'text-success' : (m.unidad < 0 ? 'text-danger' : '')) + '">' + (m.unidad||0) + '</td>' +
                    '<td class="text-center ' + (m.fraccion > 0 ? 'text-success' : (m.fraccion < 0 ? 'text-danger' : '')) + '">' + (m.fraccion||0) + '</td>' +
                    '<td class="text-center">' + (m.unidad_resultante||0) + '</td>' +
                    '<td class="text-center">' + (m.fraccion_resultante||0) + '</td>' +
                    '<td><small>' + escHtml(m.observacion || '-') + '</small></td>' +
                    '<td><small>' + escHtml(m.nombre_usuario || '-') + '</small></td>' +
                    '</tr>';
            });
        }
        $('#tbodyMF').html(html);
        var t = d.totales, ing = t.porTipo.INGRESO || { unidad: 0, fraccion: 0 };
        var salidas = 0, salidasF = 0;
        $.each(t.porTipo, function(k, v) { if (k !== 'INGRESO') { salidas += v.unidad; salidasF += v.fraccion; } });
        $('#mfk_registros').text(t.registros);
        $('#mfk_ingresos').text(ing.unidad + ' / ' + ing.fraccion);
        $('#mfk_salidas').text(salidas + ' / ' + salidasF);
        $('#mfk_valor').text('$' + formatoNumero(t.valor));
    });
}

$(function() {
    cargarInv(1);

    $('#mov_buscar').on('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarProductoMov(); }
    });
});

// ============================================================
// CIERRE DE INVENTARIO
// ============================================================
var cieCalc = null;
var cieSolape = null;

function initCierre() {
    if (!$('#cie_desde').val()) {
        sugerirRangoCierre();
    }
    cargarCierres(1);
}

function sugerirRangoCierre() {
    var periodo = $('#cie_periodo').val();
    if (periodo === 'PERSONALIZADO' && $('#cie_desde').val()) {
        return; // el usuario pone las fechas
    }
    $.getJSON(BASE_URL + '/inventario/cierres/rango', { periodo: periodo }, function(r) {
        if (!r.success) return;
        $('#cie_desde').val(r.data.desde);
        $('#cie_hasta').val(r.data.hasta);
        calcularCierre();
    });
}

function calcularCierre() {
    var desde = $('#cie_desde').val(), hasta = $('#cie_hasta').val();
    if (!desde || !hasta) return;

    $('#cie_preview').hide();
    $('#cie_solape').addClass('d-none');

    $.getJSON(BASE_URL + '/inventario/cierres/calcular', { desde: desde, hasta: hasta }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }

        cieCalc = r.data.calculo;
        cieSolape = r.data.solape;

        var c = cieCalc;
        $('#ciek_ventas').text('$' + formatoNumero(c.ventas.total));
        $('#ciek_facturas').text(c.ventas.facturas + ' facturas' + (c.anuladas.cantidad ? ' | ' + c.anuladas.cantidad + ' anuladas' : ''));
        $('#ciek_devol').text('$' + formatoNumero(c.devoluciones.total));
        $('#ciek_devol_n').text(c.devoluciones.cantidad + ' devolución(es)');
        $('#ciek_ingresos').text('$' + formatoNumero(c.ingresos.total));
        $('#ciek_ingresos_n').text(c.ingresos.cantidad + ' ingreso(s)');
        $('#ciek_egresos').text('$' + formatoNumero(c.egresos.total));
        $('#ciek_egresos_n').text(c.egresos.cantidad + ' gasto(s)');
        $('#ciek_ganancia').text('$' + formatoNumero(c.ventas.ganancia));
        $('#ciek_neta').text('$' + formatoNumero(c.utilidad_neta));
        $('#ciek_neta').removeClass('text-success text-danger').addClass(c.utilidad_neta >= 0 ? 'text-success' : 'text-danger');
        $('#ciek_invertido').text('$' + formatoNumero(c.inventario.capital_invertido));
        $('#ciek_capital').text('$' + formatoNumero(c.inventario.capital_total));
        $('#ciek_utilidad_pot').text('utilidad potencial $' + formatoNumero(c.inventario.utilidad_potencial));
        $('#cie_rango_info').text('Período: ' + c.desde + ' al ' + c.hasta + ' (' + c.dias + ' día(s))');

        if (cieSolape) {
            $('#cie_solape').removeClass('d-none').html(
                '<i class="fas fa-exclamation-triangle me-1"></i>Ya existe un cierre <strong>' + escHtml(cieSolape.periodo) + '</strong> que cubre del ' +
                escHtml(cieSolape.fecha_desde) + ' al ' + escHtml(cieSolape.fecha_hasta) + ' (estado: ' + escHtml(cieSolape.estado) + '). ' +
                'Anúlelo primero si quiere rehacerlo.');
        }

        $('#cie_preview').show();
    });
}

function cerrarInventario() {
    if (!cieCalc) { PNotify.error({ text: 'Primero pulse "Ver cálculo"' }); return; }
    if (cieSolape) { PNotify.error({ text: 'Ese período ya tiene un cierre. Anúlelo primero.' }); return; }
    if (!confirm('¿Cerrar el período del ' + cieCalc.desde + ' al ' + cieCalc.hasta + '?\n\n' +
                 'Ventas: $' + formatoNumero(cieCalc.ventas.total) + '\n' +
                 'Utilidad neta: $' + formatoNumero(cieCalc.utilidad_neta) + '\n\n' +
                 'El cierre queda registrado para siempre.')) return;

    enviarCierre('/inventario/cierres/cerrar', '#btnCerrarInv');
}

function programarCierre() {
    var desde = $('#cie_desde').val(), hasta = $('#cie_hasta').val();
    if (!desde || !hasta) { PNotify.error({ text: 'Seleccione las fechas' }); return; }
    if (!confirm('¿Dejar programado el cierre del ' + desde + ' al ' + hasta + '?\n\nSe ejecutará cuando termine el período.')) return;

    enviarCierre('/inventario/cierres/programar', '#btnProgramarCierre');
}

function enviarCierre(url, btnSel) {
    var data = {
        desde: $('#cie_desde').val(),
        hasta: $('#cie_hasta').val(),
        periodo: $('#cie_periodo').val(),
        observacion: $('#cie_obs').val() || ''
    };
    var html = $(btnSel).html();
    $(btnSel).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: BASE_URL + url, method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                PNotify.success({ text: r.message, delay: 5000 });
                $('#cie_obs').val('');
                cieCalc = null;
                $('#cie_preview').hide();
                $('#cie_solape').addClass('d-none');
                cargarCierres(1);
                sugerirRangoCierre();
            } else { PNotify.error({ text: r.message }); }
            $(btnSel).prop('disabled', false).html(html);
        },
        error: function(xhr) {
            PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar el cierre' });
            $(btnSel).prop('disabled', false).html(html);
        }
    });
}

function cargarCierres(page) {
    page = page || 1;
    var estado = $('#cie_filtro').val() || '';
    $('#tbodyCierres').html('<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i></td></tr>');

    $.getJSON(BASE_URL + '/inventario/cierres', { page: page, estado: estado }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        var d = r.data, html = '';

        if (!d.data.length) {
            html = '<tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Aún no hay cierres registrados</td></tr>';
        } else {
            $.each(d.data, function(i, c) {
                var badge = c.estado === 'CERRADO' ? 'success' : (c.estado === 'PROGRAMADO' ? 'info' : 'secondary');
                var filaClase = c.estado === 'ANULADO' ? 'table-secondary text-decoration-line-through' : '';

                var acciones = '<button class="btn btn-sm btn-outline-primary" onclick="verDetalleCierre(' + c.id_cierre + ')" title="Ver detalle"><i class="fas fa-eye"></i></button>';
                if (c.estado === 'PROGRAMADO') {
                    acciones += ' <button class="btn btn-sm btn-success" onclick="ejecutarCierre(' + c.id_cierre + ')" title="Ejecutar ahora"><i class="fas fa-play"></i></button>';
                }
                if (c.estado !== 'ANULADO') {
                    acciones += ' <button class="btn btn-sm btn-outline-danger" onclick="anularCierre(' + c.id_cierre + ')" title="Anular"><i class="fas fa-ban"></i></button>';
                }

                html += '<tr class="' + filaClase + '">' +
                    '<td><span class="badge bg-light text-dark">' + escHtml(c.periodo) + '</span></td>' +
                    '<td><small>' + (c.fecha_desde || c.fecha_cierre) + '</small></td>' +
                    '<td><small>' + (c.fecha_hasta || c.fecha_cierre) + '</small></td>' +
                    '<td class="text-end">$' + formatoNumero(c.total_ventas) + '</td>' +
                    '<td class="text-end text-warning">$' + formatoNumero(c.total_devoluciones) + '</td>' +
                    '<td class="text-end text-info">$' + formatoNumero(c.total_ingresos) + '</td>' +
                    '<td class="text-end text-danger">$' + formatoNumero(c.total_egresos) + '</td>' +
                    '<td class="text-end fw-bold ' + (c.utilidad_neta >= 0 ? 'text-success' : 'text-danger') + '">$' + formatoNumero(c.utilidad_neta) + '</td>' +
                    '<td class="text-center"><span class="badge bg-' + badge + '">' + c.estado + '</span></td>' +
                    '<td class="text-center text-nowrap">' + acciones + '</td>' +
                    '</tr>';
            });
        }
        $('#tbodyCierres').html(html);

        var badgePend = $('#badgeCierrePend');
        if (d.programados_listos > 0) {
            badgePend.removeClass('d-none').text(d.programados_listos);
        } else {
            badgePend.addClass('d-none');
        }

        var sqlCsv = "SELECT periodo AS Periodo, COALESCE(fecha_desde, fecha_cierre) AS Desde, COALESCE(fecha_hasta, fecha_cierre) AS Hasta, "
                   + "total_ventas AS Ventas, total_devoluciones AS Devoluciones, total_ingresos AS Ingresos, "
                   + "total_egresos AS Egresos, total_ganancia AS Ganancia, utilidad_neta AS UtilidadNeta, total_invertido AS CapitalInvertido, estado AS Estado "
                   + "FROM vb_cierres_inventario ORDER BY COALESCE(fecha_desde, fecha_cierre) DESC";
        $('#cie_export_csv').attr('href', BASE_URL + '/exportar/csv?sql=' + encodeURIComponent(sqlCsv) + '&nombre=cierres_inventario&titulo=Cierres de inventario');
        $('#cie_export_pdf').attr('href', BASE_URL + '/exportar/pdf?sql=' + encodeURIComponent(sqlCsv) + '&nombre=cierres_inventario&titulo=Cierres de inventario');
    });
}

function verDetalleCierre(id) {
    $('#detalleCierreBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#modalDetalleCierre').modal('show');
    $('#cie_detalle_pdf').attr('href', BASE_URL + '/exportar/pdf?sql=' + encodeURIComponent(
        "SELECT p.codigo AS Codigo, p.descripcion AS Producto, SUM(i.cantidad_unidad) AS Unidad, SUM(i.cantidad_fraccion) AS Fraccion, " +
        "COALESCE(SUM(i.cantidad_unidad * COALESCE(i.valor_compra,0)),0) AS Total FROM vb_ingresos i " +
        "JOIN vb_productos p ON i.id_producto=p.id_producto JOIN vb_cierres_inventario c ON c.id_cierre=" + id + " " +
        "WHERE i.fecha BETWEEN COALESCE(c.fecha_desde,c.fecha_cierre) AND COALESCE(c.fecha_hasta,c.fecha_cierre) GROUP BY p.id_producto"
    ) + '&nombre=detalle_cierre&titulo=Detalle del cierre');

    $.getJSON(BASE_URL + '/inventario/cierres/detalle', { id_cierre: id }, function(r) {
        if (!r.success) {
            $('#detalleCierreBody').html('<div class="alert alert-danger mb-0">' + escHtml(r.message) + '</div>');
            return;
        }
        var d = r.data, c = d.cierre, h = '';

        h += '<h6 class="fw-bold mb-3">Cierre <span class="badge bg-light text-dark">' + escHtml(c.periodo) + '</span> del ' +
             escHtml(c.fecha_desde || c.fecha_cierre) + ' al ' + escHtml(c.fecha_hasta || c.fecha_cierre) + '</h6>';

        h += '<div class="row g-2 mb-3">';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100"><small class="text-muted d-block">Ventas</small><strong class="text-primary">$' + formatoNumero(c.total_ventas) + '</strong><br><small class="text-muted">' + c.total_facturas + ' facturas</small></div></div>';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100"><small class="text-muted d-block">Devoluciones</small><strong class="text-warning">$' + formatoNumero(c.total_devoluciones) + '</strong></div></div>';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100"><small class="text-muted d-block">Egresos</small><strong class="text-danger">$' + formatoNumero(c.total_egresos) + '</strong></div></div>';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100 bg-light"><small class="text-muted d-block">Utilidad neta</small><strong class="' + (c.utilidad_neta >= 0 ? 'text-success' : 'text-danger') + '">$' + formatoNumero(c.utilidad_neta) + '</strong></div></div>';
        h += '</div>';

        if (c.observacion) {
            h += '<div class="alert alert-light py-2 small"><i class="fas fa-sticky-note me-1"></i>' + escHtml(c.observacion) + '</div>';
        }
        h += '<p class="small text-muted">Cerrado por: <strong>' + escHtml(c.nombre_usuario || 'sistema') + '</strong> el ' + escHtml(c.created_at) + '</p>';

        // En qué se vendió
        h += '<h6 class="fw-bold mt-3 mb-2"><i class="fas fa-cash-register me-1 text-primary"></i>Ventas por método de pago</h6>';
        if (!d.por_metodo.length) {
            h += '<p class="text-muted small">Sin ventas en el período.</p>';
        } else {
            h += '<div class="table-responsive mb-3"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Método</th><th class="text-center">Facturas</th><th class="text-end">Total</th></tr></thead><tbody>';
            $.each(d.por_metodo, function(i, m) {
                h += '<tr><td>' + escHtml(m.tipo_pago) + '</td><td class="text-center">' + m.facturas + '</td><td class="text-end fw-semibold">$' + formatoNumero(m.total) + '</td></tr>';
            });
            h += '</tbody></table></div>';
        }

        // Devoluciones
        h += '<h6 class="fw-bold mt-3 mb-2"><i class="fas fa-undo me-1 text-warning"></i>Devoluciones (' + d.devoluciones.length + ')</h6>';
        if (!d.devoluciones.length) {
            h += '<p class="text-muted small">Sin devoluciones en el período.</p>';
        } else {
            h += '<div class="table-responsive mb-3"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Fecha</th><th>Código</th><th>Factura</th><th>Cliente</th><th class="text-end">Total</th><th>Motivo</th></tr></thead><tbody>';
            $.each(d.devoluciones, function(i, x) {
                h += '<tr><td><small>' + escHtml(x.fecha) + '</small></td><td>' + escHtml(x.codigo) + '</td>' +
                     '<td>' + escHtml(x.factura_codigo || '-') + '</td><td>' + escHtml(x.cliente_nombre || '-') + '</td>' +
                     '<td class="text-end">$' + formatoNumero(x.total) + '</td><td><small class="text-muted">' + escHtml(x.motivo || '-') + '</small></td></tr>';
            });
            h += '</tbody></table></div>';
        }

        // Con qué se devolvió la plata (para el cuadre de caja)
        if (d.dev_por_metodo && d.dev_por_metodo.length) {
            h += '<h6 class="fw-bold mt-3 mb-2"><i class="fas fa-hand-holding-usd me-1 text-warning"></i>Forma de las devoluciones</h6>';
            h += '<div class="table-responsive mb-3"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Método</th><th class="text-center">Devoluciones</th><th class="text-end">Total</th></tr></thead><tbody>';
            $.each(d.dev_por_metodo, function(i, x) {
                h += '<tr><td>' + escHtml(x.metodo) + '</td><td class="text-center">' + x.cantidad + '</td><td class="text-end fw-semibold">$' + formatoNumero(x.total) + '</td></tr>';
            });
            h += '</tbody></table></div>';
        }

        // Egresos
        h += '<h6 class="fw-bold mt-3 mb-2"><i class="fas fa-money-bill-wave me-1 text-danger"></i>Egresos por tipo</h6>';
        if (!d.egresos.length) {
            h += '<p class="text-muted small">Sin egresos en el período.</p>';
        } else {
            h += '<div class="table-responsive mb-3"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Tipo</th><th class="text-center">Cantidad</th><th class="text-end">Total</th></tr></thead><tbody>';
            $.each(d.egresos, function(i, x) {
                h += '<tr><td>' + escHtml(x.tipo || 'Sin tipo') + '</td><td class="text-center">' + x.cantidad + '</td><td class="text-end fw-semibold">$' + formatoNumero(x.total) + '</td></tr>';
            });
            h += '</tbody></table></div>';
        }

        // Mercancía que entró
        h += '<h6 class="fw-bold mt-3 mb-2"><i class="fas fa-truck-loading me-1 text-info"></i>Mercancía que entró</h6>';
        if (!d.ingresos.length) {
            h += '<p class="text-muted small">Sin ingresos en el período.</p>';
        } else {
            h += '<div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Código</th><th>Producto</th><th class="text-center">Und</th><th class="text-center">Frac</th><th class="text-end">Valor compra</th></tr></thead><tbody>';
            $.each(d.ingresos, function(i, x) {
                h += '<tr><td>' + escHtml(x.codigo) + '</td><td>' + escHtml(x.descripcion) + '</td>' +
                     '<td class="text-center">' + (x.unidad || 0) + '</td><td class="text-center">' + (x.fraccion || 0) + '</td>' +
                     '<td class="text-end fw-semibold">$' + formatoNumero(x.total) + '</td></tr>';
            });
            h += '</tbody></table></div>';
        }

        $('#detalleCierreBody').html(h);
    });
}

function ejecutarCierre(id) {
    if (!confirm('¿Ejecutar este cierre programado ahora?')) return;
    $.ajax({url: BASE_URL + '/inventario/cierres/ejecutar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_cierre: id }),
        success: function(r) { if (r.success) { PNotify.success({ text: r.message }); cargarCierres(1); } else { PNotify.error({ text: r.message }); } },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error' }); }});
}

function anularCierre(id) {
    var motivo = prompt('¿Por qué anula este cierre?');
    if (motivo === null) return;
    $.ajax({url: BASE_URL + '/inventario/cierres/anular', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_cierre: id, motivo: motivo }),
        success: function(r) { if (r.success) { PNotify.success({ text: r.message }); cargarCierres(1); } else { PNotify.error({ text: r.message }); } },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error' }); }});
}

function verHistorialCierres() {
    cargarCierres(1);
    $('html, body').animate({ scrollTop: $('#tbodyCierres').offset().top - 200 }, 300);
}
</script>

<?php include __DIR__ . '/../partials/historial_precios.php'; ?>
