<?php

/**
 * Runtime de formularios dinámicos (Constructor, Fase 3).
 *
 * Validación en servidor de los valores capturados (espejo de la validación del
 * navegador en public/js/formDinamico.js) y armado de las columnas/filas de la
 * exportación CSV de envíos.
 */

/** Longitud máxima aceptada para un valor de texto capturado. */
const CAPTURA_MAX_VALOR = 2000;

/**
 * Valida los valores enviados contra la definición del formulario.
 *
 * Los campos ocultos por su condición no se validan ni se guardan. Las claves
 * desconocidas se descartan.
 *
 * @param array $definicion { campos: [...] } normalizada por el Constructor.
 * @param mixed $valores    { k: valor } enviado por el cliente (nested: {lv1, lv2}).
 * @return array ['ok'=>bool, 'errores'=>{k: mensaje}, 'valores'=>{k: valor}]
 */
function captura_validar_valores(array $definicion, $valores)
{
  if (!is_array($valores)) {
    $valores = array();
  }
  $campos = isset($definicion['campos']) && is_array($definicion['campos']) ? $definicion['campos'] : array();

  $errores = array();
  $limpios = array();

  foreach ($campos as $c) {
    $k = $c['k'];

    if (!empty($c['cond']) && !captura_condicion_cumplida($c['cond'], $limpios)) {
      continue;
    }

    if ($c['type'] === 'nested') {
      $v = (isset($valores[$k]) && is_array($valores[$k])) ? $valores[$k] : array();
      $lv1 = isset($v['lv1']) ? trim((string) $v['lv1']) : '';
      $lv2 = isset($v['lv2']) ? trim((string) $v['lv2']) : '';
      $error = captura_validar_anidado($c, $lv1, $lv2);
      if ($error !== null) {
        $errores[$k] = $error;
        continue;
      }
      if ($lv1 !== '' || $lv2 !== '') {
        $limpios[$k] = array('lv1' => $lv1, 'lv2' => $lv2);
      }
      continue;
    }

    $valor = (isset($valores[$k]) && is_scalar($valores[$k])) ? trim((string) $valores[$k]) : '';
    $error = captura_validar_simple($c, $valor);
    if ($error !== null) {
      $errores[$k] = $error;
      continue;
    }
    if ($valor !== '') {
      $limpios[$k] = $valor;
    }
  }

  return array('ok' => empty($errores), 'errores' => $errores, 'valores' => $limpios);
}

/**
 * ¿Se cumple la condición de visibilidad de un campo? (referencia un campo previo).
 *
 * @param array $cond { k, v }
 * @param array $limpios Valores ya validados de los campos anteriores.
 * @return bool
 */
function captura_condicion_cumplida(array $cond, array $limpios)
{
  $ref = isset($limpios[$cond['k']]) ? $limpios[$cond['k']] : '';
  if (is_array($ref)) {
    $ref = isset($ref['lv1']) ? $ref['lv1'] : '';
  }
  return (string) $ref === (string) (isset($cond['v']) ? $cond['v'] : '');
}

/**
 * Valida un campo de valor simple (text, textarea, number, date, email, select).
 *
 * @param array $c Definición del campo.
 * @param string $valor Valor recortado.
 * @return string|null Mensaje de error o null.
 */
