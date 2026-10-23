<?php
$mensajeCambio = isset($_GET['mensaje']) ? (string) $_GET['mensaje'] : '';
$tokenCsrf = security_csrf_token();
$usuarioSesion = isset($_SESSION['user']) ? (string) $_SESSION['user'] : '';
?>

<style>
  .cp-shell{max-width:1040px;margin:0 auto}
  .cp-card{background:#fff;border:0;border-radius:16px;box-shadow:0 8px 24px rgba(18,48,107,.12);overflow:hidden}
  .cp-head{background:linear-gradient(135deg,var(--blue-900),var(--blue-700));color:#fff;padding:28px 32px}
  .cp-head__icon{width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;flex:none}
  .cp-body{padding:28px 32px}
  .cp-label{font-size:13px;font-weight:600;color:var(--ink)}
  .cp-input{height:46px;border-radius:10px;font-size:14px}
  .cp-input:focus{border-color:var(--blue-700);box-shadow:0 0 0 .2rem rgba(47,95,196,.2)}
  .cp-toggle{min-width:64px;border-color:#d1d5db;color:var(--blue-700);font-size:13px;font-weight:600}
  .cp-toggle:hover{background:#eef2fb;border-color:#c3cee8;color:var(--blue-900)}
  .cp-req{display:flex;align-items:center;gap:10px;font-size:13px;color:#6b7280;transition:color .15s}
  .cp-req__dot{width:18px;height:18px;border-radius:50%;border:1px solid #cfd6e4;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex:none;background:#fff}
  .cp-req.is-ok{color:#0b6b4f}
  .cp-req.is-ok .cp-req__dot{border-color:#a9dcc8;background:#d3f0e6}
  .cp-side{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(16,24,40,.08);padding:24px}
  .cp-side__item{display:flex;gap:12px;padding:14px 0;border-top:1px solid var(--line)}
  .cp-side__item:first-of-type{border-top:0;padding-top:0}
  .cp-side__n{width:28px;height:28px;border-radius:8px;background:#e8eefb;color:var(--blue-700);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex:none}
  @media (max-width:991px){
    .cp-head,.cp-body{padding:22px}
    .cp-side{padding:20px}
  }
</style>

<div class="cp-shell">
  <div class="row g-4">

    <div class="col-12 col-lg-7">
      <section class="cp-card">
        <div class="cp-head d-flex align-items-center gap-3">
          <div class="cp-head__icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="10" width="16" height="10" rx="2"></rect>
              <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
              <path d="M12 14v2"></path>
            </svg>
          </div>
          <div class="min-w-0">
            <h1 class="h4 fw-semibold mb-1">Cambio de contraseña</h1>
            <p class="mb-0 small text-white-50">Actualiza la clave de acceso de <?php echo htmlspecialchars($usuarioSesion !== '' ? $usuarioSesion : 'tu cuenta', ENT_QUOTES, 'UTF-8'); ?>.</p>
          </div>
        </div>

        <div class="cp-body">
          <?php if ($mensajeCambio === 'error') { ?>
            <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
              <span aria-hidden="true">!</span>
              <div>No se pudo actualizar la contraseña. Verifica que tenga al menos 8 caracteres y que ambas coincidan.</div>
            </div>
          <?php } ?>

          <form id="actualizaContrasena" method="POST" action="?view=usuarios&mode=actualizaPassword" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="user" id="user" value="<?php echo isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0; ?>">

            <div class="mb-3">
              <label class="form-label cp-label" for="password">Nueva contraseña</label>
              <div class="input-group">
                <input type="password" id="password" name="password" class="form-control cp-input"
                       placeholder="Escribe tu nueva contraseña" minlength="8"
                       autocomplete="new-password" required aria-describedby="passwordHelp">
                <button class="btn btn-outline-secondary cp-toggle" type="button"
                        data-toggle-password="password" aria-label="Mostrar contraseña" aria-pressed="false">Ver</button>
              </div>
              <div id="passwordHelp" class="form-text">Mínimo 8 caracteres. Evita claves obvias o compartidas.</div>
              <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
            </div>

            <div class="mb-3">
              <label class="form-label cp-label" for="password_">Confirmar contraseña</label>
              <div class="input-group">
                <input type="password" id="password_" name="password_" class="form-control cp-input"
                       placeholder="Repite la nueva contraseña" minlength="8"
                       autocomplete="new-password" required>
                <button class="btn btn-outline-secondary cp-toggle" type="button"
                        data-toggle-password="password_" aria-label="Mostrar confirmación de contraseña" aria-pressed="false">Ver</button>
              </div>
              <div class="invalid-feedback">Las contraseñas no coinciden.</div>
            </div>

            <div class="d-grid gap-2 mb-4" aria-live="polite">
              <div class="cp-req" id="reqLen"><span class="cp-req__dot" aria-hidden="true">✓</span> Al menos 8 caracteres</div>
              <div class="cp-req" id="reqMatch"><span class="cp-req__dot" aria-hidden="true">✓</span> Ambas contraseñas coinciden</div>
              <div class="cp-req" id="reqDefault"><span class="cp-req__dot" aria-hidden="true">✓</span> No usar la clave 123456</div>
            </div>

            <div id="cpError" class="alert alert-danger py-2 px-3 small d-none" role="alert"></div>

            <div class="d-flex flex-column flex-sm-row gap-2 pt-2">
              <a href="?view=formulario&mode=index" class="btn btn-outline-secondary px-4">Cancelar</a>
              <button id="btn-actualiza" name="btn-actualiza" type="submit" class="btn btn-primary flex-grow-1">
                Guardar contraseña
              </button>
            </div>
          </form>
        </div>
      </section>
    </div>

    <div class="col-12 col-lg-5">
      <aside class="cp-side h-100">
        <h2 class="h6 fw-semibold mb-1">Recomendaciones de seguridad</h2>
        <p class="small text-muted mb-4">Una contraseña robusta protege tu gestión y la de tu equipo.</p>

        <div class="cp-side__item">
          <span class="cp-side__n">1</span>
          <div>
            <div class="fw-semibold small">Combina letras, números y símbolos</div>
            <div class="small text-muted">Evita nombres, fechas o palabras fáciles de adivinar.</div>
          </div>
        </div>
        <div class="cp-side__item">
          <span class="cp-side__n">2</span>
          <div>
            <div class="fw-semibold small">No reutilices claves de otros servicios</div>
            <div class="small text-muted">Si otra plataforma se ve comprometida, esta cuenta seguirá segura.</div>
          </div>
        </div>
        <div class="cp-side__item">
          <span class="cp-side__n">3</span>
          <div>
            <div class="fw-semibold small">No compartas tu contraseña</div>
            <div class="small text-muted">Cada usuario debe ingresar con sus propias credenciales.</div>
          </div>
        </div>

        <div class="alert alert-light border small mb-0 mt-3" role="note">
          Al guardar, se cerrará tu sesión para que inicies con la nueva contraseña.
        </div>
      </aside>
    </div>

  </div>
</div>

<script>
(function () {
  'use strict';

  var form = document.getElementById('actualizaContrasena');
  if (!form) return;

  var pass = document.getElementById('password');
  var confirm = document.getElementById('password_');
  var errorBox = document.getElementById('cpError');
  var reqLen = document.getElementById('reqLen');
  var reqMatch = document.getElementById('reqMatch');
  var reqDefault = document.getElementById('reqDefault');

  function marcar(elemento, activo) {
    if (!elemento) return;
    elemento.classList.toggle('is-ok', activo);
  }

  function validar() {
    var largo = pass.value.length >= 8;
    var coincide = pass.value !== '' && pass.value === confirm.value;
    var noObvia = pass.value !== '123456';

    marcar(reqLen, largo);
    marcar(reqMatch, coincide);
    marcar(reqDefault, noObvia);

    confirm.setCustomValidity(pass.value !== confirm.value ? 'Las contraseñas no coinciden.' : '');

    return largo && coincide && noObvia;
  }

  function mostrarError(mensaje) {
    if (!errorBox) return;
    errorBox.textContent = mensaje;
    errorBox.classList.toggle('d-none', mensaje === '');
  }

  document.querySelectorAll('[data-toggle-password]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      var input = document.getElementById(boton.getAttribute('data-toggle-password'));
      if (!input) return;

      var mostrar = input.type === 'password';
      input.type = mostrar ? 'text' : 'password';
      boton.textContent = mostrar ? 'Ocultar' : 'Ver';
      boton.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
      boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
      input.focus();
    });
  });

  [pass, confirm].forEach(function (input) {
    input.addEventListener('input', function () {
      if (validar()) mostrarError('');
    });
  });

  form.addEventListener('submit', function (evento) {
    var valido = validar();
    form.classList.add('was-validated');

    if (!valido) {
      evento.preventDefault();
      if (pass.value.length < 8) {
        mostrarError('La contraseña debe tener al menos 8 caracteres.');
        pass.focus();
      } else if (pass.value !== confirm.value) {
        mostrarError('Las contraseñas no coinciden.');
        confirm.focus();
      } else {
        mostrarError('La contraseña no puede ser 123456.');
        pass.focus();
      }
      return;
    }

    mostrarError('');
    var boton = document.getElementById('btn-actualiza');
    if (boton) {
      boton.disabled = true;
      boton.textContent = 'Guardando…';
    }
  });
})();
</script>
