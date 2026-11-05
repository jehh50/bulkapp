<?php

/**
 * Utilidades de seguridad transversales: sesiones, CSRF, contraseñas,
 * validación de entrada y carga de archivos.
 */

function security_is_https()
{
  if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
    return true;
  }
  if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
    return true;
  }
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
    return true;
  }
  return false;
}

function security_session_start()
{
  if (session_status() === PHP_SESSION_ACTIVE) {
    return true;
  }
  if (headers_sent()) {
    return false;
  }

  $timeout = max(300, (int) env('SESSION_IDLE_TIMEOUT', 1800));

  ini_set('session.use_strict_mode', '1');
  ini_set('session.use_only_cookies', '1');
  ini_set('session.cookie_httponly', '1');
  ini_set('session.cookie_samesite', 'Lax');
  ini_set('session.cookie_secure', security_is_https() ? '1' : '0');
  ini_set('session.cookie_lifetime', '0');
  ini_set('session.gc_maxlifetime', (string) $timeout);

  return session_start();
}

function security_regenerate_session_id()
{
  if (session_status() !== PHP_SESSION_ACTIVE || headers_sent()) {
    return false;
  }
  return session_regenerate_id(true);
}

function security_destroy_session()
{
  $_SESSION = [];

  if (session_status() === PHP_SESSION_ACTIVE) {
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        isset($params['path']) ? $params['path'] : '/',
        isset($params['domain']) ? $params['domain'] : '',
        !empty($params['secure']),
        !empty($params['httponly'])
      );
    }
    session_destroy();
  }
}

/**
 * Cierra la sesión por inactividad. Se invoca desde session.php en cada
 * petición autenticada.
 */
function security_enforce_timeout()
{
  if (empty($_SESSION)) {
    return;
  }

  $timeout = max(300, (int) env('SESSION_IDLE_TIMEOUT', 1800));
  $ahora = time();

  if (isset($_SESSION['last_activity']) && ($ahora - (int) $_SESSION['last_activity']) > $timeout) {
    if (function_exists('app_log')) {
      app_log('info', 'Sesión cerrada por inactividad', array(
        'event' => 'auth.session_timeout',
        'user_id' => isset($_SESSION['id']) ? (int) $_SESSION['id'] : null,
        'ip' => function_exists('app_client_ip') ? app_client_ip() : '',
      ));
    }
    security_destroy_session();
    return;
  }

  $_SESSION['last_activity'] = $ahora;
}

/**
 * Indica si la sesión corresponde a un usuario autenticado.
 *
 * No alcanza con que $_SESSION tenga datos: la página de login crea el token
 * CSRF antes de autenticar, así que una sesión anónima puede no estar vacía.
 */
function security_usuario_autenticado()
{
  return isset($_SESSION['id'], $_SESSION['usertype_id'])
    && (int) $_SESSION['id'] > 0;
}

/**
 * Tipo de usuario de la sesión actual (0 si no está autenticado).
 *
 * @return int
 */
function security_tipo_usuario()
{
  return isset($_SESSION['usertype_id']) ? (int) $_SESSION['usertype_id'] : 0;
}

/**
 * Exige una sesión autenticada. Si no existe, redirige al login y termina.
 *
 * @return void
 */
function security_requerir_autenticacion()
{
  if (security_usuario_autenticado()) {
    return;
  }

  header('Location: index.php');
  exit;
}

/**
 * Exige que el usuario autenticado tenga uno de los tipos indicados.
 * Responde 403 y termina si el rol no está permitido.
 *
 * @param array $tipos
 * @return void
 */
function security_requerir_rol(array $tipos)
{
  security_requerir_autenticacion();

  $permitidos = array_map('intval', $tipos);
  if (in_array(security_tipo_usuario(), $permitidos, true)) {
    return;
  }

  if (function_exists('app_log')) {
    app_log('warning', 'Acceso denegado por rol', array(
      'event' => 'security.access_denied',
      'user_id' => isset($_SESSION['id']) ? (int) $_SESSION['id'] : null,
      'usertype_id' => security_tipo_usuario(),
      'view' => isset($_GET['view']) ? $_GET['view'] : '',
      'mode' => isset($_GET['mode']) ? $_GET['mode'] : '',
      'ip' => function_exists('app_client_ip') ? app_client_ip() : '',
    ));
  }

  security_responder_sin_permiso();
}

