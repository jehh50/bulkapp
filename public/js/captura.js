/* Captura de formularios dinámicos (Constructor, Fase 3).
 * Con BD vigente: buscar registro → habilitar formulario → guardar (el registro queda
 * atendido). Sin BD: el formulario está habilitado desde el inicio.
 * Endpoints: ?view=captura&mode=buscar|guardar; csrf.js adjunta el token. */
(function () {
  'use strict';

  $(function () {
    var BOOT = window.CAPTURA_BOOT;
    if (!BOOT) { return; }

    var $campos = $('#capCampos');
    var bd = BOOT.bd;
    var mapa = {};
    var filaId = null;

    var toastEl = document.getElementById('capToast');
    var toastObj = new bootstrap.Toast(toastEl, { delay: 3500 });
    function toast(msg, err) {
      toastEl.classList.toggle('text-bg-success', !err);
      toastEl.classList.toggle('text-bg-danger', !!err);
      $('#capToastMsg').text(msg);
      toastObj.show();
    }

    function postJSON(mode, data) {
      var fd = new FormData();
      Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
      return fetch('index.php?view=captura&mode=' + mode, { method: 'POST', body: fd }).then(function (r) { return r.json(); });
    }

    function habilitar(on) {
      $('#capFieldset').prop('disabled', !on);
      $('#capHint').toggle(!on);
    }

    function mostrarRegistro(datos) {
      $('#capRegistro [data-col]').each(function () {
        var v = datos ? datos[$(this).attr('data-col')] : '';
        $(this).text(v ? v : '—');
      });
    }

    function errorBusqueda(msg) {
      $('#capBuscarError').text(msg || '').toggle(!!msg);
    }

    function reiniciar() {
      mapa = FormDinamico.render($campos, BOOT.campos);
      filaId = null;
      if (bd) {
        mostrarRegistro(null);
        errorBusqueda('');
        habilitar(false);
        $('#capClave').val('').trigger('focus');
      }
    }

    $('#capBuscar').on('submit', function (e) {
      e.preventDefault();
      var clave = $.trim($('#capClave').val());
      if (!clave) { errorBusqueda('Ingresa un valor para buscar.'); return; }
      errorBusqueda('');
      postJSON('buscar', { clave: clave }).then(function (res) {
        if (res.response === 'true') {
          mapa = FormDinamico.render($campos, BOOT.campos);
          filaId = res.registro.id;
          mostrarRegistro(res.registro.datos);
          habilitar(true);
        } else {
          filaId = null;
          mostrarRegistro(null);
          habilitar(false);
          errorBusqueda(res.message || 'No se encontró el registro.');
        }
      }).catch(function () { toast('No se pudo completar la búsqueda.', true); });
    });

    $('#capForm').on('submit', function (e) {
      e.preventDefault();
      if (bd && !filaId) { toast('Busca un registro antes de guardar.', true); return; }
      if (!FormDinamico.validar($campos, mapa)) { toast('Revisa los campos marcados.', true); return; }

      var $btn = $('#capGuardar').prop('disabled', true);
      postJSON('guardar', {
        valores: JSON.stringify(FormDinamico.leer($campos, mapa)),
        fila_id: filaId || ''
      }).then(function (res) {
        if (res.response === 'true') {
          toast('Gestión guardada.');
          reiniciar();
        } else if (res.response === 'atendido') {
          toast(res.message || 'Este registro ya fue atendido.', true);
          reiniciar();
        } else {
          FormDinamico.marcarErrores($campos, res.errores);
          toast(res.message || 'No se pudo guardar.', true);
        }
      }).catch(function () {
        toast('No se pudo guardar la gestión.', true);
      }).then(function () {
        $btn.prop('disabled', false);
      });
    });

    reiniciar();
  });
})();
