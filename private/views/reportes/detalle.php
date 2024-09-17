<script type="text/javascript">
  function validateForm() {
    if (document.getElementById('servicio').value == "") {
      alert("Debe seleccionar un servicio");
      return false;
    }
  }
</script>

<div class="container">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-12">
      <section class="container">
        <header>
          <h1></h1>
        </header>
      </section>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <label>
                <h1>Gestión detallada</h1>
              </label>
            </section>
            <form name="resultados" method="POST" action="?view=reportes&mode=descargar"
              onsubmit="return validateForm()">
              <div class="form-group">
                <div class="form-group col-lg-3">
                  <label>Desde</label><input type="date" class="form-control" aria-describedby="fecha_d"
                    value="<?= date('Y-m-d') ?>" name="fecha_d" id="fecha_d" autofocus />
                </div>
                <div class="form-group col-lg-3">
                  <label>Hasta</label><input type="date" class="form-control" aria-describedby="fecha_h"
                    value="<?= date('Y-m-d') ?>" name="fecha_h" id="fecha_h" autofocus />
                <div class="form-group col-lg-3">
                  <label>Servicio</label>
                  <select class="form-control" name="servicio" id="servicio">
                    <?php foreach ($servicio as $s) { ?>
                      <option value='' disabled selected style='display:none;'>Seleccione...</option>
                      <option value='<?=$s['id'];?>'><?=$s['descripcion'];} ?></option>
                  </select>
                </div>
              </div>
              <div class="form-group col-lg-12">
                <input type="submit" class="btn btn-medium btn-success" value="Descargar" id="btn-buscar">
              </div>
            </form>
            <div class="table-responsive col-lg-12">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>