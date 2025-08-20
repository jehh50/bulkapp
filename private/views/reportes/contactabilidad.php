<script type="text/javascript" src="public/js/loader.js"></script>
<div class="container" style="margin-top: 10px;">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="form-group">
          <section>
            <label>
              <h1>Resumen de gestión por Contactos</h1>
            </label>
          </section>
          <form name="resultados" method="POST" action="?view=reportes&mode=contactabilidad">
            <div class="form-group">
              <div class="form-group col-lg-3">
                <label>Desde</label><input type="date" class="form-control" aria-describedby="fecha_d"
                  value="<?= $desde ?>" name="fecha_d" id="fecha_d" autofocus />
              </div>
              <div class="form-group col-lg-3">
                <label>Hasta</label><input type="date" class="form-control" aria-describedby="fecha_h"
                  value="<?= $hasta ?>" name="fecha_h" id="fecha_h" autofocus />
              </div>
              <div class="form-group col-lg-3">
                <label>Servicio</label>
                <select class="form-control" name="servicio" id="servicio">
                  <?php foreach ($servicio as $s) { ?>
                    <option value='' disabled selected style='display:none;'>Seleccione...</option>
                    <option value='<?php echo $s['id']; ?>'><?php echo $s['descripcion'];
                  } ?></option>
                </select>
              </div>
            </div>
            <div class="form-group col-lg-12">
              <input type="submit" class="btn btn-medium btn-success" value="Buscar" id="btn-buscar">
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <div class="form-row">
    <div class="col-lg-6">
      <div class="panel panel-danger">
        <div class="panel-heading">Gestión NO CONTACTO</div>
        <div class="panel-body">
          <table class="table table-responsive table-hover table-condensed">
            <thead>
              <tr>
                <th style="text-align: center;">
                  <h5>DESCRIPCIÓN</h5>
                </th>
                <th style="text-align: center;">
                  <h5>TOTAL</h5>
                </th>
              </tr>
            </thead>
            <?php $total = 0;
            if (!empty($resultn)) {
              foreach ($resultn as $noefectivo) { ?>
                <tbody>
                  <tr align="center">
                    <td>
                      <h5><?= $noefectivo['descripcion']; ?></h5>
                    </td>
                    <td>
                      <h5><?= $noefectivo['total']; ?></h5>
                    </td>
                  </tr>
                  <?php
                  $total = $total + $noefectivo['total'];
              }
            } ?>
              <tr align="center">
                <td>
                  <h5>TOTAL</h5>
                </td>
                <td>
                  <h5><?= $total; ?></h5>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div id="chart_ne"></div>
      </div>
    </div>

    <div class="col-lg-offset-0 col-lg-6">
      <div class="panel panel-success">
        <div class="panel-heading">Gestión CONTACTO</div>
        <div class="panel-body">
          <table class="table table-responsive table-hover table-condensed">
            <thead>
              <tr>
                <th style="text-align: center;">
                  <h5>DESCRIPCIÓN</h5>
                </th>
                <th style="text-align: center;">
                  <h5>TOTAL</h5>
                </th>
              </tr>
            </thead>

            <?php $total2 = 0; if(!$resulte){}else{
            foreach ($resulte as $efectivo) { ?>
              <tbody>
                <tr align="center">
                  <td>
                    <h5><?= $efectivo['descripcion']; ?></h5>
                  </td>
                  <td>
                    <h5><?= $efectivo['total']; ?></h5>
                  </td>
                </tr>
                <?php
                $total2 = $total2 + $efectivo['total'];
            } ?>
              <tr align="center">
                <td>
                  <h5>TOTAL</h5>
                </td>
                <td>
                  <h5><?= $total2; ?></h5>
                </td>
              </tr>
            </tbody>
            <?php } ?>
          </table>
        </div>
        <div id="chart_ef"></div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  // Load the Visualization API and the corechart package.
  google.charts.load('current', { 'packages': ['corechart'] });

  // Set a callback to run when the Google Visualization API is loaded.
  google.charts.setOnLoadCallback(drawChart);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawChart() {

    // Create the data table.
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Motivo no contacto');
    data.addColumn('number', 'Total');
    <?php foreach ($resultn as $noefectivo) { ?>
      data.addRows([
        ['<?= $noefectivo['descripcion']; ?>', <?= $noefectivo['total']; ?>]
      ]);
    <?php } ?>

    // Set chart options
    var options = {
      'title': 'Contacto NO EFECTIVO',
      'width': 530,
      'height': 400,
      is3D: true
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('chart_ne'));
    chart.draw(data, options);
  }

</script>
<script type="text/javascript">

  // Load the Visualization API and the corechart package.
  google.charts.load('current', { 'packages': ['corechart'] });

  // Set a callback to run when the Google Visualization API is loaded.
  google.charts.setOnLoadCallback(drawChart);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawChart() {

    // Create the data table.
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Topping');
    data.addColumn('number', 'Slices');
    <?php foreach ($resulte as $efectivo) { ?>
      data.addRows([
        ['<?= $efectivo['descripcion']; ?>', <?= $efectivo['total']; ?>]
      ]);
    <?php } ?>

    // Set chart options
    var options = {
      'title': 'Contacto EFECTIVO',
      'width': 530,
      'height': 400,
      is3D: true
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('chart_ef'));
    chart.draw(data, options);
  }
</script>