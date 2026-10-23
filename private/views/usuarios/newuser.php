<script src="<?php echo asset('public/js/register.js'); ?>"></script>

<script>
function mayus(e){
  e.value = e.value.toUpperCase();
}
</script>

<style>
body {
  background-color: #f5f5f5;
}

.login-wrapper {
  margin-top: 40px;
  margin-bottom: 40px;
}

@media (min-width: 768px) {
  .login-wrapper {
    margin-top: 80px;
  }
}
</style>

<div class="container login-wrapper">
  <div class="row">
    <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-4 offset-lg-4">

      <div class="card">
        <div class="card-header text-center">
          <h3 class="card-title"><strong>Nuevo usuario</strong></h3>
        </div>

        <div class="card-body">
          <form name="f_newuser" id="f_newuser" autocomplete="off">

            <div class="mb-3">
              <label>Nombre</label>
              <input type="text"
                     id="nombre"
                     class="form-control"
                     placeholder="Juan"
                     name="nombre"
                     maxlength="15"
                     onkeyup="mayus(this);"
                     required>
            </div>

            <div class="mb-3">
              <label>Apellido</label>
              <input type="text"
                     id="apellido"
                     class="form-control"
                     placeholder="Gomez"
                     name="apellido"
                     maxlength="15"
                     onkeyup="mayus(this);"
                     required>
            </div>

            <div class="mb-3">
              <label>Usuario</label>
              <input type="text"
                     id="user"
                     class="form-control"
                     placeholder="user"
                     name="user"
                     maxlength="10"
                     required>
            </div>

            <div class="mb-3">
              <label>Contraseña</label>
              <input type="password"
                     id="password"
                     class="form-control"
                     placeholder="abcd1234."
                     name="password"
                     maxlength="15"
                     minlength="8"
                     required>
            </div>

            <div class="mb-3">
              <label>Tipo de usuario</label>
              <select class="form-select"
                      name="tipo_usuario"
                      id="tipo_usuario"
                      required>
                <option value="" disabled selected>Seleccione...</option>
                <option value="1">Operador</option>
                <option value="2">Supervisor</option>
              </select>
            </div>

            <div class="mb-3">
              <label>Servicio</label>
              <select class="form-select"
                      name="servicio"
                      id="servicio"
                      required>
                <option value="" disabled selected>Servicio...</option>
                <?php foreach ($servicio as $s) { ?>
                  <option value="<?php echo $s['id']; ?>">
                    <?php echo $s['descripcion']; ?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <a href="?view=usuarios&mode=index" id="btn-regresar" class="btn btn-outline-secondary btn-md"> Regresar</a>
            <button type="button" id="btn-register" class="btn btn-info btn-md"> Guardar </button>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>
