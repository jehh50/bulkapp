<?php

/**
 * Clientes (empresas) y campañas — helpers compartidos.
 *
 * - Validación pura (sin BD) de empresas y campañas: la usa empresasController y los tests.
 * - Etiqueta «Empresa › Campaña» para los selectores (PHP y SQL).
 * - Mapa id → tipo_flujo que alimenta Constantes::esServicioBancario()/servicios().
 * - Campañas asignadas a un usuario (selector de campaña de la navbar).
 *
 * Una campaña ES un registro de `servicios`: su id sigue siendo `servicio_id` en todo el
 * sistema. El nombre de la campaña vive en `servicios.descripcion`.
 */

/** Separador visual entre empresa y campaña. */
const CAMPANAS_SEPARADOR = ' › ';

/**
 * Etiqueta de una campaña para mostrar en selectores.
 * Si la campaña se llama igual que su empresa (caso de las campañas históricas) se
 * muestra solo el nombre, para no repetir «Banco Activo › Banco Activo».
 *
 * @param string|null $empresa
 * @param string $campana
 * @return string
 */
function campanas_etiqueta($empresa, $campana)
{
  $campana = trim((string) $campana);
  $empresa = trim((string) $empresa);

  if ($empresa === '' || mb_strtolower($empresa) === mb_strtolower($campana)) {
    return $campana;
  }

  return $empresa . CAMPANAS_SEPARADOR . $campana;
}

/**
 * Expresión SQL equivalente a campanas_etiqueta() (alias `s` = servicios, `e` = empresas).
 *
 * @param string $s Alias de servicios.
 * @param string $e Alias de empresas.
 * @return string
 */
function campanas_sql_etiqueta($s = 's', $e = 'e')
{
  return "IF($e.nombre IS NULL OR LOWER(TRIM($e.nombre)) = LOWER(TRIM($s.descripcion)), "
    . "TRIM($s.descripcion), CONCAT(TRIM($e.nombre), '" . CAMPANAS_SEPARADOR . "', TRIM($s.descripcion)))";
}

/**
 * Normaliza un RIF venezolano (J-12345678-9). Devuelve '' si viene vacío y null si es inválido.
 *
 * @param mixed $valor
 * @return string|null
 */
function campanas_normalizar_rif($valor)
{
  $valor = strtoupper(preg_replace('/[\s.]/', '', (string) $valor));
  if ($valor === '') {
    return '';
  }
  if (!preg_match('/^([VEJPG])-?(\d{6,8})-?(\d)?$/', $valor, $m)) {
    return null;
  }

  return $m[1] . '-' . $m[2] . (isset($m[3]) && $m[3] !== '' ? '-' . $m[3] : '');
}

/**
 * Valida los datos de una empresa.
 *
 * @param array $entrada nombre, identificacion, status_id
 * @return array { ok: bool, errores: string[], datos: array }
 */
function campanas_validar_empresa(array $entrada)
{
  $errores = array();

  $nombre = trim(preg_replace('/\s+/', ' ', (string) (isset($entrada['nombre']) ? $entrada['nombre'] : '')));
  if ($nombre === '') {
    $errores[] = 'El nombre del cliente es obligatorio.';
  } elseif (mb_strlen($nombre) > 100) {
    $errores[] = 'El nombre del cliente admite hasta 100 caracteres.';
  }

  $rif = campanas_normalizar_rif(isset($entrada['identificacion']) ? $entrada['identificacion'] : '');
  if ($rif === null) {
    $errores[] = 'El RIF no es válido (formato J-12345678-9).';
  }

  $estado = input_enum(
    isset($entrada['status_id']) ? $entrada['status_id'] : null,
    array(Constantes::REGISTRO_ACTIVO, Constantes::REGISTRO_INACTIVO),
    Constantes::REGISTRO_ACTIVO
  );

  return array(
    'ok' => empty($errores),
    'errores' => $errores,
    'datos' => array(
      'nombre' => $nombre,
      'identificacion' => ($rif === null || $rif === '') ? null : $rif,
      'status_id' => (int) $estado,
    ),
  );
}

/**
 * Valida los datos de una campaña.
 * El flujo solo se fija al crear: cambiarlo después re-enrutaría su historial.
 * El código es obligatorio en campañas bancarias (lo usa el XML como company code).
 *
 * @param array $entrada nombre, detalle, fecha_inicio, meta, tipo_flujo, codigo, status_id
 * @param bool $esNueva
 * @return array { ok: bool, errores: string[], datos: array }
 */
