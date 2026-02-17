<script type="text/javascript" src="public/js/loader.js"></script>
<div class="container-fluid" style="margin-top:15px;">
  <div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">
      <div class="panel panel-default">
        <div class="panel-body">
          <h2 class="text-center">Resumen de gestión por Contactos</h2>
          <hr>
          <form method="POST" action="?view=reportes&mode=contactabilidad">
            <div class="row">

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Desde</label>
                <input type="date"
                       class="form-control"
                       name="fecha_d"
                       value="<?= $desde ?>">
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Hasta</label>
                <input type="date"
                       class="form-control"
                       name="fecha_h"
                       value="<?= $hasta ?>">
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Servicio</label>
                <select class="form-control" name="servicio" required>
                  <option value="" disabled selected>Seleccione...</option>
                  <?php foreach ($servicio as $s) { ?>
                    <option value="<?= $s['id']; ?>">
                      <?= $s['descripcion']; ?>
                    </option>
                  <?php } ?>
                </select>
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3" style="margin-top:25px;">
                <button type="submit"
                        class="btn btn-success btn-block">
                  Buscar
                </button>
              </div>

            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">

      <!-- NO CONTACTO -->
      <div class="col-xs-12 col-md-6">
        <div class="panel panel-danger">
          <div class="panel-heading text-center">
            <strong>Gestión NO CONTACTO</strong>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-condensed">
                <thead>
                  <tr class="text-center">
                    <th>Descripción</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $total = 0;
                  if (!empty($resultn)) {
                    foreach ($resultn as $r) {
                      $total += $r['total']; ?>
                      <tr class="text-center">
                        <td><?= $r['descripcion']; ?></td>
                        <td><?= $r['total']; ?></td>
                      </tr>
                  <?php }} ?>
                  <tr class="info text-center">
                    <td><strong>TOTAL</strong></td>
                    <td><strong><?= $total; ?></strong></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div id="chart_ne" style="width:100%; height:400px;"></div>

          </div>
        </div>
      </div>

      <!-- CONTACTO -->
      <div class="col-xs-12 col-md-6">
        <div class="panel panel-success">
          <div class="panel-heading text-center">
            <strong>Gestión CONTACTO</strong>
          </div>
          <div class="panel-body">

            <div class="table-responsive">
              <table class="table table-striped table-condensed">
                <thead>
                  <tr class="text-center">
                    <th>Descripción</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $total2 = 0;
                  if (!empty($resulte)) {
                    foreach ($resulte as $r) {
                      $total2 += $r['total']; ?>
                      <tr class="text-center">
                        <td><?= $r['descripcion']; ?></td>
                        <td><?= $r['total']; ?></td>
                      </tr>
                  <?php }} ?>
                  <tr class="success text-center">
                    <td><strong>TOTAL</strong></td>
                    <td><strong><?= $total2; ?></strong></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div id="chart_ef" style="width:100%; height:400px;"></div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>


<script>
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts(){

  drawPie('chart_ne', 'Contacto NO EFECTIVO', [
    <?php foreach ($resultn as $r) { ?>
      ['<?= $r['descripcion']; ?>', <?= $r['total']; ?>],
    <?php } ?>
  ]);

  drawPie('chart_ef', 'Contacto EFECTIVO', [
    <?php foreach ($resulte as $r) { ?>
      ['<?= $r['descripcion']; ?>', <?= $r['total']; ?>],
    <?php } ?>
  ]);

}

function drawPie(elementId, title, rows){

  var data = new google.visualization.DataTable();
  data.addColumn('string','Motivo');
  data.addColumn('number','Total');
  data.addRows(rows);

  var options = {
    title: title,
    width: '100%',
    height: 400,
    chartArea: {width:'90%', height:'75%'},
    is3D: true
  };

  var chart = new google.visualization.PieChart(
    document.getElementById(elementId)
  );

  chart.draw(data, options);

  window.addEventListener('resize', function(){
    chart.draw(data, options);
  });

}
</script>