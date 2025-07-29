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
              <table id="example" class="hover" style="width:100%">
                <thead>
                  <tr>
                    <th style="text-align: center; width: 4%;">#</th>
                    <th style="text-align: center; width: 20%;">Nombre</th>
                    <th style="text-align: center; width: 12%;">Nacionalidad</th>
                    <th style="text-align: center; width: 12%;">Teléfono</th>
                    <th style="text-align: center; width: 12%;">Cédula</th>
                    <th style="text-align: center; width: 20%;">Tipo de cuenta</th>
                    <th style="text-align: center; width: 4%;">Género</th>
                    <th style="text-align: center; width: 4%;">Edad</th>
                    <th style="text-align: center; width: 12%;">Correo</th>
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

              <!-- Alineación de los botones con Bootstrap 3 -->
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
            <button type="button" class="btn btn-default" data-dismiss="modal">Descartar</button>
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
  $(document).ready(function () {
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
    $('#guardar-form').on('submit', function () {
      // Mostrar el loader
      $('#loader').show();
    });
  });
</script>

<script>
  const data = <?php echo json_encode($registros); ?>;
  document.getElementById('data').value = JSON.stringify(data);

  $('#btn-guardar').click(function(){
      $('#modalConfirm').modal('hide');
      $('#modalWait').modal('toggle');
      $('#modalWait').modal('show')
    })
</script>

<script src="public/js/jquery.dataTables.min.js"></script>