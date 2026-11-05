<?php
/**
 * Clientes (empresas) y campañas — vista del Administrador.
 *
 * Listado de clientes → ficha del cliente (datos + campañas) → modales de campaña y de
 * usuarios asignados. Lo opera public/js/empresas.js sobre EMPRESAS_BOOT.
 * Variables: $empresas.
 */
?>
<link rel="stylesheet" href="<?php echo asset('public/css/empresas.css'); ?>">

<!-- ===================== LISTADO DE CLIENTES ===================== -->
<section id="empListView">
  <div class="filter-card">
    <div class="row g-3 align-items-end">
      <div class="col-lg-5">
        <label class="filter-label" for="empSearch">Buscar</label>
        <input type="text" class="form-control" id="empSearch" placeholder="Nombre o RIF del cliente…">
      </div>
      <div class="col-lg-3">
        <label class="filter-label" for="empFiltroEstado">Estado</label>
        <select class="form-select" id="empFiltroEstado">
          <option value="">Todos</option>
          <option value="<?php echo Constantes::REGISTRO_ACTIVO; ?>">Activo</option>
          <option value="<?php echo Constantes::REGISTRO_INACTIVO; ?>">Inactivo</option>
        </select>
      </div>
      <div class="col-lg-4 text-lg-end">
        <button type="button" class="btn btn-primary search-btn" id="empNuevo">
          <i class="fi fi-rr-plus"></i> Nuevo cliente
        </button>
      </div>
    </div>
  </div>

  <div class="data-card">
    <div class="table-responsive">
      <table class="data-table table align-middle">
        <thead>
          <tr>
            <th class="ta-left">Cliente</th>
            <th>Campañas activas</th>
            <th>Usuarios asignados</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="empRows"></tbody>
      </table>
    </div>
    <div id="empEmpty" class="data-empty" style="display:none">No hay clientes para mostrar.</div>
  </div>
</section>

<!-- ===================== FICHA DEL CLIENTE ===================== -->
<section id="empDetailView" style="display:none">
  <input type="hidden" id="empId">

  <div class="filter-card">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <button type="button" class="btn btn-sm btn-outline-primary emp-back" id="empVolver"><i class="fi fi-rr-angle-small-left"></i> Clientes</button>
      <span class="emp-chip" id="empResumen"></span>
    </div>
    <div class="row g-3 align-items-end">
      <div class="col-lg-5">
        <label class="filter-label" for="empNombre">Nombre del cliente</label>
        <input type="text" class="form-control" id="empNombre" maxlength="100" placeholder="Ej: Banco Activo">
      </div>
      <div class="col-lg-3">
        <label class="filter-label" for="empRif">RIF <span class="text-muted fw-normal text-lowercase">(opcional)</span></label>
        <input type="text" class="form-control" id="empRif" maxlength="20" placeholder="J-12345678-9">
      </div>
      <div class="col-lg-2">
        <label class="filter-label d-block">Estado</label>
        <div class="form-check form-switch emp-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="empEstado" checked>
          <label class="form-check-label" for="empEstado" id="empEstadoLbl">Activo</label>
        </div>
      </div>
      <div class="col-lg-2 text-lg-end">
        <button type="button" class="btn btn-primary search-btn w-100" id="empGuardar">Guardar</button>
      </div>
    </div>
  </div>

  <div class="data-card" id="empCampanasCard">
    <div class="emp-card-head">
      <div>
        <div class="emp-card-title">Campañas</div>
        <div class="emp-card-sub">Cada campaña tiene su propio flujo, productos y usuarios asignados.</div>
      </div>
      <button type="button" class="btn btn-gold search-btn" id="empNuevaCampana"><i class="fi fi-rr-plus"></i> Nueva campaña</button>
    </div>
    <div class="table-responsive">
      <table class="data-table table align-middle">
        <thead>
          <tr>
            <th class="ta-left">Campaña</th>
            <th>Flujo</th>
            <th>Inicio</th>
            <th>Meta</th>
            <th>Usuarios</th>
            <th>Productos</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="empCampRows"></tbody>
      </table>
    </div>
    <div id="empCampEmpty" class="data-empty" style="display:none">Este cliente aún no tiene campañas.</div>
  </div>
  <div class="data-card data-empty" id="empCampanasHint" style="display:none">
    Guarda el cliente para poder registrar sus campañas.
  </div>
