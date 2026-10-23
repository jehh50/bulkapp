<script type="text/javascript">
  function upperCase(e) {
    e.value = e.value.toUpperCase();
  }
</script>

<div class="container" style="margin-top:40px;">
  <div class="row">
    <div class="col-12 col-sm-10 offset-sm-1 col-md-10">
      
      <div class="card">

        <!-- 🔹 TÍTULO AHORA DENTRO DEL PANEL -->
        <div class="card-header clearfix">
          <h3 class="card-title">
            <strong>Edición del producto:</strong>
            <span class="text-primary">
              <strong><?=$product['descripcion']?></strong>
            </span>
          </h3>
        </div>

        <div class="card-body">
          <form name="editProduct" method="POST" action="?view=configuracion&mode=actualizarProducto">

            <div class="row">

              <!-- Servicio -->
              <div class="col-12 col-sm-6 col-md-4">
                <div class="mb-3">
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
              <div class="col-12 col-sm-6 col-md-4">
                <div class="mb-3">
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
              <div class="col-12 col-sm-6 col-md-4">
                <div class="mb-3">
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
              <div class="col-12 col-sm-6 col-md-4">
                <div class="mb-3">
                  <label>Precio del producto</label>
                  <input type="text"
                         class="form-control"
                         name="costo"
                         data-bs-toggle="tooltip"
                         title="Formato 12345.67"
                         value="<?=$product['costo_prod']?>"
                         required>
                </div>
              </div>

            </div>

            <input type="hidden" name="id" value="<?=$_GET['id']?>">

            <div class="text-start">
              <a href="?view=configuracion&mode=index"
                 class="btn btn-outline-secondary">
                 Regresar
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise"></i> Actualizar
              </button>

            </div>

          </form>
        </div>

      </div>

    </div>
  </div>
</div>