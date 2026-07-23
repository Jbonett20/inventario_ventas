<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-cash-register text-primary me-2"></i>Facturación</h4>
                <p class="text-muted mb-0">Punto de venta - Crear factura</p>
            </div>
            <div>
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
                <h6 class="fw-bold mb-3"><i class="fas fa-credit-card me-2"></i>Forma de Pago</h6>
                <div class="d-flex flex-wrap gap-2 mb-3" id="metodosPago">
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
                    <label class="form-label small fw-semibold">Recibido</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control form-control-lg" id="pagoRecibido" min="0" step="500" value="0" onchange="calcularCambio()" onkeyup="calcularCambio()">
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Cambio</span>
                    <span class="fw-bold fs-5 text-success" id="lblCambio">$0</span>
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

// ===== BÚSQUEDA DE PRODUCTOS =====
var timerBusqueda;
$('#buscarProducto').on('keyup', function() {
    clearTimeout(timerBusqueda);
    var q = $(this).val().trim();
    if (q.length < 1) { $('#resultadosBusqueda').hide(); return; }
    timerBusqueda = setTimeout(function() {
        $.getJSON(BASE_URL+'/productos/buscar', { q: q }, function(r) {
            if (!r.success || !r.data.length) { $('#resultadosBusqueda').hide(); return; }
            var html = '';
            $.each(r.data, function(i, p) {
                var stockClass = (p.unidad||0) <= (p.stock_minimo||0) ? 'text-danger' : 'text-muted';
                html += '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" ' +
                    'onclick="agregarAlCarrito(' + p.id_producto + ',\'' + escHtml2(p.descripcion).replace(/'/g,"\\'") + '\',\'' + escHtml2(p.codigo_producto||p.codigo) + '\',' + (p.valor_venta||0) + ',' + (p.iva_valor||0) + ',' + (p.fraccion||0) + ',' + (p.valor_unidad||0) + ')">' +
                    '<div><strong>' + escHtml2(p.codigo_producto||p.codigo) + '</strong> - ' + escHtml2(p.descripcion) +
                    (p.presentacion ? ' <small class="text-muted">' + escHtml2(p.presentacion) + '</small>' : '') +
                    '</div><div class="text-end"><small class="' + stockClass + '">Stock: ' + (p.unidad||0) + '</small>' +
                    '<br><strong>$' + formatoNumero(p.valor_venta) + '</strong></div></button>';
            });
            $('#resultadosBusqueda').html(html).show();
        });
    }, 250);
});

function escHtml2(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }

// ===== AGREGAR AL CARRITO =====
function agregarAlCarrito(id, nombre, codigo, precio, iva, fraccion, valorUnd) {
    // Verificar si ya existe en carrito
    var existente = carrito.findIndex(function(p) { return p.id_producto === id; });
    
    if (existente >= 0) {
        // Incrementar cantidad
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
            total: precio
        });
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
            var sub = p.cantidad_unidad * p.precio_unitario;
            var ivaV = sub * (p.iva / 100);
            var tot = sub + ivaV;
            subtotal += sub;
            totalIva += ivaV;
            total += tot;

            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td><strong>' + escHtml2(p.descripcion) + '</strong><br><small class="text-muted">' + p.codigo + '</small></td>' +
                '<td><input type="number" class="form-control form-control-sm" value="' + p.cantidad_unidad + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'und\')"></td>' +
                '<td><input type="number" class="form-control form-control-sm" value="' + p.cantidad_fraccion + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'frac\')"></td>' +
                '<td>$' + formatoNumero(p.precio_unitario) + '</td>' +
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
    if (tipo === 'und') item.cantidad_unidad = val;
    else item.cantidad_fraccion = val;
    if (item.cantidad_unidad <= 0 && item.cantidad_fraccion <= 0) {
        carrito = carrito.filter(function(p) { return p.idCarrito !== id; });
    }
    actualizarCarrito();
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(function(p) { return p.idCarrito !== id; });
    actualizarCarrito();
}

// ===== CÁLCULO DE CAMBIO =====
function calcularCambio() {
    var totalTexto = $('#lblTotal').text().replace(/[^0-9]/g, '');
    var total = parseFloat(totalTexto) || 0;
    var pago = parseFloat($('#pagoRecibido').val()) || 0;
    var cambio = Math.max(0, pago - total);
    $('#lblCambio').text('$' + formatoNumero(cambio));
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

    var detalles = [];
    $.each(carrito, function(i, p) {
        detalles.push({
            id_producto: p.id_producto,
            descripcion: p.descripcion,
            cantidad_unidad: p.cantidad_unidad,
            cantidad_fraccion: p.cantidad_fraccion || 0,
            precio_unitario: p.precio_unitario,
            iva: p.iva,
            fraccion: p.fraccion
        });
    });

    var data = {
        id_cliente: parseInt($('#id_cliente').val()) || 1,
        tipo_pago: $('input[name="tipoPago"]:checked').val() || 'EFECTIVO',
        pago_recibido: parseFloat($('#pagoRecibido').val()) || 0,
        descuento: parseFloat($('#descuentoValor').val()) || 0,
        tipo: tipo,
        detalles: detalles
    };

    var btn = tipo === 'ELECTRONICA' ? '#btnFacturarFE' : '#btnFacturar';
    $(btn).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');

    $.ajax({
        url: BASE_URL+BASE_URL+'/facturacion/guardar',
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

                // Limpiar carrito
                carrito = [];
                idCounter = 0;
                actualizarCarrito();
                $('#pagoRecibido').val(0);
                $('#lblCambio').text('$0');
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
    if (e.key === 'F4') { e.preventDefault(); $('#pagoRecibido').focus().select(); }
    if (e.key === 'Escape') { carrito = []; actualizarCarrito(); }
    if (e.key === 'F8') { cambiarCliente(); }
});

// Al inicio, enfocar búsqueda
$(function() { $('#buscarProducto').focus(); });

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
