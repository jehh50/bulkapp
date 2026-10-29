<?php
$mensajeHtml = '';
$toastClass = 'success';
switch (isset($_GET['mensaje']) ? $_GET['mensaje'] : '') {
  case 'exito':        $mensajeHtml = '¡Éxito! El archivo se ha procesado correctamente.';          $toastClass = 'success'; break;
  case 'errorFormato': $mensajeHtml = 'Error de formato: el delimitador del archivo es incorrecto. Verifique que sea un CSV (punto y coma).'; $toastClass = 'error'; break;
  case 'errorArchivo': $mensajeHtml = 'No se recibió el archivo o hubo un error al subirlo.';      $toastClass = 'error'; break;
  case 'errorProceso': $mensajeHtml = 'Ocurrió un error al guardar los registros. Intente nuevamente.'; $toastClass = 'error'; break;
  case 'sinDatos':     $mensajeHtml = 'El archivo no contenía filas válidas para importar.';     $toastClass = 'error'; break;
}
?>

<style>
  /* ---------- Carga de base de datos (BS5) ---------- */
  .ca-wrap{max-width:640px;margin:0 auto}
  .ca-card{background:#fff;border-radius:16px;box-shadow:0 8px 24px rgba(18,48,107,.12);border-top:5px solid var(--gold);padding:26px;display:flex;flex-direction:column;gap:18px}

  .ca-lbl{display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:600;color:var(--ink);margin:0}
  .ca-card .form-select{height:44px;border-radius:10px;font-size:14px;color:var(--ink)}
  .ca-card .form-select:focus{border-color:var(--blue-700);box-shadow:0 0 0 .2rem rgba(47,95,196,.2)}

  .ca-filebox{display:flex;align-items:center;gap:12px;border:1px dashed #d1d5db;border-radius:10px;padding:14px 16px;background:#fdf6e4}
  .ca-filebox__icon{flex:0 0 42px;width:42px;height:42px;border-radius:50%;background:var(--blue-700);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px}
  .ca-filebox__text{min-width:0}
  .ca-filebox__title{font-size:14px;font-weight:600;color:var(--ink)}
  .ca-filebox__sub{font-size:12.5px;color:#6b7280}

  .ca-file{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);border:0}
  .ca-file-btn{display:inline-flex;align-items:center;justify-content:center;height:44px;padding:0 18px;border:0;border-radius:10px;background:var(--blue-700);color:#fff;font-size:14px;font-weight:600;cursor:pointer;white-space:nowrap}
  .ca-file-btn:hover{background:var(--blue-900)}
  .ca-file-name{font-size:13px;font-weight:500;color:var(--ink);overflow-wrap:anywhere}
  .ca-sub{display:block;font-size:12.5px;font-weight:400;color:#6b7280}
  .has-file .ca-file-btn{background:var(--gold);color:var(--ink)}
  .has-file .ca-file-btn:hover{background:#e9a924}

  .ca-alert{display:flex;gap:10px;align-items:flex-start;padding:12px 16px;border-radius:10px;background:#eef4ff;border:1px solid #d5e2fb;font-size:13.5px;color:#334176}
  .ca-alert__text{min-width:0;overflow-wrap:anywhere}
  .ca-error{padding:12px 16px;border-radius:10px;background:#fdecec;border:1px solid #f3c2c2;font-size:14px;color:#b42318;overflow-wrap:anywhere}

  .ca-actions{display:flex;gap:10px;flex-wrap:wrap;border-top:1px solid var(--line);padding-top:18px}
  .ca-actions .btn-primary{height:46px;padding:0 26px}

  .ca-toast{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 auto 14px;padding:12px 16px;border-radius:12px;font-size:14px;box-shadow:0 4px 12px rgba(18,48,107,.1);max-width:640px}
  .ca-toast--success{background:#d3f0e6;border:1px solid #a9dcc8}
  .ca-toast--error{background:#fdecec;border:1px solid #f3c2c2}
  .ca-toast__close{border:0;background:transparent;font-size:18px;cursor:pointer;color:var(--ink);width:32px;height:32px;flex:none}
</style>

<?php if ($mensajeHtml) { ?>
  <div class="ca-toast ca-toast--<?php echo $toastClass; ?>" id="caToast">
    <span><?php echo $toastClass === 'success' ? '✓ ' : '⚠ '; ?><?php echo htmlspecialchars($mensajeHtml); ?></span>
    <button type="button" class="ca-toast__close" id="caToastClose" aria-label="Cerrar">×</button>
  </div>
<?php } ?>

<div class="ca-wrap">
  <section class="ca-card">

    <div>
      <h2 style="margin:0;font-size:19px;font-weight:600;color:var(--ink)">Importar CSV</h2>
      <div style="font-size:13.5px;color:#6b7280;margin-top:4px">Selecciona el servicio y carga el archivo de la base de clientes.</div>
    </div>

    <div class="ca-alert"><span class="ca-alert__text">ℹ Solo se aceptan archivos con extensión <strong>.csv</strong> (delimitado por <strong>;</strong>). Puedes descargar el <a href="?view=carga_archivo&mode=download">archivo modelo</a>.</span></div>

    <form id="caForm" action="?view=carga_archivo&mode=registro" method="POST" enctype="multipart/form-data" autocomplete="off">
      <div class="d-flex flex-column gap-4">
        <label class="ca-lbl">Servicio
          <select class="form-select" name="servicio" required>
            <option value="" selected disabled>Seleccione…</option>
            <?php foreach ($servicio as $s) { ?>
              <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['descripcion']); ?></option>
            <?php } ?>
          </select>
        </label>

        <label class="ca-lbl">Archivo
          <div class="ca-filebox" id="caFileBox">
            <div class="ca-filebox__icon">⇪</div>
            <div class="ca-filebox__text">
              <div class="ca-filebox__title" id="caFileName">Adjunte el archivo</div>
              <div class="ca-filebox__sub">Formato .csv separado por punto y coma</div>
              <span class="ca-sub"></span>
            </div>
            <span class="ca-file-btn ms-auto">Examinar…</span>
            <input type="file" name="archivo" id="caFile" class="ca-file" accept=".csv" required>
          </div>
        </label>

        <div id="error1" class="ca-error" style="display:none">No se ha seleccionado ningún archivo</div>
        <div id="error2" class="ca-error" style="display:none">Extensión incorrecta. Solo se permite .csv</div>

        <div class="ca-actions">
          <button id="botonS" type="submit" class="btn btn-primary">Subir archivo</button>
        </div>
      </div>
    </form>

  </section>
</div>

<script>
(function () {
  'use strict';

  var fileInput = document.getElementById('caFile');
  var fileBox = document.getElementById('caFileBox');
  var fileName = document.getElementById('caFileName');
  var error1 = document.getElementById('error1');
  var error2 = document.getElementById('error2');
  var botonS = document.getElementById('botonS');
  var form = document.getElementById('caForm');
  var toast = document.getElementById('caToast');
  var toastClose = document.getElementById('caToastClose');

  function extensionOk() {
    var archivo = fileInput.value;
    var extension = archivo.substring(archivo.lastIndexOf('.')).toLowerCase();
    return extension === '.csv';
  }

  // Validar extensión al elegir archivo
  fileInput.addEventListener('change', function () {
    if (!fileInput.value) {
      fileBox.classList.remove('has-file');
      fileName.textContent = 'Adjunte el archivo';
      error2.style.display = 'none';
      return;
    }

    if (extensionOk()) {
      error2.style.display = 'none';
      fileBox.classList.add('has-file');
      fileName.textContent = fileInput.files[0].name;
    } else {
      error2.style.display = 'block';
      fileBox.classList.remove('has-file');
      fileName.textContent = 'Adjunte el archivo';
    }
  });

  // Abrir el selector de archivos al hacer clic sobre la caja
  fileBox.addEventListener('click', function () {
    fileInput.click();
  });

  form.addEventListener('submit', function (e) {
    if (!fileInput.value || !fileInput.files.length) {
      e.preventDefault();
      error1.style.display = 'block';
      fileInput.focus();
      return;
    }
    error1.style.display = 'none';
    if (!extensionOk()) {
      e.preventDefault();
      error2.style.display = 'block';
      return;
    }
    error2.style.display = 'none';
    botonS.disabled = true;
    botonS.textContent = 'Procesando…';
  });

  if (toast && toastClose) {
    toastClose.addEventListener('click', function () {
      toast.style.display = 'none';
    });
  }
})();
</script>
