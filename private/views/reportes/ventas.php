<script type="text/javascript" src="public/js/loader.js"></script>
<div class="container">
  <div class="row">
    <div class="col-lg-12">
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
                <h1>Resumen de gestión por contactos</h1>
              </label>
            </section>
            <form name="resultados" method="POST" action="?view=reportes&mode=ventas">
              <div class="form-group">
                <div class="form-group col-lg-3">
                  <label>Desde</label><input type="date" class="form-control" aria-describedby="fecha_d" name="fecha_d"
                    id="fecha_d" autofocus required>
                </div>
                <div class="form-group col-lg-3">
                  <label>Hasta</label><input type="date" class="form-control" aria-describedby="fecha_h" name="fecha_h"
                    id="fecha_h" autofocus required>
                </div>
                <div class="form-group col-lg-3">
                  <label>Servicio</label>
                  <select class="form-control" name="servicio" id="servicio" required>
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
</div>
<div class="container">
  <div class="row">
    <div class="col-lg-6">
      <div class="panel panel-danger">
        <div class="panel-heading" style="text-align: center; font-size: 16px; letter-spacing: 4pt;"><strong>VENTAS POR
            ESTADO</strong></div>
        <div class="panel-body">
          <div class="table-responsive">
            <div style="overflow-y: scroll; height: 500px;">
              <table class="table table-responsive table-hover table-condensed">
                <thead>
                  <tr>
                    <th style="text-align: center;">
                      <h5><strong>ESTADO</strong></h5>
                    </th>
                    <th style="text-align: center;">
                      <h5><strong>VENTAS</strong></h5>
                    </th>
                  </tr>
                </thead>
                <?php $total = 0;
                if(!$result){}else{foreach ($result as $ventas) { ?>
                  <tbody>
                    <tr align="center">
                      <td>
                        <h5><a
                            href="?view=reportes&mode=ventasxestado&id=<?= $ventas['id'] ?>&d=<?= $desde ?>&h=<?= $hasta ?>&estado=<?= $ventas['estado'] ?>&servicio=<?= $serv ?>">
                            <?= $ventas['estado'];?>
                          </a>
                        </h5>
                      </td>
                      <td>
                        <h5><?= $ventas['total']; ?></h5>
                      </td>
                    </tr>
                    <?php
                    $total = $total + $ventas['total'];
                } ?>
                  <tr align="center">
                    <td>
                      <h5><strong>TOTAL</strong></h5>
                    </td>
                    <td>
                      <h5><strong><?= $total; ?></strong></h5>
                    </td>
                  </tr>
                </tbody><?php }?>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="panel panel-success">
        <div class="panel-body">
          <div id="chart_ne" style="height: 542px"></div>
        </div>
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
    data.addColumn('string', 'Estado');
    data.addColumn('number', 'Total');
    <?php foreach ($result as $ventas) { ?>
      data.addRows([
        ['<?= $ventas['estado']; ?>', <?= $ventas['total']; ?>]
      ]);
    <?php } ?>

    // Set chart options
    var options = {
      'title': 'Ventas x Estado',
      legend: { position: "none" },
      is3D: true
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('chart_ne'));
    chart.draw(data, options);
  }
</script>