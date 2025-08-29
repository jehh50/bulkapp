<link href="public/css/jquery.datatables.min.css" rel="stylesheet">

<div class="container" style="margin-top:1em;">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-12">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <h1>Gestión detallada CASHEA</h1>
            </section>
            <form name="resultados" method="POST" action="?view=reportes&mode=gestionDetalladaCashea">
              <div class="form-group">
                <div class="form-group col-lg-3">
                  <label>Desde</label>
                  <input type="date" class="form-control" aria-describedby="fecha_d" name="fecha_d" id="fecha_d" required autofocus />
                </div>
                <div class="form-group col-lg-3">
                  <label>Hasta</label>
                  <input type="date" class="form-control" aria-describedby="fecha_h" name="fecha_h" id="fecha_h" required />
                </div>
                  <input type="hidden" name="servicio" id="servicio" value="2">
              </div>
              <div class="form-group col-lg-12">
                <input type="submit" class="btn btn-medium btn-success" value="Buscar" id="btn-buscar">
                <a href="?view=reportes&mode=downloadCashea&from=<?= date('Y-m-d', strtotime($desde)); ?>&to=<?= date('Y-m-d', strtotime($hasta)); ?>&servicio=2" class="btn btn-medium btn-info" id="btn-download">Descargar</a>
              </div>
            </form>
            <div class="form-group col-lg-12">
            </div>

            <div class="table-responsive col-lg-12" style="max-height: 500px; overflow-y: auto; overflow-x: auto;">
              <?php if ($_POST) { ?>
                <p class="sub-title">Total resultados: <?=count($detallados)?>. Período consultado <?= date('d-m-Y', strtotime($desde)); ?> - <?= date('d-m-Y', strtotime($hasta)) ?></p>
              <?php } ?>
              <table class="table table-hover table-condensed" id="tableResult">
                <thead>
                  <tr class="bg-success">
                    <th style="text-align: center;">#</th>
                    <th style="text-align: center;">Tipo de contacto</th>
                    <th style="text-align: center;">No efectivo</th>
                    <th style="text-align: center;">Efectivo</th>
                    <th style="text-align: center;">Sub Contacto </th>
                    <th style="text-align: center;">Plan de pago</th>
                    <th style="text-align: center;">Fecha de pago</th>
                    <th style="text-align: center;">ID Cuota</th>
                    <th style="text-align: center;">Monto</th>
                    <th style="text-align: center;">Nombre del contacto</th>
                    <th style="text-align: center;">Parentesco</th>
                    <th style="text-align: center;">Observaciones</th>
                    <th style="text-align: center;">Cliente</th>
                    <th style="text-align: center;">Cédula</th>
                    <th style="text-align: center;">Estatus</th>
                    <th style="text-align: center;">Operador</th>
                    <th style="text-align: center;">Fecha gestión</th>
                    <th style="text-align: center;">Hora gestión</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($detallados)) { $i=1;
                    foreach ($detallados as $detallado) { ?>
                      <tr align="center">
                        <td><?= $i?></td>
                        <td><?= $detallado['tipo_contacto']; ?></td>
                        <td><?= $detallado['efectivo']; ?></td>
                        <td><?= $detallado['no_efectivo']; ?></td>
                        <td><?= $detallado['sub_contacto']; ?></td>
                        <td><?= $detallado['planDePago']; ?></td>
                        <td><?= $detallado['fechaPago']; ?></td>
                        <td><?= $detallado['idCuota']; ?></td>
                        <td><?= $detallado['monto']; ?></td>
                        <td><?= $detallado['nombreEncargado']; ?></td>
                        <td><?= $detallado['parentesco']; ?></td>
                        <td><?= $detallado['observaciones']; ?></td>
                        <td><?= $detallado['nombre_usuario']; ?></td>
                        <td><?= $detallado['cedula']; ?></td>
                        <td><?= $detallado['status']; ?></td>
                        <td><?= $detallado['operador']; ?></td>
                        <td><?= $detallado['fecha_gestion']; ?></td>
                        <td><?= $detallado['hora_gestion']; ?></td>
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

<script type="text/javascript">
$(document).ready(function() {
    $('#tableResult').DataTable( {
        "scrollY":        "300px",
        "scrollCollapse": true,
        "paging":         true,
        "language": {
            "lengthMenu": "Mostrando _MENU_ registros por página",
            "zeroRecords": "Sin coincidencias",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay información",
            "infoFiltered": "(filtered from _MAX_ total records)",
            "sSearch":"Buscar",
            oPaginate: {
                sFirst: "Primero",
                sLast: "Último",
                sNext: "Siguiente",
                sPrevious: "Anterior"
            },
            aLengthMenu: [5, 10, 25, 50, 100]
        }
    } );
} );
</script>

<script src="public/js/download.js"></script>
<script src="public/js/jquery.dataTables.min.js"></script>

