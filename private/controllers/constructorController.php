<?php

/**
 * Constructor de formularios dinámicos.
 *
 * Fase 0 (cimientos): acceso exclusivo del rol Administrador y shell de la
 * pantalla. El CRUD, el versionado, el preview y la carga de BD llegan en las
 * fases siguientes (ver docs/constructor-plan.md).
 */

include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_ADMINISTRADOR));

if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  $mode = isset($_GET['mode']) ? $_GET['mode'] : 'index';

  try {
    switch ($mode) {
      case 'index':
        $nav_crumb    = 'Configuración / Constructor';
        $nav_title    = 'Constructor de formularios';
        $nav_subtitle = 'Crea, versiona y asigna formularios dinámicos a los servicios.';

        include(PUBLIC_DIR . 'general/nheader.php');
        include(PUBLIC_DIR . 'general/nnavbar.php');
        include(HTML_DIR . 'constructor/index.php');
        include(PUBLIC_DIR . 'general/nfooter.php');
        break;

      default:
        http_response_code(404);
        include(HTML_DIR . 'error.html');
        break;
    }
  } catch (Throwable $e) {
    app_log('error', 'Error en el controlador constructor: ' . $e->getMessage(), array(
      'excepcion' => get_class($e),
      'archivo' => $e->getFile(),
      'linea' => $e->getLine(),
      'modo' => $mode,
    ));

    app_responder_error();
  }
}
