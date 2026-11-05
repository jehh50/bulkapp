<?php

/**
 * Pruebas de los helpers de Clientes (empresas) y campañas.
 */

test('campanas_etiqueta no repite el nombre cuando campaña y empresa coinciden', function () {
  assert_igual('Banco Activo', campanas_etiqueta('Banco Activo', 'Banco Activo'));
  assert_igual('Bancaribe', campanas_etiqueta('bancaribe ', 'Bancaribe'));
  assert_igual('Banco Activo › Seguros', campanas_etiqueta('Banco Activo', 'Seguros'));
  assert_igual('Seguros', campanas_etiqueta(null, 'Seguros'));
});

test('campanas_sql_etiqueta usa los alias indicados', function () {
  $sql = campanas_sql_etiqueta('s', 'em');
  assert_verdadero(strpos($sql, 'em.nombre') !== false);
  assert_verdadero(strpos($sql, 's.descripcion') !== false);
  assert_falso(strpos($sql, 'e.nombre') !== false && strpos($sql, 'em.nombre') === false);
});

test('campanas_normalizar_rif acepta formatos comunes y rechaza basura', function () {
  assert_igual('J-12345678-9', campanas_normalizar_rif('j123456789'));
  assert_igual('J-12345678-9', campanas_normalizar_rif(' J-12.345.678-9 '));
  assert_igual('G-20000001', campanas_normalizar_rif('G-20000001'));
  assert_igual('', campanas_normalizar_rif(''));
  assert_igual(null, campanas_normalizar_rif('X-123'));
});

test('campanas_validar_empresa exige nombre y valida el RIF', function () {
  $ok = campanas_validar_empresa(array('nombre' => '  Banco   Nuevo ', 'identificacion' => 'j-30000000-1', 'status_id' => '2'));
  assert_verdadero($ok['ok']);
  assert_igual('Banco Nuevo', $ok['datos']['nombre']);
  assert_igual('J-30000000-1', $ok['datos']['identificacion']);
  assert_igual(2, $ok['datos']['status_id']);

  $sinRif = campanas_validar_empresa(array('nombre' => 'X'));
  assert_verdadero($sinRif['ok']);
  assert_igual(null, $sinRif['datos']['identificacion']);

  $mal = campanas_validar_empresa(array('nombre' => '', 'identificacion' => 'abc'));
  assert_falso($mal['ok']);
  assert_igual(2, count($mal['errores']));
});

test('campanas_validar_campana: alta válida con meta opcional', function () {
  $r = campanas_validar_campana(array(
    'nombre' => 'Seguros', 'detalle' => 'Venta de pólizas', 'fecha_inicio' => '2026-11-02',
    'meta' => '', 'tipo_flujo' => 'dinamico', 'codigo' => '', 'status_id' => '2',
  ), true);
  assert_verdadero($r['ok']);
  assert_igual(null, $r['datos']['meta']);
  assert_igual('dinamico', $r['datos']['tipo_flujo']);

  $r2 = campanas_validar_campana(array(
    'nombre' => 'Planes', 'fecha_inicio' => '2026-11-02', 'meta' => '500', 'tipo_flujo' => 'bancario', 'codigo' => 'ab-17',
  ), true);
  assert_verdadero($r2['ok']);
  assert_igual(500, $r2['datos']['meta']);
  assert_igual('AB-17', $r2['datos']['codigo']);
});

test('campanas_validar_campana: errores de nombre, fecha, meta, flujo y código', function () {
  $r = campanas_validar_campana(array('nombre' => '', 'fecha_inicio' => '02/11/2026', 'meta' => '0', 'tipo_flujo' => 'otro'), true);
  assert_falso($r['ok']);
  assert_igual(4, count($r['errores']));

  $sinCodigo = campanas_validar_campana(array('nombre' => 'X', 'fecha_inicio' => '2026-11-02', 'tipo_flujo' => 'bancario'), true);
  assert_falso($sinCodigo['ok']);

  $codigoMalo = campanas_validar_campana(array('nombre' => 'X', 'fecha_inicio' => '2026-11-02', 'tipo_flujo' => 'dinamico', 'codigo' => 'a b'), true);
  assert_falso($codigoMalo['ok']);
});

test('campanas_validar_campana: al editar no acepta ni devuelve tipo_flujo', function () {
  $r = campanas_validar_campana(array('nombre' => 'X', 'fecha_inicio' => '2026-11-02', 'tipo_flujo' => 'bancario'), false);
  assert_verdadero($r['ok']);
  assert_falso(array_key_exists('tipo_flujo', $r['datos']));
});
