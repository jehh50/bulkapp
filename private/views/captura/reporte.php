<?php
/**
 * Reporte de formularios dinámicos: filtros en cascada cliente → campaña → formulario
 * (public/js/reporteFormularios.js sobre REPORTE_ARBOL), tabla paginada en servidor y
 * exportación a .xlsx con todas las filas del filtro.
 *
 * Variables: $arbol, $seleccion (null si no hay combinación válida), $fCliente, $fCampana,
 *            $fFormulario, $fDesde, $fHasta, $porPagina, $pagina, $totalPaginas, $total,
 *            $envios, $columnas (['bd'=>{k:label}, 'campos'=>{k:label}]), $version.
 */
$filtrosQs = array(
  'view' => 'captura', 'mode' => 'reporte',
  'cliente' => $fCliente, 'campana' => $fCampana, 'formulario' => $fFormulario,
  'desde' => $fDesde, 'hasta' => $fHasta, 'n' => $porPagina,
);
$urlPagina = function ($p) use ($filtrosQs) {
  return '?' . http_build_query(array_merge($filtrosQs, array('p' => $p)));
};
$urlExportar = '?' . http_build_query(array_merge($filtrosQs, array('mode' => 'exportar')));
$nBd = count($columnas['bd']);
$nCampos = count($columnas['campos']);
$nColumnas = 2 + $nBd + $nCampos;
?>
<link rel="stylesheet" href="<?php echo asset('public/css/reporteFormularios.css'); ?>">

<section class="filter-card" aria-label="Filtros">
  <form method="get" id="repForm" class="rf-filtros" autocomplete="off">
    <input type="hidden" name="view" value="captura">
    <input type="hidden" name="mode" value="reporte">
    <input type="hidden" name="n" value="<?php echo (int) $porPagina; ?>">
    <div>
      <label class="filter-label" for="repCliente">1 · Cliente</label>
      <select class="form-select" id="repCliente" name="cliente" data-sel="<?php echo (int) $fCliente; ?>">
        <option value="">Selecciona…</option>
      </select>
    </div>
    <div>
      <label class="filter-label" for="repCampana">2 · Campaña</label>
      <select class="form-select" id="repCampana" name="campana" data-sel="<?php echo (int) $fCampana; ?>" disabled>
        <option value="">Elige un cliente</option>
      </select>
    </div>
    <div>
      <label class="filter-label" for="repFormulario">3 · Formulario</label>
      <select class="form-select" id="repFormulario" name="formulario" data-sel="<?php echo (int) $fFormulario; ?>" disabled>
        <option value="">Elige una campaña</option>
      </select>
    </div>
    <div>
      <label class="filter-label" for="repDesde">Desde</label>
      <input type="date" class="form-control" id="repDesde" name="desde" value="<?php echo htmlspecialchars($fDesde); ?>" required>
    </div>
    <div>
      <label class="filter-label" for="repHasta">Hasta</label>
      <input type="date" class="form-control" id="repHasta" name="hasta" value="<?php echo htmlspecialchars($fHasta); ?>" required>
    </div>
    <div class="rf-filtros__acciones">
      <button type="submit" class="btn btn-primary search-btn" id="repBuscar" disabled>Buscar</button>
      <a class="btn btn-outline-secondary search-btn" href="?view=captura&amp;mode=reporte">Limpiar</a>
    </div>
  </form>
</section>

