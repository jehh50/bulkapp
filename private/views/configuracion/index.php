<?php
$listarProducto = is_array($listarProducto ?? null) ? $listarProducto : [];
$total = count($listarProducto);
$servicio = isset($servicio) && is_array($servicio) ? $servicio : [];

// Toast de resultado (redirect tras guardar / actualizar)
$toastMsg = '';
switch (isset($_GET['mensaje']) ? $_GET['mensaje'] : '') {
  case 'exito':       $toastMsg = 'Productos guardados correctamente.';       break;
  case 'actualizado': $toastMsg = 'Productos actualizados correctamente.';   break;
}
?>

<style>
  /* ---------- Productos (BS5) ---------- */
  .pg-card{background:#fff;border-radius:16px;box-shadow:0 8px 24px rgba(18,48,107,.12);padding:24px;display:flex;flex-direction:column;gap:16px}
  .pg-search{flex:1 1 220px;max-width:360px;height:44px;border-radius:10px;font-size:14px}
  .pg-input{height:44px;border-radius:10px;font-size:14px;color:var(--ink)}
  .pg-input:focus{border-color:var(--blue-700);box-shadow:0 0 0 .2rem rgba(47,95,196,.2)}
  .pg-btn{height:44px;padding:0 18px;border-radius:10px;font-size:14px;font-weight:600}
  .pg-btn--sel{height:44px;padding:0 18px;border:0;border-radius:10px;background:#e4e8f0;color:var(--ink);font-size:14px;font-weight:600}
  .pg-btn--sel.is-active{background:var(--blue-700);color:#fff}
  .pg-btn--sel.is-active:hover{background:var(--blue-900)}
  .pg-btn--ghost{height:44px;padding:0 18px;border:1px dashed var(--blue-700);border-radius:10px;background:#f4f7fd;color:var(--blue-700);font-size:14px;font-weight:600;cursor:pointer}
  .pg-btn--ghost:hover{background:#e8eefb}
  .pg-btn--sec{height:46px;padding:0 22px;border:1px solid #d1d5db;border-radius:10px;background:#fff;color:var(--ink);font-size:15px;font-weight:500}
  .pg-btn--sec:hover{background:#f4f6fa}
  .pg-actions{display:flex;gap:10px;flex-wrap:wrap;border-top:1px solid var(--line);padding-top:18px;margin-top:auto}
  .pg-actions .btn-primary{height:46px;padding:0 26px}

  .pg-tableWrap{border:1px solid #dfe5f2;border-radius:12px;overflow-x:auto}
  .pg-table{width:100%;min-width:820px;border-collapse:collapse;font-size:14px}
  .pg-th{padding:12px 14px;text-align:left;background:#f1f4fa;font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--ink);white-space:nowrap}
  .pg-td{padding:10px 14px;border-top:1px solid #eef0f4;vertical-align:middle}
  .pg-td--name{font-weight:500}
  .pg-td--dim{color:#6b7280}
  .pg-td--right{text-align:right}
  .pg-td--center{text-align:center}
  .pg-th--right{text-align:right}
  .pg-th--center{text-align:center}
  .pg-th--check{width:44px}
  .pg-table input[type=checkbox]{width:18px;height:18px;accent-color:var(--blue-700);cursor:pointer}
  .pg-table tr.is-selected{background:#f4f7fd}
  .pg-svc{font-size:12px;font-weight:600;padding:3px 12px;border-radius:20px;background:#e8eefb;color:var(--blue-700);white-space:nowrap}
  .pg-edit{width:36px;height:36px;border:0;border-radius:8px;background:var(--blue-700);color:#fff;font-size:15px;cursor:pointer}
  .pg-edit:hover{background:var(--blue-900)}
  .pg-empty{display:none;padding:28px;text-align:center;font-size:14px;color:var(--ink)}
  .pg-count{font-size:13px;color:var(--ink)}

  .pg-draft{border:1px solid #dfe5f2;border-radius:12px;overflow:hidden}
  .pg-draft__head{background:#f1f4fa;padding:8px 8px 8px 16px;display:flex;justify-content:space-between;align-items:center;gap:8px;min-height:36px}
  .pg-draft__title{font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--ink)}
  .pg-draft__rm{height:36px;padding:0 14px;border:0;border-radius:8px;background:#fde0e0;color:#b42318;font-size:13px;font-weight:600;cursor:pointer}
  .pg-draft__rm:hover{background:#f9caca}
  .pg-draft__grid{padding:14px 16px;display:grid;grid-template-columns:3fr 1fr 1fr 1fr;gap:12px 14px}
  .pg-draft__grid--edit{grid-template-columns:1.1fr 2fr 1fr 1fr 1fr}
  @media (max-width:767px){
    .pg-draft__grid,.pg-draft__grid--edit{grid-template-columns:1fr 1fr}
  }
  .pg-lbl{display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:600;color:var(--ink);margin:0}
  .pg-lbl--svc{max-width:360px}
  .pg-lbl .form-select{background:#fff}

  .pg-error{padding:12px 16px;border-radius:10px;background:#fdecec;border:1px solid #f3c2c2;font-size:14px;color:#b42318}
  .pg-radio-error{border-color:#d92d20 !important}
  .pg-toast{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;padding:12px 16px;border-radius:12px;background:#d3f0e6;border:1px solid #a9dcc8;font-size:14px;box-shadow:0 4px 12px rgba(18,48,107,.1)}
  .pg-toast__close{border:0;background:transparent;font-size:18px;cursor:pointer;color:var(--ink);width:32px;height:32px}
</style>

<?php if ($toastMsg) { ?>
  <div class="pg-toast" id="pgToast">
    <span>✓ <?php echo htmlspecialchars($toastMsg); ?></span>
    <button type="button" class="pg-toast__close" id="pgToastClose" aria-label="Cerrar">×</button>
  </div>
<?php } ?>

<!-- ========== LISTA DE PRODUCTOS ========== -->
<section id="listView" class="pg-card">
  <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
    <input type="text" id="prodSearch" class="form-control pg-search" placeholder="Buscar producto, código, plan…">
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="pg-btn--sel" id="btnEditSel" disabled>Editar seleccionados</button>
      <button type="button" class="btn btn-gold pg-btn" id="btnAdd">+ Agregar productos</button>
    </div>
  </div>

  <div class="pg-tableWrap">
    <table class="pg-table">
      <thead>
        <tr>
          <th class="pg-th pg-th--check"><input type="checkbox" id="pgCheckAll" aria-label="Seleccionar todos"></th>
          <th class="pg-th pg-td--dim">#</th>
          <th class="pg-th">Nombre</th>
          <th class="pg-th">Código</th>
          <th class="pg-th pg-th--right">Costo</th>
          <th class="pg-th">Fecha activación</th>
          <th class="pg-th">Plan</th>
          <th class="pg-th">Servicio</th>
          <th class="pg-th pg-th--center">Editar</th>
        </tr>
      </thead>
      <tbody id="prodRows">
        <?php $i = 0; foreach ($listarProducto as $u) { $i++; ?>
        <tr data-id="<?php echo (int)$u['id']; ?>"
            data-nombre="<?php echo htmlspecialchars($u['descripcion'], ENT_QUOTES); ?>"
            data-codigo="<?php echo htmlspecialchars($u['codigo_producto'], ENT_QUOTES); ?>"
            data-costo="<?php echo htmlspecialchars($u['costo_prod'], ENT_QUOTES); ?>"
            data-fecha="<?php echo htmlspecialchars($u['fecha'], ENT_QUOTES); ?>"
            data-plan="<?php echo htmlspecialchars($u['codplan'], ENT_QUOTES); ?>"
            data-servicio="<?php echo htmlspecialchars($u['servicio'], ENT_QUOTES); ?>">
          <td class="pg-td"><input type="checkbox" class="pg-check" value="<?php echo (int)$u['id']; ?>" aria-label="Seleccionar <?php echo htmlspecialchars($u['descripcion']); ?>"></td>
          <td class="pg-td pg-td--dim"><?php echo $i; ?></td>
          <td class="pg-td pg-td--name"><?php echo htmlspecialchars($u['descripcion']); ?></td>
          <td class="pg-td"><?php echo htmlspecialchars($u['codigo_producto']); ?></td>
          <td class="pg-td pg-td--right"><?php echo number_format((float)$u['costo_prod'], 2, '.', ''); ?></td>
          <td class="pg-td"><?php echo htmlspecialchars($u['fecha']); ?></td>
          <td class="pg-td"><?php echo htmlspecialchars($u['codplan']); ?></td>
          <td class="pg-td"><span class="pg-svc"><?php echo htmlspecialchars($u['servicio']); ?></span></td>
          <td class="pg-td pg-td--center"><button type="button" class="pg-edit" aria-label="Editar <?php echo htmlspecialchars($u['descripcion']); ?>">✎</button></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div id="pgEmpty" class="pg-empty">Sin resultados.</div>
  </div>

  <div class="pg-count" id="pgCount">Mostrando <?php echo $total; ?> de <?php echo $total; ?> productos activos</div>
</section>

<!-- ========== ALTA / EDICIÓN DE PRODUCTOS ========== -->
<section id="formView" class="pg-card" style="display:none">

  <form id="createForm" method="POST" action="?view=configuracion&mode=guardarProductos" autocomplete="off" style="display:none">
    <label class="pg-lbl pg-lbl--svc">Servicio
      <select class="form-select pg-input" name="venta" required>
        <option value="" selected disabled>Seleccione…</option>
        <?php foreach ($servicio as $s) { ?>
          <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['descripcion']); ?></option>
        <?php } ?>
      </select>
    </label>

    <div class="d-flex flex-column gap-3" id="createRows"></div>

    <div><button type="button" id="btnAddRow" class="pg-btn--ghost">+ Agregar otro producto</button></div>

    <div id="createError" class="pg-error" style="display:none"></div>

    <div class="pg-actions">
      <button type="button" class="pg-btn--sec pg-back">Regresar</button>
      <button type="submit" class="btn btn-primary pg-btn">Guardar</button>
    </div>
  </form>

  <form id="editForm" method="POST" action="?view=configuracion&mode=actualizarProductos" autocomplete="off" style="display:none">
    <div class="d-flex flex-column gap-3" id="editRows"></div>
    <div id="editError" class="pg-error" style="display:none"></div>
    <div class="pg-actions">
      <button type="button" class="pg-btn--sec pg-back">Regresar</button>
      <button type="submit" class="btn btn-primary pg-btn">Actualizar</button>
    </div>
  </form>
</section>

<script>
function mayus(e) {
  e.value = e.value.toUpperCase();
}
</script>
<script src="<?php echo asset('public/js/productos.js'); ?>"></script>
