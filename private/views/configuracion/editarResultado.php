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

<div class="container-xl px-0">

  <section class="card border-0 shadow-sm rounded-4 p-4">
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

    <form id="erSearchForm" class="row g-2" autocomplete="off" novalidate>
      <div class="col-12">
        <label class="form-label small fw-semibold text-uppercase mb-0" for="cedula">Cédula del cliente</label>
      </div>
      <div class="col-12 col-md-5 col-lg-4">
        <input type="text" class="form-control" id="cedula" name="cedula"
               placeholder="12658457" maxlength="9" inputmode="numeric"
               aria-describedby="cedulaHelp">
      </div>
      <div class="col-12 col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4" id="btn-buscar">
          <span class="spinner-border spinner-border-sm d-none" id="erSpinner" aria-hidden="true"></span>
          <span id="erSearchLabel">Buscar</span>
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btn-limpiar">Limpiar</button>
      </div>
    </form>
    <div id="cedulaHelp" class="form-text">Sin puntos ni guiones.</div>

    <div id="erFeedback" class="mt-3 d-none" role="alert" aria-live="polite"></div>
  </section>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 mb-2">
    <h2 class="h6 fw-semibold mb-0">Resultados</h2>
    <span class="badge rounded-pill text-bg-light border" id="erCount" aria-live="polite">0 registros</span>
  </div>

  <div id="accordion" class="accordion"></div>

  <div id="erEmpty" class="card border-0 shadow-sm rounded-4 text-center py-5 px-3 d-none">
    <div class="fs-1 text-primary mb-2" aria-hidden="true">⌕</div>
    <div class="fw-semibold mb-1">Sin resultados</div>
    <p class="text-muted small mb-0">No encontramos ventas asociadas a esa cédula. Verifica el número e intenta de nuevo.</p>
  </div>

</div>

<div id="modals"></div>

<script src="<?php echo asset('public/js/edit.js'); ?>"></script>
