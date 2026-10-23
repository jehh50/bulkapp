<?php

/**
 * Pruebas del helper de assets (cache-busting) y de los helpers de error.
 */

test('asset() agrega la versión por hash del archivo', function () {
  $ruta = asset('public/js/formulario.js');

  assert_contiene('public/js/formulario.js', $ruta);
  assert_contiene('?v=', $ruta);

  $versiona = substr($ruta, strpos($ruta, '?v=') + 3);
  assert_igual(8, strlen($versiona));
});

test('asset() no modifica las URLs externas', function () {
  $url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css';
  assert_igual($url, asset($url));
});

test('asset() conserva los parámetros existentes', function () {
  $ruta = asset('public/js/datatables.min.js?v=2');
  assert_contiene('?v=2&v=', $ruta);
});

test('asset_version() devuelve la versión de la aplicación para archivos inexistentes', function () {
  assert_igual(APP_VERSION, asset_version('public/js/no-existe-este-archivo.js'));
});

test('app_es_ajax detecta las peticiones AJAX', function () {
  $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
  assert_verdadero(app_es_ajax());

  unset($_SERVER['HTTP_X_REQUESTED_WITH']);
  assert_falso(app_es_ajax());
});

test('app_mensaje_error devuelve un mensaje genérico', function () {
  $mensaje = app_mensaje_error();
  assert_igual('string', gettype($mensaje));
  assert_contiene('error', strtolower($mensaje));
});
