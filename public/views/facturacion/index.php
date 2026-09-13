<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-cash-register text-primary me-2"></i>Facturación</h4>
                <p class="text-muted mb-0">Punto de venta - Crear factura</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($baseDiaria): ?>
                <span class="badge bg-success bg-opacity-10 text-success p-2 px-3 rounded-pill">
                    <i class="fas fa-piggy-bank me-1"></i>Base: $<?= number_format($baseDiaria['base'], 0, ',', '.') ?>
                </span>
                <?php elseif ($isCajero): ?>
                <span class="badge bg-warning bg-opacity-10 text-warning p-2 px-3 rounded-pill" id="badgeSinBase">
                    <i class="fas fa-exclamation-triangle me-1"></i>Sin base diaria
                </span>
                <?php else: ?>
                <span class="badge bg-secondary bg-opacity-10 text-secondary p-2 px-3 rounded-pill">
                    <i class="fas fa-piggy-bank me-1"></i>Sin base (admin)
                </span>
                <?php endif; ?>
                <span class="badge bg-primary bg-opacity-10 text-primary p-2 px-3 rounded-pill me-2">
                    <i class="fas fa-calendar me-1"></i><?= date('d/m/Y') ?>
                </span>
                <span class="badge bg-success bg-opacity-10 text-success p-2 px-3 rounded-pill" id="reloj"></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- COLUMNA IZQUIERDA: Productos y búsqueda -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" id="buscarProducto" 
                           placeholder="Buscar producto por código, nombre o código de barras..." autocomplete="off" autofocus>
                    <button class="btn btn-primary" type="button" onclick="focusBuscar()">
                        <i class="fas fa-barcode"></i> F1
                    </button>
                </div>
                <div id="resultadosBusqueda" class="list-group mt-2" style="max-height:300px;overflow-y:auto;display:none;"></div>
            </div>
        </div>

        <!-- Tabla de productos (paginada) -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="fas fa-boxes text-primary me-2"></i>Productos</h5>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted" id="infoProdPag"></small>
                    <div class="input-group input-group-sm" style="width:200px">
                        <span class="input-group-text bg-white"><i class="fas fa-filter fa-xs text-muted"></i></span>
                        <input type="text" class="form-control" id="filtroProdPos" placeholder="Filtrar productos..." autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:350px;overflow-y:auto">
                    <table class="table table-hover align-middle mb-0 table-sm">
                        <thead class="table-light" style="position:sticky;top:0;z-index:1">
                            <tr>
                                <th style="width:60px">Código</th>
                                <th>Producto</th>
                                <th style="width:90px">Precio</th>
                                <th style="width:70px">Stock</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyProductosPos">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x d-block mb-2"></i>Cargando productos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light">
                    <small class="text-muted" id="infoProdPagBottom"></small>
                    <ul class="pagination pagination-sm mb-0" id="pagProductos"></ul>
                </div>
            </div>
        </div>

        <!-- Tabla del carrito -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="fas fa-shopping-cart text-primary me-2"></i>Carrito</h5>
                <span class="badge bg-primary rounded-pill" id="itemsCount">0</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Producto</th>
                                <th style="width:80px">Cajas</th>
                                <th style="width:80px">Und</th>
                                <th style="width:100px">P.Unit</th>
                                <th style="width:80px">IVA</th>
                                <th style="width:110px">Total</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCarrito">
                            <tr id="filaVacia">
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-cart-plus fa-3x d-block mb-3 opacity-25"></i>
                                    Agregue productos al carrito
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Cliente + Totales + Pago -->
    <div class="col-lg-4">
        <!-- Cliente -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user me-2"></i>Cliente</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="cambiarCliente()">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>
                <div class="p-3 bg-light rounded-3" id="clienteInfo">
                    <input type="hidden" id="id_cliente" value="1">
                    <strong id="clienteNombre">CLIENTE GENERAL</strong>
                    <br><small class="text-muted" id="clienteDoc">CC: 12345</small>
                </div>
                <div class="mt-2" id="busquedaCliente" style="display:none;">
                    <input type="text" class="form-control form-control-sm" id="buscarCliente" placeholder="Buscar cliente...">
                    <div id="resultadosClientes" class="list-group mt-1" style="max-height:150px;overflow-y:auto;display:none;"></div>
                </div>
            </div>
        </div>

        <!-- Vendedor -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user-tie me-2"></i>Vendedor <small class="text-muted fw-normal">(F9)</small></h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleVendedorVenta()" title="Cambiar vendedor">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>

                <div class="p-3 bg-light rounded-3" id="vendedorInfo">
                    <input type="hidden" id="id_vendedor_registrado" value="">
                    <strong id="vendedorNombre">Yo mismo (<?= \SIG\Core\View::esc($usuarioNombre ?? '') ?>)</strong>
                    <br><small class="text-muted" id="vendedorDetalle">Venta a nombre del usuario en caja</small>
                </div>

                <div class="mt-2" id="panelVendedorVenta" style="display:none;">
                    <div class="d-flex gap-2 mb-2">
                        <label class="btn btn-outline-primary btn-sm rounded-pill active flex-grow-1">
                            <input type="radio" name="tipoVendedor" value="MISMO" checked class="d-none" onchange="cambiarTipoVendedor(this)">
                            <i class="fas fa-user me-1"></i>Yo mismo
                        </label>
                        <label class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1">
                            <input type="radio" name="tipoVendedor" value="REGISTRADO" class="d-none" onchange="cambiarTipoVendedor(this)">
                            <i class="fas fa-user-tie me-1"></i>Vendedor registrado
                        </label>
                    </div>
                    <div id="busquedaVendedor" style="display:none;">
                        <input type="text" class="form-control form-control-sm" id="buscarVendedor" placeholder="Buscar por cédula, código o nombre...">
                        <div id="resultadosVendedores" class="list-group mt-1" style="max-height:150px;overflow-y:auto;display:none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span id="lblSubtotal">$0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">IVA</span>
                    <span id="lblIva">$0</span>
                </div>
                <div class="d-flex justify-content-between mb-2" id="descuentoRow" style="display:none;">
                    <span class="text-danger">Descuento</span>
                    <span class="text-danger" id="lblDescuento">-$0</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">TOTAL</span>
                    <span class="fw-bold fs-5 text-primary" id="lblTotal">$0</span>
                </div>
                <?php if ($isAdmin): ?>
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted small">Ganancia</span>
                    <span class="text-success small fw-bold" id="lblGanancia">$0</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pago -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-credit-card me-2"></i>Forma de Pago</span>
                    <small class="text-muted fw-normal">se puede pagar con varias</small>
                </h6>

                <!-- Método con el que se agrega el pago -->
                <div class="d-flex flex-wrap gap-2 mb-2" id="metodosPago">
                    <?php
                    $metodos = ['EFECTIVO', 'NEQUI', 'DAVIPLATA', 'ADDI', 'TRANSFERENCIA', 'OTROS'];
                    foreach ($metodos as $i => $m):
                    ?>
                    <label class="btn btn-outline-primary btn-sm rounded-pill <?= $i === 0 ? 'active' : '' ?>">
                        <input type="radio" name="tipoPago" value="<?= $m ?>" <?= $i === 0 ? 'checked' : '' ?> 
                               class="d-none" onchange="$(this).closest('label').addClass('active').siblings().removeClass('active')">
                        <?= $m ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="pagoMonto" min="0" step="100" value=""
                               placeholder="Monto" onkeydown="if(event.key==='Enter'){event.preventDefault();agregarPago();}">
                        <button class="btn btn-primary" type="button" onclick="agregarPago()" title="Agregar el pago con el método seleccionado">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-outline-secondary" type="button" onclick="agregarPagoRestante()" title="Cubrir lo que falta">
                            Resto
                        </button>
                    </div>
                </div>

                <!-- Pagos agregados -->
                <div id="listaPagos" class="mb-2"></div>

                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Total a pagar</span>
                    <span class="fw-bold" id="lblTotalPagar">$0</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Pagado</span>
                    <span class="fw-bold text-success" id="lblPagado">$0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" id="lblFaltaTxt">Falta</span>
                    <span class="fw-bold fs-5 text-danger" id="lblFalta">$0</span>
                </div>

                <!-- Descuento (solo admin) -->
                <?php if ($isAdmin): ?>
                <div class="mb-2">
                    <a href="#" class="small text-decoration-none" onclick="toggleDescuento();return false;">
                        <i class="fas fa-tag me-1"></i>Agregar descuento
                    </a>
                    <div id="descuentoInput" style="display:none;" class="mt-2">
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="descuentoValor" min="0" value="0" onchange="calcularCambio()">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary btn-lg" onclick="facturar('NORMAL')" id="btnFacturar" disabled>
                        <i class="fas fa-receipt me-2"></i>Facturar (F2)
                    </button>
                    <?php if ($puedeFE): ?>
                    <button class="btn btn-info btn-lg" onclick="facturar('ELECTRONICA')" id="btnFacturarFE" disabled>
                        <i class="fas fa-cloud-upload-alt me-2"></i>Factura Electrónica (F3)
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Base Diaria -->
<?php if ($isCajero && !$baseDiaria): ?>
<div class="modal fade" id="modalBaseDiaria" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title fw-bold"><i class="fas fa-piggy-bank me-2"></i>Base Diaria</h5>
            </div>
            <div class="modal-body">
                <p class="mb-3">Registre la base de caja para iniciar el turno:</p>
                <div class="input-group input-group-lg">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="inputBaseDiaria" min="0" step="1000" value="0" autofocus>
                </div>
                <small class="text-muted">Este monto será la base inicial de la caja para hoy.</small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary w-100" onclick="guardarBaseDiaria()">
                    <i class="fas fa-check me-1"></i>Iniciar Turno
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function guardarBaseDiaria() {
    var base = parseFloat($('#inputBaseDiaria').val()) || 0;
    if (base < 0) { PNotify.error({ text: 'La base no puede ser negativa' }); return; }
    $.ajax({
        url: BASE_URL+'/facturacion/base-diaria/guardar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ base: base }),
        success: function(r) {
            if (r.success) {
                $('#modalBaseDiaria').modal('hide');
                $('#badgeSinBase').replaceWith(
                    '<span class="badge bg-success bg-opacity-10 text-success p-2 px-3 rounded-pill">' +
                    '<i class="fas fa-piggy-bank me-1"></i>Base: $' + formatoNumero(base) + '</span>'
                );
                PNotify.success({ text: 'Base diaria registrada: $' + formatoNumero(base) });
                $('#buscarProducto').focus();
            } else {
                PNotify.error({ text: r.message });
            }
        },
        error: function() {
            PNotify.error({ text: 'Error al guardar base diaria' });
        }
    });
}

