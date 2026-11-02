/* Formulario dinámico: render, condiciones, validación y lectura de valores a partir
 * de la definición del Constructor. Compartido por la vista previa (constructor.js)
 * y la captura del operador (captura.js). La validación es espejo de la del servidor
 * (private/helpers/captura.php). Requiere jQuery. */
(function (window, $) {
  'use strict';

  function esc(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
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

  /** Muestra u oculta (clase d-none) los campos según su condición. */
  function aplicarCondicionales($b) {
    $b.find('[data-cond-k]').each(function () {
      var $g = $(this);
      var $ref = $b.find('[data-k="' + $g.attr('data-cond-k') + '"]').first();
      var cur = ($ref.length && !$ref.hasClass('d-none')) ? ($ref.find('input,select,textarea').first().val() || '') : '';
      $g.toggleClass('d-none', String(cur) !== String($g.attr('data-cond-v')));
    });
  }

  /**
   * Renderiza los campos en el contenedor.
   * @return {Object} Mapa k -> definición del campo.
   */
  function render($b, campos) {
    var mapa = {};
    $b.empty().off('change.fd input.fd');
    (campos || []).forEach(function (c) {
      mapa[c.k] = c;
      var $grp = $('<div class="mb-3">').attr('data-k', c.k);
      $grp.append($('<label class="form-label">').html(esc(c.label || c.k) + (c.req ? ' <span class="req-mark">*</span>' : '')));
      $grp.append(construirControl(c));
      if (c.help) { $grp.append($('<div class="form-text">').text(c.help)); }
      $grp.append('<div class="field-error" style="display:none"></div>');
      if (c.cond) { $grp.attr('data-cond-k', c.cond.k).attr('data-cond-v', c.cond.v); }
      $b.append($grp);
    });
    $b.on('change.fd input.fd', function () { aplicarCondicionales($b); });
    aplicarCondicionales($b);
    return mapa;
  }

  function errorDeCampo(c, $g) {
    if (c.type === 'nested') {
      var p = $g.find('[data-nivel="1"]').val(), h = $g.find('[data-nivel="2"]').val();
      if (!p && !h) { return c.req ? 'Selecciona ambos niveles.' : ''; }
      return (!p || !h) ? 'Selecciona ambos niveles.' : '';
    }
    var val = ($g.find('input,select,textarea').val() || '').trim();
    if (!val) { return c.req ? 'Campo obligatorio.' : ''; }
    if (c.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val)) { return 'Correo inválido.'; }
    if (c.type === 'text' || c.type === 'textarea') {
      if (c.min != null && val.length < c.min) { return 'Mínimo ' + c.min + ' caracteres.'; }
      if (c.max != null && val.length > c.max) { return 'Máximo ' + c.max + ' caracteres.'; }
    }
    if (c.type === 'number') {
      var n = parseFloat(val);
      if (isNaN(n)) { return 'Número inválido.'; }
      if (c.min != null && n < c.min) { return 'Mínimo ' + c.min + '.'; }
      if (c.max != null && n > c.max) { return 'Máximo ' + c.max + '.'; }
    }
    if (c.regex && (c.type === 'text' || c.type === 'textarea' || c.type === 'number')) {
      try { if (!(new RegExp(c.regex)).test(val)) { return 'Formato inválido.'; } } catch (e) { /* regex validada al guardar */ }
    }
    return '';
  }

  /** Marca errores por campo; devuelve true si todo es válido. */
  function validar($b, mapa) {
    var ok = true;
    $b.find('[data-k]').each(function () {
      var $g = $(this);
      var $e = $g.find('.field-error').hide().text('');
      if ($g.hasClass('d-none')) { return; }
      var err = errorDeCampo(mapa[$g.attr('data-k')], $g);
      if (err) { ok = false; $e.text(err).show(); }
    });
    return ok;
  }

  /** Muestra errores devueltos por el servidor: { k: mensaje }. */
  function marcarErrores($b, errores) {
    Object.keys(errores || {}).forEach(function (k) {
      $b.find('[data-k="' + k + '"] .field-error').text(errores[k]).show();
    });
  }

  /** Valores de los campos visibles: { k: valor } (anidado: { lv1, lv2 }). */
  function leer($b, mapa) {
    var valores = {};
    $b.find('[data-k]').each(function () {
      var $g = $(this);
      if ($g.hasClass('d-none')) { return; }
      var k = $g.attr('data-k');
      if (mapa[k].type === 'nested') {
        valores[k] = { lv1: $g.find('[data-nivel="1"]').val() || '', lv2: $g.find('[data-nivel="2"]').val() || '' };
      } else {
        valores[k] = ($g.find('input,select,textarea').val() || '').trim();
      }
    });
    return valores;
  }

  window.FormDinamico = { render: render, validar: validar, marcarErrores: marcarErrores, leer: leer };
})(window, jQuery);