</section>

<!-- ===================== MODAL: CAMPAÑA ===================== -->
<div class="modal fade" id="empModalCampana" tabindex="-1" aria-labelledby="empModalCampanaTitulo" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="empModalCampanaTitulo">Nueva campaña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="campId">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-semibold" for="campNombre">Nombre</label>
            <input type="text" class="form-control" id="campNombre" maxlength="50" placeholder="Ej: Venta de planes odontológicos">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" for="campFecha">Fecha de inicio</label>
            <input type="date" class="form-control" id="campFecha">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" for="campDetalle">Descripción</label>
            <textarea class="form-control" id="campDetalle" rows="3" maxlength="500" placeholder="Objetivo, público, condiciones…"></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" for="campMeta">Meta <span class="text-muted fw-normal">(opcional)</span></label>
            <input type="number" class="form-control" id="campMeta" min="1" step="1" placeholder="Ej: 500">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" for="campFlujo">Flujo</label>
            <select class="form-select" id="campFlujo">
              <option value="<?php echo Constantes::FLUJO_DINAMICO; ?>">Formulario dinámico</option>
              <option value="<?php echo Constantes::FLUJO_BANCARIO; ?>">Formulario bancario</option>
            </select>
            <div class="form-text" id="campFlujoAyuda">Se fija al crear la campaña.</div>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" for="campCodigo">Código <span class="text-muted fw-normal" id="campCodigoOpc">(opcional)</span></label>
            <input type="text" class="form-control text-uppercase" id="campCodigo" maxlength="10" placeholder="Ej: 17">
            <div class="form-text">Company code del XML en campañas bancarias.</div>
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="campEstado" checked>
              <label class="form-check-label" for="campEstado" id="campEstadoLbl">Activa</label>
            </div>
          </div>
        </div>
        <div class="alert alert-danger mt-3 mb-0 py-2" id="campError" style="display:none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="campGuardar">Guardar campaña</button>
      </div>
    </div>
  </div>
</div>

<!-- ===================== MODAL: USUARIOS DE LA CAMPAÑA ===================== -->
<div class="modal fade" id="empModalUsuarios" tabindex="-1" aria-labelledby="empModalUsuariosTitulo" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="empModalUsuariosTitulo">Usuarios asignados</h5>
          <div class="small text-muted" id="usrCampana"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 align-items-center mb-3">
          <div class="col-md-7">
            <input type="text" class="form-control" id="usrBuscar" placeholder="Buscar por nombre, usuario o rol…">
          </div>
          <div class="col-md-5 d-flex justify-content-md-end align-items-center gap-3">
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" id="usrSoloAsignados">
              <label class="form-check-label small" for="usrSoloAsignados">Solo asignados</label>
            </div>
            <span class="emp-chip" id="usrConteo">0 asignados</span>
          </div>
        </div>
        <div class="emp-userlist" id="usrLista"></div>
        <div class="data-empty" id="usrVacio" style="display:none">Sin resultados.</div>
        <div class="form-text mt-2">
          La campaña <strong>principal</strong> es con la que el usuario inicia sesión. Si lo quitas de su
          principal, pasa a otra de sus campañas; si no tiene otra, se mantiene asignado.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="usrGuardar">Guardar asignación</button>
      </div>
    </div>
  </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1090">
  <div id="empToast" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex"><div class="toast-body" id="empToastMsg">Guardado.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
  </div>
</div>

<script>
window.EMPRESAS_BOOT = {
  empresas: <?php echo json_encode($empresas, JSON_UNESCAPED_UNICODE); ?>,
  estados: { activo: <?php echo Constantes::REGISTRO_ACTIVO; ?>, inactivo: <?php echo Constantes::REGISTRO_INACTIVO; ?> },
  flujos: { bancario: <?php echo json_encode(Constantes::FLUJO_BANCARIO); ?>, dinamico: <?php echo json_encode(Constantes::FLUJO_DINAMICO); ?> },
  hoy: <?php echo json_encode(date('Y-m-d')); ?>
};
</script>
<script src="<?php echo asset('public/js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?php echo asset('public/js/empresas.js'); ?>"></script>
