<?php
// ---------- Cálculo ----------
$rowsC = [];
$total = 0;
if (!empty($result)) {
  foreach ($result as $r) { $total += (int) $r['total']; }
  $max = 0;
  foreach ($result as $r) { if ((int) $r['total'] > $max) { $max = (int) $r['total']; } }
  $i = 0;
  foreach ($result as $r) {
    $n = (int) $r['total'];
    $rowsC[] = [
      'ciudad' => $r['ciudad'],
      'n'      => $n,
      'color'  => 'hsl(' . fmod($i * 137.5 + 220, 360) . ' 58% 46%)',
      'pct'    => $total ? round($n / $total * 1000) / 10 : 0,
      'w'      => $max ? round($n / $max * 100) : 0,
    ];
    $i++;
  }
}
$acc = 0; $segs = [];
foreach ($rowsC as $rc) {
  $a = $total ? $acc / $total * 100 : 0;
  $acc += $rc['n'];
  $b = $total ? $acc / $total * 100 : 100;
  $segs[] = $rc['color'] . ' ' . $a . '% ' . $b . '%';
}
$stops = empty($segs) ? '#e5e9f2 0% 100%' : implode(',', $segs);
?>

<!-- FILTROS (pre-cargados para una nueva búsqueda) -->
<section class="card border-0 shadow rounded-4 mb-3">
  <div class="card-body p-4">
    <form name="resultados" method="POST" action="?view=reportes&mode=ventas" onsubmit="return validateVentas();">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label text-uppercase small fw-semibold" for="fecha_d">Desde</label>
          <input type="date" class="form-control" name="fecha_d" id="fecha_d" value="<?= htmlspecialchars($backDesde); ?>" required>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label text-uppercase small fw-semibold" for="fecha_h">Hasta</label>
          <input type="date" class="form-control" name="fecha_h" id="fecha_h" value="<?= htmlspecialchars($backHasta); ?>" required>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label text-uppercase small fw-semibold" for="servicio">Servicio</label>
          <select class="form-select" name="servicio" id="servicio" required>
            <option value="" disabled>Seleccione...</option>
            <?php if (!empty($servicio)) { foreach ($servicio as $s) { ?>
              <option value="<?= $s['id']; ?>" <?= ($s['id'] == $serv) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($s['descripcion']); ?>
              </option>
            <?php } } ?>
          </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <button type="submit" class="btn btn-primary w-100">Buscar</button>
        </div>
      </div>
    </form>
  </div>
</section>

<form method="POST" action="?view=reportes&mode=ventas" class="mb-3">
  <input type="hidden" name="fecha_d" value="<?= htmlspecialchars($backDesde); ?>">
  <input type="hidden" name="fecha_h" value="<?= htmlspecialchars($backHasta); ?>">
  <input type="hidden" name="servicio" value="<?= htmlspecialchars($serv); ?>">
  <button type="submit" class="btn btn-outline-secondary bg-white rounded-3 fw-semibold">
    ← Volver a ventas por estado
  </button>
</form>

<div class="row g-4 align-items-start">

  <!-- TABLA -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
      <div class="d-flex justify-content-between align-items-center gap-3 p-3 text-white" style="background:#8f2626">
        <div class="fs-5 fw-semibold">Ventas por ciudad</div>
        <div class="text-end">
          <div class="fs-3 fw-bold lh-1"><?= $total; ?></div>
          <div class="small" style="color:#f6cfcf">ventas</div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive" style="max-height:460px;overflow-y:auto">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr class="small text-uppercase text-muted">
                <th style="position:sticky;top:0;background:#fff">Ciudad</th>
                <th class="text-end" style="position:sticky;top:0;background:#fff">Ventas</th>
                <th class="text-end" style="position:sticky;top:0;background:#fff;width:64px">%</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($rowsC)) { foreach ($rowsC as $rc) { ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="rounded-1 flex-shrink-0" style="width:10px;height:10px;background:<?= $rc['color']; ?>"></span>
                      <span class="fw-medium"><?= htmlspecialchars($rc['ciudad']); ?></span>
                    </div>
                    <div class="rounded-pill mt-1 ms-4" style="height:4px;background:#eef0f4">
                      <div class="rounded-pill h-100" style="width:<?= $rc['w']; ?>%;background:<?= $rc['color']; ?>"></div>
                    </div>
                  </td>
                  <td class="text-end fw-semibold"><?= $rc['n']; ?></td>
                  <td class="text-end text-muted"><?= $rc['pct']; ?>%</td>
                </tr>
              <?php } } else { ?>
                <tr><td colspan="3" class="text-center text-muted py-4">Sin datos para mostrar</td></tr>
              <?php } ?>
            </tbody>
            <?php if (!empty($rowsC)) { ?>
              <tfoot>
                <tr class="fw-bold" style="--bs-table-bg:#fbeaea">
                  <td>Total</td>
                  <td class="text-end"><?= $total; ?></td>
                  <td class="text-end">100%</td>
                </tr>
              </tfoot>
            <?php } ?>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- GRÁFICO -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm rounded-4 h-100">
      <div class="card-body p-4">
        <div class="fw-semibold fs-6 mb-3">Ventas x ciudad</div>
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-4">
          <div class="donut donut--lg" style="background:conic-gradient(<?= $stops; ?>)">
            <div class="donut__hole">
              <div class="donut__label">Ventas</div>
              <div class="donut__total"><?= $total; ?></div>
            </div>
          </div>
          <div class="row row-cols-1 row-cols-sm-2 g-2 flex-fill" style="min-width:200px">
            <?php foreach ($rowsC as $rc) { ?>
              <div class="col">
                <div class="d-flex align-items-center gap-2 small">
                  <span class="rounded-1 flex-shrink-0" style="width:10px;height:10px;background:<?= $rc['color']; ?>"></span>
                  <span class="flex-fill text-truncate"><?= htmlspecialchars($rc['ciudad']); ?></span>
                  <span class="text-muted"><?= $rc['pct']; ?>%</span>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function validateVentas() {
  var sel = document.getElementById('servicio');
  if (!sel || !sel.value) { alert('Por favor seleccione un servicio'); return false; }
  var d = document.getElementById('fecha_d').value;
  var h = document.getElementById('fecha_h').value;
  if (d && h && h < d) { alert('La fecha "HASTA" es menor a la fecha "DESDE". Por favor verifique'); return false; }
  return true;
}
</script>
