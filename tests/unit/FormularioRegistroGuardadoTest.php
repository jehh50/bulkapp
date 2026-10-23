<?php

/**
 * Pruebas del despacho del registro de gestión/venta contra un modelo simulado.
 */

/**
 * Modelo simulado que registra las llamadas recibidas.
 */
class ModeloSimulado
{
  public $llamadas = array();

  public function registroResultados()
  {
    $this->llamadas[] = array('metodo' => 'registroResultados', 'args' => func_get_args());
    return true;
  }

  public function registroGestion()
  {
    $this->llamadas[] = array('metodo' => 'registroGestion', 'args' => func_get_args());
    return 42;
  }

  public function registroResultadosCashea()
  {
    $this->llamadas[] = array('metodo' => 'registroResultadosCashea', 'args' => func_get_args());
    return true;
  }
}

test('formulario_registro_guardar usa registroResultados en la venta bancaria', function () {
  $modelo = new ModeloSimulado();

  $datos = formulario_registro_datos(
    array(
      'servicio' => '1',
      'id_cliente' => '123',
      'contacto' => '1',
      'efectivo' => '1',
      'noefectivo' => '2',
      'venta' => '7',
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
    ),
    array('id' => '9')
  );

  $resultado = formulario_registro_guardar($modelo, $datos);

  assert_igual(1, count($modelo->llamadas));
  assert_igual('registroResultados', $modelo->llamadas[0]['metodo']);
  assert_igual(true, $resultado);
});

test('formulario_registro_guardar usa registroGestion cuando no hay venta', function () {
  $modelo = new ModeloSimulado();

  $datos = formulario_registro_datos(
    array(
      'servicio' => '1',
      'id_cliente' => '123',
      'contacto' => '0',
      'noefectivo' => '2',
      'venta' => '7',
    ),
    array('id' => '9')
  );

  formulario_registro_guardar($modelo, $datos);

  assert_igual(1, count($modelo->llamadas));
  assert_igual('registroGestion', $modelo->llamadas[0]['metodo']);
});

test('formulario_registro_guardar registra gestión y resultado en Cashea', function () {
  $modelo = new ModeloSimulado();

  $datos = formulario_registro_datos(
    array(
      'servicio' => '2',
      'id_cliente' => '123',
      'contacto' => '1',
      'efectivo' => '1',
      'noefectivo' => '2',
      'subContacto' => '10',
      'venta' => '7',
      'paymentPlan' => '3',
      'amount' => '500',
      'fullName' => 'Maria Perez',
      'relationship' => 'Hija',
      'paymentDate' => '2026-10-21',
      'idQuote' => array('10|500'),
    ),
    array('id' => '9')
  );

  formulario_registro_guardar($modelo, $datos);

  assert_igual(2, count($modelo->llamadas));
  assert_igual('registroGestion', $modelo->llamadas[0]['metodo']);
  assert_igual('registroResultadosCashea', $modelo->llamadas[1]['metodo']);
  assert_igual(42, $modelo->llamadas[1]['args'][9]);
});
