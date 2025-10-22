<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CRM - Iniciar Sesión</title>

  <link href="public/css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="public/images/icon.png" rel="icon" type="image/png"/>
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet">
  <script src="public/js/jquery-3.2.1.min.js"></script>
  <style>
    body {
      background-color: #e9e9e9 !important;
      font-family: 'Poppins', sans-serif;
    }
    .login-wrapper {
      /* min-height: 100vh; se mantiene para centrar el contenido */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      text-align: center;
      padding: 20px 10px; /* Padding para evitar que el contenido toque los bordes en móviles */
    }
    .panel {
      margin-top: 20px;
      /* Sombra sutil para un aspecto más moderno */
      box-shadow: 0 4px 8px rgba(0,0,0,.1);
    }
    .panel-heading {
      background-color: #f7f7f7;
      color: #333;
    }
    .form-group {
      margin-bottom: 15px;
    }
    /* Estilo para ajustar el ancho del logo en responsive */
    .logo-img {
        max-width: 120px; /* Un tamaño un poco más grande para el logo */
        height: auto;
        margin-bottom: 20px; /* Espacio debajo del logo */
    }
  </style>

  <script>
    $(function () {
      $('#session').click(function () {
        // Oculta todas las alertas antes de validar
        $('#alert-campos, #alert-invalido, #alert-inactivo').hide();

        if ($('#username').val() === "" || $('#password').val() === "") {
          // Muestra la alerta de campos incompletos
          $('#alert-campos').show();
        } else {
          // Si los campos están llenos, procede con la petición AJAX
          $.post('?view=session&mode=login',
            {
              user: $('#username').val(),
              pass: $('#password').val()
            }, function (confirm) {
              if (confirm == 2) {
                // Usuario o contraseña inválido
                $('#alert-invalido').show();
              } else if (confirm == 3) {
                // Usuario inactivo
                $('#alert-inactivo').show();
              } else {
                // Inicio de sesión exitoso
                window.location = '?view=formulario&mode=index';
              }
            });
        }
      });
    });
  </script>
</head>

<body>
  <div class="container login-wrapper">
    <img src="public/images/logo.jpg" class="img-responsive center-block logo-img" alt="Logo BULKSALES">

    <div class="col-xs-12 col-sm-8 col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Iniciar sesión</strong></h3>
        </div>
        <div class="panel-body">
          <fieldset>
            <div id="alert-campos" class="alert alert-danger alert-dismissible" role="alert" style="display:none;">
              <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <strong>¡Atención!</strong> Debe completar todos los campos.
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                <input type="text" id="username" class="form-control" placeholder="Usuario" maxlength="10" autofocus>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input type="password" id="password" class="form-control" placeholder="Contraseña" maxlength="15">
              </div>
            </div>

            <div id="alert-invalido" class="alert alert-warning alert-dismissible" role="alert" style="display:none;">
              <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <strong>Error de Acceso:</strong> Usuario o contraseña inválido.
            </div>

            <div id="alert-inactivo" class="alert alert-info alert-dismissible" role="alert" style="display:none;">
              <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <strong>Cuenta Inactiva:</strong> Contacte al administrador.
            </div>

            <button id="session" class="btn btn-success btn-block btn-lg">
              <i class="glyphicon glyphicon-log-in"></i> Iniciar sesión
            </button>
          </fieldset>
        </div>
      </div>
    </div>

    <footer class="col-xs-12" style="margin-top:20px; color: #777;">
      <p>Este sitio fue desarrollado por @jehh_50</p>
      <p><?php echo APP_COPY; ?></p>
    </footer>
  </div>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script>
    // Inicializar el evento para ocultar las alertas
    $('[data-hide="alert"]').on('click', function(){
      $(this).closest('.alert').hide();
    });
  </script>
</body>
</html>