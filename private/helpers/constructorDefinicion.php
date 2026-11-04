<?php

/**
 * Validación y normalización de la definición de un formulario del Constructor.
 *
 * La definición es el documento JSON { "campos": [ ... ] } que vive en
 * `formulario_versiones.definicion`. Este validador es el **espejo en servidor**
 * de la validación del editor: nunca se confía en lo que llega del cliente.
 *
 * Modelo de campo (docs/constructor-plan.md §4.1):
 *   k, type, label, placeholder, req, min, max, expr, help,
 *   cond{k,v}, opts[], tree[{p,c}], lv1, lv2
 *
 * `expr` es la clave de una opción del catálogo `formulario_expresiones`. Al validar,
 * se copian en el campo su `regex` (patrón) y/o `transform` (transformación): la versión
 * guardada queda congelada aunque el catálogo cambie después. Un `regex` libre enviado
 * por el cliente se descarta.
 */

/**
 * Indexa por clave las filas del catálogo de expresiones.
 *
 * @param array[] $filas Filas { clave, patron, transformacion, tipos } de formulario_expresiones.
 * @return array clave => ['regex' => ?string, 'transform' => ?string, 'tipos' => string[]]
 */
function constructor_indexar_expresiones(array $filas)
{
  $indice = array();
  foreach ($filas as $f) {
    if (!isset($f['clave']) || (string) $f['clave'] === '') {
      continue;
    }
    $tipos = array_filter(array_map('trim', explode(',', isset($f['tipos']) ? (string) $f['tipos'] : '')), 'strlen');
    $indice[(string) $f['clave']] = array(
      'regex'     => (isset($f['patron']) && $f['patron'] !== '') ? (string) $f['patron'] : null,
      'transform' => (isset($f['transformacion']) && $f['transformacion'] !== '') ? (string) $f['transformacion'] : null,
      'tipos'     => array_values($tipos),
    );
  }
  return $indice;
}

/**
 * Decodifica el JSON de la definición a un arreglo asociativo.
 *
 * @param string|null $json
 * @return array|null Arreglo decodificado, o null si el JSON es inválido.
 */
function constructor_decodificar_definicion($json)
{
  if (!is_string($json) || $json === '') {
    return null;
  }

  $data = json_decode($json, true);

  return is_array($data) ? $data : null;
}

/**
 * Valida y normaliza una definición de formulario.
 *
 * @param array $definicion  Definición ya decodificada: { campos: [...] }.
 * @param array $expresiones Catálogo indexado (constructor_indexar_expresiones()).
 * @return array ['ok' => bool, 'errores' => string[], 'definicion' => array]
 */
