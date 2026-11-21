<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-cash-register text-primary me-2"></i>Facturación</h4>
                <p class="text-muted mb-0">Punto de venta - Crear factura</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($turnoAbierto): ?>
                <span class="badge bg-success bg-opacity-10 text-success p-2 px-3 rounded-pill">
                    <i class="fas fa-cash-register me-1"></i>
                    Turno #<?= (int)$turnoAbierto['id_turno'] ?> A· Base: $<?= number_format((float)$turnoAbierto['base_inicial'], 0, ',', '.') ?>
                </span>
                <button class="btn btn-outline-danger btn-sm" type="button" onclick="mostrarModalCerrarTurno()">
                    <i class="fas fa-lock me-1"></i>Cerrar turno
                </button>
                <?php else: ?>
                <span class="badge bg-warning bg-opacity-10 text-warning p-2 px-3 rounded-pill" id="badgeSinTurno">
                    <i class="fas fa-exclamation-triangle me-1"></i>Sin turno abierto
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
    <!-- COLUMNA IZQUIERDA: Productos y bÃºsqueda -->
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
                                <th style="width:60px">CÃ³digo</th>
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
                    <table class="table table-hover align-middle mb-0" id="tablaCarrito" style="table-layout:fixed;width:100%;min-width:760px">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Producto</th>
                                <th style="width:70px" id="thColCajas">Cajas</th>
                                <th style="width:70px" id="thColIntermedia" class="d-none">Nivel</th>
                                <th style="width:80px" id="thColUnidades">Und</th>
                                <th style="width:110px">P.Unit</th>
                                <th style="width:60px">IVA</th>
                                <th style="width:90px">Total</th>
                                <th style="width:130px">Opciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCarrito">
                            <tr id="filaVacia">
                                <td colspan="9" class="text-center text-muted py-5">
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
                        <input type="text" class="form-control form-control-sm" id="buscarVendedor" placeholder="Buscar por cÃ©dula, cÃ³digo o nombre...">
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

                <!-- MÃ©todo con el que se agrega el pago -->
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

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="ventaCredito" onchange="cambiarModoCredito()">
                    <label class="form-check-label fw-semibold" for="ventaCredito">Financiar saldo pendiente al cliente</label>
                    <div class="form-text">Selecciona un cliente activo. Los pagos registrados sÃ­ ingresan a caja; el saldo queda en cartera.</div>
                </div>
                <div class="mb-2 ms-4" id="fechaCreditoWrap" style="display:none;">
                    <label class="form-label small fw-semibold" for="fechaVencimientoCredito">Vencimiento (opcional)</label>
                    <input type="date" class="form-control form-control-sm" id="fechaVencimientoCredito">
                </div>

                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="pagoMonto" min="0" step="100" value=""
                               placeholder="Monto" onkeydown="if(event.key==='Enter'){event.preventDefault();agregarPago();}">
                        <button class="btn btn-primary" type="button" onclick="agregarPago()" title="Agregar el pago con el mÃ©todo seleccionado">
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

<!-- Modal para abrir un turno -->
<?php if (!$turnoAbierto): ?>
<div class="modal fade" id="modalAbrirTurno" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title fw-bold"><i class="fas fa-cash-register me-2"></i>Abrir turno de caja</h5>
            </div>
            <div class="modal-body">
                <p class="mb-3">Registra la base inicial para este turno:</p>
                <div class="input-group input-group-lg">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="inputBaseInicial" min="0" step="0.01" value="0" autofocus>
                </div>
                <small class="text-muted">Cada apertura queda registrada por separado, incluso si hay varios turnos en el dí­a.</small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary w-100" onclick="abrirTurno()">
                    <i class="fas fa-check me-1"></i>Abrir turno
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function abrirTurno() {
    var base = Number($('#inputBaseInicial').val());
    if (!Number.isFinite(base) || base < 0) {
        PNotify.error({ text: 'Ingresa una base inicial vÃ¡lida' });
        return;
    }

    $.ajax({
        url: BASE_URL+'/facturacion/turno/abrir',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ base_inicial: base }),
        success: function(r) {
            if (r.success) {
                window.location.reload();
            } else {
                PNotify.error({ text: r.message });
            }
        },
        error: function(xhr) {
            PNotify.error({
                text: xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'No se pudo abrir el turno de caja'
            });
        }
    });
}

$(function() {
    $('#modalAbrirTurno').modal('show');
});
</script>
<?php endif; ?>

<?php if ($turnoAbierto): ?>
<div class="modal fade" id="modalCerrarTurno" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold"><i class="fas fa-lock me-2"></i>Cerrar turno #<?= (int)$turnoAbierto['id_turno'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="resumenTurnoCaja" class="mb-3">
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-spinner fa-spin me-1"></i> Cargando resumen del turno...
                    </div>
                </div>
                <div id="errorResumenTurno" class="alert alert-danger d-none" role="alert"></div>
                <p class="mb-2">Cuenta e ingresa el efectivo que queda en caja:</p>
                <div class="input-group input-group-lg">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="inputEfectivoContado" min="0" step="0.01" value="0">
                </div>
                <div class="small mt-2" id="diferenciaTurno" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger w-100" id="btnConfirmarCierreTurno" type="button" onclick="cerrarTurno()" disabled>
                    <i class="fas fa-check me-1"></i>Confirmar cierre
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalTurnoCerrado" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2 text-success"></i>Turno cerrado</h5>
            </div>
            <div class="modal-body">
                <div id="resumenCierreFinal" class="mb-3"></div>
                <p class="mb-0">Puedes imprimir el comprobante del cierre antes de volver al punto de venta.</p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-primary" id="linkComprobanteTurno" href="#" target="_blank" rel="noopener">
                    <i class="fas fa-print me-1"></i>Imprimir comprobante
                </a>
                <button class="btn btn-success" type="button" onclick="window.location.reload()">
                    Volver al punto de venta
                </button>
            </div>
        </div>
    </div>
</div>
<script>
var resumenTurnoActual = null;

function mostrarModalCerrarTurno() {
    if (carrito.length > 0) {
        PNotify.error({ text: 'Finaliza o limpia la venta actual antes de cerrar el turno' });
        return;
    }
    $('#modalCerrarTurno').modal('show');
}

