<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bulksales - Iniciar sesión</title>
  <?php echo security_csrf_meta(); ?>
  <script src="<?php echo asset('public/js/csrf.js'); ?>"></script>
  <link href="public/images/icon.png" rel="icon" type="image/png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?php echo asset('public/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <style>
    :root{--blue-900:#12306b;--blue-700:#1a4391;--ink:#252525}
    body{margin:0;font-family:Poppins,system-ui,sans-serif;color:var(--ink);background:#fff}
    .btn-primary{--bs-btn-bg:var(--blue-700);--bs-btn-border-color:var(--blue-700);--bs-btn-hover-bg:var(--blue-900);--bs-btn-hover-border-color:var(--blue-900);--bs-btn-active-bg:var(--blue-900);--bs-btn-active-border-color:var(--blue-900)}
    .form-control:focus,.btn-outline-secondary:focus{border-color:var(--blue-700);box-shadow:0 0 0 .2rem rgba(47,95,196,.2)}

    .login-split{min-height:100vh;display:grid;grid-template-columns:1.1fr 1fr}
    .login-hero{position:relative;background:var(--blue-900);overflow:hidden;min-height:100vh}
    .login-hero__bg{position:absolute;inset:0;background:
      radial-gradient(1200px 500px at 80% -10%, rgba(245,184,61,.18), transparent 60%),
      radial-gradient(900px 500px at -10% 110%, rgba(47,95,196,.35), transparent 55%),
      var(--blue-900)}
    .login-hero__overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(18,48,107,.25) 0%,rgba(18,48,107,.9) 100%)}
    .login-hero__text{position:absolute;left:0;right:0;bottom:0;padding:48px 56px;color:#fff}
    .login-hero__badge{display:inline-flex;align-items:center;gap:10px;color:#fff;font-weight:700;font-size:20px;margin-bottom:18px}
    .login-hero__logo{width:34px;height:34px;border-radius:9px;background:#f5b83d;color:var(--blue-900);display:flex;align-items:center;justify-content:center;font-size:17px}
    .login-hero__title{font-size:34px;font-weight:600;line-height:1.2;max-width:460px}
    .login-hero__sub{font-size:15px;color:#e6ecf8;margin-top:10px;max-width:420px}

    .login-form{display:flex;flex-direction:column;justify-content:center;align-items:center;padding:48px 40px;background:#f4f6fa}
    .login-form__inner{width:100%;max-width:400px}
    .login-brand{font-size:34px;margin-bottom:28px;letter-spacing:.2px}
    .login-brand b{font-weight:700;color:var(--blue-700)}
    .login-foot{margin-top:32px;font-size:12.5px;line-height:1.7;color:#6b7280}

    @media (max-width:899px){
      .login-split{grid-template-columns:1fr;grid-template-rows:200px 1fr}
      .login-hero{min-height:200px}
      .login-hero__text{padding:20px}
      .login-hero__title{font-size:20px}
      .login-hero__sub{display:none}
      .login-form{padding:32px 20px}
    }
  </style>
</head>
<body>
  <div class="login-split">

    <!-- HERO -->
    <div class="login-hero">
      <div class="login-hero__bg"></div>
      <div class="login-hero__overlay"></div>
      <div class="login-hero__text">
        <div class="login-hero__badge"><span class="login-hero__logo">B</span>Bulksales</div>
        <div class="login-hero__title">Cada llamada es una oportunidad de venta.</div>
        <div class="login-hero__sub">Gestiona contactos, ventas y reportes de tu equipo en un solo lugar.</div>
      </div>
    </div>

    <!-- FORMULARIO -->
    <div class="login-form">
      <div class="login-form__inner">
        <div class="login-brand"><b>B</b>ulksales</div>

        <form id="loginForm" autocomplete="on">
          <h1 class="h4 fw-semibold mb-1">Iniciar sesión</h1>
          <p class="text-muted mb-4">Ingresa tus credenciales para continuar.</p>

          <div class="mb-3">
            <label class="form-label fw-semibold" for="username">Usuario</label>
            <input type="text" class="form-control form-control-lg" id="username" name="user"
                   maxlength="10" autocomplete="username" placeholder="javier" autofocus>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" for="password">Contraseña</label>
            <div class="input-group input-group-lg">
              <input type="password" class="form-control" id="password" name="pass"
                     maxlength="15" autocomplete="current-password" placeholder="••••••••">
              <button class="btn btn-outline-secondary" type="button" id="togglePw">Mostrar</button>
            </div>
          </div>

          <div class="alert alert-danger d-none" id="loginError" role="alert"></div>
          <div class="alert alert-info<?php echo isset($_GET['expirado']) ? '' : ' d-none'; ?>" id="loginInfo" role="alert"><?php echo isset($_GET['expirado']) ? 'Tu sesión expiró. Vuelve a iniciar sesión.' : ''; ?></div>

          <button type="submit" class="btn btn-primary btn-lg w-100" id="btnLogin">Iniciar sesión</button>

          <div class="text-center mt-3">
            <button type="button" class="btn btn-link p-0 text-decoration-underline" id="resetLink">¿Olvidaste o necesitas reiniciar tu contraseña?</button>
          </div>
        </form>

        <div class="login-foot">
          Este sitio fue desarrollado por @jehh_50<br>
          <?php echo defined('APP_COPY') ? APP_COPY : 'Copyright © 2026'; ?>
        </div>
      </div>
    </div>

  </div>

  <script src="<?php echo asset('public/js/bootstrap.bundle.min.js'); ?>"></script>
  <script>
    (function () {
      var form = document.getElementById('loginForm');
      var user = document.getElementById('username');
      var pass = document.getElementById('password');
      var err = document.getElementById('loginError');
      var info = document.getElementById('loginInfo');
      var btn = document.getElementById('btnLogin');

      // Mostrar / ocultar contraseña
      document.getElementById('togglePw').addEventListener('click', function () {
        var show = pass.type === 'password';
        pass.type = show ? 'text' : 'password';
        this.textContent = show ? 'Ocultar' : 'Mostrar';
      });

      // Enlace de reinicio (sin backend): aviso
      document.getElementById('resetLink').addEventListener('click', function () {
        err.classList.add('d-none');
        info.textContent = 'Para reiniciar tu contraseña, contacta al administrador del sistema.';
        info.classList.remove('d-none');
      });

      [user, pass].forEach(function (el) {
        el.addEventListener('input', function () { err.classList.add('d-none'); info.classList.add('d-none'); });
      });

      form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        err.classList.add('d-none'); info.classList.add('d-none');

        if (!user.value.trim() || !pass.value) {
          err.textContent = 'Ingresa tu usuario y contraseña.';
          err.classList.remove('d-none');
          return;
        }

        btn.disabled = true; btn.textContent = 'Ingresando…';

        var body = new URLSearchParams({ user: user.value.trim(), pass: pass.value });
        fetch('?view=session&mode=login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString()
        })
          .then(function (r) { return r.text(); })
          .then(function (txt) {
            var code = txt.trim();
            if (code === '2') {
              btn.disabled = false; btn.textContent = 'Iniciar sesión';
              err.textContent = 'Usuario o contraseña inválido.';
              err.classList.remove('d-none');
            } else if (code === '3') {
              btn.disabled = false; btn.textContent = 'Iniciar sesión';
              err.textContent = 'Cuenta inactiva. Contacta al administrador.';
              err.classList.remove('d-none');
            } else {
              window.location = '?view=formulario&mode=index';
            }
          })
          .catch(function () {
            btn.disabled = false; btn.textContent = 'Iniciar sesión';
            err.textContent = 'No se pudo iniciar sesión. Intenta nuevamente.';
            err.classList.remove('d-none');
          });
      });
    })();
  </script>
</body>
</html>
