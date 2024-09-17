<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BULKSALES</title>
    <link href="public/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="public/images/icon.png" rel="icon" type="image/png"/>
    <script src="public/js/jquery-3.2.1.min.js"></script>
    <script>
    //VALIDACION DEL NOMBRE DE USUARIO
    $(document).ready(function(){
    //VALIDACION DE LA CONTRASEÑA
       $('#session').click(function(){
          if ($('#username').val() === "" || $('#password').val() === "" ) {
            $('#mensaje').show()
          }
          else{
            console.log($('#username').val() + ' ' + $('#password').val());
            $('#mensaje3').hide()
            $.post('?view=session&mode=login',
              {
                user:$('#username').val(),
                pass:$('#password').val()
              },
              function(confirm){
              if(confirm==2){
                $('#mensaje2').show();
                $('#mensaje3').hide();
              }
              else if(confirm==3){ //inactive user
                $('#mensaje2').hide();
                $('#mensaje3').show();
              }
              else{
                window.location='?view=formulario&mode=index';
              }
            })
          }
        })
    })     
    </script>
    <style type="text/css">
      @import url('https://fonts.googleapis.com/css?family=Poppins');
      .loginbox{
        margin-top: 200px;
      }

      .loginboxes{
        margin-top: 50px;
        margin-bottom: -180px;
        margin-left: 100px;
      }
    </style>
  </head>

  <body style="background-color: #f0f0f0 !important;">
    <div class="loginboxes">
      <div class="container">
        <div class="row">
          <div class="col-lg-offset-5">
            <div class="mx-auto">
              <img src="public/images/logo.jpg" height="15%" width="15%">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="loginbox">
     <div class="container">
      <div class="row">
       <div class="col-lg-offset-4 col-lg-4">
        <div class="login-panel panel panel-default">
         <div class="panel-heading">
          <h1 class="panel-title "><strong>Inicio de sesión</strong></h1>
         </div>
         <div class="panel-body">
           <fieldset>
            <div class="form-group">
             <input type='text' class="form-control" name="username" id='username' placeholder='Usuario' maxlength="10" required autofocus>
              <div id="mensaje" class="message" style="display:none; color:red;">Debe completar todos los campos.</div>
            </div>
            <div class="form-group">
             <input type='password' class="form-control" name="password" id='password' placeholder='Contraseña' maxlength="15" required>
             <div id="mensaje2" class="message" style="display:none; color: red;">Usuario o contraseña invalido.</div>
             <div id="mensaje3" class="message" style="display:none; color: red;">Usuario inactivo. Contacte al administrador.</div>
            </div>
            <input id="session" name="session" type="submit" class="btn btn-lg btn-success btn-block" value="Iniciar sesión" />
           </fieldset>
          </form>
         </div>
        </div>
       </div>
      </div>
     </div>
    </div>
    <div class="footer">
      
      <p style="text-align: center;">Este sitio fue desarrollado por @jehh_50</p>
      <p style="text-align: center;"><?=APP_COPY?></p>
    </div>
  </body>
</html>