$(function() {
    $('#modalBaseDiaria').modal('show');
});
</script>
<?php endif; ?>

<script>
let carrito = [];
let idCounter = 0;
let totalGanancia = 0;

// ===== RELOJ =====
function actualizarReloj() {
    var d = new Date();
    $('#reloj').text(d.toLocaleTimeString('es-CO'));
}
setInterval(actualizarReloj, 1000);
actualizarReloj();

// ===== DETECCIÓN DE ESCÁNER DE CÓDIGO DE BARRAS =====
var scannerTimer;
var scannerBuffer = '';
var scannerLastTime = 0;
// Si hay menos de 50ms entre teclas, es un escáner
var SCANNER_THRESHOLD = 50;

$(document).on('keydown', function(e) {
    // Si el foco está en el input de búsqueda
    if (!$('#buscarProducto').is(':focus')) return;

    var now = Date.now();
    var timeDiff = now - scannerLastTime;
    scannerLastTime = now;

    // Enter del escáner o manual
    if (e.key === 'Enter') {
        e.preventDefault();
        var q = $('#buscarProducto').val().trim();

        if (scannerBuffer.length > 3 && timeDiff < SCANNER_THRESHOLD) {
            // Vino de un escáner - usar buffer
            q = scannerBuffer;
        }

        if (q.length > 0) {
            // Buscar y agregar directamente el primer resultado
            $.getJSON(BASE_URL+'/productos/buscar', { q: q }, function(r) {
                if (r.success && r.data && r.data.length > 0) {
                    var p = r.data[0];
                    agregarAlCarrito(p.id_producto,
                        escHtml2(p.descripcion).replace(/'/g,"\\'"),
                        escHtml2(p.codigo_producto||p.codigo),
                        p.valor_venta||0,
                        p.iva_valor||0,
                        p.fraccion||0,
                        p.valor_unidad||0,
                        p.unidad||0,
                        p.tipo_venta||'UNIDAD',
                        escHtml2(p.unidad_medida||''),
                        p.cantidad_por_unidad||1
                    );
                    // Si hay más de un resultado, mostrar los demás
                    if (r.data.length > 1) {
                        mostrarResultadosBusqueda(r.data);
                    }
                } else {
                    PNotify.error({ text: 'Producto no encontrado: ' + q });
                }
            });
        }

        scannerBuffer = '';
        return;
    }

    // Detectar escáner por velocidad de tipeo
    if (timeDiff < SCANNER_THRESHOLD && e.key.length === 1) {
        scannerBuffer += e.key;
    } else if (e.key.length === 1) {
        scannerBuffer = e.key;
    } else {
        scannerBuffer = '';
    }

    // Reset buffer si pasa mucho tiempo sin teclear
    clearTimeout(scannerTimer);
    scannerTimer = setTimeout(function() { scannerBuffer = ''; }, 200);
});

// ===== BÚSQUEDA DE PRODUCTOS (manual) =====
var timerBusqueda;
$('#buscarProducto').on('keyup', function(e) {
    if (e.key === 'Enter') return; // Ya lo manejamos arriba

    clearTimeout(timerBusqueda);
    var q = $(this).val().trim();
    if (q.length < 1) { $('#resultadosBusqueda').hide(); return; }
    timerBusqueda = setTimeout(function() {
        $.getJSON(BASE_URL+'/productos/buscar', { q: q }, function(r) {
            if (!r.success || !r.data || !r.data.length) { $('#resultadosBusqueda').hide(); return; }
            mostrarResultadosBusqueda(r.data);
        });
    }, 250);
});

function mostrarResultadosBusqueda(data) {
    var html = '';
    $.each(data, function(i, p) {
        var esDecimal = p.tipo_venta === 'FRACCION_DECIMAL';
        var stockVal = esDecimal
            ? (parseFloat(p.stock_fraccion||0) > 0 ? (parseFloat(p.stock_fraccion||0)).toFixed(2) + ' ' + (p.unidad_medida||'und') : (p.unidad||0) + ' und')
            : (p.unidad||0);
        var stockClass = (p.unidad||0) <= (p.stock_minimo||0) ? 'text-danger' : 'text-muted';
        html += '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" ' +
            'onclick="agregarAlCarrito(' + p.id_producto + ',\'' + escHtml2(p.descripcion).replace(/'/g,"\\'") + '\',\'' + escHtml2(p.codigo_producto||p.codigo) + '\',' + (p.valor_venta||0) + ',' + (p.iva_valor||0) + ',' + (p.fraccion||0) + ',' + (p.valor_unidad||0) + ',' + (p.unidad||0) + ',\'' + (p.tipo_venta||'UNIDAD') + '\',\'' + escHtml2(p.unidad_medida||'') + '\',' + (p.cantidad_por_unidad||1) + ');$(\'#resultadosBusqueda\').hide()" title="Agregar al carrito">' +
            '<div><strong>' + escHtml2(p.codigo_producto||p.codigo) + '</strong> - ' + escHtml2(p.descripcion) +
            (p.presentacion ? ' <small class="text-muted">' + escHtml2(p.presentacion) + '</small>' : '') +
            '</div><div class="text-end"><small class="' + stockClass + '">Stock: ' + stockVal + '</small>' +
            '<br><strong>$' + formatoNumero(p.valor_venta) + '</strong></div></button>';
    });
    $('#resultadosBusqueda').html(html).show();
}

let prodPagina = 1;
let prodSearch = '';

function escHtml2(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }

// ===== PRODUCTOS PAGINADOS =====
function cargarProductosPos() {
    $('#tbodyProductosPos').html('<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x d-block mb-2"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL+'/productos/listar-pos', { page: prodPagina, search: prodSearch }, function(r) {
        if (!r.success) return;
        var d = r.data;
        var html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-box-open fa-2x d-block mb-2"></i>No hay productos</td></tr>';
        } else {
            $.each(d.data, function(i, p) {
                var esDecimal = p.tipo_venta === 'FRACCION_DECIMAL';
                var stock = parseInt(p.stock_unidad) || 0;
                var stockFrac = parseFloat(p.stock_fraccion) || 0;
                var stockLabel;
                var stockClass, stockIcon;
                if (esDecimal) {
                    // Stock decimal: mostrar fraccion (decimal) + unidades como respaldo
                    if (stock <= 0 && stockFrac <= 0) {
                        stockClass = 'text-danger fw-bold';
                        stockIcon = '<i class="fas fa-times-circle text-danger me-1"></i>';
                        stockLabel = 'AGOTADO';
                    } else if (stock <= (parseInt(p.stock_minimo)||0)) {
                        stockClass = 'text-warning fw-bold';
                        stockIcon = '<i class="fas fa-exclamation-triangle text-warning me-1"></i>';
                        stockLabel = stockFrac.toFixed(2) + (stock > 0 ? ' +' + stock + ' ud' : ' ' + (p.unidad_medida||'und'));
                    } else {
                        stockClass = 'text-success';
                        stockIcon = '<i class="fas fa-check-circle text-success me-1"></i>';
                        stockLabel = stockFrac.toFixed(2) + (stock > 0 ? ' +' + stock + ' ud' : ' ' + (p.unidad_medida||'und'));
                    }
                } else {
                    stockLabel = stock;
                    if (stockFrac > 0) stockLabel += '+' + stockFrac;
                    if (stock <= 0) {
                        stockClass = 'text-danger fw-bold';
                        stockIcon = '<i class="fas fa-times-circle text-danger me-1"></i>';
                        stockLabel = 'AGOTADO';
                    } else if (stock <= (parseInt(p.stock_minimo)||0)) {
                        stockClass = 'text-warning fw-bold';
                        stockIcon = '<i class="fas fa-exclamation-triangle text-warning me-1"></i>';
                    } else {
                        stockClass = 'text-success';
                        stockIcon = '<i class="fas fa-check-circle text-success me-1"></i>';
                    }
                }
                var disabled = stock <= 0 ? 'disabled' : '';
                var fracLabel = '';
                if (p.tipo_venta === 'FRACCION_DECIMAL') {
                    fracLabel = '<i class="fas fa-balance-scale text-warning ms-1" title="Venta por fracción decimal (' + (p.unidad_medida || 'und') + ')"></i>';
                } else if (parseInt(p.fraccion) > 0) {
                    fracLabel = '<i class="fas fa-cubes text-info ms-1" title="Fraccionable: ' + p.fraccion + ' und/cja"></i>';
                } else {
                    fracLabel = '<i class="fas fa-cube text-secondary ms-1" title="No fraccionable"></i>';
                }
                html += '<tr class="' + (stock <= 0 ? 'opacity-50' : '') + '">' +
                    '<td><small class="text-muted">' + escHtml2(p.codigo) + '</small></td>' +
                    '<td><strong>' + escHtml2(p.descripcion) + '</strong>' + fracLabel +
                    (p.presentacion ? '<br><small class="text-muted">' + escHtml2(p.presentacion) + '</small>' : '') + '</td>' +
                    '<td class="fw-bold">$' + formatoNumero(p.valor_venta) + '</td>' +
                    '<td><span class="' + stockClass + '" style="font-size:12px">' + stockIcon + stockLabel + '</span></td>' +
                    '<td><button class="btn btn-sm btn-outline-primary" ' + disabled +
                    ' onclick="agregarAlCarrito(' + p.id_producto + ',\'' + escHtml2(p.descripcion).replace(/'/g,"\\'") + '\',\'' + escHtml2(p.codigo) + '\',' + (p.valor_venta||0) + ',' + (p.iva_porcentaje||0) + ',' + (p.fraccion||0) + ',' + (p.valor_unidad||0) + ',' + stock + ',\'' + (p.tipo_venta||'UNIDAD') + '\',\'' + escHtml2(p.unidad_medida||'') + '\',' + (p.cantidad_por_unidad||1) + ')" title="Agregar al carrito">' +
                    '<i class="fas fa-cart-plus"></i></button></td></tr>';
            });
        }
        $('#tbodyProductosPos').html(html);
        $('#infoProdPag').text('Pág ' + d.page + ' de ' + d.totalPages + ' (' + d.total + ' prod.)');
        $('#infoProdPagBottom').text('Página ' + d.page + ' de ' + d.totalPages + ' — ' + d.total + ' producto(s)');

        // Paginación
        var pagHtml = '';
        if (d.totalPages > 1) {
            pagHtml += '<li class="page-item ' + (d.page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="prodPagina=' + (d.page-1) + ';cargarProductosPos();return false;">&laquo;</a></li>';
            for (var pi = Math.max(1, d.page-2); pi <= Math.min(d.totalPages, d.page+2); pi++) {
                pagHtml += '<li class="page-item ' + (pi === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="prodPagina=' + pi + ';cargarProductosPos();return false;">' + pi + '</a></li>';
            }
            pagHtml += '<li class="page-item ' + (d.page >= d.totalPages ? 'disabled' : '') + '"><a class="page-link" href="#" onclick="prodPagina=' + (d.page+1) + ';cargarProductosPos();return false;">&raquo;</a></li>';
        }
        $('#pagProductos').html(pagHtml);
    });
}

// Filtro de productos en POS
$('#filtroProdPos').on('keyup', function() {
    prodSearch = $(this).val().trim();
    prodPagina = 1;
    cargarProductosPos();
});

// ===== AGREGAR AL CARRITO =====
function agregarAlCarrito(id, nombre, codigo, precio, iva, fraccion, valorUnd, stockDisponible, tipoVenta, unidadMedida, cantidadPorUnidad) {
    stockDisponible = stockDisponible || 9999;
    tipoVenta = tipoVenta || 'UNIDAD';
    unidadMedida = unidadMedida || '';
    cantidadPorUnidad = cantidadPorUnidad || 1;

    var existente = carrito.findIndex(function(p) { return p.id_producto === id; });

    if (tipoVenta === 'FRACCION_DECIMAL') {
        // Producto de fracción decimal: se agrega con cantidad_decimal = 0 (el usuario ingresa el valor)
        if (existente >= 0) {
            // Sumar 0.5 por defecto
            carrito[existente].cantidad_decimal = (carrito[existente].cantidad_decimal || 0) + 0.5;
            carrito[existente].total = carrito[existente].cantidad_decimal * carrito[existente].precio_unitario;
        } else {
            idCounter++;
            carrito.push({
                idCarrito: idCounter,
                id_producto: id,
                descripcion: nombre,
                codigo: codigo,
                precio_unitario: precio,
                valor_unidad: valorUnd || 0,
                iva: iva || 0,
                fraccion: fraccion || 0,
                cantidad_unidad: 0,
                cantidad_fraccion: 0,
                cantidad_decimal: 0.5,
                tipo_venta: 'FRACCION_DECIMAL',
                unidad_medida: unidadMedida,
                cantidad_por_unidad: cantidadPorUnidad,
                stock_maximo: stockDisponible,
                total: precio * 0.5
            });
        }
    } else {
        var enCarritoUnd = existente >= 0 ? carrito[existente].cantidad_unidad : 0;
        var enCarritoFrac = existente >= 0 ? carrito[existente].cantidad_fraccion : 0;
        var esFrac = parseInt(fraccion) > 0;

        if (!esFrac && (enCarritoUnd + enCarritoFrac + 1 > stockDisponible)) {
            PNotify.error({ text: 'Stock insuficiente. Disponible: ' + stockDisponible + ' unidades' });
            return;
        }
        if (esFrac && (enCarritoUnd + 1 > stockDisponible)) {
            PNotify.error({ text: 'Stock insuficiente. Disponible: ' + stockDisponible + ' cajas' });
            return;
        }

        if (existente >= 0) {
            carrito[existente].cantidad_unidad++;
            carrito[existente].total = (carrito[existente].cantidad_unidad * carrito[existente].precio_unitario) + 
                                       (carrito[existente].cantidad_fraccion * (carrito[existente].valor_unidad || carrito[existente].precio_unitario));
        } else {
            idCounter++;
            carrito.push({
                idCarrito: idCounter,
                id_producto: id,
                descripcion: nombre,
                codigo: codigo,
                precio_unitario: precio,
                valor_unidad: valorUnd || 0,
                iva: iva || 0,
                fraccion: fraccion || 0,
                cantidad_unidad: 1,
                cantidad_fraccion: 0,
                tipo_venta: tipoVenta,
                unidad_medida: unidadMedida,
                cantidad_por_unidad: cantidadPorUnidad,
                stock_maximo: stockDisponible,
                total: precio
            });
        }
    }
    
    $('#buscarProducto').val('').focus();
    $('#resultadosBusqueda').hide();
    actualizarCarrito();
}

// ===== ACTUALIZAR CARRITO =====
function actualizarCarrito() {
    var html = '';
    var subtotal = 0, totalIva = 0, total = 0;
    totalGanancia = 0;

    if (carrito.length === 0) {
        $('#filaVacia').show();
        $('#itemsCount').text('0');
        $('#btnFacturar').prop('disabled', true);
        $('#btnFacturarFE').prop('disabled', true);
    } else {
        $('#filaVacia').hide();
        $('#itemsCount').text(carrito.length);
        $('#btnFacturar').prop('disabled', false);
        $('#btnFacturarFE').prop('disabled', false);

        $.each(carrito, function(i, p) {
            var sub;
            var esDecimal = p.tipo_venta === 'FRACCION_DECIMAL';
            var esFraccionable = parseInt(p.fraccion) > 0;

            if (esDecimal) {
                // Venta por fracción decimal
                sub = (parseFloat(p.cantidad_decimal) || 0) * p.precio_unitario;
            } else if (esFraccionable) {
                // Fraccionable: cajas * precio_caja + unidades * precio_unidad
                sub = (p.cantidad_unidad * p.precio_unitario) + (p.cantidad_fraccion * (parseFloat(p.valor_unidad) || p.precio_unitario));
            } else {
                // No fraccionable: (cajas + unidades) * precio_unitario
                sub = (p.cantidad_unidad + p.cantidad_fraccion) * p.precio_unitario;
            }
            var ivaV = sub * (p.iva / 100);
            var tot = sub + ivaV;
            subtotal += sub;
            totalIva += ivaV;
            total += tot;

            var precioLabel = '$' + formatoNumero(p.precio_unitario);

            var inputCajas, inputUnd;
            if (esDecimal) {
                // Input decimal único
                var undLabel = p.unidad_medida ? ' <small class="text-muted">(' + escHtml2(p.unidad_medida) + ')</small>' : '';
                precioLabel += undLabel;
                inputCajas = '<input type="number" class="form-control form-control-sm" value="' + (parseFloat(p.cantidad_decimal) || 0).toFixed(4) + '" min="0" step="0.25" onchange="cambiarCantidadDecimal(' + p.idCarrito + ', this.value)">';
                inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="—" disabled>';
            } else {
                inputCajas = '<input type="number" class="form-control form-control-sm" value="' + p.cantidad_unidad + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'und\')">';
                if (esFraccionable) {
                    inputUnd = '<input type="number" class="form-control form-control-sm" value="' + p.cantidad_fraccion + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'frac\')">';
                } else {
                    inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="N/A" disabled title="Producto no fraccionable">';
                }
            }

            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td><strong>' + escHtml2(p.descripcion) + '</strong><br><small class="text-muted">' + p.codigo + '</small></td>' +
                '<td>' + inputCajas + '</td>' +
                '<td>' + inputUnd + '</td>' +
                '<td>' + precioLabel + '</td>' +
                '<td>' + p.iva + '%</td>' +
                '<td class="fw-bold">$' + formatoNumero(tot) + '</td>' +
                '<td><button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(' + p.idCarrito + ')"><i class="fas fa-times"></i></button></td>' +
                '</tr>';
        });
    }

    $('#tbodyCarrito').html(html);
    
    var descuento = parseFloat($('#descuentoValor').val()) || 0;
    var totalFinal = total - descuento;
    
    $('#lblSubtotal').text('$' + formatoNumero(subtotal));
    $('#lblIva').text('$' + formatoNumero(totalIva));
    $('#lblTotal').text('$' + formatoNumero(Math.max(0, totalFinal)));
    
    if (descuento > 0) {
        $('#descuentoRow').show();
        $('#lblDescuento').text('-$' + formatoNumero(descuento));
    } else {
        $('#descuentoRow').hide();
    }

    calcularCambio();
}

function cambiarCantidad(id, val, tipo) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item) return;
    val = parseInt(val) || 0;
    var maxStock = item.stock_maximo || 9999;
    var esFrac = parseInt(item.fraccion) > 0;

    if (tipo === 'und') {
        if (val > maxStock) {
            PNotify.error({ text: 'Stock insuficiente. Disponible: ' + maxStock + (esFrac ? ' cajas' : ' unidades') });
            actualizarCarrito();
            return;
        }
        item.cantidad_unidad = val;
    } else {
        if (!esFrac) {
            PNotify.error({ text: 'Este producto no es fraccionable. Use solo el campo Cajas para indicar la cantidad.' });
            actualizarCarrito();
            return;
        }
        var fracVal = parseInt(item.fraccion);
        if (val > 0 && val % fracVal !== 0) {
            var msg = '⚠️ Este producto usa fracciones de a <strong>' + fracVal + '</strong> unidades. ' +
                      'No puedes vender <strong>' + val + '</strong> sueltas porque no es múltiplo de ' + fracVal + '.<br><br>' +
                      '🔸 Ingresa un múltiplo de ' + fracVal + ' (ej: ' + (Math.ceil(val/fracVal)*fracVal) + ')<br>' +
                      '🔸 O cambia el producto a <strong>"Fracción Decimal"</strong> en Configuración > Productos para vender cantidades exactas.';
            PNotify.error({ text: msg, hide: false });
            actualizarCarrito();
            return;
        }
        var maxFrac = (maxStock * fracVal) - (item.cantidad_unidad * fracVal);
        if (val > maxFrac) {
            PNotify.error({ text: 'Stock de fracciones insuficiente. Máximo disponible: ' + maxFrac + ' unidades' });
            actualizarCarrito();
            return;
        }
        item.cantidad_fraccion = val;
    }
    actualizarCarrito();
}

function cambiarCantidadDecimal(id, val) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item) return;
    val = parseFloat(val) || 0;
    if (val < 0) val = 0;
    item.cantidad_decimal = val;
    actualizarCarrito();
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(function(p) { return p.idCarrito !== id; });
    actualizarCarrito();
}

