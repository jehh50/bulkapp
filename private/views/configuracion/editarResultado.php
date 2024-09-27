<script src="public/js/edit.js"></script>
<script type="text/javascript">
  function formatDate(e) {
    let value = e.value.replace(/\D/g, ''); // Eliminar todo lo que no sea dígito
    let day, month, year;

    if (value.length >= 1) {
      day = value.substring(0, 2); // Obtener los primeros 2 dígitos (día)
    }

    if (value.length >= 3) {
      month = value.substring(2, 4); // Obtener los siguientes 2 dígitos (mes)
    }

    if (value.length >= 5) {
      year = value.substring(4, 8); // Obtener los últimos 4 dígitos (año)
    }

    if (year) {
      e.value = `${day}/${month}/${year}`;
    } else if (month) {
      e.value = `${day}/${month}`;
    } else if (day) {
      e.value = day;
    }
  }

  function formatDate_(e) {
    let value = e.value.replace(/\D/g, ''); // Eliminar todo lo que no sea dígito
    let day, month, year;

    if (value.length >= 1) {
      day = value.substring(0, 4); // Obtener los primeros 2 dígitos (día)
    }

    if (value.length >= 3) {
      month = value.substring(4, 6); // Obtener los siguientes 2 dígitos (mes)
    }

    if (value.length >= 5) {
      year = value.substring(6, 8); // Obtener los últimos 4 dígitos (año)
    }

    if (year) {
      e.value = `${day}${month}${year}`;
    } else if (month) {
      e.value = `${day}${month}`;
    } else if (day) {
      e.value = day;
    }
  }

  function onlyLetters(e) {
    // Convertir el texto a mayúsculas y permitir solo letras y números
    e.value = e.value
      .toUpperCase() // Convertir a mayúsculas
      .replace(/[^A-Z\s]/g, ''); // Eliminar caracteres que no sean letras ni números
  }

  function onlyNumbers(e) {
    // Convertir el texto a mayúsculas y permitir solo letras y números
    e.value = e.value
      .replace(/[^0-9]/g, ''); // Eliminar caracteres que no sean números
  }

  function validateMail(e) {
    valueForm = e.value;
    var patron = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/)
    if (valueForm.search(patron) != 0) {
      alert('La dirección de correo es invalida, el formato debe coincidir con DIRECCION@DOMINIO.COM intente de nuevo.');
      window.location.hash = "#correo";
    }
  }
</script>

