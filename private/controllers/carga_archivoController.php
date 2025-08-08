<?php
include(PUBLIC_DIR . 'general/session.php');
if (empty($_SESSION)) {
	header('location:index.php');
} else {

	include_once(MODEL_DIR . 'carga_archivoModel.php');
	$con = new database();
	if (isset($_GET['mode'])) {

		switch ($_GET['mode']) {
			case 'index':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $con->servicio();
				include(HTML_DIR . 'carga_archivo/index.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'registro':
				ob_clean(); // Limpia el buffer de salida
				$time = date('H:i:s');
				// Validaciones básicas de seguridad y existencia del archivo
				if (empty($_FILES["archivo"]) || $_FILES["archivo"]['error'] !== UPLOAD_ERR_OK) {
					echo json_encode(['error' => 'No se recibió el archivo o hubo un error al subirlo.']);
					break;
				}
				$archivo = $_FILES["archivo"]['tmp_name'];
				$servicio = $_POST['servicio'];
				$batchSize = 500; // Tamaño del lote para inserciones
				$response = json_encode(['mensaje' => 'No se procesaron filas.']); // Respuesta por defecto

				if (($fp = fopen($archivo, "r")) !== false) {
					// Detectar la codificación del archivo una sola vez
					$encoding = mb_detect_encoding(file_get_contents($archivo), 'UTF-8, ISO-8859-1', true);

					// 1. LEER Y DESCARTAR LA CABECERA (HEADER) ANTES DEL BUCLE
					// Esto posiciona el puntero del archivo en la primera fila de datos.
					fgetcsv($fp, 0, ";");

					$paramsBatch = [];
					$paramTypes = '';

					// 2. CORRECCIÓN DEL BUCLE: Leer una nueva línea del archivo en cada iteración
					while (($datos = fgetcsv($fp, 0, ";")) !== false) {

						// Omitir filas vacías que fgetcsv a veces puede retornar
						if (empty($datos) || $datos[0] === null) {
							continue;
						}

						// Convertir la codificación de cada celda a UTF-8
						$datos = array_map(function ($value) use ($encoding) {
							return mb_convert_encoding($value, 'UTF-8', $encoding);
						}, $datos);

						// Preparar la fila actual según el servicio
						if ($servicio == 1) {
							$fila = array_slice($datos, 0, 9);
							$paramTypes = str_repeat('s', 8) . 'i'; // Tipos para el servicio 1
						} else {
							$fila = array_slice($datos, 0, 20);
							$paramTypes = str_repeat('s', 19) . 'i'; // Tipos para el servicio 2
						}

						// 3. CORRECCIÓN DEL LOTE: Añadir la fila al lote (array de arrays)
						$paramsBatch[] = $fila;

						// Si el lote alcanza el tamaño definido, insertarlo en la BD
						if (count($paramsBatch) >= $batchSize) {
							$response = $con->guardarRegistrosBatch($paramsBatch, $paramTypes, $servicio);
							$paramsBatch = []; // Reiniciar el lote para el siguiente grupo de filas
						}
					}

					// 4. CORRECCIÓN DE LÓGICA: Mover el cierre y la inserción final FUERA del bucle
					fclose($fp);

					// Insertar cualquier fila restante que no completó un lote
					if (!empty($paramsBatch)) {
						$response = $con->guardarRegistrosBatch($paramsBatch, $paramTypes, $servicio);
					}

					// Puedes usar una respuesta más informativa si lo deseas
					header('Location: ?view=carga_archivo&mode=index&mensaje=exito');
				} else {
					echo json_encode(['error' => 'No se pudo abrir el archivo.']);
				}

				break;


			default:
				header('location:' . HTML_DIR . 'error.html');
				break;
		}
	}
}
