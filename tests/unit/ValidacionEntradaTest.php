<?php

/**
 * Pruebas de los helpers de validación de entrada (private/config/security.php).
 */

test('input_string recorta, limita longitud y elimina caracteres de control', function () {
  assert_igual('hola', input_string('  hola  '));
  assert_igual('abc', input_string("a\x00b\x07c"));
  assert_igual('ab', input_string('abcdef', 2));
  assert_igual('', input_string(array('no' => 'es')));
});

test('input_int valida el rango y devuelve el valor por defecto', function () {
  assert_igual(5, input_int('5'));
  assert_igual(5, input_int('5', 1, 10));
  assert_igual(null, input_int('5', 6, 10));
  assert_igual(null, input_int('abc'));
  assert_igual(0, input_int('abc', null, null, 0));
  assert_igual(3, input_int(' 3 ', 0, 10));
});

test('input_enum solo acepta los valores permitidos', function () {
  assert_igual('1', input_enum('1', array(1, 2, 5)));
  assert_igual('5', input_enum(5, array(1, 2, 5)));
  assert_igual(null, input_enum('3', array(1, 2, 5)));
  assert_igual('1', input_enum('3', array(1, 2, 5), '1'));
});

test('input_date exige el formato indicado', function () {
  assert_igual('2026-10-20', input_date('2026-10-20'));
  assert_igual(null, input_date('20/10/2026'));
  assert_igual(null, input_date(''));
  assert_igual('2026-10-20', input_date('2026-10-20', 'Y-m-d'));
});

test('input_pan acepta dígitos y asteriscos hasta 20 caracteres', function () {
  assert_igual('123456789******1234', input_pan('123456789******1234'));
  assert_igual('1234', input_pan('1234'));
  assert_igual(null, input_pan('1234-5678'));
  assert_igual('12345678901234567890', input_pan('123456789012345678901')); // se recorta a 20
});

test('input_email valida el formato de correo', function () {
  assert_igual('usuario@dominio.com', input_email('usuario@dominio.com'));
  assert_igual(null, input_email('usuario@dominio'));
  assert_igual(null, input_email('no es un correo'));
});

test('input_letters deja solo letras y espacios', function () {
  assert_igual('Pedro Luis', input_letters('  Pedro  Luis  '));
  assert_igual('Hola', input_letters('Hola123!'));
});

test('input_digits conserva únicamente dígitos', function () {
  assert_igual('12624422', input_digits('1262-4422'));
  assert_igual('', input_digits('abc'));
});

test('input_amount acepta números con separadores', function () {
  assert_igual('1234.56', input_amount('1234.56'));
  assert_igual(null, input_amount('mil'));
});

test('input_int_array filtra y limita los elementos', function () {
  assert_igual(array(1, 2), input_int_array(array('1', '2', 'x')));
  assert_igual(array(), input_int_array('no es un arreglo'));
});
