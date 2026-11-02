<?php

/**
 * Pruebas del runtime de formularios dinámicos (private/helpers/captura.php):
 * validación en servidor de valores capturados y armado del CSV de envíos.
 */

/**
 * Definición de ejemplo que cubre todos los tipos y una condición.
 *
 * @return array
 */
function captura_definicion_ejemplo()
{
  return array('campos' => array(
    array('k' => 'resultado', 'type' => 'select', 'label' => 'Resultado', 'req' => true, 'opts' => array('Contactado', 'No contesta')),
    array('k' => 'motivo', 'type' => 'text', 'label' => 'Motivo', 'req' => true, 'min' => 3, 'max' => 20,
      'cond' => array('k' => 'resultado', 'v' => 'No contesta')),
    array('k' => 'correo', 'type' => 'email', 'label' => 'Correo'),
    array('k' => 'monto', 'type' => 'number', 'label' => 'Monto', 'min' => 1, 'max' => 100),
    array('k' => 'fecha', 'type' => 'date', 'label' => 'Fecha'),
    array('k' => 'codigo', 'type' => 'text', 'label' => 'Código', 'regex' => '^[A-Z]{2}\d+$'),
    array('k' => 'ubicacion', 'type' => 'nested', 'label' => 'Ubicación', 'lv1' => 'Estado', 'lv2' => 'Ciudad',
      'tree' => array(array('p' => 'Miranda', 'c' => 'Los Teques, Guarenas'))),
  ));
}

test('captura_validar_valores acepta valores válidos y descarta claves desconocidas', function () {
  $r = captura_validar_valores(captura_definicion_ejemplo(), array(
    'resultado' => 'Contactado',
    'correo' => 'ana@dominio.com',
    'monto' => '50',
    'fecha' => '2026-10-30',
    'codigo' => 'AB123',
    'ubicacion' => array('lv1' => 'Miranda', 'lv2' => 'Guarenas'),
    'intruso' => 'x',
  ));

  assert_verdadero($r['ok']);
  assert_igual('Contactado', $r['valores']['resultado']);
  assert_igual(array('lv1' => 'Miranda', 'lv2' => 'Guarenas'), $r['valores']['ubicacion']);
  assert_falso(isset($r['valores']['intruso']));
});

test('captura_validar_valores omite campos ocultos por condición', function () {
  $r = captura_validar_valores(captura_definicion_ejemplo(), array('resultado' => 'Contactado', 'motivo' => 'no aplica'));
  assert_verdadero($r['ok']);
  assert_falso(isset($r['valores']['motivo']));
});

test('captura_validar_valores exige el campo condicional cuando se muestra', function () {
  $r = captura_validar_valores(captura_definicion_ejemplo(), array('resultado' => 'No contesta'));
  assert_falso($r['ok']);
  assert_igual('Campo obligatorio.', $r['errores']['motivo']);
});

test('captura_validar_valores valida tipos, rangos, regex y opciones', function () {
  $r = captura_validar_valores(captura_definicion_ejemplo(), array(
    'resultado' => 'Otra cosa',
    'correo' => 'no-es-correo',
    'monto' => '500',
    'fecha' => '2026-02-30',
    'codigo' => 'ab1',
  ));
  assert_falso($r['ok']);
  assert_igual('Opción inválida.', $r['errores']['resultado']);
  assert_igual('Correo inválido.', $r['errores']['correo']);
  assert_igual('Máximo 100.', $r['errores']['monto']);
  assert_igual('Fecha inválida.', $r['errores']['fecha']);
  assert_igual('Formato inválido.', $r['errores']['codigo']);
});

test('captura_validar_valores valida el anidado contra su árbol', function () {
  $def = captura_definicion_ejemplo();
  $base = array('resultado' => 'Contactado');

  $r1 = captura_validar_valores($def, $base + array('ubicacion' => array('lv1' => 'Miranda', 'lv2' => 'Valencia')));
  assert_igual('Opción inválida.', $r1['errores']['ubicacion']);

  $r2 = captura_validar_valores($def, $base + array('ubicacion' => array('lv1' => 'Miranda', 'lv2' => '')));
  assert_igual('Selecciona ambos niveles.', $r2['errores']['ubicacion']);

  $r3 = captura_validar_valores($def, $base);
  assert_verdadero($r3['ok']);
});

test('captura_validar_valores trata entrada no válida como vacía', function () {
  $r = captura_validar_valores(captura_definicion_ejemplo(), 'basura');
  assert_falso($r['ok']);
  assert_igual('Campo obligatorio.', $r['errores']['resultado']);
});

test('captura_columnas_reporte une versiones y cargas, con etiquetas de lo más reciente', function () {
  $definiciones = array(
    array('campos' => array(array('k' => 'resultado', 'label' => 'Resultado final'), array('k' => 'nota', 'label' => 'Nota'))),
    array('campos' => array(array('k' => 'resultado', 'label' => 'Resultado'), array('k' => 'viejo', 'label' => 'Campo viejo'))),
  );
  $mappings = array(
    array('busqueda' => 'doc', 'columnas' => array(array('k' => 'doc', 'label' => 'Cédula'))),
    array('busqueda' => 'doc', 'columnas' => array(array('k' => 'doc', 'label' => 'Doc'), array('k' => 'tel', 'label' => 'Teléfono'))),
  );

  $c = captura_columnas_reporte($definiciones, $mappings);

  assert_igual(array('doc' => 'Cédula', 'tel' => 'Teléfono'), $c['bd']);
  assert_igual(array('resultado' => 'Resultado final', 'nota' => 'Nota', 'viejo' => 'Campo viejo'), $c['campos']);
});

test('captura_celdas_reporte ordena celdas y aplana el anidado', function () {
  $columnas = array('bd' => array('doc' => 'Cédula'), 'campos' => array('resultado' => 'R', 'ubicacion' => 'U', 'nota' => 'N'));
  $celdas = captura_celdas_reporte(
    array('doc' => 'V-1'),
    array('resultado' => 'Contactado', 'ubicacion' => array('lv1' => 'Miranda', 'lv2' => 'Guarenas')),
    $columnas
  );
  assert_igual(array('V-1', 'Contactado', 'Miranda / Guarenas', ''), $celdas);
});
