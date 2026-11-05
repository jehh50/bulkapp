<?php

/**
 * Runner de las pruebas unitarias de BulkApp.
 *
 * Uso:  php tests/run.php
 * Devuelve 0 si todas las pruebas pasan, 1 en caso contrario.
 */

require __DIR__ . '/bootstrap.php';

$archivos = glob(__DIR__ . '/unit/*Test.php');
sort($archivos);

if (empty($archivos)) {
  echo "No se encontraron pruebas en tests/unit/" . PHP_EOL;
  exit(1);
}

echo 'Ejecutando pruebas de ' . count($archivos) . ' archivo(s)...' . PHP_EOL . PHP_EOL;

foreach ($archivos as $archivo) {
  require $archivo;
}

exit(TestSuite::ejecutar());
