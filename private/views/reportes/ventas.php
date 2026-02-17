<script type="text/javascript" src="public/js/loader.js"></script>

<style>
  .table-scroll {
    max-height: 60vh;
    overflow-y: auto;
  }

  .chart-container {
    width: 100%;
    min-height: 320px;
    height: 60vh;
  }
</style>

<div class="container-fluid" style="margin-top:40px;">
  <div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">

      <div class="panel panel-default">
        <div class="panel-body">
          <h2 class="text-center">Resumen de ventas por Estado</h2>
          <hr>

          <form name="resultados" method="POST" action="?view=reportes&mode=ventas">
            <div class="row">

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Desde</label>
                <input type="date" class="form-control" name="fecha_d" id="fecha_d" required>
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Hasta</label>
                <input type="date" class="form-control" name="fecha_h" id="fecha_h" required>
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Servicio</label>
                <select class="form-control" name="servicio" id="servicio" required>
                  <option value="" disabled selected>Seleccione...</option>
                  <?php if (!empty($servicio)) {
                    foreach ($servicio as $s) { ?>
                      <option value="<?= $s['id']; ?>">
                        <?= htmlspecialchars($s['descripcion']); ?>
                      </option>
                  <?php }
                  } ?>
                </select>
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3" style="margin-top:25px;">
                <button type="submit" class="btn btn-success btn-block">
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

      <div class="row">

        <!-- TABLA -->
        <div class="col-xs-12 col-md-6">

          <div class="panel panel-danger">
            <div class="panel-heading text-center">
              <strong>VENTAS POR ESTADO</strong>
            </div>

            <div class="panel-body">
              <div class="table-responsive table-scroll">
                <table class="table table-striped table-condensed">

                  <thead>
                    <tr class="text-center">
                      <th>ESTADO</th>
                      <th>VENTAS</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php 
                    $total = 0;
                    if (!empty($result)) {
                      foreach ($result as $ventas) { 
                        $total += $ventas['total'];
                    ?>
                        <tr class="text-center">
                          <td>
                            <a href="?view=reportes&mode=ventasxestado&id=<?= $ventas['id'] ?>&d=<?= $desde ?>&h=<?= $hasta ?>&estado=<?= urlencode($ventas['estado']) ?>&servicio=<?= $serv ?>">
                              <?= htmlspecialchars($ventas['estado']); ?>
                            </a>
                          </td>
                          <td><?= $ventas['total']; ?></td>
                        </tr>
                    <?php 
                      } 
                    ?>
                      <tr class="info text-center">
                        <td><strong>TOTAL</strong></td>
                        <td><strong><?= $total; ?></strong></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td colspan="2" class="text-center text-muted">
                          Sin datos para mostrar
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>

                </table>
              </div>
            </div>
          </div>

        </div>


        <!-- GRAFICO -->
        <div class="col-xs-12 col-md-6">

          <div class="panel panel-success">
            <div class="panel-body">
              <div id="chart_ne" class="chart-container"></div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </div>
</div>


<script>
google.charts.load('current', { packages:['corechart'] });
google.charts.setOnLoadCallback(initChart);

let chart, data, options;

function initChart() {

  const container = document.getElementById('chart_ne');
  if (!container) return;

  const rows = <?= json_encode(
    !empty($result)
      ? array_map(function ($r) {
          return [$r['estado'], (int)$r['total']];
        }, $result)
      : []
  ); ?>;

  if (!rows || rows.length === 0) {
    container.innerHTML = "<div class='text-center text-muted'>Sin datos para mostrar</div>";
    return;
  }

  data = new google.visualization.DataTable();
  data.addColumn('string', 'Estado');
  data.addColumn('number', 'Total');
  data.addRows(rows);

  options = {
    title: 'Ventas x Estado',
    chartArea: { width: '90%', height: '80%' },
    legend: { position: window.innerWidth < 768 ? 'bottom' : 'right' },
    is3D: true
  };

  chart = new google.visualization.PieChart(container);
  drawChartResponsive();
}

function drawChartResponsive() {
  if (!chart) return;

  const container = document.getElementById('chart_ne');
  options.width = container.offsetWidth;
  options.height = container.offsetHeight;

  chart.draw(data, options);
}

let resizeTimeout;
window.addEventListener('resize', function() {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(drawChartResponsive, 200);
});
</script>
