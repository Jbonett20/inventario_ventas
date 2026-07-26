<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-hand-holding-heart text-primary me-2"></i>Plan Separe</h4>
                <p class="text-muted mb-0">Gestión de apartados y reservas de productos</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#modalAbonos" id="btnHistorialAbonos" style="display:none">
                    <i class="fas fa-history me-1"></i>Historial Abonos
                </button>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalPlanSepare">
                    <i class="fas fa-plus me-2"></i>Nuevo Plan Separe
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Abonado</th>
                        <th>Saldo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyPlanSepare">
                    <tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagPS"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagPS"></ul></nav>
</div>

<!-- Modal Nuevo Plan Separe -->
<div class="modal fade" id="modalPlanSepare" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-heart me-2"></i>Nuevo Plan Separe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPlanSepare">
                <div class="modal-body">
                    <!-- Datos del cliente -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_cliente" required>
                                <option value="">Seleccione un cliente...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buscador de productos -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body py-3">
                            <label class="form-label fw-semibold"><i class="fas fa-search me-1"></i>Agregar Producto</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="ps_buscar_producto" placeholder="Buscar por nombre, código o código de barras..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="ps_btn_buscar"><i class="fas fa-search"></i></button>
                            </div>
                            <div class="mt-2" id="ps_resultados_busqueda" style="display:none"></div>
                        </div>
                    </div>

                    <!-- Tabla de productos seleccionados -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold"><i class="fas fa-box me-1"></i>Productos a separar</span>
                            <span class="badge bg-primary" id="ps_total_productos">0 productos</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr><th>Código</th><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">V.Unitario</th><th class="text-end">Subtotal</th><th class="text-center"></th></tr>
                                    </thead>
                                    <tbody id="ps_tbody_detalle">
                                        <tr id="ps_sin_productos"><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-box-open me-1"></i>Agregue productos al plan separe</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Totales y abono -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Subtotal</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control fw-bold" id="ps_subtotal" value="0" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valor Total <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control fw-bold" name="valor_total" id="ps_valor_total" step="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Abono Inicial</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor_inicial" id="ps_valor_inicial" value="0" step="1" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar Plan Separe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Productos del Plan -->
<div class="modal fade" id="modalVerProductos" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-box me-2"></i>Productos del Plan Separe</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold" id="vp_info"></p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr><th>Código</th><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">V.Unitario</th><th class="text-end">Subtotal</th></tr>
                        </thead>
                        <tbody id="vp_tbody">
                            <tr><td colspan="5" class="text-center py-3 text-muted">Sin productos</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Abono -->
<div class="modal fade" id="modalAbono" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-usd me-2"></i>Registrar Abono</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAbono">
                <input type="hidden" name="id_plan_separe" id="ab_id">
                <div class="modal-body">
                    <p class="fw-bold" id="ab_info"></p>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" name="valor" id="ab_valor" step="1" min="100" required autofocus>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check me-2"></i>Abonar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historial de Abonos -->
<div class="modal fade" id="modalAbonos" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i>Historial de Abonos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold" id="abonos_info"></p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr><th>#</th><th>Fecha</th><th>Valor</th><th>Registrado por</th></tr>
                        </thead>
                        <tbody id="tbodyAbonos">
                            <tr><td colspan="4" class="text-center py-3 text-muted">Sin abonos registrados</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }

let paginaActual = 1;

