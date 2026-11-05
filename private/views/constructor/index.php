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
        <div class="text-muted small" id="cnsVersion"></div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="cnsVersiones" style="display:none">
          <i class="fi fi-rr-time-past"></i> Versiones
        </button>
      </div>
    </div>
  </div>

  <!-- Wizard: 1 lienzo → 2 servicio → 3 base de datos (ahí se muestra «Finalizar»). -->
  <ol class="cns-steps mb-3" id="cnsSteps">
    <li class="cns-step" data-paso="1"><button type="button"><span class="cns-step__num">1</span><span class="cns-step__txt">Lienzo</span></button></li>
    <li class="cns-step" data-paso="2"><button type="button"><span class="cns-step__num">2</span><span class="cns-step__txt">Servicio</span></button></li>
    <li class="cns-step" data-paso="3"><button type="button"><span class="cns-step__num">3</span><span class="cns-step__txt">Base de datos</span></button></li>
  </ol>

  <div class="cns-wizard">
    <!-- Paso 1: Campos (lienzo) -->
    <div class="cns-wizard__panel" id="tabCampos" data-paso="1">
      <div class="cns-editor__grid">
        <div class="cns-col">
          <div class="cns-col__title">Paleta</div>
          <div id="cnsPaleta"></div>
          <p class="text-muted" style="font-size:12px;margin:8px 0 0">Arrastra al lienzo o haz clic para agregar.</p>
        </div>
        <div class="cns-col">
          <div class="cns-col__title">Lienzo</div>
          <div class="cns-canvas__scroll" id="cnsLienzoScroll">
            <ul id="cnsLienzo" class="cns-canvas" style="padding:0;margin:0"></ul>
            <div id="cnsLienzoEmpty" class="cns-canvas__empty">Sin campos todavía. Agrega desde la paleta.</div>
          </div>
        </div>
        <div class="cns-col">
          <div class="cns-col__title">Propiedades</div>
          <div id="cnsProps" class="cns-prop"><div class="cns-prop__empty">Selecciona un campo del lienzo.</div></div>
        </div>
      </div>
    </div>

    <!-- Paso 2: Servicio -->
    <div class="cns-wizard__panel" id="tabServicios" data-paso="2">
      <div class="cns-col">
        <div class="cns-col__title">Servicios asignados</div>
        <p class="text-muted" style="font-size:13px">Regla: un solo formulario <strong>activo</strong> por servicio. Solo servicios no bancarios: los de banca conservan su formulario fijo.</p>
        <div id="cnsServicios" class="row g-2"></div>
      </div>
    </div>

    <!-- Paso 3: Base de datos (el formulario ya se guardó al salir del paso 2) -->
    <div class="cns-wizard__panel" id="tabBd" data-paso="3">
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

  </div>

  <ul id="cnsErrores" class="cns-errors mt-3" style="display:none"></ul>

  <div class="d-flex flex-wrap justify-content-between gap-2 mt-3 mb-5">
    <button type="button" class="btn btn-outline-primary" id="cnsVolver">Volver al listado</button>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-outline-primary" id="cnsPreview">Vista previa</button>
      <button type="button" class="btn btn-outline-primary" id="cnsAnterior">
        <i class="fi fi-rr-angle-small-left"></i> Anterior
      </button>
      <button type="button" class="btn btn-primary" id="cnsSiguiente">
        Siguiente <i class="fi fi-rr-angle-small-right"></i>
      </button>
      <button type="button" class="btn btn-primary" id="cnsFinalizar" style="display:none">Finalizar</button>
    </div>
  </div>
</section>

<!-- ===================== MODALES ===================== -->
<!-- Al finalizar el wizard: confirmar el estado (el switch de la cabecera pasa desapercibido). -->
<div class="modal fade" id="cnsModalEstado" tabindex="-1" aria-labelledby="cnsEstadoTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cnsEstadoTitulo">¿Dejar el formulario activo?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p>Estado actual: <strong id="cnsEstadoActual"></strong>.</p>
        <ul class="small text-muted mb-0">
          <li><strong>Activo:</strong> los operadores de las campañas asignadas lo ven y lo usan de inmediato.</li>
          <li><strong>Inactivo:</strong> queda guardado, pero nadie lo ve hasta que lo actives.</li>
        </ul>
        <div class="alert alert-warning small mt-3 mb-0" id="cnsEstadoSinCampana" style="display:none">
          No tiene campañas asignadas: aunque quede activo, ningún operador lo verá.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" id="cnsFinalizarInactivo">Dejar inactivo</button>
        <button type="button" class="btn btn-primary" id="cnsFinalizarActivo">Dejar activo</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cnsModalCambioEstado" tabindex="-1" aria-labelledby="cnsCambioEstadoTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cnsCambioEstadoTitulo">Cambiar estado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p id="cnsCambioEstadoTexto" class="mb-2"></p>
        <p id="cnsCambioEstadoDetalle" class="small text-muted mb-0"></p>
        <div class="alert alert-warning small mt-3 mb-0" id="cnsCambioEstadoSinCampana" style="display:none">
          No tiene campañas asignadas: aunque quede activo, ningún operador lo verá.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="cnsCambioEstadoConfirmar">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cnsModalVersiones" tabindex="-1" aria-labelledby="cnsVersionesTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cnsVersionesTitulo">Historial de versiones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="cnsHistorial"><p class="text-muted mb-0">Guarda el formulario para ver su historial.</p></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cnsModalEliminar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Eliminar formulario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">¿Eliminar <strong id="cnsEliminarNombre"></strong>? Los datos del formulario e historial se conservan para reportería.</div>
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
        <p class="mb-0 text-muted small">Puedes guardarlo así, pero ningún operador lo verá hasta que lo asignes a una campaña en el paso <strong>2 · Servicio</strong>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" id="cnsSinCampanaGuardar">Continuar sin campaña</button>
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