function cargarResumenTurno() {
    resumenTurnoActual = null;
    $('#btnConfirmarCierreTurno').prop('disabled', true);
    $('#errorResumenTurno').addClass('d-none').text('');
    $('#resumenTurnoCaja').html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-1"></i> Cargando resumen del turno...</div>');

    $.ajax({
        url: BASE_URL + '/facturacion/turno/resumen',
        method: 'GET',
        dataType: 'json',
        success: function(r) {
            if (!r.success || !r.data) {
                $('#errorResumenTurno').removeClass('d-none').text(r.message || 'No se pudo cargar el resumen del turno.');
                return;
            }

            resumenTurnoActual = r.data;
            var html = '<div class="table-responsive"><table class="table table-sm mb-2">' +
                '<tbody>' +
                '<tr><td>Base inicial</td><td class="text-end">$' + formatoNumero(r.data.base_inicial) + '</td></tr>' +
                '<tr><td>Ventas en efectivo (netas del cambio)</td><td class="text-end">$' + formatoNumero(r.data.ventas_efectivo) + '</td></tr>' +
                '<tr><td>Cambio entregado (ya descontado)</td><td class="text-end">$' + formatoNumero(r.data.cambio_total) + '</td></tr>' +
                '<tr><td>Devoluciones en efectivo</td><td class="text-end">-$' + formatoNumero(r.data.devolucion_efectivo) + '</td></tr>' +
                '<tr><td>Egresos en efectivo</td><td class="text-end">-$' + formatoNumero(r.data.egreso_efectivo) + '</td></tr>' +
                '<tr class="table-light fw-bold"><td>Efectivo esperado</td><td class="text-end">$' + formatoNumero(r.data.efectivo_esperado) + '</td></tr>' +
                '</tbody></table></div>' +
                '<div class="small text-muted mb-2">El cambio ya estÃ¡ descontado de los pagos de efectivo y no se vuelve a restar.</div>';

            if (r.data.medios_no_efectivos && r.data.medios_no_efectivos.length) {
                html += '<div class="small fw-bold mb-1">Movimientos por medios no efectivos</div>' +
                    '<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr>' +
                    '<th>MÃ©todo</th><th class="text-end">Ventas</th><th class="text-end">Devoluciones</th><th class="text-end">Egresos</th>' +
                    '</tr></thead><tbody>';
                $.each(r.data.medios_no_efectivos, function(i, m) {
                    html += '<tr><td>' + escHtml2(m.metodo) + '</td>' +
                        '<td class="text-end">$' + formatoNumero(m.ventas) + '</td>' +
                        '<td class="text-end">$' + formatoNumero(m.devoluciones) + '</td>' +
                        '<td class="text-end">$' + formatoNumero(m.egresos) + '</td></tr>';
                });
                html += '</tbody></table></div>';
            } else {
                html += '<div class="small text-muted">Sin movimientos por medios no efectivos.</div>';
            }

            $('#resumenTurnoCaja').html(html);
            $('#btnConfirmarCierreTurno').prop('disabled', false);
            actualizarDiferenciaTurno();
        },
        error: function(xhr) {
            var mensaje = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'No se pudo cargar el resumen del turno.';
            $('#errorResumenTurno').removeClass('d-none').text(mensaje);
        }
    });
}

function actualizarDiferenciaTurno() {
    if (!resumenTurnoActual) return;
    var contado = Number($('#inputEfectivoContado').val());
    if (!Number.isFinite(contado) || contado < 0) return;

    var diferencia = Math.round((contado - Number(resumenTurnoActual.efectivo_esperado)) * 100) / 100;
    var texto = diferencia === 0
        ? 'Sin diferencia'
        : (diferencia < 0 ? 'Faltante: -$' : 'Sobrante: +$') + formatoNumero(Math.abs(diferencia));
    $('#diferenciaTurno')
        .removeClass('text-danger text-success text-muted')
        .addClass(diferencia < 0 ? 'text-danger' : (diferencia > 0 ? 'text-success' : 'text-muted'))
        .text(texto);
}

$('#modalCerrarTurno').on('shown.bs.modal', cargarResumenTurno);
$('#inputEfectivoContado').on('input', actualizarDiferenciaTurno);

function cerrarTurno() {
    var efectivoContado = Number($('#inputEfectivoContado').val());
    if (!Number.isFinite(efectivoContado) || efectivoContado < 0) {
        PNotify.error({ text: 'Ingresa un efectivo contado vÃ¡lido' });
        return;
    }
    if (!resumenTurnoActual) {
        PNotify.error({ text: 'Espera a que se cargue el resumen del turno' });
        return;
    }

    $.ajax({
        url: BASE_URL+'/facturacion/turno/cerrar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            id_turno: <?= (int)$turnoAbierto['id_turno'] ?>,
            efectivo_contado: efectivoContado
        }),
        success: function(r) {
            if (r.success) {
                var diferencia = Number(r.data.diferencia || 0);
                var diferenciaTexto = diferencia < 0
                    ? 'Faltante: -$' + formatoNumero(Math.abs(diferencia))
                    : (diferencia > 0 ? 'Sobrante: +$' + formatoNumero(diferencia) : 'Sin diferencia');
                $('#resumenCierreFinal').html(
                    '<div class="d-flex justify-content-between"><span>Efectivo esperado</span><strong>$' + formatoNumero(r.data.efectivo_esperado) + '</strong></div>' +
                    '<div class="d-flex justify-content-between"><span>Efectivo contado</span><strong>$' + formatoNumero(r.data.efectivo_contado) + '</strong></div>' +
                    '<div class="d-flex justify-content-between"><span>Diferencia</span><strong>' + diferenciaTexto + '</strong></div>'
                );
                $('#linkComprobanteTurno').attr(
                    'href',
                    BASE_URL + '/facturacion/turno/' + encodeURIComponent(r.data.id_turno) + '/imprimir'
                );
                $('#modalCerrarTurno').modal('hide');
                $('#modalTurnoCerrado').modal({ backdrop: 'static', keyboard: false });
            } else {
                PNotify.error({ text: r.message });
            }
        },
        error: function(xhr) {
            PNotify.error({
                text: xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'No se pudo cerrar el turno'
            });
        }
    });
}
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

// ===== DETECCIÃ“N DE ESCÃNER DE CÃ“DIGO DE BARRAS =====
var scannerTimer;
var scannerBuffer = '';
var scannerLastTime = 0;
// Si hay menos de 50ms entre teclas, es un escÃ¡ner
var SCANNER_THRESHOLD = 50;