// ===== PAGOS FRACCIONADOS =====
var pagos = [];   // [{ metodo, monto }]

function metodoSeleccionado() {
    return $('input[name="tipoPago"]:checked').val() || 'EFECTIVO';
}

function totalAPagar() {
    var totalTexto = $('#lblTotal').text().replace(/[^0-9]/g, '');
    return parseFloat(totalTexto) || 0;
}

function totalPagado() {
    return pagos.reduce(function(s, p) { return s + (parseFloat(p.monto) || 0); }, 0);
}

function agregarPago() {
    var monto = parseFloat($('#pagoMonto').val()) || 0;
    if (monto <= 0) { PNotify.error({ text: 'Escriba el monto del pago' }); return; }

    pagos.push({ metodo: metodoSeleccionado(), monto: monto });
    $('#pagoMonto').val('');
    calcularCambio();
    $('#pagoMonto').focus();
}

function agregarPagoRestante() {
    var falta = totalAPagar() - totalPagado();
    if (falta <= 0) { PNotify.info({ text: 'La venta ya está cubierta' }); return; }

    pagos.push({ metodo: metodoSeleccionado(), monto: Math.round(falta * 100) / 100 });
    $('#pagoMonto').val('');
    calcularCambio();
}

function quitarPago(i) {
    pagos.splice(i, 1);
    calcularCambio();
}

