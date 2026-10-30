/* Constructor de formularios — Builder (Fase 1).
 * Bootstrap 5 + jQuery 3.7.1 + jQuery UI (drag&drop). Los endpoints JSON viven
 * en ?view=constructor&mode=...; csrf.js adjunta el token automáticamente. */
(function () {
  'use strict';

  $(function () {
    var BOOT = window.CONSTRUCTOR_BOOT || { servicios: [], formularios: [], tipos: [], estados: {} };
    var ACTIVO = BOOT.estados.activo;
    var INACTIVO = BOOT.estados.inactivo;

    var TIPOS = {
      text:     { label: 'Texto', icon: 'fi-rr-text' },
      textarea: { label: 'Área de texto', icon: 'fi-rr-align-left' },
      number:   { label: 'Número', icon: 'fi-rr-calculator' },
      date:     { label: 'Fecha', icon: 'fi-rr-calendar' },
      email:    { label: 'Correo', icon: 'fi-rr-envelope' },
      select:   { label: 'Lista (select)', icon: 'fi-rr-list' },
      nested:   { label: 'Anidado (2 niveles)', icon: 'fi-rr-apps' }
    };

    var forms = (BOOT.formularios || []).slice();
    var form = null;        // formulario en edición
    var selected = -1;      // índice del campo seleccionado
    var uidSeq = 1;
    var recibido = false;   // guard para sortable receive/update
    var aEliminar = null;
    var previewCampos = {}; // k -> campo, para validar la vista previa

    // ---------- Modales / toast ----------
    var eliminarModal = new bootstrap.Modal(document.getElementById('cnsModalEliminar'));
    var conflictoModal = new bootstrap.Modal(document.getElementById('cnsModalConflicto'));
    var previewModal = new bootstrap.Modal(document.getElementById('cnsModalPreview'));
    var toastEl = document.getElementById('cnsToast');
    var toastObj = new bootstrap.Toast(toastEl, { delay: 3000 });

    function toast(msg, err) {
      toastEl.classList.toggle('text-bg-success', !err);
      toastEl.classList.toggle('text-bg-danger', !!err);
      $('#cnsToastMsg').text(msg);
      toastObj.show();
    }

    // ---------- Utilidades ----------
    function esc(s) {
      return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }
    function fmtFecha(s) { return s ? String(s).slice(0, 16).replace('T', ' ') : ''; }

    function getJSON(mode, params) {
      var q = $.param($.extend({ view: 'constructor', mode: mode }, params || {}));
      return fetch('index.php?' + q, { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); });
    }
    function postJSON(mode, data) {
      var fd = new FormData();
      Object.keys(data).forEach(function (k) {
        var v = data[k];
        if (Array.isArray(v)) { v.forEach(function (x) { fd.append(k + '[]', x); }); }
        else { fd.append(k, v); }
      });
      return fetch('index.php?view=constructor&mode=' + mode, { method: 'POST', body: fd }).then(function (r) { return r.json(); });
    }

    // ---------- Listado ----------
    function estadoPill(id) {
      return parseInt(id, 10) === ACTIVO
        ? '<span class="cns-estado cns-estado--activo">Activo</span>'
        : '<span class="cns-estado cns-estado--inactivo">Inactivo</span>';
    }

    function renderList() {
      var term = ($('#cnsSearch').val() || '').toLowerCase();
      var filtro = $('#cnsFiltroEstado').val();
      var $tb = $('#cnsRows').empty();
      var visibles = 0;

      forms.forEach(function (f) {
        if (term && String(f.nombre).toLowerCase().indexOf(term) < 0) { return; }
        if (filtro && String(f.status_id) !== String(filtro)) { return; }
        visibles++;

        var servicios = (f.servicios || []).map(function (s) { return esc(s.descripcion); }).join(', ') || '—';
        var $tr = $('<tr>');
        $tr.append($('<td class="ta-left">').html('<strong>' + esc(f.nombre) + '</strong>'));
        $tr.append($('<td>').html(servicios));
        $tr.append($('<td>').text(f.num_campos || 0));
        $tr.append($('<td>').text(f.version_vigente || '—'));
        $tr.append($('<td>').html(estadoPill(f.status_id)));
        $tr.append($('<td>').text(fmtFecha(f.actualizado)));

        var $acc = $('<td>');
        $acc.append($('<button class="btn btn-sm btn-outline-secondary me-1" title="Vista previa"><i class="fi fi-rr-eye"></i></button>')
          .on('click', function () { previewServidor(f.id); }));
        $acc.append($('<button class="btn btn-sm btn-outline-primary me-1" title="Editar"><i class="fi fi-rr-edit"></i></button>')
          .on('click', function () { editarForm(f.id); }));
        $acc.append($('<button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fi fi-rr-trash"></i></button>')
          .on('click', function () { pedirEliminar(f); }));
        $tr.append($acc);
        $tb.append($tr);
      });

      $('#cnsEmpty').toggle(visibles === 0);
    }

    function refrescarLista(cb) {
      getJSON('list').then(function (res) {
        if (res.response === 'true') { forms = res.formularios || []; renderList(); }
        if (cb) { cb(); }
      });
    }

    // ---------- Editor ----------
    function nuevoForm() {
      form = { id: null, nombre: '', status_id: INACTIVO, btn_texto: 'Guardar', servicios: [], campos: [] };
      selected = -1;
      abrirEditor(null);
    }

    function editarForm(id) {
      getJSON('detalle', { id: id }).then(function (res) {
        if (res.response !== 'true') { toast(res.message || 'No se pudo cargar.', true); return; }
        var f = res.formulario;
        form = {
          id: f.id,
          nombre: f.nombre,
          status_id: parseInt(f.status_id, 10),
          btn_texto: f.btn_texto || 'Guardar',
          servicios: (f.servicios || []).map(Number),
          campos: ((f.definicion && f.definicion.campos) ? f.definicion.campos : []).map(function (c) {
            c._uid = uidSeq++;
            return c;
          })
        };
        selected = -1;
        abrirEditor(f);
      });
    }

    function abrirEditor(f) {
      $('#cnsId').val(form.id || '');
      $('#cnsNombre').val(form.nombre);
      $('#cnsBtnTexto').val(form.btn_texto);
      $('#cnsEstado').prop('checked', form.status_id === ACTIVO);
      actualizarEstadoLbl();
      $('#cnsVersion').text(form.id ? ('Versión vigente: ' + (f && f.version ? f.version : '—')) : 'Nuevo');
      mostrarErrores([]);
      renderServicios();
      renderLienzo();
      renderProps();
      renderHistorial();
      $('#cnsListView').hide();
      $('#cnsEditorView').show();
      window.scrollTo(0, 0);
    }

    function volverListado() {
      $('#cnsEditorView').hide();
      $('#cnsListView').show();
    }

    function actualizarEstadoLbl() {
      $('#cnsEstadoLbl').text($('#cnsEstado').is(':checked') ? 'Activo' : 'Inactivo');
    }

    // ---------- Campos: paleta, lienzo, propiedades ----------
    function renderPaleta() {
      var $p = $('#cnsPaleta').empty();
      BOOT.tipos.forEach(function (t) {
        var meta = TIPOS[t] || { label: t, icon: 'fi-rr-square' };
        var $it = $('<div class="cns-palette__item" data-type="' + t + '"><i class="fi ' + meta.icon + '"></i><span>' + esc(meta.label) + '</span></div>');
        $it.on('click', function () { agregarCampo(t); });
        $p.append($it);
      });
      $('#cnsPaleta .cns-palette__item').draggable({
        connectToSortable: '#cnsLienzo',
        helper: 'clone',
        revert: 'invalid'
      });
    }

    function initLienzo() {
      $('#cnsLienzo').sortable({
        items: '> li.campo-chip',
        handle: '.campo-chip__drag',
        placeholder: 'campo-placeholder',
        forcePlaceholderSize: true,
        tolerance: 'pointer',
        receive: function (ev, ui) {
          var type = ui.item.attr('data-type');
          var index = ui.item.index();
          recibido = true;
          try { ui.item.remove(); } catch (e) { /* noop */ }
          setTimeout(function () { agregarCampo(type, index); }, 0);
        },
        update: function () {
          if (recibido) { recibido = false; return; }
          var orden = $('#cnsLienzo > li.campo-chip').map(function () { return parseInt($(this).attr('data-uid'), 10); }).get();
          var selUid = (selected >= 0 && form.campos[selected]) ? form.campos[selected]._uid : null;
          form.campos.sort(function (a, b) { return orden.indexOf(a._uid) - orden.indexOf(b._uid); });
          selected = selUid ? indicePorUid(selUid) : -1;
          setTimeout(renderLienzo, 0);
        }
      });
    }

    function indicePorUid(uid) {
      for (var i = 0; i < form.campos.length; i++) { if (form.campos[i]._uid === uid) { return i; } }
      return -1;
    }

    function siguienteK(type) {
      var base = (type === 'nested') ? 'anidado' : type;
      var ks = form.campos.map(function (c) { return c.k; });
      var n = 1, k;
      do { k = base + '_' + n; n++; } while (ks.indexOf(k) >= 0);
      return k;
    }

    function nuevoCampoObj(type) {
      var c = { _uid: uidSeq++, k: siguienteK(type), type: type, label: (TIPOS[type] ? TIPOS[type].label : type), req: false };
      if (type === 'select') { c.opts = []; }
      if (type === 'nested') { c.tree = []; c.lv1 = 'Nivel 1'; c.lv2 = 'Nivel 2'; }
      return c;
    }

    function agregarCampo(type, index) {
      if (!type || !TIPOS[type]) { return; }
      var campo = nuevoCampoObj(type);
      if (typeof index === 'number' && index >= 0 && index <= form.campos.length) { form.campos.splice(index, 0, campo); }
      else { form.campos.push(campo); }
      selected = indicePorUid(campo._uid);
      renderLienzo();
      renderProps();
    }

    function quitarCampo(i) {
      form.campos.splice(i, 1);
      if (selected === i) { selected = -1; }
      else if (selected > i) { selected--; }
      renderLienzo();
      renderProps();
    }

    function metaCampoHtml(c) {
      var meta = TIPOS[c.type] || { label: c.type };
      return '<span class="tag">' + esc(meta.label) + '</span>' +
        (c.req ? '<span class="tag">obligatorio</span>' : '') +
        (c.cond ? '<span class="tag">condicional</span>' : '') +
        ' · <code>' + esc(c.k || '') + '</code>';
    }

    function renderLienzo() {
      var $l = $('#cnsLienzo').empty();
      form.campos.forEach(function (c, i) {
        var $li = $('<li class="campo-chip" data-uid="' + c._uid + '">' +
          '<span class="campo-chip__drag" title="Arrastrar">⋮⋮</span>' +
          '<div class="campo-chip__body"><div class="campo-chip__label"></div><div class="campo-chip__meta"></div></div>' +
          '<div class="campo-chip__btns"><button class="btn btn-sm btn-outline-danger" title="Quitar"><i class="fi fi-rr-trash"></i></button></div>' +
          '</li>');
        $li.find('.campo-chip__label').text(c.label || '(sin etiqueta)');
        $li.find('.campo-chip__meta').html(metaCampoHtml(c));
        if (i === selected) { $li.addClass('is-selected'); }
        $li.find('.campo-chip__body').on('click', function () { selected = i; renderLienzo(); renderProps(); });
        $li.find('.campo-chip__btns button').on('click', function (e) { e.stopPropagation(); quitarCampo(i); });
        $l.append($li);
      });
      $('#cnsLienzoEmpty').toggle(form.campos.length === 0);
      if ($('#cnsLienzo').data('ui-sortable')) { $('#cnsLienzo').sortable('refresh'); }
    }

    function refrescarChip(c) {
      var $li = $('#cnsLienzo > li[data-uid="' + c._uid + '"]');
      $li.find('.campo-chip__label').text(c.label || '(sin etiqueta)');
      $li.find('.campo-chip__meta').html(metaCampoHtml(c));
    }

    function renderProps() {
      var $p = $('#cnsProps').empty();
      if (selected < 0 || !form.campos[selected]) {
        $p.html('<div class="cns-prop__empty">Selecciona un campo del lienzo.</div>');
        return;
      }
      var c = form.campos[selected];

      function row(label, $ctrl) {
        var $r = $('<div class="cns-prop__row">');
        if (label) { $r.append($('<label>').text(label)); }
        $r.append($ctrl);
        return $r;
      }
      function text(val, on, attrs) {
        var $i = $('<input class="form-control form-control-sm" type="text">').val(val || '');
        if (attrs) { $i.attr(attrs); }
        $i.on('input', function () { on($i.val()); });
        return $i;
      }
      function numInput(val, on) {
        var $i = $('<input class="form-control form-control-sm" type="number">').val((val === undefined || val === null) ? '' : val);
        $i.on('input', function () { var v = $i.val(); on(v === '' ? undefined : parseFloat(v)); refrescarChip(c); });
        return $i;
      }

      $p.append(row('Etiqueta', text(c.label, function (v) { c.label = v; refrescarChip(c); })));
      $p.append(row('Clave (k)', text(c.k, function (v) { c.k = v; refrescarChip(c); }, { maxlength: 40 })));
      $p.append(row('Placeholder', text(c.placeholder, function (v) { c.placeholder = v; })));
      $p.append(row('Ayuda', text(c.help, function (v) { c.help = v; })));

      var $req = $('<div class="form-check form-switch cns-prop__row"><input class="form-check-input" type="checkbox" id="pReq"><label class="form-check-label" for="pReq">Obligatorio</label></div>');
      $req.find('input').prop('checked', !!c.req).on('change', function () { c.req = this.checked; refrescarChip(c); });
      $p.append($req);

      if (c.type === 'text' || c.type === 'textarea' || c.type === 'number') {
        var etq = (c.type === 'number') ? 'valor' : 'longitud';
        $p.append(row('Mín (' + etq + ')', numInput(c.min, function (v) { if (v === undefined) { delete c.min; } else { c.min = v; } })));
        $p.append(row('Máx (' + etq + ')', numInput(c.max, function (v) { if (v === undefined) { delete c.max; } else { c.max = v; } })));
        $p.append(row('Regex (sin barras)', text(c.regex, function (v) { if (v) { c.regex = v; } else { delete c.regex; } })));
      }

      if (c.type === 'select') {
        var $ta = $('<textarea class="form-control form-control-sm" rows="4" placeholder="Una opción por línea"></textarea>').val((c.opts || []).join('\n'));
        $ta.on('input', function () {
          c.opts = $ta.val().split('\n').map(function (s) { return s.trim(); }).filter(function (s) { return s !== ''; });
        });
        $p.append(row('Opciones', $ta));
      }

      if (c.type === 'nested') {
        $p.append(row('Etiqueta nivel 1', text(c.lv1, function (v) { c.lv1 = v; })));
        $p.append(row('Etiqueta nivel 2', text(c.lv2, function (v) { c.lv2 = v; })));
        var $tree = $('<textarea class="form-control form-control-sm" rows="4" placeholder="Padre: hijo1, hijo2"></textarea>')
          .val((c.tree || []).map(function (n) { return n.p + ': ' + n.c; }).join('\n'));
        $tree.on('input', function () {
          c.tree = $tree.val().split('\n').map(function (line) {
            var idx = line.indexOf(':');
            if (idx < 0) { var p = line.trim(); return p ? { p: p, c: '' } : null; }
            return { p: line.slice(0, idx).trim(), c: line.slice(idx + 1).trim() };
          }).filter(function (n) { return n && n.p; });
        });
        $p.append(row('Árbol (padre: hijos)', $tree));
      }

      var previos = form.campos.slice(0, selected).filter(function (x) { return x.k; });
      if (previos.length) {
        var $sel = $('<select class="form-select form-select-sm"><option value="">— sin condición —</option></select>');
        previos.forEach(function (x) { $sel.append($('<option>').val(x.k).text(x.label + ' (' + x.k + ')')); });
        $sel.val(c.cond ? c.cond.k : '');
        var $val = text(c.cond ? c.cond.v : '', function (v) { if (c.cond) { c.cond.v = v; } }, { placeholder: 'valor que activa' });
        $val.prop('disabled', !c.cond);
        $sel.on('change', function () {
          var k = $sel.val();
          if (!k) { delete c.cond; $val.val('').prop('disabled', true); }
          else { c.cond = { k: k, v: (c.cond ? c.cond.v : '') }; $val.prop('disabled', false); }
          refrescarChip(c);
        });
        $p.append(row('Mostrar si (campo previo)', $sel));
        $p.append(row('…es igual a', $val));
      }
    }

    // ---------- Servicios ----------
    function renderServicios() {
      var $c = $('#cnsServicios').empty();
      BOOT.servicios.forEach(function (s) {
        var id = parseInt(s.id, 10);
        var $col = $('<div class="col-md-4">');
        var $chk = $('<div class="form-check"><input class="form-check-input" type="checkbox" id="srv' + id + '"><label class="form-check-label" for="srv' + id + '">' + esc(s.descripcion) + '</label></div>');
        $chk.find('input').prop('checked', form.servicios.indexOf(id) >= 0).on('change', function () {
          if (this.checked) { if (form.servicios.indexOf(id) < 0) { form.servicios.push(id); } }
          else { form.servicios = form.servicios.filter(function (x) { return x !== id; }); }
        });
        $col.append($chk);
        $c.append($col);
      });
    }

    // ---------- Historial ----------
    function renderHistorial() {
      var $h = $('#cnsHistorial');
      if (!form.id) { $h.html('<p class="text-muted mb-0">Guarda el formulario para ver su historial.</p>'); return; }
      $h.html('<p class="text-muted mb-0">Cargando…</p>');
      getJSON('historial', { id: form.id }).then(function (res) {
        var hs = res.historial || [];
        if (!hs.length) { $h.html('<p class="text-muted mb-0">Sin versiones.</p>'); return; }
        var $ul = $('<ul class="list-group">');
        hs.forEach(function (v) {
          var vig = parseInt(v.vigente, 10);
          var $li = $('<li class="list-group-item d-flex justify-content-between align-items-center">');
          $li.append($('<span>').html('v' + v.version + ' · ' + esc(fmtFecha(v.fecha)) + (v.nota ? (' · ' + esc(v.nota)) : '') + (vig ? ' · <strong>vigente</strong>' : '')));
          if (!vig) {
            $li.append($('<button class="btn btn-sm btn-outline-primary">Hacer vigente</button>').on('click', function () { hacerVigente(v.id); }));
          }
          $ul.append($li);
        });
        $h.empty().append($ul);
      });
    }

    function hacerVigente(versionId) {
      postJSON('versionVigente', { id: form.id, version_id: versionId }).then(function (res) {
        if (res.response === 'true') { toast('Versión marcada como vigente.'); renderHistorial(); refrescarLista(); }
        else { toast(res.message || 'No se pudo.', true); }
      });
    }

    // ---------- Validación (espejo cliente) ----------
    function validarDefinicionCliente(campos) {
      var err = [], vistas = {};
      if (!campos.length) { err.push('El formulario debe tener al menos un campo.'); }
      campos.forEach(function (c, i) {
        var pos = i + 1;
        if (!c.k || !/^[a-z][a-z0-9_]*$/.test(c.k)) { err.push('Campo #' + pos + ': clave inválida (minúsculas, números, guion_bajo).'); }
        else if (vistas[c.k]) { err.push('Campo #' + pos + ': clave «' + c.k + '» repetida.'); }
        if (BOOT.tipos.indexOf(c.type) < 0) { err.push('Campo #' + pos + ': tipo inválido.'); }
        if (!c.label || !String(c.label).trim()) { err.push('Campo #' + pos + ': etiqueta obligatoria.'); }
        if (c.min != null && c.max != null && c.min > c.max) { err.push('Campo #' + pos + ': «min» mayor que «max».'); }
        if (c.regex) { try { new RegExp(c.regex); } catch (e) { err.push('Campo #' + pos + ': regex inválida.'); } }
        if (c.type === 'select' && (!c.opts || !c.opts.length)) { err.push('Campo #' + pos + ': un «select» requiere opciones.'); }
        if (c.type === 'nested' && (!c.tree || !c.tree.length)) { err.push('Campo #' + pos + ': un anidado requiere nodos.'); }
        if (c.cond) {
          if (!c.cond.k) { err.push('Campo #' + pos + ': condición sin campo de referencia.'); }
          else if (!vistas[c.cond.k]) { err.push('Campo #' + pos + ': la condición debe referenciar un campo anterior.'); }
        }
        if (c.k && !vistas[c.k]) { vistas[c.k] = true; }
      });
      return err;
    }

    function mostrarErrores(errs) {
      var $e = $('#cnsErrores').empty();
      if (!errs.length) { $e.hide(); return; }
      errs.forEach(function (m) { $e.append($('<li>').text(m)); });
      $e.show();
    }

    // ---------- Guardar ----------
    function limpiarCampo(c) {
      var o = {};
      Object.keys(c).forEach(function (k) { if (k !== '_uid') { o[k] = c[k]; } });
      return o;
    }

    function guardar(reasignar) {
      form.nombre = $('#cnsNombre').val().trim();
      form.btn_texto = $('#cnsBtnTexto').val().trim() || 'Guardar';
      form.status_id = $('#cnsEstado').is(':checked') ? ACTIVO : INACTIVO;

      var errs = [];
      if (!form.nombre) { errs.push('El nombre del formulario es obligatorio.'); }
      errs = errs.concat(validarDefinicionCliente(form.campos));
      if (errs.length) { mostrarErrores(errs); return; }
      mostrarErrores([]);

      var limpio = form.campos.map(limpiarCampo);
      postJSON('save', {
        id: form.id || '',
        nombre: form.nombre,
        estado: form.status_id,
        btn_texto: form.btn_texto,
        definicion: JSON.stringify({ campos: limpio }),
        servicios: form.servicios,
        reasignar: reasignar ? '1' : ''
      }).then(function (res) {
        if (res.response === 'true') {
          form.id = res.id;
          toast('Formulario guardado (v' + res.version + ').');
          refrescarLista(volverListado);
        } else if (res.response === 'conflict') {
          mostrarConflicto(res.conflictos || []);
        } else {
          mostrarErrores((res.errores && res.errores.length) ? res.errores : [res.message || 'No se pudo guardar.']);
        }
      });
    }

    function mostrarConflicto(conf) {
      var $l = $('#cnsConflictoLista').empty();
      conf.forEach(function (c) { $l.append($('<li>').html(esc(c.servicio) + ' — ocupado por «' + esc(c.formulario) + '»')); });
      conflictoModal.show();
    }

    // ---------- Eliminar ----------
    function pedirEliminar(f) {
      aEliminar = f;
      $('#cnsEliminarNombre').text(f.nombre);
      eliminarModal.show();
    }

    // ---------- Vista previa ----------
    function previewServidor(id) {
      getJSON('preview', { id: id }).then(function (res) {
        if (res.response !== 'true') { toast(res.message || 'No disponible.', true); return; }
        renderPreview(res.formulario.nombre, res.formulario.definicion.campos || [], res.formulario.btn_texto);
      });
    }
    function previewEditor() {
      renderPreview($('#cnsNombre').val() || '(sin nombre)', form ? form.campos : [], $('#cnsBtnTexto').val() || 'Guardar');
    }

    function construirControl(c) {
      if (c.type === 'textarea') { return $('<textarea class="form-control">').attr('name', c.k).attr('placeholder', c.placeholder || ''); }
      if (c.type === 'select') {
        var $s = $('<select class="form-select"><option value="">—</option></select>').attr('name', c.k);
        (c.opts || []).forEach(function (o) { $s.append($('<option>').val(o).text(o)); });
        return $s;
      }
      if (c.type === 'nested') {
        var $w = $('<div>');
        var $p = $('<select class="form-select mb-2" data-nivel="1">').append($('<option value="">').text(c.lv1 || 'Nivel 1'));
        var $h = $('<select class="form-select" data-nivel="2">').append($('<option value="">').text(c.lv2 || 'Nivel 2'));
        (c.tree || []).forEach(function (n) { $p.append($('<option>').val(n.p).text(n.p)); });
        $p.on('change', function () {
          $h.empty().append($('<option value="">').text(c.lv2 || 'Nivel 2'));
          var nodo = (c.tree || []).filter(function (n) { return n.p === $p.val(); })[0];
          if (nodo) {
            nodo.c.split(',').map(function (s) { return s.trim(); }).filter(Boolean).forEach(function (ch) { $h.append($('<option>').val(ch).text(ch)); });
          }
        });
        return $w.append($p).append($h);
      }
      var tipo = c.type === 'number' ? 'number' : (c.type === 'date' ? 'date' : (c.type === 'email' ? 'email' : 'text'));
      return $('<input class="form-control">').attr('type', tipo).attr('name', c.k).attr('placeholder', c.placeholder || '');
    }

    function renderPreview(nombre, campos, btnTexto) {
      previewCampos = {};
      $('#cnsPreviewTitulo').text('Vista previa — ' + nombre);
      var $b = $('#cnsPreviewBody').empty().off('change input');
      campos.forEach(function (c) {
        previewCampos[c.k] = c;
        var $grp = $('<div class="mb-3">').attr('data-k', c.k);
        $grp.append($('<label class="form-label">').html(esc(c.label || c.k) + (c.req ? ' <span class="req-mark">*</span>' : '')));
        $grp.append(construirControl(c));
        if (c.help) { $grp.append($('<div class="form-text">').text(c.help)); }
        $grp.append('<div class="field-error" style="display:none"></div>');
        if (c.cond) { $grp.attr('data-cond-k', c.cond.k).attr('data-cond-v', c.cond.v); }
        $b.append($grp);
      });
      $b.on('change input', function () { aplicarCondicionales($b); });
      aplicarCondicionales($b);
      previewModal.show();
    }

    function aplicarCondicionales($b) {
      $b.find('[data-cond-k]').each(function () {
        var $g = $(this);
        var $ref = $b.find('[data-k="' + $g.attr('data-cond-k') + '"]').find('input,select,textarea').first();
        var cur = $ref.length ? ($ref.val() || '') : '';
        $g.toggle(String(cur) === String($g.attr('data-cond-v')));
      });
    }

    function validarValores() {
      var $b = $('#cnsPreviewBody');
      var ok = true;
      $b.find('[data-k]').each(function () {
        var $g = $(this);
        if (!$g.is(':visible')) { return; }
        var c = previewCampos[$g.attr('data-k')];
        var $e = $g.find('.field-error').hide().text('');
        var err = '';
        if (c.type === 'nested') {
          var p = $g.find('[data-nivel="1"]').val(), h = $g.find('[data-nivel="2"]').val();
          if (c.req && (!p || !h)) { err = 'Selecciona ambos niveles.'; }
        } else {
          var val = ($g.find('input,select,textarea').val() || '').trim();
          if (c.req && !val) { err = 'Campo obligatorio.'; }
          else if (val) {
            if (c.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val)) { err = 'Correo inválido.'; }
            if (!err && c.regex) { try { if (!(new RegExp(c.regex)).test(val)) { err = 'Formato inválido.'; } } catch (e) { /* noop */ } }
            if (!err && (c.type === 'text' || c.type === 'textarea')) {
              if (c.min != null && val.length < c.min) { err = 'Mínimo ' + c.min + ' caracteres.'; }
              else if (c.max != null && val.length > c.max) { err = 'Máximo ' + c.max + ' caracteres.'; }
            }
            if (!err && c.type === 'number') {
              var n = parseFloat(val);
              if (isNaN(n)) { err = 'Número inválido.'; }
              else if (c.min != null && n < c.min) { err = 'Mínimo ' + c.min + '.'; }
              else if (c.max != null && n > c.max) { err = 'Máximo ' + c.max + '.'; }
            }
          }
        }
        if (err) { ok = false; $e.text(err).show(); }
      });
      toast(ok ? 'Validación OK.' : 'Hay errores en el formulario.', !ok);
    }

    // ---------- Eventos ----------
    $('#cnsNuevo').on('click', nuevoForm);
    $('#cnsVolver').on('click', volverListado);
    $('#cnsGuardar').on('click', function () { guardar(false); });
    $('#cnsPreview').on('click', previewEditor);
    $('#cnsPreviewValidar').on('click', validarValores);
    $('#cnsReasignar').on('click', function () { conflictoModal.hide(); guardar(true); });
    $('#cnsConfirmarEliminar').on('click', function () {
      if (!aEliminar) { return; }
      postJSON('delete', { id: aEliminar.id }).then(function (res) {
        eliminarModal.hide();
        if (res.response === 'true') { toast('Formulario eliminado.'); refrescarLista(); }
        else { toast(res.message || 'No se pudo eliminar.', true); }
      });
    });
    $('#cnsSearch').on('input', renderList);
    $('#cnsFiltroEstado').on('change', renderList);
    $('#cnsEstado').on('change', actualizarEstadoLbl);

    // ---------- Init ----------
    initLienzo();
    renderPaleta();
    renderList();
  });
})();