function cargarPlanSepare(page) {
    page = page || 1;
    paginaActual = page;
    $('#tbodyPlanSepare').html('<tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');

    $.getJSON(BASE_URL + '/plan-separe/listar', {page}, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        if (!d.data || !d.data.length) {
            html = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay planes separe</td></tr>';
        } else {
            $.each(d.data, function(i, ps) {
                var estClass = ps.estado === 'ACTIVO' ? 'warning' : (ps.estado === 'COMPLETADO' ? 'success' : 'danger');
                var estIcon  = ps.estado === 'ACTIVO' ? 'fa-clock' : (ps.estado === 'COMPLETADO' ? 'fa-check-circle' : 'fa-times-circle');
                html += '<tr>' +
                    '<td><strong>' + escHtml(ps.codigo) + '</strong></td>' +
                    '<td>' + escHtml(ps.cliente_nombre) + '<br><small class="text-muted">' + escHtml(ps.cliente_documento) + '</small></td>' +
                    '<td class="fw-bold">$' + formatoNumero(ps.valor_total) + '</td>' +
                    '<td class="text-success fw-bold">$' + formatoNumero(ps.valor_abonado) + '</td>' +
                    '<td class="fw-bold ' + (ps.saldo_pendiente > 0 ? 'text-danger' : 'text-success') + '">$' + formatoNumero(ps.saldo_pendiente) + '</td>' +
                    '<td>' + ps.fecha_inicio + '</td>' +
                    '<td>' + (ps.fecha_fin || '-') + '</td>' +
                    '<td><span class="badge bg-' + estClass + '"><i class="fas ' + estIcon + ' me-1"></i>' + ps.estado + '</span></td>' +
                    '<td class="text-nowrap">' +
                        (ps.estado === 'ACTIVO' ?
                            '<button class="btn btn-sm btn-success me-1" onclick="abrirAbono(' + ps.id_plan_separe + ',\'' + escHtml(ps.codigo) + '\',' + ps.saldo_pendiente + ')" title="Abonar"><i class="fas fa-hand-holding-usd"></i></button>' +
                            '<button class="btn btn-sm btn-danger" onclick="cancelarPS(' + ps.id_plan_separe + ')" title="Cancelar"><i class="fas fa-times"></i></button>'
                            : ''
                        ) +
                        '<button class="btn btn-sm btn-info ms-1" onclick="verProductos(' + ps.id_plan_separe + ', \'' + escHtml(ps.codigo) + '\')" title="Ver productos"><i class="fas fa-box"></i></button>' +
                        '<button class="btn btn-sm btn-secondary ms-1" onclick="verAbonos(' + ps.id_plan_separe + ',\'' + escHtml(ps.codigo) + '\',\'' + escHtml(ps.cliente_nombre) + '\')" title="Ver abonos"><i class="fas fa-list"></i></button>' +
                    '</td></tr>';
            });
        }
        $('#tbodyPlanSepare').html(html);
        $('#infoPagPS').text((d.data ? d.data.length : 0) + ' de ' + d.total);

        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) {
            ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '">' +
                  '<a class="page-link" href="#" onclick="cargarPlanSepare(' + i + ');return false;">' + i + '</a></li>';
        }
        $('#pagPS').html(ph);
    });
}

// Cargar clientes en el select
function cargarClientesSelect() {
    var $sel = $('#formPlanSepare select[name="id_cliente"]');
    if ($sel.find('option').length > 1) return;
    $.getJSON(BASE_URL + '/clientes/listar?page=1&perPage=200', function(r) {
        if (!r.success || !r.data.data) return;
        var opts = '<option value="">Seleccione un cliente...</option>';
        $.each(r.data.data, function(i, c) {
            opts += '<option value="' + c.id_cliente + '">' + escHtml(c.nombre) + ' (' + c.documento + ')</option>';
        });
        $sel.html(opts);
    });
}

// ===== GESTIÓN DE PRODUCTOS EN EL MODAL =====
var psDetalles = [];

function psAgregarProducto(id, codigo, descripcion, precio, stock) {
    // Verificar si ya existe
    for (var i = 0; i < psDetalles.length; i++) {
        if (psDetalles[i].id_producto === id) {
            PNotify.info({text: 'El producto ya está en la lista'});
            return;
        }
    }

    psDetalles.push({
        id_producto: id,
        codigo: codigo,
        descripcion: descripcion,
        cantidad: 1,
        valor_unitario: precio,
        subtotal: precio
    });

    psRenderDetalle();
    $('#ps_resultados_busqueda').hide().html('');
    $('#ps_buscar_producto').val('').focus();
}

function psEliminarProducto(index) {
    psDetalles.splice(index, 1);
    psRenderDetalle();
}

function psCambiarCantidad(index, cantidad) {
    cantidad = parseInt(cantidad) || 1;
    if (cantidad < 1) cantidad = 1;
    psDetalles[index].cantidad = cantidad;
    psDetalles[index].subtotal = cantidad * psDetalles[index].valor_unitario;
    psRenderDetalle();
}

function psCambiarPrecio(index, precio) {
    precio = parseFloat(precio) || 0;
    if (precio < 0) precio = 0;
    psDetalles[index].valor_unitario = precio;
    psDetalles[index].subtotal = psDetalles[index].cantidad * precio;
    psRenderDetalle();
}

