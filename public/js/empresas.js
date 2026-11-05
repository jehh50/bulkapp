/* Clientes y campañas — vista del Administrador.
 * Bootstrap 5 + jQuery 3.7.1. Endpoints JSON en ?view=empresas&mode=...;
 * csrf.js adjunta el token automáticamente. */
(function () {
  'use strict';

  $(function () {
    var BOOT = window.EMPRESAS_BOOT || { empresas: [], estados: {}, flujos: {}, hoy: '' };
    var ACTIVO = BOOT.estados.activo;
    var INACTIVO = BOOT.estados.inactivo;
    var FLUJO_LABEL = {};
    FLUJO_LABEL[BOOT.flujos.bancario] = 'Bancario';
    FLUJO_LABEL[BOOT.flujos.dinamico] = 'Dinámico';

    var empresas = (BOOT.empresas || []).slice();
    var empresa = null;      // cliente abierto en la ficha
    var campanas = [];       // campañas del cliente abierto
    var usuarios = [];       // usuarios del modal de asignación
    var campanaUsuarios = null;

    var campanaModal = new bootstrap.Modal(document.getElementById('empModalCampana'));
    var usuariosModal = new bootstrap.Modal(document.getElementById('empModalUsuarios'));
    var toastEl = document.getElementById('empToast');
    var toastObj = new bootstrap.Toast(toastEl, { delay: 4000 });

    // ---------- Utilidades ----------
    function toast(msg, tipo) {
      toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
      toastEl.classList.add(tipo === 'error' ? 'text-bg-danger' : (tipo === 'aviso' ? 'text-bg-warning' : 'text-bg-success'));
      $('#empToastMsg').text(msg);
      toastObj.show();
    }
    function esc(s) {
      return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }
    function fmtFecha(s) {
      if (!s) { return '—'; }
      var p = String(s).slice(0, 10).split('-');
      return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : s;
    }
    function fmtNum(n) { return (n === null || n === undefined || n === '') ? '—' : Number(n).toLocaleString('es-VE'); }
    function esActivo(id) { return parseInt(id, 10) === ACTIVO; }
    function estadoPill(id, fem) {
      return esActivo(id)
        ? '<span class="emp-estado emp-estado--activo">' + (fem ? 'Activa' : 'Activo') + '</span>'
        : '<span class="emp-estado emp-estado--inactivo">' + (fem ? 'Inactiva' : 'Inactivo') + '</span>';
    }
    function flujoPill(f) {
      return '<span class="emp-flujo emp-flujo--' + esc(f) + '">' + esc(FLUJO_LABEL[f] || f) + '</span>';
    }

    function getJSON(mode, params) {
      var q = $.param($.extend({ view: 'empresas', mode: mode }, params || {}));
      return fetch('index.php?' + q, { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); });
    }
    function postJSON(mode, data) {
      var fd = new FormData();
      Object.keys(data).forEach(function (k) {
        var v = data[k];
        if (Array.isArray(v)) { v.forEach(function (x) { fd.append(k + '[]', x); }); }
        else if (v !== null && v !== undefined) { fd.append(k, v); }
      });
      return fetch('index.php?view=empresas&mode=' + mode, {
        method: 'POST', body: fd, headers: { Accept: 'application/json' }
      }).then(function (r) { return r.json(); });
    }
    function fallo() { toast('No se pudo completar la operación. Intenta de nuevo.', 'error'); }

    // ---------- Listado de clientes ----------
    function renderList() {
      var term = ($('#empSearch').val() || '').toLowerCase();
      var filtro = $('#empFiltroEstado').val();
      var $tb = $('#empRows').empty();
      var visibles = 0;

      empresas.forEach(function (e) {
        var hay = (String(e.nombre) + ' ' + String(e.identificacion || '')).toLowerCase();
        if (term && hay.indexOf(term) < 0) { return; }
        if (filtro && String(e.status_id) !== String(filtro)) { return; }
        visibles++;

        var $tr = $('<tr>');
        $tr.append($('<td class="ta-left">').html('<strong>' + esc(e.nombre) + '</strong>' +
          (e.identificacion ? '<span class="emp-sub">' + esc(e.identificacion) + '</span>' : '')));
        $tr.append($('<td>').text((e.campanas_activas || 0) + ' / ' + (e.campanas || 0)));
        $tr.append($('<td>').text(e.usuarios || 0));
        $tr.append($('<td>').html(estadoPill(e.status_id)));
        var $acc = $('<td>');
        $acc.append($('<button type="button" class="btn btn-sm btn-outline-primary" title="Abrir ficha"><i class="fi fi-rr-edit"></i> Abrir</button>')
          .on('click', function () { abrirEmpresa(e.id); }));
        $tr.append($acc);
        $tb.append($tr);
      });

      $('#empEmpty').toggle(visibles === 0);
    }

    function refrescarLista() {
      return getJSON('list').then(function (res) {
        if (res.response === 'true') { empresas = res.empresas || []; renderList(); }
      });
    }

    function mostrarLista() {
      empresa = null;
      $('#empDetailView').hide();
      $('#empListView').show();
      refrescarLista();
    }

    // ---------- Ficha del cliente ----------
    function pintarEstadoEmpresa() {
      $('#empEstadoLbl').text($('#empEstado').is(':checked') ? 'Activo' : 'Inactivo');
    }

    function pintarFicha() {
      var nuevo = !empresa || !empresa.id;
      $('#empId').val(nuevo ? '' : empresa.id);
      $('#empNombre').val(nuevo ? '' : empresa.nombre);
      $('#empRif').val(nuevo ? '' : (empresa.identificacion || ''));
      $('#empEstado').prop('checked', nuevo ? true : esActivo(empresa.status_id));
      pintarEstadoEmpresa();
      $('#empCampanasCard').toggle(!nuevo);
      $('#empCampanasHint').toggle(nuevo);
      $('#empResumen').toggle(!nuevo);
      $('#empListView').hide();
      $('#empDetailView').show();
      renderCampanas();
    }

    function nuevaEmpresa() {
      empresa = { id: null };
      campanas = [];
      pintarFicha();
      $('#empNombre').trigger('focus');
    }

    function abrirEmpresa(id) {
      return getJSON('detalle', { id: id }).then(function (res) {
        if (res.response !== 'true') { toast(res.message || 'Cliente no encontrado.', 'error'); return; }
        empresa = res.empresa;
        campanas = res.campanas || [];
        pintarFicha();
      }).catch(fallo);
    }

    function guardarEmpresa() {
      var datos = {
        id: $('#empId').val(),
        nombre: $.trim($('#empNombre').val()),
        identificacion: $.trim($('#empRif').val()),
        status_id: $('#empEstado').is(':checked') ? ACTIVO : INACTIVO
      };
      if (!datos.nombre) { toast('El nombre del cliente es obligatorio.', 'error'); return; }

      var activas = campanas.filter(function (c) { return esActivo(c.status_id); }).length;
      if (datos.id && datos.status_id === INACTIVO && empresa && esActivo(empresa.status_id) && activas > 0 &&
          !window.confirm('Al inactivar el cliente se inactivarán sus ' + activas + ' campaña(s) activa(s). ¿Continuar?')) {
        return;
      }

      $('#empGuardar').prop('disabled', true);
      postJSON('saveEmpresa', datos).then(function (res) {
        if (res.response !== 'true') { toast(res.message || 'No se pudo guardar el cliente.', 'error'); return; }
        var msg = datos.id ? 'Cliente actualizado.' : 'Cliente creado. Ya puedes registrar sus campañas.';
        if (res.campanas_inactivadas > 0) { msg += ' Se inactivaron ' + res.campanas_inactivadas + ' campaña(s).'; }
        toast(msg);
        return abrirEmpresa(res.id);
      }).catch(fallo).then(function () { $('#empGuardar').prop('disabled', false); });
    }

    // ---------- Campañas ----------
    function renderCampanas() {
      var $tb = $('#empCampRows').empty();
      var activas = 0;

      campanas.forEach(function (c) {
        if (esActivo(c.status_id)) { activas++; }
        var $tr = $('<tr>');
        $tr.append($('<td class="ta-left">').html('<strong>' + esc(c.nombre) + '</strong>' +
          (c.detalle ? '<span class="emp-sub">' + esc(c.detalle) + '</span>' : '') +
          (c.codigo ? '<span class="emp-sub">Código ' + esc(c.codigo) + '</span>' : '')));
        $tr.append($('<td>').html(flujoPill(c.tipo_flujo)));
        $tr.append($('<td>').text(fmtFecha(c.fecha_inicio)));
        $tr.append($('<td>').text(fmtNum(c.meta)));
        $tr.append($('<td>').text(c.usuarios || 0));
        $tr.append($('<td>').text(c.productos || 0));
        $tr.append($('<td>').html(estadoPill(c.status_id, true)));
        var $acc = $('<td class="text-nowrap">');
        $acc.append($('<button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Usuarios asignados"><i class="fi fi-rr-users"></i></button>')
          .on('click', function () { abrirUsuarios(c); }));
        $acc.append($('<button type="button" class="btn btn-sm btn-outline-primary" title="Editar campaña"><i class="fi fi-rr-edit"></i></button>')
          .on('click', function () { abrirCampana(c); }));
        $tr.append($acc);
        $tb.append($tr);
      });

      $('#empCampEmpty').toggle(campanas.length === 0);
      $('#empResumen').text(activas + ' de ' + campanas.length + ' campaña(s) activa(s)');
    }

    function pintarCampanaForm() {
      var bancario = $('#campFlujo').val() === BOOT.flujos.bancario;
      $('#campCodigoOpc').text(bancario ? '(obligatorio)' : '(opcional)');
      $('#campEstadoLbl').text($('#campEstado').is(':checked') ? 'Activa' : 'Inactiva');
    }

    function abrirCampana(c) {
      var nueva = !c;
      $('#empModalCampanaTitulo').text(nueva ? 'Nueva campaña · ' + empresa.nombre : 'Editar campaña · ' + c.nombre);
      $('#campId').val(nueva ? '' : c.id);
      $('#campNombre').val(nueva ? '' : c.nombre);
      $('#campDetalle').val(nueva ? '' : (c.detalle || ''));
      $('#campFecha').val(nueva ? BOOT.hoy : String(c.fecha_inicio || '').slice(0, 10));
      $('#campMeta').val(nueva ? '' : (c.meta || ''));
      $('#campFlujo').val(nueva ? BOOT.flujos.dinamico : c.tipo_flujo).prop('disabled', !nueva);
      $('#campFlujoAyuda').text(nueva ? 'Se fija al crear la campaña.' : 'El flujo no se modifica una vez creada la campaña.');
      $('#campCodigo').val(nueva ? '' : (c.codigo || ''));
      $('#campEstado').prop('checked', nueva ? esActivo(empresa.status_id) : esActivo(c.status_id))
        .prop('disabled', !esActivo(empresa.status_id) && (nueva || !esActivo(c.status_id)));
      $('#campError').hide().text('');
      pintarCampanaForm();
      campanaModal.show();
    }

    function guardarCampana() {
      var datos = {
        id: $('#campId').val(),
        empresa_id: empresa.id,
        nombre: $.trim($('#campNombre').val()),
        detalle: $.trim($('#campDetalle').val()),
        fecha_inicio: $('#campFecha').val(),
        meta: $.trim($('#campMeta').val()),
        tipo_flujo: $('#campFlujo').val(),
        codigo: $.trim($('#campCodigo').val()),
        status_id: $('#campEstado').is(':checked') ? ACTIVO : INACTIVO
      };
      if (datos.id) { delete datos.tipo_flujo; }

      var errores = [];
      if (!datos.nombre) { errores.push('El nombre es obligatorio.'); }
      if (!datos.fecha_inicio) { errores.push('La fecha de inicio es obligatoria.'); }
      if (datos.meta !== '' && !(/^\d+$/.test(datos.meta) && parseInt(datos.meta, 10) > 0)) { errores.push('La meta debe ser un entero mayor que cero.'); }
      if (!datos.id && datos.tipo_flujo === BOOT.flujos.bancario && !datos.codigo) { errores.push('Las campañas bancarias requieren código.'); }
      if (errores.length) { $('#campError').text(errores.join(' ')).show(); return; }

      $('#campGuardar').prop('disabled', true);
      postJSON('saveCampana', datos).then(function (res) {
        if (res.response !== 'true') { $('#campError').text(res.message || 'No se pudo guardar la campaña.').show(); return; }
        campanaModal.hide();
        toast(datos.id ? 'Campaña actualizada.' : 'Campaña creada. Asígnale usuarios con el botón de usuarios.');
        return abrirEmpresa(empresa.id);
      }).catch(fallo).then(function () { $('#campGuardar').prop('disabled', false); });
    }

    // ---------- Usuarios asignados ----------
    function contarAsignados() {
      var n = usuarios.filter(function (u) { return u._sel; }).length;
      $('#usrConteo').text(n + (n === 1 ? ' asignado' : ' asignados'));
    }

    function renderUsuarios() {
      var term = ($('#usrBuscar').val() || '').toLowerCase();
      var solo = $('#usrSoloAsignados').is(':checked');
      var $lst = $('#usrLista').empty();
      var visibles = 0;

      usuarios.forEach(function (u) {
        var hay = [u.nombre, u.apellido, u.user, u.rol].join(' ').toLowerCase();
        if (term && hay.indexOf(term) < 0) { return; }
        if (solo && !u._sel) { return; }
        visibles++;

        var id = 'usr' + u.id;
        var $row = $('<label class="emp-user" for="' + id + '">');
        var $chk = $('<input type="checkbox" class="form-check-input" id="' + id + '">').prop('checked', !!u._sel);
        $chk.on('change', function () { u._sel = this.checked; contarAsignados(); });
        $row.append($chk);
        $row.append('<div class="emp-user__id"><div class="emp-user__name">' + esc(u.nombre + ' ' + u.apellido) +
          '</div><div class="emp-user__login">' + esc(u.user) + '</div></div>');
        if (parseInt(u.principal, 10) === 1) { $row.append('<span class="emp-principal">Principal</span>'); }
        $row.append('<span class="emp-rol">' + esc(u.rol) + '</span>');
        $lst.append($row);
      });

      $('#usrVacio').toggle(visibles === 0);
      $('#usrLista').toggle(visibles > 0);
    }

    function abrirUsuarios(c) {
      getJSON('usuarios', { servicio_id: c.id }).then(function (res) {
        if (res.response !== 'true') { toast(res.message || 'Campaña no encontrada.', 'error'); return; }
        campanaUsuarios = c;
        usuarios = (res.usuarios || []).map(function (u) { u._sel = parseInt(u.asignado, 10) === 1; return u; });
        $('#usrCampana').text(empresa.nombre + ' › ' + c.nombre);
        $('#usrBuscar').val('');
        $('#usrSoloAsignados').prop('checked', false);
        renderUsuarios();
        contarAsignados();
        usuariosModal.show();
      }).catch(fallo);
    }

    function guardarUsuarios() {
      var ids = usuarios.filter(function (u) { return u._sel; }).map(function (u) { return u.id; });
      $('#usrGuardar').prop('disabled', true);
      postJSON('asignar', { servicio_id: campanaUsuarios.id, usuarios: ids }).then(function (res) {
        if (res.response !== 'true') { toast(res.message || 'No se pudo guardar la asignación.', 'error'); return; }
        usuariosModal.hide();
        var partes = [];
        if (res.agregados) { partes.push(res.agregados + ' agregado(s)'); }
        if (res.quitados) { partes.push(res.quitados + ' quitado(s)'); }
        if (res.principal_movida) { partes.push(res.principal_movida + ' con nueva campaña principal'); }
        var msg = 'Asignación guardada' + (partes.length ? ': ' + partes.join(', ') + '.' : ' (sin cambios).');
        if (res.bloqueados && res.bloqueados.length) {
          msg += ' No se quitó a ' + res.bloqueados.map(function (b) { return b.user; }).join(', ') +
            ': es su única campaña.';
          toast(msg, 'aviso');
        } else {
          toast(msg);
        }
        return abrirEmpresa(empresa.id);
      }).catch(fallo).then(function () { $('#usrGuardar').prop('disabled', false); });
    }

    // ---------- Eventos ----------
    $('#empSearch').on('input', renderList);
    $('#empFiltroEstado').on('change', renderList);
    $('#empNuevo').on('click', nuevaEmpresa);
    $('#empVolver').on('click', mostrarLista);
    $('#empEstado').on('change', pintarEstadoEmpresa);
    $('#empGuardar').on('click', guardarEmpresa);
    $('#empNuevaCampana').on('click', function () { abrirCampana(null); });
    $('#campFlujo, #campEstado').on('change', pintarCampanaForm);
    $('#campGuardar').on('click', guardarCampana);
    $('#usrBuscar').on('input', renderUsuarios);
    $('#usrSoloAsignados').on('change', renderUsuarios);
    $('#usrGuardar').on('click', guardarUsuarios);

    renderList();
  });
})();