$(document).on('keydown', function(e) {
    // Si el foco estÃ¡ en el input de bÃºsqueda
    if (!$('#buscarProducto').is(':focus')) return;

    var now = Date.now();
    var timeDiff = now - scannerLastTime;
    scannerLastTime = now;

    // Enter del escÃ¡ner o manual
    if (e.key === 'Enter') {
        e.preventDefault();
        var q = $('#buscarProducto').val().trim();

        if (scannerBuffer.length > 3 && timeDiff < SCANNER_THRESHOLD) {
            // Vino de un escÃ¡ner - usar buffer
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
                        parseInt(p.stock_vendible, 10) || 0,
                        p.tipo_venta||'UNIDAD',
                        escHtml2(p.unidad_medida||''),
                        p.cantidad_por_unidad||1,
                        p.valor_intermedio||0,
                        escHtml2(p.nombre_intermedio||''),
                        p.unidades_por_intermedio||1
                    );
                    // Si hay mÃ¡s de un resultado, mostrar los demÃ¡s
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

    // Detectar escÃ¡ner por velocidad de tipeo
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

// ===== BÃšSQUEDA DE PRODUCTOS (manual) =====
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
        var stockVendible = parseInt(p.stock_vendible, 10) || 0;
        var stockVal = textoStockProducto(p, stockVendible);
        var stockClass = stockVendible <= (parseInt(p.stock_minimo, 10) || 0) ? 'text-danger' : 'text-muted';
        html += '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" ' +
            'onclick="agregarAlCarrito(' + p.id_producto + ',\'' + escHtml2(p.descripcion).replace(/'/g,"\\'") + '\',\'' + escHtml2(p.codigo_producto||p.codigo) + '\',' + (p.valor_venta||0) + ',' + (p.iva_valor||0) + ',' + (p.fraccion||0) + ',' + (p.valor_unidad||0) + ',' + stockVendible + ',\'' + (p.tipo_venta||'UNIDAD') + '\',\'' + escHtml2(p.unidad_medida||'') + '\',' + (p.cantidad_por_unidad||1) + ',' + (p.valor_intermedio||0) + ',\'' + escHtml2(p.nombre_intermedio||'') + '\',' + (p.unidades_por_intermedio||1) + ',' + (p.unidades_intermedias||0) + ');$(\'#resultadosBusqueda\').hide()" title="Agregar al carrito">' +
            '<div><strong>' + escHtml2(p.codigo_producto||p.codigo) + '</strong> - ' + escHtml2(p.descripcion) +
            (p.presentacion ? ' <small class="text-muted">' + escHtml2(p.presentacion) + '</small>' : '') +
            '</div><div class="text-end"><small class="' + stockClass + '">Stock: ' + stockVal + '</small>' +
            '<br><strong>' + etiquetaPrecio(p) + '</strong></div></button>';
    });
    $('#resultadosBusqueda').html(html).show();
}

// Precio que se muestra según la presentación configurada.
function etiquetaPrecio(p) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') {
        return '$' + formatoNumero(p.valor_venta) + ' <small class="text-muted">/' + escHtml2(p.unidad_medida || 'und') + '</small>';
    }
    var porCaja = parseInt(p.unidad_cerrada, 10) || 1;
    var intermedios = parseInt(p.unidades_intermedias, 10) || 0;
    var texto = '$' + formatoNumero(p.valor_venta) + ' <small class="text-muted">caja</small>';
    if (porCaja > 1 && intermedios > 0 && parseFloat(p.valor_intermedio) > 0) {
        texto += ' · $' + formatoNumero(p.valor_intermedio) + ' <small class="text-muted">' + escHtml2(p.nombre_intermedio || 'nivel') + '</small>';
    }
    if (porCaja > 1 && parseInt(p.fraccion, 10) > 0) {
        texto += ' · $' + formatoNumero(p.valor_unidad) + ' <small class="text-muted">und</small>';
    }
    return texto;
}

// Cantidades del producto en texto (caja / nivel intermedio / unidad mínima)
function unidadMinimaEtiqueta(p) {
    return escHtml2(p.nombre_intermedio || 'und');
}

// Equivalencia de la caja que se vende abierta: "1 caja = 50 sobres x 10 pastillas"
function textoAbreCaja(p, porCaja) {
    var intermedios = parseInt(p.unidades_intermedias, 10) || 0;
    var porIntermedio = parseInt(p.unidades_por_intermedio, 10) || 1;
    var plural = function(t) { return t + 's'; };
    if (intermedios > 0 && (parseFloat(p.valor_intermedio) || 0) > 0) {
        if (porIntermedio <= 1) {
            return '1 caja = ' + intermedios + ' ' + escHtml2(p.nombre_intermedio || 'nivel') + '(es)';
        }
        return '1 caja = ' + intermedios + ' ' + escHtml2(p.nombre_intermedio || 'nivel') + '(es) × ' + porIntermedio + ' ' + plural(etiquetaMinimaCorta(p));
    }
    return '1 caja = ' + porCaja + ' ' + plural(etiquetaMinimaCorta(p));
}

// Etiqueta corta de la unidad mínima para los botones +/- del carrito
function etiquetaMinimaCorta(p) {
    return etiquetaUnidadMinima(p);
}

// Un producto tiene nivel intermedio (caja -> sobre -> pastilla) cuando
// trae sobres configurados con precio de venta.
function tieneNivelIntermedio(p) {
    return (parseInt(p.unidades_intermedias, 10) || 0) > 0 && (parseFloat(p.valor_intermedio) || 0) > 0;
}

function nombreNivelIntermedio(p) {
    return escHtml2(p.nombre_intermedio || 'nivel');
}

// Texto del stock que queda de un producto, desglosado en cajas, sobres y
// unidades mínimas según su configuración de empaque.
function textoDisponibleProducto(p, unidades) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') {
        return (parseFloat(unidades) || 0).toFixed(2) + ' ' + (p.unidad_medida || 'und');
    }
    var porCaja = parseInt(p.cantidad_por_unidad, 10) || 1;
    if (porCaja <= 1) {
        return unidades + ' und';
    }
    var cajas = Math.floor(unidades / porCaja);
    var resto = unidades % porCaja;
    var texto = cajas + ' caja(s)';
    if ((parseInt(p.unidades_intermedias, 10) || 0) > 0 && (parseFloat(p.valor_intermedio) || 0) > 0) {
        var porIntermedio = parseInt(p.unidades_por_intermedio, 10) || 1;
        var intermedios = Math.floor(resto / porIntermedio);
        var minimas = resto % porIntermedio;
        if (intermedios > 0) texto += ' + ' + intermedios + ' ' + nombreNivelIntermedio(p) + '(es)';
        if (minimas > 0) texto += ' + ' + minimas + ' ' + etiquetaUnidadMinima(p) + '(s)';
    } else if (resto > 0) {
        texto += ' + ' + resto + ' ' + etiquetaUnidadMinima(p) + '(s)';
    }
    return texto;
}

