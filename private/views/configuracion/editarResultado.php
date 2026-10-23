<?php
$mensajeResultado = isset($_GET['mensaje']) ? (string) $_GET['mensaje'] : '';
$alertaResultado = null;

switch ($mensajeResultado) {
  case 'exito':
    $alertaResultado = ['success', 'Registro actualizado correctamente.'];
    break;
  case 'eliminado':
    $alertaResultado = ['success', 'Registro eliminado correctamente.'];
    break;
  case 'error':
    $alertaResultado = ['danger', 'No se pudo completar la operación. Intenta nuevamente.'];
    break;
}
?>

<style>
  .er-shell{max-width:1120px;margin:0 auto}
  .er-card{background:#fff;border:0;border-radius:16px;box-shadow:0 8px 24px rgba(18,48,107,.12);padding:24px}
  .er-shell .form-control,.er-shell .form-select{height:44px;border-radius:10px;font-size:14px;color:var(--ink)}
  .er-shell .form-control:focus,.er-shell .form-select:focus{border-color:var(--blue-700);box-shadow:0 0 0 .2rem rgba(47,95,196,.2)}
  .er-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--ink)}
  .er-search-btn{height:44px;border-radius:10px;font-weight:600;min-width:132px}
  .er-reset{border-radius:10px;height:44px}
  .er-results{margin-top:18px}
  .er-results .accordion-item{border:0;border-radius:14px!important;box-shadow:0 1px 3px rgba(16,24,40,.08);overflow:hidden;margin-bottom:14px}
  .er-results .accordion-button{background:#fff;font-size:15px;font-weight:600;color:var(--ink);padding:18px 20px;box-shadow:none}
  .er-results .accordion-button:not(.collapsed){background:#f4f7fd;color:var(--blue-900)}
  .er-results .accordion-button:focus{box-shadow:0 0 0 .2rem rgba(47,95,196,.2);border-color:transparent}
  .er-results .accordion-button::after{filter:none}
  .er-result__head{display:flex;align-items:center;gap:10px;flex-wrap:wrap;min-width:0;text-align:left}
  .er-result__name{overflow-wrap:anywhere}
  .er-chip{display:inline-block;font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:20px;background:#e8eefb;color:var(--blue-700);white-space:nowrap}
  .er-chip--muted{background:#f1f4fa;color:#5b6475}
  .er-results .accordion-body{padding:20px}
  .er-field{display:flex;flex-direction:column;gap:6px}
  .er-field .form-label{font-size:12px;font-weight:600;color:#5b6475;margin:0}
  .er-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;border-top:1px solid var(--line);padding-top:16px;margin-top:20px}
  .er-empty{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(16,24,40,.08);padding:38px 24px;text-align:center}
  .er-empty__icon{width:52px;height:52px;border-radius:50%;background:#e8eefb;color:var(--blue-700);display:inline-flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:12px}
  .er-loading{width:16px;height:16px}
  @media (max-width:767px){
    .er-card{padding:18px}
    .er-results .accordion-button{padding:16px}
    .er-results .accordion-body{padding:16px}
    .er-actions{justify-content:stretch}
    .er-actions .btn{flex:1 1 auto}
  }
</style>

<div class="er-shell">

  <section class="er-card">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="h4 fw-semibold mb-1">Edición de resultados</h1>
        <p class="text-muted small mb-0">Busca una venta por cédula para corregir los datos del cliente o del producto.</p>
      </div>
      <span class="badge rounded-pill text-bg-light border px-3 py-2">Administración</span>
    </div>

    <?php if ($alertaResultado !== null) { ?>
      <div class="alert alert-<?php echo $alertaResultado[0]; ?> alert-dismissible fade show" role="alert" id="message">
        <?php echo htmlspecialchars($alertaResultado[1], ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php } ?>

    <form id="erSearchForm" class="row g-3 align-items-end" autocomplete="off" novalidate>
      <div class="col-12 col-md-5 col-lg-4">
        <label class="form-label er-label" for="cedula">Cédula del cliente</label>
        <input type="text" class="form-control" id="cedula" name="cedula"
               placeholder="12658457" maxlength="9" inputmode="numeric"
               aria-describedby="cedulaHelp">
        <div id="cedulaHelp" class="form-text">Sin puntos ni guiones.</div>
      </div>
      <div class="col-12 col-md-auto">
        <div class="d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-primary er-search-btn" id="btn-buscar">
            <span class="spinner-border spinner-border-sm er-loading d-none" id="erSpinner" aria-hidden="true"></span>
            <span id="erSearchLabel">Buscar</span>
          </button>
          <button type="button" class="btn btn-outline-secondary er-reset" id="btn-limpiar">Limpiar</button>
        </div>
      </div>
    </form>

    <div id="erFeedback" class="mt-3 d-none" role="alert" aria-live="polite"></div>
  </section>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 mb-2">
    <h2 class="h6 fw-semibold mb-0">Resultados</h2>
    <span class="badge rounded-pill text-bg-light border" id="erCount" aria-live="polite">0 registros</span>
  </div>

  <div id="accordion" class="accordion er-results"></div>

  <div id="erEmpty" class="er-empty d-none">
    <div class="er-empty__icon" aria-hidden="true">⌕</div>
    <div class="fw-semibold mb-1">Sin resultados</div>
    <p class="text-muted small mb-0">No encontramos ventas asociadas a esa cédula. Verifica el número e intenta de nuevo.</p>
  </div>

</div>

<div id="modals"></div>

<script src="<?php echo asset('public/js/edit.js'); ?>"></script>
