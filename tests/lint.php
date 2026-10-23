<?php

/**
 * Verificación de sintaxis de todos los archivos PHP del proyecto.
 *
 * Uso:  php tests/lint.php
 * Devuelve 0 si no hay errores de sintaxis, 1 en caso contrario.
 */

$raizProyecto = dirname(__DIR__);
$directorios = array('index.php', 'private', 'public', 'tests');

$archivos = array();

foreach ($directorios as $directorio) {
  $ruta = $raizProyecto . '/' . $directorio;
  if (is_file($ruta)) {
    $archivos[] = $ruta;
    continue;
  }
  if (!is_dir($ruta)) {
    continue;
  }
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ruta, FilesystemIterator::SKIP_DOTS));
  foreach ($iterator as $archivo) {
    if ($archivo->isFile() && strtolower($archivo->getExtension()) === 'php') {
      $archivos[] = $archivo->getPathname();
    }
  }
}

sort($archivos);

$errores = array();

foreach ($archivos as $archivo) {
  $salida = array();
  $codigo = 0;
  exec(escapeshellcmd(PHP_BINARY) . ' -l ' . escapeshellarg($archivo) . ' 2>&1', $salida, $codigo);
  if ($codigo !== 0) {
    $errores[] = $archivo . ': ' . implode(' ', $salida);
  }
}

echo 'Archivos verificados: ' . count($archivos) . ', con errores: ' . count($errores) . PHP_EOL;

foreach ($errores as $error) {
  echo '  - ' . $error . PHP_EOL;
}

exit(empty($errores) ? 0 : 1);
