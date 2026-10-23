<?php

/**
 * Sistema de logging estructurado de la aplicación (sin Composer ni Monolog).
 *
 * Los registros se escriben en un archivo dentro de LOG_PATH
 * (por defecto `storage/logs/app.log`). Por defecto cada línea es un objeto
 * JSON independiente (JSON Lines):
 *
 *   {"timestamp":"2026-10-20T14:05:33-04:00","level":"error","event":"db.query",
 *    "message":"Error al ejecutar la consulta","request_id":"...","context":{...}}
 *
 * Configuración por variables de entorno:
 *   LOG_ENABLED  1|0        activa/desactiva la escritura (por defecto 1)
 *   LOG_LEVEL    debug|info|warning|error|critical  (por defecto info)
 *   LOG_FORMAT   json|text  formato de salida (por defecto json)
 *   LOG_PATH     ruta del archivo de log
 *   LOG_MAX_BYTES tamaño máximo antes de rotar (por defecto 5 MB)
 */
class AppLogger
{
  const NIVEL_DEBUG = 'debug';
  const NIVEL_INFO = 'info';
  const NIVEL_WARNING = 'warning';
  const NIVEL_ERROR = 'error';
  const NIVEL_CRITICAL = 'critical';

  private static $niveles = array(
    self::NIVEL_DEBUG => 0,
    self::NIVEL_INFO => 1,
    self::NIVEL_WARNING => 2,
    self::NIVEL_ERROR => 3,
    self::NIVEL_CRITICAL => 4,
  );

  private static $rutaArchivo = null;
  private static $nivelMinimo = null;
  private static $habilitado = null;
  private static $formato = null;
  private static $requestId = null;

  /**
   * Permite fijar la configuración en runtime (usado por las pruebas).
   *
   * @param string|null $rutaArchivo
   * @param string|null $nivelMinimo
   * @param bool|null $habilitado
   */
  
  public static function configurar($rutaArchivo = null, $nivelMinimo = null, $habilitado = null)
  {
    if ($rutaArchivo !== null) {
      self::$rutaArchivo = $rutaArchivo;
    }
    if ($nivelMinimo !== null) {
      self::$nivelMinimo = strtolower((string) $nivelMinimo);
    }
    if ($habilitado !== null) {
      self::$habilitado = (bool) $habilitado;
    }
  }

  /**
   * @return string Ruta del archivo donde se escriben los registros.
   */
  public static function rutaArchivo()
  {
    if (self::$rutaArchivo === null) {
      $ruta = (string) env('LOG_PATH', dirname(__DIR__, 2) . '/storage/logs/app.log');
      $esAbsoluta = $ruta !== '' && ($ruta[0] === '/' || $ruta[0] === '\\'
        || preg_match('/^[A-Za-z]:[\\\\\\/]/', $ruta));
      if (!$esAbsoluta) {
        $ruta = dirname(__DIR__, 2) . '/' . ltrim($ruta, '/\\');
      }
      self::$rutaArchivo = $ruta;
    }

    return self::$rutaArchivo;
  }

  /**
   * @return string Formato de salida: json (JSON Lines) o text.
   */
  public static function formato()
  {
    if (self::$formato === null) {
      self::$formato = strtolower((string) env('LOG_FORMAT', 'json'));
    }

    if (!in_array(self::$formato, array('json', 'text'), true)) {
      self::$formato = 'json';
    }

    return self::$formato;
  }

  /**
   * Identificador de la petición actual. Se reutiliza el valor recibido en
   * X-Request-Id si es válido; en caso contrario se genera uno.
   *
   * @return string
   */
  public static function requestId()
  {
    if (self::$requestId !== null) {
      return self::$requestId;
    }

    $recibido = isset($_SERVER['HTTP_X_REQUEST_ID']) ? (string) $_SERVER['HTTP_X_REQUEST_ID'] : '';
    if (preg_match('/^[A-Za-z0-9._-]{8,64}$/', $recibido)) {
      self::$requestId = $recibido;
      return self::$requestId;
    }

    try {
      $aleatorio = bin2hex(random_bytes(12));
    } catch (Throwable $e) {
      $aleatorio = uniqid('', true);
    }

    if (PHP_SAPI === 'cli') {
      self::$requestId = 'cli-' . getmypid() . '-' . $aleatorio;
    } else {
      self::$requestId = $aleatorio;
    }

    return self::$requestId;
  }

