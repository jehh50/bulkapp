<link href="public/css/jquery.datatables.min.css" rel="stylesheet">
<script src="public/js/register.js"></script>

<div class="container">
  <div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
      <section class="container">
        <header><h1></h1></header>
      </section>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <label><h1>Mantenimiento de usuarios</h1></label>
            </section>
            <a href="?view=usuarios&mode=new" class="btn btn-info btn-md btn-md" style="margin-bottom: 10px;">Nuevo</a>
              <div>
                <table id="example" class="hover" style="width:100%">
                  <thead>
                    <tr>
                      <th style="text-align: center;"><h5><strong>#</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Usuario</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Nombre</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Apellido</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Rol</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Servicio</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Estatus</strong></h5></th>
                      <th style="text-align: center;"><h5><strong>Editar</strong></h5></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                  <?php foreach($listUser as $u) {?>
                     <td><h5><?php echo $u['id'];?></h5></td>
                     <td><h5><?php echo $u['user'];?></h5></td>
                     <td><h5><?php echo $u['nombre'];?></h5></td>
                     <td><h5><?php echo $u['apellido'];?></h5></td>
                     <td><h5><?php echo $u['name'];?></h5></td>
                     <td><h5><?php echo $u['descripcion'];?></h5></td>
                     <td><h5><?php echo $u['status'];?></h5></td>
                     <td><a href="?view=usuarios&mode=edituser&id=<?php echo $u['id'];?>" name="edituser" id="edituser" class="btn btn-small btn-success"><span class="glyphicon glyphicon-pencil"></span></a></td>
                    </tr>
                   <?php }?>
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
    $('#example').DataTable( {
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
<script src="public/js/jquery.dataTables.min.js"></script>
