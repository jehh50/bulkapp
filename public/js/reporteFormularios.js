/**
 * Reporte de formularios dinámicos: selects en cascada cliente → campaña → formulario
 * sobre window.REPORTE_ARBOL, y cambio de «Filas por página».
 */
(function () {
  'use strict';

  var arbol = Array.isArray(window.REPORTE_ARBOL) ? window.REPORTE_ARBOL : [];
  var selCliente = document.getElementById('repCliente');
  var selCampana = document.getElementById('repCampana');
  var selFormulario = document.getElementById('repFormulario');
  var btnBuscar = document.getElementById('repBuscar');
  if (!selCliente || !selCampana || !selFormulario || !btnBuscar) {
    return;
  }

  function buscar(lista, id) {
    for (var i = 0; i < lista.length; i++) {
      if (String(lista[i].id) === String(id)) {
        return lista[i];
      }
    }
    return null;
  }

  function llenar(select, lista, vacio, seleccionado) {
    select.innerHTML = '';
    var op = document.createElement('option');
    op.value = '';
    op.textContent = vacio;
    select.appendChild(op);
    lista.forEach(function (n) {
      var o = document.createElement('option');
      o.value = n.id;
      o.textContent = n.nombre;
      if (String(n.id) === String(seleccionado)) {
        o.selected = true;
      }
      select.appendChild(o);
    });
    select.disabled = lista.length === 0;
  }

  function refrescarCampanas(seleccionada) {
    var cliente = buscar(arbol, selCliente.value);
    if (!cliente) {
      llenar(selCampana, [], 'Elige un cliente', '');
    } else {
      llenar(selCampana, cliente.campanas, 'Selecciona…', seleccionada);
      // Con una sola campaña se elige sola.
      if (cliente.campanas.length === 1) {
        selCampana.value = cliente.campanas[0].id;
      }
    }
  }

  function refrescarFormularios(seleccionado) {
    var cliente = buscar(arbol, selCliente.value);
    var campana = cliente ? buscar(cliente.campanas, selCampana.value) : null;
    if (!campana) {
      llenar(selFormulario, [], selCliente.value ? 'Elige una campaña' : 'Elige un cliente', '');
    } else {
      llenar(selFormulario, campana.formularios, 'Selecciona…', seleccionado);
      if (campana.formularios.length === 1) {
        selFormulario.value = campana.formularios[0].id;
      }
    }
    btnBuscar.disabled = !selFormulario.value;
  }

  llenar(selCliente, arbol, 'Selecciona…', selCliente.getAttribute('data-sel'));
  selCliente.disabled = false;
  refrescarCampanas(selCampana.getAttribute('data-sel'));
  refrescarFormularios(selFormulario.getAttribute('data-sel'));

  selCliente.addEventListener('change', function () {
    refrescarCampanas('');
    refrescarFormularios('');
  });
  selCampana.addEventListener('change', function () {
    refrescarFormularios('');
  });
  selFormulario.addEventListener('change', function () {
    btnBuscar.disabled = !selFormulario.value;
  });

  var selTamano = document.getElementById('repTamano');
  if (selTamano) {
    selTamano.addEventListener('change', function () {
      selTamano.form.submit();
    });
  }
})();
