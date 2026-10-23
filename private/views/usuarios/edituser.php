<script src="<?php echo asset('public/js/register.js'); ?>"></script>
<div class="container">
  <div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
      <section class="container">
        <header><h1></h1></header>
      </section>
      <div class="card">
        <div class="card-body">
          <div class="mb-3">
            <section>
              <label><h1>Mantenimiento de usuarios</h1></label>
            </section>
            <section>
              <h2><span class="input-group-text"><strong>Datos del usuario </strong><v style="color:#5bc0de; font-weight: 600;"><?=$user?></v></span></h2>
            </section>
            <div class="mb-3 col-lg-6">
              <span class="input-group-text">Nombre</span>
              <input type="text" class="form-control" aria-describedby="nombre" name="nombre" id="nombre" value="<?=$nombre?>"/>
            </div>
            <div class="mb-3 col-lg-6">
              <span class="input-group-text">Apellido</span>
              <input type="text" class="form-control" aria-describedby="apellido"  name="apellido" id="apellido" value="<?=$apellido?>" />
            </div>
            <div class="mb-3 col-lg-6">
              <span class="input-group-text">Rol activo</span>
              <select class='selectpicker show-menu-arrow show-tick form-control' name="rol" id="rol">
                   <option value='1' <?=$e?>>OPERADOR</option>
                   <option value='2' <?=$f?>>SUPERVISOR</option>
              </select>
            </div>
            <div class="mb-3 col-lg-6">
              <span class="input-group-text">Estatus</span>
              <select class='selectpicker show-menu-arrow show-tick form-control' name="status" id="status">
                   <option value='<?=$status_id;?>' selected style='display:none;'><?=$status;?></option>
                   <option value='2'>ACTIVO</option>
                   <option value='1'>INACTIVO</option>
              </select>
            </div>
            <div class="mb-3 col-lg-6">
              <span class="input-group-text">Servicio</span>
              <select class='selectpicker show-menu-arrow show-tick form-control' name="servicio" id="servicio">
                  <option value='<?php echo $codserv; ?>' selected style='display:none;'><?php echo $cliente;?></option>
                  <?php foreach ($servicio as $s) { ?>
                  <option value='<?php echo $s['id'];?>'><?php echo $s['descripcion'];}?></option>
              </select>
            </div>
            <input type="hidden" id="userid" value="<?=$id_user?>">
            
            <div class="mb-3 offset-lg-0 col-lg-7">
              <input type="button" class="btn btn-large btn-success" aria-describedby="update" id="update" value="Actualizar" />
              <input type="button" class="btn btn-large btn-info" aria-describedby="password" id="password" value="Reiniciar Contrase単a" />
              <a href="?view=usuarios&mode=index" class="btn btn-medium btn-success">Volver</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>