<?php
/**
 * Parcial: Historial de precios y compras de un producto.
 *
 * Se incluye desde las vistas que necesiten mostrar la trazabilidad:
 *   <?php include __DIR__ . '/../partials/historial_precios.php'; ?>
 *
 * Requiere que la página defina ANTES:
 *   - BASE_URL       (constante de la app)
 *   - escHtml()      (escapar texto)
 *   - formatoNumero()(formatear cifras)
 *
 * Uso:  verHistorialPrecios(idProducto);
 */
?>
<!-- Modal Historial de Precios -->
<div class="modal fade" id="modalHistPrecios" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i>Historial de precios y compras</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="histPrecBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto"><i class="fas fa-lock me-1"></i>Este historial no se puede modificar ni borrar (trazabilidad contable).</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function verHistorialPrecios(id) {
    if (!id) { PNotify.error({ text: 'Seleccione un producto' }); return; }
    $('#histPrecBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#modalHistPrecios').modal('show');

    $.getJSON(BASE_URL + '/ingresos/historial-precios', { id_producto: id }, function(r) {
        if (!r.success) {
            $('#histPrecBody').html('<div class="alert alert-danger mb-0">' + escHtml(r.message || 'No se pudo cargar el historial') + '</div>');
            return;
        }
        var d = r.data, s = d.resumen || {}, h = '';

        if (d.producto) {
            h += '<h6 class="fw-bold mb-3">' + escHtml(d.producto.descripcion) +
                 ' <small class="text-muted">(' + escHtml(d.producto.codigo) + ')</small></h6>';
        }

        // ---- Resumen ----
        h += '<div class="row g-2 mb-3">';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100">' +
             '<small class="text-muted d-block">Primera compra</small><strong>$' + formatoNumero(s.primera_compra || 0) + '</strong>' +
             '<br><small class="text-muted">' + escHtml(s.fecha_primera || 'sin datos') + '</small></div></div>';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100">' +
             '<small class="text-muted d-block">Última compra</small><strong>$' + formatoNumero(s.ultima_compra || 0) + '</strong>' +
             '<br><small class="text-muted">' + escHtml(s.fecha_ultima || 'sin datos') + '</small></div></div>';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100 bg-light">' +
             '<small class="text-muted d-block">Costo promedio</small><strong class="text-primary">$' + formatoNumero(s.costo_promedio || 0) + '</strong>' +
             '<br><small class="text-muted">el que se usa hoy</small></div></div>';
        h += '<div class="col-6 col-md-3"><div class="border rounded-3 p-2 text-center h-100">' +
             '<small class="text-muted d-block">Precio de venta</small><strong>$' + formatoNumero(s.precio_venta || 0) + '</strong>' +
             '<br><small class="text-muted">margen ' + (s.rentabilidad !== null ? s.rentabilidad : 0) + '%</small></div></div>';
        h += '</div>';

        if (s.tope_regulado) {
            h += '<div class="alert alert-info py-2 small"><i class="fas fa-balance-scale me-1"></i>Precio máximo regulado: <strong>$' + formatoNumero(s.tope_regulado) + '</strong></div>';
        }

        // ---- Compras ----
        h += '<h6 class="fw-bold mt-3 mb-2"><i class="fas fa-truck-loading me-1 text-primary"></i>Compras registradas</h6>';
        if (!d.compras || !d.compras.length) {
            h += '<p class="text-muted small mb-3">Este producto todavía no tiene compras con precio registrado.</p>';
        } else {
            h += '<div class="table-responsive mb-3"><table class="table table-sm table-hover align-middle">' +
                 '<thead class="table-light"><tr><th>Fecha</th><th class="text-center">Cantidad</th>' +
                 '<th class="text-end">Precio de compra</th><th>Usuario</th><th>Observación</th></tr></thead><tbody>';
            $.each(d.compras, function(i, c) {
                var cant = (c.cantidad_unidad || 0) + (parseInt(c.cantidad_fraccion) ? ' + ' + c.cantidad_fraccion + ' frac' : '');
                h += '<tr><td><small>' + escHtml(c.fecha) + '</small></td>' +
                     '<td class="text-center">' + cant + '</td>' +
                     '<td class="text-end fw-semibold">$' + formatoNumero(c.valor_compra) + '</td>' +
                     '<td><small>' + escHtml(c.nombre_usuario || '-') + '</small></td>' +
                     '<td><small class="text-muted">' + escHtml(c.observacion || '-') + '</small></td></tr>';
            });
            h += '</tbody></table></div>';
        }

        // ---- Cambios de precio ----
        h += '<h6 class="fw-bold mt-3 mb-2"><i class="fas fa-tags me-1 text-success"></i>Cambios de precio</h6>';
        if (!d.historial || !d.historial.length) {
            h += '<p class="text-muted small mb-0">Todavía no hay cambios de precio registrados para este producto.</p>';
        } else {
            h += '<div class="table-responsive"><table class="table table-sm table-hover align-middle">' +
                 '<thead class="table-light"><tr><th>Fecha</th><th>Origen</th><th>Costo promedio</th><th>Precio de venta</th>' +
                 '<th class="text-center">Margen</th><th>Motivo</th><th>Usuario</th></tr></thead><tbody>';
            $.each(d.historial, function(i, x) {
                var cambiaCosto = parseFloat(x.costo_anterior) !== parseFloat(x.costo_nuevo);
                var cambiaVenta = parseFloat(x.precio_venta_anterior) !== parseFloat(x.precio_venta_nuevo);
                h += '<tr><td><small>' + escHtml(x.created_at) + '</small></td>' +
                     '<td><span class="badge bg-light text-dark">' + escHtml(x.origen) + '</span></td>' +
                     '<td>$' + formatoNumero(x.costo_anterior) + ' <i class="fas fa-arrow-right mx-1 text-muted"></i> ' +
                     '<strong class="' + (cambiaCosto ? 'text-primary' : 'text-muted') + '">$' + formatoNumero(x.costo_nuevo) + '</strong></td>' +
                     '<td>$' + formatoNumero(x.precio_venta_anterior) + ' <i class="fas fa-arrow-right mx-1 text-muted"></i> ' +
                     '<strong class="' + (cambiaVenta ? 'text-danger' : 'text-muted') + '">$' + formatoNumero(x.precio_venta_nuevo) + '</strong></td>' +
                     '<td class="text-center">' + (x.rentabilidad !== null ? x.rentabilidad + '%' : '-') + '</td>' +
                     '<td><small class="text-muted">' + escHtml(x.motivo || '-') + '</small></td>' +
                     '<td><small>' + escHtml(x.nombre_usuario || '-') + '</small></td></tr>';
            });
            h += '</tbody></table></div>';
        }

        $('#histPrecBody').html(h);
    });
}
</script>
