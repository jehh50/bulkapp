<script src="public/js/register.js"></script>
<script type="text/javascript">
function mayus(e) {
  e.value = e.value.toUpperCase();
}
</script>
<style>
.caja{
  margin-top: -120px;
}
</style>
<div class="loginbox">
  <div class="container">
    <div class="caja">
      <div class="col-sm-offset-4 col-sm-4 col-md-offset-4 col-md-4 col-lg-offset-4 col-lg-4">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h1 class="panel-title "><strong>Nuevo usuario</strong></h1>
          </div>
          <div class="panel-body">
            <form role="form" name="f_newuser" id="f_newuser" autocomplete="off">
              <fieldset>
              <div class="form-group">
                <input type="text" id="nombre" class="form-control" placeholder="Juan" name="nombre" maxlength="15" onkeyup="mayus(this);" required autofocus />
              </div>
              <div class="form-group">
                <input type="text" id="apellido" class="form-control" placeholder="Gomez" name="apellido" onkeyup="mayus(this);" maxlength="15" required />
              </div>
              <div class="form-group">
                <input type="text" id="user" class="form-control" placeholder="user" name="user" maxlength="10" required />
              </div>
              <div class="form-group">
                <input type="password" id="password" class="form-control" placeholder="abcd1234." name="password"  maxlength="15" required />
              </div>
              <div class="form-group">
                <select class='selectpicker show-menu-arrow show-tick form-control' name="tipo_usuario" id="tipo_usuario">
                  <option value='' disabled selected style='display:none;'>Tipo de usuario...</option>
                  <option value='1'>Operador</option>
                  <option value='2'>Supervisor</option>
                </select>
              </div>
              <div class="form-group">
                <select class="form-control" name="servicio" id="servicio" required>
                  <?php foreach ($servicio as $s) { ?>
                  <option value='0' disabled selected style='display:none;'>Servicio...</option>
                  <option value='<?php echo $s['id'];?>'><?php echo $s['descripcion'];}?></option>
                </select>
              </div>
              <input id="btn-register" name="btn-register" type="button" class="btn btn-md btn-info btn-block" value="Guardar" />
              </fieldset>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>