function constructor_validar_definicion($definicion, array $expresiones = array())
{
  if (!is_array($definicion) || !isset($definicion['campos']) || !is_array($definicion['campos'])) {
    return array(
      'ok' => false,
      'errores' => array('La definición debe contener una lista de campos.'),
      'definicion' => array('campos' => array()),
    );
  }

  $errores = array();
  $campos = array_values($definicion['campos']);

  if (count($campos) === 0) {
    $errores[] = 'El formulario debe tener al menos un campo.';
  }

  $tiposValidos = Constantes::tiposCampoFormulario();
  $clavesVistas = array();   // k ya definidas: unicidad y referencia de cond a campo previo
  $normalizados = array();

  foreach ($campos as $i => $campo) {
    $pos = $i + 1;

    if (!is_array($campo)) {
      $errores[] = "Campo #$pos: estructura inválida.";
      continue;
    }

    $k     = isset($campo['k']) ? (string) $campo['k'] : '';
    $type  = isset($campo['type']) ? (string) $campo['type'] : '';
    $label = isset($campo['label']) ? trim((string) $campo['label']) : '';

    if ($k === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $k)) {
      $errores[] = "Campo #$pos: la clave es obligatoria (minúsculas, números y guion_bajo, iniciando con letra).";
    } elseif (isset($clavesVistas[$k])) {
      $errores[] = "Campo #$pos: la clave «{$k}» está repetida.";
    }

    if (!in_array($type, $tiposValidos, true)) {
      $errores[] = "Campo #$pos: el tipo «{$type}» no es válido.";
    }

    if ($label === '') {
      $errores[] = "Campo #$pos: la etiqueta es obligatoria.";
    }

    $norm = array('k' => $k, 'type' => $type, 'label' => $label);

    foreach (array('placeholder', 'help') as $opt) {
      if (isset($campo[$opt]) && is_string($campo[$opt]) && $campo[$opt] !== '') {
        $norm[$opt] = (string) $campo[$opt];
      }
    }

    if (!empty($campo['req'])) {
      $norm['req'] = true;
    }

    // min / max (longitud en texto, valor en number)
    $tieneMin = isset($campo['min']) && $campo['min'] !== '' && $campo['min'] !== null;
    $tieneMax = isset($campo['max']) && $campo['max'] !== '' && $campo['max'] !== null;
    if ($tieneMin) {
      if (!is_numeric($campo['min'])) {
        $errores[] = "Campo #$pos: «min» debe ser numérico.";
      } else {
        $norm['min'] = $campo['min'] + 0;
      }
    }
    if ($tieneMax) {
      if (!is_numeric($campo['max'])) {
        $errores[] = "Campo #$pos: «max» debe ser numérico.";
      } else {
        $norm['max'] = $campo['max'] + 0;
      }
    }
    if (isset($norm['min'], $norm['max']) && $norm['min'] > $norm['max']) {
      $errores[] = "Campo #$pos: «min» no puede ser mayor que «max».";
    }

    // expr -> opción del catálogo (se congela regex/transform en la versión)
    if (isset($campo['expr']) && is_string($campo['expr']) && $campo['expr'] !== '') {
      $clave = $campo['expr'];
      if (!isset($expresiones[$clave])) {
        $errores[] = "Campo #$pos: la validación «{$clave}» no existe en el catálogo.";
      } elseif (!in_array($type, $expresiones[$clave]['tipos'], true)) {
        $errores[] = "Campo #$pos: la validación «{$clave}» no aplica a campos de tipo «{$type}».";
      } else {
        $opcion = $expresiones[$clave];
        $patron = $opcion['regex'] !== null ? '/' . str_replace('/', '\\/', $opcion['regex']) . '/u' : null;
        if ($patron !== null && @preg_match($patron, '') === false) {
          $errores[] = "Campo #$pos: la expresión del catálogo «{$clave}» no es válida.";
        } else {
          $norm['expr'] = $clave;
          if ($opcion['regex'] !== null) {
            $norm['regex'] = $opcion['regex'];
          }
          if ($opcion['transform'] !== null && in_array($opcion['transform'], Constantes::transformacionesCampo(), true)) {
            $norm['transform'] = $opcion['transform'];
          }
        }
      }
    }

    // select -> opts
    if ($type === 'select') {
      $opts = array();
      if (isset($campo['opts']) && is_array($campo['opts'])) {
        foreach ($campo['opts'] as $o) {
          $o = trim((string) $o);
          if ($o !== '') {
            $opts[] = $o;
          }
        }
      }
      if (count($opts) === 0) {
        $errores[] = "Campo #$pos: un «select» requiere al menos una opción.";
      } else {
        $norm['opts'] = $opts;
      }
    }

    // nested -> tree (padre -> hijos por coma) + etiquetas lv1/lv2
    if ($type === 'nested') {
      $tree = array();
      if (isset($campo['tree']) && is_array($campo['tree'])) {
        foreach ($campo['tree'] as $nodo) {
          if (is_array($nodo) && isset($nodo['p']) && trim((string) $nodo['p']) !== '') {
            $tree[] = array(
              'p' => (string) $nodo['p'],
              'c' => isset($nodo['c']) ? (string) $nodo['c'] : '',
            );
          }
        }
      }
      if (count($tree) === 0) {
        $errores[] = "Campo #$pos: un campo anidado requiere al menos un nodo padre.";
      } else {
        $norm['tree'] = $tree;
      }
      if (isset($campo['lv1']) && is_string($campo['lv1']) && $campo['lv1'] !== '') {
        $norm['lv1'] = (string) $campo['lv1'];
      }
      if (isset($campo['lv2']) && is_string($campo['lv2']) && $campo['lv2'] !== '') {
        $norm['lv2'] = (string) $campo['lv2'];
      }
    }

    // cond -> debe referenciar un campo PREVIO (ya visto)
    if (isset($campo['cond']) && is_array($campo['cond'])) {
      $ck = isset($campo['cond']['k']) ? (string) $campo['cond']['k'] : '';
      $cv = isset($campo['cond']['v']) ? (string) $campo['cond']['v'] : '';
      if ($ck === '') {
        $errores[] = "Campo #$pos: la condición requiere un campo de referencia.";
      } elseif (!isset($clavesVistas[$ck])) {
        $errores[] = "Campo #$pos: la condición debe referenciar un campo anterior («{$ck}» no existe antes).";
      } else {
        $norm['cond'] = array('k' => $ck, 'v' => $cv);
      }
    }

    if ($k !== '' && !isset($clavesVistas[$k])) {
      $clavesVistas[$k] = true;
    }

    $normalizados[] = $norm;
  }

  return array(
    'ok' => count($errores) === 0,
    'errores' => $errores,
    'definicion' => array('campos' => $normalizados),
  );
}
