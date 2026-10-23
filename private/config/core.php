<?php

require_once __DIR__ . '/env.php';

$raizProyecto = dirname(__DIR__, 2);
load_env($raizProyecto . '/.env');

$appDebug = env('APP_DEBUG', '0') === '1';
ini_set('display_errors', $appDebug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('America/Caracas');

#Credenciales de base de datos (definidas en .env, fuera del repositorio)
$dbHost = env('DB_HOST', 'localhost');
$dbPort = (int) env('DB_PORT', 3306);
$dbUser = env('DB_USER');
$dbPass = env('DB_PASS');
$dbName = env('DB_NAME');

if ($dbUser === null || $dbPass === null || $dbName === null) {
  throw new RuntimeException('Configuración de base de datos incompleta. Revise el archivo .env.');
}

define('DB_HOST', $dbHost);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);
define('DB_PORT', $dbPort);

#Constantes de la APP
define('HTML_DIR', 'private/views/');
define('MODEL_DIR', 'private/model/');
define('PUBLIC_DIR', 'public/');
define('LIB_DIR', 'private/lib/');
define('APP_TITLE', 'BULKSALES');
define('APP_COPY', 'Copyright &copy; ' . date('Y',time()));
define('APP_URL', env('APP_URL', 'http://localhost/'));
define('APP_VERSION', '1.0.0');

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/constantes.php';
require_once dirname(__DIR__) . '/lib/logger.php';
require_once dirname(__DIR__) . '/lib/errors.php';
require_once dirname(__DIR__) . '/lib/assets.php';
require_once __DIR__ . '/database.php';

app_registrar_handlers();

?>
