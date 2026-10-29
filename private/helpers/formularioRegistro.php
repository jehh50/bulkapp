<?php

/**
 * Helpers del registro de gestión/venta del formulario de captura.
 *
 * Separan el armado y la validación de los datos (funciones puras, sin base de
 * datos) de la escritura (funciones que reciben el modelo). Así el controlador
 * queda corto y la lógica se puede probar de forma aislada.
 */

/**
 * Datos comunes a cualquier servicio.
 *
 * @param array $post
 * @param array $session
 * @return array|false Datos comunes o false si faltan campos obligatorios.
 */
function formulario_registro_datos_base($post, $session)
{
  $servicio = input_enum(isset($post['servicio']) ? $post['servicio'] : null, Constantes::servicios());
  $usuario = input_int(isset($session['id']) ? $session['id'] : null, 1);
  $idCliente = input_int(isset($post['id_cliente']) ? $post['id_cliente'] : null, 1);
  $contacto = input_int(isset($post['contacto']) ? $post['contacto'] : null, 0, 1);

  if ($servicio === null || $usuario === null || $idCliente === null || $contacto === null) {
    return false;
  }

  $efectivo = isset($post['efectivo']) ? input_int($post['efectivo'], 0) : null;
  $noefectivo = isset($post['noefectivo']) ? input_int($post['noefectivo'], 0) : null;

  $status = null;
  if ($noefectivo !== null) {
    $status = Constantes::ESTADO_NO_EFECTIVO;
  } elseif ($efectivo !== null) {
    $status = Constantes::ESTADO_EFECTIVO;
  }

  return array(
    'servicio' => (int) $servicio,
    'usuario' => $usuario,
    'id_cliente' => $idCliente,
    'contacto' => $contacto,
    'fecha' => date('Y-m-d'),
    'hora' => date('H:i:s'),
    'gestion' => isset($post['gestion']) ? input_string($post['gestion'], 255) : null,
    'efectivo' => $efectivo,
    'noefectivo' => $noefectivo,
    'status' => $status,
    'producto' => isset($post['venta']) ? input_int($post['venta'], 0) : null,
    'sub_contacto' => isset($post['subContacto']) ? input_int($post['subContacto'], 0) : null,
    'dni' => isset($post['dni_cliente']) ? input_string($post['dni_cliente'], 30) : null,
    'id_quote' => formulario_registro_id_quotes(isset($post['idQuote']) ? $post['idQuote'] : null),
    'payment_date' => isset($post['paymentDate']) ? input_date($post['paymentDate'], 'Y-m-d') : null,
  );
}

/**
 * Normaliza el arreglo de cuotas (idQuote) recibido del formulario.
 *
 * @param mixed $idQuote
 * @return array|null
 */
function formulario_registro_id_quotes($idQuote)
{
  if (!is_array($idQuote)) {
    return null;
  }

  $resultado = array();
  foreach ($idQuote as $quote) {
    $resultado[] = input_string($quote, 100);
  }

  return $resultado;
}

/**
 * Datos del servicio bancario (servicios 1 y 5).
 *
 * @param array $post
 * @param array $base
 * @return array|false
 */
function formulario_registro_datos_bancario($post, array $base)
{
  $datos = $base;

  $cuenta = isset($post['pan']) ? input_pan($post['pan']) : null;
  $datos['cuenta'] = ($cuenta === null) ? '00000000000000000000' : $cuenta;
  $datos['nacionalidad'] = input_enum(isset($post['nacionalidad']) ? $post['nacionalidad'] : null, array(1, 2, 3));
  $datos['edificacion'] = isset($post['edificacion']) ? input_string($post['edificacion'], 255) : null;
  $datos['tipo_cuenta'] = isset($post['tipo_cuenta']) ? input_int($post['tipo_cuenta'], 1) : null;

  if ((int) $base['efectivo'] !== 1) {
    return $datos;
  }

  $datos['nombre'] = input_letters(isset($post['nombre2']) ? $post['nombre2'] : '', 60);
  $datos['apellido'] = input_letters(isset($post['apellido2']) ? $post['apellido2'] : '', 60);
  $datos['genero'] = input_enum(isset($post['genero']) ? $post['genero'] : null, array('M', 'F'));
  $datos['fecha_nac'] = input_string(isset($post['fecha_nac']) ? $post['fecha_nac'] : '', 10);
  $datos['cedula'] = input_digits(isset($post['cedula2']) ? $post['cedula2'] : '', 10);
  $datos['correo'] = input_email(isset($post['correo2']) ? $post['correo2'] : '', 150);
  $datos['telf_hab'] = input_phone(isset($post['telf_hab']) ? $post['telf_hab'] : '');
  $datos['telf_cel'] = input_phone(isset($post['telf_cel']) ? $post['telf_cel'] : '');
  $datos['estado'] = input_int(isset($post['estado']) ? $post['estado'] : null, 1);
  $datos['ciudad'] = input_int(isset($post['ciudad']) ? $post['ciudad'] : null, 1);
  $datos['municipio'] = input_int(isset($post['municipio']) ? $post['municipio'] : null, 1);
  $datos['observaciones'] = input_string(isset($post['observaciones']) ? $post['observaciones'] : '', 1000);
  $datos['fecha_venta'] = date('Ymd');
  $datos['status'] = Constantes::ESTADO_ATENDIDO;
  $datos['var'] = 0;

  if (!formulario_registro_venta_valida($datos)) {
    return false;
  }

  return $datos;
}

