<script>
function onlyLetters(e) {
    // Convertir el texto a mayúsculas y permitir solo letras y números
    e.value = e.value
        .toUpperCase() // Convertir a mayúsculas
        .replace(/[^A-Z\s]/g, ''); // Eliminar caracteres que no sean letras ni números
}

function onlyNumbers(e) {
    // Permitir solo números y un solo punto decimal
    e.value = e.value.replace(/[^0-9.]/g, '');
    // Solo permite un punto decimal
    let parts = e.value.split('.');
    if (parts.length > 2) {
        e.value = parts[0] + '.' + parts.slice(1).join('');
    }
}
</script>
<script src="public/js/edit.js"></script>

<div class="container-fluid" style="margin-top: 40px";>
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">

      <div class="panel panel-default">
        <div class="panel-body">

          <!-- Título -->
          <div class="row">
            <div class="col-xs-12">
              <h2 class="text-center text-sm-left" style="margin-top:0;">
                Edición de resultados
              </h2>
              <hr>
            </div>
          </div>

          <?php
          if (isset($_GET['mensaje'])) {
            switch ($_GET['mensaje']) {
              case 'exito':
                $mensajeHtml = '<strong>¡Éxito!</strong> Registro actualizado.';
                $tipo_alerta = 'success';
                break;
              case 'error':
                $mensajeHtml = '<strong>Error</strong> Se presentó un error intente de nuevo.';
                $tipo_alerta = 'danger';
                break;
              case 'eliminado':
                $mensajeHtml = '<strong>¡Éxito!</strong> Registro eliminado.';
                $tipo_alerta = 'success';
                break;
            }
            echo '
            <div class="alert alert-'.$tipo_alerta.' alert-dismissible text-center" role="alert" id="message">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              '.$mensajeHtml.'
            </div>';
          }

          ?>

          <!-- Campo Cédula -->
          <div class="row">
            <div class="form-group col-xs-12 col-sm-6 col-md-4">
              <label for="cedula">Cédula</label>
              <input type="text"
                     class="form-control"
                     placeholder="12658457"
                     id="cedula"
                     maxlength="9"
                     oninput="onlyNumbers(this)">
            </div>
          </div>

          <!-- Panel dinámico -->
          <div class="row">
            <div class="col-xs-12">
              <div id="sales">
                <div class="panel-group" id="accordion"></div>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-4">
              <button class="btn btn-primary" id="btn-buscar">
                <span class="glyphicon glyphicon-search"></span> Buscar
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modales -->
<div id="modals"></div>
