<?php
/**
 * Constructor de formularios — vista del Builder (Fases 1–2).
 *
 * El listado y el editor se renderizan/operan desde public/js/constructor.js
 * sobre los datos boot embebidos abajo. Bootstrap 5 para todo; constructor.css
 * solo para lo que BS5 no cubre (paleta, lienzo, estados de arrastre).
 */
?>
<link rel="stylesheet" href="<?php echo asset('public/css/constructor.css'); ?>">

<!-- ===================== LISTADO ===================== -->
<section id="cnsListView">
  <div class="filter-card">
    <div class="row g-3 align-items-end">
      <div class="col-lg-5">
        <label class="filter-label" for="cnsSearch">Buscar</label>
        <input type="text" class="form-control" id="cnsSearch" placeholder="Nombre del formulario…">
      </div>
      <div class="col-lg-3">
        <label class="filter-label" for="cnsFiltroEstado">Estado</label>
        <select class="form-select" id="cnsFiltroEstado">
          <option value="">Todos</option>
          <option value="<?php echo Constantes::REGISTRO_ACTIVO; ?>">Activo</option>
          <option value="<?php echo Constantes::REGISTRO_INACTIVO; ?>">Inactivo</option>
        </select>
      </div>
      <div class="col-lg-4 text-lg-end">
        <button type="button" class="btn btn-primary search-btn" id="cnsNuevo">
          <i class="fi fi-rr-plus"></i> Nuevo formulario
        </button>
      </div>
    </div>
  </div>

  <div class="data-card">
    <div class="table-responsive">
      <table class="data-table table align-middle">
        <thead>
          <tr>
            <th class="ta-left">Formulario</th>
            <th>Servicios</th>
            <th>Campos</th>
            <th>Versión</th>
            <th>Estado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="cnsRows"></tbody>
      </table>
    </div>
    <div id="cnsEmpty" class="data-empty" style="display:none">No hay formularios para mostrar.</div>
  </div>
</section>

<!-- ===================== EDITOR ===================== -->
<section id="cnsEditorView" style="display:none">
  <input type="hidden" id="cnsId">

  <div class="filter-card">
    <div class="row g-3 align-items-end">
      <div class="col-lg-5">
        <label class="filter-label" for="cnsNombre">Nombre del formulario</label>
        <input type="text" class="form-control" id="cnsNombre" maxlength="150" placeholder="Ej: Captación Bancaribe">
      </div>
      <div class="col-lg-3">
        <label class="filter-label" for="cnsBtnTexto">Texto del botón</label>
        <input type="text" class="form-control" id="cnsBtnTexto" maxlength="80" value="Guardar">
      </div>
      <div class="col-lg-2">
        <label class="filter-label d-block">Estado</label>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="cnsEstado">
          <label class="form-check-label" for="cnsEstado" id="cnsEstadoLbl">Inactivo</label>
        </div>
      </div>
      <div class="col-lg-2 text-lg-end">
        <span class="text-muted" id="cnsVersion"></span>
      </div>
    </div>
  </div>

  <ul class="nav nav-tabs mb-3" id="cnsTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabCampos" type="button">Campos</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabServicios" type="button">Servicios</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBd" type="button">Base de datos</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHist" type="button">Historial</button></li>
  </ul>

  <div class="tab-content">
    <!-- Campos -->
    <div class="tab-pane fade show active" id="tabCampos">
      <div class="cns-editor__grid">
        <div class="cns-col">
          <div class="cns-col__title">Paleta</div>
          <div id="cnsPaleta"></div>
          <p class="text-muted" style="font-size:12px;margin:8px 0 0">Arrastra al lienzo o haz clic para agregar.</p>
        </div>
        <div class="cns-col">
          <div class="cns-col__title">Lienzo</div>
          <ul id="cnsLienzo" class="cns-canvas" style="padding:0;margin:0"></ul>
          <div id="cnsLienzoEmpty" class="cns-canvas__empty">Sin campos todavía. Agrega desde la paleta.</div>
        </div>
        <div class="cns-col">
          <div class="cns-col__title">Propiedades</div>
          <div id="cnsProps" class="cns-prop"><div class="cns-prop__empty">Selecciona un campo del lienzo.</div></div>
        </div>
      </div>
    </div>

    <!-- Servicios -->
    <div class="tab-pane fade" id="tabServicios">
      <div class="cns-col">
        <div class="cns-col__title">Servicios asignados</div>
        <p class="text-muted" style="font-size:13px">Regla: un solo formulario <strong>activo</strong> por servicio. Solo servicios no bancarios: los de banca conservan su formulario fijo.</p>
        <div id="cnsServicios" class="row g-2"></div>
      </div>
    </div>

    <!-- Base de datos -->
    <div class="tab-pane fade" id="tabBd">
      <div class="cns-col">
        <div class="cns-col__title">Base de datos del formulario</div>
        <div id="cnsBdAviso" class="text-muted" style="display:none">Guarda el formulario primero para poder cargar su base de datos.</div>
        <div id="cnsBdPanel">
          <div id="cnsBdVigente" class="mb-3"></div>
          <div class="row g-2 align-items-end mb-3">
            <div class="col-md-8">
              <label class="filter-label" for="cnsArchivo">Cargar nueva BD (.csv / .xlsx)</label>
              <input type="file" class="form-control" id="cnsArchivo" accept=".csv,.txt,.xlsx">
            </div>
            <div class="col-md-4">
              <button type="button" class="btn btn-outline-primary" id="cnsAnalizar">Analizar archivo</button>
            </div>
          </div>

          <div id="cnsMapeo" style="display:none">
            <h6 class="mb-1">Columnas de la card «Datos de la búsqueda»</h6>
            <p class="text-muted mb-2" style="font-size:13px">Independientes de los campos del formulario. Elige qué columnas mostrar, su etiqueta y la columna clave para buscar el registro.</p>
            <div class="table-responsive mb-3">
              <table class="table table-sm align-middle mb-0">
                <thead><tr><th style="width:70px">Mostrar</th><th>Columna del archivo</th><th>Etiqueta</th><th style="width:90px">Búsqueda</th></tr></thead>
                <tbody id="cnsMapeoCampos"></tbody>
              </table>
            </div>
            <div class="text-muted mb-1" style="font-size:12px">Muestra del archivo</div>
            <div class="table-responsive mb-2">
              <table class="table table-sm table-bordered" style="font-size:12px">
                <thead id="cnsMuestraHead"></thead>
                <tbody id="cnsMuestraBody"></tbody>
              </table>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-primary" id="cnsConfirmarCarga">Confirmar carga</button>
              <span class="text-muted" id="cnsMapeoInfo"></span>
            </div>
          </div>

          <hr>
          <h6 class="mb-2">Historial de cargas</h6>
          <div id="cnsCargas"><p class="text-muted mb-0">—</p></div>
        </div>
      </div>
    </div>

    <!-- Historial -->
    <div class="tab-pane fade" id="tabHist">
      <div class="cns-col">
        <div class="cns-col__title">Historial de versiones</div>
        <div id="cnsHistorial"><p class="text-muted mb-0">Guarda el formulario para ver su historial.</p></div>
      </div>
    </div>
  </div>

  <ul id="cnsErrores" class="cns-errors mt-3" style="display:none"></ul>

  <div class="d-flex justify-content-between mt-3 mb-5">
    <button type="button" class="btn btn-outline-primary" id="cnsVolver">Volver al listado</button>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-primary" id="cnsPreview">Vista previa</button>
      <button type="button" class="btn btn-primary" id="cnsGuardar">Guardar formulario</button>
    </div>
  </div>
