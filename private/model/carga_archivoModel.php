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


public function guardarRegistrosBatch($batch, $cols, $servicio)
{
	
    if (empty($batch)) return false;

    if ($servicio == 1) {
        $query = "INSERT INTO clientes
            (identificacion, nombre_legal, telf_hab, telf_ofi, telf_cel, correo, direccion, cuenta, oferta)
            VALUES ";

			$this->db->query("UPDATE clientes SET status_id = 11)");
    } else {

		$this->db->query("UPDATE cashea_customers SET status_id = 11 WHERE status_id = 3");
		
        $query = "INSERT INTO cashea_customers
            (cedula, id_cuota, nombre_grupo, fecha_pagar, monto_cuota, numero_cuota, fee, plata_por_cobrar,
             capital_asignado, id_orden, identificacion_orden, fecha_creacion_orden, email, telefono,
             nombre_usuario, local_origen, estado_deuda, tramo_inicial, tramo_actual, segmento)
            VALUES ";
    }

    // Placeholders fijos por fila
    $rowPlaceholders = '(' . rtrim(str_repeat('?,', $cols), ',') . ')';
    $placeHolders = array_fill(0, count($batch), $rowPlaceholders);

    // Aplana parámetros garantizando $cols por fila
    $flatParams = [];
    foreach ($batch as $row) {
        // Por seguridad, trunca/pad aquí también
        $row = array_slice($row, 0, $cols);
        if (count($row) < $cols) $row = array_pad($row, $cols, null);
        array_push($flatParams, ...$row);
    }

    $query .= implode(',', $placeHolders);
    $types = str_repeat('s', $cols * count($batch));

    try {
        $stmt = $this->db->preparedQuery($query, $flatParams, $types);
        if ($stmt) $stmt->close();
        return true;
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
