<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-money-bill-wave text-primary me-2"></i>Egresos</h4>
                <p class="text-muted mb-0">Registro de gastos</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $basePath ?>/exportar/csv?sql=<?= urlencode('SELECT e.fecha AS Fecha, t.nombre AS Tipo, e.pagado_a AS PagadoA, e.valor AS Valor, e.observacion AS Observacion FROM vb_egresos e JOIN vb_tipos_egreso t ON e.id_tipo_egreso=t.id_tipo_egreso ORDER BY e.fecha DESC') ?>&nombre=egresos&titulo=Egresos" class="btn btn-outline-success btn-sm rounded-pill"><i class="fas fa-file-excel me-1"></i>Excel</a>
                <a href="<?= $basePath ?>/exportar/pdf?sql=<?= urlencode('SELECT e.fecha AS Fecha, t.nombre AS Tipo, e.pagado_a AS PagadoA, e.valor AS Valor FROM vb_egresos e JOIN vb_tipos_egreso t ON e.id_tipo_egreso=t.id_tipo_egreso ORDER BY e.fecha DESC') ?>&nombre=egresos&titulo=Egresos" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fas fa-file-pdf me-1"></i>PDF</a>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalEgreso"><i class="fas fa-plus me-2"></i>Nuevo Egreso</button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <select class="form-select" id="filtroMes" onchange="cargarEgr(1)">
            <option value="">Todos los meses</option>
            <?php foreach ($meses as $m): ?>
            <option value="<?= $m['mes'] ?>"><?= $m['mes'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning bg-opacity-10 border-0 p-2 text-center">
            <small class="text-muted">Total</small>
            <strong id="totalEgr" class="text-warning">$0</strong>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Tipo</th><th>Pagado A</th><th>Valor</th><th>Observación</th><th>Usuario</th></tr>
                </thead>
                <tbody id="tbodyEgr">
                    <tr><td colspan="6" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted" id="infoPagEgr"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagEgr"></ul></nav>
</div>

<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Nuevo Egreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEgreso">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_tipo_egreso" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id_tipo_egreso'] ?>"><?= \SIG\Core\View::esc($t['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Valor <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="valor" step="100" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pagado A</label>
                            <input type="text" class="form-control" name="pagado_a" placeholder="Nombre o entidad">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha</label>
                            <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observación</label>
                            <input type="text" class="form-control" name="observacion">
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
function escHtml(s) { if (!s) return ''; return $('<div>').text(s).html(); }
function formatoNumero(n) { return parseFloat(n||0).toLocaleString('es-CO', {minimumFractionDigits:0}); }
let pe = 1;
function cargarEgr(page) {
    page = page || 1; pe = page;
    var mes = $('#filtroMes').val();
    $('#tbodyEgr').html('<tr><td colspan="6" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x d-block mb-3"></i>Cargando...</td></tr>');
    $.getJSON(BASE_URL + '/egresos/listar', {page, mes}, function(r) {
        if (!r.success) return; var d = r.data, html = '';
        if (!d.data.length) { html = '<tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Sin egresos</td></tr>'; }
        else { $.each(d.data, function(i, e) {
            html += '<tr><td>' + e.fecha + '</td><td><span class="badge bg-secondary">' + escHtml(e.tipo_nombre) + '</span></td><td>' + escHtml(e.pagado_a||'-') + '</td><td class="fw-bold text-danger">-$' + formatoNumero(e.valor) + '</td><td><small>' + escHtml(e.observacion||'-') + '</small></td><td><small class="text-muted">' + escHtml(e.nombre_usuario||'-') + '</small></td></tr>';
        }); }
        $('#tbodyEgr').html(html);
        $('#totalEgr').text('$' + formatoNumero(d.suma||0));
        $('#infoPagEgr').text(d.data.length + ' de ' + d.total + ' | Total: $' + formatoNumero(d.suma||0));
        var ph = '';
        for (var i = 1; i <= d.totalPages; i++) { ph += '<li class="page-item ' + (i === d.page ? 'active' : '') + '"><a class="page-link" href="#" onclick="cargarEgr(' + i + ');return false;">' + i + '</a></li>'; }
        $('#pagEgr').html(ph);
    });
}
$('#formEgreso').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serializeArray().reduce(function(o,i){o[i.name]=i.value;return o;},{});
    $.ajax({url: BASE_URL + '/egresos/guardar', method:'POST', contentType:'application/json', data:JSON.stringify(data),
        success:function(r){if(r.success){$('#modalEgreso').modal('hide');cargarEgr(1);PNotify.success({text:r.message});}}});
});
$(function(){cargarEgr(1);});
</script>
