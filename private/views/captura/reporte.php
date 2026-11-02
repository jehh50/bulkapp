<?php
/**
 * Reportería de formularios dinámicos (Constructor, Fase 3): filtro de envíos y
 * exportación CSV. Muestra los 100 envíos más recientes; el CSV incluye todos.
 *
 * Variables: $formularios, $servicios, $fFormulario, $fServicio, $fDesde, $fHasta,
 *            $total, $envios, $columnas (['bd'=>{k:label}, 'campos'=>{k:label}]).
 */
$qsExport = http_build_query(array(
  'view' => 'captura', 'mode' => 'exportar',
  'formulario' => $fFormulario, 'servicio' => $fServicio, 'desde' => $fDesde, 'hasta' => $fHasta,
));
?>
<div class="filter-card">
  <form method="get" class="row g-3 align-items-end" autocomplete="off">
    <input type="hidden" name="view" value="captura">
    <input type="hidden" name="mode" value="reporte">
    <div class="col-lg-4">
      <label class="filter-label" for="repFormulario">Formulario</label>
      <select class="form-select" id="repFormulario" name="formulario" required>
        <option value="">Selecciona…</option>
        <?php foreach ($formularios as $f) { ?>
          <option value="<?php echo (int) $f['id']; ?>"<?php echo ((string) $f['id'] === (string) $fFormulario) ? ' selected' : ''; ?>>
            <?php echo htmlspecialchars($f['nombre']) . ((int) $f['eliminado'] ? ' (eliminado)' : ''); ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="col-lg-3">
      <label class="filter-label" for="repServicio">Servicio</label>
      <select class="form-select" id="repServicio" name="servicio">
        <option value="">Todos</option>
        <?php foreach ($servicios as $s) { ?>
          <option value="<?php echo (int) $s['id']; ?>"<?php echo ((string) $s['id'] === (string) $fServicio) ? ' selected' : ''; ?>><?php echo htmlspecialchars($s['descripcion']); ?></option>
        <?php } ?>
      </select>
    </div>
    <div class="col-lg-2">
      <label class="filter-label" for="repDesde">Desde</label>
      <input type="date" class="form-control" id="repDesde" name="desde" value="<?php echo htmlspecialchars($fDesde); ?>">
    </div>
    <div class="col-lg-2">
      <label class="filter-label" for="repHasta">Hasta</label>
      <input type="date" class="form-control" id="repHasta" name="hasta" value="<?php echo htmlspecialchars($fHasta); ?>">
    </div>
    <div class="col-lg-1 d-grid">
      <button type="submit" class="btn btn-primary search-btn">Filtrar</button>
    </div>
  </form>
</div>

<div class="data-card">
  <?php if ($fFormulario === null) { ?>
    <div class="data-empty">Selecciona un formulario para ver sus envíos.</div>
  <?php } else { ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <strong><?php echo (int) $total; ?></strong> envío(s)
        <?php if ($total > count($envios)) { ?><span class="text-muted">· se muestran los <?php echo count($envios); ?> más recientes</span><?php } ?>
      </div>
      <?php if ($total > 0) { ?>
        <a class="btn btn-outline-primary" href="?<?php echo htmlspecialchars($qsExport); ?>"><i class="fi fi-rr-download"></i> Exportar CSV</a>
      <?php } ?>
    </div>

    <?php if (empty($envios)) { ?>
      <div class="data-empty">No hay envíos con estos filtros.</div>
    <?php } else { ?>
      <div class="table-responsive">
        <table class="data-table table align-middle">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Servicio</th>
              <th>Operador</th>
              <?php foreach ($columnas['bd'] as $label) { ?><th><?php echo htmlspecialchars($label); ?></th><?php } ?>
              <?php foreach ($columnas['campos'] as $label) { ?><th><?php echo htmlspecialchars($label); ?></th><?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($envios as $e) {
              $registro = json_decode((string) $e['registro'], true);
              $valores = json_decode((string) $e['valores'], true);
              $celdas = captura_celdas_reporte(is_array($registro) ? $registro : array(), is_array($valores) ? $valores : array(), $columnas);
            ?>
              <tr>
                <td><?php echo htmlspecialchars(substr($e['fecha'], 0, 16)); ?></td>
                <td><?php echo htmlspecialchars($e['servicio']); ?></td>
                <td><?php echo htmlspecialchars($e['operador'] !== '' ? $e['operador'] : (string) $e['usuario']); ?></td>
                <?php foreach ($celdas as $c) { ?><td><?php echo htmlspecialchars($c); ?></td><?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } ?>
  <?php } ?>
</div>
