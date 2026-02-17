<script>
$(document).ready(function(){

  $('#fecha_h').change(function(){
    var d = $('#fecha_d').val();
    var h = $('#fecha_h').val();

    if(d && h && h < d){
      alert('La fecha "HASTA" es menor a la fecha "DESDE". Por favor verifique');
      $('#fecha_h').val('');
    }
  });

});

function validateService(){
  var servicio = $('#servicio').val();
  if(!servicio){
    alert('Por favor seleccione un servicio');
    return false;
  }
  return true;
}
</script>

<div class="container-fluid" style="margin-top: 40px;">
  <div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">

      <div class="panel panel-default">
        <div class="panel-body">

          <h2 class="text-center">Resumen de gestión por operador</h2>
          <hr>

          <!-- FORM FILTROS -->
          <form name="resultados"
                method="POST"
                action="?view=reportes&mode=acumulado"
                onsubmit="return validateService();">

            <div class="row">

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Desde</label>
                <input type="date"
                       class="form-control"
                       name="fecha_d"
                       id="fecha_d">
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Hasta</label>
                <input type="date"
                       class="form-control"
                       name="fecha_h"
                       id="fecha_h">
              </div>

              <div class="form-group col-xs-12 col-sm-6 col-md-3">
                <label>Servicio</label>
                <select class="form-control"
                        name="servicio"
                        id="servicio"
                        required>
                  <option value="" disabled selected>Seleccione...</option>
                  <?php foreach ($servicio as $s) { ?>
                    <option value="<?php echo $s['id']; ?>">
                      <?php echo $s['descripcion']; ?>
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

          <hr>

          <!-- TABLA -->
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">

              <thead style="background-color:#1a4190; color:#fff;">
                <tr class="text-center">
                  <th>Nombre y Apellido</th>
                  <th>Gestionados</th>
                  <th>Contactados</th>
                  <th>No contactados</th>
                  <th>Ventas</th>
                  <th>% Contactados</th>
                  <th>% No contactados</th>
                  <th>% Ventas</th>
                </tr>
              </thead>

              <tbody>
                <?php
                $totalg = $totalc = $totaln = $totalv = 0;

                if (!empty($gestion)) {
                  foreach($gestion as $g){

                    $noContactados = $g['gestion'] - $g['efectivos'];
                    $pctContactados = $g['gestion'] ? round(($g['efectivos']/$g['gestion'])*100,2) : 0;
                    $pctNoContactados = $g['gestion'] ? round(($noContactados/$g['gestion'])*100,2) : 0;
                    $pctVentas = $g['efectivos'] ? round(($g['ventas']/$g['efectivos'])*100,2) : 0;

                    echo "<tr class='text-center'>
                            <td>{$g['nombre']}</td>
                            <td>{$g['gestion']}</td>
                            <td>{$g['efectivos']}</td>
                            <td>{$noContactados}</td>
                            <td>{$g['ventas']}</td>
                            <td>{$pctContactados}%</td>
                            <td>{$pctNoContactados}%</td>
                            <td>{$pctVentas}%</td>
                          </tr>";

                    $totalg += $g['gestion'];
                    $totalc += $g['efectivos'];
                    $totalv += $g['ventas'];
                  }

                  $totaln = $totalg - $totalc;
                  $totalPctC = $totalg ? round(($totalc/$totalg)*100,2) : 0;
                  $totalPctN = $totalg ? round(($totaln/$totalg)*100,2) : 0;
                  $totalPctV = $totalc ? round(($totalv/$totalc)*100,2) : 0;
                ?>

                <tr style="background-color:#1a4190; color:#fff;" class="text-center">
                  <td><strong>TOTALES</strong></td>
                  <td><strong><?php echo $totalg; ?></strong></td>
                  <td><strong><?php echo $totalc; ?></strong></td>
                  <td><strong><?php echo $totaln; ?></strong></td>
                  <td><strong><?php echo $totalv; ?></strong></td>
                  <td><strong><?php echo $totalPctC; ?>%</strong></td>
                  <td><strong><?php echo $totalPctN; ?>%</strong></td>
                  <td><strong><?php echo $totalPctV; ?>%</strong></td>
                </tr>

                <?php } ?>
              </tbody>

            </table>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
