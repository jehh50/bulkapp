<?php
$servicio = isset($servicio) && is_array($servicio) ? $servicio : [];
?>

<div class="row justify-content-center">
  <div class="col-12 col-lg-10 col-xl-8">

    <section class="card border-0 shadow-sm rounded-4 p-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="h4 fw-semibold mb-1">Nuevo usuario</h1>
          <p class="text-muted small mb-0">Crea el acceso y asigna la campaña principal con la que iniciará sesión.</p>
        </div>
        <span class="badge rounded-pill text-bg-light border px-3 py-2">Administración</span>
      </div>

      <form id="f_newuser" class="row g-3" autocomplete="off" novalidate>
        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold text-uppercase" for="nombre">Nombre</label>
          <input type="text" class="form-control" id="nombre" name="nombre"
                 placeholder="JUAN" maxlength="15" data-mayus required>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold text-uppercase" for="apellido">Apellido</label>
          <input type="text" class="form-control" id="apellido" name="apellido"
                 placeholder="GOMEZ" maxlength="15" data-mayus required>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold text-uppercase" for="user">Usuario</label>
          <input type="text" class="form-control" id="user" name="user"
                 placeholder="jgomez" maxlength="10" autocomplete="off"
                 aria-describedby="userHelp" required>
          <div id="userHelp" class="form-text">Letras, números, punto, guion y guion bajo.</div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold text-uppercase" for="nuevaClave">Contraseña</label>
          <input type="password" class="form-control" id="nuevaClave" name="pass"
                 maxlength="15" minlength="8" autocomplete="new-password"
                 aria-describedby="claveHelp" required>
          <div id="claveHelp" class="form-text">Mínimo 8 caracteres.</div>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold text-uppercase" for="tipo_usuario">Tipo de usuario</label>
          <select class="form-select" id="tipo_usuario" name="tipo" required>
            <option value="" selected disabled>Seleccione…</option>
            <option value="<?php echo Constantes::USUARIO_AGENTE; ?>">Operador</option>
            <option value="<?php echo Constantes::USUARIO_SUPERVISOR; ?>">Supervisor</option>
            <option value="<?php echo Constantes::USUARIO_ADMINISTRADOR; ?>">Administrador</option>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold text-uppercase" for="servicio">Campaña principal</label>
          <select class="form-select" id="servicio" name="cliente" required>
            <option value="" selected disabled>Campaña…</option>
            <?php foreach ($servicio as $s) { ?>
              <option value="<?php echo (int) $s['id']; ?>"><?php echo htmlspecialchars($s['descripcion'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="col-12">
          <div id="newUserError" class="alert alert-danger d-none mb-0" role="alert" aria-live="polite"></div>
        </div>

        <div class="col-12 d-flex flex-wrap justify-content-end gap-2 border-top pt-3 mt-2">
          <a href="?view=usuarios&amp;mode=index" class="btn btn-outline-secondary flex-fill flex-md-grow-0">Regresar</a>
          <button type="submit" class="btn btn-primary px-4 flex-fill flex-md-grow-0" id="btn-register">
            <span class="spinner-border spinner-border-sm d-none" id="newUserSpinner" aria-hidden="true"></span>
            Guardar
          </button>
        </div>
      </form>
    </section>

  </div>
</div>

<script src="<?php echo asset('public/js/nuevoUsuario.js'); ?>"></script>