function psRenderDetalle() {
    var html = '';
    var subtotal = 0;
    var totalItems = 0;

    if (!psDetalles.length) {
        html = '<tr id="ps_sin_productos"><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-box-open me-1"></i>Agregue productos al plan separe</td></tr>';
    } else {
        $.each(psDetalles, function(i, p) {
            subtotal += p.subtotal;
            totalItems += p.cantidad;
            html += '<tr>' +
                '<td><small>' + escHtml(p.codigo) + '</small></td>' +
                '<td><small>' + escHtml(p.descripcion) + '</small></td>' +
                '<td class="text-center" style="width:90px">' +
                    '<div class="input-group input-group-sm">' +
                        '<button class="btn btn-outline-secondary btn-sm" type="button" onclick="psCambiarCantidad(' + i + ',' + (p.cantidad - 1) + ')">-</button>' +
                        '<input type="number" class="form-control text-center" value="' + p.cantidad + '" min="1" style="width:45px" onchange="psCambiarCantidad(' + i + ', this.value)">' +
                        '<button class="btn btn-outline-secondary btn-sm" type="button" onclick="psCambiarCantidad(' + i + ',' + (p.cantidad + 1) + ')">+</button>' +
                    '</div>' +
                '</td>' +
                '<td class="text-end" style="width:130px">' +
                    '<div class="input-group input-group-sm">' +
                        '<span class="input-group-text">$</span>' +
                        '<input type="number" class="form-control text-end" value="' + p.valor_unitario + '" min="0" step="1" style="width:80px" onchange="psCambiarPrecio(' + i + ', this.value)">' +
                    '</div>' +
                '</td>' +
                '<td class="text-end fw-bold">$' + formatoNumero(p.subtotal) + '</td>' +
                '<td class="text-center">' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="psEliminarProducto(' + i + ')" title="Quitar"><i class="fas fa-times"></i></button>' +
                '</td></tr>';
        });
    }

    $('#ps_tbody_detalle').html(html);
    $('#ps_total_productos').text(totalItems + ' productos');
    $('#ps_subtotal').val(formatoNumero(subtotal));

    // Si no hay productos, poner valor total manual, si hay sugerir subtotal
    if (psDetalles.length > 0) {
        $('#ps_valor_total').val(subtotal);
    }
    psValidarValores();
}

function psValidarValores() {
    var total = parseFloat($('#ps_valor_total').val()) || 0;
    var inicial = parseFloat($('#ps_valor_inicial').val()) || 0;
    if (inicial > total) {
        $('#ps_valor_inicial').val(total);
    }
}

// Buscar productos
$('#ps_btn_buscar, #ps_buscar_producto').on('keypress click', function(e) {
    if (e.type === 'click' || e.which === 13) {
        var q = $('#ps_buscar_producto').val().trim();
        if (q.length < 1) return;
        $.getJSON(BASE_URL + '/plan-separe/buscar-productos', {q: q}, function(r) {
            if (!r.success || !r.data || !r.data.length) {
                $('#ps_resultados_busqueda').html('<div class="text-muted small p-2">No se encontraron productos</div>').show();
                return;
            }
            var html = '<div class="list-group list-group-flush" style="max-height:200px;overflow-y:auto">';
            $.each(r.data, function(i, p) {
                html += '<a href="#" class="list-group-item list-group-item-action py-2 small" onclick="psAgregarProducto(' +
                    p.id_producto + ',\'' + escHtml(p.codigo) + '\',\'' + escHtml(p.descripcion) + '\',' +
                    p.valor_venta + ',' + p.stock + ');return false;">' +
                    '<div class="d-flex justify-content-between align-items-center">' +
                    '<span><strong>' + escHtml(p.codigo) + '</strong> - ' + escHtml(p.descripcion) + '</span>' +
                    '<span class="text-nowrap">$' + formatoNumero(p.valor_venta) + ' | Stock: ' + p.stock + '</span>' +
                    '</div></a>';
            });
            html += '</div>';
            $('#ps_resultados_busqueda').html(html).show();
        });
    }
});

// Validar que abono inicial no supere total
$('#ps_valor_total, #ps_valor_inicial').on('input', function() {
    psValidarValores();
});

// Limpiar resultados al escribir
$('#ps_buscar_producto').on('keyup', function() {
    if ($(this).val().trim().length === 0) {
        $('#ps_resultados_busqueda').hide().html('');
    }
});

// Guardar nuevo plan separe
$('#formPlanSepare').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o, i) { o[i.name] = i.value; return o; }, {});
    data.detalles = psDetalles.map(function(p) {
        return {
            id_producto: p.id_producto,
            cantidad: p.cantidad,
            valor_unitario: p.valor_unitario
        };
    });

    if (!data.detalles.length) {
        PNotify.error({text: 'Debe agregar al menos un producto al plan separe'});
        return;
    }

    $.ajax({
        url: BASE_URL + '/plan-separe/guardar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalPlanSepare').modal('hide');
                $('#formPlanSepare')[0].reset();
                $('#formPlanSepare select[name="id_cliente"]').val('');
                psDetalles = [];
                psRenderDetalle();
                cargarPlanSepare(1);
                PNotify.success({text: r.message});
            }
        },
        error: function(x) {
            var msg = 'Error al crear Plan Separe';
            try { var r = JSON.parse(x.responseText); if (r.message) msg = r.message; } catch(e) {}
            PNotify.error({text: msg});
        }
    });
});

