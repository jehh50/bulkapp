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

/**
 * Catálogo de expresiones de prueba (mismo formato que formulario_expresiones).
 *
 * @return array
 */
function catalogo_expresiones()
{
  return constructor_indexar_expresiones(array(
    array('clave' => 'solo_numeros', 'patron' => '^[0-9]+$', 'transformacion' => null, 'tipos' => 'text,textarea'),
    array('clave' => 'mayusculas', 'patron' => null, 'transformacion' => 'mayusculas', 'tipos' => 'text, textarea'),
    array('clave' => 'rota', 'patron' => '([a-z', 'transformacion' => null, 'tipos' => 'text'),
  ));
}

test('expr del catálogo copia regex/transform en la definición', function () {
  $r = constructor_validar_definicion(def(array(
    array('k' => 'doc', 'type' => 'text', 'label' => 'Doc', 'expr' => 'solo_numeros'),
    array('k' => 'nom', 'type' => 'textarea', 'label' => 'Nombre', 'expr' => 'mayusculas'),
  )), catalogo_expresiones());

  assert_verdadero($r['ok']);
  assert_igual('^[0-9]+$', $r['definicion']['campos'][0]['regex']);
  assert_falso(isset($r['definicion']['campos'][0]['transform']));
  assert_igual('mayusculas', $r['definicion']['campos'][1]['transform']);
  assert_falso(isset($r['definicion']['campos'][1]['regex']));
});

test('rechaza expr inexistente, de otro tipo o con patrón roto; descarta regex libre', function () {
  $cat = catalogo_expresiones();
  assert_falso(constructor_validar_definicion(def(array(
    array('k' => 'c', 'type' => 'text', 'label' => 'C', 'expr' => 'no_existe'),
  )), $cat)['ok']);
  assert_falso(constructor_validar_definicion(def(array(
    array('k' => 'n', 'type' => 'number', 'label' => 'N', 'expr' => 'solo_numeros'),
  )), $cat)['ok']);
  assert_falso(constructor_validar_definicion(def(array(
    array('k' => 'c', 'type' => 'text', 'label' => 'C', 'expr' => 'rota'),
  )), $cat)['ok']);

  $libre = constructor_validar_definicion(def(array(
    array('k' => 'c', 'type' => 'text', 'label' => 'C', 'regex' => '^x$'),
  )), $cat);
  assert_verdadero($libre['ok']);
  assert_falso(isset($libre['definicion']['campos'][0]['regex']));
});

test('rechaza min mayor que max', function () {
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
