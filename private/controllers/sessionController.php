<?php
include_once (MODEL_DIR . 'sessionModel.php');
$conexion = new database();
$ipCliente = app_client_ip();
$agenteCliente = app_user_agent();
if (isset($_GET['mode'])) {
  try {
    switch ($_GET['mode']) {
      case 'login':
        $userName = input_string(isset($_POST['user']) ? $_POST['user'] : '', 50);
        $pass = isset($_POST['pass']) && is_string($_POST['pass']) ? $_POST['pass'] : '';

        if ($userName === '' || $pass === '') {
          app_log('warning', 'Intento de inicio de sesión incompleto', array(
            'event' => 'auth.login_failed',
            'reason' => 'missing_credentials',
            'username' => $userName,
            'ip' => $ipCliente,
            'user_agent' => $agenteCliente,
          ));
          echo 2;
          break;
        }

        $user = $conexion->sessionLogin($userName);
        $hash = ($user && isset($user['password'])) ? $user['password'] : '';

        if (!$user || !security_password_verify($pass, $hash)) {
          $contexto = array(
            'event' => 'auth.login_failed',
            'reason' => 'invalid_credentials',
            'username' => $userName,
            'ip' => $ipCliente,
            'user_agent' => $agenteCliente,
          );
          if ($user) {
            $contexto['user_id'] = (int) $user['id'];
          }
          app_log('warning', 'Inicio de sesión fallido', $contexto);
          echo 2;
          break;
        }

        if ((int) $user['status_id'] === 1) {
          app_log('warning', 'Inicio de sesión rechazado: usuario inactivo', array(
            'event' => 'auth.login_failed',
            'reason' => 'disabled',
            'user_id' => (int) $user['id'],
            'username' => $user['user'],
            'ip' => $ipCliente,
            'user_agent' => $agenteCliente,
          ));
          echo 3;
          break;
        }

        // Migración transparente de hashes legacy (md5) a password_hash().
        // Si falla el rehash (por ejemplo, una columna password demasiado corta),
        // el login debe continuar con el hash anterior en vez de devolver 500.
        if (security_password_needs_rehash($hash)) {
          try {
            $conexion->updatePass($user['id'], security_hash_password($pass));
            app_log('info', 'Hash de contraseña actualizado', array(
              'event' => 'auth.password_rehash',
              'user_id' => (int) $user['id'],
              'ip' => $ipCliente,
            ));
          } catch (Throwable $e) {
            app_log('warning', 'No se pudo migrar el hash de contraseña', array(
              'event' => 'auth.password_rehash_failed',
              'user_id' => (int) $user['id'],
              'excepcion' => get_class($e),
              'error' => $e->getMessage(),
              'ip' => $ipCliente,
            ));
          }
        }

        $_SESSION = [];
        security_regenerate_session_id();

        $_SESSION['id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['apellido'] = $user['apellido'];
        $_SESSION['user'] = $user['user'];
        $_SESSION['usertype_id'] = $user['usertype_id'];
        $_SESSION['servicio_id'] = $user['servicio_id'];
        $_SESSION['status_id'] = $user['status_id'];
        $_SESSION['last_activity'] = time();
        // Campañas asignadas (N:M): alimentan el selector de campaña de la navbar.
        campanas_sesion_refrescar((int) $user['id']);

        app_log('info', 'Inicio de sesión exitoso', array(
          'event' => 'auth.login_success',
          'user_id' => (int) $user['id'],
          'username' => $user['user'],
          'usertype_id' => (int) $user['usertype_id'],
          'servicio_id' => (int) $user['servicio_id'],
          'ip' => $ipCliente,
          'user_agent' => $agenteCliente,
        ));

        header('location:?view=formulario&mode=index');
        exit;
        break;

      #_________________________________________________________________________________
      case 'campana':
        // Cambia la campaña activa de la sesión entre las asignadas al usuario.
        include (PUBLIC_DIR . 'general/session.php');
        if (!security_usuario_autenticado() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
          header('location:index.php');
          exit;
        }
        $userIdSesion = (int) $_SESSION['id'];
        $destino = input_int(isset($_POST['servicio_id']) ? $_POST['servicio_id'] : null, 1);
        $permitidas = array_map('intval', array_column(campanas_sesion_refrescar($userIdSesion), 'id'));

        if ($destino === null || !in_array((int) $destino, $permitidas, true)) {
          app_log('warning', 'Cambio de campaña rechazado', array(
            'event' => 'auth.campana_rechazada',
            'user_id' => $userIdSesion,
            'servicio_id' => $destino,
            'ip' => $ipCliente,
          ));
          header('location:?view=formulario&mode=index');
          exit;
        }

        $anterior = isset($_SESSION['servicio_id']) ? (int) $_SESSION['servicio_id'] : null;
        $_SESSION['servicio_id'] = (int) $destino;
        app_log('info', 'Cambio de campaña', array(
          'event' => 'auth.campana_cambiada',
          'user_id' => $userIdSesion,
          'desde' => $anterior,
          'hacia' => (int) $destino,
          'ip' => $ipCliente,
        ));
        header('location:?view=formulario&mode=index');
        exit;
        break;

      #_________________________________________________________________________________
      case 'disconect':
        $usuarioSesion = isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;
        $nombreSesion = isset($_SESSION['user']) ? (string) $_SESSION['user'] : '';
        include (PUBLIC_DIR . 'general/session.php');
        security_destroy_session();
        app_log('info', 'Cierre de sesión', array(
          'event' => 'auth.logout',
          'user_id' => $usuarioSesion,
          'username' => $nombreSesion,
          'ip' => $ipCliente,
          'user_agent' => $agenteCliente,
        ));
        header('location:index.php');
        exit;
        break;
      #_________________________________________________________________________________
      default:
        app_responder_no_encontrado();
    }
  } catch (Throwable $e) {
    app_log('error', 'Error en el controlador session: ' . $e->getMessage(), array(
      'event' => 'auth.error',
      'excepcion' => get_class($e),
      'archivo' => $e->getFile(),
      'linea' => $e->getLine(),
      'modo' => isset($_GET['mode']) ? $_GET['mode'] : '',
      'ip' => $ipCliente,
      'user_agent' => $agenteCliente,
    ));

    app_responder_error();
  }
} else {
  include (HTML_DIR . 'login/index.php');
}