// Nombre de la unidad mínima que se vende suelta (pastilla, sobre, unidad...).
// Se deduce de la presentación del producto ("Caja x 50 sobres x 10 pastillas").
function etiquetaUnidadMinima(p) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') return escHtml2(p.unidad_medida || 'und');
    if ((parseInt(p.unidades_intermedias, 10) || 0) > 0) {
        var m = /x\s*\d+\s+[^\s]+\s+x\s*\d+\s+([^\s(]+)/i.exec(p.descripcion || '');
        if (m) return m[1].toLowerCase().replace(/s$/, '');
        return 'pastilla';
    }
    if ((parseInt(p.cantidad_por_unidad, 10) || 1) > 1) {
        // "Caja x 12 sobres de café" -> sobre; sin nombre se usa "unidad"
        var m2 = /x\s*\d+\s+([^\s(]+)/i.exec(p.descripcion || '');
        if (m2 && !/^\d+$/.test(m2[1])) return m2[1].toLowerCase().replace(/s$/, '');
        return 'unidad';
    }
    return 'unidad';
}
function textoStockProducto(p, stock) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') {
        return stock.toFixed(2) + ' ' + (p.unidad_medida || 'und');
    }
    var intermedios = parseInt(p.unidades_intermedias, 10) || 0;
    if (intermedios > 0) {
        var porIntermedio = parseInt(p.unidades_por_intermedio, 10) || 1;
        var total = stock;
        var sobres = porIntermedio > 0 ? Math.floor(total / porIntermedio) : 0;
        return total + ' und mín. (' + sobres + ' ' + (p.nombre_intermedio || 'nivel') + '(s) + ' + (total % porIntermedio) + ')';
    }
    return stock + ' und internas';
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
                var stockVendible = parseInt(p.stock_vendible, 10) || 0;
                var stockClass, stockIcon, stockLabel;

                if (stockVendible <= 0) {
                    stockClass = 'text-danger fw-bold';
                    stockIcon = '<i class="fas fa-times-circle text-danger me-1"></i>';
                    stockLabel = 'AGOTADO';
                } else if (stockVendible <= (parseInt(p.stock_minimo, 10) || 0)) {
                    stockClass = 'text-warning fw-bold';
                    stockIcon = '<i class="fas fa-exclamation-triangle text-warning me-1"></i>';
                    stockLabel = textoStockProducto(p, stockVendible);
                } else {
                    stockClass = 'text-success';
                    stockIcon = '<i class="fas fa-check-circle text-success me-1"></i>';
                    stockLabel = textoStockProducto(p, stockVendible);
                }

                var disabled = stockVendible <= 0 ? 'disabled' : '';
                var fracLabel = '';
                if (esDecimal) {
                    fracLabel = '<i class="fas fa-balance-scale text-warning ms-1" title="Venta por fracción decimal (' + (p.unidad_medida || 'und') + ')"></i>';
                } else if (parseInt(p.unidades_intermedias, 10) > 0) {
                    fracLabel = '<i class="fas fa-layer-group text-info ms-1" title="Caja de ' + p.unidades_intermedias + ' ' + escHtml2(p.nombre_intermedio || 'nivel') + '(s) × ' + p.unidades_por_intermedio + ' = ' + p.unidad_cerrada + ' unidades mínimas"></i>';
                } else if (parseInt(p.unidad_cerrada, 10) > 1) {
                    fracLabel = '<i class="fas fa-cubes text-info ms-1" title="Caja de ' + p.unidad_cerrada + ' unidades internas' + (parseInt(p.fraccion, 10) > 0 ? ', se venden sueltas' : '') + '"></i>';
                } else {
                    fracLabel = '<i class="fas fa-cube text-secondary ms-1" title="Venta por unidad"></i>';
                }
                html += '<tr class="' + (stockVendible <= 0 ? 'opacity-50' : '') + '">' +
                    '<td><small class="text-muted">' + escHtml2(p.codigo) + '</small></td>' +
                    '<td><strong>' + escHtml2(p.descripcion) + '</strong>' + fracLabel +
                    (p.presentacion ? '<br><small class="text-muted">' + escHtml2(p.presentacion) + '</small>' : '') + '</td>' +
                    '<td class="fw-bold">' + etiquetaPrecio(p) + '</td>' +
                    '<td><span class="' + stockClass + '" style="font-size:12px">' + stockIcon + stockLabel + '</span></td>' +
                    '<td><button class="btn btn-sm btn-outline-primary" ' + disabled +
                    ' onclick="agregarAlCarrito(' + p.id_producto + ',\'' + escHtml2(p.descripcion).replace(/'/g,"\\'") + '\',\'' + escHtml2(p.codigo) + '\',' + (p.valor_venta||0) + ',' + (p.iva_porcentaje||0) + ',' + (p.fraccion||0) + ',' + (p.valor_unidad||0) + ',' + stockVendible + ',\'' + (p.tipo_venta||'UNIDAD') + '\',\'' + escHtml2(p.unidad_medida||'') + '\',' + (p.cantidad_por_unidad||1) + ',' + (p.valor_intermedio||0) + ',\'' + escHtml2(p.nombre_intermedio||'') + '\',' + (p.unidades_por_intermedio||1) + ',' + (p.unidades_intermedias||0) + ')" title="Agregar al carrito">' +
                    '<i class="fas fa-cart-plus"></i></button></td></tr>';
            });
        }        $('#tbodyProductosPos').html(html);
        $('#infoProdPag').text('PÃ¡g ' + d.page + ' de ' + d.totalPages + ' (' + d.total + ' prod.)');
        $('#infoProdPagBottom').text('PÃ¡gina ' + d.page + ' de ' + d.totalPages + ' â€” ' + d.total + ' producto(s)');

        // PaginaciÃ³n
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
function agregarAlCarrito(id, nombre, codigo, precio, iva, fraccion, valorUnd, stockDisponible, tipoVenta, unidadMedida, cantidadPorUnidad, valorIntermedio, nombreIntermedio, unidadesPorIntermedio, unidadesIntermedias) {
    stockDisponible = parseInt(stockDisponible, 10) || 0;
    tipoVenta = tipoVenta || 'UNIDAD';
    unidadMedida = unidadMedida || '';
    cantidadPorUnidad = parseFloat(cantidadPorUnidad) || 1;
    valorUnd = parseFloat(valorUnd) || 0;
    valorIntermedio = parseFloat(valorIntermedio) || 0;
    nombreIntermedio = nombreIntermedio || '';
    unidadesPorIntermedio = parseInt(unidadesPorIntermedio, 10) || 1;
    unidadesIntermedias = parseInt(unidadesIntermedias, 10) || 0;
    // La equivalencia de la caja se envía como unidades_intermedias ×
    // unidades_por_intermedio, así que con nivel intermedio se deduce de ahí.
    if (unidadesIntermedias > 0) {
        cantidadPorUnidad = unidadesIntermedias * unidadesPorIntermedio;
    }

    var existente = carrito.findIndex(function(p) { return p.id_producto === id; });

    // La línea ya está en el carrito: se conserva su configuración (la caja
    // pudo haberse abierto) y solo se suma una caja o un sobre.
    if (existente >= 0 && tipoVenta !== 'FRACCION_DECIMAL') {
        return sumarUnaCaja(carrito[existente].idCarrito);
    }

    if (tipoVenta === 'FRACCION_DECIMAL') {
        if (existente >= 0) {
            var itemDec = carrito[existente];
            if (unidadesInternasLinea(itemDec) + 0.5 > stockDisponible) {
                PNotify.error({ text: 'Stock insuficiente. Disponible: ' + stockDisponible + ' ' + (unidadMedida || 'unidades') });
            } else {
                itemDec.cantidad_decimal = (itemDec.cantidad_decimal || 0) + 0.5;
                itemDec.total = itemDec.cantidad_decimal * itemDec.precio_unitario;
            }
        } else {
            idCounter++;
            carrito.push({
                idCarrito: idCounter,
                id_producto: id,
                descripcion: nombre,
                codigo: codigo,
                precio_unitario: precio,
                valor_unidad: valorUnd,
                iva: iva || 0,
                fraccion: 0,
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
        var porIntermedio = unidadesPorIntermedio || 1;
        // La línea nace como una caja cerrada; los campos de sobre y unidad
        // se editan directamente en el carrito según lo que permita el producto.
        var inicio = cantidadPorUnidad > 1
            ? { und: 1, inter: 0, frac: 0 }
            : { und: 0, inter: 0, frac: 1 };

        if (unidadesInternasLinea(inicio) > stockDisponible) {
            PNotify.error({ text: 'Stock insuficiente. Disponible: ' + stockDisponible + ' unidades mínimas' });
            return;
        }
        idCounter++;
        carrito.push({
            idCarrito: idCounter,
            id_producto: id,
            descripcion: nombre,
            codigo: codigo,
            precio_unitario: precio,
            valor_unidad: valorUnd,
            valor_intermedio: valorIntermedio,
            nombre_intermedio: nombreIntermedio,
            unidades_por_intermedio: porIntermedio,
            unidades_intermedias: unidadesIntermedias,
            iva: iva || 0,
            fraccion: parseInt(fraccion, 10) > 0 ? 1 : 0,
            cantidad_unidad: inicio.und,
            cantidad_intermedia: inicio.inter,
            cantidad_fraccion: inicio.frac,
            tipo_venta: tipoVenta,
            unidad_medida: unidadMedida,
            // Equivalencia real de la caja (en 3 niveles cantidad_por_unidad llega en 1)
            cantidad_por_unidad: unidadesIntermedias > 0 ? unidadesIntermedias * porIntermedio : cantidadPorUnidad,
            stock_maximo: stockDisponible,
            total: precio
        });
    }

    $('#buscarProducto').val('').focus();
    $('#resultadosBusqueda').hide();
    actualizarCarrito();
}

// Unidades mínimas que representa una línea del carrito.
function unidadesInternasLinea(p) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') return parseFloat(p.cantidad_decimal) || 0;
    var porCaja = parseFloat(p.cantidad_por_unidad) || 1;
    var porIntermedio = parseFloat(p.unidades_por_intermedio) || 1;
    return ((parseFloat(p.cantidad_unidad) || 0) * porCaja)
        + ((parseFloat(p.cantidad_intermedia) || 0) * porIntermedio)
        + (parseFloat(p.cantidad_fraccion) || 0);
}

// Precio de una línea: cajas a precio de caja, nivel intermedio y unidades mínimas a su precio.
function subtotalLinea(p) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') {
        return (parseFloat(p.cantidad_decimal) || 0) * (parseFloat(p.precio_unitario) || 0);
    }
    var precioInterno = parseFloat(p.valor_unidad) > 0 ? parseFloat(p.valor_unidad) : parseFloat(p.precio_unitario);
    var precioIntermedio = parseFloat(p.valor_intermedio) || 0;
    return ((parseFloat(p.cantidad_unidad) || 0) * (parseFloat(p.precio_unitario) || 0))
        + ((parseFloat(p.cantidad_intermedia) || 0) * precioIntermedio)
        + ((parseFloat(p.cantidad_fraccion) || 0) * precioInterno);
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
            var esDecimal = p.tipo_venta === 'FRACCION_DECIMAL';
            var esFrac = parseInt(p.fraccion, 10) > 0;
            var porCaja = parseFloat(p.cantidad_por_unidad) || 1;
            var sub = subtotalLinea(p);
            var ivaV = sub * (p.iva / 100);
            var tot = sub + ivaV;
            subtotal += sub;
            totalIva += ivaV;
            total += tot;

            var precioLabel = '$' + formatoNumero(p.precio_unitario);
            var inputCajas, inputUnd, inputInter;
            var tieneIntermedio = (parseFloat(p.valor_intermedio) || 0) > 0;
            var conCaja = porCaja > 1;
            var esFrac = parseInt(p.fraccion, 10) > 0;
            var etiquetaInter = escHtml2(p.nombre_intermedio || 'Sobre');
            var etiquetaUnd = etiquetaUnidadMinima(p);
            var campo = 'class="form-control form-control-sm"';
            if (esDecimal) {
                var undLabel = p.unidad_medida ? ' <small class="text-muted">(' + escHtml2(p.unidad_medida) + ')</small>' : '';
                precioLabel += undLabel;
                inputCajas = '<input type="number" ' + campo + ' value="' + (parseFloat(p.cantidad_decimal) || 0).toFixed(4) + '" min="0" step="0.25" onchange="cambiarCantidadDecimal(' + p.idCarrito + ', this.value)">';
                inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="—" disabled>';
                inputInter = '';
            } else {
                precioLabel += conCaja ? ' <small class="text-muted">caja</small>' : ' <small class="text-muted">und</small>';
                if (tieneIntermedio) {
                    precioLabel += '<br><small class="text-muted">' + etiquetaInter + ': $' + formatoNumero(p.valor_intermedio) + '</small>';
                }
                if (conCaja && esFrac && parseInt(p.unidades_por_intermedio, 10) > 1) {
                    precioLabel += '<br><small class="text-muted">' + etiquetaUnd + ': $' + formatoNumero(parseFloat(p.valor_unidad) || p.precio_unitario) + '</small>';
                }
                // Cajas siempre editables; sobre y unidad solo si el producto
                // se puede vender en ese nivel.
                // La caja se guarda en cantidad_unidad y el nivel intermedio en
                // cantidad_intermedia; el inventario cuenta cada uno una sola vez.
                var campoCajas = conCaja ? 'cambiarCantidad(' + p.idCarrito + ', this.value, \'und\')' : 'cambiarCantidad(' + p.idCarrito + ', this.value, \'frac\')';
                inputCajas = '<input type="number" ' + campo + ' value="' + (conCaja
                    ? (parseInt(p.cantidad_unidad, 10) || 0)
                    : (parseInt(p.cantidad_fraccion, 10) || 0)) + '" min="0" onchange="' + campoCajas + '">';
                inputInter = tieneIntermedio
                    ? '<input type="number" ' + campo + ' value="' + (parseInt(p.cantidad_intermedia, 10) || 0) + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'inter\')">'
                    : '<input type="text" class="form-control form-control-sm bg-light text-muted" value="—" disabled>';
                if (!conCaja) {
                    // Producto sin caja: se vende solo por unidades.
                    inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="—" disabled>';
                } else if (esFrac) {
                    inputUnd = '<input type="number" ' + campo + ' value="' + (parseInt(p.cantidad_fraccion, 10) || 0) + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'frac\')">';
                } else {
                    inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="—" disabled title="Este producto solo se vende por caja completa">';
                }
            }

            var avisoStock = '';
            if (p.stock_maximo > 0 && unidadesInternasLinea(p) > p.stock_maximo) {
                avisoStock = '<br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Supera el stock (' + p.stock_maximo + ')</small>';
            }

            // Disponible del producto después de lo que va en la línea
            var restante = Math.max(0, (parseInt(p.stock_maximo, 10) || 0) - unidadesInternasLinea(p));
            var textoRestante = textoDisponibleProducto(p, restante);
            var disponible = '<br><small class="text-muted"><i class="fas fa-warehouse me-1"></i>Quedan: ' + textoRestante + '</small>';

            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td><strong>' + escHtml2(p.descripcion) + '</strong><br><small class="text-muted">' + p.codigo +
                    (conCaja ? ' · ' + textoAbreCaja(p, porCaja) : '') + '</small>' + avisoStock + disponible + '</td>' +
                '<td>' + inputCajas + '</td>' +
                '<td class="col-intermedia' + (tieneIntermedio ? ' nivel-visible' : '') + '">' + (inputInter || '') + '</td>' +
                '<td class="col-unidad' + (conCaja && esFrac && parseInt(p.unidades_por_intermedio, 10) > 1 ? ' nivel-visible' : '') + '">' + (inputUnd || '') + '</td>' +
                '<td>' + precioLabel + '</td>' +
                '<td>' + p.iva + '%</td>' +
                '<td class="fw-bold">$' + formatoNumero(tot) + '</td>' +
                '<td class="opciones-carrito">' +
                    '<div class="d-flex flex-nowrap align-items-center gap-1">' +
                        '<button class="btn btn-sm btn-outline-success" onclick="sumarUnaCaja(' + p.idCarrito + ')" title="Agregar ' + (conCaja ? 'una caja' : 'una unidad') + '"><i class="fas fa-plus"></i></button>' +
                        '<button class="btn btn-sm btn-outline-secondary" onclick="restarUnaUnidad(' + p.idCarrito + ')" title="Quitar una ' + etiquetaMinimaCorta(p) + '"><i class="fas fa-minus"></i></button>' +
                        '<button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(' + p.idCarrito + ')" title="Quitar del carrito"><i class="fas fa-times"></i></button>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        });
    }

    $('#tbodyCarrito').html(html);

    var hayIntermedio = carrito.some(function(p) { return (parseFloat(p.valor_intermedio) || 0) > 0; });
    var nombreIntermedio = '';
    $.each(carrito, function(i, p) {
        if (!nombreIntermedio && (parseFloat(p.valor_intermedio) || 0) > 0) {
            nombreIntermedio = p.nombre_intermedio || 'Sobre';
        }
    });
    $('#thColIntermedia').text(nombreIntermedio || 'Sobre');

    // Encabezados según lo que realmente se vende: cajas, sobres o unidades
    var hayCajas = carrito.some(function(p) { return p.tipo_venta !== 'FRACCION_DECIMAL' && (parseInt(p.cantidad_por_unidad, 10) || 1) > 1; });
    var hayPastillas = carrito.some(function(p) { return (parseFloat(p.valor_intermedio) || 0) > 0; });
    var haySueltas = carrito.some(function(p) {
        return p.tipo_venta !== 'FRACCION_DECIMAL'
            && (parseInt(p.cantidad_por_unidad, 10) || 1) > 1
            && (parseInt(p.fraccion, 10) || 0) > 0;
    });
    // Cajas que se pueden abrir con el botón de la fila (café: caja o sobre)
    var hayCajasAbribles = carrito.some(function(p) {
        return p.tipo_venta !== 'FRACCION_DECIMAL'
            && (parseInt(p.cantidad_por_unidad, 10) || 1) > 1
            && (parseInt(p.fraccion, 10) || 0) > 0
            && (parseFloat(p.valor_intermedio) || 0) <= 0;
    });
    var unidadMinima = '';
    $.each(carrito, function(i, p) {
        if (!unidadMinima) unidadMinima = etiquetaUnidadMinima(p);
    });
    var hayCantidadesSueltas = hayPastillas || haySueltas || hayCajasAbribles;
    $('#thColCajas').text(hayCajas ? 'Cajas' : 'Cant.');
    // El nombre de la columna de unidades sueltas se toma del producto que la usa
    var productoSueltas = null;
    $.each(carrito, function(i, p) {
        if (!productoSueltas && (parseFloat(p.valor_intermedio) || 0) > 0) productoSueltas = p;
    });
    $.each(carrito, function(i, p) {
        if (!productoSueltas && hayCajasAbribles && (parseInt(p.cantidad_por_unidad, 10) || 1) > 1) productoSueltas = p;
    });
    var nombreColUnidades = productoSueltas ? etiquetaUnidadMinima(productoSueltas) : (unidadMinima || 'Und');
    $('#thColUnidades').text(nombreColUnidades.charAt(0).toUpperCase() + nombreColUnidades.slice(1));
    // Las columnas de nivel intermedio y unidades sueltas se muestran siempre
    // (con "—" cuando el producto no usa ese nivel) para que la tabla no cambie
    // de ancho y los botones de Opciones queden alineados.
    $('#thColIntermedia').removeClass('d-none');
    $('#thColUnidades').removeClass('d-none');

    var descuento = parseFloat($('#descuentoValor').val()) || 0;
    var totalFinal = total - descuento;
    
    $('#lblSubtotal').text('$' + formatoNumero(subtotal));
    $('#lblIva').text('$' + formatoNumero(totalIva));
    $('#lblTotal').text('$' + formatoNumero(totalFinal > 0 ? totalFinal : 0));

    calcularCambio();
}

