<script>
  function validateService() {
    var a = document.getElementById('servicio').value;
    if (a === '') {
      alert('Por favor seleccione un servicio');
      return false;
    }
    return true;
  }
</script>

<div class="container" style="margin-top:1em;">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-12">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <h1>Resumen de ventas</h1>
            </section>
            <form name="resultados" method="POST" action="?view=reportes&mode=detalleventas" onsubmit="return validateService()">
              <div class="form-group">
                <div class="form-group col-lg-3">
                  <label>Desde</label>
                  <input type="date" class="form-control" aria-describedby="fecha_d" name="fecha_d" id="fecha_d" required autofocus />
                </div>
                <div class="form-group col-lg-3">
                  <label>Hasta</label>
                  <input type="date" class="form-control" aria-describedby="fecha_h" name="fecha_h" id="fecha_h" required />
                </div>
                <div class="form-group col-lg-3">
                  <label>Servicio</label>
                  <select class="form-control" name="servicio" id="servicio" required>
                    <option value="" disabled selected>Seleccione...</option>
                    <?php foreach ($servicio as $s) { ?>
                      <option value='<?php echo $s['id']; ?>'><?php echo $s['descripcion']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group col-lg-12">
                <input type="submit" class="btn btn-medium btn-success" value="Buscar" id="btn-buscar">
                <a href="?view=reportes&mode=download&from=<?= $desde ?>&to=<?= $hasta ?>&servicio=<?= $servicio[0]['id'] ?>" class="btn btn-medium btn-info" id="btn-download">Descargar</a>
              </div>
            </form>
            <div class="form-group col-lg-12">
            </div>

            <div class="table-responsive col-lg-12" style="max-height: 400px; overflow-y: auto;">
              <?php if ($_POST) { ?>
                <p class="sub-title">Total resultados: <?=count($ventas)?>. Período consultado <?= date('d-m-Y', strtotime($desde)); ?> - <?= date('d-m-Y', strtotime($hasta)) ?></p>
              <?php } ?>
              <table class="table table-hover table-condensed">
                <thead>
                  <tr class="bg-success">
                    <th style="text-align: center;">#</th>
                    <th style="text-align: center;">Nombre</th>
                    <th style="text-align: center;">Apellido</th>
                    <th style="text-align: center;">Cédula</th>
                    <th style="text-align: center;">Fecha de Nacimiento</th>
                    <th style="text-align: center;">Teléfono 1</th>
                    <th style="text-align: center;">Teléfono 2</th>
                    <th style="text-align: center;">Correo</th>
                    <th style="text-align: center;">Producto</th>
                    <th style="text-align: center;">Agente</th>
                    <th style="text-align: center;">Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($ventas)) { $i=1;
                    foreach ($ventas as $venta) { ?>
                      <tr align="center">
                        <td><?= $i?></td>
                        <td><?= $venta['nombre']; ?></td>
                        <td><?= $venta['apellido']; ?></td>
                        <td><?= $venta['cedula']; ?></td>
                        <td><?= $venta['fecha_nacimiento']; ?></td>
                        <td><?= $venta['telf_hab']; ?></td>
                        <td><?= $venta['telf_celular']; ?></td>
                        <td><?= $venta['correo']; ?></td>
                        <td><?= $venta['descripcion']; ?></td>
                        <td><?= $venta['agente']; ?></td>
                        <td><?= date('d-m-Y', strtotime($venta['fecha_venta'])); ?></td>
                      </tr>
                    <?php $i++;}
                  } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="public/js/download.js"></script>