function calcularCambio() {
    var total = totalAPagar();
    var pagado = totalPagado();
    var falta = total - pagado;

    var html = '';
    $.each(pagos, function(i, p) {
        html += '<div class="d-flex justify-content-between align-items-center border rounded-3 px-2 py-1 mb-1 bg-light">' +
                '<span><span class="badge bg-primary">' + escHtml2(p.metodo) + '</span> <strong>$' + formatoNumero(p.monto) + '</strong></span>' +
                '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="quitarPago(' + i + ')" title="Quitar"><i class="fas fa-times"></i></button>' +
                '</div>';
    });
    $('#listaPagos').html(html);

    $('#lblTotalPagar').text('$' + formatoNumero(total));
    $('#lblPagado').text('$' + formatoNumero(pagado));

    if (falta > 0.009) {
        $('#lblFaltaTxt').text('Falta');
        $('#lblFalta').removeClass('text-success').addClass('text-danger').text('$' + formatoNumero(falta));
    } else if (falta < -0.009) {
        $('#lblFaltaTxt').text('Cambio');
        $('#lblFalta').removeClass('text-danger').addClass('text-success').text('$' + formatoNumero(Math.abs(falta)));
    } else {
        $('#lblFaltaTxt').text('Falta');
        $('#lblFalta').removeClass('text-danger').addClass('text-success').text('$0');
    }
}