function cambiarCantidad(id, val, tipo) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item) return;
    val = parseInt(val, 10) || 0;
    if (val < 0) val = 0;

    var maxStock = parseInt(item.stock_maximo, 10) || 0;

    // Cajas, nivel intermedio y unidades mínimas se editan libremente
    if (tipo === 'und') {
        item.cantidad_unidad = val;
    } else if (tipo === 'inter') {
        item.cantidad_intermedia = val;
    } else {
        item.cantidad_fraccion = val;
    }

    if (unidadesInternasLinea(item) > maxStock) {
        PNotify.error({ text: 'Stock insuficiente. Disponible: ' + maxStock + ' unidades mínimas' });
        if (tipo === 'und') {
            item.cantidad_unidad = Math.max(0, val - 1);
        } else if (tipo === 'inter') {
            item.cantidad_intermedia = Math.max(0, val - 1);
        } else {
            item.cantidad_fraccion = Math.max(0, val - 1);
        }
    }

    actualizarCarrito();
}

function cambiarCantidadDecimal(id, val) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item) return;
    val = parseFloat(val) || 0;
    if (val < 0) val = 0;
    var maxStock = parseFloat(item.stock_maximo) || 0;
    if (maxStock > 0 && val > maxStock) {
        PNotify.error({ text: 'Stock insuficiente. Disponible: ' + maxStock + ' ' + (item.unidad_medida || 'unidades') });
        val = maxStock;
    }
    item.cantidad_decimal = val;
    actualizarCarrito();
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(function(p) { return p.idCarrito !== id; });
    actualizarCarrito();
}