</section>

<!-- ===================== MODALES ===================== -->
<div class="modal fade" id="cnsModalEliminar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Eliminar formulario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">¿Eliminar <strong id="cnsEliminarNombre"></strong>? Es un borrado lógico: se conservan datos e historial para reportería.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="cnsConfirmarEliminar">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cnsModalConflicto" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Conflicto de servicios</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Estos servicios ya tienen un formulario activo:</p>
        <ul id="cnsConflictoLista"></ul>
        <p class="mb-0">¿Reasignarlos a este formulario y guardar?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="cnsReasignar">Reasignar y guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cnsModalPreview" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="cnsPreviewTitulo">Vista previa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-5" id="cnsPreviewBdCol" style="display:none">
            <div class="card">
              <div class="card-header fw-semibold">Datos de la búsqueda</div>
              <div class="card-body" id="cnsPreviewBd"></div>
            </div>
          </div>
          <div class="col-lg-8 mx-auto" id="cnsPreviewFormCol">
            <form class="cns-preview" id="cnsPreviewBody" novalidate></form>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="cnsPreviewValidar">Validar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cnsModalExpresiones" tabindex="-1" aria-labelledby="cnsExpresionesTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cnsExpresionesTitulo">Validaciones disponibles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Elige una validación en el campo para controlar lo que el operador puede escribir. Las de formato rechazan el valor si no coincide; las de transformación cambian el texto mientras se escribe.</p>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Validación</th>
                <th scope="col">Qué hace</th>
                <th scope="col">Ejemplo válido</th>
                <th scope="col">Ejemplo no válido</th>
                <th scope="col">Aplica a</th>
              </tr>
            </thead>
            <tbody id="cnsExpresionesFilas"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cnsModalSinCampana" tabindex="-1" aria-labelledby="cnsSinCampanaTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cnsSinCampanaTitulo">Formulario sin campaña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p>No seleccionaste ninguna campaña para este formulario.</p>
        <p class="mb-0 text-muted small">Puedes guardarlo así, pero ningún operador lo verá hasta que lo asignes a una campaña en la pestaña <strong>Servicios</strong>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" id="cnsSinCampanaGuardar">Guardar sin campaña</button>
        <button type="button" class="btn btn-primary" id="cnsSinCampanaAsignar">Asignar campaña</button>
      </div>
    </div>
  </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1090">
  <div id="cnsToast" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex"><div class="toast-body" id="cnsToastMsg">Guardado.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
  </div>
</div>

<script>
window.CONSTRUCTOR_BOOT = {
  servicios: <?php echo json_encode($servicios, JSON_UNESCAPED_UNICODE); ?>,
  formularios: <?php echo json_encode($formularios, JSON_UNESCAPED_UNICODE); ?>,
  tipos: <?php echo json_encode(Constantes::tiposCampoFormulario()); ?>,
  expresiones: <?php echo json_encode($expresiones, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>,
  estados: { activo: <?php echo Constantes::REGISTRO_ACTIVO; ?>, inactivo: <?php echo Constantes::REGISTRO_INACTIVO; ?> }
};
</script>
<script src="<?php echo asset('public/js/jquery-3.7.1.min.js'); ?>"></script>
<script src="<?php echo asset('public/js/jquery-ui.min.js'); ?>"></script>
<script src="<?php echo asset('public/js/formDinamico.js'); ?>"></script>
<script src="<?php echo asset('public/js/constructor.js'); ?>"></script>
