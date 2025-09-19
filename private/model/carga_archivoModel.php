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


	/**
	 * Guarda un lote de registros en la base de datos.
	 *
	 * @param array $batch Un array de arrays, donde cada array interno representa una fila de datos.
	 * @param int $servicio El identificador del servicio (1 para clientes, otro valor para cashea).
	 * @return int|false El número de filas insertadas con éxito, o false si hubo un error.
	 */
	public function guardarRegistrosBatch($batch, $paramTypes,$servicio)
	{
		if (empty($batch)) {
			return 0;
		}

		$columnas = [];
		$tabla = '';
		$columnasEsperadas = 0;

		if ($servicio == 1) {
			$tabla = 'clientes';
			$columnas = ['identificacion', 'nombre_legal', 'telf_hab', 'telf_ofi', 'telf_cel', 'correo', 'direccion', 'cuenta', 'oferta'];
			$columnasEsperadas = 9;
		} else {
			$tabla = 'cashea_customers';
			$columnas = ['cedula', 'id_cuota', 'nombre_grupo', 'fecha_pagar', 'monto_cuota', 'numero_cuota', 'fee', 'plata_por_cobrar', 'capital_asignado', 'id_orden', 'identificacion_orden', 'fecha_creacion_orden', 'email', 'telefono', 'nombre_usuario', 'local_origen', 'estado_deuda', 'tramo_inicial', 'tramo_actual', 'segmento'];
			$columnasEsperadas = 20;
		}
		$query = "INSERT INTO " . $tabla . " (" . implode(', ', $columnas) . ") VALUES ";
		$placeHolders = [];
		$paramsAplanados = [];
		$batchValido = [];

		foreach ($batch as $indice => $fila) {
			if (count($fila) === $columnasEsperadas) {
				$batchValido[] = $fila;
			} else {
				error_log("Error en la carga batch: La fila " . ($indice + 1) . " fue omitida. Se esperaban " . $columnasEsperadas . " columnas, pero se recibieron " . count($fila) . ".");
			}
		}
		
		if (empty($batchValido)) {
			error_log("Carga batch abortada: Ninguna fila pasó la validación de columnas.");
			return 0;
		}

		foreach ($batchValido as $fila) {
			$placeHolders[] = '(' . implode(',', array_fill(0, $columnasEsperadas, '?')) . ')';
			$paramsAplanados = array_merge($paramsAplanados, $fila);
		}

		try {
			
			$query .= implode(',', $placeHolders);
			$tiposParams = str_repeat('s', count($paramsAplanados));		
            $stmt = $this->db->preparedQuery($query, $paramsAplanados, $tiposParams);
            if ($stmt) {
                $filasAfectadas = $stmt->affected_rows;
                $stmt->close();
                return $filasAfectadas;
            }
            return 0;
			
		} catch (Exception $e) {
			error_log("Error en guardarRegistrosBatch: " . $e->getMessage());
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

	public function updateQuotes($paramsBatch, $paramTypes)
	{
		try {
			foreach ($paramsBatch as $param) {
				$param = explode(';', $param[0]);
				$id = $param[0];
				$status = ($param[1] == 'Pago' ? 7 : 10);

				echo "ID: $id, Status: $status\n";

				// Reactivar cuota
				$query = "UPDATE cashea_customers SET status_id = $status WHERE id_cuota = ?";
				$stmt = $this->db->preparedQuery($query, [$id], $paramTypes);
				if ($stmt) $stmt->close();

				/*
				// Si el estado es 'Pago', marcar como pagado, si no, como rechazado
					$status = ($param[1] == 'Pago' ? 7 : 5);

					// Marcar resultado como rechazado
					$query = "UPDATE results_cashea SET status_id = $status WHERE idQuote LIKE  '?'";
					$stmt = $this->db->preparedQuery($query, [$id . '%'], $paramTypes);
					if ($stmt) $stmt->close();
				
				*/
			}

			return true;
		} catch (Exception $e) {
			echo "Error en updateQuotes: " . $e->getMessage();
			return false;
		}
	}
}
