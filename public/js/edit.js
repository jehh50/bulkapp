(function () {
  'use strict';

  var meta = document.querySelector('meta[name="csrf-token"]');
  var token = meta ? meta.getAttribute('content') : '';
  var buscarEnCurso = false;

  function escapeHtml(valor) {
    return String(valor === null || valor === undefined ? '' : valor).replace(/[&<>"']/g, function (caracter) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[caracter];
    });
  }

  function porId(id) {
    return document.getElementById(id);
  }

  function mostrarFeedback(mensaje, tipo) {
    var caja = porId('erFeedback');
    if (!caja) return;

    var icono = document.createElement('span');
    icono.className = 'fw-bold';
    icono.setAttribute('aria-hidden', 'true');
    icono.textContent = tipo === 'success' ? '✓' : '!';

    var texto = document.createElement('div');
    texto.textContent = mensaje;

    caja.replaceChildren(icono, texto);
    caja.className = 'alert alert-' + (tipo || 'danger') + ' d-flex align-items-start gap-2 mt-3 mb-0';
  }

  function limpiarFeedback() {
    var caja = porId('erFeedback');
    if (!caja) return;
    caja.className = 'mt-3 d-none';
    caja.replaceChildren();
  }

  function actualizarContador(total) {
    var contador = porId('erCount');
    if (contador) {
      contador.textContent = total + (total === 1 ? ' registro' : ' registros');
    }
  }

  function mostrarVacio(mostrar) {
    var vacio = porId('erEmpty');
    if (vacio) {
      vacio.classList.toggle('d-none', !mostrar);
    }
  }

  function cargando(activo) {
    var boton = porId('btn-buscar');
    var spinner = porId('erSpinner');
    var etiqueta = porId('erSearchLabel');

    if (boton) {
      boton.disabled = activo;
      boton.setAttribute('aria-busy', activo ? 'true' : 'false');
    }
    if (spinner) spinner.classList.toggle('d-none', !activo);
    if (etiqueta) etiqueta.textContent = activo ? 'Buscando…' : 'Buscar';
  }

  async function peticion(url, datos) {
    var cuerpo = new URLSearchParams(datos);
    if (token) cuerpo.append('csrf_token', token);

    var respuesta = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: cuerpo.toString()
    });

    if (!respuesta.ok) {
      throw new Error('La solicitud falló con estado ' + respuesta.status);
    }

    return respuesta.json();
  }

  function soloDigitos(valor, maximo) {
    return String(valor || '').replace(/\D+/g, '').slice(0, maximo);
  }

  function formatearTelefono(valor) {
    var digitos = String(valor || '').replace(/\D+/g, '');
    if (!digitos) return '';

    var salida = '(0';
    if (digitos.length > 1) salida += digitos.substring(1, 4);
    if (digitos.length >= 5) salida += ')' + digitos.substring(4, 7);
    if (digitos.length >= 8) salida += '.' + digitos.substring(7, 9);
    if (digitos.length >= 10) salida += '.' + digitos.substring(9, 11);

    return salida;
  }

  function limpiarErrorCampo(campo) {
    if (!campo) return;
    campo.classList.remove('is-invalid');
  }

  function prepararCampos(indice) {
    var nombre = porId('nombre' + indice);
    var apellido = porId('apellido' + indice);
    var cedula = porId('cedula' + indice);
    var nacimiento = porId('d_nacimiento' + indice);
    var venta = porId('saleDate' + indice);
    var cuenta = porId('account' + indice);
    var hab = porId('telf_hab' + indice);
    var cel = porId('telf_cel' + indice);

    [nombre, apellido].forEach(function (campo) {
      if (!campo) return;
      campo.addEventListener('input', function () {
        campo.value = campo.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÜÑ\s'-]/g, '');
        limpiarErrorCampo(campo);
      });
    });

    if (cedula) {
      cedula.addEventListener('input', function () {
        cedula.value = soloDigitos(cedula.value, 9);
        limpiarErrorCampo(cedula);
      });
    }

    if (nacimiento) {
      nacimiento.addEventListener('input', function () {
        nacimiento.value = nacimiento.value.replace(/[^0-9\-\/]/g, '').slice(0, 10);
        limpiarErrorCampo(nacimiento);
      });
    }

    if (venta) {
      venta.addEventListener('input', function () {
        venta.value = soloDigitos(venta.value, 8);
        limpiarErrorCampo(venta);
      });
    }

    if (cuenta) {
      cuenta.addEventListener('input', function () {
        cuenta.value = cuenta.value.replace(/[^0-9*]/g, '').slice(0, 20);
        limpiarErrorCampo(cuenta);
      });
    }

    [hab, cel].forEach(function (campo) {
      if (!campo) return;
      campo.addEventListener('input', function () {
        campo.value = formatearTelefono(campo.value);
        limpiarErrorCampo(campo);
      });
    });

    var correo = porId('correo' + indice);
    if (correo) {
      correo.addEventListener('input', function () {
        limpiarErrorCampo(correo);
      });
    }

    var genero = porId('genero' + indice);
    if (genero) {
      genero.addEventListener('change', function () {
        limpiarErrorCampo(genero);
      });
    }
  }

  function abrirModal(id) {
    var modal = porId(id);
    if (!modal) return;

    if (window.bootstrap && window.bootstrap.Modal) {
      window.bootstrap.Modal.getOrCreateInstance(modal).show();
      return;
    }

    modal.classList.add('show');
    modal.style.display = 'block';
    modal.removeAttribute('aria-hidden');
    modal.setAttribute('aria-modal', 'true');
  }

  function modalEliminar(indice, item) {
    var id = 'modalRechazo' + indice;
    return `
      <div class="modal fade" id="${id}" tabindex="-1" aria-labelledby="${id}Title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
              <h2 class="modal-title fs-5" id="${id}Title">Eliminar venta</h2>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <p class="mb-1">¿Eliminar la venta de <strong>${escapeHtml(item.nombre)} ${escapeHtml(item.apellido)}</strong>?</p>
              <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
              <form method="POST" action="?view=configuracion&mode=eliminar" class="d-inline">
                <input type="hidden" name="id" value="${escapeHtml(item.id_resultado)}">
                <input type="hidden" name="csrf_token" value="${escapeHtml(token)}">
                <button type="submit" class="btn btn-danger">Eliminar</button>
              </form>
            </div>
          </div>
        </div>
      </div>`;
  }

  function modalActualizar(indice, item) {
    var id = 'modalActualiza' + indice;
    return `
      <div class="modal fade" id="${id}" tabindex="-1" aria-labelledby="${id}Title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
              <h2 class="modal-title fs-5" id="${id}Title">Guardar cambios</h2>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <p class="mb-0">¿Confirmas la actualización de <strong>${escapeHtml(item.nombre)} ${escapeHtml(item.apellido)}</strong>?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-primary" id="confirmUpdate${indice}">Guardar cambios</button>
            </div>
          </div>
        </div>
      </div>`;
  }

  function resultadoHtml(indice, item) {
    var generoF = String(item.genero) === 'F' ? ' selected' : '';
    var generoM = String(item.genero) === 'M' ? ' selected' : '';

    return `
      <div class="accordion-item">
        <h2 class="accordion-header" id="heading${indice}">
          <button class="accordion-button ${indice === 0 ? '' : 'collapsed'}" type="button"
                  data-bs-toggle="collapse" data-bs-target="#collapse${indice}"
                  aria-expanded="${indice === 0 ? 'true' : 'false'}" aria-controls="collapse${indice}">
            <span class="er-result__head">
              <span class="er-result__name">${escapeHtml(item.nombre)} ${escapeHtml(item.apellido)}</span>
              <span class="er-chip">CI ${escapeHtml(item.cedula)}</span>
              <span class="er-chip er-chip--muted">${escapeHtml(item.servicio)}</span>
            </span>
          </button>
        </h2>
        <div id="collapse${indice}" class="accordion-collapse collapse ${indice === 0 ? 'show' : ''}"
             aria-labelledby="heading${indice}" data-bs-parent="#accordion">
          <div class="accordion-body">
            <div class="row g-3">
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="producto${indice}">Producto</label>
                  <select class="form-select" id="producto${indice}" required>
                    <option value="" selected disabled>Cargando…</option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="nombre${indice}">Nombre</label>
                  <input type="text" class="form-control" id="nombre${indice}" maxlength="50"
                         value="${escapeHtml(item.nombre)}" required>
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="apellido${indice}">Apellido</label>
                  <input type="text" class="form-control" id="apellido${indice}" maxlength="50"
                         value="${escapeHtml(item.apellido)}" required>
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="cedula${indice}">Cédula</label>
                  <input type="text" class="form-control" id="cedula${indice}" maxlength="9"
                         inputmode="numeric" value="${escapeHtml(item.cedula)}" required>
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="genero${indice}">Sexo</label>
                  <select class="form-select" id="genero${indice}" required>
                    <option value="" ${item.genero ? '' : 'selected'} disabled>Seleccione…</option>
                    <option value="F"${generoF}>Femenino</option>
                    <option value="M"${generoM}>Masculino</option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="d_nacimiento${indice}">Fecha de nacimiento</label>
                  <input type="text" class="form-control" id="d_nacimiento${indice}" maxlength="10"
                         placeholder="DD-MM-AAAA" value="${escapeHtml(item.nacimiento)}" required>
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="telf_hab${indice}">Teléfono habitación</label>
                  <input type="text" class="form-control telefono" id="telf_hab${indice}" maxlength="15"
                         placeholder="(0212)345.67.89" value="${escapeHtml(item.telf_hab)}">
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="telf_cel${indice}">Teléfono celular</label>
                  <input type="text" class="form-control telefono" id="telf_cel${indice}" maxlength="15"
                         placeholder="(0424)234.56.78" value="${escapeHtml(item.telf_celular)}">
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="correo${indice}">Correo electrónico</label>
                  <input type="email" class="form-control" id="correo${indice}" maxlength="50"
                         placeholder="usuario@dominio.com" value="${escapeHtml(item.correo)}" required>
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="saleDate${indice}">Fecha de venta</label>
                  <input type="text" class="form-control" id="saleDate${indice}" maxlength="8"
                         inputmode="numeric" placeholder="20240101" value="${escapeHtml(item.fecha_venta)}">
                </div>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="er-field">
                  <label class="form-label" for="account${indice}">Número de cuenta</label>
                  <input type="text" class="form-control" id="account${indice}" maxlength="20"
                         inputmode="numeric" placeholder="12345678901234567890" value="${escapeHtml(item.cuenta)}">
                </div>
              </div>
            </div>

            <input type="hidden" id="id_resultado${indice}" value="${escapeHtml(item.id_resultado)}">
            <input type="hidden" id="gestion${indice}" value="${escapeHtml(item.id_gestion)}">

            <div class="er-actions">
              <a href="?view=configuracion&mode=editarResultado" class="btn btn-outline-secondary">Regresar</a>
              <button type="button" class="btn btn-success" id="btnGuardar${indice}">Guardar</button>
              <button type="button" class="btn btn-outline-danger" id="btnEliminar${indice}">Eliminar</button>
            </div>
          </div>
        </div>
      </div>`;
  }

  async function cargarProductos(indice, seleccionado) {
    var select = porId('producto' + indice);
    if (!select) return;

    try {
      var datos = await peticion('?view=configuracion&mode=productos', { id: 1 });
      select.replaceChildren();

      if (datos.response === 'true' && datos.productos) {
        String(datos.productos).split('|').forEach(function (entrada) {
          if (!entrada) return;

          var separador = entrada.indexOf(',');
          var id = separador === -1 ? entrada : entrada.slice(0, separador);
          var nombre = separador === -1 ? entrada : entrada.slice(separador + 1);

          var opcion = document.createElement('option');
          opcion.value = id;
          opcion.textContent = nombre;
          if (String(id) === String(seleccionado)) opcion.selected = true;
          select.appendChild(opcion);
        });
      }

      if (!select.options.length) {
        var vacio = document.createElement('option');
        vacio.value = '';
        vacio.textContent = 'Sin productos disponibles';
        vacio.disabled = true;
        vacio.selected = true;
        select.appendChild(vacio);
      }
    } catch (error) {
      select.replaceChildren();
      var fallo = document.createElement('option');
      fallo.value = '';
      fallo.textContent = 'No se pudieron cargar los productos';
      fallo.disabled = true;
      fallo.selected = true;
      select.appendChild(fallo);
    }
  }

  function validarResultado(indice) {
    var requeridos = [
      [porId('producto' + indice), 'Seleccione un producto.'],
      [porId('nombre' + indice), 'Ingrese el nombre.'],
      [porId('apellido' + indice), 'Ingrese el apellido.'],
      [porId('cedula' + indice), 'Ingrese la cédula.'],
      [porId('genero' + indice), 'Seleccione el sexo.'],
      [porId('d_nacimiento' + indice), 'Ingrese la fecha de nacimiento.'],
      [porId('correo' + indice), 'Ingrese un correo válido.']
    ];

    for (var i = 0; i < requeridos.length; i++) {
      var campo = requeridos[i][0];
      if (!campo) continue;

      if (!campo.value.trim() || (campo.type === 'email' && !campo.checkValidity())) {
        campo.classList.add('is-invalid');
        campo.focus();
        if (typeof campo.reportValidity === 'function') campo.reportValidity();
        mostrarFeedback(requeridos[i][1], 'warning');
        return false;
      }
    }

    limpiarFeedback();
    return true;
  }

  function actualizarRegistro(indice, idResultado) {
    if (!validarResultado(indice)) return;

    var datos = {
      productoId: porId('producto' + indice) ? porId('producto' + indice).value : '',
      id: idResultado,
      nombre: porId('nombre' + indice) ? porId('nombre' + indice).value : '',
      apellido: porId('apellido' + indice) ? porId('apellido' + indice).value : '',
      cedula: porId('cedula' + indice) ? porId('cedula' + indice).value : '',
      sexo: porId('genero' + indice) ? porId('genero' + indice).value : '',
      nacimiento: porId('d_nacimiento' + indice) ? porId('d_nacimiento' + indice).value : '',
      hab: porId('telf_hab' + indice) ? porId('telf_hab' + indice).value : '',
      cel: porId('telf_cel' + indice) ? porId('telf_cel' + indice).value : '',
      correo: porId('correo' + indice) ? porId('correo' + indice).value : '',
      venta: porId('saleDate' + indice) ? porId('saleDate' + indice).value : '',
      cuenta: porId('account' + indice) ? porId('account' + indice).value : ''
    };

    var formulario = document.createElement('form');
    formulario.method = 'POST';
    formulario.action = '?view=configuracion&mode=actualiza';

    if (token) {
      var csrf = document.createElement('input');
      csrf.type = 'hidden';
      csrf.name = 'csrf_token';
      csrf.value = token;
      formulario.appendChild(csrf);
    }

    Object.keys(datos).forEach(function (clave) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = clave;
      input.value = datos[clave] === null || datos[clave] === undefined ? '' : datos[clave];
      formulario.appendChild(input);
    });

    document.body.appendChild(formulario);
    formulario.submit();
  }

  function mostrarResultado(indice, item) {
    var contenedor = porId('accordion');
    if (!contenedor) return;

    contenedor.insertAdjacentHTML('beforeend', resultadoHtml(indice, item));

    prepararCampos(indice);

    var guardar = porId('btnGuardar' + indice);
    if (guardar) {
      guardar.addEventListener('click', function () {
        if (validarResultado(indice)) {
          abrirModal('modalActualiza' + indice);
        }
      });
    }

    var eliminar = porId('btnEliminar' + indice);
    if (eliminar) {
      eliminar.addEventListener('click', function () {
        abrirModal('modalRechazo' + indice);
      });
    }

    var modales = porId('modals');
    if (modales) {
      modales.insertAdjacentHTML('beforeend', modalEliminar(indice, item));
      modales.insertAdjacentHTML('beforeend', modalActualizar(indice, item));
    }

    var confirmar = porId('confirmUpdate' + indice);
    if (confirmar) {
      confirmar.addEventListener('click', function () {
        actualizarRegistro(indice, item.id_resultado);
      });
    }

    cargarProductos(indice, item.producto_id);
  }

  async function buscar() {
    if (buscarEnCurso) return;

    var cedulaInput = porId('cedula');
    var contenedor = porId('accordion');
    var modales = porId('modals');
    if (!cedulaInput || !contenedor) return;

    var cedula = soloDigitos(cedulaInput.value, 9);
    cedulaInput.value = cedula;

    if (!cedula) {
      mostrarFeedback('Ingrese una cédula válida.', 'warning');
      cedulaInput.focus();
      return;
    }

    var mensaje = porId('message');
    if (mensaje) mensaje.remove();

    limpiarFeedback();
    mostrarVacio(false);
    actualizarContador(0);
    contenedor.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Buscando…</span></div></div>';
    if (modales) modales.replaceChildren();

    buscarEnCurso = true;
    cargando(true);

    try {
      var datos = await peticion('?view=configuracion&mode=buscar', { cedula: cedula });
      contenedor.replaceChildren();

      if (datos.response === 'true' && datos.count > 0 && Array.isArray(datos.data)) {
        datos.data.forEach(function (item, indice) {
          mostrarResultado(indice, item);
        });
        actualizarContador(datos.count);
      } else {
        actualizarContador(0);
        mostrarVacio(true);
      }
    } catch (error) {
      contenedor.replaceChildren();
      mostrarFeedback('No se pudo consultar la cédula. Intenta nuevamente.', 'danger');
    } finally {
      buscarEnCurso = false;
      cargando(false);
    }
  }

  function limpiar() {
    var formulario = porId('erSearchForm');
    if (!formulario || !porId('accordion')) {
      window.location.href = '?view=configuracion&mode=editarResultado';
      return;
    }

    formulario.reset();

    var contenedor = porId('accordion');
    var modales = porId('modals');
    if (contenedor) contenedor.replaceChildren();
    if (modales) modales.replaceChildren();

    actualizarContador(0);
    mostrarVacio(false);
    limpiarFeedback();

    var cedula = porId('cedula');
    if (cedula) cedula.focus();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var cedula = porId('cedula');
    var formulario = porId('erSearchForm');
    var buscarBtn = porId('btn-buscar');
    var limpiarBtn = porId('btn-limpiar');

    if (cedula) {
      cedula.addEventListener('input', function () {
        cedula.value = soloDigitos(cedula.value, 9);
      });
    }

    if (formulario) {
      formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        buscar();
      });
    }

    if (buscarBtn) {
      buscarBtn.addEventListener('click', function (evento) {
        evento.preventDefault();
        buscar();
      });
    }

    if (limpiarBtn) {
      limpiarBtn.addEventListener('click', limpiar);
    }
  });
})();
