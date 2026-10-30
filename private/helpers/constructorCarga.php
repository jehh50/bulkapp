<?php

/**
 * Carga de base de datos por formulario (Constructor, Fase 2).
 *
 * Parseo de CSV y XLSX con extensiones nativas (ZipArchive + SimpleXML), sin
 * dependencias externas. La BD es independiente de los campos del formulario: se
 * eligen columnas a mostrar (card «Datos de la búsqueda») y una columna clave.
 * Los parsers reciben una ruta de archivo y devuelven ['headers'=>[], 'filas'=>[[...]]].
 */

/**
 * Valida un archivo subido para la carga (CSV/TXT/XLSX).
 *
 * @param array|null $archivo Entrada de $_FILES.
 * @param int $maxBytes
 * @return array [bool $ok, string $extOrMsg]
 */
function constructor_validar_subida($archivo, $maxBytes = 20971520)
{
  if (!is_array($archivo) || !isset($archivo['tmp_name'], $archivo['error'], $archivo['size'])) {
    return array(false, 'No se recibió el archivo.');
  }
  if ((int) $archivo['error'] !== UPLOAD_ERR_OK) {
    return array(false, 'La carga del archivo falló.');
  }
  if (!is_uploaded_file($archivo['tmp_name'])) {
    return array(false, 'El archivo recibido no es una carga válida.');
  }
  $tamano = (int) $archivo['size'];
  if ($tamano <= 0 || $tamano > $maxBytes) {
    return array(false, 'El archivo está vacío o excede el tamaño permitido.');
  }
  $ext = strtolower(pathinfo(isset($archivo['name']) ? $archivo['name'] : '', PATHINFO_EXTENSION));
  if (!in_array($ext, array('csv', 'txt', 'xlsx'), true)) {
    return array(false, 'Solo se permiten archivos CSV o XLSX.');
  }

  if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = strtolower((string) $finfo->file($archivo['tmp_name']));
    if ($ext === 'xlsx') {
      $ok = in_array($mime, array('application/zip', 'application/octet-stream',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'), true);
    } else {
      $ok = in_array($mime, array('text/plain', 'text/csv', 'text/x-csv', 'application/csv', 'application/vnd.ms-excel'), true);
    }
    if ($mime !== '' && !$ok) {
      return array(false, 'El tipo de archivo no corresponde a la extensión.');
    }
  }

  return array(true, $ext);
}

/**
 * Parsea un archivo (CSV o XLSX) según su extensión.
 *
 * @param string $ruta
 * @param string $ext 'csv' | 'txt' | 'xlsx'
 * @return array|null ['headers'=>string[], 'filas'=>array[]]  o null si falla.
 */
function constructor_parsear_archivo($ruta, $ext)
{
  $ext = strtolower($ext);
  if ($ext === 'xlsx') {
    return constructor_parsear_xlsx($ruta);
  }
  return constructor_parsear_csv($ruta);
}

/**
 * Parsea un CSV: detecta codificación y delimitador (; o ,).
 *
 * @param string $ruta
 * @return array|null
 */
function constructor_parsear_csv($ruta)
{
  $fp = @fopen($ruta, 'r');
  if ($fp === false) {
    return null;
  }

  $muestra = fread($fp, 1048576);
  rewind($fp);
  $enc = mb_detect_encoding($muestra, 'UTF-8, ISO-8859-1', true);
  if ($enc === false) {
    $enc = 'UTF-8';
  }

  $primera = fgets($fp);
  rewind($fp);
  $delim = (substr_count((string) $primera, ';') >= substr_count((string) $primera, ',')) ? ';' : ',';

  $filas = array();
  while (($row = fgetcsv($fp, 0, $delim)) !== false) {
    if ($row === array(null) || (count($row) === 1 && trim((string) $row[0]) === '')) {
      continue;
    }
    $row = array_map(function ($v) use ($enc) {
      return ($v === null) ? '' : mb_convert_encoding($v, 'UTF-8', $enc);
    }, $row);
    $filas[] = $row;
  }
  fclose($fp);

  if (empty($filas)) {
    return array('headers' => array(), 'filas' => array());
  }
  $headers = array_map('strval', $filas[0]);

  return array('headers' => $headers, 'filas' => array_slice($filas, 1));
}

/**
 * Parsea la primera hoja de un XLSX (ZipArchive + SimpleXML), resolviendo
 * cadenas compartidas y huecos de columna.
 *
 * @param string $ruta
 * @return array|null
 */
function constructor_parsear_xlsx($ruta)
{
  if (!class_exists('ZipArchive')) {
    return null;
  }
  $zip = new ZipArchive();
  if ($zip->open($ruta) !== true) {
    return null;
  }

  // Cadenas compartidas
  $shared = array();
  $ssXml = $zip->getFromName('xl/sharedStrings.xml');
  if ($ssXml !== false) {
    $xml = @simplexml_load_string(constructor_xlsx_sin_namespace($ssXml));
    if ($xml) {
      foreach ($xml->si as $si) {
        if (isset($si->t)) {
          $shared[] = (string) $si->t;
        } else {
          $texto = '';
          foreach ($si->r as $r) {
            $texto .= (string) $r->t;
          }
          $shared[] = $texto;
        }
      }
    }
  }

  $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
  $zip->close();
  if ($sheetXml === false) {
    return null;
  }
  $xml = @simplexml_load_string(constructor_xlsx_sin_namespace($sheetXml));
  if (!$xml || !isset($xml->sheetData)) {
    return null;
  }

  $filas = array();
  foreach ($xml->sheetData->row as $row) {
    $celdas = array();
    $colIdx = 0;
    foreach ($row->c as $c) {
      $col = constructor_xlsx_col_index((string) $c['r']);
      while ($colIdx < $col) {
        $celdas[] = '';
        $colIdx++;
      }
      $t = (string) $c['t'];
      if ($t === 's') {
        $i = isset($c->v) ? (int) $c->v : -1;
        $valor = isset($shared[$i]) ? $shared[$i] : '';
      } elseif ($t === 'inlineStr') {
        $valor = isset($c->is->t) ? (string) $c->is->t : '';
      } else {
        $valor = isset($c->v) ? (string) $c->v : '';
      }
      $celdas[] = $valor;
      $colIdx++;
    }
    $filas[] = $celdas;
  }

  if (empty($filas)) {
    return array('headers' => array(), 'filas' => array());
  }
  $headers = array_map('strval', $filas[0]);

  return array('headers' => $headers, 'filas' => array_slice($filas, 1));
}

/**
 * Quita el namespace por defecto del XML para poder acceder a los elementos
 * por su nombre local con SimpleXML (patrón común al leer OOXML).
 *
 * @param string $xml
 * @return string
 */
function constructor_xlsx_sin_namespace($xml)
{
  return preg_replace('/\sxmlns="[^"]*"/', '', (string) $xml, 1);
}

/**
 * Índice 0-based de columna a partir de una referencia de celda ('B12' -> 1).
 *
 * @param string $ref
 * @return int
 */
function constructor_xlsx_col_index($ref)
{
  if (!preg_match('/^([A-Z]+)/', (string) $ref, $m)) {
    return 0;
  }
  $letras = $m[1];
  $n = 0;
  for ($i = 0; $i < strlen($letras); $i++) {
    $n = $n * 26 + (ord($letras[$i]) - 64);
  }
  return $n - 1;
}

/**
 * Clave estable (`k`) a partir de un encabezado: minúsculas, sin acentos,
 * solo [a-z0-9_], sin empezar por dígito, máx. 40 caracteres.
 *
 * @param string $header
 * @return string
 */
function constructor_slug_columna($header)
{
  $s = mb_strtolower(trim((string) $header), 'UTF-8');
  $s = strtr($s, array('á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n'));
  $s = trim(preg_replace('/[^a-z0-9]+/', '_', $s), '_');
  if ($s === '') {
    $s = 'col';
  }
  if (ctype_digit($s[0])) {
    $s = 'c_' . $s;
  }
  return substr($s, 0, 40);
}

/**
 * Valida y normaliza la definición de columnas de la card de búsqueda.
 *
 * Entrada (del cliente): { busqueda: "<encabezado>", columnas: [ {col, label}, ... ] }
 * Salida: { busqueda: "<k>", columnas: [ {k, col, label}, ... ] }
 *
 * @param mixed $def
 * @param string[] $headers Encabezados del archivo analizado.
 * @return array ['ok'=>bool, 'errores'=>string[], 'columnas'=>array|null]
 */
function constructor_normalizar_columnas($def, array $headers)
{
  $errores = array();
  if (!is_array($def) || empty($def['columnas']) || !is_array($def['columnas'])) {
    return array('ok' => false, 'errores' => array('Debes elegir al menos una columna.'), 'columnas' => null);
  }

  $columnas = array();
  $usadasCol = array();
  $usadasK = array();
  $kBusqueda = null;
  $busqueda = isset($def['busqueda']) ? (string) $def['busqueda'] : '';

  foreach ($def['columnas'] as $c) {
    $col = (is_array($c) && isset($c['col'])) ? (string) $c['col'] : '';
    if ($col === '' || !in_array($col, $headers, true)) {
      $errores[] = 'La columna «' . $col . '» no existe en el archivo.';
      continue;
    }
    if (isset($usadasCol[$col])) {
      continue;
    }
    $usadasCol[$col] = true;

    $label = isset($c['label']) ? trim((string) $c['label']) : '';
    if ($label === '') {
      $label = $col;
    }
    $label = mb_substr($label, 0, 80, 'UTF-8');

    $base = constructor_slug_columna($col);
    $k = $base;
    $n = 2;
    while (isset($usadasK[$k])) {
      $k = substr($base, 0, 36) . '_' . $n++;
    }
    $usadasK[$k] = true;

    if ($col === $busqueda) {
      $kBusqueda = $k;
    }
    $columnas[] = array('k' => $k, 'col' => $col, 'label' => $label);
  }

  if (empty($columnas)) {
    $errores[] = 'Debes elegir al menos una columna.';
  } elseif ($kBusqueda === null) {
    $errores[] = 'La columna de búsqueda debe estar entre las columnas elegidas.';
  }

  if (!empty($errores)) {
    return array('ok' => false, 'errores' => $errores, 'columnas' => null);
  }
  return array('ok' => true, 'errores' => array(), 'columnas' => array('busqueda' => $kBusqueda, 'columnas' => $columnas));
}

/**
 * Arma las filas de la BD con las columnas elegidas.
 *
 * @param string[] $headers Encabezados del archivo.
 * @param array[] $filas Filas (arreglos indexados por columna).
 * @param array $def Salida normalizada de constructor_normalizar_columnas().
 * @return array[] Filas como { k => valor }; se omiten las que no tienen clave de búsqueda.
 */
function constructor_aplicar_columnas(array $headers, array $filas, array $def)
{
  $idx = array();
  foreach ($def['columnas'] as $c) {
    $pos = array_search($c['col'], $headers, true);
    if ($pos !== false) {
      $idx[$c['k']] = $pos;
    }
  }
  $kBusqueda = $def['busqueda'];

  $out = array();
  foreach ($filas as $fila) {
    $rec = array();
    foreach ($idx as $k => $pos) {
      $rec[$k] = isset($fila[$pos]) ? trim((string) $fila[$pos]) : '';
    }
    if (isset($rec[$kBusqueda]) && $rec[$kBusqueda] !== '') {
      $out[] = $rec;
    }
  }

  return $out;
}
