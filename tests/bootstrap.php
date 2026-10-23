<?php

/**
 * Bootstrap de las pruebas: carga únicamente el código que no necesita base de datos.
 */

$raizProyecto = dirname(__DIR__);

require_once __DIR__ . '/framework.php';
require_once $raizProyecto . '/private/config/env.php';

if (is_file($raizProyecto . '/.env')) {
  load_env($raizProyecto . '/.env');
}

require_once $raizProyecto . '/private/config/security.php';
require_once $raizProyecto . '/private/config/constantes.php';
require_once $raizProyecto . '/private/lib/logger.php';
require_once $raizProyecto . '/private/lib/errors.php';
require_once $raizProyecto . '/private/lib/assets.php';
require_once $raizProyecto . '/private/helpers/formularioRegistro.php';

if (!defined('APP_VERSION')) {
  define('APP_VERSION', '1.0.0');
}

define('BULKAPP_RAIZ', $raizProyecto);