function limpiarPagos() {
    pagos = [];
    $('#pagoMonto').val('');
    calcularCambio();
}

// ===== CLIENTE =====
function cambiarCliente() {
    $('#busquedaCliente').toggle();
    if ($('#busquedaCliente').is(':visible')) $('#buscarCliente').focus();
}

$('#buscarCliente').on('keyup', function() {
    var q = $(this).val();
    if (q.length < 2) { $('#resultadosClientes').hide(); return; }
    $.getJSON(BASE_URL+'/clientes/buscar', { q: q }, function(r) {
        if (!r.success || !r.data.length) { $('#resultadosClientes').hide(); return; }
        var html = '';
        $.each(r.data, function(i, c) {
            html += '<button type="button" class="list-group-item list-group-item-action" onclick="seleccionarCliente(' + c.id_cliente + ',\'' + escHtml2(c.nombre).replace(/'/g,"\\'") + '\',\'' + c.tipo_documento + '\',\'' + escHtml2(c.documento) + '\')">' +
                '<strong>' + escHtml2(c.nombre) + '</strong><br><small class="text-muted">' + c.tipo_documento + ': ' + c.documento + '</small></button>';
        });
        $('#resultadosClientes').html(html).show();
    });
});

function seleccionarCliente(id, nombre, tipoDoc, doc) {
    $('#id_cliente').val(id);
    $('#clienteNombre').text(nombre);
    $('#clienteDoc').text(tipoDoc + ': ' + doc);
    $('#busquedaCliente').hide();
    $('#resultadosClientes').hide();
    $('#buscarCliente').val('');
}

// ===== VENDEDOR DE LA VENTA =====
// Por defecto la venta queda a nombre del usuario logueado (caja).
// Opcionalmente se puede asignar a un vendedor registrado.
var usuarioCajaNombre = <?= json_encode($usuarioNombre ?? 'Usuario') ?>;
var timerVendPos = null;

function toggleVendedorVenta() {
    // Si ya está abierto, cerrar
    if ($('#panelVendedorVenta').is(':visible')) {
        $('#panelVendedorVenta').hide();
        return;
    }

    // Sincronizar el modo con el vendedor asignado actualmente
    var asignado = parseInt($('#id_vendedor_registrado').val()) > 0;
    var modo = asignado ? 'REGISTRADO' : 'MISMO';

    $('input[name="tipoVendedor"][value="' + modo + '"]')
        .prop('checked', true)
        .closest('label').addClass('active')
        .siblings().removeClass('active');

    $('#busquedaVendedor').toggle(asignado);
    $('#panelVendedorVenta').show();

    if (asignado) {
        $('#buscarVendedor').focus();
    }
}

function cambiarTipoVendedor(el) {
    $(el).closest('label').addClass('active').siblings().removeClass('active');

    if ($(el).val() === 'REGISTRADO') {
        $('#busquedaVendedor').show();
        $('#buscarVendedor').focus();
    } else {
        $('#busquedaVendedor').hide();
        $('#resultadosVendedores').hide();
        $('#buscarVendedor').val('');
        resetVendedorVenta();
    }
}

function resetVendedorVenta() {
    $('#id_vendedor_registrado').val('');
    $('#vendedorNombre').text('Yo mismo (' + usuarioCajaNombre + ')');
    $('#vendedorDetalle').text('Venta a nombre del usuario en caja');
    $('#vendedorInfo').removeClass('border border-primary');
}

$('#buscarVendedor').on('input', function() {
    var q = $(this).val().trim();
    if (q.length < 2) { $('#resultadosVendedores').hide(); return; }

    clearTimeout(timerVendPos);
    timerVendPos = setTimeout(function() {
        $.getJSON(BASE_URL + '/vendedores/buscar', { q: q }, function(r) {
            if (!r.success || !r.data.length) {
                $('#resultadosVendedores').html('<div class="list-group-item text-muted small">Sin resultados</div>').show();
                return;
            }
            var html = '';
            $.each(r.data, function(i, v) {
                var com = parseFloat(v.comision) || 0;
                html += '<button type="button" class="list-group-item list-group-item-action py-2" onclick="seleccionarVendedor(' + v.id_vendedor + ',\'' +
                    escHtml2(v.nombre).replace(/'/g, "\\'") + '\',\'' + escHtml2(v.codigo) + '\',\'' + escHtml2(v.cedula || '') + '\',' + com + ')">' +
                    '<strong>' + escHtml2(v.nombre) + '</strong> <span class="badge bg-light text-dark">' + escHtml2(v.codigo) + '</span>' +
                    (v.cedula ? '<br><small class="text-muted">CC: ' + escHtml2(v.cedula) + '</small>' : '') +
                    ' <small class="text-muted">· Comisión ' + com + '%</small></button>';
            });
            $('#resultadosVendedores').html(html).show();
        });
    }, 300);
});

function seleccionarVendedor(id, nombre, codigo, cedula, comision) {
    $('#id_vendedor_registrado').val(id);
    $('#vendedorNombre').text(nombre);
    $('#vendedorDetalle').text(codigo + (cedula ? ' · CC: ' + cedula : '') + ' · Comisión ' + comision + '%');
    $('#vendedorInfo').addClass('border border-primary');
    $('#resultadosVendedores').hide();
    $('#buscarVendedor').val('');
    $('#busquedaVendedor').hide();
    $('#panelVendedorVenta').hide();
    PNotify.success({ text: 'Venta asignada a ' + nombre });
}

// ===== DESCUENTO (admin) =====
var descuentoVisible = false;
function toggleDescuento() {
    descuentoVisible = !descuentoVisible;
    $('#descuentoInput').toggle();
    if (!descuentoVisible) { $('#descuentoValor').val(0); actualizarCarrito(); }
}

$(document).on('change', '#descuentoValor', function() { actualizarCarrito(); });
$(document).on('keyup', '#descuentoValor', function() { actualizarCarrito(); });

// ===== FACTURAR =====
function facturar(tipo) {
    if (carrito.length === 0) { PNotify.error({ text: 'Carrito vacío' }); return; }

    // Validar stock de todos los productos antes de enviar
    for (var i = 0; i < carrito.length; i++) {
        var p = carrito[i];
        var maxStock = p.stock_maximo || 9999;
        var esFrac = parseInt(p.fraccion) > 0;
        var esDecimal = p.tipo_venta === 'FRACCION_DECIMAL';

        if (!esDecimal && esFrac && p.cantidad_fraccion > 0 && p.cantidad_fraccion % parseInt(p.fraccion) !== 0) {
            PNotify.error({
                text: '⚠️ <strong>' + escHtml2(p.descripcion) + '</strong> usa fracciones de a <strong>' + parseInt(p.fraccion) + '</strong> unidades.<br>' +
                      'Tienes <strong>' + p.cantidad_fraccion + '</strong> en Und, que no es múltiplo de ' + parseInt(p.fraccion) + '.<br><br>' +
                      '🔸 Ingresa un múltiplo de ' + parseInt(p.fraccion) + ' en Und<br>' +
                      '🔸 O cambia el producto a <strong>"Fracción Decimal"</strong> en Productos',
                hide: false
            });
            return;
        }
        if (!esFrac && (p.cantidad_unidad + p.cantidad_fraccion) > maxStock) {
            PNotify.error({ text: 'Stock insuficiente de ' + p.descripcion + '. Disponible: ' + maxStock + ' unidades' });
            return;
        }
        if (esFrac && p.cantidad_unidad > maxStock) {
            PNotify.error({ text: 'Stock insuficiente de ' + p.descripcion + '. Disponible: ' + maxStock + ' cajas' });
            return;
        }
    }

    var detalles = [];
    $.each(carrito, function(i, p) {
        var det = {
            id_producto: p.id_producto,
            descripcion: p.descripcion,
            cantidad_unidad: p.cantidad_unidad || 0,
            cantidad_fraccion: p.cantidad_fraccion || 0,
            precio_unitario: p.precio_unitario,
            iva: p.iva,
            fraccion: p.fraccion
        };
        if (p.tipo_venta === 'FRACCION_DECIMAL') {
            det.cantidad_decimal = parseFloat(p.cantidad_decimal) || 0;
            det.cantidad_unidad = 0;
            det.cantidad_fraccion = 0;
        }
        detalles.push(det);
    });

    var totalPagar = totalAPagar();
    var pagado = totalPagado();

    // Si no agregaron pagos, se cobra todo con el método seleccionado
    if (!pagos.length) {
        pagos.push({ metodo: metodoSeleccionado(), monto: totalPagar });
        calcularCambio();
    }

    if (totalPagado() + 0.009 < totalPagar) {
        PNotify.error({ text: 'Faltan $' + formatoNumero(totalPagar - totalPagado()) + ' por cubrir' });
        return;
    }

    var data = {
        id_cliente: parseInt($('#id_cliente').val()) || 1,
        tipo_pago: metodoSeleccionado(),
        pagos: pagos,
        pago_recibido: totalPagado(),
        descuento: parseFloat($('#descuentoValor').val()) || 0,
        tipo: tipo,
        id_vendedor_registrado: parseInt($('#id_vendedor_registrado').val()) || null,
        detalles: detalles
    };

    var btn = tipo === 'ELECTRONICA' ? '#btnFacturarFE' : '#btnFacturar';
    $(btn).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');

    $.ajax({
        url: BASE_URL+'/facturacion/guardar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                var idFactura = r.data.id_factura;
                PNotify.success({ text: 'Factura #' + r.data.codigo + ' creada' });

                // Si es FE, enviar a DIAN
                if (tipo === 'ELECTRONICA' && r.data.fe_pendiente) {
                    enviarFE(idFactura, r.data.codigo);
                } else {
                    // Abrir PDF
                    window.open(BASE_URL+'/facturacion/pdf/' + idFactura, '_blank');
                }

                // Limpiar carrito y recargar productos
                carrito = [];
                idCounter = 0;
                actualizarCarrito();
                resetVendedorVenta();
                limpiarPagos();
                cargarProductosPos();
                $('#buscarProducto').focus();
            } else {
                PNotify.error({ text: r.message });
            }
            $(btn).prop('disabled', false).html('<i class="fas fa-receipt me-2"></i>Facturar (F2)');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al crear factura';
            PNotify.error({ text: msg });
            $(btn).prop('disabled', false).html('<i class="fas fa-receipt me-2"></i>Facturar (F2)');
        }
    });
}