/**
 * Respuesta cuando el usuario autenticado no tiene permiso para la sección.
 *
 * Navegación normal: página con un modal de "acceso restringido" que redirige
 * al formulario. Peticiones AJAX: JSON controlado con el destino sugerido.
 *
 * @return void
 */
function security_responder_sin_permiso()
{
  $destino = 'index.php?view=formulario&mode=index';

  $esAjax = (function_exists('app_es_ajax') && app_es_ajax())
    || isset($_SERVER['HTTP_X_CSRF_TOKEN'])
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos((string) $_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

  if (!headers_sent()) {
    http_response_code(403);
    header('Content-Type: ' . ($esAjax ? 'application/json' : 'text/html') . '; charset=UTF-8');
  }

  if ($esAjax) {
    echo json_encode(array(
      'response' => 'false',
      'denied'   => true,
      'message'  => 'No tienes acceso a esta sección.',
      'redirect' => $destino,
    ));
    exit;
  }

  $destinoAttr = htmlspecialchars($destino, ENT_QUOTES, 'UTF-8');
  $destinoJs   = json_encode($destino);

  echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
    . '<meta name="viewport" content="width=device-width, initial-scale=1">'
    . '<title>Acceso restringido</title><style>'
    . ':root{--blue-900:#12306b;--blue-700:#1a4391;--gold:#f5b83d;--ink:#252525}'
    . '*{box-sizing:border-box}body{margin:0;font-family:system-ui,Segoe UI,Roboto,sans-serif;'
    . 'background:rgba(18,48,107,.55);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}'
    . '.sp-card{background:#fff;border-radius:16px;box-shadow:0 18px 50px rgba(18,48,107,.35);max-width:420px;width:100%;'
    . 'padding:28px;text-align:center;border-top:5px solid var(--gold)}'
    . '.sp-ico{width:62px;height:62px;border-radius:50%;background:#fdecec;color:#b42318;display:flex;align-items:center;'
    . 'justify-content:center;font-size:30px;margin:0 auto 16px}'
    . '.sp-title{margin:0 0 8px;font-size:20px;font-weight:600;color:var(--ink)}'
    . '.sp-text{margin:0 0 20px;font-size:14.5px;color:#5b6475;line-height:1.5}'
    . '.sp-btn{display:inline-block;height:44px;line-height:44px;padding:0 24px;border-radius:10px;background:var(--blue-700);'
    . 'color:#fff;font-size:14px;font-weight:600;text-decoration:none}.sp-btn:hover{background:var(--blue-900)}'
    . '</style></head><body>'
    . '<div class="sp-card" role="alertdialog" aria-labelledby="spTitle" aria-describedby="spText">'
    . '<div class="sp-ico">&#9888;</div>'
    . '<h1 class="sp-title" id="spTitle">Acceso restringido</h1>'
    . '<p class="sp-text" id="spText">No tienes permiso para acceder a esta sección. '
    . 'Te redirigiremos al formulario en unos segundos.</p>'
    . '<a class="sp-btn" href="' . $destinoAttr . '">Ir al formulario</a>'
    . '</div>'
    . '<script>setTimeout(function(){window.location=' . $destinoJs . ';},3000);</script>'
    . '</body></html>';
  exit;
}

/* -------------------------------------------------------------------------
 * CSRF
 * ---------------------------------------------------------------------- */

function security_csrf_token()
{
  security_session_start();

  if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  return $_SESSION['csrf_token'];
}

function security_csrf_meta()
{
  return '<meta name="csrf-token" content="' . htmlspecialchars(security_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function security_csrf_request_token()
{
  if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])) {
    return $_POST['csrf_token'];
  }
  if (isset($_SERVER['HTTP_X_CSRF_TOKEN']) && is_string($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    return $_SERVER['HTTP_X_CSRF_TOKEN'];
  }
  return '';
}

function security_require_csrf()
{
  if (strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') !== 'POST') {
    return;
  }

  $esperado = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
  $recibido = security_csrf_request_token();

  if ($esperado === '' || $recibido === '' || !hash_equals($esperado, $recibido)) {
    if (function_exists('app_log')) {
      app_log('warning', 'Token CSRF inválido o ausente', array(
        'event' => 'security.csrf_failed',
        'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
        'view' => isset($_GET['view']) ? $_GET['view'] : '',
        'mode' => isset($_GET['mode']) ? $_GET['mode'] : '',
        'ip' => function_exists('app_client_ip') ? app_client_ip() : '',
        'user_agent' => function_exists('app_user_agent') ? app_user_agent() : '',
      ));
    }

    security_responder_csrf_invalido();
  }
}

/**
 * Respuesta controlada cuando el token CSRF es inválido o la sesión expiró.
 *
 * Evita el 500/respuesta cruda que rompía las llamadas AJAX: para una petición
 * AJAX devuelve un JSON que el front puede interpretar (response=false +
 * expired), y para una navegación normal redirige al login con un aviso.
 *
 * @return void
 */
function security_responder_csrf_invalido()
{
  $mensaje = 'Su sesión expiró. Vuelva a iniciar sesión e intente nuevamente.';

  $esAjax = (function_exists('app_es_ajax') && app_es_ajax())
    || isset($_SERVER['HTTP_X_CSRF_TOKEN'])
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos((string) $_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

  if ($esAjax) {
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode(array(
      'response' => 'false',
      'expired'  => true,
      'message'  => $mensaje,
    ));
    exit;
  }

  if (!headers_sent()) {
    header('Location: index.php?expirado=1');
  }
  exit;
}

/* -------------------------------------------------------------------------
 * Contraseñas
 * ---------------------------------------------------------------------- */

function security_hash_password($password)
{
  return password_hash($password, PASSWORD_DEFAULT);
}

function security_password_verify($password, $hash)
{
  if (!is_string($password) || !is_string($hash) || $hash === '') {
    return false;
  }

  if (password_verify($password, $hash)) {
    return true;
  }

  // Compatibilidad con hashes legacy md5: se aceptan una sola vez y se
  // reemplazan por password_hash() tras un login exitoso.
  if (preg_match('/^[a-f0-9]{32}$/i', $hash)) {
    return hash_equals(strtolower($hash), md5($password));
  }

  return false;
}

function security_password_needs_rehash($hash)
{
  if (!is_string($hash) || $hash === '') {
    return true;
  }
  if (preg_match('/^[a-f0-9]{32}$/i', $hash)) {
    return true;
  }
  return password_needs_rehash($hash, PASSWORD_DEFAULT);
}

function security_random_password($length = 14)
{
  $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*';
  $max = strlen($alfabeto) - 1;
  $password = '';

  for ($i = 0; $i < $length; $i++) {
    $password .= $alfabeto[random_int(0, $max)];
  }

  return $password;
}

/* -------------------------------------------------------------------------
 * Validación de entrada
 * ---------------------------------------------------------------------- */

function input_string($valor, $maxLength = 255)
{
  if (!is_scalar($valor)) {
    return '';
  }

  $valor = trim((string) $valor);
  $valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $valor);

  if ($valor === null) {
    return '';
  }
  if ($maxLength > 0) {
    $valor = mb_substr($valor, 0, $maxLength);
  }

  return $valor;
}

function input_int($valor, $min = null, $max = null, $porDefecto = null)
{
  if (!is_scalar($valor) || !preg_match('/^-?\d+$/', trim((string) $valor))) {
    return $porDefecto;
  }

  $numero = (int) $valor;
  if ($min !== null && $numero < $min) {
    return $porDefecto;
  }
  if ($max !== null && $numero > $max) {
    return $porDefecto;
  }

  return $numero;
}

function input_enum($valor, array $permitidos, $porDefecto = null)
{
  if (!is_scalar($valor)) {
    return $porDefecto;
  }
  if (in_array((string) $valor, array_map('strval', $permitidos), true)) {
    return (string) $valor;
  }
  return $porDefecto;
}

function input_date($valor, $formato = 'Y-m-d')
{
  if (!is_string($valor) || $valor === '') {
    return null;
  }

  $fecha = DateTime::createFromFormat('!' . $formato, $valor);
  if (!$fecha || $fecha->format($formato) !== $valor) {
    return null;
  }

  return $valor;
}

function input_email($valor, $maxLength = 150)
{
  $valor = input_string($valor, $maxLength);
  return filter_var($valor, FILTER_VALIDATE_EMAIL) ? $valor : null;
}

function input_letters($valor, $maxLength = 50)
{
  $valor = input_string($valor, $maxLength);
  $valor = preg_replace("/[^\p{L}\s'\-]/u", '', $valor);
  $valor = preg_replace('/\s+/u', ' ', $valor);
  return trim((string) $valor);
}

function input_digits($valor, $maxLength = 20)
{
  if (!is_scalar($valor)) {
    return '';
  }
  $valor = preg_replace('/\D+/', '', (string) $valor);
  return mb_substr($valor, 0, $maxLength);
}

function input_pan($valor)
{
  $valor = input_string($valor, 20);
  return preg_match('/^[0-9*]{1,20}$/', $valor) ? $valor : null;
}

function input_phone($valor, $maxLength = 20)
{
  $valor = input_string($valor, $maxLength);
  return preg_match('/^[0-9()\.\-\s+]+$/', $valor) ? $valor : null;
}

function input_amount($valor)
{
  $valor = input_string($valor, 30);
  return preg_match('/^[0-9.,]+$/', $valor) ? $valor : null;
}

function input_int_array($valor, $maxItems = 1000)
{
  if (!is_array($valor)) {
    return [];
  }

  $resultado = [];
  foreach (array_slice($valor, 0, $maxItems) as $item) {
    $numero = input_int($item, 0);
    if ($numero !== null) {
      $resultado[] = $numero;
    }
  }

  return $resultado;
}

/* -------------------------------------------------------------------------
 * Carga de archivos
 * ---------------------------------------------------------------------- */

function security_validate_csv_upload($archivo, $maxBytes = 10485760)
{
  if (!is_array($archivo) || !isset($archivo['tmp_name'], $archivo['error'], $archivo['size'])) {
    return [false, 'No se recibió el archivo.'];
  }

  if ((int) $archivo['error'] !== UPLOAD_ERR_OK) {
    return [false, 'La carga del archivo falló.'];
  }

  if (!is_uploaded_file($archivo['tmp_name'])) {
    return [false, 'El archivo recibido no es una carga válida.'];
  }

  $tamano = (int) $archivo['size'];
  if ($tamano <= 0 || $tamano > $maxBytes) {
    return [false, 'El archivo está vacío o excede el tamaño permitido.'];
  }

  $extension = strtolower(pathinfo(isset($archivo['name']) ? $archivo['name'] : '', PATHINFO_EXTENSION));
  if (!in_array($extension, ['csv', 'txt'], true)) {
    return [false, 'Solo se permiten archivos CSV o TXT.'];
  }

  if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);
    $permitidos = [
      'text/plain',
      'text/csv',
      'text/x-csv',
      'application/csv',
      'application/vnd.ms-excel',
    ];
    if ($mime !== false && !in_array(strtolower($mime), $permitidos, true)) {
      return [false, 'El tipo de archivo no es válido.'];
    }
  }

  $muestra = file_get_contents($archivo['tmp_name'], false, null, 0, 4096);
  if ($muestra === false || strpos($muestra, "\0") !== false) {
    return [false, 'El contenido del archivo no es válido.'];
  }

  return [true, ''];
}
