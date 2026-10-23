<?php
if (!function_exists('dnum')) {
  function dnum($n) { return number_format((int)$n, 0, ',', '.'); }
}
if (!function_exists('dpct')) {
  function dpct($a, $b) { return $b ? number_format($a / $b * 100, 1, ',', '.') . '%' : '0%'; }
}
if (!function_exists('mesLabel')) {
  function mesLabel($ym) {
    $nom = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $t = strtotime($ym . '-01');
    return $nom[(int)date('n', $t)] . ' ' . date('Y', $t);
  }
}

$servicios = isset($servicios) && is_array($servicios) ? $servicios : [];
$diarias   = isset($diarias) && is_array($diarias) ? $diarias : [];
$meses     = isset($meses) && is_array($meses) ? $meses : [];
if (!in_array($mes, $meses)) { array_unshift($meses, $mes); }

// Ventas por día agrupadas por servicio
$byDay = [];
foreach ($diarias as $d) { $byDay[$d['id']][$d['dia']] = (int)$d['ventas']; }

// Lista de días del rango consultado
$days = [];
$cur = strtotime($desde); $end = strtotime($hasta);
while ($cur <= $end) { $days[] = date('Y-m-d', $cur); $cur = strtotime('+1 day', $cur); }
$nDays = max(1, count($days));

$hcs = ['#12306b', '#8a3fb0', '#0b7a5c', '#b8470f', '#0e7490', '#b4235a'];

$tg = 0; $tc = 0; $tv = 0; $rows = [];
foreach ($servicios as $i => $s) {
  $g = (int)$s['gestionados']; $c = (int)$s['efectivos']; $v = (int)$s['ventas'];
  $tg += $g; $tc += $c; $tv += $v;
  $cr = $g ? $c / $g : 0;   // contactabilidad
  $vr = $c ? $v / $c : 0;   // ventas sobre contactados
  $sd = isset($byDay[$s['id']]) ? $byDay[$s['id']] : [];
  $maxV = 0; foreach ($days as $dd) { $maxV = max($maxV, isset($sd[$dd]) ? $sd[$dd] : 0); }
  $bars = [];
  foreach ($days as $k => $dd) {
    $vv = isset($sd[$dd]) ? $sd[$dd] : 0;
    $bars[] = [
      'h' => $maxV ? max(6, round($vv / $maxV * 100)) : 6,
      'o' => round(0.35 + 0.65 * ($k + 1) / $nDays, 2),
    ];
  }
  $rows[] = [
    'id' => $s['id'],
    'name' => trim($s['nombre']), 'g' => $g, 'c' => $c, 'v' => $v,
    'cpct' => dpct($c, $g), 'vpct' => dpct($v, $c),
    'cdeg' => round($cr * 360),
    'vdeg' => round(min(1, $vr * 3) * 360),       // amplificado x3 para visibilidad
    'vbarw' => min(100, $vr * 3 * 100),
    'hc' => $hcs[$i % count($hcs)],
    'bars' => $bars,
  ];
}
$corte = date('d/m/Y', strtotime($hasta));
$hoy   = date('Y-m-d');

// Con menos de 3 servicios, agrandamos las tarjetas (ocupan todo el ancho)
$nSvc = count($rows);
$gridClass = $nSvc === 1 ? 'svc-grid--1' : ($nSvc === 2 ? 'svc-grid--2' : '');
?>