// Abre una caja cerrada para vender sus unidades sueltas.
// La caja se cambia por sus niveles: 1 caja -> 50 sobres o 12 sobres.
// Si la caja tiene nivel intermedio (caja -> sobre -> pastilla) las unidades
// se reparten entre sobres y pastillas; si no, quedan como unidades sueltas.
function abrirCaja(id) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item || item.tipo_venta === 'FRACCION_DECIMAL') return;

    var porCaja = parseInt(item.cantidad_por_unidad, 10) || 1;
    if (porCaja <= 1) return;

    // Todas las cajas del carrito se convierten en unidades sueltas
    var unidades = (parseInt(item.cantidad_unidad, 10) || 0) * porCaja;
    if (unidades <= 0) {
        PNotify.error({ text: 'Indique al menos una caja en el campo Cajas para abrirla.' });
        return;
    }

    item.fraccion = 1;
    item.cantidad_unidad = 0;
    var texto = unidades + ' ' + etiquetaMinimaCorta(item) + 's';
    if (tieneNivelIntermedio(item)) {
        var porIntermedio = parseInt(item.unidades_por_intermedio, 10) || 1;
        item.cantidad_intermedia = Math.floor(unidades / porIntermedio);
        item.cantidad_fraccion = unidades % porIntermedio;
        texto = item.cantidad_intermedia + ' ' + nombreNivelIntermedio(item) + '(es)'
            + (item.cantidad_fraccion > 0 ? ' + ' + item.cantidad_fraccion + ' ' + etiquetaMinimaCorta(item) + '(s)' : '');
    } else {
        item.cantidad_intermedia = 0;
        item.cantidad_fraccion = unidades;
    }

    PNotify.success({ text: 'Caja abierta: ' + texto + ' disponibles en el carrito' });
    actualizarCarrito();
}

