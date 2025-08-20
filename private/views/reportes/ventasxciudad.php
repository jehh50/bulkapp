<script type="text/javascript" src="public/js/loader.js"></script>
<div class="container">
  <div class="row">
    <div class="col-lg-12">
      <section class="container">
        <header>
          <h1></h1>
        </header>
      </section>
      <section>
        <label>
          <h1 style="color:#ffffff">Detalle de ventas para el estado <strong><a href="?view=reportes&mode=ventas"><?= $estado; ?></a></strong>
          </h1>
        </label>
      </section>
    </div>
  </div>
</div>
<br>
<div class="container">
  <div class="row">
    <div class="col-lg-6">
      <div class="panel panel-danger">
        <div class="panel-heading" style="text-align: center; font-size: 16px;">VENTAS POR CIUDAD</div>
        <div class="panel-body">
          <div class="table-responsive">
            <div style="overflow-y: scroll; height:auto">
              <table class="table table-responsive table-hover table-condensed">
                <thead class="bg-danger">
                  <tr>
                    <th style="text-align: center;">
                      <h5>CIUDAD</h5>
                    </th>
                    <th style="text-align: center;">
                      <h5>VENTAS</h5>
                    </th>
                  </tr>
                </thead>
                <?php $total = 0;
                foreach ($result as $ventas) { ?>
                  <tbody>
                    <tr align="center">
                      <td>
                        <h5><?= $ventas['ciudad']; ?></h5>
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
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="panel panel-success">
        <div class="panel-body bg-warning">
          <div id="chart_ne" style="height:300px"></div>
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
        ['<?= $ventas['ciudad']; ?>', <?= $ventas['total']; ?>]
      ]);
    <?php } ?>

    // Set chart options
    var options = {
      'title': 'Ventas x Ciudad',
      legend: { position: "none" },
      is3D: true
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('chart_ne'));
    chart.draw(data, options);
  }
</script>