<style>
  .dash-top{display:flex;justify-content:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:20px}
  .dash-period{display:flex;align-items:center;gap:14px;background:var(--blue-900);color:#fff;border-radius:14px;padding:10px 18px}
  .dash-period__dot{width:10px;height:10px;border-radius:50%;background:var(--gold)}
  .dash-period__lbl{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted)}
  .dash-period__sel{margin-top:2px;background:transparent;border:0;color:#fff;font-family:inherit;font-size:20px;font-weight:700;cursor:pointer;padding:0}
  .dash-period__sel:focus{outline:none}
  .dash-period__cut{font-size:15px;font-weight:500}
  .dash-period__sep{width:1px;align-self:stretch;background:var(--blue-600);margin:0 2px}

  .dash-bar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
  .dash-bar__count{font-size:16px;font-weight:600}
  .dash-toggle{display:inline-flex;background:#e5e9f2;border-radius:10px;padding:3px;gap:2px}
  .dash-toggle button{height:34px;padding:0 16px;border:0;border-radius:8px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;background:transparent;color:var(--ink)}
  .dash-toggle button.is-active{background:#fff;box-shadow:0 1px 2px rgba(16,24,40,.12)}

  .svc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,340px),1fr));gap:16px;margin-bottom:20px}
  .svc-grid--2{grid-template-columns:repeat(2,minmax(0,1fr))}
  .svc-grid--1{grid-template-columns:minmax(0,640px)}
  @media (max-width:767px){.svc-grid--2{grid-template-columns:1fr}}
  .svc{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(16,24,40,.08);overflow:hidden}
  .svc__head{color:#fff;padding:14px 22px;font-size:16px;font-weight:600}
  .svc__body{padding:24px 22px;display:flex;flex-direction:column;gap:24px}
  .svc__donuts{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .svc__d{display:flex;align-items:center;gap:12px}
  .svc__donut{width:92px;height:92px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex:none}
  .svc__hole{width:66px;height:66px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700}
  .svc__dlbl{font-size:11.5px;font-weight:600;text-transform:uppercase;line-height:1.3}
  .svc__foot{border-top:1px solid var(--line);padding-top:20px;display:flex;align-items:flex-end;justify-content:space-between;gap:14px}
  .svc__vlbl{font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px}
  .svc__v{font-size:32px;font-weight:700;line-height:1.1}
  .svc__bars{display:flex;align-items:flex-end;gap:3px;height:64px;flex:1;max-width:200px}
  .svc__bars span{flex:1;background:var(--blue-700);border-radius:2px 2px 0 0;min-width:0}
  .svc__ctaform{margin:0}
  .svc__cta{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid #d7deee;background:#fff;color:var(--blue-700);border-radius:10px;padding:11px 14px;font-family:inherit;font-size:13.5px;font-weight:600;cursor:pointer;transition:background .15s,border-color .15s}
  .svc__cta:hover{background:#eef2fb;border-color:#c3cee8}

  .cmp-table{width:100%;border-collapse:collapse;font-size:14px}
  .cmp-table thead th{background:#f1f4fa;font-size:11.5px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;padding:12px;text-align:left;white-space:nowrap}
  .cmp-table thead th.ta-r{text-align:right}
  .cmp-table tbody td{border-top:1px solid var(--line);padding:12px}
  .cmp-table tbody td.ta-r{text-align:right}
  .cmp-table .prog{display:flex;align-items:center;gap:10px}
  .cmp-table .prog__track{flex:1;height:8px;border-radius:4px;background:#e5e9f2;overflow:hidden}
  .cmp-table .prog__fill{height:100%}
  .cmp-table .prog__n{width:58px;text-align:right;font-weight:500}
  .cmp-table tfoot td{background:var(--blue-900);color:#fff;font-weight:600;padding:14px 12px}
</style>

<!-- Selector de mes + corte -->
<div class="dash-top">
  <div class="dash-period">
    <div class="dash-period__dot"></div>
    <div>
      <div class="dash-period__lbl">Mes</div>
      <select class="dash-period__sel" onchange="location.href='?view=dashboard&mode=index&mes='+this.value">
        <?php foreach ($meses as $ym) { ?>
          <option value="<?= $ym; ?>"<?= $ym === $mes ? ' selected' : ''; ?> style="color:#252525"><?= mesLabel($ym); ?></option>
        <?php } ?>
      </select>
    </div>
    <div class="dash-period__sep"></div>
    <div>
      <div class="dash-period__lbl">Corte</div>
      <div class="dash-period__cut"><?= $corte; ?></div>
    </div>
  </div>
</div>

<?php if (empty($rows)) { ?>
  <div class="data-card"><div class="data-empty">No hay gestión registrada en <?= mesLabel($mes); ?>.</div></div>
<?php } else { ?>

  <!-- KPIs -->
  <div class="kpi-grid">
    <div class="kpi" style="border-top-color:#8a3fb0">
      <div class="kpi__label">% Contactabilidad acumulada</div>
      <div class="kpi__row"><span class="kpi__value"><?= dpct($tc, $tg); ?></span></div>
      <div class="kpi__sub"><?= dnum($tc); ?> de <?= dnum($tg); ?> gestionados</div>
    </div>
    <div class="kpi" style="border-top-color:#0f9d75">
      <div class="kpi__label">% Ventas acumulada</div>
      <div class="kpi__row"><span class="kpi__value"><?= dpct($tv, $tc); ?></span></div>
      <div class="kpi__sub"><?= dnum($tv); ?> sobre <?= dnum($tc); ?> contactados</div>
    </div>
    <div class="kpi" style="border-top-color:#f5b83d">
      <div class="kpi__label">Total ventas acumuladas</div>
      <div class="kpi__row"><span class="kpi__value"><?= dnum($tv); ?></span></div>
      <div class="kpi__sub">todos los servicios</div>
    </div>
  </div>

  <!-- Barra: conteo + toggle -->
  <div class="dash-bar">
    <div class="dash-bar__count"><?= count($rows); ?> servicio<?= count($rows) === 1 ? '' : 's'; ?></div>
    <div class="dash-toggle">
      <button type="button" id="btnCards" class="is-active" onclick="dashView('cards')">Tarjetas</button>
      <button type="button" id="btnTable" onclick="dashView('table')">Comparativo</button>
    </div>
  </div>

  <!-- Vista Tarjetas -->
  <section id="dashCards" class="svc-grid <?= $gridClass; ?>">
    <?php foreach ($rows as $r) { ?>
      <div class="svc">
        <div class="svc__head" style="background:<?= $r['hc']; ?>"><?= htmlspecialchars($r['name']); ?></div>
        <div class="svc__body">
          <div class="svc__donuts">
            <div class="svc__d">
              <div class="svc__donut" style="background:conic-gradient(#1a4391 <?= $r['cdeg']; ?>deg,#e5e9f2 0)">
                <div class="svc__hole"><?= $r['cpct']; ?></div>
              </div>
              <div class="svc__dlbl">% Contacta-<br>bilidad</div>
            </div>
            <div class="svc__d">
              <div class="svc__donut" style="background:conic-gradient(#0f9d75 <?= $r['vdeg']; ?>deg,#e5e9f2 0)">
                <div class="svc__hole"><?= $r['vpct']; ?></div>
              </div>
              <div class="svc__dlbl">% Ventas</div>
            </div>
          </div>
          <div class="svc__foot">
            <div>
              <div class="svc__vlbl">Ventas acum.</div>
              <div class="svc__v"><?= dnum($r['v']); ?></div>
            </div>
            <div class="svc__bars">
              <?php foreach ($r['bars'] as $b) { ?>
                <span style="height:<?= $b['h']; ?>%;opacity:<?= $b['o']; ?>"></span>
              <?php } ?>
            </div>
          </div>
          <form class="svc__ctaform" method="post" action="?view=reportes&mode=acumulado">
            <input type="hidden" name="fecha_d" value="<?= $hoy; ?>">
            <input type="hidden" name="fecha_h" value="<?= $hoy; ?>">
            <input type="hidden" name="servicio" value="<?= $r['id']; ?>">
            <button type="submit" class="svc__cta">Ver acumulado de gestión de hoy &rarr;</button>
          </form>
        </div>
      </div>
    <?php } ?>
  </section>

  <!-- Vista Comparativo -->
  <section id="dashTable" class="data-card" style="display:none">
    <div class="table-responsive">
      <table class="cmp-table">
        <thead>
          <tr>
            <th>Servicio</th>
            <th class="ta-r">Gestionados</th>
            <th style="width:24%">% Contactabilidad</th>
            <th style="width:24%">% Ventas</th>
            <th class="ta-r">Ventas acum.</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r) { ?>
            <tr>
              <td style="font-weight:600"><?= htmlspecialchars($r['name']); ?></td>
              <td class="ta-r"><?= dnum($r['g']); ?></td>
              <td>
                <div class="prog">
                  <div class="prog__track"><div class="prog__fill" style="background:#1a4391;width:<?= $r['g'] ? round($r['c'] / $r['g'] * 100, 1) : 0; ?>%"></div></div>
                  <span class="prog__n"><?= $r['cpct']; ?></span>
                </div>
              </td>
              <td>
                <div class="prog">
                  <div class="prog__track"><div class="prog__fill" style="background:#0f9d75;width:<?= $r['vbarw']; ?>%"></div></div>
                  <span class="prog__n"><?= $r['vpct']; ?></span>
                </div>
              </td>
              <td class="ta-r" style="font-weight:700;font-size:16px"><?= dnum($r['v']); ?></td>
            </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td>Total</td>
            <td class="ta-r"><?= dnum($tg); ?></td>
            <td><?= dpct($tc, $tg); ?></td>
            <td><?= dpct($tv, $tc); ?></td>
            <td class="ta-r"><?= dnum($tv); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </section>

<?php } ?>

<script>
  function dashView(v) {
    var cards = document.getElementById('dashCards');
    var table = document.getElementById('dashTable');
    var bc = document.getElementById('btnCards');
    var bt = document.getElementById('btnTable');
    if (!cards || !table) { return; }
    var isCards = v === 'cards';
    cards.style.display = isCards ? 'grid' : 'none';
    table.style.display = isCards ? 'none' : 'block';
    bc.classList.toggle('is-active', isCards);
    bt.classList.toggle('is-active', !isCards);
  }
</script>
