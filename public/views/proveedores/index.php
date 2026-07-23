<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-truck text-primary me-2"></i>Proveedores</h4>
                <p class="text-muted mb-0">Gestión de proveedores</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT p.codigo AS Codigo, p.nombre AS Nombre, p.responsable AS Responsable, p.telefono AS Telefono, p.email AS Email, p.departamento AS Departamento FROM vb_proveedores p WHERE p.estado=1') ?>&nombre=proveedores&titulo=Proveedores" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT p.codigo AS Codigo, p.nombre AS Nombre, p.responsable AS Responsable, p.telefono AS Telefono, p.email AS Email, p.departamento AS Departamento FROM vb_proveedores p WHERE p.estado=1') ?>&nombre=proveedores&titulo=Proveedores" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalProveedor"><i class="fas fa-plus me-2"></i>Nuevo Proveedor</button>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control" id="searchProv" placeholder="Buscar...">
        </div>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Código</th><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th>Dpto</th><th style="width:80px">Acciones</th></tr>
                </thead>
                <tbody id="tbodyProv">
                    <tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagProv"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagProv"></ul></nav>
</div>

<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light"><h5 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i>Nuevo Proveedor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formProv">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Código <span class="text-danger">*</span></label><input type="text" class="form-control" name="codigo" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Responsable</label><input type="text" class="form-control" name="responsable"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Teléfono</label><input type="text" class="form-control" name="telefono"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" name="email"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Días de Pago</label><input type="number" class="form-control" name="dias_pago" min="0"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Departamento</label><input type="text" class="form-control" name="departamento"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Ciudad</label><input type="text" class="form-control" name="ciudad"></div>
                        <div class="col-12"><label class="form-label fw-semibold">Dirección</label><input type="text" class="form-control" name="direccion"></div>
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

<script>
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let pp = 1;
function cargarProv(page, s) {
    page = page || 1; s = s || $('#searchProv').val();
    $('#tbodyProv').html('<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/proveedores/listar', { page, search: s }, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        if (!d.data.length) { html = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay proveedores</td></tr>'; }
        else {
            $.each(d.data, function(i, p) {
                html += '<tr><td>' + escHtml(p.codigo) + '</td><td><strong>' + escHtml(p.nombre) + '</strong></td><td>' + escHtml(p.responsable || '-') + '</td><td>' + escHtml(p.telefono || '-') + '</td><td>' + escHtml(p.email || '-') + '</td><td>' + escHtml(p.departamento || '-') + '</td><td><button class="btn btn-sm btn-outline-danger" onclick="eliminarProv(' + p.id_proveedor + ')"><i class="fas fa-trash"></i></button></td></tr>';
            });
        }
        $('#tbodyProv').html(html);
        $('#infoPagProv').text(d.data.length + ' de ' + d.total);
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarProv(' + i + ');return false;">' + i + '</a></li>'; }
        $('#pagProv').html(ph);
    });
}
$('#formProv').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o, i) { o[i.name] = i.value; return o; }, {});
    $.ajax({ url: BASE_URL + '/proveedores/guardar', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
        success: function(r) { if (r.success) { $('#modalProveedor').modal('hide'); cargarProv(pp); PNotify.success({ text: r.message }); } }
    });
});
function eliminarProv(id) {
    SIG_confirmar('¿Eliminar este proveedor? Se desactivará su registro.','Eliminar Proveedor',function(){
    $.ajax({ url: BASE_URL + '/proveedores/eliminar', method: 'POST', contentType: 'application/json', data: JSON.stringify({ id_proveedor: id }),
        success: function(r) { if (r.success) { cargarProv(pp); PNotify.success({ text: r.message }); } }
    });
    });
}
var st;
$('#searchProv').on('keyup', function() { clearTimeout(st); st = setTimeout(function() { pp = 1; cargarProv(1); }, 400); });
$(function() { cargarProv(1); });
</script>