  /**
   * @return string Nivel mínimo que se registra.
   */
  public static function nivelMinimo()
  {
    if (self::$nivelMinimo === null) {
      self::$nivelMinimo = strtolower((string) env('LOG_LEVEL', self::NIVEL_INFO));
    }

    if (!isset(self::$niveles[self::$nivelMinimo])) {
      self::$nivelMinimo = self::NIVEL_INFO;
    }

    return self::$nivelMinimo;
  }

  /**
   * @return bool
   */
  public static function estaHabilitado()
  {
    if (self::$habilitado === null) {
      self::$habilitado = env('LOG_ENABLED', '1') !== '0';
    }

    return self::$habilitado;
  }

  /**
   * Registra un mensaje.
   *
   * @param string $nivel
   * @param string $mensaje
   * @param array $contexto
   * @return bool True si el mensaje se escribió.
   */
  public static function log($nivel, $mensaje, array $contexto = array())
  {
    $nivel = strtolower((string) $nivel);
    if (!isset(self::$niveles[$nivel])) {
      $nivel = self::NIVEL_INFO;
    }

    if (!self::estaHabilitado()) {
      return false;
    }

    if (self::$niveles[$nivel] < self::$niveles[self::nivelMinimo()]) {
      return false;
    }

    $linea = self::formatearLinea($nivel, $mensaje, $contexto);

    return self::escribir($linea);
  }

  /**
   * @param string $mensaje
   * @param array $contexto
   * @return bool
   */
  public static function debug($mensaje, array $contexto = array())
  {
    return self::log(self::NIVEL_DEBUG, $mensaje, $contexto);
  }

  /**
   * @param string $mensaje
   * @param array $contexto
   * @return bool
   */
  public static function info($mensaje, array $contexto = array())
  {
    return self::log(self::NIVEL_INFO, $mensaje, $contexto);
  }

  /**
   * @param string $mensaje
   * @param array $contexto
   * @return bool
   */
  public static function warning($mensaje, array $contexto = array())
  {
    return self::log(self::NIVEL_WARNING, $mensaje, $contexto);
  }

  /**
   * @param string $mensaje
   * @param array $contexto
   * @return bool
   */
  public static function error($mensaje, array $contexto = array())
  {
    return self::log(self::NIVEL_ERROR, $mensaje, $contexto);
  }

  /**
   * @param string $mensaje
   * @param array $contexto
   * @return bool
   */
  public static function critical($mensaje, array $contexto = array())
  {
    return self::log(self::NIVEL_CRITICAL, $mensaje, $contexto);
  }

  /**
   * Arma la línea de log.
   *
   * @param string $nivel
   * @param string $mensaje
   * @param array $contexto
   * @return string
   */
  public static function formatearLinea($nivel, $mensaje, array $contexto = array())
  {
    $contexto = self::contextoConMetadatos($contexto);

    if (self::formato() === 'json') {
      return self::formatearJson($nivel, $mensaje, $contexto);
    }

    $mensaje = self::normalizar((string) $mensaje);
    $linea = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($nivel) . '] ' . $mensaje;

    if (!empty($contexto)) {
      $linea .= ' | contexto: ' . self::normalizar(self::codificarContexto($contexto));
    }

    return $linea;
  }

  /**
   * Agrega metadatos comunes a todos los registros.
   *
   * @param array $contexto
   * @return array
   */
  private static function contextoConMetadatos(array $contexto)
  {
    if (!array_key_exists('request_id', $contexto)) {
      $contexto['request_id'] = self::requestId();
    }

    if (!array_key_exists('user_id', $contexto) && isset($_SESSION['id'])) {
      $contexto['user_id'] = (int) $_SESSION['id'];
    }

    return $contexto;
  }

