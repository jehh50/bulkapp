<?php
$busqueda = !empty($_POST);
$total    = is_array($ventas ?? null) ? count($ventas) : 0;
// Color de píldora por producto
$prodPal   = ['#dbe5fa', '#fdf0cf', '#d3f0e6', '#f7d9e0', '#e4dcf7', '#d9eef7'];
$prodColor = [];
$descargaUrl = '?view=reportes&mode=download&from=' . $desde . '&to=' . $hasta . '&servicio=' . urlencode($selServicio);
?>

<!-- FILTROS -->
<section class="card border-0 shadow rounded-4 mb-3">
  <div class="card-body p-4">
    <form name="resultados" method="POST" action="?view=reportes&mode=detalleventas" onsubmit="return validateDetalle();">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label text-uppercase small fw-semibold" for="fecha_d">Desde</label>
          <input type="date" class="form-control" name="fecha_d" id="fecha_d" value="<?= htmlspecialchars($backDesde); ?>" required autofocus>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label text-uppercase small fw-semibold" for="fecha_h">Hasta</label>
          <input type="date" class="form-control" name="fecha_h" id="fecha_h" value="<?= htmlspecialchars($backHasta); ?>" required>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label text-uppercase small fw-semibold" for="servicio">Servicio</label>
          <select class="form-select" name="servicio" id="servicio" required>
            <option value="" disabled <?= $selServicio === '' ? 'selected' : ''; ?>>Seleccione...</option>
            <?php foreach ($servicio as $s) { ?>
              <option value="<?= $s['id']; ?>" <?= ($s['id'] == $selServicio) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($s['descripcion']); ?>
              </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Buscar</button>
            <?php if (!empty($ventas)) { ?>
              <a href="<?= $descargaUrl; ?>" class="btn btn-outline-primary flex-fill">Descargar</a>
            <?php } else { ?>
              <button type="button" class="btn btn-outline-primary flex-fill" disabled>Descargar</button>
            <?php } ?>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<?php if ($busqueda) { ?>
  <!-- RESULTADOS -->
  <section class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-bottom">
      <div class="fs-6"><b class="fw-bold"><?= $total; ?></b> resultados</div>
      <?php if ($backDesde && $backHasta) { ?>
        <div class="small text-muted">
          Período <?= date('d-m-Y', strtotime($backDesde)); ?> – <?= date('d-m-Y', strtotime($backHasta)); ?>
        </div>
      <?php } ?>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 table-sticky" style="min-width:1280px;font-size:13.5px">
        <thead class="table-light">
          <tr class="small text-uppercase">
            <th>#</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Cédula</th>
            <th>Fecha de nacimiento</th>
            <th>Teléfono 1</th>
            <th>Teléfono 2</th>
            <th>Correo</th>
            <th>Producto</th>
            <th>Agente</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($ventas)) { $i = 1;
            foreach ($ventas as $venta) {
              $prod = $venta['descripcion'];
              if (!isset($prodColor[$prod])) { $prodColor[$prod] = $prodPal[count($prodColor) % count($prodPal)]; }
          ?>
            <tr class="venta-row">
              <td class="text-muted"><?= $i; ?></td>
              <td class="fw-medium"><?= htmlspecialchars($venta['nombre']); ?></td>
              <td class="fw-medium"><?= htmlspecialchars($venta['apellido']); ?></td>
              <td><?= htmlspecialchars($venta['cedula']); ?></td>
              <td class="text-nowrap"><?= htmlspecialchars($venta['fecha_nacimiento']); ?></td>
              <td class="text-nowrap"><?= htmlspecialchars($venta['telf_hab']); ?></td>
              <td class="text-nowrap"><?= htmlspecialchars($venta['telf_celular']); ?></td>
              <td><?= htmlspecialchars($venta['correo']); ?></td>
              <td><span class="badge rounded-pill fw-medium" style="background:<?= $prodColor[$prod]; ?>;color:#252525"><?= htmlspecialchars($prod); ?></span></td>
              <td><?= htmlspecialchars($venta['agente']); ?></td>
              <td class="text-nowrap"><?= date('d-m-Y', strtotime($venta['fecha_venta'])); ?></td>
            </tr>
          <?php $i++; } } else { ?>
            <tr><td colspan="11" class="text-center text-muted py-5">No se encontraron ventas para el filtro seleccionado.</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-2 border-top" id="ventasPagerWrap" style="display:none!important">
      <div class="small text-muted" id="ventasPageInfo"></div>
      <nav aria-label="Paginación de ventas">
        <ul class="pagination pagination-sm mb-0" id="ventasPager"></ul>
      </nav>
    </div>
  </section>
<?php } else { ?>
  <section class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center text-muted py-5">
      Seleccione un rango de fechas y un servicio, luego presione <strong>Buscar</strong>.
    </div>
  </section>
<?php } ?>

<script>
function validateDetalle() {
  var sel = document.getElementById('servicio');
  if (!sel || !sel.value) { alert('Por favor seleccione un servicio'); return false; }
  var d = document.getElementById('fecha_d').value;
  var h = document.getElementById('fecha_h').value;
  if (d && h && h < d) { alert('La fecha "HASTA" es menor a la fecha "DESDE". Por favor verifique'); return false; }
  return true;
}

// Paginación del listado (10 por página)
(function () {
  var rows = Array.prototype.slice.call(document.querySelectorAll('.venta-row'));
  var pager = document.getElementById('ventasPager');
  var wrap = document.getElementById('ventasPagerWrap');
  var info = document.getElementById('ventasPageInfo');
  if (!rows.length || !pager || !wrap) { return; }

  var pageSize = 10;
  var pages = Math.ceil(rows.length / pageSize);
  if (pages <= 1) { return; }

  wrap.style.setProperty('display', 'flex', 'important');
  var page = 1;

  function render() {
    var start = (page - 1) * pageSize, end = start + pageSize;
    rows.forEach(function (r, i) { r.style.display = (i >= start && i < end) ? '' : 'none'; });

    var html = '<li class="page-item ' + (page === 1 ? 'disabled' : '') + '">' +
               '<a class="page-link" href="#" data-pg="' + (page - 1) + '" aria-label="Anterior">&laquo;</a></li>';
    for (var p = 1; p <= pages; p++) {
      html += '<li class="page-item ' + (p === page ? 'active' : '') + '">' +
              '<a class="page-link" href="#" data-pg="' + p + '">' + p + '</a></li>';
    }
    html += '<li class="page-item ' + (page === pages ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-pg="' + (page + 1) + '" aria-label="Siguiente">&raquo;</a></li>';
    pager.innerHTML = html;

    if (info) {
      var s = (page - 1) * pageSize + 1, e = Math.min(page * pageSize, rows.length);
      info.textContent = 'Mostrando ' + s + '–' + e + ' de ' + rows.length + ' resultados';
    }
  }

  pager.addEventListener('click', function (e) {
    var a = e.target.closest('a[data-pg]');
    if (!a) { return; }
    e.preventDefault();
    var pg = parseInt(a.getAttribute('data-pg'), 10);
    if (pg >= 1 && pg <= pages && pg !== page) { page = pg; render(); }
  });

  render();
})();
</script>
