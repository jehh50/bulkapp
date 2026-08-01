<script type="text/javascript">
  function upperCase(e) {
    e.value = e.value.toUpperCase();
  }

  $(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>

<div class="container" style="margin-top:40px;">
  <div class="row">
    <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10">
      
      <div class="panel panel-default">

        <!-- 🔹 TÍTULO AHORA DENTRO DEL PANEL -->
        <div class="panel-heading clearfix">
          <h3 class="panel-title">
            <strong>Edición del producto:</strong>
            <span class="text-primary">
              <strong><?=$product['descripcion']?></strong>
            </span>
          </h3>
        </div>

        <div class="panel-body">
          <form name="editProduct" method="POST" action="?view=configuracion&mode=actualizarProducto">

            <div class="row">

              <!-- Servicio -->
              <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                  <label>Servicio</label>
                  <input type="text"
                         class="form-control"
                         name="nombre"
                         onkeyup="upperCase(this);"
                         value="<?=$product['servicio']?>"
                         readonly>
                </div>
              </div>

              <!-- Nombre -->
              <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                  <label>Nombre del producto</label>
                  <input type="text"
                         class="form-control"
                         name="nombre"
                         onkeyup="upperCase(this);"
                         value="<?=$product['descripcion']?>"
                         required>
                </div>
              </div>

              <!-- Código -->
              <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                  <label>Código del producto</label>
                  <input type="text"
                         class="form-control"
                         name="codigo"
                         onkeyup="upperCase(this);"
                         value="<?=$product['codigo_producto']?>"
                         required>
                </div>
              </div>

              <!-- Precio -->
              <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                  <label>Precio del producto</label>
                  <input type="text"
                         class="form-control"
                         name="costo"
                         data-toggle="tooltip"
                         title="Formato 12345.67"
                         value="<?=$product['costo_prod']?>"
                         required>
                </div>
              </div>

            </div>

            <input type="hidden" name="id" value="<?=$_GET['id']?>">

            <div class="text-left">
              <a href="?view=configuracion&mode=index"
                 class="btn btn-default">
                 Regresar
              </a>
              <button type="submit" class="btn btn-primary">
                <span class="glyphicon glyphicon-refresh"></span> Actualizar
              </button>

            </div>

          </form>
        </div>

      </div>

    </div>
  </div>
</div>