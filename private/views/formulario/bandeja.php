<div class="container">
   <div class="row">
      <div class="col-sm-12 col-md-12 col-lg-12">
         <section class="container">
            <header>
               <h1></h1>
            </header>
         </section>
         <div class="panel panel-default">
            <div class="panel-body">
               <div class="form-group">
                  <section>
                     <label>
                        <h1>Bandeja de clientes a llamar luego</h1>
                     </label>
                  </section>
                  <form name="form1" enctype="multipart/form-data" method="POST" action="?view=formulario&mode=bandeja">
                     <div class="form-row col-lg-4">
                        <input type="date" id="fecha" name="fecha" class="form-control" value="$fecha"> 
                     </div>
                     <div class="form-row col-lg-4">
                        <button type="submit" id="btn-buscar" name="btn-buscar" class="btn btn-sm btn-success">Buscar</button>
                     </div>
                  </form>
                  <div class="form-row col-lg-12">
                     <div style="overflow-y: scroll; height: 550px; margin-top: 10px;">
                        <table class="table table-hover table-condensed table-responsive">
                           <thead>
                              <tr>
                                 <th style="text-align: center;">
                                    <h5><strong>Fecha y hora de gestión</strong></h5>
                                 </th>
                                 <th style="text-align: center;">
                                    <h5><strong>Nombre y apellido</strong></h5>
                                 </th>
                                 <th style="text-align: center;">
                                    <h5><strong>Cedula</strong></h5>
                                 </th>
                                 <th style="text-align: center;">
                                    <h5><strong>Teléfono habitación</strong></h5>
                                 </th>
                                 <th style="text-align: center;">
                                    <h5><strong>Teléfono oficina</strong></h5>
                                 </th>
                                 <th style="text-align: center;">
                                    <h5><strong>Teléfono celular</strong></h5>
                                 </th>
                                 <?php if($_SESSION['type_user']==3){echo '<th style="text-align: center;"><h5><strong>Operador</strong></h5></th>';}?>
                              </tr>
                           </thead>
                           <?php foreach($referidos as $r) {?>
                           <tbody>
                              <tr align="center">
                                 <td>
                                    <h5><?php echo $r['fecha'];?></h5>
                                 </td>
                                 <td>
                                    <h5><?php echo $r['nombre_legal'];?></h5>
                                 </td>
                                 <td>
                                    <h5><a href="?view=formulario&mode=index&numero=<?=$r['identificacion'];?>"><?php echo $r['identificacion'];?></a></h5>
                                 </td>
                                 <td>
                                    <h5><a href="?view=formulario&mode=index&numero=<?=$r['telf_hab'];?>"><?php echo $r['telf_hab'];?></a></h5>
                                 </td>
                                 <td>
                                    <h5><a href="?view=formulario&mode=index&numero=<?=$r['telf_ofi'];?>"><?php echo $r['telf_ofi'];?></a></h5>
                                 </td>
                                 <td>
                                    <h5><a href="?view=formulario&mode=index&numero=<?=$r['telf_cel'];?>"><?php echo $r['telf_cel'];?></a></h5>
                                 </td>
                                 <?php if($_SESSION['type_user'] == 3){echo '<td><h5>'.$r['operador'].'</a></h5></td>';}?>
                              </tr>
                           </tbody>
                           <?php }?>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>