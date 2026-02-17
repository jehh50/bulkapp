<script type="text/javascript" src="public/js/loader.js"></script>

<style>
  .table-scroll {
    max-height: 60vh;
    overflow-y: auto;
  }

  .chart-container {
    width: 100%;
    min-height: 320px;
    height: 55vh;
    margin-top: 20px;
  }

  @media (max-width: 768px) {
    h2 {
      font-size: 20px;
    }
  }
</style>


<div class="container-fluid" style="margin-top:40px;">
  <div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">

      <div class="panel panel-default">
        <div class="panel-body text-center">
          <h2>
            Detalle de ventas para el estado
            <strong>
              <a href="?view=reportes&mode=ventas">
                <?= htmlspecialchars($estado); ?>
              </a>
            </strong>
          </h2>
        </div>
      </div>

    </div>
  </div>
</div>


<div class="container-fluid">
  <div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">

      <div class="panel panel-danger">

        <div class="panel-heading text-center">
          <strong>VENTAS POR CIUDAD</strong>
        </div>

        <div class="panel-body">

          <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">

              <div class="table-responsive table-scroll">
                <table class="table table-striped table-condensed">

                  <thead class="bg-danger text-center">
                    <tr>
                      <th class="text-center">CIUDAD</th>
                      <th class="text-center">VENTAS</th>
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
                          <td><?= htmlspecialchars($ventas['ciudad']); ?></td>
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

          <!-- GRÁFICO -->
          <div class="chart-container">
            <div id="chart_ne" style="width:100%; height:100%;"></div>
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
          return [$r['ciudad'], (int)$r['total']];
        }, $result)
      : []
  ); ?>;

  if (!rows || rows.length === 0) {
    container.innerHTML = "<div class='text-center text-muted'>Sin datos para mostrar</div>";
    return;
  }

  data = new google.visualization.DataTable();
  data.addColumn('string', 'Ciudad');
  data.addColumn('number', 'Total');
  data.addRows(rows);

  options = {
    title: 'Ventas x Ciudad',
    chartArea: { width: '85%', height: '75%' },
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
