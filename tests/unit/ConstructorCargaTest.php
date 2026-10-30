<?php

/**
 * Pruebas del parseo de CSV/XLSX y del mapeo columna→campo
 * (private/helpers/constructorCarga.php).
 */

/**
 * Crea un archivo temporal con el contenido dado y devuelve su ruta.
 *
 * @param string $contenido
 * @param string $ext
 * @return string
 */
function tmp_archivo($contenido, $ext)
{
  $ruta = tempnam(sys_get_temp_dir(), 'cnstest_') . '.' . $ext;
  file_put_contents($ruta, $contenido);
  return $ruta;
}

test('constructor_xlsx_col_index resuelve referencias de celda', function () {
  assert_igual(0, constructor_xlsx_col_index('A1'));
  assert_igual(1, constructor_xlsx_col_index('B12'));
  assert_igual(26, constructor_xlsx_col_index('AA1'));
});

test('parsea CSV con delimitador ; y encabezados', function () {
  $ruta = tmp_archivo("Cedula;Plan\nV-123;Full\nV-456;Basico\n", 'csv');
  $r = constructor_parsear_csv($ruta);
  unlink($ruta);

  assert_igual(array('Cedula', 'Plan'), $r['headers']);
  assert_igual(2, count($r['filas']));
  assert_igual('Full', $r['filas'][0][1]);
});

test('parsea CSV con delimitador , (autodetección)', function () {
  $ruta = tmp_archivo("a,b,c\n1,2,3\n", 'csv');
  $r = constructor_parsear_csv($ruta);
  unlink($ruta);

  assert_igual(array('a', 'b', 'c'), $r['headers']);
  assert_igual('3', $r['filas'][0][2]);
});

test('parsea XLSX: cadenas compartidas, huecos y celdas numéricas', function () {
  $NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
  $ss = '<?xml version="1.0"?><sst xmlns="' . $NS . '">'
    . '<si><t>Cedula</t></si><si><t>Plan</t></si><si><t>V-123</t></si><si><t>Full</t></si></sst>';
  $sheet = '<?xml version="1.0"?><worksheet xmlns="' . $NS . '"><sheetData>'
    . '<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row>'
    . '<row r="2"><c r="A2" t="s"><v>2</v></c><c r="B2" t="s"><v>3</v></c></row>'
    . '<row r="3"><c r="B3"><v>42</v></c></row>'
    . '</sheetData></worksheet>';

  $ruta = tempnam(sys_get_temp_dir(), 'cnsxlsx_') . '.xlsx';
  $zip = new ZipArchive();
  $zip->open($ruta, ZipArchive::CREATE);
  $zip->addFromString('xl/sharedStrings.xml', $ss);
  $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
  $zip->close();

  $r = constructor_parsear_xlsx($ruta);
  unlink($ruta);

  assert_igual(array('Cedula', 'Plan'), $r['headers']);
  assert_igual(2, count($r['filas']));
  assert_igual('V-123', $r['filas'][0][0]);
  assert_igual('Full', $r['filas'][0][1]);
  // fila con hueco en A y número en B
  assert_igual('', $r['filas'][1][0]);
  assert_igual('42', $r['filas'][1][1]);
});

test('constructor_slug_columna genera claves estables', function () {
  assert_igual('nro_doc', constructor_slug_columna('NRO_DOC'));
  assert_igual('telefono_movil', constructor_slug_columna(' Teléfono Móvil '));
  assert_igual('c_2024', constructor_slug_columna('2024'));
  assert_igual('col', constructor_slug_columna('***'));
});

test('constructor_normalizar_columnas arma la definición de la card', function () {
  $headers = array('NRO_DOC', 'NOMBRE', 'TELEFONO');
  $r = constructor_normalizar_columnas(array(
    'busqueda' => 'NRO_DOC',
    'columnas' => array(
      array('col' => 'NRO_DOC', 'label' => 'Cédula'),
      array('col' => 'NOMBRE', 'label' => ''),
    ),
  ), $headers);

  assert_verdadero($r['ok']);
  assert_igual('nro_doc', $r['columnas']['busqueda']);
  assert_igual(2, count($r['columnas']['columnas']));
  assert_igual('Cédula', $r['columnas']['columnas'][0]['label']);
  assert_igual('NOMBRE', $r['columnas']['columnas'][1]['label']); // etiqueta vacía -> encabezado
});

test('constructor_normalizar_columnas exige búsqueda entre las elegidas', function () {
  $r = constructor_normalizar_columnas(array(
    'busqueda' => 'TELEFONO',
    'columnas' => array(array('col' => 'NOMBRE')),
  ), array('NOMBRE', 'TELEFONO'));
  assert_falso($r['ok']);
});

test('constructor_normalizar_columnas rechaza columnas inexistentes o vacías', function () {
  $r1 = constructor_normalizar_columnas(array('busqueda' => 'X', 'columnas' => array(array('col' => 'X'))), array('A'));
  assert_falso($r1['ok']);
  $r2 = constructor_normalizar_columnas(array('busqueda' => 'A', 'columnas' => array()), array('A'));
  assert_falso($r2['ok']);
});

test('constructor_normalizar_columnas desambigua claves repetidas', function () {
  $r = constructor_normalizar_columnas(array(
    'busqueda' => 'Nro Doc',
    'columnas' => array(array('col' => 'Nro Doc'), array('col' => 'NRO-DOC')),
  ), array('Nro Doc', 'NRO-DOC'));
  assert_verdadero($r['ok']);
  assert_igual('nro_doc', $r['columnas']['columnas'][0]['k']);
  assert_igual('nro_doc_2', $r['columnas']['columnas'][1]['k']);
});

test('constructor_aplicar_columnas arma filas y omite las sin clave de búsqueda', function () {
  $headers = array('NRO_DOC', 'NOMBRE', 'EXTRA');
  $filas = array(
    array('V-1', 'Ana', 'x'),
    array('', 'Sin documento', 'y'),   // sin clave -> se omite
    array(' V-2 ', 'Luis', 'z'),
  );
  $def = array('busqueda' => 'nro_doc', 'columnas' => array(
    array('k' => 'nro_doc', 'col' => 'NRO_DOC', 'label' => 'Cédula'),
    array('k' => 'nombre', 'col' => 'NOMBRE', 'label' => 'Nombre'),
  ));

  $out = constructor_aplicar_columnas($headers, $filas, $def);

  assert_igual(2, count($out));
  assert_igual('V-1', $out[0]['nro_doc']);
  assert_igual('V-2', $out[1]['nro_doc']);   // recortado
  assert_falso(isset($out[0]['EXTRA']));
});
