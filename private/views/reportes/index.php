<script type="text/javascript">
$(document).ready(function(){
  $('#fecha_h').change(function(){
    d = ($('#fecha_d').val());
    h = ($('#fecha_h').val());
    if(h<d){
      alert('La fecha "HASTA" es menor a la fecha "DESDE". Por favor verifique');
    }
  });
})

function validateService(){
  var a = document.getElementById('servicio').value;
  if(a == ''){
    alert('Por favor seleccione un servicio');
    return false;
  }
  else{
    return true;
  }
}
</script>
<div class="container">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-12">
      <section class="container">
        <header><h1></h1></header>
      </section>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <label><h1>Resumen de gestión por operador</h1></label>
            </section>
          <form name="resultados" method="POST" action="?view=reportes&mode=acumulado" onsubmit="return validateService(this)">
            <div class="form-group">              
              <div class="form-group col-lg-3">  
                <label>Desde</label><input type="date" class="form-control" aria-describedby="fecha_d" name="fecha_d" id="fecha_d" autofocus />
              </div>
              <div class="form-group col-lg-3">  
                <label>Hasta</label><input type="date" class="form-control" aria-describedby="fecha_h" name="fecha_h" id="fecha_h" autofocus />
              </div>
              <div class="form-group col-lg-3">
                <label>Servicio</label>
                <select class="form-control" name="servicio" id="servicio" >
                  <?php foreach ($servicio as $s) {?>
                  <option value='' disabled selected style='display:none;'>Seleccione...</option>
                  <option value='<?php echo $s['id'];?>'><?php echo $s['descripcion'];}?></option>
                </select>
              </div>

            </div>
            <div class="form-group col-lg-12">
                <input type="submit" class="btn btn-medium btn-success" value="Buscar" id="btn-buscar">
            </div>
          </form>
            <div class="table-responsive col-lg-12">
              <div style="overflow-y: scroll; height: 350px; margin-top: 10px;">
                <table class="table table-responsive table-hover table-condensed">
                  <thead>
                    <tr  style="background-color: #1a4190; color: #ffffff">
                      <th style="text-align: center;"><h5><strong>Nombre y Apellido</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Clientes gestionados</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Clientes contactados</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Clientes no contactados</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Ventas realizadas</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>% Clientes contactados</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>% Clientes no contactados</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>%Ventas realizadas</strong></h5></th>
                    </tr>
                  </thead>
                  <?php $totalg = $totalc = $totaln = $totalv = 0;
                    if (!empty($gestion)) {
                      foreach($gestion as $gestionados){?>
                        <tbody>
                          <tr align="center">
                            <td><h5><?php echo $gestionados['nombre'];?></h5></td>
                            <td><h5><?php echo $gestionados['gestion'];?></h5></td>
                            <td><h5><?php echo $gestionados['efectivos'];?></h5></td>
                            <td><h5><?php echo ($gestionados['gestion'] - $gestionados['efectivos']);?></h5></td>
                            <td><h5><?php echo $gestionados['ventas'];?></h5></td>
                            <td><h5><?php echo (round($gestionados['efectivos']/$gestionados['gestion'],4) * 100).'%';?></h5></td>
                            <td><h5><?php echo (round(($gestionados['gestion'] - $gestionados['efectivos'])/$gestionados['gestion'],4) * 100).'%';?></h5></td>
                            <td><h5><?php if($gestionados['ventas'] == 0){ echo 0;}else{echo (round($gestionados['ventas']/$gestionados['efectivos'],4) * 100).'%';}?></h5></td>
                          </tr>
                        </tbody>
                   <?php 
                      $totalg=$totalg+$gestionados['gestion'];
                      $totalc=$totalc+$gestionados['efectivos'];
                      $totaln=($totalg-$totalc);
                      $totalv=$totalv+$gestionados['ventas'];
                    }}?>
                      <tr align="center" style="background-color: #1a4190; color: #ffffff">
                        <td><h5><strong>TOTALES</strong></h5></td>
                        <td><h5><strong><?php echo $totalg ;?></strong></h5></td>
                        <td><h5><strong><?php echo $totalc ;?></strong></h5></td>
                        <td><h5><strong><?php echo $totaln ;?></strong></h5></td>
                        <td><h5><strong><?php echo $totalv ;?></strong></h5></td>
                        <td><h5><strong><?php if ($totalg==0) {echo 0;}else{echo (round(($totalc/$totalg),4)*100).'%';}?></strong></h5></td>
                        <td><h5><strong><?php if ($totalg==0) {echo 0;}else{echo (round(($totaln/$totalg),4)*100).'%';}?></strong></h5></td>
                        <td><h5><strong><?php if ($totalc==0) {echo 0;}else{echo (round(($totalv/$totalc),4)*100).'%';}?></strong></h5></td>
                      </tr>
                      </div>
                </table>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>