function captura_validar_simple(array $c, $valor)
{
  if ($valor === '') {
    return !empty($c['req']) ? 'Campo obligatorio.' : null;
  }
  if (mb_strlen($valor, 'UTF-8') > CAPTURA_MAX_VALOR) {
    return 'El valor es demasiado largo.';
  }

  switch ($c['type']) {
    case 'email':
      if (filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
        return 'Correo inválido.';
      }
      break;
    case 'number':
      if (!is_numeric($valor)) {
        return 'Número inválido.';
      }
      $n = (float) $valor;
      if (isset($c['min']) && $n < $c['min']) {
        return 'Mínimo ' . $c['min'] . '.';
      }
      if (isset($c['max']) && $n > $c['max']) {
        return 'Máximo ' . $c['max'] . '.';
      }
      break;
    case 'date':
      $d = DateTime::createFromFormat('!Y-m-d', $valor);
      if ($d === false || $d->format('Y-m-d') !== $valor) {
        return 'Fecha inválida.';
      }
      break;
    case 'select':
      if (!in_array($valor, isset($c['opts']) ? $c['opts'] : array(), true)) {
        return 'Opción inválida.';
      }
      break;
    default: // text, textarea
      $largo = mb_strlen($valor, 'UTF-8');
      if (isset($c['min']) && $largo < $c['min']) {
        return 'Mínimo ' . $c['min'] . ' caracteres.';
      }
      if (isset($c['max']) && $largo > $c['max']) {
        return 'Máximo ' . $c['max'] . ' caracteres.';
      }
  }

  if (!empty($c['regex']) && in_array($c['type'], array('text', 'textarea', 'number'), true)) {
    $patron = '/' . str_replace('/', '\/', $c['regex']) . '/u';
    if (@preg_match($patron, $valor) === 0) {
      return 'Formato inválido.';
    }
  }

  return null;
}

/**
 * Valida un campo anidado (dos niveles dependientes).
 *
 * @param array $c Definición del campo.
 * @param string $lv1
 * @param string $lv2
 * @return string|null Mensaje de error o null.
 */
function captura_validar_anidado(array $c, $lv1, $lv2)
{
  if ($lv1 === '' && $lv2 === '') {
    return !empty($c['req']) ? 'Selecciona ambos niveles.' : null;
  }
  if ($lv1 === '' || $lv2 === '') {
    return 'Selecciona ambos niveles.';
  }
  foreach (isset($c['tree']) ? $c['tree'] : array() as $nodo) {
    if ($nodo['p'] === $lv1) {
      $hijos = array_filter(array_map('trim', explode(',', (string) $nodo['c'])), 'strlen');
      return in_array($lv2, $hijos, true) ? null : 'Opción inválida.';
    }
  }
  return 'Opción inválida.';
}

/**
 * Columnas de la exportación: une las versiones del formulario y las cargas de BD.
 * Las etiquetas salen de lo más reciente; las claves que solo existen en
 * versiones/cargas antiguas se agregan al final.
 *
 * @param array[] $definiciones Definiciones { campos } de la más reciente a la más antigua.
 * @param array[] $mappings     Definiciones de card { busqueda, columnas } de la más reciente a la más antigua.
 * @return array ['bd'=>{k: label}, 'campos'=>{k: label}]
 */
function captura_columnas_reporte(array $definiciones, array $mappings)
{
  $bd = array();
  foreach ($mappings as $m) {
    foreach ((is_array($m) && isset($m['columnas'])) ? $m['columnas'] : array() as $col) {
      if (!isset($bd[$col['k']])) {
        $bd[$col['k']] = $col['label'];
      }
    }
  }

  $campos = array();
  foreach ($definiciones as $d) {
    foreach ((is_array($d) && isset($d['campos'])) ? $d['campos'] : array() as $c) {
      if (!isset($campos[$c['k']])) {
        $campos[$c['k']] = $c['label'];
      }
    }
  }

  return array('bd' => $bd, 'campos' => $campos);
}

/**
 * Celdas de una fila de exportación, en el orden de captura_columnas_reporte().
 *
 * @param array $registro Datos de la card { k: valor } (o vacío).
 * @param array $valores  Valores del formulario { k: valor }.
 * @param array $columnas Salida de captura_columnas_reporte().
 * @return string[]
 */
function captura_celdas_reporte(array $registro, array $valores, array $columnas)
{
  $celdas = array();
  foreach (array_keys($columnas['bd']) as $k) {
    $celdas[] = isset($registro[$k]) ? (string) $registro[$k] : '';
  }
  foreach (array_keys($columnas['campos']) as $k) {
    $v = isset($valores[$k]) ? $valores[$k] : '';
    if (is_array($v)) {
      $v = trim((isset($v['lv1']) ? $v['lv1'] : '') . ' / ' . (isset($v['lv2']) ? $v['lv2'] : ''), ' /');
    }
    $celdas[] = (string) $v;
  }
  return $celdas;
}
