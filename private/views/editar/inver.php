<script src="public/js/editinv.js"></script>
<script type="text/javascript">
  function mayus(e) {
    e.value = e.value.toUpperCase();
  }
  function soloNumeros(e){
    key=e.keyCode || e.which;
    teclado=String.fromCharCode(key).toLowerCase();
    numero="0123456789";
    if(numero.indexOf(teclado)==-1){
      alert('Solo se permiten números.')
      return false;
    }
  }

  function soloLetras(e) {
    key=e.keyCode || e.which;
    teclado=String.fromCharCode(key).toLowerCase();
    letras="qwertyuiopasdfghjklñzxcvbnm";
    especiales="32";
    if(letras.indexOf(teclado)==-1 && especiales!=key){
        alert("Solo se permite letras.")
        return false;
    }
  }

  function validateMail(e){
    valueForm=e.value;
    var patron = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/)
    if(valueForm.search(patron)!=0){
      alert('La dirección de correo es invalida, el formato debe coincidir con DIRECCION@DOMINIO.COM intente de nuevo.');
      window.location.hash = "#correo";
    }
  }
</script>

<?php
if (isset($_GET['mensaje'])=='exito') {
  echo '  <script type="text/javascript">alert("REGISTRO EXITOSO"); $(location).attr("href","?view=editar&mode=index");</script>';
}
?>
<div class="row">
  <div class="col-sm-10 col-md-10 col-lg-offset-1 col-lg-10">
    <section class="container">
      <header><h1></h1></header>
    </section>
    <div class="panel panel-default" >
      <div class="panel-body">
        <div class="form-group">
          <section>
            <label><h1>Edición de resultados</h1></label>
          </section>
          <input type="hidden" id="id_resultado">
          <input type="hidden" id="cod_servicio">
          <input type="hidden" id="gestion">
        </div>            
        <div class="form-group">
            <h5>Número de teléfono</h5>
              <input type="text" class="form-control" placeholder="17862546869" aria-describedby="b-telf"  name="b-telf" id="b-telf" maxlength="11" autofocus />
        </div>
        
        <div class="form-group" id="resultado">
        <hr>
          <div class="form-group" id="d_efectivo">
            <span class="form-group-addon">Tipo de contacto efectivo</span>
            <select class="form-control" name="contacto" id="contacto" >
              <?php foreach ($efectivo as $e) {?>
              <option value="" disabled selected style="display:none;">Seleccione...</option>
              <option value="<?php echo $e['id_efectivo'];?>"><?php echo $e['descripcion'];}?></option>
            </select>
          </div>

        <hr>
        <div id="formulario">
          <div class="col-sm-6 col-md-6 col-lg-6">
            <section>
              <h4><span class="form-group-addon"><strong>Datos de contacto</strong></span></h4>
            </section>
            <div class="form-group">
              <span class="form-group-addon">Nombre del cliente</span>
              <input type="text" class="form-control" onkeypress="return sololetras(event)" aria-describedby="nombre" name="nombre" id="nombre" onkeyup="mayus(this);" />
            </div>
            <div class="form-group">
              <span class="form-group-addon">Apellido del cliente</span>
              <input type="text" class="form-control" onkeypress="return sololetras(event)" aria-describedby="apellido" name="apellido" id="apellido" onkeyup="mayus(this);" />
            </div>
            <div class="form-group">
              <span class="form-group-addon">Edad</span>
              <input type="number" class="form-control" aria-describedby="edad" id="edad" min="18" max="100" name="edad" id="edad" onchange="return validaEdad(this)" />
            </div>
            <div class="form-group">
              <span class="form-group-addon">Teléfono Habitación</span>
              <input type="text" class="form-control" placeholder="7862546869" aria-describedby="telf_hab" name="telf_hab" id="telf_hab" onkeypress="return soloNumeros(event)" maxlength="11" />
            </div>
            <div class="form-group">
              <span class="form-group-addon">Teléfono Celular</span>
              <input type="text" class="form-control" placeholder="7862546869" aria-describedby="telf_cel" name="telf_cel" id="telf_cel"  onkeypress="return soloNumeros(event)" maxlength="11" />
            </div>
            <div class="form-group">
              <span class="form-group-addon">Correo electrónico</span>
              <input type="text" class="form-control" aria-describedby="correo" name="correo" id="correo" onkeyup="mayus(this);" onchange="validateMail(this)" />
            </div>
            <section>
              <h4><span class="form-group-addon"><strong>Datos adicionales</strong></span></h4>
            </section>
            <div class="form-group">
              <span class="form-group-addon">¿Ud. actualmente posee cobertura médica?</span>
              <select class="form-control" id="cobertura" name="cobertura">
                <option value="">Seleccione...</option>
                <option value="SI">SI</option>
                <option value="NO">NO</option>
                <option value="NO CONTESTA">NO CONTESTA</option>
              </select>
            </div>
            <div class="form-group">
              <span class="form-group-addon">¿Con cuál compañia posee cobertura médica?</span>
              <select class="form-control" name="compania" id="compania">
              <?php foreach ($seguro as $s) { ?>
                <option value="" disabled selected style="display:none;">Seleccione...</option>
                <option value="<?php echo $s['id_seguro'];?>" style="display:enable;"><?php echo $s['descripcion'];}?></option>
              </select>
            </div>
            <div class="form-group">
              <span class="form-group-addon">¿Esta Ud. trabajando actualmente?</span>
              <select class="form-control" id="trabaja" name="trabaja">
                <option value="" disabled selected style="display:none;">Seleccione...</option>
                <option value="SI">SI</option>
                <option value="NO">NO</option>
                <option value="NO CONTESTA">NO CONTESTA</option>
              </select>
            </div>
            <div class="form-group">
              <span class="form-group-addon">¿Cuenta Ud. con cuenta de banco?</span>
              <select class="form-control" id="banco" name="banco">
                <option value="" disabled selected style="display:none;">Seleccione...</option>
                <option value="SI">SI</option>
                <option value="NO">NO</option>
                <option value="NO CONTESTA">NO CONTESTA</option>
              </select>
            </div>
            <div class="form-group">
              <span class="form-group-addon">¿Es Ud. ciudadano, residente o esta en proceso?</span>
              <select class="form-control" id="ciudadano" name="ciudadano">
                <option value="" disabled selected style="display:none;">Seleccione...</option>
                <option value="CIUDADANO">CIUDADANO</option>
                <option value="RESIDENTE">RESIDENTE</option>
                <option value="EN PROCESO">EN PROCESO</option>
                <option value="NO CONTESTA">NO CONTESTA</option>
              </select>
            </div>
          </div>
          <div class="col-sm-6 col-md-6 col-lg-6">
              <section>
                <h4><span class="form-group-addon"><strong>Dirección</strong></span></h4>
              </section>
              <div class="form-group">
                <section>
                  <span class="form-group-addon">Estado</span>
                </section>
                <select class="form-control" name="estado" id="estado">
                  <option value="" style="display:enable;">Seleccione...</option>
                  <?php foreach ($estado as $es) { ?>
                  <option value="<?php echo $es['id_estado'];?>" style="display:enable;"><?php echo $es['estado'];}?></option>
                </select>
              </div>
              <div class="form-group">
                <section>
                  <span class="form-group-addon">Ciudad</span>
                </section>
                <select class="form-control" name="ciudad" id="ciudad">
                  <option value="" style="display:enable;">Seleccione...</option>
                  <?php foreach ($ciudad as $c) { ?>
                  <option value="<?php echo $c['id_ciudad'];?>" style="display:enable;"><?php echo $c['ciudad'];}?></option>
                </select>
              </div>

              <div class="form-group">
                <section>
                  <span class="form-group-addon">Codigo Postal</span>
                </section>
                <input type="text" class="form-control" id="zipcode" name="zipcode" placeholder="33132">
              </div>
          </div>
          <div class="col-sm-6 col-md-6 col-lg-6">
              <section>
                <h4><span class="form-group-addon"><strong>Agendamiento de cita o llamada</strong></span></h4>
              </section>
              <div class="form-group">
                <section>
                  <span class="form-group-addon">Fecha</span>
                </section>
                <input type="date" class="form-control" id="fecha_cita" name="fecha_cita">
              </div>
              <div class="form-group">
                <section>
                  <span class="form-group-addon">Hora</span>
                </section>
                <input type="time" class="form-control" id="hora_cita" name="hora_cita">
              </div>
          </div>
        </div>
      </div>
      </div>
        <div class="container form-group">
            <button class="btn btn-md btn-primary" id="btn-buscar"><span class="glyphicon glyphicon-search"></span> Buscar</button>
            <button class="btn btn-md btn-success" id="btn-actualizar"><span class="glyphicon glyphicon-floppy-disk"></span> Actualizar</button>
            <button class="btn btn-md btn-success" id="btn-actualizar_"><span class="glyphicon glyphicon-floppy-disk"></span> Actualizar</button>
            <button class="btn btn-md btn-warning" id="btn-limpiar"><span class="glyphicon glyphicon-refresh"></span> Limpiar</button>
            <button class="btn btn-md btn-danger" id="btn-eliminar" data-toggle="modal" data-target="#modalRechazo"><span class="glyphicon glyphicon-remove"></span> Eliminar</button>
        </div>
    </div>
  </div>
