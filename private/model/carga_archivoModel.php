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
	public function guardarRegistrosBatch(array $batch, int $servicio)
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

	public function servicio(): array
	{
		return $this->db->selectQuery("SELECT id,descripcion FROM servicios WHERE status_id = 2 ORDER BY id ASC");
	}
}
