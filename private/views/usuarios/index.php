<link href="public/css/jquery.datatables.min.css" rel="stylesheet">
<script src="public/js/register.js"></script>

<div class="container" style="margin-top:40px;">
  <div class="row">
    <div class="col-xs-12">
      <div class="panel panel-default">
        <!-- HEADER -->
        <div class="panel-heading clearfix">
          <h3 class="panel-title pull-left">
            <strong>Lista de productos activos</strong>
          </h3>
          <a href="?view=usuarios&mode=new"
            class="btn btn-info btn-sm pull-right">
            <span class="glyphicon glyphicon-plus"></span> Agregar
          </a>
        </div>

        <div class="panel-body">

          <!-- Tabla responsive -->
          <div class="table-responsive">
            <table id="users" class="table table-striped table-bordered table-hover nowrap">
              <thead>
                <tr class="text-center">
                  <th>#</th>
                  <th>Usuario</th>
                  <th>Nombre</th>
                  <th>Apellido</th>
                  <th>Rol</th>
                  <th>Servicio</th>
                  <th>Estatus</th>
                  <th>Editar</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($listUser as $u) { ?>
                  <tr>
                    <td><?php echo $u['id'];?></td>
                    <td><?php echo $u['user'];?></td>
                    <td><?php echo $u['nombre'];?></td>
                    <td><?php echo $u['apellido'];?></td>
                    <td><?php echo $u['name'];?></td>
                    <td><?php echo $u['descripcion'];?></td>
                    <td>
                      <span class="label label-<?php echo ($u['status']=='Activo')?'success':'danger'; ?>">
                        <?php echo $u['status'];?>
                      </span>
                    </td>
                    <td class="text-center">
                      <a href="?view=usuarios&mode=edituser&id=<?php echo $u['id'];?>"
                         class="btn btn-xs btn-warning">
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
    $('#users').DataTable( {
        "scrollY":        "auto",
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
<script src="public/js/jquery.dataTables.min.js"></script>
