<?php

/**
 * Pruebas del sistema de logging.
 */

test('AppLogger escribe una línea por mensaje', function () {
  $archivo = tempnam(sys_get_temp_dir(), 'bulkapp-log-');
  AppLogger::configurar($archivo, 'debug', true);

  app_log('info', 'mensaje de prueba', array('clave' => 'valor'));

  $contenido = file_get_contents($archivo);
  assert_contiene('mensaje de prueba', $contenido);
  assert_contiene('clave', $contenido);

  unlink($archivo);
});

test('AppLogger respeta el nivel mínimo configurado', function () {
  $archivo = tempnam(sys_get_temp_dir(), 'bulkapp-log-');
  AppLogger::configurar($archivo, 'error', true);

  app_log('info', 'no debe aparecer');
  app_log('error', 'debe aparecer');

  $contenido = file_get_contents($archivo);
  assert_contiene('debe aparecer', $contenido);

  unlink($archivo);
});

test('AppLogger puede deshabilitarse', function () {
  $archivo = tempnam(sys_get_temp_dir(), 'bulkapp-log-');
  AppLogger::configurar($archivo, 'debug', false);

  app_log('error', 'no debe escribirse');

  assert_igual('', file_get_contents($archivo));

  unlink($archivo);
  AppLogger::configurar(null, 'info', true);
});

test('AppLogger normaliza los saltos de línea del mensaje', function () {
  $archivo = tempnam(sys_get_temp_dir(), 'bulkapp-log-');
  AppLogger::configurar($archivo, 'debug', true);

  app_log('warning', "linea uno\nlinea dos");

  $contenido = file_get_contents($archivo);
  assert_contiene('linea uno linea dos', $contenido);
  assert_igual(1, substr_count(rtrim($contenido), "\n") + 1);

  unlink($archivo);
});

test('AppLogger escribe una línea por registro', function () {
  $archivo = tempnam(sys_get_temp_dir(), 'bulkapp-log-');
  AppLogger::configurar($archivo, 'debug', true);

  app_log('info', 'primero');
  app_log('info', 'segundo');

  $lineas = array_filter(explode("\n", file_get_contents($archivo)));
  assert_igual(2, count($lineas));

  unlink($archivo);
});
