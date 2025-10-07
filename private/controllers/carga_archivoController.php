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
				ob_clean();

				if (empty($_FILES["archivo"]) || $_FILES["archivo"]['error'] !== UPLOAD_ERR_OK) {
					echo json_encode(['error' => 'No se recibió el archivo o hubo un error al subirlo.']);
					break;
				}
				$archivo = $_FILES["archivo"]['tmp_name'];
				$servicio = $_POST['servicio'];
				$batchSize = 1000;
				$response = json_encode(['mensaje' => 'No se procesaron filas.']); 

				if (($fp = fopen($archivo, "r")) !== false) {
					$encoding = mb_detect_encoding(file_get_contents($archivo), 'UTF-8, ISO-8859-1', true);

					$r = (fgetcsv($fp, 0, ";"));

					if(count($r) === 1){
						header('Location: ?view=carga_archivo&mode=index&mensaje=errorFormato');
						break;
					}

					$paramsBatch = [];
					$paramTypes = '';

					while (($datos = fgetcsv($fp, 0, ";")) !== false) {

						if (empty($datos) || $datos[0] === null) {
							continue;
						}

						$datos = array_map(function ($value) use ($encoding) {
							return mb_convert_encoding($value, 'UTF-8', $encoding);
						}, $datos);

						if ($servicio == 1) {
							$fila = array_slice($datos, 0, 9);
							$paramTypes = str_repeat('s', 9);
						} else {
							$fila = array_slice($datos, 0, 20);
							$paramTypes = str_repeat('s', 20);
						}

						$paramsBatch[] = $fila;
						if (count($paramsBatch) >= $batchSize) {
							$response = $con->guardarRegistrosBatch($paramsBatch, $paramTypes, $servicio);
							$paramsBatch = [];
						}
					}

					fclose($fp);

					if (!empty($paramsBatch)) {
						$response = $con->guardarRegistrosBatch($paramsBatch, $paramTypes, $servicio);
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
				ob_clean(); 
				if (empty($_FILES["archivo"]) || $_FILES["archivo"]['error'] !== UPLOAD_ERR_OK) {
					echo json_encode(['error' => 'No se recibió el archivo o hubo un error al subirlo.']);
					break;
				}
				$archivo = $_FILES["archivo"]['tmp_name'];
				$batchSize = 500;
				$response = json_encode(['mensaje' => 'No se procesaron filas.']);

				if (($fp = fopen($archivo, "r")) !== false) {
					fgetcsv($fp, 0, ";");

					$paramsBatch = [];
					$paramTypes = '';

					while (($datos = fgetcsv($fp, 0, ";")) !== false) {

						if (empty($datos) || $datos[0] === null) {
							continue;
						}
							$fila = array_slice($datos, 0, 3);
							var_dump($fila);
							$paramTypes = str_repeat('s', 1);
							var_dump($paramTypes);

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
