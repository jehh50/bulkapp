<?php

/**
 * Pruebas del reporte de formularios dinámicos: escritor XLSX (private/lib/xlsx.php)
 * y helpers del reporte en private/helpers/captura.php.
 */

test('xlsx_columna convierte índices a letras de columna', function () {
  assert_igual('A', xlsx_columna(0));
  assert_igual('Z', xlsx_columna(25));
  assert_igual('AA', xlsx_columna(26));
  assert_igual('AZ', xlsx_columna(51));
  assert_igual('BA', xlsx_columna(52));
});

test('xlsx_serial_fecha calcula el serial de Excel y rechaza texto', function () {
  assert_igual(46326.0, xlsx_serial_fecha('2026-10-31'));
  assert_igual(46326.5, xlsx_serial_fecha('2026-10-31 12:00:00'));
  assert_igual(null, xlsx_serial_fecha('31/10/2026'));
  assert_igual(null, xlsx_serial_fecha('2026-02-30'));
});

test('xlsx_fecha usa estilo con hora solo si la trae; si no es fecha queda texto', function () {
  assert_igual(XLSX_ESTILO_FECHA, xlsx_fecha('2026-10-31')['s']);
  assert_igual(XLSX_ESTILO_FECHA_HORA, xlsx_fecha('2026-10-31 08:15')['s']);
  assert_igual('s', xlsx_fecha('mañana')['t']);
});

test('xlsx_empaquetar genera un libro válido con texto escapado y valores nativos', function () {
  if (!class_exists('ZipArchive')) {
    return;
  }
  $h = xlsx_hoja_abrir('Registros', array(10, 20), true);
  xlsx_hoja_fila($h, array(xlsx_celda('ID', XLSX_ESTILO_ENCABEZADO_1), xlsx_celda('Nombre <&>', XLSX_ESTILO_ENCABEZADO_2)));
  xlsx_hoja_fila($h, array(xlsx_numero('12.5'), xlsx_celda("José \x01Pérez")));
  xlsx_hoja_cerrar($h);
  $f = xlsx_hoja_abrir('Fil/tros');
  xlsx_hoja_fila($f, array(xlsx_celda('Cliente', XLSX_ESTILO_NEGRITA), xlsx_celda('Cashea')));
  xlsx_hoja_cerrar($f);

  $destino = tempnam(sys_get_temp_dir(), 'xlsx_test_');
  xlsx_empaquetar($destino, array($h, $f));
  assert_falso(file_exists($h['ruta']), 'Debe borrar los temporales de las hojas');

  $zip = new ZipArchive();
  assert_verdadero($zip->open($destino) === true);
  foreach (array('[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels', 'xl/styles.xml', 'xl/worksheets/sheet1.xml', 'xl/worksheets/sheet2.xml') as $parte) {
    $xml = $zip->getFromName($parte);
    assert_verdadero($xml !== false, "Falta $parte");
    assert_verdadero(@simplexml_load_string($xml) !== false, "XML inválido en $parte");
  }
  $hoja = $zip->getFromName('xl/worksheets/sheet1.xml');
  $libro = $zip->getFromName('xl/workbook.xml');
  $zip->close();
  @unlink($destino);

  assert_contiene('Nombre &lt;&amp;&gt;', $hoja);
  assert_contiene('<c r="A2"><v>12.5</v></c>', $hoja);
  assert_contiene('José Pérez', $hoja);
  assert_contiene('state="frozen"', $hoja);
  assert_contiene('name="Fil tros"', $libro);
});

test('captura_tipos_campos toma el tipo de la versión más reciente', function () {
  $tipos = captura_tipos_campos(array(
    array('campos' => array(array('k' => 'monto', 'type' => 'number', 'label' => 'Monto'))),
    array('campos' => array(array('k' => 'monto', 'type' => 'text', 'label' => 'Monto'), array('k' => 'f', 'type' => 'date', 'label' => 'F'))),
  ));
  assert_igual(array('monto' => 'number', 'f' => 'date'), $tipos);
});

test('captura_arbol_reporte agrupa cliente → campaña → formulario y valida la selección', function () {
  $arbol = captura_arbol_reporte(array(
    array('empresa_id' => 2, 'empresa' => 'Cashea', 'servicio_id' => 2, 'campana' => 'Cobranza', 'formulario_id' => 7, 'formulario' => 'Compromiso', 'eliminado' => 0),
    array('empresa_id' => 2, 'empresa' => 'Cashea', 'servicio_id' => 2, 'campana' => 'Cobranza', 'formulario_id' => 8, 'formulario' => 'Viejo', 'eliminado' => 1),
    array('empresa_id' => 2, 'empresa' => 'Cashea', 'servicio_id' => 9, 'campana' => 'Recuperación', 'formulario_id' => 7, 'formulario' => 'Compromiso', 'eliminado' => 0),
  ));
  assert_igual(1, count($arbol));
  assert_igual(2, count($arbol[0]['campanas']));
  assert_igual('Viejo (eliminado)', $arbol[0]['campanas'][0]['formularios'][1]['nombre']);

  $sel = captura_seleccion_reporte($arbol, 2, 9, 7);
  assert_igual('Recuperación', $sel['campana']['nombre']);
  assert_igual(null, captura_seleccion_reporte($arbol, 2, 9, 8), 'El formulario 8 no está en la campaña 9');
  assert_igual(null, captura_seleccion_reporte($arbol, 1, 2, 7), 'La campaña 2 no es del cliente 1');
});

test('captura_paginas muestra extremos, vecinas y huecos', function () {
  assert_igual(array(1), captura_paginas(1, 1));
  assert_igual(array(1, 2, 3, 4, 5), captura_paginas(3, 5));
  assert_igual(array(1, 2, null, 6), captura_paginas(1, 6));
  assert_igual(array(1, null, 4, 5, 6, null, 10), captura_paginas(5, 10));
  assert_igual(array(1, null, 9, 10), captura_paginas(99, 10), 'Una página fuera de rango se ajusta a la última');
});

test('captura_slug arma nombres de archivo seguros', function () {
  assert_igual('cobranza-temprana', captura_slug(' Cobranza  Temprana '));
  assert_igual('recuperacion-90', captura_slug('Recuperación 90+'));
  assert_igual('compania-ano', captura_slug('Compañía/Año'));
  assert_igual('sin-nombre', captura_slug('¿?'));
});

test('captura_celdas_xlsx exporta números y fechas como valores nativos', function () {
  $columnas = array('bd' => array('cedula' => 'Cédula'), 'campos' => array('monto' => 'Monto', 'fecha' => 'Fecha', 'obs' => 'Obs'));
  $tipos = array('monto' => 'number', 'fecha' => 'date', 'obs' => 'text');
  $c = captura_celdas_xlsx(array('cedula' => '00123'), array('monto' => '120.5', 'fecha' => '2026-11-05', 'obs' => '42'), $columnas, $tipos);
  assert_igual('s', $c[0]['t'], 'La cédula de la BD es texto (conserva ceros)');
  assert_igual('00123', $c[0]['v']);
  assert_igual('n', $c[1]['t']);
  assert_igual(120.5, $c[1]['v']);
  assert_igual(XLSX_ESTILO_FECHA, $c[2]['s']);
  assert_igual('s', $c[3]['t'], 'Un texto numérico de campo text sigue siendo texto');

  $vacio = captura_celdas_xlsx(array(), array('monto' => '', 'fecha' => ''), $columnas, $tipos);
  assert_igual('s', $vacio[1]['t']);
  assert_igual('s', $vacio[2]['t']);
});
