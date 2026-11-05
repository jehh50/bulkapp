<?php

/**
 * Pruebas de las constantes de dominio.
 */

test('Constantes::servicioBase normaliza el alias 5 a 1', function () {
  assert_igual(1, Constantes::servicioBase(5));
  assert_igual(1, Constantes::servicioBase(1));
  assert_igual(2, Constantes::servicioBase(2));
});

test('Constantes::esServicioBancario reconoce 1 y 5', function () {
  assert_verdadero(Constantes::esServicioBancario(1));
  assert_verdadero(Constantes::esServicioBancario(5));
  assert_falso(Constantes::esServicioBancario(2));
});

test('Constantes::servicios y tiposUsuario devuelven las listas permitidas', function () {
  assert_igual(array(1, 5), Constantes::servicios());
  assert_igual(array(1, 2, 3), Constantes::tiposUsuario());
});

test('Constantes: sin BD usa el mapa histórico de flujos (1 y 5 bancarios)', function () {
  Constantes::definirFlujosCampana(null);
  assert_igual(array(1, 5), Constantes::serviciosBancarios());
  assert_igual(array('bancario', 'dinamico'), Constantes::flujos());
});

test('Constantes: el flujo de una campaña nueva sale del mapa de la BD', function () {
  Constantes::definirFlujosCampana(array(1 => 'bancario', 2 => 'dinamico', 5 => 'bancario', 9 => 'bancario', 12 => 'dinamico'));
  try {
    assert_verdadero(Constantes::esServicioBancario(9));
    assert_verdadero(Constantes::esServicioBancario('9'));
    assert_falso(Constantes::esServicioBancario(12));
    assert_falso(Constantes::esServicioBancario(99)); // inexistente
    assert_igual(1, Constantes::servicioBase(9));     // catálogos de banca
    assert_igual(12, Constantes::servicioBase(12));
    assert_igual(array(1, 2, 5, 9, 12), Constantes::servicios());
    assert_igual(array(1, 5, 9), Constantes::serviciosBancarios());
  } finally {
    Constantes::definirFlujosCampana(null);
  }
});