  /**
   * Genera una línea JSON (JSON Lines). El evento se toma de
   * `$contexto['event']` para no duplicarlo dentro de `context`.
   *
   * @param string $nivel
   * @param string $mensaje
   * @param array $contexto
   * @return string
   */
  private static function formatearJson($nivel, $mensaje, array $contexto)
  {
    $evento = 'app.log';
    if (isset($contexto['event']) && is_scalar($contexto['event'])) {
      $evento = (string) $contexto['event'];
      unset($contexto['event']);
    }

    $requestId = self::requestId();
    if (isset($contexto['request_id'])) {
      $requestId = (string) $contexto['request_id'];
      unset($contexto['request_id']);
    }

    $userId = null;
    if (array_key_exists('user_id', $contexto)) {
      $userId = $contexto['user_id'];
      unset($contexto['user_id']);
    }

    $registro = array(
      'timestamp' => date('c'),
      'level' => $nivel,
      'event' => $evento,
      'message' => self::normalizar((string) $mensaje),
      'request_id' => $requestId,
    );

    if ($userId !== null) {
      $registro['user_id'] = $userId;
    }

    if (!empty($contexto)) {
      $registro['context'] = $contexto;
    }

    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
      | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR;
    $json = @json_encode($registro, $flags);

    if ($json === false) {
      $registro['context'] = array('_error' => 'contexto no serializable');
      $json = @json_encode($registro, $flags);
    }

    if ($json === false) {
      $json = '{"timestamp":"' . date('c') . '","level":"error",'
        . '"event":"logger.encode_failed","message":"No se pudo serializar el registro",'
        . '"request_id":' . json_encode($requestId) . '}';
    }

    return $json;
  }

  /**
   * @param array $contexto
   * @return string
   */
  private static function codificarContexto(array $contexto)
  {
    $json = @json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
      return '[contexto no serializable]';
    }

    return $json;
  }

  /**
   * Reemplaza saltos de línea para no romper el formato de una línea por registro.
   *
   * @param string $texto
   * @return string
   */
  private static function normalizar($texto)
  {
    return str_replace(array("\r\n", "\n", "\r"), ' ', $texto);
  }

  /**
   * Escribe la línea en disco rotando el archivo si excede el tamaño máximo.
   *
   * @param string $linea
   * @return bool
   */
  private static function escribir($linea)
  {
    $ruta = self::rutaArchivo();
    $directorio = dirname($ruta);

    if (!is_dir($directorio)) {
      if (!@mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        error_log('[AppLogger] No se pudo crear el directorio de logs: ' . $directorio);
        return false;
      }
    }

    if (!self::is_file_writable($ruta)) {
      error_log('[AppLogger] No se pudo escribir en el archivo de log: ' . $ruta);
      return false;
    }

    self::rotarSiEsNecesario($ruta);

    return @file_put_contents($ruta, $linea . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
  }

  /**
   * @param string $ruta
   * @return bool
   */
  private static function is_file_writable($ruta)
  {
    if (is_file($ruta)) {
      return is_writable($ruta);
    }

    return is_dir(dirname($ruta)) && is_writable(dirname($ruta));
  }

  /**
   * @param string $ruta
   * @return void
   */
  private static function rotarSiEsNecesario($ruta)
  {
    if (!is_file($ruta)) {
      return;
    }

    $maximo = (int) env('LOG_MAX_BYTES', 5 * 1024 * 1024);
    if ($maximo <= 0) {
      return;
    }

    $tamano = @filesize($ruta);
    if ($tamano === false || $tamano < $maximo) {
      return;
    }

    @rename($ruta, $ruta . '.1');
  }
}

/**
 * Atajo funcional del logger.
 *
 * @param string $nivel
 * @param string $mensaje
 * @param array $contexto
 * @return bool
 */
function app_log($nivel, $mensaje, array $contexto = array())
{
  return AppLogger::log($nivel, $mensaje, $contexto);
}

/**
 * @return string Identificador de la petición actual.
 */
function app_request_id()
{
  return AppLogger::requestId();
}

/**
 * Devuelve la IP del cliente sin confiar en cabeceras reenviadas.
 *
 * @return string
 */
function app_client_ip()
{
  $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';

  return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

/**
 * @param int $maximo
 * @return string
 */
function app_user_agent($maximo = 200)
{
  $agente = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';

  return substr($agente, 0, max(0, (int) $maximo));
}