// Cierra las unidades sueltas del carrito y las vuelve a empacar en cajas.
// Lo que no complete una caja se puede dejar como sobre o unidad suelta.
function cerrarCaja(id) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item || item.tipo_venta === 'FRACCION_DECIMAL') return;

    var porCaja = parseInt(item.cantidad_por_unidad, 10) || 1;
    if (porCaja <= 1) return;

    var unidades = unidadesInternasLinea(item);
    if (unidades <= 0) return;

    var cajas = Math.floor(unidades / porCaja);
    var sobrantes = unidades % porCaja;
    item.cantidad_unidad = cajas;
    var texto = cajas + ' caja(s)';
    if (tieneNivelIntermedio(item)) {
        var porIntermedio = parseInt(item.unidades_por_intermedio, 10) || 1;
        item.cantidad_intermedia = Math.floor(sobrantes / porIntermedio);
        item.cantidad_fraccion = sobrantes % porIntermedio;
        if (item.cantidad_intermedia > 0) texto += ' + ' + item.cantidad_intermedia + ' ' + nombreNivelIntermedio(item) + '(es)';
        if (item.cantidad_fraccion > 0) texto += ' + ' + item.cantidad_fraccion + ' ' + etiquetaMinimaCorta(item) + '(es)';
    } else {
        item.cantidad_intermedia = 0;
        item.cantidad_fraccion = sobrantes;
        if (sobrantes > 0) texto += ' + ' + sobrantes + ' ' + etiquetaMinimaCorta(item) + '(s) sueltas';
    }

    PNotify.success({ text: 'Empacado: ' + texto });
    actualizarCarrito();
}

// Agrega una caja (o una unidad si el producto no tiene caja)
function sumarUnaCaja(id) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item) return;

    if (item.tipo_venta === 'FRACCION_DECIMAL') {
        item.cantidad_decimal = (parseFloat(item.cantidad_decimal) || 0) + 0.5;
    } else if ((parseInt(item.cantidad_por_unidad, 10) || 1) > 1) {
        item.cantidad_unidad = (parseInt(item.cantidad_unidad, 10) || 0) + 1;
    } else {
        item.cantidad_fraccion = (parseInt(item.cantidad_fraccion, 10) || 0) + 1;
    }

    var maxStock = parseInt(item.stock_maximo, 10) || 0;
    if (unidadesInternasLinea(item) > maxStock) {
        if (item.tipo_venta === 'FRACCION_DECIMAL') {
            item.cantidad_decimal = Math.max(0, (parseFloat(item.cantidad_decimal) || 0) - 0.5);
        } else if ((parseInt(item.cantidad_por_unidad, 10) || 1) > 1) {
            item.cantidad_unidad = Math.max(0, (parseInt(item.cantidad_unidad, 10) || 0) - 1);
        } else {
            item.cantidad_fraccion = Math.max(0, (parseInt(item.cantidad_fraccion, 10) || 0) - 1);
        }
        PNotify.error({ text: 'Stock insuficiente. Disponible: ' + maxStock + ' unidades mínimas' });
    }

    actualizarCarrito();
}

// Quita una unidad mínima de la línea (pastilla, sobre, unidad o caja suelta)
function restarUnaUnidad(id) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item) return;

    if (item.tipo_venta === 'FRACCION_DECIMAL') {
        item.cantidad_decimal = Math.max(0, (parseFloat(item.cantidad_decimal) || 0) - 0.25);
    } else if ((item.cantidad_unidad || 0) > 0) {
        // Primero se quitan cajas (de mayor a menor)
        item.cantidad_unidad--;
    } else if ((item.cantidad_intermedia || 0) > 0) {
        item.cantidad_intermedia--;
    } else if ((item.cantidad_fraccion || 0) > 0) {
        item.cantidad_fraccion--;
    }

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
    if (falta <= 0) { PNotify.info({ text: 'La venta ya estÃ¡ cubierta' }); return; }

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

    if (falta > 0.009 && $('#ventaCredito').is(':checked')) {
        $('#lblFaltaTxt').text('Saldo a crÃ©dito');
        $('#lblFalta').removeClass('text-success text-danger').addClass('text-warning').text('$' + formatoNumero(falta));
    } else if (falta > 0.009) {
        $('#lblFaltaTxt').text('Falta');
        $('#lblFalta').removeClass('text-success text-warning').addClass('text-danger').text('$' + formatoNumero(falta));
    } else if (falta < -0.009) {
        $('#lblFaltaTxt').text('Cambio');
        $('#lblFalta').removeClass('text-danger text-warning').addClass('text-success').text('$' + formatoNumero(Math.abs(falta)));
    } else {
        $('#lblFaltaTxt').text('Falta');
        $('#lblFalta').removeClass('text-danger text-warning').addClass('text-success').text('$0');
    }
}

function limpiarPagos() {
    pagos = [];
    $('#pagoMonto').val('');
    calcularCambio();
}

// ===== CLIENTE =====
var clientePOSSeleccionado = false;

