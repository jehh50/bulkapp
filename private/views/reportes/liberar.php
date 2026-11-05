<div class="mx-auto" style="max-width:620px">

  <!-- BÚSQUEDA -->
  <div class="card border-0 shadow rounded-4">
    <div class="card-body p-4">
      <form id="formBuscar" onsubmit="return buscarCliente(event);">
        <label class="form-label fw-semibold" for="cliente">Introduzca el número de cédula del cliente a liberar</label>
        <input type="text" class="form-control form-control-lg" id="cliente" name="cliente"
               inputmode="numeric" maxlength="11" placeholder="Ej. 16136850" autofocus style="font-size:15px">
        <button type="submit" class="btn btn-primary btn-lg w-100 mt-3" id="btnBuscar">Buscar</button>

        <div class="alert alert-danger mb-0 mt-3 d-none" id="libError" role="alert"></div>
      </form>

      <!-- RESULTADO -->
      <div id="libResultado" class="mt-4 d-none">
        <div class="border rounded-3 overflow-hidden">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2" style="background:#f1f4fa">
            <span class="small fw-semibold text-uppercase" style="letter-spacing:.5px">Cliente encontrado</span>
            <span class="badge rounded-pill" id="cEstatus" style="background:#fde0e0;color:#b42318"></span>
          </div>
          <table class="table table-sm align-middle mb-0">
            <tbody>
              <tr><td class="text-muted" style="width:45%">Nombre</td><td class="fw-semibold" id="cNombre"></td></tr>
              <tr><td class="text-muted">Cédula</td><td class="fw-semibold" id="cCedula"></td></tr>
              <tr><td class="text-muted">Teléfono habitación</td><td class="fw-semibold" id="cHab"></td></tr>
              <tr><td class="text-muted">Teléfono oficina</td><td class="fw-semibold" id="cOfi"></td></tr>
              <tr><td class="text-muted">Teléfono celular</td><td class="fw-semibold" id="cCel"></td></tr>
              <tr><td class="text-muted border-bottom-0">Servicio</td><td class="fw-semibold border-bottom-0" id="cServicio"></td></tr>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn btn-gold btn-lg w-100 mt-3" id="btnLiberar" onclick="liberarCliente();">Liberar cliente</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ÉXITO -->
<div class="modal fade" id="modalLiberado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
    <div class="modal-content rounded-4 border-0 text-center">
      <div class="modal-body p-4">
        <div class="d-inline-flex align-items-center justify-content-center fw-bold mb-3"
             style="width:56px;height:56px;border-radius:50%;background:#d3f0e6;color:#0b6b4f;font-size:28px">&check;</div>
        <div class="fs-5 fw-semibold">Número liberado correctamente</div>
        <div class="small text-muted mt-1" id="doneText"></div>
        <button type="button" class="btn btn-primary btn-lg w-100 mt-4" id="btnAceptar">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<script>
var liberadoModal = null;

function showError(msg) {
  var el = document.getElementById('libError');
  el.textContent = msg;
  el.classList.remove('d-none');
  document.getElementById('libResultado').classList.add('d-none');
}

function buscarCliente(ev) {
  ev.preventDefault();
  var cedula = document.getElementById('cliente').value.trim();
  document.getElementById('libError').classList.add('d-none');

  if (!cedula) { showError('Introduzca un número de cédula válido e intente de nuevo.'); return false; }

  var btn = document.getElementById('btnBuscar');
  btn.disabled = true; btn.textContent = 'Buscando…';

  var body = new URLSearchParams({ cliente: cedula });
  fetch('?view=reportes&mode=cliente', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      btn.disabled = false; btn.textContent = 'Buscar';
      if (!d || d.response !== 'true') { showError('La cédula no existe. Verifique e intente nuevamente.'); return; }
      if (d.status === 'Abierto')   { showError('Este cliente aún no ha sido contactado.'); return; }
      if (d.status === 'Eliminado') { showError('Este cliente fue eliminado por una venta errada. No se puede liberar.'); return; }

      document.getElementById('cNombre').textContent   = d.nombre;
      document.getElementById('cCedula').textContent   = d.cedula;
      document.getElementById('cHab').textContent      = d.hab;
      document.getElementById('cOfi').textContent      = d.ofi;
      document.getElementById('cCel').textContent      = d.cel;
      document.getElementById('cServicio').textContent = d.servicio;
      document.getElementById('cEstatus').textContent  = d.status;
      document.getElementById('btnLiberar').dataset.cedula = d.cedula;
      document.getElementById('doneText').textContent = d.nombre + ' · C.I. ' + d.cedula;

      document.getElementById('libResultado').classList.remove('d-none');
    })
    .catch(function () {
      btn.disabled = false; btn.textContent = 'Buscar';
      showError('Ocurrió un error al consultar. Intente nuevamente.');
    });

  return false;
}

function liberarCliente() {
  var cedula = document.getElementById('btnLiberar').dataset.cedula || '';
  var btn = document.getElementById('btnLiberar');
  btn.disabled = true; btn.textContent = 'Liberando…';

  var body = new URLSearchParams({ cliente: cedula });
  fetch('?view=reportes&mode=disponible', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      btn.disabled = false; btn.textContent = 'Liberar cliente';
      if (d && d.response === 'true') {
        if (!liberadoModal) { liberadoModal = new bootstrap.Modal(document.getElementById('modalLiberado')); }
        liberadoModal.show();
      } else {
        showError('Este cliente fue una venta. No se puede desbloquear.');
      }
    })
    .catch(function () {
      btn.disabled = false; btn.textContent = 'Liberar cliente';
      showError('Ocurrió un error al liberar. Intente nuevamente.');
    });
}

document.getElementById('btnAceptar').addEventListener('click', function () {
  window.location.href = '?view=reportes&mode=liberar';
});
</script>
