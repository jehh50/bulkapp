<?php

/**
 * Carga mínima de variables de entorno (sin Composer).
 *
 * Lee un archivo .env en la raíz del proyecto y expone los valores a través de
 * getenv(). Las variables ya definidas en el entorno tienen prioridad.
 */
function load_env($path)
{
  if (!is_file($path) || !is_readable($path)) {
    return;
  }

  $lineas = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if ($lineas === false) {
    return;
  }

  foreach ($lineas as $linea) {
    $linea = trim($linea);
    if ($linea === '' || $linea[0] === '#') {
      continue;
    }
    if (strpos($linea, '=') === false) {
      continue;
    }

    list($clave, $valor) = explode('=', $linea, 2);
    $clave = trim($clave);
    $valor = trim($valor);

    if ($clave === '') {
      continue;
    }

    // Quita comillas envolventes simples o dobles.
    if (strlen($valor) >= 2) {
      $primero = $valor[0];
      $ultimo = substr($valor, -1);
      if (($primero === '"' && $ultimo === '"') || ($primero === "'" && $ultimo === "'")) {
        $valor = substr($valor, 1, -1);
      }
    }

    if (getenv($clave) === false) {
      putenv($clave . '=' . $valor);
      $_ENV[$clave] = $valor;
    }
  }
}

function env($clave, $porDefecto = null)
{
  $valor = getenv($clave);
  if ($valor === false || $valor === '') {
    return $porDefecto;
  }
  return $valor;
}
