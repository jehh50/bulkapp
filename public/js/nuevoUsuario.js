/**
 * Alta de usuario en su propia ruta (?view=usuarios&mode=new).
 * Mismas reglas que el alta inline de usuarios.js; sin jQuery (el layout nuevo no lo carga).
 * El token CSRF lo añade csrf.js al envolver fetch().
 */
(function () {
  'use strict';

  var form = document.getElementById('f_newuser');
  if (!form) {
    return;
  }

  var caja = document.getElementById('newUserError');
  var boton = document.getElementById('btn-register');
  var spinner = document.getElementById('newUserSpinner');
  var patronUser = /^[A-Za-z0-9._-]+$/;

  Array.prototype.forEach.call(form.querySelectorAll('[data-mayus]'), function (campo) {
    campo.addEventListener('input', function () {
      campo.value = campo.value.toUpperCase();
    });
  });

  function mostrarError(mensaje) {
    caja.textContent = mensaje;
    caja.classList.remove('d-none');
  }

  function ocultarError() {
    caja.textContent = '';
    caja.classList.add('d-none');
  }

  function ocupado(si) {
    boton.disabled = si;
    spinner.classList.toggle('d-none', !si);
  }

  form.addEventListener('submit', function (evento) {
    evento.preventDefault();
    ocultarError();

    var datos = {
      nombre: document.getElementById('nombre').value.trim(),
      apellido: document.getElementById('apellido').value.trim(),
      user: document.getElementById('user').value.trim(),
      pass: document.getElementById('nuevaClave').value,
      tipo: document.getElementById('tipo_usuario').value,
      cliente: document.getElementById('servicio').value
    };

    if (!datos.nombre || !datos.apellido || !datos.user || !datos.tipo || !datos.cliente) {
      return mostrarError('Completa todos los campos.');
    }
    if (!patronUser.test(datos.user)) {
      return mostrarError('El usuario solo admite letras, números, punto, guion y guion bajo.');
    }
    if (datos.pass.length < 8) {
      return mostrarError('La contraseña debe tener al menos 8 caracteres.');
    }

    ocupado(true);
    fetch('?view=usuarios&mode=registro', {
      method: 'POST',
      body: new URLSearchParams(datos),
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (respuesta) {
        if (respuesta.response === 'true') {
          // usuarios.js muestra este aviso al cargar el listado.
          try { sessionStorage.setItem('usToast', 'Usuario creado correctamente.'); } catch (e) { /* sin storage */ }
          window.location.href = '?view=usuarios&mode=index';
          return;
        }
        ocupado(false);
        mostrarError(respuesta.message || 'No se pudo registrar el usuario.');
      })
      .catch(function () {
        ocupado(false);
        mostrarError('No se pudo registrar el usuario. Intenta nuevamente.');
      });
  });
})();
