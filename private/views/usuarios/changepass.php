<script type="text/javascript">
  function mayus(e) {
    e.value = e.value.toUpperCase();
  }
  
  function mostrarContrasena1(){
    var tipo = document.getElementById("password");
    if(tipo.type == "password"){
      tipo.type = "text";
    }else{
      tipo.type = "password";
    }
  }

  function mostrarContrasena2(){
    var tipo = document.getElementById("password_");
    if(tipo.type == "password"){
      tipo.type = "text";
    }else{
      tipo.type = "password";
    }
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
            <h1 class="panel-title "><strong>Cambio de contraseña</strong></h1>
          </div>
          <div class="panel-body">
            <form id="actualizaContrasena" enctype="multipart/form-data" method="POST" action="?view=usuarios&mode=actualizaPassword">
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="button" onclick="mostrarContrasena1()">
                        <span class="glyphicon glyphicon-eye-open"></span>
                      </button>
                    </span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña">
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="button" onclick="mostrarContrasena2()">
                        <span class="glyphicon glyphicon-eye-open"></span>
                      </button>
                    </span>
                    <input type="password" id="password_" name="password_" class="form-control" placeholder="Repita la contraseña">
                  </div>
                </div>
                <div id="mensaje" style="color: red;" hidden>
                  No coinciden las contraseñas
                </div>

              <input type="hidden" name="user" id="user" value="<?=$_SESSION['id']?>">
              <button id="btn-actualiza" name="btn-actualiza" type="submit" class="btn btn-info btn-block">Guardar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
  $("#password_").keyup(function(){
    if($('#password').val() == $('#password_').val()){
      $('#mensaje').hide();
      if ($('#password_').val() == 123456){
        alert('La contraseña no puede ser igual a 123456.');
      }
    }else{
      $('#mensaje').show();
    }
  });
})  
</script>