var $ = (function(){
    function el(){ var o = {}; ['val','show','hide','text','prop','toggleClass','html','after','on','modal','removeClass','addClass','attr','append','empty','click','focus','ready'].forEach(function(k){ o[k] = function(){ return o; }; }); o.closest = function(){ return o; }; o.find = function(){ return { length: 0 }; }; o.data = function(){ return null; }; o.is = function(){ return false; }; o.each = function(){}; return o; }
    var f = function(){ return el(); };
    f.each = function(arr, fn){ if (arr && arr.length !== undefined) { for (var i = 0; i < arr.length; i++) fn(i, arr[i]); } };
    f.getJSON = function(){};
    return f;
})();
function escHtml2(s){ return String(s == null ? '' : s); }
function formatoNumero(n){ return String(n); }
var PNotify = { error: function(){}, success: function(){}, info: function(){} };
var BASE_URL = '';
var carrito = [];
var idCounter = 0;
var totalGanancia = 0;
function calcularCambio(){}

function etiquetaMinimaCorta(p) {
    return etiquetaUnidadMinima(p);
}

// Nombre de la unidad mÃ­nima que se vende suelta (pastilla, sobre, unidad...).
// Se deduce de la presentaciÃ³n del producto ("Caja x 50 sobres x 10 pastillas").
function etiquetaUnidadMinima(p) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') return escHtml2(p.unidad_medida || 'und');
    if ((parseInt(p.unidades_intermedias, 10) || 0) > 0) {
        var m = /x\s*\d+\s+[^\s]+\s+x\s*\d+\s+([^\s(]+)/i.exec(p.descripcion || '');
        if (m) return m[1].toLowerCase().replace(/s$/, '');
        return 'pastilla';
    }
    if ((parseInt(p.cantidad_por_unidad, 10) || 1) > 1) {
        // "Caja x 12 sobres de cafÃ©" -> sobre; sin nombre se usa "unidad"
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
        return total + ' und mÃ­n. (' + sobres + ' ' + (p.nombre_intermedio || 'nivel') + '(s) + ' + (total % porIntermedio) + ')';
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
                    fracLabel = '<i class="fas fa-balance-scale text-warning ms-1" title="Venta por fracciÃ³n decimal (' + (p.unidad_medida || 'und') + ')"></i>';
                } else if (parseInt(p.unidades_intermedias, 10) > 0) {
                    fracLabel = '<i class="fas fa-layer-group text-info ms-1" title="Caja de ' + p.unidades_intermedias + ' ' + escHtml2(p.nombre_intermedio || 'nivel') + '(s) Ã— ' + p.unidades_por_intermedio + ' = ' + p.unidad_cerrada + ' unidades mÃ­nimas"></i>';
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
        $('#infoProdPag').text('PÃƒÂ¡g ' + d.page + ' de ' + d.totalPages + ' (' + d.total + ' prod.)');
        $('#infoProdPagBottom').text('PÃƒÂ¡gina ' + d.page + ' de ' + d.totalPages + ' Ã¢â‚¬â€ ' + d.total + ' producto(s)');

        // PaginaciÃƒÂ³n
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

    var existente = carrito.findIndex(function(p) { return p.id_producto === id; });

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
        var esFrac = parseInt(fraccion, 10) > 0;
        var porIntermedio = unidadesPorIntermedio || 1;
        // Cada clic agrega una caja. Si la caja no se abre, solo se vende
        // cerrada. Si se abre, la caja se convierte en sus niveles intermedios
        // (sobres) o en sus unidades mÃ­nimas (pastillas).
        var abreCaja = esFrac && cantidadPorUnidad > 1;
        var inicio = !abreCaja
            ? { und: 1, inter: 0, frac: 0 }
            : (unidadesIntermedias > 0 && valorIntermedio > 0)
                ? { und: 0, inter: unidadesIntermedias, frac: 0 }
                : { und: 0, inter: 0, frac: cantidadPorUnidad };

        if (existente >= 0) {
            var item = carrito[existente];
            item.cantidad_unidad = (item.cantidad_unidad || 0) + inicio.und;
            item.cantidad_intermedia = (item.cantidad_intermedia || 0) + inicio.inter;
            item.cantidad_fraccion = (item.cantidad_fraccion || 0) + inicio.frac;
            if (unidadesInternasLinea(item) > stockDisponible) {
                item.cantidad_unidad -= inicio.und;
                item.cantidad_intermedia -= inicio.inter;
                item.cantidad_fraccion -= inicio.frac;
                PNotify.error({ text: 'Stock insuficiente. Disponible: ' + stockDisponible + ' unidades mÃ­nimas' });
            }
        } else {
            if (unidadesInternasLinea(inicio) > stockDisponible) {
                PNotify.error({ text: 'Stock insuficiente. Disponible: ' + stockDisponible + ' unidades mÃ­nimas' });
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
                fraccion: esFrac ? 1 : 0,
                cantidad_unidad: inicio.und,
                cantidad_intermedia: inicio.inter,
                cantidad_fraccion: inicio.frac,
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

// Unidades mÃ­nimas que representa una lÃ­nea del carrito.
function unidadesInternasLinea(p) {
    if (p.tipo_venta === 'FRACCION_DECIMAL') return parseFloat(p.cantidad_decimal) || 0;
    var porCaja = parseFloat(p.cantidad_por_unidad) || 1;
    var porIntermedio = parseFloat(p.unidades_por_intermedio) || 1;
    return ((parseFloat(p.cantidad_unidad) || 0) * porCaja)
        + ((parseFloat(p.cantidad_intermedia) || 0) * porIntermedio)
        + (parseFloat(p.cantidad_fraccion) || 0);
}

// Precio de una lÃ­nea: cajas a precio de caja, nivel intermedio y unidades mÃ­nimas a su precio.
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
            var abreCaja = esFrac && porCaja > 1;
            var etiquetaInter = escHtml2(p.nombre_intermedio || 'Sobre');
            var etiquetaUnd = etiquetaUnidadMinima(p);
            var campo = 'class="form-control form-control-sm"';
            if (esDecimal) {
                var undLabel = p.unidad_medida ? ' <small class="text-muted">(' + escHtml2(p.unidad_medida) + ')</small>' : '';
                precioLabel += undLabel;
                inputCajas = '<input type="number" ' + campo + ' value="' + (parseFloat(p.cantidad_decimal) || 0).toFixed(4) + '" min="0" step="0.25" onchange="cambiarCantidadDecimal(' + p.idCarrito + ', this.value)">';
                inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="â€”" disabled>';
                inputInter = '';
            } else {
                precioLabel += porCaja > 1 ? ' <small class="text-muted">caja</small>' : '';
                if (tieneIntermedio) {
                    precioLabel += '<br><small class="text-muted">' + etiquetaInter + ': $' + formatoNumero(p.valor_intermedio) + '</small>';
                }
                if (porCaja > 1) {
                    precioLabel += '<br><small class="text-muted">' + etiquetaUnd + ': $' + formatoNumero(parseFloat(p.valor_unidad) || p.precio_unitario) + '</small>';
                }
                // Cajas, nivel intermedio y unidades mÃ­nimas se editan libremente
                inputCajas = '<input type="number" ' + campo + ' value="' + (parseInt(p.cantidad_unidad, 10) || 0) + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'und\')">';
                inputInter = tieneIntermedio
                    ? '<input type="number" ' + campo + ' value="' + (parseInt(p.cantidad_intermedia, 10) || 0) + '" min="0" onchange="cambiarCantidad(' + p.idCarrito + ', this.value, \'inter\')">'
                    : '';
                // Con la caja cerrada las unidades sueltas se calculan solas, asÃ­
                // que el campo se muestra bloqueado. Sin caja (unidad simple) el
                // producto se edita en el primer campo para no descontar stock dos veces.
                inputUnd = '';
                if (abreCaja) {
                    inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="' + (parseInt(p.cantidad_fraccion, 10) || 0) + '" disabled title="Se calculan al abrir la caja">';
                } else if (porCaja > 1) {
                    // Caja cerrada: el campo queda bloqueado; para vender sueltas
                    // se usa el botÃ³n de abrir caja de la fila.
                    inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="' + (parseInt(p.cantidad_fraccion, 10) || 0) + '" disabled title="Use el botÃ³n de abrir caja para vender ' + etiquetaUnd + ' sueltos">';
                } else {
                    inputUnd = '<input type="text" class="form-control form-control-sm bg-light text-muted" value="â€”" disabled>';
                }
            }

            var avisoStock = '';
            if (p.stock_maximo > 0 && unidadesInternasLinea(p) > p.stock_maximo) {
                avisoStock = '<br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Supera el stock (' + p.stock_maximo + ')</small>';
            }

            var puedeAbrir = !esDecimal && porCaja > 1 && (parseInt(p.fraccion, 10) || 0) > 0;
            // Con la caja cerrada solo se vende por cajas: se ocultan los campos
            // de nivel intermedio y unidades sueltas (que estÃ¡n en cero).
            var mostrarCantidadesSueltas = !esDecimal && (!puedeAbrir || abreCaja);
            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td><strong>' + escHtml2(p.descripcion) + '</strong><br><small class="text-muted">' + p.codigo + (abreCaja ? ' Â· ' + textoAbreCaja(p, porCaja) : '') + '</small>' + avisoStock + '</td>' +
                '<td>' + inputCajas + '</td>' +
                '<td class="col-intermedia' + (tieneIntermedio && mostrarCantidadesSueltas ? '' : ' d-none') + '">' + (inputInter || '') + '</td>' +
                '<td class="col-unidad' + (mostrarCantidadesSueltas ? '' : ' d-none') + '">' + (inputUnd || '') + '</td>' +
                '<td>' + precioLabel + '</td>' +
                '<td>' + p.iva + '%</td>' +
                '<td class="fw-bold">$' + formatoNumero(tot) + '</td>' +
                '<td class="text-nowrap">' +
                    '<button class="btn btn-sm btn-outline-success me-1" onclick="sumarUnaCaja(' + p.idCarrito + ')" title="Agregar una caja (o unidad)"><i class="fas fa-plus"></i></button>' +
                    '<button class="btn btn-sm btn-outline-secondary me-1" onclick="restarUnaUnidad(' + p.idCarrito + ')" title="Quitar una ' + etiquetaMinimaCorta(p) + '"><i class="fas fa-minus"></i></button>' +
                    ((parseInt(p.fraccion, 10) || 0) > 0 || (parseFloat(p.valor_intermedio) || 0) > 0 || porCaja > 1
                        ? '<button class="btn btn-sm btn-outline-warning me-1" onclick="abrirCaja(' + p.idCarrito + ')" title="Abrir la caja y pasar sus unidades a los campos"><i class="fas fa-box-open"></i></button>' +
                          '<button class="btn btn-sm btn-outline-primary me-1" onclick="cerrarCaja(' + p.idCarrito + ')" title="Empacar las unidades sueltas en cajas"><i class="fas fa-box"></i></button>'
                        : '') +
                    '<button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(' + p.idCarrito + ')" title="Quitar del carrito"><i class="fas fa-times"></i></button>' +
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

    // Encabezados segÃºn lo que realmente se vende: cajas, sobres o unidades
    var hayCajas = carrito.some(function(p) { return p.tipo_venta !== 'FRACCION_DECIMAL' && (parseInt(p.cantidad_por_unidad, 10) || 1) > 1; });
    var hayPastillas = carrito.some(function(p) { return (parseFloat(p.valor_intermedio) || 0) > 0; });
    var haySueltas = carrito.some(function(p) {
        return p.tipo_venta !== 'FRACCION_DECIMAL'
            && (parseInt(p.cantidad_por_unidad, 10) || 1) > 1
            && (parseInt(p.fraccion, 10) || 0) > 0;
    });
    // Cajas que se pueden abrir con el botÃ³n de la fila (cafÃ©: caja o sobre)
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
    // Los campos de nivel intermedio y unidades sueltas solo se usan cuando la
    // caja se vende abierta; con la caja cerrada no se muestran.
    $('#thColIntermedia').toggleClass('d-none', !hayIntermedio || !hayCantidadesSueltas);
    $('#thColUnidades').toggleClass('d-none', !hayCantidadesSueltas);

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

    // Cajas, nivel intermedio y unidades mÃ­nimas se editan libremente
    if (tipo === 'und') {
        item.cantidad_unidad = val;
    } else if (tipo === 'inter') {
        item.cantidad_intermedia = val;
    } else {
        item.cantidad_fraccion = val;
    }

    if (unidadesInternasLinea(item) > maxStock) {
        PNotify.error({ text: 'Stock insuficiente. Disponible: ' + maxStock + ' unidades mÃ­nimas' });
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

// Abre una caja cerrada para vender sus unidades mÃ­nimas sueltas.
// La caja se cambia por sus unidades: 1 caja -> 10 sobres o 10 pastillas.
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
    if ((parseFloat(item.valor_intermedio) || 0) > 0 && (parseInt(item.unidades_intermedias, 10) || 0) > 0) {
        var porIntermedio = parseInt(item.unidades_por_intermedio, 10) || 1;
        item.cantidad_intermedia = Math.floor(unidades / porIntermedio);
        item.cantidad_fraccion = unidades % porIntermedio;
    } else {
        item.cantidad_intermedia = 0;
        item.cantidad_fraccion = unidades;
    }

    PNotify.success({ text: 'Caja abierta: ' + unidades + ' ' + etiquetaMinimaCorta(item) + 's disponibles en el carrito' });
    actualizarCarrito();
}

// Cierra las unidades sueltas del carrito y las vuelve a empacar en cajas.
// Lo que no complete una caja se puede dejar como unidad suelta.
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
    if ((parseFloat(item.valor_intermedio) || 0) > 0 && (parseInt(item.unidades_intermedias, 10) || 0) > 0) {
        var porIntermedio = parseInt(item.unidades_por_intermedio, 10) || 1;
        item.cantidad_intermedia = Math.floor(sobrantes / porIntermedio);
        item.cantidad_fraccion = sobrantes % porIntermedio;
    } else {
        item.cantidad_intermedia = 0;
        item.cantidad_fraccion = sobrantes;
    }

    PNotify.success({ text: 'Empacado: ' + cajas + ' caja(s)' + (sobrantes > 0 ? ' + ' + sobrantes + ' ' + etiquetaMinimaCorta(item) + '(s) sueltas' : '') });
    actualizarCarrito();
}

// Agrega una caja (o una unidad si el producto no tiene caja)
function sumarUnaCaja(id) {
    var item = carrito.find(function(p) { return p.idCarrito === id; });
    if (!item) return;

    if (item.tipo_venta === 'FRACCION_DECIMAL') {
        item.cantidad_decimal = (parseFloat(item.cantidad_decimal) || 0) + 0.5;
    } else {
        item.cantidad_unidad = (item.cantidad_unidad || 0) + 1;
    }

    var maxStock = parseInt(item.stock_maximo, 10) || 0;
    if (unidadesInternasLinea(item) > maxStock) {
        if (item.tipo_venta === 'FRACCION_DECIMAL') {
            item.cantidad_decimal = Math.max(0, (parseFloat(item.cantidad_decimal) || 0) - 0.5);
        } else {
            item.cantidad_unidad = Math.max(0, (item.cantidad_unidad || 0) - 1);
        }
        PNotify.error({ text: 'Stock insuficiente. Disponible: ' + maxStock + ' unidades mÃ­nimas' });
    }

    actualizarCarrito();
}

// Quita una unidad mÃ­nima de la lÃ­nea (pastilla, sobre, unidad o caja suelta)
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

agregarAlCarrito(4, 'PRUEBA 4 - Caja x 12 sobres de cafe', '1000004', 15000, 0, 1, 1500, 120, 'UNIDAD', '', 1, 0, '', 1, 0);
console.log('ITEM', JSON.stringify(carrito[0]));
