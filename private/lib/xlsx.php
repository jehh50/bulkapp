<?php

/**
 * Escritor mínimo de XLSX (Office Open XML) con extensiones nativas (ZipArchive),
 * sin librerías externas. Escribe cada hoja fila a fila en un archivo temporal, así
 * que exportar muchos registros no los carga todos en memoria.
 *
 * Uso:
 *   $h = xlsx_hoja_abrir('Registros', array(10, 18, 30), true);
 *   xlsx_hoja_fila($h, array(xlsx_celda('ID', XLSX_ESTILO_ENCABEZADO_1), ...));
 *   xlsx_hoja_cerrar($h);
 *   xlsx_empaquetar($destino, array($h, ...));
 *
 * Celdas: xlsx_celda($valor, $estilo) para texto, xlsx_numero() y xlsx_fecha().
 */

const XLSX_ESTILO_NORMAL = 0;
const XLSX_ESTILO_ENCABEZADO_1 = 1; // azul marino  (#12306b)
const XLSX_ESTILO_ENCABEZADO_2 = 2; // azul medio   (#2d57a8)
const XLSX_ESTILO_ENCABEZADO_3 = 3; // azul         (#1a4391)
const XLSX_ESTILO_FECHA_HORA = 4;   // dd/mm/yyyy hh:mm
const XLSX_ESTILO_FECHA = 5;        // dd/mm/yyyy
const XLSX_ESTILO_NEGRITA = 6;

/**
 * Celda de texto.
 *
 * @param mixed $valor
 * @param int $estilo
 * @return array
 */
function xlsx_celda($valor, $estilo = XLSX_ESTILO_NORMAL)
{
  return array('t' => 's', 'v' => (string) $valor, 's' => (int) $estilo);
}

/**
 * Celda numérica.
 *
 * @param int|float|string $valor Debe ser numérico.
 * @param int $estilo
 * @return array
 */
function xlsx_numero($valor, $estilo = XLSX_ESTILO_NORMAL)
{
  return array('t' => 'n', 'v' => $valor + 0, 's' => (int) $estilo);
}

/**
 * Celda de fecha (serial de Excel). Si el texto no es una fecha válida queda como texto.
 *
 * @param string $valor 'Y-m-d' o 'Y-m-d H:i[:s]'
 * @return array
 */
function xlsx_fecha($valor)
{
  $serial = xlsx_serial_fecha($valor);
  if ($serial === null) {
    return xlsx_celda($valor);
  }
  $conHora = strlen(trim((string) $valor)) > 10;
  return array('t' => 'n', 'v' => $serial, 's' => $conHora ? XLSX_ESTILO_FECHA_HORA : XLSX_ESTILO_FECHA);
}

/**
 * Serial de fecha de Excel (días desde 1899-12-30) o null si no es una fecha.
 *
 * @param string $valor
 * @return float|null
 */
function xlsx_serial_fecha($valor)
{
  $valor = trim((string) $valor);
  $formatos = array('Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d');
  foreach ($formatos as $f) {
    $d = DateTime::createFromFormat('!' . $f, $valor, new DateTimeZone('UTC'));
    if ($d && $d->format($f) === $valor) {
      return (float) ($d->getTimestamp() / 86400 + 25569);
    }
  }
  return null;
}

/**
 * Letra de columna (0 → A, 26 → AA).
 *
 * @param int $indice Base 0.
 * @return string
 */
function xlsx_columna($indice)
{
  $letra = '';
  $n = (int) $indice + 1;
  while ($n > 0) {
    $m = ($n - 1) % 26;
    $letra = chr(65 + $m) . $letra;
    $n = (int) (($n - $m - 1) / 26);
  }
  return $letra;
}

/**
 * Abre una hoja en un archivo temporal.
 *
 * @param string $nombre Nombre de la pestaña (máx. 31 caracteres).
 * @param int[] $anchos Ancho de cada columna (en caracteres).
 * @param bool $congelarEncabezado Fija la primera fila.
 * @return array Manejador de la hoja.
 */
