<?php
include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_ADMINISTRADOR));
if (!security_usuario_autenticado()) {
	header('location:index.php');
	exit;
} else {

	include_once(MODEL_DIR . 'carga_archivoModel.php');
	$con = new database();
	if (isset($_GET['mode'])) {

		try {
  		switch ($_GET['mode']) {
  			case 'index':
  				$nav_crumb = "Configuración / Carga BBDD";
  				$nav_title = "Carga de base de datos";
  				$nav_subtitle = "Importa masivamente clientes y cuotas desde archivos CSV por cada servicio.";
  				include(PUBLIC_DIR . 'general/nheader.php');
  				include(PUBLIC_DIR . 'general/nnavbar.php');
  				$servicio = $con->servicio();
  				include(HTML_DIR . 'carga_archivo/index.php');
  				include(PUBLIC_DIR . 'general/nfooter.php');
  				break;

  			case 'registro':
  				ob_clean();

  				$validacionArchivo = security_validate_csv_upload(isset($_FILES["archivo"]) ? $_FILES["archivo"] : null);
  				if (!$validacionArchivo[0]) {
  					header('Location: ?view=carga_archivo&mode=index&mensaje=errorArchivo');
  					break;
  				}
  				$archivo = $_FILES["archivo"]['tmp_name'];
  				$servicio = input_enum(isset($_POST['servicio']) ? $_POST['servicio'] : null, Constantes::servicios());
  				if ($servicio === null) {
  					header('Location: ?view=carga_archivo&mode=index&mensaje=errorArchivo');
  					break;
  				}
  				$servicio = (int) $servicio;
  				$batchSize = 1000;
  				$response = json_encode(['mensaje' => 'No se procesaron filas.']); 

  				if (($fp = fopen($archivo, "r")) !== false) {
  					$encoding = mb_detect_encoding(file_get_contents($archivo), 'UTF-8, ISO-8859-1', true);
  					if ($encoding === false) { $encoding = 'UTF-8'; }

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

  						if (Constantes::esServicioBancario($servicio)) {
  							$fila =  array_slice($datos, 0, 9);
  							array_push($fila,$servicio);
  							$paramTypes = str_repeat('s', 10);
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
  					header('Location: ?view=carga_archivo&mode=index&mensaje=errorArchivo');
  				}

  				break;

  			case 'download':
  				header("Content-disposition: attachment; filename=modelo_bbdd.csv");
  				header("Content-type: application/vnd.ms-excel");
  				readfile(PUBLIC_DIR . "/carga_bbdd/modelo_bbdd.csv");
  				break;

  			case 'liberarCuotas';
  				include(PUBLIC_DIR . 'general/header.php');
  				include(PUBLIC_DIR . 'general/navbar.php');
  				include(HTML_DIR . 'carga_archivo/liberarCuotas.php');
  				include(PUBLIC_DIR . 'general/footer.php');
  				break;

  			case 'freeQuotes';
  				ob_clean(); 
  				$validacionArchivo = security_validate_csv_upload(isset($_FILES["archivo"]) ? $_FILES["archivo"] : null);
  				if (!$validacionArchivo[0]) {
  					echo json_encode(['error' => $validacionArchivo[1]]);
  					break;
  				}
  				$archivo = $_FILES["archivo"]['tmp_name'];
  				$batchSize = 50;
  				$response = json_encode(['mensaje' => 'No se procesaron filas.']);

  				if (($fp = fopen($archivo, "r")) !== false) {
  					fgetcsv($fp, 0, ",");

  					$paramsBatch = [];
  					$paramTypes = '';

  					while (($datos = fgetcsv($fp, 0, ",")) !== false) {

  						if (empty($datos) || $datos[0] === null) {
  							continue;
  						}
  							$fila = array_slice($datos, 0, 3);
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
		} catch (Throwable $e) {
		  app_log('error', 'Error en el controlador carga_archivo: ' . $e->getMessage(), array(
		    'excepcion' => get_class($e),
		    'archivo' => $e->getFile(),
		    'linea' => $e->getLine(),
		    'modo' => isset($_GET['mode']) ? $_GET['mode'] : '',
		  ));

		  app_responder_error();
		}
	}
}
