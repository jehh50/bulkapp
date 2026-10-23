<?php
class database
{

	private $db;

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
	public function guardarRegistrosBatch(array $batch, string $paramTypes, int $servicio)
	{
		if (empty($batch)) {
			return 0;
		}

		$columnas = [];
		$tabla = '';
		$columnasEsperadas = 0;

		if (Constantes::esServicioBancario($servicio)) {
			$tabla = 'clientes';
			$columnas = ['identificacion', 'nombre_legal', 'telf_hab', 'telf_ofi', 'telf_cel', 'correo', 'direccion', 'cuenta', 'oferta','servicio_id'];
			$columnasEsperadas = 10;
			//$queryUpdate = "UPDATE clientes SET is_active = 0 WHERE is_active = 1 and servicio_id = $servicio";
			$queryUpdate = "";
		} else {
			$tabla = 'cashea_customers';
			$columnas = ['cedula', 'id_cuota', 'nombre_grupo', 'fecha_pagar', 'monto_cuota', 'numero_cuota', 'fee', 'plata_por_cobrar', 'capital_asignado', 'id_orden', 'identificacion_orden', 'fecha_creacion_orden', 'email', 'telefono', 'nombre_usuario', 'local_origen', 'estado_deuda', 'tramo_inicial', 'tramo_actual', 'segmento'];
			$columnasEsperadas = 20;
			$queryUpdate = "UPDATE cashea_customers SET is_active = 0 WHERE is_active = 1";
		}
		$query = "INSERT INTO " . $tabla . " (" . implode(', ', $columnas) . ") VALUES ";
		$placeHolders = [];
		$paramsAplanados = [];
		$batchValido = [];

		foreach ($batch as $indice => $fila) {
			if (count($fila) === $columnasEsperadas) {
				$batchValido[] = $fila;
			} else {
				$msg = "Error en la carga batch: La fila " . ($indice + 1) . " fue omitida. Se esperaban " . $columnasEsperadas . " columnas, pero se recibieron " . count($fila) . ". Contenido: " . json_encode($fila);
				app_log('warning', $msg);
			}
		}
		
		if (empty($batchValido)) {
			app_log('warning', 'Carga batch abortada: ninguna fila pasó la validación de columnas.');
			return 0;
		}

		foreach ($batchValido as $fila) {
			$placeHolders[] = '(' . implode(',', array_fill(0, $columnasEsperadas, '?')) . ')';
			$paramsAplanados = array_merge($paramsAplanados, $fila);
		}

		try {
			
			if(!empty($queryUpdate)){
				$this->db->preparedQuery($queryUpdate);
			}
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
			app_log('error', 'Error en guardarRegistrosBatch: ' . $e->getMessage());
			return false;
		}
	}


	public function registroCashea(string $cedula, string $id_cuota, string $nombre_grupo, string $fecha_pagar, string $monto_cuota, string $numero_cuota, string $fee, string $plata_por_cobrar, string $capital_asignado, string $id_orden, string $identificacion_orden, string $fecha_creacion_orden, string $email, string $telefono, string $nombre_usuario, string $local_origen, string $estado_deuda, string $tramo_inicial, string $tramo_actual, string $segmento): bool {
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

		$paramTypes = 'ssssssssssssssssssss';

		try {
			$stmt = $this->db->preparedQuery($query, $params, $paramTypes);
			$stmt->close();
			return true;
		} catch (Exception $e) {
			app_log('error', 'Error al guardar en cashea_customers: ' . $e->getMessage());
			return false;
		}
	}

	public function servicio(): array
	{
		return $this->db->selectQuery("SELECT id,descripcion FROM servicios WHERE status_id = 2 ORDER BY id ASC");
	}

	public function updateQuotes(array $paramsBatch, string $paramTypes): bool
	{
		try {
			foreach ($paramsBatch as $param) {
				$tipo = isset($param[1]) ? trim((string) $param[1]) : '';
				$idCuota = isset($param[0]) ? trim((string) $param[0]) : '';
				$monto = isset($param[2]) ? str_replace(',', '.', trim((string) $param[2])) : '';

				if ($idCuota === '') {
					continue;
				}

				if ($tipo === 'Pago' || $tipo === 'Pago parcial') {
					if (!is_numeric($monto)) {
						continue;
					}
					$status = ($tipo === 'Pago') ? 7 : 13;
					$query = "UPDATE cashea_customers cc SET cc.status_id = ?, cc.plata_por_cobrar = REPLACE(CAST(REPLACE(cc.plata_por_cobrar,',','.') AS DECIMAL (10,2)) - ?, '.', ',') WHERE cc.id_cuota = ?";
					$params = [$status, $monto, $idCuota];
					$types = 'iss';
				} else {
					$status = 10;
					$query = "UPDATE cashea_customers cc SET cc.status_id = ? WHERE cc.id_cuota = ?";
					$params = [$status, $idCuota];
					$types = 'is';
				}

				$stmt = $this->db->preparedQuery($query, $params, $types);
				if ($stmt) $stmt->close();

			}
			return true;
		} catch (Exception $e) {
			app_log('error', 'Error en updateQuotes: ' . $e->getMessage());
			return false;
		}
	}
}
