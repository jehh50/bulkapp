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

/**
 * Tipo de cada campo del formulario ({k: type}), de la versión más reciente a la más antigua.
 *
 * @param array[] $definiciones Mismo orden que en captura_columnas_reporte().
 * @return array
 */
function captura_tipos_campos(array $definiciones)
{
  $tipos = array();
  foreach ($definiciones as $d) {
    foreach ((is_array($d) && isset($d['campos'])) ? $d['campos'] : array() as $c) {
      if (isset($c['k']) && !isset($tipos[$c['k']])) {
        $tipos[$c['k']] = isset($c['type']) ? (string) $c['type'] : 'text';
      }
    }
  }
  return $tipos;
}

/**
 * Árbol cliente → campaña → formulario para los filtros en cascada del reporte.
 *
 * @param array[] $filas Filas planas: empresa_id, empresa, servicio_id, campana,
 *                       formulario_id, formulario, eliminado (ya ordenadas).
 * @return array[] [{id, nombre, campanas: [{id, nombre, formularios: [{id, nombre}]}]}]
 */
function captura_arbol_reporte(array $filas)
{
  $arbol = array();
  foreach ($filas as $f) {
    $e = (int) $f['empresa_id'];
    $s = (int) $f['servicio_id'];
    if (!isset($arbol[$e])) {
      $arbol[$e] = array('id' => $e, 'nombre' => (string) $f['empresa'], 'campanas' => array());
    }
    if (!isset($arbol[$e]['campanas'][$s])) {
      $arbol[$e]['campanas'][$s] = array('id' => $s, 'nombre' => (string) $f['campana'], 'formularios' => array());
    }
    $arbol[$e]['campanas'][$s]['formularios'][] = array(
      'id' => (int) $f['formulario_id'],
      'nombre' => (string) $f['formulario'] . (!empty($f['eliminado']) ? ' (eliminado)' : ''),
    );
  }
  foreach ($arbol as &$emp) {
    $emp['campanas'] = array_values($emp['campanas']);
  }
  unset($emp);
  return array_values($arbol);
}

/**
 * Busca la combinación cliente → campaña → formulario en el árbol.
 *
 * @param array[] $arbol Salida de captura_arbol_reporte().
 * @return array|null ['cliente'=>nodo, 'campana'=>nodo, 'formulario'=>nodo] o null si no existe.
 */
function captura_seleccion_reporte(array $arbol, $clienteId, $campanaId, $formularioId)
{
  foreach ($arbol as $c) {
    if ($c['id'] !== (int) $clienteId) {
      continue;
    }
    foreach ($c['campanas'] as $s) {
      if ($s['id'] !== (int) $campanaId) {
        continue;
      }
      foreach ($s['formularios'] as $f) {
        if ($f['id'] === (int) $formularioId) {
          return array('cliente' => $c, 'campana' => $s, 'formulario' => $f);
        }
      }
    }
  }
  return null;
}

/**
 * Números de página a mostrar: primera, última y vecinas de la actual; null = «…».
 *
 * @param int $actual
 * @param int $total
 * @return array
 */
function captura_paginas($actual, $total)
{
  $total = max(1, (int) $total);
  $actual = min(max(1, (int) $actual), $total);
  $paginas = array();
  $previa = 0;
  for ($p = 1; $p <= $total; $p++) {
    if ($p === 1 || $p === $total || abs($p - $actual) <= 1) {
      if ($previa && $p - $previa > 1) {
        $paginas[] = null;
      }
      $paginas[] = $p;
      $previa = $p;
    }
  }
  return $paginas;
}

/**
 * Texto apto para nombres de archivo: minúsculas, sin acentos, palabras unidas con «-».
 *
 * @param string $texto
 * @return string
 */
function captura_slug($texto)
{
  $texto = mb_strtolower(trim((string) $texto), 'UTF-8');
  $texto = strtr($texto, array(
    'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
    'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ç' => 'c',
  ));
  $texto = trim(preg_replace('/[^a-z0-9]+/', '-', $texto), '-');
  return $texto !== '' ? mb_substr($texto, 0, 40) : 'sin-nombre';
}

/**
 * Celdas XLSX de los datos de la búsqueda y del formulario: los campos numéricos y de
 * fecha se exportan como valores nativos; el resto como texto.
 *
 * @param array $registro Datos de la card { k: valor }.
 * @param array $valores  Valores del formulario { k: valor }.
 * @param array $columnas Salida de captura_columnas_reporte().
 * @param array $tipos    Salida de captura_tipos_campos().
 * @return array[]
 */
function captura_celdas_xlsx(array $registro, array $valores, array $columnas, array $tipos)
{
  $textos = captura_celdas_reporte($registro, $valores, $columnas);
  $nBd = count($columnas['bd']);
  $claves = array_keys($columnas['campos']);
  $celdas = array();
  foreach ($textos as $i => $t) {
    $k = ($i >= $nBd) ? $claves[$i - $nBd] : null;
    $tipo = ($k !== null && isset($tipos[$k])) ? $tipos[$k] : 'text';
    if ($tipo === 'number' && is_numeric($t)) {
      $celdas[] = xlsx_numero($t);
    } elseif ($tipo === 'date' && $t !== '') {
      $celdas[] = xlsx_fecha($t);
    } else {
      $celdas[] = xlsx_celda($t);
    }
  }
  return $celdas;
}