<section class="data-card" aria-label="Resultados">
  <?php if ($seleccion === null) { ?>
    <div class="rf-vacio">
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M3 9h18M9 9v11"></path></svg>
      <div class="rf-vacio__titulo">Aún no hay un reporte en pantalla</div>
      <div class="rf-vacio__texto">Selecciona en orden el cliente, la campaña y el formulario. Las columnas de la tabla se arman a partir de la base de datos y de los campos de ese formulario.</div>
      <?php if (empty($arbol)) { ?>
        <div class="rf-vacio__texto"><strong>No hay formularios asignados a campañas.</strong> Asígnalos desde el Constructor de formularios.</div>
      <?php } ?>
    </div>
  <?php } else { ?>
    <div class="rf-barra">
      <div class="rf-barra__info">
        <div><strong class="rf-total"><?php echo number_format($total, 0, ',', '.'); ?></strong> registro<?php echo $total === 1 ? '' : 's'; ?></div>
        <span class="rf-chip"><?php echo $nColumnas; ?> columnas</span>
        <?php if ($version !== null) { ?><span class="rf-chip rf-chip--gold">Versión vigente v<?php echo (int) $version; ?></span><?php } ?>
      </div>
      <div class="rf-barra__acciones">
        <form method="get" class="rf-tamano">
          <?php foreach ($filtrosQs as $k => $v) { if ($k !== 'n') { ?>
            <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars((string) $v); ?>">
          <?php } } ?>
          <label for="repTamano">Filas por página</label>
          <select class="form-select" id="repTamano" name="n">
            <?php foreach (Constantes::filasPorPaginaReporte() as $op) { ?>
              <option value="<?php echo $op; ?>"<?php echo $op === $porPagina ? ' selected' : ''; ?>><?php echo $op; ?></option>
            <?php } ?>
          </select>
          <noscript><button type="submit" class="btn btn-sm btn-outline-primary">Aplicar</button></noscript>
        </form>
        <?php if ($total > 0) { ?>
          <a class="btn rf-btn-excel" href="<?php echo htmlspecialchars($urlExportar); ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><path d="M14 3v6h6M9 13l6 6M15 13l-6 6"></path></svg>
            Exportar a Excel
          </a>
        <?php } ?>
      </div>
    </div>

    <?php if (empty($envios)) { ?>
      <div class="data-empty">No hay registros con estos filtros.</div>
    <?php } else { ?>
      <div class="rf-tabla-wrap">
        <table class="rf-tabla">
          <thead>
            <tr class="rf-grupos">
              <th colspan="2" scope="colgroup" class="rf-g1">Gestión</th>
              <?php if ($nBd > 0) { ?><th colspan="<?php echo $nBd; ?>" scope="colgroup" class="rf-g2">Datos de la búsqueda</th><?php } ?>
              <?php if ($nCampos > 0) { ?><th colspan="<?php echo $nCampos; ?>" scope="colgroup" class="rf-g3">Formulario · <?php echo htmlspecialchars($seleccion['formulario']['nombre']); ?></th><?php } ?>
            </tr>
            <tr>
              <th scope="col">Fecha</th>
              <th scope="col">Operador</th>
              <?php foreach ($columnas['bd'] as $label) { ?><th scope="col"><?php echo htmlspecialchars($label); ?></th><?php } ?>
              <?php foreach ($columnas['campos'] as $label) { ?><th scope="col"><?php echo htmlspecialchars($label); ?></th><?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($envios as $e) {
              $registro = json_decode((string) $e['registro'], true);
              $valores = json_decode((string) $e['valores'], true);
              $celdas = captura_celdas_reporte(is_array($registro) ? $registro : array(), is_array($valores) ? $valores : array(), $columnas);
              $ts = strtotime($e['fecha']);
            ?>
              <tr>
                <td class="rf-nowrap"><?php echo $ts ? date('d/m/Y H:i', $ts) : htmlspecialchars($e['fecha']); ?></td>
                <td class="rf-nowrap"><?php echo htmlspecialchars($e['operador'] !== '' ? $e['operador'] : (string) $e['usuario']); ?></td>
                <?php foreach ($celdas as $c) { ?><td><?php echo htmlspecialchars($c); ?></td><?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <nav class="rf-paginacion" aria-label="Paginación">
        <div class="rf-paginacion__rango">
          Mostrando <strong><?php echo number_format(($pagina - 1) * $porPagina + 1, 0, ',', '.'); ?>–<?php echo number_format(min($total, $pagina * $porPagina), 0, ',', '.'); ?></strong>
          de <?php echo number_format($total, 0, ',', '.'); ?>
        </div>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item<?php echo $pagina <= 1 ? ' disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $pagina <= 1 ? '#' : htmlspecialchars($urlPagina($pagina - 1)); ?>" aria-label="Página anterior">‹ Anterior</a>
          </li>
          <?php foreach (captura_paginas($pagina, $totalPaginas) as $p) { ?>
            <?php if ($p === null) { ?>
              <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php } else { ?>
              <li class="page-item<?php echo $p === $pagina ? ' active' : ''; ?>">
                <a class="page-link" href="<?php echo htmlspecialchars($urlPagina($p)); ?>"<?php echo $p === $pagina ? ' aria-current="page"' : ''; ?> aria-label="Página <?php echo $p; ?>"><?php echo $p; ?></a>
              </li>
            <?php } ?>
          <?php } ?>
          <li class="page-item<?php echo $pagina >= $totalPaginas ? ' disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $pagina >= $totalPaginas ? '#' : htmlspecialchars($urlPagina($pagina + 1)); ?>" aria-label="Página siguiente">Siguiente ›</a>
          </li>
        </ul>
      </nav>
    <?php } ?>
  <?php } ?>
</section>

<script>
window.REPORTE_ARBOL = <?php echo json_encode($arbol, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
</script>
<script src="<?php echo asset('public/js/reporteFormularios.js'); ?>"></script>
