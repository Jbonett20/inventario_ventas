<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-users text-primary me-2"></i>Clientes</h4>
                <p class="text-muted mb-0">Gestión de clientes</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT c.tipo_documento AS TipoDoc, c.documento AS Documento, c.nombre AS Nombre, c.direccion AS Direccion, c.telefono AS Telefono, c.email AS Email FROM vb_clientes c WHERE c.activo=1') ?>&nombre=clientes&titulo=Clientes" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT c.tipo_documento AS TipoDoc, c.documento AS Documento, c.nombre AS Nombre, c.direccion AS Direccion, c.telefono AS Telefono, c.email AS Email FROM vb_clientes c WHERE c.activo=1') ?>&nombre=clientes&titulo=Clientes" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCliente"><i class="fas fa-plus me-2"></i>Nuevo Cliente</button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control" id="searchCliente" placeholder="Buscar por documento, nombre o teléfono...">
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaClientes">
                <thead class="table-light">
                    <tr>
                        <th>Tipo Doc</th><th>Documento</th><th>Nombre</th>
                        <th>Dirección</th><th>Teléfono</th><th>Email</th><th style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyClientes">
                    <tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPaginacion"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="paginacion"></ul></nav>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalClienteLabel"><i class="fas fa-user me-2"></i>Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCliente">
                <input type="hidden" name="id_cliente" id="c_id" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo Doc</label>
                            <select class="form-select" name="tipo_documento" id="c_tipo_doc">
                                <option value="CC">CC</option><option value="NIT">NIT</option>
                                <option value="CE">CE</option><option value="PP">PP</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="documento" id="c_documento" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="c_nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="c_direccion">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="c_telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" id="c_email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Régimen</label>
                            <select class="form-select" name="regimen" id="c_regimen">
                                <option value="">Seleccione...</option>
                                <option value="Simplificado">Simplificado</option>
                                <option value="Común">Común</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="c_obs" rows="2"></textarea>
                        </div>
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
let cp = 1;
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
function cargarClientes(page, s) {
    page = page || 1; s = s || $('#searchCliente').val();
    $('#tbodyClientes').html('<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/clientes/listar', { page, search: s }, function(r) {
        if (!r.success) return;
        var d = r.data, html = '';
        if (!d.data.length) {
            html = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay clientes</td></tr>';
        } else {
            $.each(d.data, function(i, c) {
                html += '<tr><td>' + escHtml(c.tipo_documento) + '</td><td><strong>' + escHtml(c.documento) + '</strong></td>' +
                    '<td>' + escHtml(c.nombre) + '</td><td>' + escHtml(c.direccion || '-') + '</td>' +
                    '<td>' + escHtml(c.telefono || '-') + '</td><td>' + escHtml(c.email || '-') + '</td>' +
                    '<td><button class="btn btn-sm btn-outline-primary me-1" onclick="editarCliente(' + c.id_cliente + ')"><i class="fas fa-edit"></i></button>' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente(' + c.id_cliente + ')"><i class="fas fa-trash"></i></button></td></tr>';
            });
        }
        $('#tbodyClientes').html(html);
        $('#infoPaginacion').text('Mostrando ' + d.data.length + ' de ' + d.total);
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) {
            ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarClientes(' + i + ');return false;">' + i + '</a></li>';
        }
        $('#paginacion').html(ph);
    });
}

$('#formCliente').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o, i) { o[i.name] = i.value; return o; }, {});
    $.ajax({
        url: BASE_URL + '/clientes/guardar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(r) { if (r.success) { $('#modalCliente').modal('hide'); cargarClientes(cp); PNotify.success({ text: r.message }); } else { PNotify.error({ text: r.message }); } }
    });
});

function editarCliente(id) {
    $.getJSON(BASE_URL + '/clientes/obtener/' + id, function(r) {
        if (!r.success) return;
        var c = r.data;
        $('#modalClienteLabel').html('<i class="fas fa-edit me-2"></i>Editar Cliente');
        $('#c_id').val(c.id_cliente); $('#c_tipo_doc').val(c.tipo_documento);
        $('#c_documento').val(c.documento); $('#c_nombre').val(c.nombre);
        $('#c_direccion').val(c.direccion); $('#c_telefono').val(c.telefono);
        $('#c_email').val(c.email); $('#c_regimen').val(c.regimen); $('#c_obs').val(c.observaciones);
        $('#modalCliente').modal('show');
    });
}

function eliminarCliente(id) {
    SIG_confirmar('¿Eliminar este cliente? Se desactivará su cuenta.','Eliminar Cliente',function(){
    $.ajax({
        url: BASE_URL + '/clientes/eliminar', method: 'POST', contentType: 'application/json',
        data: JSON.stringify({ id_cliente: id }),
        success: function(r) { if (r.success) { cargarClientes(cp); PNotify.success({ text: r.message }); } }
    });
    });
}

$('#modalCliente').on('show.bs.modal', function() {
    if (!$(this).find('#c_id').val()) {
        $('#modalClienteLabel').html('<i class="fas fa-user me-2"></i>Nuevo Cliente');
        $('#formCliente')[0].reset(); $('#c_id').val(0);
    }
});

var st;
$('#searchCliente').on('keyup', function() { clearTimeout(st); st = setTimeout(function() { cp = 1; cargarClientes(1); }, 400); });

$(function() { cargarClientes(1); });
</script>
