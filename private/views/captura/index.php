<?php
/**
 * Captura de formularios dinámicos (Constructor, Fase 3).
 *
 * Como en banca: card «Datos de la búsqueda» (BD vigente, solo lectura) a la izquierda
 * y el formulario a la derecha. Sin BD vigente no hay card y el formulario se centra.
 * La lógica vive en public/js/captura.js (+ formDinamico.js).
 *
 * Variables: $form (formulario activo del servicio o null), $bd (BD vigente o null).
 */
$claveBd = null;
if ($bd !== null) {
  foreach ($bd['columnas'] as $col) {
    if ($col['k'] === $bd['busqueda']) {
      $claveBd = $col;
    }
  }
}
?>
<link rel="stylesheet" href="<?php echo asset('public/css/captura.css'); ?>">

<?php if ($form === null) { ?>
  <section class="cap-card cap-card--green cap-solo">
    <div class="cap-empty">
      <div class="cap-empty__icon">!</div>
      <div class="cap-empty__title">Sin formulario activo</div>
      <div class="cap-empty__text">Tu servicio todavía no tiene un formulario activo. Consulta con el administrador.</div>
    </div>
  </section>
<?php } else { ?>

<div class="cap-grid<?php echo ($bd === null) ? ' cap-grid--solo' : ''; ?>">

  <?php if ($bd !== null) { ?>
  <section class="cap-card cap-card--gold">
    <form id="capBuscar" class="d-flex flex-column gap-2" autocomplete="off">
      <label class="cap-label" for="capClave"><?php echo htmlspecialchars($claveBd !== null ? $claveBd['label'] : 'Búsqueda'); ?></label>
      <div class="cap-search-row">
        <input type="text" class="form-control" id="capClave" maxlength="191" autofocus>
        <button type="submit" class="btn btn-primary cap-btn px-4">Buscar</button>
      </div>
      <div id="capBuscarError" class="cap-error" style="display:none"></div>
    </form>

    <h2 class="cap-h2">Datos de la búsqueda</h2>
    <div class="cap-fieldList" id="capRegistro">
      <?php foreach ($bd['columnas'] as $col) { ?>
        <div class="cap-field">
          <span class="cap-field__label"><?php echo htmlspecialchars($col['label']); ?></span>
          <span class="cap-field__value" data-col="<?php echo htmlspecialchars($col['k']); ?>">—</span>
        </div>
      <?php } ?>
    </div>
  </section>
  <?php } ?>

  <section class="cap-card cap-card--green">
    <form id="capForm" novalidate autocomplete="off">
      <fieldset id="capFieldset"<?php echo ($bd !== null) ? ' disabled' : ''; ?>>
        <?php if ($bd !== null) { ?>
          <div class="cap-hint" id="capHint">Busca un registro para habilitar el formulario.</div>
        <?php } ?>
        <div id="capCampos"></div>
        <div class="cap-actions">
          <button type="submit" class="btn btn-primary" id="capGuardar"><?php echo htmlspecialchars($form['btn_texto']); ?></button>
        </div>
      </fieldset>
    </form>
  </section>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1090">
  <div id="capToast" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex"><div class="toast-body" id="capToastMsg">Guardado.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
  </div>
</div>

<script>
window.CAPTURA_BOOT = {
  campos: <?php echo json_encode($form['definicion']['campos'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>,
  bd: <?php echo json_encode($bd !== null ? array('busqueda' => $bd['busqueda'], 'columnas' => $bd['columnas']) : null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>
};
</script>
<script src="<?php echo asset('public/js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?php echo asset('public/js/formDinamico.js'); ?>"></script>
<script src="<?php echo asset('public/js/captura.js'); ?>"></script>
<?php } ?>
