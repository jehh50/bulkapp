<?php
include(PUBLIC_DIR . 'general/session.php');
if (empty($_SESSION)) {
	header('location:index.php');
} else {

	include_once(MODEL_DIR . 'carga_archivoModel.php');
	$conexion = new database();
	if (isset($_GET['mode'])) {

		switch ($_GET['mode']) {
			case 'index':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $conexion->servicio();
				include(HTML_DIR . 'carga_archivo/index.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'registro':
				$servicio_id = $_POST['servicio'];

				// Validación de archivo
				if (!isset($_FILES["archivo"]) || $_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) {
					die("⚠️ Error: El archivo no se ha subido correctamente o no existe.");
				}

				// Limpieza y normalización del nombre del archivo
				$archivo = $_FILES["archivo"]['name'];
				$resultado = str_replace(" ", "", $archivo);
				$array = explode(".", $resultado);
				$nombre_aleatorio = $array[0] . "." . $array[1];
				$ruta_destino = __DIR__ . '/../../public/carga_bbdd/' . $nombre_aleatorio;

				// Mover archivo
				if (!move_uploaded_file($_FILES["archivo"]["tmp_name"], $ruta_destino)) {
					die("❌ No se pudo mover el archivo a $ruta_destino. Verifica permisos.");
				}

				// Validación de existencia y apertura
				if (!file_exists($ruta_destino)) {
					die("❌ Archivo no encontrado en: $ruta_destino");
				}

				$fp = fopen($ruta_destino, "r");
				if (!$fp) {
					die("❌ No se pudo abrir el archivo: $ruta_destino");
				}

				$nombre_campo = fgetcsv($fp, 0, ";");

				while (($datos = fgetcsv($fp, 0, ";")) !== false) {
					for ($i = 0; $i < count($datos); $i++) {
						if (empty($datos[$i])) {
							$datos[$i] = 0;
						}
					}

					if ($servicio_id == 1) {
						if (isset($datos[0])) {
							$registro = $conexion->registro(
								$datos[0], // identificacion
								$datos[1], // nombre_legal
								$datos[2], // telf_hab
								$datos[3], // telf_ofi
								$datos[4], // telf_cel
								$datos[5], // correo
								$datos[6], // edad
								$datos[7], // cuenta
								$datos[8], // oferta
								date('Y-m-d'), // fecha
								$servicio_id
							);
						}
					} else {
						if (isset($datos[0])) {
							$registro = $conexion->registroCashea(
								$datos[0],  // cedula
								$datos[1],  // id_cuota
								$datos[2],  // nombre_grupo
								$datos[3],  // fecha_pagar
								$datos[4],  // monto_cuota
								$datos[5],  // numero_cuota
								$datos[6],  // fee
								$datos[7],  // plata_por_cobrar
								$datos[8],  // capital_asignado
								$datos[9],  // id_orden
								$datos[10], // identificacion_orden
								$datos[11], // fecha_creacion_orden
								$datos[12], // email
								$datos[13], // telefono
								$datos[14], // nombre_usuario
								$datos[15], // local_origen
								$datos[16], // estado_deuda
								$datos[17], // tramo_inicial
								$datos[18], // tramo_actual
								$datos[19]  // segmento
							);
						}
					}
				}

				fclose($fp);

				// Redirección final con éxito
				header('Location:?view=carga_archivo&mode=index&mensaje=exito');
				break;


			default:
				header('location:' . HTML_DIR . 'error.html');
				break;
		}
	}
}