function cambiarModoCredito() {
    $('#fechaCreditoWrap').toggle($('#ventaCredito').is(':checked'));
    if ($('#ventaCredito').is(':checked')
        && (!clientePOSSeleccionado || parseInt($('#id_cliente').val(), 10) === 1)) {
        PNotify.info({ text: 'Selecciona un cliente distinto de CLIENTE GENERAL para financiar la venta.' });
    }
    calcularCambio();
}

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
    clientePOSSeleccionado = true;
    $('#clienteNombre').text(nombre);
    $('#clienteDoc').text(tipoDoc + ': ' + doc);
    $('#busquedaCliente').hide();
    $('#resultadosClientes').hide();
    $('#buscarCliente').val('');
    calcularCambio();
}

// ===== VENDEDOR DE LA VENTA =====
// Por defecto la venta queda a nombre del usuario logueado (caja).
// Opcionalmente se puede asignar a un vendedor registrado.
var usuarioCajaNombre = <?= json_encode($usuarioNombre ?? 'Usuario') ?>;
var timerVendPos = null;

function toggleVendedorVenta() {
    // Si ya estÃ¡ abierto, cerrar
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
                    ' <small class="text-muted">A· Comisión ' + com + '%</small></button>';
            });
            $('#resultadosVendedores').html(html).show();
        });
    }, 300);
});

function seleccionarVendedor(id, nombre, codigo, cedula, comision) {
    $('#id_vendedor_registrado').val(id);
    $('#vendedorNombre').text(nombre);
    $('#vendedorDetalle').text(codigo + (cedula ? ' A· CC: ' + cedula : '') + ' A· Comisión ' + comision + '%');
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
    if (carrito.length === 0) { PNotify.error({ text: 'Carrito vacÃ­o' }); return; }

    // Validar stock de todos los productos antes de enviar
    for (var i = 0; i < carrito.length; i++) {
        var p = carrito[i];
        var maxStock = parseInt(p.stock_maximo, 10) || 0;
        var esDecimal = p.tipo_venta === 'FRACCION_DECIMAL';
        var unidades = unidadesInternasLinea(p);

        if (unidades <= 0) {
            PNotify.error({ text: 'Indique la cantidad a vender de ' + p.descripcion });
            return;
        }
        if (maxStock > 0 && unidades > maxStock) {
            PNotify.error({ text: 'Stock insuficiente de ' + p.descripcion + '. Disponible: ' + maxStock + (esDecimal ? ' ' + (p.unidad_medida || 'unidades') : ' unidades internas') });
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
            cantidad_intermedia: p.cantidad_intermedia || 0,
            precio_unitario: p.precio_unitario,
            iva: p.iva,
            fraccion: p.fraccion
        };
        if (p.tipo_venta === 'FRACCION_DECIMAL') {
            det.cantidad_decimal = parseFloat(p.cantidad_decimal) || 0;
            det.cantidad_unidad = 0;
            det.cantidad_fraccion = 0;
            det.cantidad_intermedia = 0;
        }
        detalles.push(det);
    });

    var totalPagar = totalAPagar();
    var ventaCredito = $('#ventaCredito').is(':checked');

    if (ventaCredito && (!clientePOSSeleccionado || parseInt($('#id_cliente').val(), 10) === 1)) {
        PNotify.error({ text: 'Selecciona un cliente activo distinto de CLIENTE GENERAL para vender a crÃ©dito.' });
        return;
    }

    // Si no agregaron pagos, se cobra todo con el mÃ©todo seleccionado
    if (!pagos.length && !ventaCredito) {
        pagos.push({ metodo: metodoSeleccionado(), monto: totalPagar });
        calcularCambio();
    }

    if (!ventaCredito && totalPagado() + 0.009 < totalPagar) {
        PNotify.error({ text: 'Faltan $' + formatoNumero(totalPagar - totalPagado()) + ' por cubrir' });
        return;
    }

    var data = {
        id_cliente: parseInt($('#id_cliente').val()) || 1,
        tipo_pago: ventaCredito ? 'CREDITO' : metodoSeleccionado(),
        venta_credito: ventaCredito,
        fecha_vencimiento: ventaCredito ? ($('#fechaVencimientoCredito').val() || null) : null,
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
                $('#ventaCredito').prop('checked', false);
                $('#fechaVencimientoCredito').val('');
                $('#fechaCreditoWrap').hide();
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

// ===== ENVÃO A DIAN (Factura Electrónica) =====
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
                    text: 'âœ… Factura Electrónica #' + codigo + ' emitida exitosamente' + (r.data?.cufe ? ' - CUFE generado' : '')
                });
                var params = '?factura=' + idFactura;
                if (r.data?.cufe) params += '&cufe=' + encodeURIComponent(r.data.cufe);
                if (r.data?.qr) params += '&qr=' + encodeURIComponent(r.data.qr);
                window.open(BASE_URL+'/facturacion/pdf/' + idFactura, '_blank');
            } else {
                PNotify.error({ 
                    text: 'âŒ ' + (r.message || 'Error al emitir factura Electrónica'),
                    hide: false
                });
            }
        },
        error: function() {
            PNotify.error({ text: 'Error de conexiÃ³n con la DIAN', hide: false });
        }
    });
}

// ===== TECLAS RÃPIDAS =====
$(document).on('keydown', function(e) {
    if (e.key === 'F1') { e.preventDefault(); $('#buscarProducto').focus().select(); }
    if (e.key === 'F2') { e.preventDefault(); facturar('NORMAL'); }
    if (e.key === 'F3') { e.preventDefault(); facturar('ELECTRONICA'); }
    if (e.key === 'F4') { e.preventDefault(); $('#pagoMonto').focus().select(); }
    if (e.key === 'Escape') { carrito = []; actualizarCarrito(); }
    if (e.key === 'F8') { cambiarCliente(); }
    if (e.key === 'F9') { e.preventDefault(); toggleVendedorVenta(); }
});

// Recargar productos despuÃ©s de facturar
function recargarVista() {
    cargarProductosPos();
    $('#buscarProducto').focus();
}

// Al inicio, cargar productos y enfocar bÃºsqueda
$(function() {
    cargarProductosPos();
    $('#buscarProducto').focus();
});

// Evitar submit con Enter en el input de bÃºsqueda que agregue producto
$('#buscarProducto').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        var q = $(this).val().trim();
        if (q.length > 0) {
            // Forzar bÃºsqueda y seleccionar primer resultado
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
/* Los botones +, - y × siempre en una sola línea, sin importar el producto */
#tablaCarrito td.opciones-carrito {
    white-space: nowrap;
}
#tablaCarrito td.opciones-carrito .btn {
    flex: 0 0 auto;
    padding: 0.2rem 0.45rem;
}
/* Las columnas de nivel se mantienen aunque el producto no use ese nivel:
   así la tabla no cambia de ancho y los botones quedan alineados. */
#tablaCarrito td.col-intermedia,
#tablaCarrito td.col-unidad {
    display: table-cell !important;
}
#tablaCarrito td.col-intermedia:not(.nivel-visible),
#tablaCarrito td.col-unidad:not(.nivel-visible) {
    visibility: hidden;
}
</style>
