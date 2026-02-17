<?php
include_once (MODEL_DIR . 'sessionModel.php');
$conexion = new database();
if (isset($_GET['mode'])) {
  switch ($_GET['mode']) {
    case 'login':
      $user = $conexion->sessionNew($_POST['user'], md5($_POST['pass']));
      if (!empty($user)) {
        if ($user['status_id'] == 1) {
          echo 3;
        } else {
          session_start();
          $_SESSION['id'] = $user['id'];
          $_SESSION['nombre'] = $user['nombre'];
          $_SESSION['apellido'] = $user['apellido'];
          $_SESSION['user'] = $user['user'];
          $_SESSION['usertype_id'] = $user['usertype_id'];
          $_SESSION['servicio_id'] = $user['servicio_id'];
          $_SESSION['status_id'] = $user['status_id'];
          header("location:?view=formulario&mode=index");

          if ($_POST['pass'] == '123456') {
            header("location:?view=usuarios&mode=changepass");
          } else {
            header("location:?view=formulario&mode=index");
          }
        }
      } else {
        echo 2;
      }
      break;
    #_________________________________________________________________________________
    case 'disconect':
      include (PUBLIC_DIR . 'general/session.php');
      session_destroy();
      header('location:index.php');
      break;
    #_________________________________________________________________________________
    default:
      header('location:' . HTML_DIR . 'error.html');
      exit;
  }
} else {
  include (HTML_DIR . 'login/index.php');
}