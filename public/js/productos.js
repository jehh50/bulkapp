(function () {
  'use strict';

  var heroTitle = document.querySelector('.hero__title');
  var heroCrumb = document.querySelector('.hero__crumb');
  var heroSub = document.querySelector('.hero__sub');
  var HERO = {
    title: heroTitle ? heroTitle.textContent : '',
    crumb: heroCrumb ? heroCrumb.textContent : '',
    sub: heroSub ? heroSub.textContent : ''
  };

  var listView = document.getElementById('listView');
  var formView = document.getElementById('formView');
  var createF = document.getElementById('createForm');
  var editF = document.getElementById('editForm');
  var createRowsEl = document.getElementById('createRows');
  var editRowsEl = document.getElementById('editRows');
  var createError = document.getElementById('createError');
  var editError = document.getElementById('editError');
  var search = document.getElementById('prodSearch');
  var checkAll = document.getElementById('pgCheckAll');
  var btnEditSel = document.getElementById('btnEditSel');
  var btnAdd = document.getElementById('btnAdd');
  var btnAddRow = document.getElementById('btnAddRow');
  var tbody = document.getElementById('prodRows');
  var pgEmpty = document.getElementById('pgEmpty');
  var pgCount = document.getElementById('pgCount');
  var toast = document.getElementById('pgToast');
  var toastClose = document.getElementById('pgToastClose');

  var rows = tbody ? Array.prototype.slice.call(tbody.querySelectorAll('tr')) : [];

  var DRAFT =
    '<div class="pg-draft">' +
      '<div class="pg-draft__head"><span class="pg-draft__title pg-draft__n">Producto 1</span><button type="button" class="pg-draft__rm">− Quitar</button></div>' +
      '<div class="pg-draft__grid">' +
        '<label class="pg-lbl pg-name">Nombre<input class="form-control pg-input" name="producto[]" placeholder="PRODUCTO 1" oninput="mayus(this)" required></label>' +
        '<label class="pg-lbl">Código<input class="form-control pg-input" name="codigo[]" placeholder="025" oninput="mayus(this)" required></label>' +
        '<label class="pg-lbl">Precio<input class="form-control pg-input" name="costo[]" placeholder="12345.67" inputmode="decimal" required></label>' +
        '<label class="pg-lbl">Plan<input class="form-control pg-input" name="plan[]" placeholder="54A" oninput="mayus(this)" required></label>' +
      '</div>' +
    '</div>';

  var patronPrecio = /^\d+(\.\d{1,2})?$/;

  function esc(s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function data(row, key) {
    return row.getAttribute('data-' + key) || '';
  }

  function visibleRows() {
    return rows.filter(function (r) { return r.style.display !== 'none'; });
  }

  function checkedRows() {
    return rows.filter(function (r) { return r.querySelector('.pg-check').checked; });
  }

  function setHero(title, crumb, sub) {
    if (heroTitle) { heroTitle.textContent = title; }
    else if (document.querySelector('h1')) { document.querySelector('h1').textContent = title; }
    if (heroCrumb) { heroCrumb.textContent = crumb; }
    if (heroSub) { heroSub.textContent = sub; }
  }

  function showError(box, msg) {
    box.textContent = msg;
    box.style.display = 'block';
  }

  function hideError(box) {
    box.style.display = 'none';
  }

  function renumber() {
    Array.prototype.forEach.call(createRowsEl.children, function (el, i) {
      var t = el.querySelector('.pg-draft__n');
      if (t) { t.textContent = 'Producto ' + (i + 1); }
      var rm = el.querySelector('.pg-draft__rm');
      if (rm) { rm.style.display = createRowsEl.children.length > 1 ? '' : 'none'; }
    });
  }

  function refresh() {
    var checked = checkedRows();
    rows.forEach(function (r) {
      r.classList.toggle('is-selected', r.querySelector('.pg-check').checked);
    });
    if (btnEditSel) {
      var n = checked.length;
      btnEditSel.textContent = n ? 'Editar seleccionados (' + n + ')' : 'Editar seleccionados';
      btnEditSel.disabled = !n;
      btnEditSel.classList.toggle('is-active', n > 0);
    }
    if (checkAll) {
      var vis = visibleRows();
      checkAll.checked = vis.length > 0 && vis.every(function (r) { return r.querySelector('.pg-check').checked; });
    }
    if (pgCount) {
      pgCount.textContent = 'Mostrando ' + visibleRows().length + ' de ' + rows.length + ' productos activos';
    }
    if (pgEmpty) {
      pgEmpty.style.display = visibleRows().length ? 'none' : 'block';
    }
  }

  function applyFilter() {
    var q = search ? search.value.trim().toLowerCase() : '';
    rows.forEach(function (r) {
      var hay = [data(r, 'nombre'), data(r, 'codigo'), data(r, 'plan'), data(r, 'servicio'), data(r, 'fecha')].join(' ').toLowerCase();
      r.style.display = (!q || hay.indexOf(q) !== -1) ? '' : 'none';
    });
    refresh();
  }

  function buildEditRows(rws) {
    editRowsEl.innerHTML = rws.map(function (r) {
      return '<div class="pg-draft">' +
        '<div class="pg-draft__head"><span class="pg-draft__title">#' + esc(data(r, 'id')) + ' · ' + esc(data(r, 'servicio')) + '</span></div>' +
        '<div class="pg-draft__grid pg-draft__grid--edit">' +
          '<input type="hidden" name="id[]" value="' + esc(data(r, 'id')) + '">' +
          '<label class="pg-lbl">Servicio<input class="form-control pg-input" value="' + esc(data(r, 'servicio')) + '" readonly></label>' +
          '<label class="pg-lbl">Nombre<input class="form-control pg-input" name="nombre[]" value="' + esc(data(r, 'nombre')) + '" oninput="mayus(this)" required></label>' +
          '<label class="pg-lbl">Código<input class="form-control pg-input" name="codigo[]" value="' + esc(data(r, 'codigo')) + '" oninput="mayus(this)" required></label>' +
          '<label class="pg-lbl">Precio<input class="form-control pg-input" name="costo[]" value="' + esc(data(r, 'costo')) + '" inputmode="decimal" required></label>' +
          '<label class="pg-lbl">Plan<input class="form-control pg-input" name="plan[]" value="' + esc(data(r, 'plan')) + '" oninput="mayus(this)" required></label>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  function showCreate() {
    formView.style.display = 'flex';
    listView.style.display = 'none';
    editF.style.display = 'none';
    editRowsEl.innerHTML = '';
    hideError(editError);
    createF.style.display = '';
    createF.reset();
    createRowsEl.innerHTML = DRAFT;
    renumber();
    hideError(createError);
    setHero('Nuevo producto', HERO.crumb + ' / Nuevo', 'Agrega uno o varios productos al servicio elegido.');
  }

  function showEdit(rws) {
    if (!rws.length) { return; }
    formView.style.display = 'flex';
    listView.style.display = 'none';
    createF.style.display = 'none';
    createRowsEl.innerHTML = '';
    hideError(createError);
    editF.style.display = '';
    buildEditRows(rws);
    hideError(editError);
    var n = rws.length;
    setHero(
      n > 1 ? 'Edición de ' + n + ' productos' : 'Edición de producto',
      HERO.crumb + ' / Edición',
      'Modifica nombre, código, precio y plan. El servicio no se puede cambiar.'
    );
  }

  function backToList() {
    formView.style.display = 'none';
    listView.style.display = '';
    createF.reset();
    editF.reset();
    createRowsEl.innerHTML = '';
    editRowsEl.innerHTML = '';
    setHero(HERO.title, HERO.crumb, HERO.sub);
    applyFilter();
  }

  // Validar formato de precio
  function validarPrecios(form, errBox) {
    var precios = form.querySelectorAll('input[name="costo[]"]');
    var malos = Array.prototype.filter.call(precios, function (inp) {
      return !patronPrecio.test(inp.value.trim());
    });
    if (malos.length) {
      malos.forEach(function (inp) { inp.classList.add('pg-radio-error'); });
      showError(errBox, 'El precio debe ser un número con el formato 12345.67 (máximo 2 decimales).');
      malos[0].focus();
      return false;
    }
    return true;
  }

  // ---------- Eventos ----------

  if (search) {
    search.addEventListener('input', applyFilter);
  }

  if (checkAll) {
    checkAll.addEventListener('change', function () {
      var estado = this.checked;
      visibleRows().forEach(function (r) {
        r.querySelector('.pg-check').checked = estado;
      });
      refresh();
    });
  }

  if (tbody) {
    // Marcar/planear fila seleccionada
    tbody.addEventListener('change', function (e) {
      if (e.target.classList.contains('pg-check')) {
        refresh();
      }
    });
    // Botón de edición por fila
    tbody.addEventListener('click', function (e) {
      var btn = e.target.closest('.pg-edit');
      if (!btn) { return; }
      var tr = btn.closest('tr');
      if (tr) { showEdit([tr]); }
    });
  }

  if (btnEditSel) {
    btnEditSel.addEventListener('click', function () {
      showEdit(checkedRows());
    });
  }

  if (btnAdd) {
    btnAdd.addEventListener('click', showCreate);
  }

  if (btnAddRow) {
    btnAddRow.addEventListener('click', function () {
      createRowsEl.insertAdjacentHTML('beforeend', DRAFT);
      renumber();
    });
  }

  // Quitar producto del alta (nunca el último)
  if (createRowsEl) {
    createRowsEl.addEventListener('click', function (e) {
      var rm = e.target.closest('.pg-draft__rm');
      if (!rm) { return; }
      if (createRowsEl.children.length <= 1) { return; }
      rm.closest('.pg-draft').remove();
      renumber();
    });
  }

  // Quitar el borde rojo cuando el usuario corrige el campo
  [createF, editF].forEach(function (form) {
    if (!form) { return; }
    form.addEventListener('input', function (e) {
      if (e.target.classList && e.target.classList.contains('pg-radio-error')) {
        e.target.classList.remove('pg-radio-error');
      }
    });
  });

  if (createF) {
    createF.addEventListener('submit', function (e) {
      if (!validarPrecios(createF, createError)) { e.preventDefault(); }
    });
  }

  if (editF) {
    editF.addEventListener('submit', function (e) {
      if (!validarPrecios(editF, editError)) { e.preventDefault(); }
    });
  }

  Array.prototype.forEach.call(document.querySelectorAll('.pg-back'), function (b) {
    b.addEventListener('click', backToList);
  });

  if (toast && toastClose) {
    toastClose.addEventListener('click', function () {
      toast.style.display = 'none';
    });
  }

  refresh();
})();
