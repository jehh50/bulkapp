<?php

/**
 * Manejo global de errores y excepciones.
 *
 * - Los avisos/errores de PHP se registran en el log y no se muestran al cliente.
 * - Las excepciones no controladas se registran con su traza y se responde con un
 *   mensaje genérico (nunca el detalle interno de mysqli o del motor).
 */
function app_registrar_handlers()
{
  set_error_handler('app_error_handler');
  set_exception_handler('app_exception_handler');
}

/**
 * Registra errores y avisos de PHP. No convierte nada en excepción: solo loguea.
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 * @return bool
 */
function app_error_handler($errno, $errstr, $errfile = '', $errline = 0)
{
  if (!(error_reporting() & $errno)) {
    return false;
  }

  $niveles = array(
    E_ERROR => 'error',
    E_WARNING => 'warning',
    E_NOTICE => 'notice',
    E_USER_ERROR => 'error',
    E_USER_WARNING => 'warning',
    E_USER_NOTICE => 'notice',
    E_DEPRECATED => 'notice',
    E_USER_DEPRECATED => 'notice',
    E_STRICT => 'notice',
  );

  $nivel = isset($niveles[$errno]) ? $niveles[$errno] : 'warning';

  app_log($nivel, $errstr, array(
    'archivo' => $errfile,
    'linea' => $errline,
    'errno' => $errno,
  ));

  return true;
}

/**
 * Maneja las excepciones no controladas.
 *
 * @param Throwable $e
 * @return void
 */
function app_exception_handler($e)
{
  app_log('critical', 'Excepción no controlada: ' . $e->getMessage(), array(
    'excepcion' => get_class($e),
    'archivo' => $e->getFile(),
    'linea' => $e->getLine(),
    'traza' => $e->getTraceAsString(),
  ));

  app_responder_error();
}

/**
 * Responde al cliente con un mensaje genérico según el tipo de petición.
 *
 * @return void
 */
function app_responder_error()
{
  if (!headers_sent()) {
    http_response_code(500);
  }

  if (app_es_ajax()) {
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode(array(
      'response' => 'false',
      'message' => app_mensaje_error(),
    ));
    exit;
  }

  if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
  }

  $mensaje = htmlspecialchars(app_mensaje_error(), ENT_QUOTES, 'UTF-8');
  echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error</title></head>'
    . '<body style="font-family:system-ui,sans-serif;padding:40px;text-align:center">'
    . '<h1>Se produjo un error</h1>'
    . '<p>' . $mensaje . '</p>'
    . '<p><a href="index.php">Volver al inicio</a></p>'
    . '</body></html>';
  exit;
}

/**
 * Mensaje genérico que se muestra al usuario. El detalle técnico queda en el log.
 *
 * @return string
 */
function app_mensaje_error()
{
  $mensaje = env('ERROR_MESSAGE', 'Ocurrió un error inesperado. Intente nuevamente o contacte al administrador.');

  return (string) $mensaje;
}

/**
 * Detecta si la petición espera una respuesta JSON.
 *
 * @return bool
 */
function app_es_ajax()
{
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    return true;
  }

  if (isset($_GET['mode']) && strpos((string) $_GET['mode'], '_') !== false) {
    return true;
  }

  return false;
}

/**
 * Envuelve el cuerpo de un controlador: registra el error y responde de forma
 * genérica en lugar de propagar la excepción al cliente.
 *
 * @param callable $callback
 * @return mixed
 */
function app_ejecutar_protegido($callback)
{
  try {
    return call_user_func($callback);
  } catch (Throwable $e) {
    app_log('error', 'Error no controlado en el controlador: ' . $e->getMessage(), array(
      'excepcion' => get_class($e),
      'archivo' => $e->getFile(),
      'linea' => $e->getLine(),
    ));

    app_responder_error();
  }

  return null;
}