</div>

<!-- MODALES -->
<div class="modal fade" id="modalRechazo" tabindex="-1" role="dialog" aria-labelledby="modalRechazo">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalRechazo">Seleccione el motivo por el cual esta eliminando la venta</h4>
      </div>
      <div class="modal-body">     
        <select class="form-control" id="eliminar_venta" required>
          <option value="0" disabled selected style="display:none;">Seleccione...</option>
          <option value="error">Venta con error</option>
          <option value="forzada">Venta con mala gestión o forzadas</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Descartar</button>
        <button type="button" class="btn btn-primary" id="btn-guardar"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal confirmación de venta eliminada-->
<div class="modal fade" id="modalConfirm" tabindex="-1" role="dialog" aria-labelledby="modalConfirm">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" align="center">
      <div class="modal-header">
        <h3>Rechazo de venta procesado con exito</h3>
      </div>
      <div class="modal-body">     
       <h4>Por favor espere mientras se actualiza la página</h4>
        <img src="public/images/refresh.gif" alt="refresh" height="50px" width="50px"/>
      </div>
    </div>
  </div>
</div>

<!-- Modal confirmación de actualización -->
<div class="modal fade" id="modalActualiza" tabindex="-1" role="dialog" aria-labelledby="modalConfirm">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" align="center">
      <div class="modal-header">
        <h3>Registro actualizado con exito</h3>
      </div>
      <div class="modal-body">     
        <h4>Por favor espere mientras se actualiza la página</h4>
        <img src="public/images/refresh.gif" alt="refresh" height="50px" width="50px"/>
      </div>
    </div>
  </div>
</div>