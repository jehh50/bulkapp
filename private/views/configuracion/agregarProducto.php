<script type="text/javascript">
function mayus(e) {
  e.value = e.value.toUpperCase();
}

var conS = 0;

function agregarproducto(){
  $('#productosS').append(
    '<div id="productosS'+conS+'" class="producto-item">'+
      '<div class="row">'+
        '<div class="col-12 col-sm-6 col-md-4">'+
          '<div class="mb-3">'+
            '<input type="text" class="form-control" name="producto[]" placeholder="PRODUCTO I" onkeyup="mayus(this);" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-12 col-sm-6 col-md-2">'+
          '<div class="mb-3">'+
            '<input type="text" class="form-control" name="codigo[]" placeholder="025" onkeyup="mayus(this);" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-12 col-sm-6 col-md-2">'+
          '<div class="mb-3">'+
            '<input type="text" class="form-control" name="costo[]" placeholder="12345.67" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-12 col-sm-6 col-md-2">'+
          '<div class="mb-3">'+
            '<input type="text" class="form-control" name="plan[]" placeholder="54A" onkeyup="mayus(this);" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-12 col-sm-12 col-md-2 text-end">'+
          '<button type="button" class="btn btn-danger btn-sm" onclick="quitarProductos('+conS+')">'+
            '<i class="bi bi-dash"></i> Quitar'+
          '</button>'+
        '</div>'+
      '</div>'+
      '<hr>'+
    '</div>'
  );
  conS++;
}

function quitarProductos(e){
  $('#productosS'+e).remove();
}

</script>

<style>
.loginbox {
  margin-top: 40px;
}

.card-title .btn {
  float: right;
}

.producto-item {
  margin-bottom: 15px;
}
</style>

<div class="container loginbox">
  <div class="row">
    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2">
      
      <div class="card">
        <div class="card-header clearfix">
          <h3 class="card-title float-start">
            <strong>Nuevo Producto</strong>
          </h3>
          <button type="button" 
                  class="btn btn-info btn-sm float-end"
                  onclick="agregarproducto();">
            <i class="bi bi-plus"></i> Agregar
          </button>
        </div>

        <div class="card-body">
          <form name="resultados" method="POST" action="?view=configuracion&mode=guardarProductos">

            <!-- SELECT -->
            <div class="row">
              <div class="col-12 col-sm-6">
                <div class="mb-3">
                  <label>Servicio</label>
                  <select class="form-select" name="venta" required>
                    <option value="" disabled selected>Seleccione...</option>
                    <?php foreach ($servicio as $s) { ?>
                      <option value="<?php echo $s['id']; ?>">
                        <?php echo $s['descripcion']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>

            <hr>

            <!-- PRODUCTOS -->
            <div id="productosS">
              <div class="row">
                
                <div class="col-12 col-sm-6 col-md-4">
                  <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" class="form-control" name="producto[]" onkeyup="mayus(this);" required>
                  </div>
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                  <div class="mb-3">
                    <label>Código</label>
                    <input type="text" class="form-control" name="codigo[]" required>
                  </div>
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                  <div class="mb-3">
                    <label>Precio</label>
                    <input type="text" class="form-control" name="costo[]" 
                           data-bs-toggle="tooltip"
                           title="Formato 12345.67" required>
                  </div>
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                  <div class="mb-3">
                    <label>Plan</label>
                    <input type="text" class="form-control" name="plan[]" required>
                  </div>
                </div>

              </div>
            </div>

            <div class="text-start">
              <a href="?view=configuracion&mode=index" class="btn btn-outline-secondary">Regresar</a>
              <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Guardar
              </button>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>
