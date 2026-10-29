<?php
$total = is_array($listUser ?? null) ? count($listUser) : 0;
$servicio = isset($servicio) && is_array($servicio) ? $servicio : [];
?>

<style>
  /* ---------- Usuarios (BS5) ---------- */
  .us-card{background:#fff;border-radius:16px;box-shadow:0 8px 24px rgba(18,48,107,.12);padding:24px;display:flex;flex-direction:column;gap:16px}
  .us-search{flex:1 1 220px;max-width:360px;height:44px;border-radius:10px;font-size:14px}
  .us-input{height:44px;border-radius:10px;font-size:14px;color:var(--ink)}
  .us-input:focus{border-color:var(--blue-700);box-shadow:0 0 0 .2rem rgba(47,95,196,.2)}
  .us-btn{height:44px;padding:0 18px;border-radius:10px;font-size:14px;font-weight:600}
  .us-btn--sec{height:46px;padding:0 22px;border:1px solid #d1d5db;border-radius:10px;background:#fff;color:var(--ink);font-size:15px;font-weight:500}
  .us-btn--sec:hover{background:#f4f6fa}
  .us-actions{display:flex;gap:10px;flex-wrap:wrap;border-top:1px solid var(--line);padding-top:18px}
  .us-actions .btn-primary{height:46px;padding:0 26px}

  .us-tableWrap{border:1px solid #dfe5f2;border-radius:12px;overflow-x:auto}
  .us-table{width:100%;min-width:820px;border-collapse:collapse;font-size:14px}
  .us-th{padding:12px 14px;text-align:left;background:#f1f4fa;font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--ink);white-space:nowrap}
  .us-td{padding:10px 14px;border-top:1px solid #eef0f4;vertical-align:middle}
  .us-td--name{font-weight:500}
  .us-td--center{text-align:center}
  .us-th--center{text-align:center}
  .us-table tr.is-selected{background:#f4f7fd}
  .us-pill{display:inline-block;font-size:12px;font-weight:600;padding:3px 12px;border-radius:20px;white-space:nowrap}
  .us-pill--on{background:#d3f0e6;color:#0b6b4f}
  .us-pill--off{background:#fde0e0;color:#b42318}
  .us-svc{display:inline-block;font-size:12px;font-weight:600;padding:3px 12px;border-radius:20px;background:#e8eefb;color:var(--blue-700);white-space:nowrap}
  .us-edit{width:36px;height:36px;border:0;border-radius:8px;background:var(--blue-700);color:#fff;font-size:15px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center}
  .us-edit:hover{background:var(--blue-900)}
  .us-del{width:36px;height:36px;border:0;border-radius:8px;background:#fde0e0;color:#b42318;font-size:15px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center}
  .us-del:hover{background:#f9caca}
  .us-empty{display:none;padding:28px;text-align:center;font-size:14px;color:var(--ink)}
  .us-count{font-size:13px;color:var(--ink)}

  .us-formHead{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .us-formHead h2{margin:0;font-size:19px;font-weight:600;color:var(--ink)}
  .us-lbl{display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:600;color:var(--ink);margin:0}
  .us-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 16px}
  .us-grid--full{grid-column:1 / -1}
  @media (max-width:767px){.us-grid{grid-template-columns:1fr}}

  .us-error{padding:12px 16px;border-radius:10px;background:#fdecec;border:1px solid #f3c2c2;font-size:14px;color:#b42318}
  .us-bad-field{border-color:#d92d20 !important}
  .us-toast{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 auto 14px;padding:12px 16px;border-radius:12px;font-size:14px;box-shadow:0 4px 12px rgba(18,48,107,.1);max-width:1150px}
  .us-toast--success{background:#d3f0e6;border:1px solid #a9dcc8}
  .us-toast--error{background:#fdecec;border:1px solid #f3c2c2}
  .us-toast__close{border:0;background:transparent;font-size:18px;cursor:pointer;color:var(--ink);width:32px;height:32px;flex:none}
  .us-pass{font-family:ui-monospace,Menlo,monospace;font-weight:700}
</style>

<div id="usToast" class="us-toast us-toast--success" style="display:none">
  <span id="usToastMsg"></span>
  <button type="button" class="us-toast__close" id="usToastClose" aria-label="Cerrar">×</button>
</div>

<!-- ========== LISTA DE USUARIOS ========== -->
<section id="listView" class="us-card">
  <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
    <input type="text" id="usSearch" class="form-control us-search" placeholder="Buscar usuario, nombre, rol, servicio…">
    <button type="button" class="btn btn-gold us-btn" id="btnAdd">+ Agregar usuario</button>
  </div>

  <div class="us-tableWrap">
    <table class="us-table">
      <thead>
        <tr>
          <th class="us-th">ID</th>
          <th class="us-th">Usuario</th>
          <th class="us-th">Nombre</th>
          <th class="us-th">Apellido</th>
          <th class="us-th">Rol</th>
          <th class="us-th">Servicio</th>
          <th class="us-th">Estatus</th>
          <th class="us-th us-th--center">Editar</th>
          <th class="us-th us-th--center">Eliminar</th>
        </tr>
      </thead>
      <tbody id="usRows">
        <?php foreach ($listUser as $u) { ?>
        <tr data-id="<?php echo (int)$u['id']; ?>"
            data-user="<?php echo htmlspecialchars($u['user'], ENT_QUOTES); ?>"
            data-nombre="<?php echo htmlspecialchars($u['nombre'], ENT_QUOTES); ?>"
            data-apellido="<?php echo htmlspecialchars($u['apellido'], ENT_QUOTES); ?>"
            data-rolid="<?php echo (int)$u['usertype_id']; ?>"
            data-rol="<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>"
            data-statusid="<?php echo (int)$u['status_id']; ?>"
            data-status="<?php echo htmlspecialchars($u['status'], ENT_QUOTES); ?>"
            data-servicioid="<?php echo (int)$u['id_servicio']; ?>"
            data-servicio="<?php echo htmlspecialchars($u['descripcion'], ENT_QUOTES); ?>">
          <td class="us-td us-td--name"><?php echo (int)$u['id']; ?></td>
          <td class="us-td"><?php echo htmlspecialchars($u['user']); ?></td>
          <td class="us-td"><?php echo htmlspecialchars($u['nombre']); ?></td>
          <td class="us-td"><?php echo htmlspecialchars($u['apellido']); ?></td>
          <td class="us-td"><?php echo htmlspecialchars($u['name']); ?></td>
          <td class="us-td"><span class="us-svc"><?php echo htmlspecialchars($u['descripcion']); ?></span></td>
          <td class="us-td"><span class="us-pill <?php echo ((int)$u['status_id'] === 2) ? 'us-pill--on' : 'us-pill--off'; ?>"><?php echo strtoupper(htmlspecialchars($u['status'])); ?></span></td>
          <td class="us-td us-td--center"><button type="button" class="us-edit" aria-label="Editar <?php echo htmlspecialchars($u['user']); ?>">✎</button></td>
          <td class="us-td us-td--center"><button type="button" class="us-del" aria-label="Eliminar <?php echo htmlspecialchars($u['user']); ?>">🗑</button></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div id="usEmpty" class="us-empty">Sin resultados.</div>
  </div>

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" id="usPagerWrap">
    <div class="us-count" id="usCount"></div>
    <nav aria-label="Paginación de usuarios" id="usPagerNav" style="display:none">
      <ul class="pagination pagination-sm mb-0" id="usPager"></ul>
    </nav>
  </div>
</section>

<!-- ========== ALTA DE USUARIO ========== -->
<section id="createView" class="us-card" style="display:none">
  <div class="us-formHead"><h2>Nuevo usuario</h2></div>

  <form id="createForm" autocomplete="off" novalidate>
    <div class="us-grid">
      <label class="us-lbl">Nombre
        <input type="text" class="form-control us-input" id="c_nombre" name="nombre" placeholder="Juan" maxlength="15" oninput="mayus(this)" required>
      </label>
      <label class="us-lbl">Apellido
        <input type="text" class="form-control us-input" id="c_apellido" name="apellido" placeholder="Gomez" maxlength="15" oninput="mayus(this)" required>
      </label>
      <label class="us-lbl">Usuario
        <input type="text" class="form-control us-input" id="c_user" name="user" placeholder="jgomez" maxlength="10" autocomplete="off" required>
      </label>
      <label class="us-lbl">Contraseña
        <input type="password" class="form-control us-input" id="c_pass" name="pass" placeholder="abcd1234." maxlength="15" minlength="8" autocomplete="new-password" required>
        <span class="us-pass-hint" style="font-size:12px;font-weight:400;color:#6b7280">Mínimo 8 caracteres</span>
      </label>
      <label class="us-lbl">Tipo de usuario
        <select class="form-select us-input" name="tipo_usuario" required>
          <option value="" selected disabled>Seleccione…</option>
          <option value="1">Operador</option>
          <option value="2">Supervisor</option>
          <option value="3">Administrador</option>
        </select>
      </label>
      <label class="us-lbl">Servicio
        <select class="form-select us-input" name="cliente" id="c_servicio" required>
          <option value="" selected disabled>Servicio…</option>
          <?php foreach ($servicio as $s) { ?>
            <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['descripcion']); ?></option>
          <?php } ?>
        </select>
      </label>
    </div>

    <div id="createError" class="us-error" style="display:none"></div>

    <div class="us-actions">
      <button type="button" class="us-btn--sec us-back">Regresar</button>
      <button type="submit" class="btn btn-primary" id="btn-register">Guardar</button>
    </div>
  </form>
</section>

<!-- ========== EDICIÓN DE USUARIO ========== -->
<section id="editView" class="us-card" style="display:none">
  <div class="us-formHead"><h2 id="editTitle">Edición de usuario</h2></div>

  <form id="editForm" autocomplete="off" novalidate>
    <div class="us-grid">
      <input type="hidden" id="userid" name="userid">
      <label class="us-lbl">Usuario
        <input type="text" class="form-control us-input" value="" id="e_user" readonly>
      </label>
      <label class="us-lbl">Nombre
        <input type="text" class="form-control us-input" name="nombre" id="nombre" maxlength="15" oninput="mayus(this)" required>
      </label>
      <label class="us-lbl">Apellido
        <input type="text" class="form-control us-input" name="apellido" id="apellido" maxlength="15" oninput="mayus(this)" required>
      </label>
      <label class="us-lbl">Rol activo
        <select class="form-select us-input" name="rol" id="rol" required>
          <option value="1">OPERADOR</option>
          <option value="2">SUPERVISOR</option>
          <option value="3">ADMINISTRADOR</option>
        </select>
      </label>
      <label class="us-lbl">Estatus
        <select class="form-select us-input" name="status" id="status" required>
          <option value="2">ACTIVO</option>
          <option value="1">INACTIVO</option>
        </select>
      </label>
      <label class="us-lbl">Servicio
        <select class="form-select us-input" name="servicio" id="servicio" required>
          <?php foreach ($servicio as $s) { ?>
            <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['descripcion']); ?></option>
          <?php } ?>
        </select>
      </label>
    </div>

    <div id="editError" class="us-error" style="display:none"></div>

    <div class="us-actions">
      <button type="button" class="us-btn--sec us-back">Regresar</button>
      <button type="button" class="us-btn--sec" id="btn-resetpass">Reiniciar contraseña</button>
      <button type="submit" class="btn btn-primary" id="btn-update">Actualizar</button>
    </div>
  </form>
</section>

<script>
function mayus(e) {
  e.value = e.value.toUpperCase();
}
</script>
<script src="<?php echo asset('public/js/usuarios.js'); ?>"></script>
