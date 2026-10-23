<script type="text/javascript">
function mayus(e) {
  e.value = e.value.toUpperCase();
}

  var conS=0
function agregarproducto(){
  $('#productosS').append('<div id="productosS'+conS+'"><br><div class="row"><div class="col-lg-3"><input type="text" class="form-control" name="producto[]" placeholder="PRODUCTO I" onkeypress="return sololetras(event)" onkeyup="mayus(this);" required></div><div class="col-lg-3"><input type="text" class="form-control" name="codigo[]" placeholder="025" onkeyup="mayus(this);" required></div><div class="col-lg-3"><input type="text" class="form-control" name="costo[]" placeholder="12345.67" onkeyup="mayus(this);" required></div><div class="col-lg-2"><input type="text" class="form-control" name="plan[]" placeholder="54A" onkeyup="mayus(this);" required></div><a title="Quitar Producto" id="quitarProductos" class="btn btn-outline-secondary btn-sm" onclick="quitarProductos('+conS+')"><i class="bi bi-dash"></i></a></div></div><br>')
      conS++
}

function quitarProductos(e){
  $('#productosS'+e).remove()
}

function sololetras(e) {
  key=e.keyCode || e.which;
  teclado=String.fromCharCode(key).toLowerCase();
  letras="qwertyuiopasdfghjklñzxcvbnm";

  especiales="32";
  if(letras.indexOf(teclado)==-1 && especiales!=key){
    alert("Solo se permite letras.")
    return false;
  }
}

</script>
<style>
.caja{
  margin-top: -150px;
}
</style>
<?php
if (isset($_GET['mensaje'])=='exito') {
  
  echo '  <script type="text/javascript">alert("REGISTRO EXITOSO"); $(location).attr("href","?view=agregar&mode=index");</script>';
}
?>

<div class="loginbox">
  <div class="container">
    <div class="caja">
      <div class="col-lg">
        <div class="login-panel card">
          <div class="card-header">
            <h1 class="card-title"><strong>Nuevo Producto</strong>  <a title="Agregar Producto" class="btn btn-outline-secondary" onclick="agregarproducto();"><i class="bi bi-plus"></i></a></h1>
          </div>
          <div class="card-body">
              <form  name="resultados" method="POST" action="?view=agregar&mode=guardarProductos">
                <div class="container">
                  <div class="col-lg-3">
                    <select class="form-select" name="venta" id="venta" required>
                      <?php foreach ($servicio as $s) { ?>
                      <option value='0' disabled selected style='display:none;'>Seleccione...</option>
                      <option value='<?php echo $s['cod_servicio'];?>'><?php echo $s['descripcion'];}?></option>
                    </select>
                  </div>
                </div>
                <br>
                <div class="mb-3">
                  <div id="productosS">
                    <div class="row">
                      <div class="col-lg-3">
                        <label>Nombre del producto</label>
                        <input type="text" class="form-control" name="producto[]" id="producto" placeholder="PRODUCTO I" onkeypress="return sololetras(event)" onkeyup="mayus(this);" required>
                      </div>
                      <div class="col-lg-3">
                        <label>Código del producto</label>
                        <input type="text" class="form-control" name="codigo[]" placeholder="025" onkeyup="mayus(this);" required>
                      </div>
                      <div class="col-lg-3">
                        <label>Precio del producto</label>
                        <input type="text" class="form-control" name="costo[]" placeholder="12345.67" onkeyup="mayus(this);" data-bs-toggle="tooltip" data-bs-placement="bottom" title="El precio debe ser de esta forma 12345.67" required>
                      </div>
                      <div class="col-lg-2">
                        <label>Codigo Plan</label>
                        <input type="text" class="form-control" name="plan[]" placeholder="54A" onkeyup="mayus(this);" required>
                      </div><br>
                    </div>                        
                  </div>
                </div>
                <input type="submit" class="btn btn-md btn-success" value="Guardar" id="btn-buscar">
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