// ===== VER PRODUCTOS DE UN PLAN =====
function verProductos(id, codigo) {
    $('#vp_info').text('Plan Separe: ' + codigo);
    $('#vp_tbody').html('<tr><td colspan="5" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#modalVerProductos').modal('show');

    $.getJSON(BASE_URL + '/plan-separe/detalle/' + id, function(r) {
        if (!r.success || !r.data) {
            $('#vp_tbody').html('<tr><td colspan="5" class="text-center py-3 text-muted">Error al cargar productos</td></tr>');
            return;
        }
        var detalles = r.data.detalles || [];
        if (!detalles.length) {
            $('#vp_tbody').html('<tr><td colspan="5" class="text-center py-3 text-muted">Sin productos registrados</td></tr>');
        } else {
            var html = '';
            $.each(detalles, function(i, d) {
                html += '<tr>' +
                    '<td><small>' + escHtml(d.codigo || '-') + '</small></td>' +
                    '<td>' + escHtml(d.descripcion || 'Producto #' + d.id_producto) + '</td>' +
                    '<td class="text-center">' + d.cantidad + '</td>' +
                    '<td class="text-end">$' + formatoNumero(d.valor_unitario) + '</td>' +
                    '<td class="text-end fw-bold">$' + formatoNumero(d.subtotal) + '</td></tr>';
            });
            $('#vp_tbody').html(html);
        }
    });
}

// Abrir modal de abono
function abrirAbono(id, codigo, saldo) {
    $('#ab_id').val(id);
    $('#ab_info').text(codigo + ' - Saldo: $' + formatoNumero(saldo));
    $('#ab_valor').val(Math.min(1000, saldo));
    $('#modalAbono').modal('show');
}

// Registrar abono
$('#formAbono').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o, i) { o[i.name] = i.value; return o; }, {});
    $.ajax({
        url: BASE_URL + '/plan-separe/abonar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalAbono').modal('hide');
                $('#formAbono')[0].reset();
                cargarPlanSepare(paginaActual);
                PNotify.success({text: r.message});
            }
        },
        error: function(x) {
            var msg = 'Error al registrar abono';
            try { var r = JSON.parse(x.responseText); if (r.message) msg = r.message; } catch(e) {}
            PNotify.error({text: msg});
        }
    });
});

// Cancelar plan separe
function cancelarPS(id) {
    if (!confirm('¿Está seguro de cancelar este Plan Separe?')) return;
    $.ajax({
        url: BASE_URL + '/plan-separe/cancelar',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({id_plan_separe: id}),
        success: function(r) {
            if (r.success) {
                cargarPlanSepare(paginaActual);
                PNotify.success({text: r.message});
            }
        },
        error: function(x) {
            var msg = 'Error al cancelar';
            try { var r = JSON.parse(x.responseText); if (r.message) msg = r.message; } catch(e) {}
            PNotify.error({text: msg});
        }
    });
}

// Ver historial de abonos
function verAbonos(id, codigo, cliente) {
    $('#abonos_info').html('<strong>' + codigo + '</strong> — Cliente: <strong>' + escHtml(cliente || '') + '</strong>');
    $('#tbodyAbonos').html('<tr><td colspan="4" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#btnHistorialAbonos').show();

    $.getJSON(BASE_URL + '/plan-separe/abonos/' + id, function(r) {
        if (!r.success || !r.data) {
            $('#tbodyAbonos').html('<tr><td colspan="4" class="text-center py-3 text-muted">Sin abonos registrados</td></tr>');
        } else if (!r.data.length) {
            $('#tbodyAbonos').html('<tr><td colspan="4" class="text-center py-3 text-muted">Sin abonos registrados</td></tr>');
        } else {
            var html = '';
            $.each(r.data, function(i, a) {
                html += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + a.fecha + '</td>' +
                    '<td class="fw-bold text-success">$' + formatoNumero(a.valor) + '</td>' +
                    '<td>' + escHtml(a.nombre_usuario || '-') + '</td>' +
                    '</tr>';
            });
            $('#tbodyAbonos').html(html);
        }
        $('#modalAbonos').modal('show');
    });
}

$(function() {
    cargarPlanSepare(1);
    cargarClientesSelect();
});
</script>
