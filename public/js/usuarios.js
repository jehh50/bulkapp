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
  var createView = document.getElementById('createView');
  var editView = document.getElementById('editView');
  var tbody = document.getElementById('usRows');
  var rows = tbody ? Array.prototype.slice.call(tbody.querySelectorAll('tr')) : [];
  var search = document.getElementById('usSearch');
  var btnAdd = document.getElementById('btnAdd');
  var createF = document.getElementById('createForm');
  var editF = document.getElementById('editForm');
  var createError = document.getElementById('createError');
  var editError = document.getElementById('editError');
  var toast = document.getElementById('usToast');
  var toastMsg = document.getElementById('usToastMsg');
  var toastClose = document.getElementById('usToastClose');
  var usEmpty = document.getElementById('usEmpty');
  var usCount = document.getElementById('usCount');
  var editTitle = document.getElementById('editTitle');
  var pagerNav = document.getElementById('usPagerNav');
  var pager = document.getElementById('usPager');

  var editUser = document.getElementById('e_user');
  var editId = document.getElementById('userid');

  var pageSize = 10;
  var page = 1;
  var filtered = [];

  var patronUser = /^[A-Za-z0-9._-]+$/;
  var patronPass = /^.{8,}$/;

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

  function setHero(title, crumb, sub) {
    if (heroTitle) { heroTitle.textContent = title; }
    if (heroCrumb) { heroCrumb.textContent = crumb; }
    if (heroSub) { heroSub.textContent = sub; }
  }

  var toastTimer = null;
  function showMsg(type, text) {
    toast.classList.toggle('us-toast--success', type === 'success');
    toast.classList.toggle('us-toast--error', type !== 'success');
    toastMsg.textContent = text;
    toast.style.display = 'flex';
    if (toastTimer) { clearTimeout(toastTimer); }
    toastTimer = setTimeout(function () { toast.style.display = 'none'; }, 8000);
  }

  function hideError(box) { box.style.display = 'none'; }

  function showError(box, msg) {
    box.textContent = msg;
    box.style.display = 'block';
  }

  function computeFiltered() {
    var q = search ? search.value.trim().toLowerCase() : '';
    // No depende del display actual: las filas fuera de la página activa
    // están ocultas y visibleRows() las perdería
    filtered = rows.filter(function (r) { return rowMatches(r, q); });
  }

  function refresh() {
    computeFiltered();
    var pages = Math.max(1, Math.ceil(filtered.length / pageSize));
    if (page > pages) { page = pages; }
    var start = (page - 1) * pageSize;

    rows.forEach(function (r) {
      var idx = filtered.indexOf(r);
      var inWindow = idx >= start && idx < start + pageSize;
      r.style.display = inWindow ? '' : 'none';
    });

    if (usCount) {
      var s = filtered.length ? start + 1 : 0;
      var e = Math.min(start + pageSize, filtered.length);
      usCount.textContent = 'Mostrando ' + s + '–' + e + ' de ' + filtered.length + ' usuarios';
    }

    if (pagerNav && pager) {
      if (pages <= 1) {
        pagerNav.style.display = 'none';
      } else {
        pagerNav.style.display = '';
        renderPager(pages);
      }
    }

    if (usEmpty) {
      usEmpty.style.display = filtered.length ? 'none' : 'block';
    }
  }

  function renderPager(pages) {
    var html = '<li class="page-item ' + (page === 1 ? 'disabled' : '') + '">' +
      '<a class="page-link" href="#" data-pg="' + (page - 1) + '" aria-label="Anterior">&laquo;</a></li>';
    for (var p = 1; p <= pages; p++) {
      html += '<li class="page-item ' + (p === page ? 'active' : '') + '">' +
        '<a class="page-link" href="#" data-pg="' + p + '">' + p + '</a></li>';
    }
    html += '<li class="page-item ' + (page === pages ? 'disabled' : '') + '">' +
      '<a class="page-link" href="#" data-pg="' + (page + 1) + '" aria-label="Siguiente">&raquo;</a></li>';
    pager.innerHTML = html;
  }

  if (pager) {
    pager.addEventListener('click', function (e) {
      var a = e.target.closest('a[data-pg]');
      if (!a) { return; }
      e.preventDefault();
      var pg = parseInt(a.getAttribute('data-pg'), 10);
      if (pg >= 1 && pg !== page) {
        page = pg;
        refresh();
      }
    });
  }

  function rowMatches(row, q) {
    var hay = [data(row, 'user'), data(row, 'nombre'), data(row, 'apellido'), data(row, 'rol'), data(row, 'servicio'), data(row, 'status')].join(' ').toLowerCase();
    return !q || hay.indexOf(q) !== -1;
  }

  function applyFilter() {
    var q = search ? search.value.trim().toLowerCase() : '';
    page = 1;
    filtered = rows.filter(function (r) { return rowMatches(r, q); });
    refresh();
  }

  function backToList() {
    editView.style.display = 'none';
    createView.style.display = 'none';
    listView.style.display = '';
    createF.reset();
    editF.reset();
    hideError(createError);
    hideError(editError);
    setHero(HERO.title, HERO.crumb, HERO.sub);
    applyFilter();
  }

  // ---------- Alta ----------
  function showCreate() {
    listView.style.display = 'none';
    editView.style.display = 'none';
    hideError(editError);
    createView.style.display = '';
    createF.reset();
    hideError(createError);
    setHero('Nuevo usuario', HERO.crumb + ' / Nuevo', 'Registra un operador o supervisor y asígnale un servicio.');
  }

  createF.addEventListener('submit', function (e) {
    e.preventDefault();
    hideError(createError);

    var nombre = document.getElementById('c_nombre').value.trim();
    var apellido = document.getElementById('c_apellido').value.trim();
    var user = document.getElementById('c_user').value.trim();
    var pass = document.getElementById('c_pass').value;
    var tipo = createF.querySelector('[name="tipo_usuario"]').value;
    var cliente = document.getElementById('c_servicio').value;

    if (!nombre || !apellido || !user || !tipo || !cliente) {
      return showError(createError, 'Completa todos los campos.');
    }
    if (!patronUser.test(user)) {
      return showError(createError, 'El usuario solo admite letras, números, punto, guion y guion bajo.');
    }
    if (!patronPass.test(pass)) {
      return showError(createError, 'La contraseña debe tener al menos 8 caracteres.');
    }

    post('?view=usuarios&mode=registro', { cliente: cliente, nombre: nombre, apellido: apellido, user: user, pass: pass, tipo: tipo })
      .then(function (datos) {
        if (datos.response === 'true') {
          sessionStorage.setItem('usToast', 'Usuario creado correctamente.');
          location.reload();
        } else {
          showError(createError, datos.message || 'El usuario ya existe.');
        }
      })
      .catch(function () { showError(createError, 'No se pudo registrar el usuario.'); });
  });

  // ---------- Edición ----------
  function fillEdit(row) {
    editId.value = data(row, 'id');
    editUser.value = data(row, 'user');
    document.getElementById('nombre').value = data(row, 'nombre');
    document.getElementById('apellido').value = data(row, 'apellido');
    document.getElementById('rol').value = data(row, 'rolid');
    document.getElementById('status').value = data(row, 'statusid');
    document.getElementById('servicio').value = data(row, 'servicioid');
    editTitle.textContent = 'Edición de usuario · ' + data(row, 'user');
  }

  function showEdit(row) {
    listView.style.display = 'none';
    createView.style.display = 'none';
    hideError(createError);
    editView.style.display = '';
    editF.reset();
    fillEdit(row);
    hideError(editError);
    setHero('Edición de usuario · ' + data(row, 'user'), HERO.crumb + ' / Edición', 'Actualiza datos, rol, servicio y estatus del usuario.');
  }

  function rowRefresh(row, vals) {
    var rolName = vals.rolid === '3' ? 'ADMINISTRADOR' : (vals.rolid === '2' ? 'SUPERVISOR' : 'OPERADOR');
    var statusName = vals.statusid === '2' ? 'ACTIVO' : 'INACTIVO';
    var servicioSel = document.getElementById('servicio');
    var servicioName = servicioSel.options[servicioSel.selectedIndex].textContent;

    row.setAttribute('data-nombre', vals.nombre);
    row.setAttribute('data-apellido', vals.apellido);
    row.setAttribute('data-rolid', vals.rolid);
    row.setAttribute('data-rol', rolName);
    row.setAttribute('data-statusid', vals.statusid);
    row.setAttribute('data-status', statusName);
    row.setAttribute('data-servicioid', vals.servicioid);
    row.setAttribute('data-servicio', servicioName);

    row.children[2].textContent = vals.nombre;
    row.children[3].textContent = vals.apellido;
    row.children[4].textContent = rolName;
    row.children[5].querySelector('.us-svc').textContent = servicioName;
    var pill = row.querySelector('.us-pill');
    pill.classList.toggle('us-pill--on', vals.statusid === '2');
    pill.classList.toggle('us-pill--off', vals.statusid !== '2');
    pill.textContent = statusName;
  }

  editF.addEventListener('submit', function (e) {
    e.preventDefault();
    hideError(editError);

    var vals = {
      userid: editId.value,
      nombre: document.getElementById('nombre').value.trim(),
      apellido: document.getElementById('apellido').value.trim(),
      rol: document.getElementById('rol').value,
      status: document.getElementById('status').value,
      servicio: document.getElementById('servicio').value
    };

    if (!vals.nombre || !vals.apellido) {
      return showError(editError, 'Completa todos los campos.');
    }

    post('?view=usuarios&mode=update', vals)
      .then(function (datos) {
        if (datos.response === 'true') {
          var tr = rows.filter(function (r) { return data(r, 'id') === String(vals.userid); })[0];
          if (tr) {
            rowRefresh(tr, vals);
          }
          showMsg('success', 'Usuario actualizado correctamente.');
          backToList();
        } else {
          showError(editError, datos.message || 'No se pudo actualizar el usuario.');
        }
      })
      .catch(function () { showError(editError, 'No se pudo actualizar el usuario.'); });
  });

  // Reiniciar contraseña
  document.getElementById('btn-resetpass').addEventListener('click', function () {
    if (!window.confirm('¿Reiniciar la contraseña de ' + editUser.value + '? Se generará una contraseña temporal que el usuario debe cambiar al ingresar.')) {
      return;
    }
    post('?view=usuarios&mode=password', { userid: editId.value })
      .then(function (datos) {
        if (datos.response === 'true') {
          showMsg('success', 'Contraseña temporal: ' + datos.password + '. El usuario debe cambiarla al ingresar.');
        } else {
          showMsg('error', 'No se pudo reiniciar la contraseña.');
        }
      })
      .catch(function () { showMsg('error', 'No se pudo reiniciar la contraseña.'); });
  });

  // ---------- Eventos de la lista ----------
  if (tbody) {
    tbody.addEventListener('click', function (e) {
      var tr = e.target.closest('tr');
      if (!tr) { return; }

      var btnEdit = e.target.closest('.us-edit');
      if (btnEdit) {
        showEdit(tr);
        return;
      }

      var btnDel = e.target.closest('.us-del');
      if (btnDel) {
        var userId = data(tr, 'id');
        var userName = data(tr, 'user');
        if (!window.confirm('¿Eliminar el usuario ' + userName + '? El usuario quedará marcado como INACTIVO y conservará su historial.')) {
          return;
        }
        post('?view=usuarios&mode=eliminar', { id: userId })
          .then(function (datos) {
            if (datos.response === 'true') {
              tr.setAttribute('data-statusid', '1');
              var pill = tr.querySelector('.us-pill');
              pill.classList.remove('us-pill--on');
              pill.classList.add('us-pill--off');
              pill.textContent = 'INACTIVO';
              showMsg('success', 'Usuario ' + userName + ' marcado como inactivo.');
            } else {
              showMsg('error', datos.message || 'No se pudo eliminar el usuario.');
            }
          })
          .catch(function () { showMsg('error', 'No se pudo eliminar el usuario.'); });
      }
    });
  }

  if (btnAdd) { btnAdd.addEventListener('click', showCreate); }
  if (search) { search.addEventListener('input', applyFilter); }
  Array.prototype.forEach.call(document.querySelectorAll('.us-back'), function (b) {
    b.addEventListener('click', backToList);
  });

  if (toastClose) {
    toastClose.addEventListener('click', function () {
      toast.style.display = 'none';
    });
  }

  function post(url, params) {
    var body = new URLSearchParams(params);
    return fetch(url, { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  // Mensaje pendiente tras el reload del alta
  var pending = sessionStorage.getItem('usToast');
  if (pending) {
    sessionStorage.removeItem('usToast');
    showMsg('success', pending);
  }

  refresh();
})();
