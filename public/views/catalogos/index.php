<?php
/**
 * Vista: Catálogos
 * Gestión de Categorías, Secciones y Tipos de IVA (tablas simples)
 */
$puedeEditar = $puedeEditar ?? false;
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-tags text-primary me-2"></i>Catálogos</h4>
                <p class="text-muted mb-0">Categorías, secciones y tipos de IVA que usa el formulario de productos</p>
            </div>
        </div>
    </div>
</div>

<!-- Selector de catálogo -->
<ul class="nav nav-pills flex-wrap gap-2 mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active rounded-pill px-3" type="button" onclick="cambiarTipo('categorias', this)">
            <i class="fas fa-th-large me-1"></i>Categorías
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3" type="button" onclick="cambiarTipo('secciones', this)">
            <i class="fas fa-layer-group me-1"></i>Secciones
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3" type="button" onclick="cambiarTipo('ivas', this)">
            <i class="fas fa-percent me-1"></i>Tipos de IVA
        </button>
    </li>
</ul>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="fas fa-list me-2"></i><span id="catTitulo">Categorías</span>
            <span class="badge bg-secondary ms-2" id="catTotal">0</span>
        </h6>
        <div class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm" id="catBuscar" placeholder="Buscar..." onkeyup="filtrarCatalogo()" style="width:180px;">
            <?php if ($puedeEditar): ?>
            <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="abrirModalCatalogo()">
                <i class="fas fa-plus me-1"></i>Nuevo
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th id="catColExtra">Descripción</th>
                        <th class="text-center" style="width:110px">Estado</th>
                        <th class="text-center" style="width:170px">Productos</th>
                        <?php if ($puedeEditar): ?>
                        <th class="text-center" style="width:120px">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="tbodyCatalogo">
                    <tr><td colspan="5" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3 py-2 mb-0">
    <i class="fas fa-info-circle me-1"></i>
    Eliminar un registro <strong>no borra los productos</strong>: solo dejan de tener ese dato asignado.
</div>

<?php if ($puedeEditar): ?>
<!-- Modal Catálogo -->
<div class="modal fade" id="modalCatalogo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-tag me-2"></i><span id="catModalTitle">Nuevo</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCatalogo">
                <input type="hidden" id="cat_id" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cat_nombre" maxlength="100" required autocomplete="off">
                    </div>
                    <div class="mb-3" id="cat_grupo_descripcion">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" id="cat_descripcion" rows="2" placeholder="Opcional"></textarea>
                    </div>
                    <div class="mb-3" id="cat_grupo_iva" style="display:none;">
                        <label class="form-label fw-semibold">Porcentaje de IVA <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="cat_iva" min="0" max="100" step="0.01" value="0">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="cat_activo" checked>
                        <label class="form-check-label fw-semibold" for="cat_activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
var tipoActual = 'categorias';
var datosCatalogo = [];
var puedeEditar = <?= $puedeEditar ? 'true' : 'false' ?>;

var TITULOS = {
    categorias: 'Categorías',
    secciones: 'Secciones',
    ivas: 'Tipos de IVA'
};
var ETIQUETAS = {
    categorias: 'categoría',
    secciones: 'sección',
    ivas: 'tipo de IVA'
};

function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0, maximumFractionDigits:2}); }

function cambiarTipo(tipo, btn) {
    tipoActual = tipo;
    document.querySelectorAll('.nav-pills .nav-link').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');

    document.querySelector('#catTitulo').textContent = TITULOS[tipo] || tipo;
    document.querySelector('#catColExtra').textContent = (tipo === 'ivas') ? 'Porcentaje' : (tipo === 'secciones' ? '—' : 'Descripción');
    cargarCatalogo();
}

function cargarCatalogo() {
    $('#tbodyCatalogo').html('<tr><td colspan="5" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/catalogos/listar', { tipo: tipoActual }, function(r) {
        if (!r.success) { PNotify.error({ text: r.message }); return; }
        datosCatalogo = r.data || [];
        pintarCatalogo(datosCatalogo);
    });
}

function filtrarCatalogo() {
    var q = ($('#catBuscar').val() || '').toLowerCase().trim();
    if (!q) { pintarCatalogo(datosCatalogo); return; }
    pintarCatalogo(datosCatalogo.filter(function(x) {
        return (x.nombre || '').toLowerCase().indexOf(q) !== -1;
    }));
}