// ===== ENVÍO A DIAN (Factura Electrónica) =====
function enviarFE(idFactura, codigo) {
    PNotify.info({ text: 'Enviando factura #' + codigo + ' a la DIAN...', hide: false });

    $.ajax({
        url: BASE_URL+'/api/facturacion-electronica/enviar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ id_factura: idFactura }),
        success: function(r) {
            if (r.success) {
                PNotify.success({ 
                    text: '✅ Factura electrónica #' + codigo + ' emitida exitosamente' + (r.data?.cufe ? ' - CUFE generado' : '')
                });
                var params = '?factura=' + idFactura;
                if (r.data?.cufe) params += '&cufe=' + encodeURIComponent(r.data.cufe);
                if (r.data?.qr) params += '&qr=' + encodeURIComponent(r.data.qr);
                window.open(BASE_URL+'/facturacion/pdf/' + idFactura, '_blank');
            } else {
                PNotify.error({ 
                    text: '❌ ' + (r.message || 'Error al emitir factura electrónica'),
                    hide: false
                });
            }
        },
        error: function() {
            PNotify.error({ text: 'Error de conexión con la DIAN', hide: false });
        }
    });
}

// ===== TECLAS RÁPIDAS =====
$(document).on('keydown', function(e) {
    if (e.key === 'F1') { e.preventDefault(); $('#buscarProducto').focus().select(); }
    if (e.key === 'F2') { e.preventDefault(); facturar('NORMAL'); }
    if (e.key === 'F3') { e.preventDefault(); facturar('ELECTRONICA'); }
    if (e.key === 'F4') { e.preventDefault(); $('#pagoMonto').focus().select(); }
    if (e.key === 'Escape') { carrito = []; actualizarCarrito(); }
    if (e.key === 'F8') { cambiarCliente(); }
    if (e.key === 'F9') { e.preventDefault(); toggleVendedorVenta(); }
});

// Recargar productos después de facturar
function recargarVista() {
    cargarProductosPos();
    $('#buscarProducto').focus();
}

// Al inicio, cargar productos y enfocar búsqueda
$(function() {
    cargarProductosPos();
    $('#buscarProducto').focus();
});

// Evitar submit con Enter en el input de búsqueda que agregue producto
$('#buscarProducto').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        var q = $(this).val().trim();
        if (q.length > 0) {
            // Forzar búsqueda y seleccionar primer resultado
            $('#resultadosBusqueda button:first').click();
        }
    }
});
</script>

<style>
#tbodyCarrito input[type="number"] {
    text-align: center;
    min-width: 60px;
}
.list-group-item:hover {
    background-color: #f0f0ff;
}
#buscarProducto:focus {
    box-shadow: 0 0 0 3px rgba(118,75,162,0.25);
    border-color: #764ba2;
}
</style>
