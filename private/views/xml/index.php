<style>
  .box-marging-top {
    margin-top: 10px;
  }

  @media (max-width: 767px) {
    .caja {
      margin-top: 5px;
    }
  }
</style>
<div class="container box-marging-top">
  <div class="row">
    <!-- Bancaribe Download Panel -->
    <div class="col-lg-6 col-md-6 col-sm-12">
      <div class="panel panel-default">
        <div class="panel-heading panel-primary">
          <p class="panel-title">Descarga de XML - MD5SUM</p>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label>BANCAMIGA</label>
            <select class="selectpicker show-menu-arrow show-tick form-control" name="file_bancamiga" id="file_bancamiga">
              <option value='' disabled selected style='display:none;'>Seleccione...</option>
              <?php
              $path = opendir("public/archivos/bancamiga/");
              $fileList = [];
              while ($file = readdir($path)) {
                $fileList[] = $file;
              }
              sort($fileList);
              foreach ($fileList as $file) {
                echo "<option value='" . $file . "'>" . $file . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <input type="button" class="btn btn-md btn-success btn-block" id="btn-download-bancamiga" name="btn-download"
              value="Descargar" />
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
      <div class="panel panel-default">
        <div class="panel-heading panel-primary">
          <p class="panel-title">Descarga de XML - MD5SUM</p>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label>BANCARIBE</label>
            <select class="selectpicker show-menu-arrow show-tick form-control" name="file_bancaribe" id="file_bancaribe">
              <option value='' disabled selected style='display:none;'>Seleccione...</option>
              <?php
              $path = opendir("public/archivos/bancaribe/");
              $fileList = [];
              while ($file = readdir($path)) {
                $fileList[] = $file;
              }
              sort($fileList);
              foreach ($fileList as $file) {
                echo "<option value='" . $file . "'>" . $file . "</option>";
              }
              ?>
            </select>
          </div>
          
          <div class="form-group">
            <input type="button" class="btn btn-md btn-success btn-block" id="btn-download-bancaribe" name="btn-download"
              value="Descargar" />
          </div>
        </div>
      </div>
    </div>
    <!-- Generate XML Panel -->
    <div class="col-lg-6 col-md-6 col-sm-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <p class="panel-title">Generar XML - MD5SUM</p>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label>Servicio</label>
            <select class="form-control" name="servicio" id="servicio" required>
              <option value='0' disabled selected style='display:none;'>Servicio...</option>
              <?php foreach ($servicio as $s) { ?>
                <option value='<?php echo $s['id']; ?>'><?php echo $s['descripcion']; ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label>Fecha</label>
            <input type="date" class="form-control" aria-describedby="fecha_" value="<?= date('Y-m-d') ?>" name="fecha_"
              id="fecha_" autofocus />
          </div>
          <div class="form-group">
            <input type="button" class="btn btn-md btn-success btn-block" id="btn-download_" name="btn-download_"
              value="Generar" onclick="xml();" />
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="public/js/xml.js"></script>
<script src="public/js/jquery.binarytransport.js"></script>

<script type="text/javascript">
  function xml() {
    var fecha_ = $('#fecha_').val();
    var servicio = $('#servicio').val();
    var url = '?view=xml&mode=xml';


    $.ajax({
      type: 'POST',
      url: url,
      dataType: 'json',
      data: { fecha_: fecha_ ,servicio:servicio},
      success: function (datos) {
        if (datos.response === 'true') {
          alert('Archivo Generado');
          window.location.href = '?view=xml&mode=index';
        } else {
          alert('No se generó el archivo');
        }
      }
    });
  }
</script>