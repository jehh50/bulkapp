<link href="public/css/jquery.datatables.min.css" rel="stylesheet">
<div class="container">
  <div class="row" style="margin-top:1.5em">
    <div class="col-sm-12 col-md-12 col-lg-12">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <label>
                <h1>Registros a cargar</h1>
              </label>
            </section>
            <div>
              <?php
              if ($_POST['servicio'] == 1) { ?>
                <table id="example" class="hover" style="width:100%">
                  <thead>
                    <tr>
                      <th style="text-align: center;">#</th>
                      <th style="text-align: center;">Cédula</th>
                      <th style="text-align: center;">Nombre</th>
                      <th style="text-align: center;">Teléfono 1</th>
                      <th style="text-align: center;">Teléfono 2</th>
                      <th style="text-align: center;">Teléfono 3</th>
                      <th style="text-align: center;">Correo</th>
                      <th style="text-align: center;">Fecha de Nacimiento</th>
                      <th style="text-align: center;">Cuenta</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1;
                    foreach ($registros as $reg) { ?>
                      <tr>
                        <td style="text-align: center; width: 4%;"><?= $i; ?></td>
                        <td style="text-align: center; width: 20%;"><?= $reg['identificacion']; ?></td>
                        <td style="text-align: center; width: 12%;"><?= $reg['nombre_legal']; ?></td>
                        <td style="text-align: center; width: 12%;"><?= $reg['telf_hab']; ?></td>
                        <td style="text-align: center; width: 12%;"><?= $reg['telf_ofi']; ?></td>
                        <td style="text-align: center; width: 20%;"><?= $reg['telf_cel']; ?></td>
                        <td style="text-align: center; width: 4%;"><?= $reg['correo']; ?></td>
                        <td style="text-align: center; width: 4%;"><?= $reg['direccion']; ?></td>
                        <td style="text-align: center; width: 12%;"><?= $reg['cuenta']; ?></td>
                      </tr>
                    <?php $i++;
                    } ?>
                  </tbody>
                </table>
              <?php } else { ?>
                <table id="example" class="hover ">
                  <thead>
                    <tr>
                      <th>Cédula</th>
                      <th>ID Cuota</th>
                      <th>Grupo</th>
                      <th>Fecha a Pagar</th>
                      <th>Monto Cuota</th>
                      <th>N° Cuota</th>
                      <th>Fee</th>
                      <th>Por Cobrar</th>
                      <th>Capital Asignado</th>
                      <th>ID Orden</th>
                      <th>Identificación Orden</th>
                      <th>Fecha Creación Orden</th>
                      <th>Email</th>
                      <th>Teléfono</th>
                      <th>Usuario</th>
                      <th>Local</th>
                      <th>Estado Deuda</th>
                      <th>Tramo Inicial</th>
                      <th>Tramo Actual</th>
                      <th>Segmento</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($registros as $reg){ ?>
                      <tr>
                        <td><?= $reg['cedula']; ?></td>
                        <td><?= $reg['id_cuota']; ?></td>
                        <td><?= $reg['nombre_grupo']; ?></td>
                        <td><?= $reg['fecha_pagar']; ?></td>
                        <td><?= $reg['monto_cuota']; ?></td>
                        <td><?= $reg['numero_cuota']; ?></td>
                        <td><?= $reg['fee']; ?></td>
                        <td><?= $reg['plata_por_cobrar']; ?></td>
                        <td><?= $reg['capital_asignado']; ?></td>
                        <td><?= $reg['id_orden']; ?></td>
                        <td><?= $reg['identificacion_orden']; ?></td>
                        <td><?= $reg['fecha_creacion_orden']; ?></td>
                        <td><?= $reg['email']; ?></td>
                        <td><?= $reg['telefono']; ?></td>
                        <td><?= $reg['nombre_usuario']; ?></td>
                        <td><?= $reg['local_origen']; ?></td>
                        <td><?= $reg['estado_deuda']; ?></td>
                        <td><?= $reg['tramo_inicial']; ?></td>
                        <td><?= $reg['tramo_actual']; ?></td>
                        <td><?= $reg['segmento']; ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>

              <?php } ?>

              <div class="row" style="margin-top: 20px;">
                <div class="col-sm-6 text-left">
                  <a href="?view=configuracion&mode=cargaArchivo" class="btn btn-sm btn-primary">Regresar</a>
                </div>
                <div class="col-sm-6 text-right">
                  <input type="submit" class="btn btn-md btn-success" value="Cargar" id="btn-confirm" data-toggle="modal" data-target="#modalConfirm">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalConfirm" tabindex="-1" role="dialog" aria-labelledby="modalConfirm">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" align="center">
      <div class="modal-header">
        <h4>¿Está seguro de cargar los datos?</h4>
      </div>
      <div class="modal-body">
        <form method="POST" action="?view=configuracion&mode=guardarRegistros">
          <input type="hidden" id="data" name="data">
          <input type="hidden" value="<?= $_POST['servicio'] ?>" name="servicio">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Descartar</button>
          <input type="submit" class="btn btn-md btn-success" id="btn-guardar" data-dismiss="Modal" value="Iniciar">
        </form>
      </div>
      <!-- <img src="public/images/refresh.gif" alt="refresh" height="50px" width="50px" /> -->
    </div>
  </div>
</div>
</div>

<!-- Modal confirmación de actualización -->
<div class="modal fade" id="modalWait" tabindex="-1" role="dialog" aria-labelledby="modalWait">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" align="center">
      <div class="modal-body">
        <h4>Por favor espere mientras se actualiza la página</h4>
        <img src="public/images/loader.gif" alt="refresh" height="50px" width="50px" />
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('#example').DataTable({
      "scrollY": "300px",
      "scrollCollapse": true,
      "paging": true,
      "language": {
        "lengthMenu": "Mostrando _MENU_ registros por página",
        "zeroRecords": "Sin coincidencias",
        "info": "Mostrando página _PAGE_ de _PAGES_",
        "infoEmpty": "No hay información",
        "infoFiltered": "(filtered from _MAX_ total records)",
        "sSearch": "Buscar",
        "oPaginate": {
          "sFirst": "Primero",
          "sLast": "Último",
          "sNext": "Siguiente",
          "sPrevious": "Anterior"
        },
        "aLengthMenu": [100, 500, 1000]
      }
    });

    // Manejar el evento de envío del formulario
    $('#guardar-form').on('submit', function() {
      // Mostrar el loader
      $('#loader').show();
    });
  });
</script>

<script>
  const data = <?php echo json_encode($registros); ?>;
  document.getElementById('data').value = JSON.stringify(data);

  $('#btn-guardar').click(function() {
    $('#modalConfirm').modal('hide');
    $('#modalWait').modal('toggle');
    $('#modalWait').modal('show')
  })
</script>

<script src="public/js/jquery.dataTables.min.js"></script>