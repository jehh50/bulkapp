<?php
// Archivos XML disponibles por servicio (carpeta = nombre del servicio en minúsculas, sin espacios)
$xmlData = [];
foreach ($servicio as $s) {
  $folder = strtolower(trim(str_replace(' ', '', $s['descripcion'])));
  $dir = "public/archivos/{$folder}";
  $files = [];
  if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
      if ($f === '.' || $f === '..') { continue; }
      if (is_file("{$dir}/{$f}")) { $files[] = $f; }
    }
    rsort($files); // más recientes primero
  }
  $xmlData[$s['id']] = ['folder' => $folder, 'files' => $files];
}
$showOk = isset($_GET['ok']);
?>

<div class="mx-auto" style="max-width:1000px">
  <div class="row g-4 align-items-start">

    <!-- GENERAR -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow rounded-4 h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3 mb-4">
            <span class="d-inline-flex align-items-center justify-content-center fw-bold" style="width:40px;height:40px;border-radius:10px;background:#e8eefb;color:var(--blue-700);font-size:13px">&lt;/&gt;</span>
            <div>
              <div class="fs-6 fw-semibold">Generar XML</div>
              <div class="small text-muted">Crea el archivo del día elegido</div>
            </div>
          </div>

          <form id="formGenerar" class="d-flex flex-column gap-3" onsubmit="return generarXML(event);">
            <div>
              <label class="form-label fw-semibold" for="servicio">Servicio</label>
              <select class="form-select form-select-lg" name="servicio" id="servicio" style="font-size:15px">
                <option value="" selected>Seleccione un servicio…</option>
                <?php foreach ($servicio as $s) { ?>
                  <option value="<?= $s['id']; ?>"><?= htmlspecialchars($s['descripcion']); ?></option>
                <?php } ?>
              </select>
            </div>
            <div>
              <label class="form-label fw-semibold" for="fecha_">Fecha</label>
              <input type="date" class="form-control form-control-lg" name="fecha_" id="fecha_" value="<?= date('Y-m-d'); ?>" style="font-size:15px">
            </div>
            <button type="submit" class="btn btn-primary btn-lg" id="btnGenerar">Generar XML</button>

            <div class="alert alert-danger mb-0 d-none" id="genError" role="alert"></div>
            <div class="alert alert-success mb-0 <?= $showOk ? '' : 'd-none'; ?>" id="genOk" role="alert">
              <?= $showOk ? 'XML generado correctamente. Ya puedes descargarlo.' : ''; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- DESCARGAR -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow rounded-4 h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3 mb-4">
            <span class="d-inline-flex align-items-center justify-content-center fw-bold" style="width:40px;height:40px;border-radius:10px;background:#fdf1d4;color:var(--ink);font-size:20px">&darr;</span>
            <div>
              <div class="fs-6 fw-semibold">Descargar XML</div>
              <div class="small text-muted">Elige servicio y archivo</div>
            </div>
          </div>

          <div class="d-flex flex-column gap-3">
            <div>
              <label class="form-label fw-semibold" for="dSvc">Servicio</label>
              <select class="form-select form-select-lg" id="dSvc" style="font-size:15px">
                <option value="" selected>Seleccione un servicio…</option>
                <?php foreach ($servicio as $s) { ?>
                  <option value="<?= $s['id']; ?>"><?= htmlspecialchars($s['descripcion']); ?></option>
                <?php } ?>
              </select>
            </div>
            <div>
              <label class="form-label fw-semibold" for="dFile">Archivo</label>
              <select class="form-select form-select-lg" id="dFile" style="font-size:15px" disabled>
                <option value="" selected>Primero elige un servicio</option>
              </select>
            </div>
            <button type="button" class="btn btn-primary btn-lg" id="btnDescargar" onclick="descargarXML();" disabled>Descargar XML</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
var xmlData = <?= json_encode($xmlData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

// ---------- Generar ----------
function generarXML(ev) {
  ev.preventDefault();
  var svc = document.getElementById('servicio').value;
  var fecha = document.getElementById('fecha_').value;
  var err = document.getElementById('genError');
  var ok = document.getElementById('genOk');
  err.classList.add('d-none'); ok.classList.add('d-none');

  if (!svc || !fecha) {
    err.textContent = 'Selecciona un servicio y una fecha.';
    err.classList.remove('d-none');
    return false;
  }

  var btn = document.getElementById('btnGenerar');
  btn.disabled = true; btn.textContent = 'Generando…';

  var body = new URLSearchParams({ fecha_: fecha, servicio: svc });
  fetch('?view=xml&mode=xml', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data && data.response === 'true') {
        window.location.href = '?view=xml&mode=index&ok=1';
      } else {
        throw new Error('No se generó el archivo');
      }
    })
    .catch(function () {
      btn.disabled = false; btn.textContent = 'Generar XML';
      err.textContent = 'No se pudo generar el archivo. Verifica que haya ventas para la fecha y el servicio seleccionados.';
      err.classList.remove('d-none');
    });

  return false;
}

// ---------- Descargar ----------
(function () {
  var dSvc = document.getElementById('dSvc');
  var dFile = document.getElementById('dFile');
  var btn = document.getElementById('btnDescargar');

  function refreshBtn() { btn.disabled = !(dSvc.value && dFile.value); }

  dSvc.addEventListener('change', function () {
    var info = xmlData[dSvc.value];
    dFile.innerHTML = '';
    if (!dSvc.value || !info || !info.files.length) {
      var o = document.createElement('option');
      o.value = ''; o.textContent = dSvc.value ? 'No hay archivos para este servicio' : 'Primero elige un servicio';
      dFile.appendChild(o);
      dFile.disabled = true;
    } else {
      var ph = document.createElement('option');
      ph.value = ''; ph.textContent = 'Seleccione un archivo…';
      dFile.appendChild(ph);
      info.files.forEach(function (f) {
        var o = document.createElement('option');
        o.value = f; o.textContent = f;
        dFile.appendChild(o);
      });
      dFile.disabled = false;
    }
    refreshBtn();
  });

  dFile.addEventListener('change', refreshBtn);

  window.descargarXML = function () {
    var info = xmlData[dSvc.value];
    if (!info || !dFile.value) { return; }
    var url = 'public/archivos/' + info.folder + '/' + encodeURIComponent(dFile.value);
    var a = document.createElement('a');
    a.href = url; a.download = dFile.value;
    document.body.appendChild(a); a.click(); a.remove();
  };
})();
</script>
