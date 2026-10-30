<?php

/**
 * Pruebas del validador/normalizador de definiciones del Constructor
 * (private/helpers/constructorDefinicion.php).
 */

/**
 * @param array $campos
 * @return array
 */
function def($campos)
{
  return array('campos' => $campos);
}

test('constructor_decodificar_definicion acepta JSON válido y rechaza basura', function () {
  assert_es_array(constructor_decodificar_definicion('{"campos":[]}'));
  assert_igual(null, constructor_decodificar_definicion('no es json'));
  assert_igual(null, constructor_decodificar_definicion(''));
  assert_igual(null, constructor_decodificar_definicion(null));
});

test('una definición mínima válida pasa y se normaliza', function () {
  $r = constructor_validar_definicion(def(array(
    array('k' => 'cedula', 'type' => 'text', 'label' => 'Cédula'),
  )));

  assert_verdadero($r['ok']);
  assert_igual(0, count($r['errores']));
  assert_igual('cedula', $r['definicion']['campos'][0]['k']);
});

test('falla si no hay campos o la estructura es inválida', function () {
  assert_falso(constructor_validar_definicion(def(array()))['ok']);
  assert_falso(constructor_validar_definicion(array('otra' => 1))['ok']);
});

test('rechaza clave inválida, tipo inválido y etiqueta vacía', function () {
  $r = constructor_validar_definicion(def(array(
    array('k' => '1malo', 'type' => 'text', 'label' => 'X'),
    array('k' => 'ok', 'type' => 'inexistente', 'label' => 'Y'),
    array('k' => 'sinlabel', 'type' => 'text', 'label' => '   '),
  )));

  assert_falso($r['ok']);
  assert_igual(3, count($r['errores']));
});

test('rechaza claves repetidas', function () {
  $r = constructor_validar_definicion(def(array(
    array('k' => 'dup', 'type' => 'text', 'label' => 'A'),
    array('k' => 'dup', 'type' => 'text', 'label' => 'B'),
  )));

  assert_falso($r['ok']);
});

test('un select requiere al menos una opción', function () {
  $malo = constructor_validar_definicion(def(array(
    array('k' => 'plan', 'type' => 'select', 'label' => 'Plan', 'opts' => array()),
  )));
  assert_falso($malo['ok']);

  $bueno = constructor_validar_definicion(def(array(
    array('k' => 'plan', 'type' => 'select', 'label' => 'Plan', 'opts' => array('A', ' ', 'B')),
  )));
  assert_verdadero($bueno['ok']);
  assert_igual(2, count($bueno['definicion']['campos'][0]['opts'])); // la opción vacía se descarta
});

test('un campo anidado requiere al menos un nodo padre', function () {
  $malo = constructor_validar_definicion(def(array(
    array('k' => 'ubic', 'type' => 'nested', 'label' => 'Ubicación', 'tree' => array()),
  )));
  assert_falso($malo['ok']);

  $bueno = constructor_validar_definicion(def(array(
    array('k' => 'ubic', 'type' => 'nested', 'label' => 'Ubicación',
      'tree' => array(array('p' => 'Miranda', 'c' => 'Los Teques, Guarenas'))),
  )));
  assert_verdadero($bueno['ok']);
});

test('cond debe referenciar un campo anterior', function () {
  $adelante = constructor_validar_definicion(def(array(
    array('k' => 'plan', 'type' => 'select', 'label' => 'Plan', 'opts' => array('A'),
      'cond' => array('k' => 'tipo', 'v' => 'nuevo')),           // «tipo» aún no existe
    array('k' => 'tipo', 'type' => 'text', 'label' => 'Tipo'),
  )));
  assert_falso($adelante['ok']);

  $atras = constructor_validar_definicion(def(array(
    array('k' => 'tipo', 'type' => 'text', 'label' => 'Tipo'),
    array('k' => 'plan', 'type' => 'select', 'label' => 'Plan', 'opts' => array('A'),
      'cond' => array('k' => 'tipo', 'v' => 'nuevo')),
  )));
  assert_verdadero($atras['ok']);
  assert_igual('tipo', $atras['definicion']['campos'][1]['cond']['k']);
});

test('rechaza regex inválida y min mayor que max', function () {
  $regex = constructor_validar_definicion(def(array(
    array('k' => 'c', 'type' => 'text', 'label' => 'C', 'regex' => '([a-z'),
  )));
  assert_falso($regex['ok']);

  $rango = constructor_validar_definicion(def(array(
    array('k' => 'n', 'type' => 'number', 'label' => 'N', 'min' => 10, 'max' => 5),
  )));
  assert_falso($rango['ok']);
});

test('la normalización descarta claves desconocidas', function () {
  $r = constructor_validar_definicion(def(array(
    array('k' => 'c', 'type' => 'text', 'label' => 'C', 'intruso' => 'x', 'req' => true),
  )));

  assert_verdadero($r['ok']);
  assert_falso(isset($r['definicion']['campos'][0]['intruso']));
  assert_verdadero($r['definicion']['campos'][0]['req']);
});
