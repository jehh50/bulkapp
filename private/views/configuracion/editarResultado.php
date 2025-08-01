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
      e.value = `${day}-${month}-${year}`;
    } else if (month) {
      e.value = `${day}-${month}`;
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
    <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
      <div class="panel panel-default">
        <div class="panel-body">
          <section class="text-left">
            <h1>Edición de resultados</h1>
          </section>

          <div class="row">
            <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3">
              <label for="cedula">Cédula</label>
              <input type="text" class="form-control" placeholder="12658457" aria-describedby="cedula" id="cedula" maxlength="9" oninput="onlyNumbers(this)" value="17112024" />
            </div>
          </div>

          <!-- Panel Group -->
          <div class="row">
            <div class="col-xs-12">
              <div id="sales">
                <div class="panel-group" id="accordion">
                  <!-- Inicio de formulario -->
                  <!-- fin de formulario -->
                </div>
              </div>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div class="row text-center" style="margin-top: 15px;">
            <div class="col-xs-12">
              <button class="btn btn-primary" id="btn-buscar"><span class="glyphicon glyphicon-search"></span>
                Buscar</button>
              <button class="btn btn-warning" id="btn-limpiar"><span class="glyphicon glyphicon-refresh"></span>
                Refrescar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modales -->
<div id="modals">
</div>