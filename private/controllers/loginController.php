<?php

include(PUBLIC_DIR . 'general/session.php');

// Única pantalla: cualquier otro modo es una URL inválida.
if (isset($_GET['mode']) && $_GET['mode'] !== 'index') {
  app_responder_no_encontrado();
}

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
