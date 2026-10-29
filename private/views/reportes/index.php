<?php
if (!function_exists('rep_pct')) {
  function rep_pct($a, $b) { return $b ? round($a / $b * 10000) / 100 : 0; }
}
if (!function_exists('rep_iniciales')) {
  function rep_iniciales($nombre) {
    $parts = preg_split('/\s+/', trim($nombre));
    $ini = '';
    foreach ($parts as $p) { if ($p !== '') { $ini .= mb_substr($p, 0, 1, 'UTF-8'); } }
    return mb_strtoupper($ini, 'UTF-8');
  }
}
if (!function_exists('rep_donut')) {
  function rep_donut($items) { // items: [['n'=>,'color'=>], ...]
    $total = 0; foreach ($items as $it) { $total += $it['n']; }
    if ($total <= 0) { return '#e5e9f2 0% 100%'; }
    $acc = 0; $segs = [];
    foreach ($items as $it) {
      $a = $acc / $total * 100; $acc += $it['n']; $b = $acc / $total * 100;
      $segs[] = $it['color'] . ' ' . $a . '% ' . $b . '%';
    }
    return implode(',', $segs);
  }
}
if (!function_exists('rep_motivos_panel')) {
  function rep_motivos_panel($titulo, $bg, $rows, $pal2) {
    $total = 0; $max = 0;
    if (!empty($rows)) {
      foreach ($rows as $r) { $total += (int) $r['total']; if ((int) $r['total'] > $max) { $max = (int) $r['total']; } }
    }
    $items = []; $i = 0;
    if (!empty($rows)) {
      foreach ($rows as $r) { $items[] = ['label' => $r['descripcion'], 'n' => (int) $r['total'], 'color' => $pal2[$i % count($pal2)]]; $i++; }
    }
    $stops = rep_donut($items);
    ?>
    <div class="card border rounded-4 overflow-hidden h-100">
      <div class="d-flex justify-content-between align-items-center p-3 text-white" style="background:<?= $bg; ?>">
        <div class="fw-semibold"><?= htmlspecialchars($titulo); ?></div>
        <div class="fs-4 fw-bold lh-1"><?= $total; ?></div>
      </div>
      <div class="card-body">
        <?php if (!empty($items)) { ?>
          <div class="d-flex justify-content-center mb-3">
            <div class="donut" style="background:conic-gradient(<?= $stops; ?>)"><div class="donut__hole"></div></div>
          </div>
          <table class="table table-sm align-middle mb-0">
            <tbody>
              <?php foreach ($items as $it) { $fw = ($it['n'] === $max && $max > 0) ? 'fw-semibold' : ''; ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="rounded-1 flex-shrink-0" style="width:10px;height:10px;background:<?= $it['color']; ?>"></span>
                      <span class="<?= $fw; ?>"><?= htmlspecialchars($it['label']); ?></span>
                    </div>
                  </td>
                  <td class="text-end <?= $fw; ?>"><?= $it['n']; ?></td>
                  <td class="text-end text-muted" style="width:56px"><?= rep_pct($it['n'], $total); ?>%</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } else { ?>
          <div class="text-center text-muted py-4">Sin datos para mostrar</div>
        <?php } ?>
      </div>
    </div>
    <?php
  }
}
if(!isset($_POST['fecha_d'])){
  $fecha_d = $fecha_h = date('d/m/Y');
}else{
  $fecha_d = $_POST['fecha_d'];
  $fecha_h = $_POST['fecha_h'];
}
?>

<!-- FILTROS -->
<section class="filter-card">
  <form name="resultados"
        method="POST"
        action="?view=reportes&mode=acumulado"
        onsubmit="return validateService();">
    <div class="row g-3 align-items-end">
      <div class="col-12 col-sm-6 col-lg-3">
        <label class="form-label filter-label" for="fecha_d">Desde</label>
        <input type="date" class="form-control" name="fecha_d" id="fecha_d" value="<?=$fecha_d;?>">
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <label class="form-label filter-label" for="fecha_h">Hasta</label>
        <input type="date" class="form-control" name="fecha_h" id="fecha_h" value="<?=$fecha_h;?>">
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <label class="form-label filter-label" for="servicio">Servicio</label>
        <select class="form-select" name="servicio" id="servicio" required>
          <option value="" disabled selected>Seleccione...</option>
          <?php foreach ($servicio as $s) { ?>
            <option value="<?php echo $s['id']; ?>"><?php echo $s['descripcion']; ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <label class="form-label filter-label" for="producto">Producto</label>
        <select class="form-select" name="producto" id="producto"></select>
      </div>
      <div class="col-12 col-sm-6 col-lg-3">
        <button type="submit" class="btn btn-primary w-100 search-btn">Buscar</button>
      </div>
    </div>
  </form>
</section>

<?php if (empty($gestion)) { ?>

  <section class="card data-card">
    <div class="data-empty">Seleccione un servicio y un rango de fechas, luego presione <strong>Buscar</strong>.</div>
  </section>

<?php
} else {
  // ---------- Cálculo de filas y totales ----------
  $avatarBg = ['#dbe5fa', '#fde7b0', '#d3f0e6', '#f7d9e0', '#cca677e9', '#d9eef7'];
  $salesPal = ['#0f9d75', '#8a3fb0', '#f5b83d', '#e8743b', '#0f9d75', '#8a3fb0'];

  $rows = [];
  $tg = $tc = $tnc = $tv = $nc = 0;
  foreach ($gestion as $g) {
    $gg = (int) $g['gestion']; $cc = (int) $g['efectivos']; $vv = (int) $g['ventas'];
    $nc = $g['noefectivos'];
    $rows[] = ['nombre' => $g['nombre'], 'g' => $gg, 'c' => $cc, 'nc' => $nc, 'v' => $vv,
               'pc' => rep_pct($cc, $gg), 'pnc' => rep_pct($nc, $gg), 'pv' => rep_pct($vv, $cc)];
    $tg += $gg; $tc += $cc; $tv += $vv;
  }
  $tnc = $tg - $tc;
  $maxV = 0; foreach ($rows as $r) { if ($r['v'] > $maxV) { $maxV = $r['v']; } }

  foreach ($rows as $i => &$r) {
    $r['ini']    = rep_iniciales($r['nombre']);
    $r['avatar'] = $avatarBg[$i % count($avatarBg)];
    $ratio = $r['c'] > 0 ? $r['v'] / $r['c'] * 100 : 0;
    if ($r['v'] == 0)                      { $r['vbg'] = '#fde0e0'; $r['vfg'] = '#b42318'; }
    elseif ($ratio >= 2 && $ratio < 10)    { $r['vbg'] = '#ffe8cc'; $r['vfg'] = '#b45309'; }
    else                                   { $r['vbg'] = '#d3f0e6'; $r['vfg'] = '#0b6b4f'; }
    $r['zero']  = ($r['v'] == 0);
    $r['crown'] = ($maxV > 0 && $r['v'] == $maxV);
  }
  unset($r);

  $t = ['g' => $tg, 'c' => $tc, 'nc' => $tnc, 'v' => $tv,
        'pc' => rep_pct($tc, $tg), 'pnc' => rep_pct($tnc, $tg), 'pv' => rep_pct($tv, $tc)];

  // ---------- Gráficas ----------
  $chart1Items = [['label' => 'Contactados', 'n' => $tc, 'color' => '#1a4391'],
                  ['label' => 'No contactados', 'n' => $tnc, 'color' => '#d5dcec']];
  $chart1Total = $tc + $tnc;
  $chart2Items = [];
  $ventasProd = isset($ventasProducto) && is_array($ventasProducto) ? $ventasProducto : [];
  $i = 0;
  foreach ($ventasProd as $vp) {
    $chart2Items[] = ['label' => $vp['producto'], 'n' => (int) $vp['vendidos'], 'color' => $salesPal[$i % count($salesPal)]];
    $i++;
  }
  $chart2Total = 0; foreach ($chart2Items as $it) { $chart2Total += $it['n']; }
  $servLabel = isset($nav_service) && $nav_service !== null ? $nav_service : '';
?>

  <!-- KPIs -->
  <section class="kpi-grid">
    <div class="kpi" style="border-top-color:#1a4391">
      <div class="kpi__label">Gestionados</div>
      <div class="kpi__row"><span class="kpi__value"><?php echo $t['g']; ?></span><span class="kpi__sub">registros</span></div>
    </div>
    <div class="kpi" style="border-top-color:#2f7bd1">
      <div class="kpi__label">Contactados</div>
      <div class="kpi__row"><span class="kpi__value"><?php echo $t['c']; ?></span><span class="kpi__sub"><?php echo $t['pc']; ?>%</span></div>
    </div>
    <div class="kpi" style="border-top-color:#8a94ab">
      <div class="kpi__label">No contactados</div>
      <div class="kpi__row"><span class="kpi__value"><?php echo $t['nc']; ?></span><span class="kpi__sub"><?php echo $t['pnc']; ?>%</span></div>
    </div>
    <div class="kpi" style="border-top-color:#0f9d75">
      <div class="kpi__label">Ventas</div>
      <div class="kpi__row"><span class="kpi__value"><?php echo $t['v']; ?></span><span class="kpi__sub"><?php echo $t['pv']; ?>% de contactados</span></div>
    </div>
  </section>

  <!-- GRÁFICAS -->
  <section class="charts-grid">
    <?php
    $charts = [
      ['title' => 'Contactados vs no contactados', 'center' => 'Gestión', 'total' => $chart1Total,
       'items' => $chart1Items, 'cta' => true],
      ['title' => 'Ventas por producto', 'center' => 'Ventas', 'total' => $chart2Total,
       'items' => $chart2Items, 'cta' => false],
    ];
    foreach ($charts as $ch) {
      $chTotal = 0; foreach ($ch['items'] as $it) { $chTotal += $it['n']; }
    ?>
      <div class="chart-card">
        <div class="chart-card__head">
          <div class="chart-card__title"><?php echo $ch['title']; ?></div>
          <?php if ($ch['cta']) { ?>
            <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalMotivos">Ver motivos de contacto →</button>
          <?php } ?>
        </div>
        <div class="chart-body">
          <div class="donut" style="background:conic-gradient(<?php echo rep_donut($ch['items']); ?>)">
            <div class="donut__hole">
              <div class="donut__label"><?php echo $ch['center']; ?></div>
              <div class="donut__total"><?php echo $ch['total']; ?></div>
            </div>
          </div>
          <div class="legend">
            <?php foreach ($ch['items'] as $it) { ?>
              <div class="legend__item">
                <span class="legend__sw" style="background:<?php echo $it['color']; ?>"></span>
                <span class="legend__label"><?php echo $it['label']; ?></span>
                <span class="legend__n"><?php echo $it['n']; ?></span>
                <span class="legend__pct"><?php echo rep_pct($it['n'], $chTotal); ?>%</span>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    <?php } ?>
  </section>

  <!-- TABLA (escritorio) -->
  <section class="card data-card d-none d-lg-block">
    <div class="table-responsive">
      <table class="table table-hover data-table">
        <thead>
          <tr>
            <th class="ta-left">Operador</th>
            <th>Gestionados</th>
            <th>Contactados</th>
            <th>No contact.</th>
            <th>Ventas</th>
            <th class="ta-left">% Contacto</th>
            <th class="ta-right">% No Contacto</th>
            <th>% Ventas</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r) { ?>
            <tr class="op-row <?php echo $r['zero'] ? 'is-zero' : ''; ?>">
              <td class="ta-left">
                <div class="op">
                  <span class="op__avatar" style="background:<?php echo $r['avatar']; ?>"><?php echo $r['ini']; ?></span>
                  <span class="op__name"><?php echo $r['nombre']; ?></span>
                  <?php if ($r['crown']) { ?><span class="badge-top">Top ventas</span><?php } ?>
                </div>
              </td>
              <td><?php echo $r['g']; ?></td>
              <td><?php echo $r['c']; ?></td>
              <td><?php echo $r['nc']; ?></td>
              <td><span class="pill" style="background:<?php echo $r['vbg']; ?>;color:<?php echo $r['vfg']; ?>"><?php echo $r['v']; ?></span></td>
              <td class="ta-left" colspan="2" style="min-width:220px">
                <div class="bar"><div class="bar__fill" style="width:<?php echo $r['pc']; ?>%"></div></div>
                <div class="bar-meta"><span><?php echo $r['pc']; ?>%</span><span><?php echo $r['pnc']; ?>%</span></div>
              </td>
              <td><span class="pill pill--wide" style="background:<?php echo $r['vbg']; ?>;color:<?php echo $r['vfg']; ?>"><?php echo $r['pv']; ?>%</span></td>
            </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td class="ta-left">Totales</td>
            <td><?php echo $t['g']; ?></td>
            <td><?php echo $t['c']; ?></td>
            <td><?php echo $t['nc']; ?></td>
            <td><?php echo $t['v']; ?></td>
            <td class="ta-left" style="font-weight:500;font-size:13px"><?php echo $t['pc']; ?>% contactados · <?php echo $t['pnc']; ?>% no</td>
            <td><?php echo $t['pv']; ?>%</td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-top" id="opPagerWrap" style="display:none!important">
      <div class="small text-muted" id="opPageInfo"></div>
      <nav aria-label="Paginación de operadores">
        <ul class="pagination pagination-sm mb-0" id="opPager"></ul>
      </nav>
    </div>
  </section>

  <!-- CARDS (móvil) -->
  <section class="d-lg-none">
    <div class="cards-title">Desempeño por operador<?php echo $servLabel ? ' · ' . htmlspecialchars($servLabel) : ''; ?></div>
    <div class="op-cards">
      <?php foreach ($rows as $r) { ?>
        <div class="op-card <?php echo $r['zero'] ? 'is-zero' : ''; ?>">
          <div class="op-card__head">
            <span class="op__avatar" style="background:<?php echo $r['avatar']; ?>"><?php echo $r['ini']; ?></span>
            <div class="op-card__id">
              <div class="op-card__name"><?php echo $r['nombre']; ?></div>
              <?php if ($r['crown']) { ?><span class="badge-top">Top ventas</span><?php } ?>
            </div>
            <span class="pill pill--wide" style="background:<?php echo $r['vbg']; ?>;color:<?php echo $r['vfg']; ?>"><?php echo $r['pv']; ?>%</span>
          </div>
          <div class="op-card__grid">
            <div><div class="op-card__n"><?php echo $r['g']; ?></div><div class="op-card__l">Gest.</div></div>
            <div><div class="op-card__n"><?php echo $r['c']; ?></div><div class="op-card__l">Contact.</div></div>
            <div><div class="op-card__n"><?php echo $r['nc']; ?></div><div class="op-card__l">No cont.</div></div>
            <div><div class="op-card__n" style="color:<?php echo $r['vfg']; ?>"><?php echo $r['v']; ?></div><div class="op-card__l">Ventas</div></div>
          </div>
          <div class="bar"><div class="bar__fill" style="width:<?php echo $r['pc']; ?>%"></div></div>
          <div class="bar-meta"><span><?php echo $r['pc']; ?>% contactados</span><span><?php echo $r['pnc']; ?>% no</span></div>
        </div>
      <?php } ?>
      <div class="op-cards__totals">
        <span>Totales</span>
        <span style="font-size:13px;font-weight:500"><?php echo $t['g']; ?> gest. · <?php echo $t['c']; ?> cont. · <?php echo $t['v']; ?> ventas · <?php echo $t['pv']; ?>%</span>
      </div>
    </div>
    <nav class="justify-content-center mt-3" id="opCardsPagerWrap" style="display:none!important" aria-label="Paginación de operadores">
      <ul class="pagination pagination-sm mb-0" id="opCardsPager"></ul>
    </nav>
  </section>

  <!-- MODAL: Motivos de contacto -->
  <?php $pal2 = ['#2f5fc4', '#12306b', '#f5b83d', '#e8743b', '#1f6b3a', '#8a3fb0', '#0f9bb5', '#d94a7a', '#8f2626']; ?>
  <div class="modal fade" id="modalMotivos" tabindex="-1" aria-labelledby="modalMotivosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content rounded-4 border-0">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="modalMotivosLabel">Motivos de contacto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 align-items-start">
            <div class="col-12 col-lg-6">
              <?php rep_motivos_panel('No contacto', '#8f2626', isset($motivosNC) ? $motivosNC : [], $pal2); ?>
            </div>
            <div class="col-12 col-lg-6">
              <?php rep_motivos_panel('Contacto', '#1f6b3a', isset($motivosC) ? $motivosC : [], $pal2); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php } ?>

<script>
(function () {
  var fh = document.getElementById('fecha_h');
  if (fh) {
    fh.addEventListener('change', function () {
      var d = document.getElementById('fecha_d').value;
      var h = fh.value;
      if (d && h && h < d) {
        alert('La fecha "HASTA" es menor a la fecha "DESDE". Por favor verifique');
        fh.value = '';
      }
    });
  }
})();

function validateService() {
  var sel = document.getElementById('servicio');
  if (!sel || !sel.value) {
    alert('Por favor seleccione un servicio');
    return false;
  }
  return true;
}

// Paginación de operadores (10 por página) — tabla (escritorio) y cards (móvil)
(function () {
  function paginate(opts) {
    var rows = Array.prototype.slice.call(document.querySelectorAll(opts.rowSelector));
    var pager = document.getElementById(opts.pagerId);
    var wrap = document.getElementById(opts.wrapId);
    var info = opts.infoId ? document.getElementById(opts.infoId) : null;
    if (!rows.length || !pager || !wrap) { return; }

    var pageSize = 10;
    var pages = Math.ceil(rows.length / pageSize);
    if (pages <= 1) { return; } // 10 o menos: sin paginación

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
        info.textContent = 'Mostrando ' + s + '–' + e + ' de ' + rows.length + ' operadores';
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
  }

  paginate({ rowSelector: '.op-row',  pagerId: 'opPager',      wrapId: 'opPagerWrap',      infoId: 'opPageInfo' });
  paginate({ rowSelector: '.op-card', pagerId: 'opCardsPager', wrapId: 'opCardsPagerWrap' });
})();

// Producto (depende del servicio seleccionado)
(function () {
  var servicio = document.getElementById('servicio');
  var producto = document.getElementById('producto');
  if (!servicio || !producto) { return; }

  servicio.addEventListener('change', function () {
    producto.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
    if (!servicio.value) { return; }

    var cuerpo = new URLSearchParams();
    cuerpo.set('id', servicio.value);

    fetch('?view=reportes&mode=producto', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: cuerpo.toString()
    })
      .then(function (resp) { return resp.json(); })
      .then(function (datos) {
        if (!Array.isArray(datos)) { return; }
        datos.forEach(function (item) {
          var opt = document.createElement('option');
          opt.value = item.id;
          opt.textContent = item.descripcion;
          producto.appendChild(opt);
        });
      })
      .catch(function () { /* el combo queda en "Seleccione..." si falla la carga */ });
  });
})();
</script>
