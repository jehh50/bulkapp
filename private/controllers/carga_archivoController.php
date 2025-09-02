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
				$response = json_encode(['mensaje' => 'No se procesaron filas.']); // Respuesta por defecto
				// ...
				$batchSize = 500;

				if (($fp = fopen($archivo, "r")) !== false) {
					$encoding = mb_detect_encoding(file_get_contents($archivo), 'UTF-8, ISO-8859-1', true);
					fgetcsv($fp, 0, ";"); // descarta header

					$paramsBatch = [];
					$cols = ($servicio == 1) ? 9 : 20; // columnas esperadas fijas

					while (($datos = fgetcsv($fp, 0, ";")) !== false) {
						if (empty($datos) || $datos[0] === null) continue;

						// Normaliza encoding
						$datos = array_map(function ($v) use ($encoding) {
							return mb_convert_encoding($v, 'UTF-8', $encoding ?: 'UTF-8');
						}, $datos);

						// Toma primeras N columnas y pad con NULL hasta completar
						$fila = array_slice($datos, 0, $cols);
						if (count($fila) < $cols) {
							$fila = array_pad($fila, $cols, null);
						}

						$paramsBatch[] = $fila;

						if (count($paramsBatch) >= $batchSize) {
							$response = $con->guardarRegistrosBatch($paramsBatch, $cols, $servicio);
							$paramsBatch = [];
						}
					}
					fclose($fp);

					if (!empty($paramsBatch)) {
						$response = $con->guardarRegistrosBatch($paramsBatch, $cols, $servicio);
					}

					header('Location: ?view=carga_archivo&mode=index&mensaje=exito');
				} else {
					echo json_encode(['error' => 'No se pudo abrir el archivo.']);
				}

				break;

			case 'liberarCuotas';
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				include(HTML_DIR . 'carga_archivo/liberarCuotas.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'freeQuotes';
				ob_clean(); // Limpia el buffer de salida
				// Validaciones básicas de seguridad y existencia del archivo
				if (empty($_FILES["archivo"]) || $_FILES["archivo"]['error'] !== UPLOAD_ERR_OK) {
					echo json_encode(['error' => 'No se recibió el archivo o hubo un error al subirlo.']);
					break;
				}
				$archivo = $_FILES["archivo"]['tmp_name'];
				$batchSize = 500; // Tamaño del lote para inserciones
				$response = json_encode(['mensaje' => 'No se procesaron filas.']); // Respuesta por defecto

				if (($fp = fopen($archivo, "r")) !== false) {
					// 1. LEER Y DESCARTAR LA CABECERA (HEADER) ANTES DEL BUCLE
					// Esto posiciona el puntero del archivo en la primera fila de datos.
					fgetcsv($fp, 0, ",");

					$paramsBatch = [];
					$paramTypes = '';

					// 2. CORRECCIÓN DEL BUCLE: Leer una nueva línea del archivo en cada iteración
					while (($datos = fgetcsv($fp, 0, ",")) !== false) {

						// Omitir filas vacías que fgetcsv a veces puede retornar
						if (empty($datos) || $datos[0] === null) {
							continue;
						}

						// Preparar la fila actual según el servicio
						$fila = array_slice($datos, 0, 2);
						$paramTypes = str_repeat('s', 1);

						$paramsBatch[] = $fila;

						if (count($paramsBatch) >= $batchSize) {
							$response = $con->updateQuotes($paramsBatch, $paramTypes);
							$paramsBatch = [];
						}
					}

					fclose($fp);

					if (!empty($paramsBatch)) {
						$response = $con->updateQuotes($paramsBatch, $paramTypes);
					}

					header('Location: ?view=carga_archivo&mode=liberarCuotas&mensaje=exito');
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