/**
 * Valida los datos de la venta capturada en el formulario bancario.
 *
 * @param array $datos
 * @return bool
 */
function formulario_registro_venta_valida(array $datos)
{
  $fechaValida = (bool) preg_match('/^\d{2}-\d{2}-\d{4}$/', $datos['fecha_nac'])
    || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_nac']);

  return $datos['nombre'] !== ''
    && $datos['apellido'] !== ''
    && $datos['genero'] !== null
    && $fechaValida
    && $datos['cedula'] !== ''
    && $datos['correo'] !== null
    && $datos['telf_hab'] !== null
    && $datos['telf_cel'] !== null
    && $datos['estado'] !== null
    && $datos['ciudad'] !== null
    && $datos['municipio'] !== null
    && $datos['nacionalidad'] !== null
    && $datos['tipo_cuenta'] !== null;
}

/**
 * Datos completos del registro según el servicio.
 *
 * @param array $post
 * @param array $session
 * @return array|false
 */
function formulario_registro_datos($post, $session)
{
  $base = formulario_registro_datos_base($post, $session);
  if ($base === false) {
    return false;
  }

  if (Constantes::esServicioBancario($base['servicio'])) {
    return formulario_registro_datos_bancario($post, $base);
  }

  return false;
}

/**
 * Escribe el registro de gestión/venta.
 *
 * @param object $conn Modelo del módulo (clase database).
 * @param array $datos
 * @return mixed Resultado de la operación.
 */
function formulario_registro_guardar($conn, array $datos)
{
  if (Constantes::esServicioBancario($datos['servicio'])) {
    return formulario_registro_guardar_bancario($conn, $datos);
  }

  return false;
}

/**
 * @param object $conn
 * @param array $datos
 * @return mixed
 */
function formulario_registro_guardar_bancario($conn, array $datos)
{
  if ((int) $datos['efectivo'] === 1) {
    return $conn->registroResultados(
      $datos['contacto'],
      $datos['efectivo'],
      $datos['producto'],
      $datos['noefectivo'],
      $datos['usuario'],
      $datos['fecha'],
      $datos['nombre'],
      $datos['apellido'],
      $datos['genero'],
      $datos['fecha_nac'],
      $datos['nacionalidad'],
      $datos['cedula'],
      $datos['telf_hab'],
      $datos['telf_cel'],
      $datos['correo'],
      $datos['estado'],
      $datos['ciudad'],
      $datos['municipio'],
      $datos['cuenta'],
      $datos['tipo_cuenta'],
      $datos['observaciones'],
      $datos['fecha_venta'],
      $datos['status'],
      $datos['id_cliente'],
      $datos['var'],
      $datos['hora'],
      $datos['servicio']
    );
  }

  return formulario_registro_guardar_gestion($conn, $datos);
}

/**
 * Registra la gestión (sin venta) del cliente.
 *
 * @param object $conn
 * @param array $datos
 * @return mixed
 */
function formulario_registro_guardar_gestion($conn, array $datos)
{
  return $conn->registroGestion(
    $datos['contacto'],
    $datos['efectivo'],
    $datos['producto'],
    $datos['noefectivo'],
    $datos['sub_contacto'],
    $datos['usuario'],
    $datos['fecha'],
    $datos['id_cliente'],
    $datos['status'],
    $datos['hora'],
    $datos['servicio'],
    $datos['dni'],
    $datos['id_quote'],
    $datos['gestion']
  );
}
