<?php

/**
 * Helper de assets con cache-busting.
 *
 * Las referencias a CSS/JS se versionan con el hash del contenido del archivo,
 * de modo que el navegador descarga de nuevo el recurso solo cuando cambia.
 * Ejemplo: `asset('public/js/formulario.js')` → `public/js/formulario.js?v=1a2b3c4d`.
 */

/**
 * @param string $ruta Ruta relativa a la raíz del proyecto (p. ej. 'public/js/app.js').
 * @return string Ruta con el parámetro de versión.
 */
function asset($ruta)
{
  $ruta = (string) $ruta;

  if ($ruta === '' || preg_match('#^(https?:)?//#i', $ruta) === 1) {
    return $ruta;
  }

  $separador = (strpos($ruta, '?') === false) ? '?' : '&';

  return $ruta . $separador . 'v=' . asset_version($ruta);
}

/**
 * Calcula la versión (hash corto) de un asset.
 *
 * @param string $ruta
 * @return string
 */
function asset_version($ruta)
{
  $ruta = (string) $ruta;
  $pregunta = strpos($ruta, '?');
  if ($pregunta !== false) {
    $ruta = substr($ruta, 0, $pregunta);
  }

  $absoluta = asset_ruta_absoluta($ruta);
  if ($absoluta !== null && is_file($absoluta)) {
    $hash = @md5_file($absoluta);
    if ($hash !== false) {
      return substr($hash, 0, 8);
    }
  }

  return APP_VERSION;
}

/**
 * Resuelve la ruta absoluta de un asset dentro del proyecto.
 *
 * @param string $ruta
 * @return string|null
 */
function asset_ruta_absoluta($ruta)
{
  $ruta = ltrim((string) $ruta, '/');

  // No se resuelven rutas que salgan de la raíz del proyecto.
  if (strpos($ruta, '..') !== false) {
    return null;
  }

  $absoluta = dirname(__DIR__, 2) . '/' . $ruta;

  return is_file($absoluta) ? $absoluta : null;
}
