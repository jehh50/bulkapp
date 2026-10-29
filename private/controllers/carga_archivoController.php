<?php
include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));
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
  				$totalInsertadas = 0;
  				$huboError = false;

  				if (($fp = fopen($archivo, "r")) !== false) {
  					$muestra = fread($fp, 1048576); // 1 MB basta para detectar la codificación
  					rewind($fp);
  					$encoding = mb_detect_encoding($muestra, 'UTF-8, ISO-8859-1', true);
  					if ($encoding === false) { $encoding = 'UTF-8'; }

  					$r = fgetcsv($fp, 0, ";");

  					if ($r === false || count($r) === 1) {
  						fclose($fp);
  						header('Location: ?view=carga_archivo&mode=index&mensaje=errorFormato');
  						break;
  					}

  					$paramsBatch = [];

  					while (($datos = fgetcsv($fp, 0, ";")) !== false) {

  						if (empty($datos) || $datos[0] === null) {
  							continue;
  						}

  						$datos = array_map(function ($value) use ($encoding) {
  							return mb_convert_encoding($value, 'UTF-8', $encoding);
  						}, $datos);

  						if (Constantes::esServicioBancario($servicio)) {
  							$fila = array_slice($datos, 0, 9);
  							array_push($fila, $servicio);
  						} else {
  							$fila = array_slice($datos, 0, 20);
  						}

  						$paramsBatch[] = $fila;
  						if (count($paramsBatch) >= $batchSize) {
  							$res = $con->guardarRegistrosBatch($paramsBatch, $servicio);
  							if ($res === false) { $huboError = true; } else { $totalInsertadas += $res; }
  							$paramsBatch = [];
  						}
  					}

  					fclose($fp);

  					if (!empty($paramsBatch)) {
  						$res = $con->guardarRegistrosBatch($paramsBatch, $servicio);
  						if ($res === false) { $huboError = true; } else { $totalInsertadas += $res; }
  					}

  					if ($huboError) {
  						header('Location: ?view=carga_archivo&mode=index&mensaje=errorProceso');
  					} elseif ($totalInsertadas === 0) {
  						header('Location: ?view=carga_archivo&mode=index&mensaje=sinDatos');
  					} else {
  						header('Location: ?view=carga_archivo&mode=index&mensaje=exito');
  					}
  				} else {
  					header('Location: ?view=carga_archivo&mode=index&mensaje=errorArchivo');
  				}

  				break;

  			case 'download':
  				header("Content-disposition: attachment; filename=modelo_bbdd.csv");
  				header("Content-type: application/vnd.ms-excel");
  				readfile(PUBLIC_DIR . "/carga_bbdd/modelo_bbdd.csv");
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