function campanas_validar_campana(array $entrada, $esNueva)
{
  $errores = array();
  $get = function ($k) use ($entrada) {
    return isset($entrada[$k]) ? $entrada[$k] : null;
  };

  $nombre = trim(preg_replace('/\s+/', ' ', (string) $get('nombre')));
  if ($nombre === '') {
    $errores[] = 'El nombre de la campaña es obligatorio.';
  } elseif (mb_strlen($nombre) > 50) {
    $errores[] = 'El nombre de la campaña admite hasta 50 caracteres.';
  }

  $detalle = trim((string) $get('detalle'));
  if (mb_strlen($detalle) > 500) {
    $errores[] = 'La descripción admite hasta 500 caracteres.';
  }

  $fecha = input_date($get('fecha_inicio'));
  if ($fecha === null) {
    $errores[] = 'La fecha de inicio es obligatoria (AAAA-MM-DD).';
  }

  $metaRaw = trim((string) $get('meta'));
  $meta = null;
  if ($metaRaw !== '') {
    $meta = input_int($metaRaw, 1, 100000000);
    if ($meta === null) {
      $errores[] = 'La meta debe ser un número entero mayor que cero.';
    }
  }

  $flujo = null;
  if ($esNueva) {
    $flujo = input_enum($get('tipo_flujo'), Constantes::flujos());
    if ($flujo === null) {
      $errores[] = 'Selecciona el tipo de flujo de la campaña.';
    }
  }

  $codigo = strtoupper(trim((string) $get('codigo')));
  if (mb_strlen($codigo) > 10 || ($codigo !== '' && !preg_match('/^[A-Z0-9_-]+$/', $codigo))) {
    $errores[] = 'El código admite hasta 10 letras, dígitos, guion o guion bajo.';
  } elseif ($esNueva && $flujo === Constantes::FLUJO_BANCARIO && $codigo === '') {
    $errores[] = 'Las campañas bancarias requieren código (company code del XML).';
  }

  $estado = input_enum(
    $get('status_id'),
    array(Constantes::REGISTRO_ACTIVO, Constantes::REGISTRO_INACTIVO),
    Constantes::REGISTRO_ACTIVO
  );

  $datos = array(
    'nombre' => $nombre,
    'detalle' => ($detalle === '') ? null : $detalle,
    'fecha_inicio' => $fecha,
    'meta' => $meta,
    'codigo' => $codigo,
    'status_id' => (int) $estado,
  );
  if ($esNueva) {
    $datos['tipo_flujo'] = $flujo;
  }

  return array('ok' => empty($errores), 'errores' => $errores, 'datos' => $datos);
}

/**
 * Lee de la BD el mapa id → tipo_flujo de todas las campañas.
 * Devuelve null si no hay conexión o la migración aún no se aplicó (Constantes usa
 * entonces el mapa histórico).
 *
 * @return array|null
 */
function campanas_flujos_desde_bd()
{
  if (!class_exists('Conexion')) {
    return null;
  }

  try {
    $db = new Conexion();
    $filas = $db->selectQuery('SELECT id, tipo_flujo FROM servicios');
  } catch (Throwable $e) {
    if (function_exists('app_log')) {
      app_log('warning', 'No se pudo leer tipo_flujo de servicios; se usa el mapa histórico', array(
        'event' => 'campanas.flujos_fallback',
        'error' => $e->getMessage(),
      ));
    }
    return null;
  }

  $mapa = array();
  foreach ($filas as $f) {
    $mapa[(int) $f['id']] = (string) $f['tipo_flujo'];
  }

  return empty($mapa) ? null : $mapa;
}

/**
 * Campañas activas (de empresas activas) asignadas a un usuario, para el selector de la
 * navbar. Incluye siempre la principal si está activa.
 *
 * @param int $userId
 * @return array Lista de { id, etiqueta }
 */
function campanas_de_usuario($userId)
{
  if (!class_exists('Conexion')) {
    return array();
  }

  try {
    $db = new Conexion();
    return $db->selectQuery(
      'SELECT s.id, ' . campanas_sql_etiqueta() . ' AS etiqueta
         FROM usuario_servicios us
         JOIN servicios s ON s.id = us.servicio_id
         JOIN empresas e ON e.id = s.empresa_id
        WHERE us.user_id = ? AND s.status_id = ? AND e.status_id = ?
        ORDER BY etiqueta',
      array((int) $userId, Constantes::REGISTRO_ACTIVO, Constantes::REGISTRO_ACTIVO),
      'iii'
    );
  } catch (Throwable $e) {
    if (function_exists('app_log')) {
      app_log('warning', 'No se pudieron leer las campañas del usuario', array(
        'event' => 'campanas.usuario_error',
        'user_id' => (int) $userId,
        'error' => $e->getMessage(),
      ));
    }
    return array();
  }
}

/**
 * Refresca en sesión las campañas del usuario (la navbar las lee de ahí).
 *
 * @param int $userId
 * @return array
 */
function campanas_sesion_refrescar($userId)
{
  $_SESSION['campanas'] = campanas_de_usuario($userId);

  return $_SESSION['campanas'];
}
