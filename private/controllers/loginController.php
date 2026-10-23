<?php

include(PUBLIC_DIR . 'general/session.php');

try {
  include(HTML_DIR . 'login/index.php');
} catch (Throwable $e) {
  app_log('error', 'Error en el controlador de login: ' . $e->getMessage(), array(
    'excepcion' => get_class($e),
    'archivo' => $e->getFile(),
    'linea' => $e->getLine(),
  ));

  app_responder_error();
}
