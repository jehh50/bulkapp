<script type="text/javascript">
  function upperCase(e) {
    e.value = e.value.toUpperCase();
  }
</script>

<div class="container">
  <div class="col">
    <div class="h3 mt-4">
      Edición del producto: <label class="text-primary"><?=$product['descripcion']?></label>
    </div>
  </div>
  <div class="card shadow">
    <div class="card-body shadow">
      <form name="editProduct" method="POST" action="?view=configuracion&mode=actualizarProducto">
        <div class="row">
          <div class="col-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="nombre" id="nombre" onkeyup="upperCase(this);" value="<?=$product['descripcion'] ?>">
              <label for="product" class="text-wrap py-2">Nombre del producto</label>
            </div>
          </div>
          <div class="col-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="codigo" id="codigo" onkeyup="upperCase(this);" value="<?=$product['codigo_producto'] ?>">
              <label for="code" class="text-wrap py-2">Código del producto</label>
            </div>
          </div>
          <div class="col-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="costo" id="costo" onkeyup="upperCase(this);" data-toggle="tooltip"
                data-placement="bottom" title="El precio debe ser de esta forma 12345.67" value="<?=$product['costo_prod']?>">
              <label for="cost" class="text-wrap py-2">Precio del producto</label>
            </div>
          </div>
        </div>
        <input type="hidden" name="id" value="<?=$_GET['id']?>">
        <input type="submit" class="btn btn-md btn-primary my-2" value="Actualizar" id="btn-save">
        <a href="?view=configuration&action=listProducts" class="btn btn-md btn-dark my-2">Regresar</a>
    </div>
    </form>
  </div>
</div>