<?php
class database
{

	private $db;
	private $id;
	private $nombre;

	public function __construct()
	{
		$this->db = new Conexion();
	}


	public function guardarRegistrosBatch($batch, $types, $servicio)
	{
		// CORRECCIÓN 1: Verificar la variable correcta ($batch)
		if (empty($batch)) {
			return false;
		}

		if ($servicio == 1) {
			$query = "INSERT INTO clientes (identificacion, nombre_legal, telf_hab, telf_ofi, telf_cel, correo, direccion, cuenta, oferta) VALUES ";
		} else {
			$query = "INSERT INTO cashea_customers (cedula, id_cuota, nombre_grupo, fecha_pagar, monto_cuota, numero_cuota, fee, plata_por_cobrar, capital_asignado, id_orden, identificacion_orden, fecha_creacion_orden, email, telefono, nombre_usuario, local_origen, estado_deuda, tramo_inicial, tramo_actual, segmento) VALUES ";
		}

		$placeHolders = [];
		$flatennedParams = [];

		foreach ($batch as $row) {
			// CORRECCIÓN 2: Usar count($row) para obtener el número de columnas
			$placeHolders[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';
			$flatennedParams = array_merge($flatennedParams, $row);
		}

		try {
			$query .= implode(',', $placeHolders);
			$stmt = $this->db->preparedQuery($query, $flatennedParams, str_repeat($types, count($batch)));

			if ($stmt) {
				$stmt->close();
			}

			return true;
		} catch (Exception $e) {
			error_log("Error en guardarRegistrosTemporalBatch: " . $e->getMessage());
			return false;
		}
	}


	public function registroCashea(
		$cedula,
		$id_cuota,
		$nombre_grupo,
		$fecha_pagar,
		$monto_cuota,
		$numero_cuota,
		$fee,
		$plata_por_cobrar,
		$capital_asignado,
		$id_orden,
		$identificacion_orden,
		$fecha_creacion_orden,
		$email,
		$telefono,
		$nombre_usuario,
		$local_origen,
		$estado_deuda,
		$tramo_inicial,
		$tramo_actual,
		$segmento
	) {
		// Conversión de encoding si es necesario
		$cedula = iconv(mb_detect_encoding($cedula, mb_detect_order(), true), "UTF-8", $cedula);
		$nombre_grupo = iconv(mb_detect_encoding($nombre_grupo, mb_detect_order(), true), "UTF-8", $nombre_grupo);
		$email = iconv(mb_detect_encoding($email, mb_detect_order(), true), "UTF-8", $email);
		$telefono = iconv(mb_detect_encoding($telefono, mb_detect_order(), true), "UTF-8", $telefono);
		$nombre_usuario = iconv(mb_detect_encoding($nombre_usuario, mb_detect_order(), true), "UTF-8", $nombre_usuario);
		$local_origen = iconv(mb_detect_encoding($local_origen, mb_detect_order(), true), "UTF-8", $local_origen);
		$estado_deuda = iconv(mb_detect_encoding($estado_deuda, mb_detect_order(), true), "UTF-8", $estado_deuda);
		$tramo_inicial = iconv(mb_detect_encoding($tramo_inicial, mb_detect_order(), true), "UTF-8", $tramo_inicial);
		$tramo_actual = iconv(mb_detect_encoding($tramo_actual, mb_detect_order(), true), "UTF-8", $tramo_actual);
		$segmento = iconv(mb_detect_encoding($segmento, mb_detect_order(), true), "UTF-8", $segmento);

		$query = "INSERT INTO cashea_customers (
        cedula, id_cuota, nombre_grupo, fecha_pagar, monto_cuota, numero_cuota, fee, plata_por_cobrar,
        capital_asignado, id_orden, identificacion_orden, fecha_creacion_orden, email, telefono,
        nombre_usuario, local_origen, estado_deuda, tramo_inicial, tramo_actual, segmento
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$params = [
			$cedula,
			$id_cuota,
			$nombre_grupo,
			$fecha_pagar,
			$monto_cuota,
			$numero_cuota,
			$fee,
			$plata_por_cobrar,
			$capital_asignado,
			$id_orden,
			$identificacion_orden,
			$fecha_creacion_orden,
			$email,
			$telefono,
			$nombre_usuario,
			$local_origen,
			$estado_deuda,
			$tramo_inicial,
			$tramo_actual,
			$segmento
		];

		// Tipos de datos: ajusta según los tipos reales de tus columnas
		$paramTypes = 'ssssssssssssssssssss';

		try {
			$stmt = $this->db->preparedQuery($query, $params, $paramTypes);
			$stmt->close();
			return true;
		} catch (Exception $e) {
			echo "Error al guardar en cashea_customers => " . $e->getMessage();
		}
	}

	public function servicio()
	{
		$sql = $this->db->query("SELECT * FROM servicios");
		if ($this->db->rows($sql) > 0) {
			while ($data = $this->db->recorrer($sql)) {
				$respuesta[] = $data;
			}
		} else {
			$respuesta = false;
		}
		return $respuesta;
	}
}