function xlsx_hoja_abrir($nombre, array $anchos = array(), $congelarEncabezado = false)
{
  $ruta = tempnam(sys_get_temp_dir(), 'xlsx_');
  if ($ruta === false) {
    throw new RuntimeException('No se pudo crear el archivo temporal de la hoja.');
  }
  $fh = fopen($ruta, 'wb');

  fwrite($fh, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
    . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
    . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
  if ($congelarEncabezado) {
    fwrite($fh, '<sheetViews><sheetView workbookViewId="0">'
      . '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
      . '<selection pane="bottomLeft" activeCell="A2" sqref="A2"/></sheetView></sheetViews>');
  }
  if (!empty($anchos)) {
    fwrite($fh, '<cols>');
    foreach (array_values($anchos) as $i => $w) {
      fwrite($fh, '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $w . '" customWidth="1"/>');
    }
    fwrite($fh, '</cols>');
  }
  fwrite($fh, '<sheetData>');

  $nombre = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', (string) $nombre);
  return array('nombre' => mb_substr($nombre, 0, 31), 'ruta' => $ruta, 'fh' => $fh, 'fila' => 0);
}

/**
 * Escribe una fila.
 *
 * @param array $hoja Manejador (por referencia: lleva el contador de filas).
 * @param array[] $celdas Salida de xlsx_celda()/xlsx_numero()/xlsx_fecha().
 * @return void
 */
function xlsx_hoja_fila(array &$hoja, array $celdas)
{
  $hoja['fila']++;
  $r = $hoja['fila'];
  $xml = '<row r="' . $r . '">';
  foreach (array_values($celdas) as $i => $c) {
    $ref = xlsx_columna($i) . $r;
    $estilo = !empty($c['s']) ? ' s="' . (int) $c['s'] . '"' : '';
    if ($c['t'] === 'n') {
      $v = is_float($c['v']) ? var_export($c['v'], true) : (string) $c['v']; // precisión completa
      $xml .= '<c r="' . $ref . '"' . $estilo . '><v>' . $v . '</v></c>';
    } elseif ($c['v'] !== '') {
      $xml .= '<c r="' . $ref . '"' . $estilo . ' t="inlineStr"><is><t xml:space="preserve">'
        . xlsx_escapar($c['v']) . '</t></is></c>';
    } elseif ($estilo !== '') {
      $xml .= '<c r="' . $ref . '"' . $estilo . '/>';
    }
  }
  fwrite($hoja['fh'], $xml . '</row>');
}

/**
 * Cierra el XML de la hoja.
 *
 * @param array $hoja
 * @return void
 */
function xlsx_hoja_cerrar(array &$hoja)
{
  fwrite($hoja['fh'], '</sheetData></worksheet>');
  fclose($hoja['fh']);
  $hoja['fh'] = null;
}

/**
 * Arma el .xlsx con las hojas cerradas y borra sus temporales.
 *
 * @param string $destino Ruta del archivo a crear.
 * @param array[] $hojas Manejadores ya cerrados, en orden.
 * @return void
 */
function xlsx_empaquetar($destino, array $hojas)
{
  if (!class_exists('ZipArchive')) {
    throw new RuntimeException('La extensión zip de PHP no está disponible.');
  }
  $zip = new ZipArchive();
  if ($zip->open($destino, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException('No se pudo crear el archivo XLSX.');
  }

  $tipos = '';
  $rels = '';
  $lista = '';
  foreach (array_values($hojas) as $i => $h) {
    $n = $i + 1;
    $zip->addFile($h['ruta'], 'xl/worksheets/sheet' . $n . '.xml');
    $tipos .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" '
      . 'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    $rels .= '<Relationship Id="rId' . $n . '" '
      . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
      . 'Target="worksheets/sheet' . $n . '.xml"/>';
    $lista .= '<sheet name="' . xlsx_escapar($h['nombre']) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
  }
  $idEstilos = count($hojas) + 1;

  $cab = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
  $zip->addFromString('[Content_Types].xml', $cab
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
    . $tipos . '</Types>');
  $zip->addFromString('_rels/.rels', $cab
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    . '</Relationships>');
  $zip->addFromString('xl/workbook.xml', $cab
    . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
    . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    . '<sheets>' . $lista . '</sheets></workbook>');
  $zip->addFromString('xl/_rels/workbook.xml.rels', $cab
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels
    . '<Relationship Id="rId' . $idEstilos . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    . '</Relationships>');
  $zip->addFromString('xl/styles.xml', $cab . xlsx_estilos());

  $ok = $zip->close();
  foreach ($hojas as $h) {
    @unlink($h['ruta']);
  }
  if (!$ok) {
    throw new RuntimeException('No se pudo cerrar el archivo XLSX.');
  }
}

/**
 * Hoja de estilos: el índice de cada <xf> coincide con las constantes XLSX_ESTILO_*.
 *
 * @return string
 */
function xlsx_estilos()
{
  $relleno = function ($rgb) {
    return '<fill><patternFill patternType="solid"><fgColor rgb="FF' . $rgb . '"/><bgColor indexed="64"/></patternFill></fill>';
  };
  return '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    . '<numFmts count="2"><numFmt numFmtId="164" formatCode="dd/mm/yyyy hh:mm"/><numFmt numFmtId="165" formatCode="dd/mm/yyyy"/></numFmts>'
    . '<fonts count="3">'
    . '<font><sz val="11"/><name val="Calibri"/><family val="2"/></font>'
    . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>'
    . '<font><b/><sz val="11"/><name val="Calibri"/><family val="2"/></font>'
    . '</fonts>'
    . '<fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
    . $relleno('12306B') . $relleno('2D57A8') . $relleno('1A4391') . '</fills>'
    . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
    . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
    . '<cellXfs count="7">'
    . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
    . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
    . '<xf numFmtId="0" fontId="1" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
    . '<xf numFmtId="0" fontId="1" fillId="4" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
    . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
    . '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
    . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
    . '</cellXfs>'
    . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
    . '</styleSheet>';
}

/**
 * Escapa texto para XML y quita caracteres de control no válidos.
 *
 * @param string $texto
 * @return string
 */
function xlsx_escapar($texto)
{
  $texto = mb_convert_encoding((string) $texto, 'UTF-8', 'UTF-8');
  $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $texto);
  return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
}