function pintarCatalogo(lista) {
    var colspan = puedeEditar ? 5 : 4;
    var html = '';

    if (!lista.length) {
        html = '<tr><td colspan="' + colspan + '" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay registros</td></tr>';
    } else {
        $.each(lista, function(i, x) {
            var extra = '';
            if (tipoActual === 'ivas') {
                extra = '<span class="badge bg-light text-dark">' + formatoNumero(x.iva) + ' %</span>';
            } else if (tipoActual === 'secciones') {
                extra = '<span class="text-muted">—</span>';
            } else {
                extra = '<small class="text-muted">' + escHtml(x.descripcion || '-') + '</small>';
            }

            var idCol = (tipoActual === 'categorias') ? x.id_categoria : (tipoActual === 'secciones' ? x.id_seccion : x.id_iva);
            var nombre = escHtml(x.nombre).replace(/'/g, "\\'");

            html += '<tr>' +
                '<td><strong>' + escHtml(x.nombre) + '</strong></td>' +
                '<td>' + extra + '</td>' +
                '<td class="text-center"><span class="badge ' + (parseInt(x.activo) === 1 ? 'bg-success' : 'bg-secondary') + '">' +
                    (parseInt(x.activo) === 1 ? 'Activo' : 'Inactivo') + '</span></td>' +
                '<td class="text-center">' +
                    '<span class="badge bg-light text-dark"><i class="fas fa-boxes me-1"></i>' + (x.productos || 0) + '</span>' +
                '</td>';

            if (puedeEditar) {
                html += '<td class="text-center text-nowrap">' +
                    '<button class="btn btn-sm btn-outline-primary me-1" onclick="editarCatalogo(' + idCol + ')" title="Editar"><i class="fas fa-edit"></i></button>' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="eliminarCatalogo(' + idCol + ',\'' + nombre + '\')" title="Eliminar"><i class="fas fa-trash"></i></button>' +
                    '</td>';
            }
            html += '</tr>';
        });
    }

    $('#tbodyCatalogo').html(html);
    $('#catTotal').text(lista.length);
}

function abrirModalCatalogo() {
    $('#cat_id').val(0);
    $('#cat_nombre').val('');
    $('#cat_descripcion').val('');
    $('#cat_iva').val(0);
    $('#cat_activo').prop('checked', true);
    $('#catModalTitle').text('Nuevo ' + (ETIQUETAS[tipoActual] || ''));
    $('#cat_grupo_descripcion').toggle(tipoActual === 'categorias');
    $('#cat_grupo_iva').toggle(tipoActual === 'ivas');
    $('#modalCatalogo').modal('show');
}

function editarCatalogo(id) {
    var col = (tipoActual === 'categorias') ? 'id_categoria' : (tipoActual === 'secciones' ? 'id_seccion' : 'id_iva');
    var item = null;
    $.each(datosCatalogo, function(i, x) { if (parseInt(x[col]) === parseInt(id)) { item = x; return false; } });
    if (!item) { PNotify.error({ text: 'No se encontró el registro' }); return; }

    $('#cat_id').val(id);
    $('#cat_nombre').val(item.nombre || '');
    $('#cat_descripcion').val(item.descripcion || '');
    $('#cat_iva').val(item.iva || 0);
    $('#cat_activo').prop('checked', parseInt(item.activo) === 1);
    $('#catModalTitle').text('Editar ' + (ETIQUETAS[tipoActual] || ''));
    $('#cat_grupo_descripcion').toggle(tipoActual === 'categorias');
    $('#cat_grupo_iva').toggle(tipoActual === 'ivas');
    $('#modalCatalogo').modal('show');
}

$('#formCatalogo').on('submit', function(e) {
    e.preventDefault();
    var data = {
        tipo: tipoActual,
        id: parseInt($('#cat_id').val()) || 0,
        nombre: ($('#cat_nombre').val() || '').trim(),
        activo: $('#cat_activo').is(':checked') ? 1 : 0
    };
    if (!data.nombre) { PNotify.error({ text: 'El nombre es obligatorio' }); return; }
    if (tipoActual === 'categorias') data.descripcion = $('#cat_descripcion').val();
    if (tipoActual === 'ivas') data.iva = parseFloat($('#cat_iva').val()) || 0;

    $.ajax({
        url: BASE_URL + '/catalogos/guardar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) {
            if (r.success) {
                $('#modalCatalogo').modal('hide');
                PNotify.success({ text: r.message });
                cargarCatalogo();
            } else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar' }); }
    });
});

function eliminarCatalogo(id, nombre) {
    if (!confirm('¿Eliminar "' + nombre + '"?\n\nLos productos que lo usen quedarán sin este dato.')) return;
    $.ajax({
        url: BASE_URL + '/catalogos/eliminar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ tipo: tipoActual, id: id }),
        success: function(r) {
            if (r.success) { PNotify.success({ text: r.message }); cargarCatalogo(); }
            else { PNotify.error({ text: r.message }); }
        },
        error: function(xhr) { PNotify.error({ text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al eliminar' }); }
    });
}

$(function() { cargarCatalogo(); });
</script>
