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
  assert_igual(array(1, 2, 5), Constantes::servicios());
  assert_igual(array(1, 2, 3), Constantes::tiposUsuario());
});
