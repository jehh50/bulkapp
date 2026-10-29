<?php

/**
 * Pruebas del armado de datos del registro de gestión/venta (formulario de captura).
 */

/**
 * @return array
 */
function datos_post_base()
{
  return array(
    'servicio' => '1',
    'id_cliente' => '123',
    'contacto' => '1',
    'efectivo' => '1',
    'noefectivo' => '2',
    'venta' => '7',
    'gestion' => 'llamada',
    'dni_cliente' => '12624422',
  );
}

/**
 * @return array
 */
function sesion_base()
{
  return array('id' => '9', 'servicio_id' => '1', 'usertype_id' => '2');
}

test('formulario_registro_datos_base arma los datos comunes', function () {
  $datos = formulario_registro_datos_base(datos_post_base(), sesion_base());

  assert_es_array($datos);
  assert_igual(1, $datos['servicio']);
  assert_igual(9, $datos['usuario']);
  assert_igual(123, $datos['id_cliente']);
  assert_igual(1, $datos['contacto']);
  assert_igual(7, $datos['producto']);
  assert_igual('llamada', $datos['gestion']);
});

test('formulario_registro_datos_base calcula el estado según el tipo de contacto', function () {
  $post = datos_post_base();
  unset($post['noefectivo']);
  $datos = formulario_registro_datos_base($post, sesion_base());
  assert_igual(Constantes::ESTADO_EFECTIVO, $datos['status']);

  $post = datos_post_base();
  unset($post['efectivo']);
  $datos = formulario_registro_datos_base($post, sesion_base());
  assert_igual(Constantes::ESTADO_NO_EFECTIVO, $datos['status']);
});

test('formulario_registro_datos_base rechaza datos incompletos', function () {
  $post = datos_post_base();
  unset($post['id_cliente']);
  assert_falso(formulario_registro_datos_base($post, sesion_base()));

  $post = datos_post_base();
  $post['servicio'] = '9';
  assert_falso(formulario_registro_datos_base($post, sesion_base()));
});

test('formulario_registro_datos_bancario valida la venta capturada', function () {
  $base = formulario_registro_datos_base(datos_post_base(), sesion_base());

  $post = array(
    'nombre2' => 'Pedro Luis',
    'apellido2' => 'Perez Gomez',
    'genero' => 'M',
    'fecha_nac' => '10-05-1985',
    'cedula2' => '12624422',
    'correo2' => 'pedro@dominio.com',
    'telf_hab' => '(0212)345.67.89',
    'telf_cel' => '(0424)234.56.78',
    'estado' => '1',
    'ciudad' => '2',
    'municipio' => '3',
    'nacionalidad' => '1',
    'tipo_cuenta' => '1',
    'pan' => '123456789******1234',
  );

  $datos = formulario_registro_datos_bancario($post, $base);

  assert_es_array($datos);
  assert_igual(Constantes::ESTADO_ATENDIDO, $datos['status']);
  assert_igual('123456789******1234', $datos['cuenta']);
  assert_igual('Pedro Luis', $datos['nombre']);
});

test('formulario_registro_datos_bancario rechaza una venta incompleta', function () {
  $base = formulario_registro_datos_base(datos_post_base(), sesion_base());

  $post = array(
    'nombre2' => 'Pedro',
    'apellido2' => '',
    'genero' => 'M',
    'fecha_nac' => '10-05-1985',
    'cedula2' => '12624422',
    'correo2' => 'no es un correo',
    'telf_hab' => '(0212)345.67.89',
    'telf_cel' => '(0424)234.56.78',
    'estado' => '1',
    'ciudad' => '2',
    'municipio' => '3',
    'nacionalidad' => '1',
    'tipo_cuenta' => '1',
  );

  assert_falso(formulario_registro_datos_bancario($post, $base));
});

test('formulario_registro_datos_bancario usa el PAN por defecto cuando no se envía', function () {
  $base = formulario_registro_datos_base(datos_post_base(), sesion_base());
  $base['efectivo'] = null;

  $datos = formulario_registro_datos_bancario(array(), $base);

  assert_igual('00000000000000000000', $datos['cuenta']);
});

test('formulario_registro_datos enruta según el servicio', function () {
  $post = datos_post_base();
  unset($post['efectivo']); // gestión sin venta: no exige los datos de la ficha del cliente
  unset($post['noefectivo']);

  $datos = formulario_registro_datos($post, sesion_base());
  assert_es_array($datos);
  assert_igual(1, $datos['servicio']);

  $post = array_merge($post, array('servicio' => '9'));
  assert_falso(formulario_registro_datos($post, sesion_base()));
});