<?php
if (isset($_GET['mensaje']) == 'exito') {
  echo '  <script type="text/javascript">alert("REGISTRO EXITOSO"); $(location).attr("href","?view=editar&mode=index");</script>';
}
?>
<div class="container" style="margin-top:20px;">
  <div class="row">
    <div class="col-sm-10 col-md-10 col-lg-offset-1 col-lg-10">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <label>
                <h1>Edición de resultados</h1>
              </label>
            </section>
            <div class="form-group col-lg-12" id="servicio" hidden>
            </div>
            <input type="hidden" id="id_resultado">
            <input type="hidden" id="cod_servicio">
            <input type="hidden" id="gestion">

            <div class="form-group col-lg-6">
              <span class="form-group-addon">Cedula</span>
              <input type="text" class="form-control" placeholder="12658457" aria-describedby="cedula" id="cedula"
                maxlength="9" oninput="onlyNumbers(this)" />
            </div>
            <div class="form-group col-lg-6">
              <span class="form-group-addon">Nombres</span>
              <input type="text" class="form-control" placeholder="Julio Cesar" aria-describedby="nombre" id="nombre"
                oninput="onlyLetters(this);" readonly />
            </div>
            <div class="form-group col-lg-6">
              <span class="form-group-addon">Apellidos</span>
              <input type="text" class="form-control" placeholder="Perez Gomez" aria-describedby="apellido"
                id="apellido" oninput="onlyLetters(this);" readonly />
            </div>

            <div class="form-group col-lg-6" id="d_genero">
            </div>

            <div class="form-group col-lg-6">
              <span class="form-group-addon">Fecha de nacimiento</span>
              <input type="text" class="form-control" placeholder="01/01/2024" aria-describedby="d_nacimiento"
                id="d_nacimiento" oninput="formatDate(this);" readonly />
            </div>

            <div class="form-group col-lg-6">
              <span class="form-group-addon">Teléfono habitación</span>
              <input type="text" class="form-control telefono" placeholder="(0212)345.67.89" aria-describedby="tlf_hab"
                id="telf_hab" readonly />
            </div>

            <div class="form-group col-lg-6">
              <span class="form-group-addon">Teléfono celular</span>
              <input type="text" class="form-control telefono" placeholder="(0424)234.56.78" aria-describedby="tlf_celu"
                id="telf_cel" readonly />
            </div>
            <div class="form-group col-lg-6">
              <span class="form-group-addon">Correo</span>
              <input type="mail" class="form-control" placeholder="usario@dominio.com" aria-describedby="correo"
                id="correo" onkeyup="mayus(this);" onchange="validateMail(this);" readonly />
            </div>
            <div class="form-group col-lg-6">
              <span class="form-group-addon">Fecha de venta</span>
              <input type="mail" class="form-control" placeholder="20240101" aria-describedby="saleDate" id="saleDate"
                oninput="formatDate_(this)" readonly />
            </div>
            <div class="form-group col-lg-6" id="d_producto">
              <span class="form-group-addon">Tipo de producto</span>
              <select class="form-control" name="producto" id="producto">
                <option value='' disabled selected style='display:none;'>Seleccione...</option>
                <?php foreach ($productos as $p) { ?>
                  <option value='<?php echo $p['id']; ?>'><?php echo $p['descripcion'];
                } ?></option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <button class="btn btn-md btn-primary btn-md" id="btn-buscar"><span
              class="glyphicon glyphicon-search"></span> Buscar</button>
              <button class="btn btn-md btn-success btn-md" id="btn-actualizar"><span
              class="glyphicon glyphicon-floppy-disk"></span> Actualizar</button>
              <button class="btn btn-md btn-warning btn-md" id="btn-limpiar"><span
              class="glyphicon glyphicon-refresh"></span> Limpiar</button>
              <!-- <button class="btn btn-md btn-danger btn-md" id="btn-eliminar" data-toggle="modal" data-target="#modalRechazo"><span class="glyphicon glyphicon-remove"></span> Eliminar</button> -->
            </div>
          </div>
          <!--/form-->
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalRechazo" tabindex="-1" role="dialog" aria-labelledby="modalRechazo">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalRechazo">Seleccione el motivo por el cual esta eliminando la venta</h4>
      </div>
      <div class="modal-body">
        <select class="form-control" id="eliminar_venta" required>
          <option value="0" disabled selected style="display:none;">Seleccione...</option>
          <option value="error">Venta con error</option>
          <option value="forzada">Venta con mala gestión o forzadas</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Descartar</button>
        <button type="button" class="btn btn-primary" id="btn-guardar"><span
            class="glyphicon glyphicon-floppy-disk"></span> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal confirmación de venta eliminada-->
<div class="modal fade" id="modalConfirm" tabindex="-1" role="dialog" aria-labelledby="modalConfirm">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" align="center">
      <div class="modal-header">
        <h3>Rechazo de venta procesado con exito</h3>
      </div>
      <div class="modal-body">
        <h4>Por favor espere mientras se actualiza la página</h4>
        <img src="public/images/refresh.gif" alt="refresh" height="50px" width="50px" />
      </div>
    </div>
  </div>
</div>

<!-- Modal confirmación de actualización -->
<div class="modal fade" id="modalActualiza" tabindex="-1" role="dialog" aria-labelledby="modalConfirm">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" align="center">
      <div class="modal-header">
        <h3>Registro actualizado con exito</h3>
      </div>
      <div class="modal-body">
        <h4>Por favor espere mientras se actualiza la página</h4>
        <img src="public/images/refresh.gif" alt="refresh" height="50px" width="50px" />
      </div>
    </div>
  </div>
</div>