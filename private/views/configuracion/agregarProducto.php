<script type="text/javascript">
function mayus(e) {
  e.value = e.value.toUpperCase();
}

var conS = 0;

function agregarproducto(){
  $('#productosS').append(
    '<div id="productosS'+conS+'" class="producto-item">'+
      '<div class="row">'+
        '<div class="col-xs-12 col-sm-6 col-md-4">'+
          '<div class="form-group">'+
            '<input type="text" class="form-control" name="producto[]" placeholder="PRODUCTO I" onkeyup="mayus(this);" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-xs-12 col-sm-6 col-md-2">'+
          '<div class="form-group">'+
            '<input type="text" class="form-control" name="codigo[]" placeholder="025" onkeyup="mayus(this);" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-xs-12 col-sm-6 col-md-2">'+
          '<div class="form-group">'+
            '<input type="text" class="form-control" name="costo[]" placeholder="12345.67" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-xs-12 col-sm-6 col-md-2">'+
          '<div class="form-group">'+
            '<input type="text" class="form-control" name="plan[]" placeholder="54A" onkeyup="mayus(this);" required>'+
          '</div>'+
        '</div>'+
        '<div class="col-xs-12 col-sm-12 col-md-2 text-right">'+
          '<button type="button" class="btn btn-danger btn-sm" onclick="quitarProductos('+conS+')">'+
            '<span class="glyphicon glyphicon-minus"></span> Quitar'+
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

$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

<style>
.loginbox {
  margin-top: 40px;
}

.panel-title .btn {
  float: right;
}

.producto-item {
  margin-bottom: 15px;
}
</style>

<div class="container loginbox">
  <div class="row">
    <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
      
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <h3 class="panel-title pull-left">
            <strong>Nuevo Producto</strong>
          </h3>
          <button type="button" 
                  class="btn btn-info btn-sm pull-right"
                  onclick="agregarproducto();">
            <span class="glyphicon glyphicon-plus"></span> Agregar
          </button>
        </div>

        <div class="panel-body">
          <form name="resultados" method="POST" action="?view=configuracion&mode=guardarProductos">

            <!-- SELECT -->
            <div class="row">
              <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                  <label>Servicio</label>
                  <select class="form-control" name="venta" required>
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
                
                <div class="col-xs-12 col-sm-6 col-md-4">
                  <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" class="form-control" name="producto[]" onkeyup="mayus(this);" required>
                  </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-2">
                  <div class="form-group">
                    <label>Código</label>
                    <input type="text" class="form-control" name="codigo[]" required>
                  </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-2">
                  <div class="form-group">
                    <label>Precio</label>
                    <input type="text" class="form-control" name="costo[]" 
                           data-toggle="tooltip"
                           title="Formato 12345.67" required>
                  </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-2">
                  <div class="form-group">
                    <label>Plan</label>
                    <input type="text" class="form-control" name="plan[]" required>
                  </div>
                </div>

              </div>
            </div>

            <div class="text-left">
              <a href="?view=configuracion&mode=index" class="btn btn-default">Regresar</a>
              <button type="submit" class="btn btn-success">
                <span class="glyphicon glyphicon-floppy-disk"></span> Guardar
              </button>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>
