<link href="public/css/jquery.datatables.min.css" rel="stylesheet">

<div class="container" style="margin-top:40px;">
  <div class="row">
    <div class="col-xs-12">
      <div class="panel panel-default">
        <!-- HEADER -->
        <div class="panel-heading clearfix">
          <h3 class="panel-title pull-left">
            <strong>Lista de productos activos</strong>
          </h3>
          <a href="?view=configuracion&mode=agregarProducto"
            class="btn btn-info btn-sm pull-right">
            <span class="glyphicon glyphicon-plus"></span> Agregar
          </a>
        </div>

        <div class="panel-body">

          <!-- RESPONSIVE WRAPPER -->
          <div class="table-responsive">
            <table id="products"
              class="table table-bordered table-striped table-hover">

              <thead>
                <tr class="text-center">
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Código</th>
                  <th>Costo</th>
                  <th>Fecha activación</th>
                  <th>Plan</th>
                  <th>Servicio</th>
                  <th>Editar</th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($listarProducto as $u) { ?>
                  <tr>
                    <td class="text-center"><?= $u['id']; ?></td>
                    <td><?= $u['descripcion']; ?></td>
                    <td class="text-center"><?= $u['codigo_producto']; ?></td>
                    <td class="text-right"><?= $u['costo_prod']; ?></td>
                    <td class="text-center"><?= $u['fecha']; ?></td>
                    <td class="text-center"><?= $u['codplan']; ?></td>
                    <td class="text-center"><?= $u['servicio']; ?></td>
                    <td class="text-center">
                      <a href="?view=configuracion&mode=editarProducto&id=<?= $u['id']; ?>"
                        class="btn btn-success btn-xs">
                        <span class="glyphicon glyphicon-pencil"></span>
                      </a>
                    </td>
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

<script type="text/javascript">
  $(document).ready(function() {
    $('#products').DataTable({
      "scrollY": "auto",
      "scrollCollapse": true,
      "paging": true,
      "language": {
        "lengthMenu": "Mostrando _MENU_ registros por página",
        "zeroRecords": "Sin coincidencias",
        "info": "Mostrando página _PAGE_ de _PAGES_",
        "infoEmpty": "No hay información",
        "infoFiltered": "(filtered from _MAX_ total records)",
        "sSearch": "Buscar",
        oPaginate: {
          sFirst: "Primero",
          sLast: "Último",
          sNext: "Siguiente",
          sPrevious: "Anterior"
        },
        aLengthMenu: [5, 10, 25, 50, 100]
      }
    });
  });
</script>
<script src="public/js/jquery.dataTables.min.js"></script>
