<div class="container" style="margin-top:20px;">
  <div class="row">
    <div class="col-sm-6 col-md-6 col-lg-6">
      <div class="panel panel-default">
        <div class="panel-body" >
          <div class="form-group">
            <h5>Número de teléfono</h5>
            <input type="text" class="form-control" placeholder="04242848747" aria-describedby="b_telf"  name="b_telf" id="b_telf" maxlength="11" autofocus/>
            <input type="hidden" name="idUser" id="idUser" value="<?php echo $_SESSION['servicio_id'];?>" />
            <br>
            <input type="button" class="btn btn-medium btn-success" value="Buscar" id="btn-buscar">
          </div>
          <section>
            <h2><strong>Datos de cliente</strong></h2>
          </section>
          <div class="form-group">
            <h5>Nombre del cliente</h5>
            <input type="text" class="form-control" aria-describedby="nombreCliente"  name="nombreCliente" id="nombreCliente" disabled/>
          </div>

          <div class="form-group">
            <h5>Nacionalidad</h5>
            <input type="text" class="form-control" aria-describedby="nacionalidadCliente"  name="nacionalidadCliente" id="nacionalidadCliente" disabled/>
          </div>
          
          <div class="form-group">
            <h5>Cédula</h5>
            <input type="text" class="form-control"  aria-describedby="cedulaCliente"  name="cedulaCliente" id="cedulaCliente" disabled/>
          </div>

          <div class="form-group">
            <h5>Teléfono 1</h5>
            <input type="text" class="form-control" aria-describedby="telfCliente"  name="telfCliente" id="telfCliente" disabled/>
          </div>

          <div class="form-group">
            <h5>Tipo de cuenta</h5>
            <input type="text" class="form-control"  aria-describedby="tipoCuentaBancaria"  name="tipoCuentaBancaria" id="tipoCuentaBancaria" disabled/>
          </div>

          <div class="form-group">
            <h5>Genero</h5>
            <input type="text" class="form-control"  aria-describedby="generoCliente"  name="generoCliente" id="generoCliente" disabled/>
          </div>

          <div class="form-group">
            <h5>Edad<h5>
            <input type="text" class="form-control"  aria-describedby="edadCliente"  name="edadCliente" id="edadCliente" disabled/>
          </div>

          <div class="form-group">
            <h5>Correo electrónico</h5>
            <input type="text" class="form-control"  aria-describedby="correoCliente"  name="correoCliente" id="correoCliente" disabled/>
          </div>
         </div>
      </div>
    </div>

    <!-- FIN DE DATOS DEL CLIENTE -->
    <!-- INICIO DEL FORMULARIO DE VENTAS -->

    <div class="col-offset-lg-6 col-sm-6 col-md-6 col-lg-6" id="formularioCliente" hidden>
      <div class="panel panel-default">
        <div class="panel-body">  <!-- onpaste="alert('No puedes pegar');return false"  -->
          <form name="form1" enctype="multipart/form-data" method="POST" onsubmit="return validaform(this);" action="?view=formulario&mode=registro" autocomplete="off">
          <input type="hidden" name="id_cliente" id="id_cliente">
          <input type="text" style="display:none;" name="hora_actual" id="hora_actuall">


          <input type="hidden" id="usuario" name="usuario" value="<?php echo $_SESSION['id'];?>">
          <input type="hidden" id="servicio" name="servicio" value="<?php echo $_SESSION['servicio_id'];?>">
          <section>
            <h2><span class="form-group-addon"><strong>Datos de contacto</strong></span></h2>
          </section>          
          <div class="form-group" >
            <span class="form-group-addon">Contacto Efectivo</span>
            <select class='selectpicker show-menu-arrow show-tick form-control' name="contacto" id="contacto">
                   <option value='' disabled selected style='display:none;'>Seleccione...</option>
                   <option value='1'>Si</option>
                   <option value='0'>No</option>
            </select>
          </div>

          <div class="form-group" id="d_efectivo">
            <span class="form-group-addon">Motivo de contacto</span>
            <select class="form-control" name="efectivo" id="efectivo" >
            <?php foreach ($efectivo as $e) {?>
            <option value='' disabled selected style='display:none;'>Seleccione...</option>
            <option value='<?php echo $e['id'];?>'><?php echo $e['descripcion'];}?></option>
            </select>
          </div>

          <div class="form-group" id="d_venta";>
            <span class="form-group-addon">Tipo de producto</span>
            <select class="form-control" name="venta" id="venta" required">
            <?php foreach ($producto as $p) { ?>
              <option value='0' disabled selected style='display:none;'>Seleccione...</option>
              <option value='<?php echo $p['id'];?>'><?php echo $p['descripcion'];}?></option>
            </select>
          </div>

          <div class="form-group" id="d_noefectivo">
            <span class="form-group-addon">Motivo de NO contacto</span>
            <select class="form-control" name="noefectivo" id="noefectivo" >
            <?php foreach ($noefectivo as $ne) { ?>
              <option value='' disabled selected style='display:none;'>Seleccione...</option>
              <option value='<?php echo $ne['id'];?>' style='display:enable;'><?php echo $ne['descripcion'];}?></option>
            </select>
          </div>

          <div id="formulario">
            <section>
              <h2><span class="form-group-addon"><strong>Datos del cliente</strong></span></h2>
            </section>

            <div class="form-group">
              <span class="form-group-addon">Nombre del cliente</span>
              <input type="text" class="form-control" placeholder="Carolina" aria-describedby="nombre2" name="nombre2" id="nombre2" oninput="onlyLetters(this);">
            </div>

            <div class="form-group">
              <span class="form-group-addon">Apellido del cliente</span>
              <input type="text" class="form-control" placeholder="Perez" aria-describedby="apellido2" name="apellido2" id="apellido2" oninput="onlyLetters(this);">
            </div>
            <div class="form-group">
              <section>
                <span class="form-group-addon">Sexo</span>
              </section>
              <select class="form-control" name="genero" id="genero">
                  <option value=''>Seleccione...</option>
                  <option value='F'>FEMENINO</option>
                  <option value='M'>MASCULINO</option>
              </select>
            </div>

            <div class="form-group">
              <span class="form-group-addon">Fecha de nacimiento</span>
              <input type="text" class="form-control" aria-describedby="fecha_nac" name="fecha_nac" id="fecha_nac" placeholder="01/01/2024" oninput="formatDate(this)"/>
            </div>

            <div class="form-group">
              <section>
                <span class="form-group-addon">Nacionalidad</span>
              </section>
              <select class="form-control" name="nacionalidad" id="nacionalidad">
                  <option value=''>Seleccione...</option>
                  <option value='1'>VENEZOLANA</option>
                  <option value='2'>EXTRANJERA</option>
                  <option value='3'>JURIDICO</option>
              </select>
            </div>

            <div class="form-group">
              <span class="form-group-addon">Cedula del cliente</span>
              <input type="text" class="form-control" oninput="onlyNumbers(this)" placeholder="12456345" aria-describedby="cedula2" name="cedula2" id="cedula2" maxlength="8" required/>
            </div>

            <div class="form-group">
              <span class="form-group-addon">Teléfono Habitación</span>
              <input type="text" class="form-control telefono" placeholder="(0212)345.67.89" aria-describedby="telf_hab" name="telf_hab" id="telf_hab" onchange="validaTelf(this);" maxlength="15" required/>
            </div>
            
            <div class="form-group">
              <span class="form-group-addon">Teléfono Celular</span>
              <input type="text" class="form-control telefono" placeholder="(0424)234.56.78" aria-describedby="telf_cel" name="telf_cel" id="telf_cel" maxlength="15" required/>
            </div>

            <div class="form-group">
              <span class="form-group-addon">Correo electrónico</span>
              <input type="text" class="form-control" placeholder="usario@dominio.com" aria-describedby="correo2" name="correo2" id="correo2" oninput="upperCase(this);" onchange="validateEmail(this)" required/>
            </div>


            <section>
              <h2><span class="form-group-addon"><strong>Dirección</strong></span></h2>
            </section>

            <div class="form-group">
              <section>
                <span class="form-group-addon">Estado</span>
              </section>
              <select class="form-control" name="estado" id="estado" required>
                <option value='' style='display:enable;'>Seleccione...</option>
                <?php foreach ($estado as $es) { ?>
                <option value='<?php echo $es['id'];?>' style='display:enable;'><?php echo $es['estado'];}?></option>
              </select>
            </div>

            <div class="form-group">
              <section>
              <span class="form-group-addon">Ciudad</span>
              </section>
              <select class="form-control" name="ciudad" id="ciudad" required>
              </select>
            </div>

            <div class="form-group">
              <section>
                <span class="form-group-addon">Municipio</span>
              </section>
              <select class="form-control" name="municipio" id="municipio" required>
              </select>
            </div>       

            <section>
              <h2><span><strong>Datos de bancarios</strong></span></h2>
            </section>
            <div class="form-group">
              <section>
                <span class="form-group-addon">Tipo de cuenta</span>
              </section>
              <select class="form-control" name="tipo_cuenta" id="tipo_cuenta" required>
                  <option value=''>Seleccione...</option>
                  <?php foreach ($cuentas as $cuenta) { ?>
                    <option value='<?php echo $cuenta['id'];?>' style='display:enable;'><?php echo $cuenta['nombre'];}?></option>

              </select>
            </div>

            <div class="form-group">
              <span class="form-group-addon" id="">Observaciones</span>
              <textarea class="form-control" rows=3 maxlength="250" name="observaciones" id="observaciones" oninput="upperCase(this);" ></textarea>
            </div>        
          </div>
          <button type="submit" class="btn btn-success btn-md" onclick="validateForm()">Guardar</button>
          <!-- <a href="#" class="btn btn-success btn-md" onclick="validateForm()">Valida</a> -->
        </div>
      </div>
    </div>
  </div>
</div>

<script src="public/js/formulario.js"></script>
<script src="public/js/fieldsValidation.js"></script>