(function () {
  'use strict';

  var meta = document.querySelector('meta[name="csrf-token"]');
  var token = meta ? meta.getAttribute('content') : '';

  if (!token) {
    return;
  }

  function agregarCampo(form) {
    if (!form || (form.method || 'GET').toUpperCase() !== 'POST') {
      return;
    }
    if (form.querySelector('input[name="csrf_token"]')) {
      return;
    }

    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'csrf_token';
    input.value = token;
    form.appendChild(input);
  }

  // Formularios HTML normales (incluye formularios creados dinámicamente).
  document.addEventListener('submit', function (evento) {
    agregarCampo(evento.target);
  }, true);

  // XMLHttpRequest: cubre todas las llamadas AJAX de jQuery y librerías legacy.
  var xhrSendOriginal = XMLHttpRequest.prototype.send;
  XMLHttpRequest.prototype.send = function (body) {
    try {
      this.setRequestHeader('X-CSRF-Token', token);
    } catch (e) {
      // Si el encabezado ya no puede modificarse, se ignora.
    }
    return xhrSendOriginal.call(this, body);
  };

  // fetch(): usado por las vistas nuevas.
  if (typeof window.fetch === 'function') {
    var fetchOriginal = window.fetch;
    window.fetch = function (input, init) {
      init = init || {};
      var metodo = (init.method || (typeof Request !== 'undefined' && input instanceof Request ? input.method : 'GET')).toUpperCase();

      if (metodo !== 'GET' && metodo !== 'HEAD') {
        var headers = new Headers(init.headers || (typeof Request !== 'undefined' && input instanceof Request ? input.headers : undefined));
        if (!headers.has('X-CSRF-Token')) {
          headers.set('X-CSRF-Token', token);
        }
        init.headers = headers;
      }

      return fetchOriginal.call(window, input, init);
    };
  }
})();
