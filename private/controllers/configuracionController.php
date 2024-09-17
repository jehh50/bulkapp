<?php
ob_start();
include(PUBLIC_DIR . 'general/session.php');
if (empty($_SESSION)) {
	header('location:index.php');
} else {
	include_once(MODEL_DIR . 'configuracionModel.php');
	$con = new database();
	if (isset($_GET['mode'])) {
		switch ($_GET['mode']) {
			case 'editarProducto':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $con->servicio();
				$product = $con->products($_GET['id']);
				include(HTML_DIR . 'configuracion/editarProducto.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'actualizarProducto':
				$res = $con->updateProd($_POST["id"], $_POST["nombre"], $_POST["codigo"], $_POST['costo']);
				header("location:?view=configuracion&mode=consultarProducto");
				break;

			case 'agregarProducto':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $con->servicio();
				include(HTML_DIR . 'configuracion/agregarProducto.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'consultarProducto':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$listarProducto = $con->listarProductos();
				include(HTML_DIR . 'configuracion/consultarProducto.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'guardarProductos':
				$servicio = $_POST['venta'];
				$gestion = $con->updateStatus($servicio);
				for ($i = 0; $i < count($_POST['producto']); ) {
					$gestion = $con->guardarProductos($_POST['producto'][$i], $_POST['codigo'][$i], $_POST['costo'][$i], $servicio, $_POST['plan'][$i]);
					$i++;
				}
				header('location:?view=configuracion&mode=consultarProducto&mensaje=exito');
				break;

			case 'cargaArchivo':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $con->servicio();
				include(HTML_DIR . 'configuracion/cargaArchivo.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'download':
				header("Content-disposition: attachment; filename=modelo_bbdd.csv");
				header("Content-type: application/vnd.ms-excel");
				readfile(PUBLIC_DIR . "/carga_bbdd/modelo_bbdd.csv");
				break;

			case 'registro':
				ob_clean();
				$archivo = $_FILES["archivo"]['tmp_name'];
				if (($fp = fopen($archivo, "r")) !== false) {
					$header = fgetcsv($fp, 0, ";");
					while (($datos = fgetcsv($fp, 0, ";")) !== false) {
						if ($datos) {
							$i = 1;
							$identificacion = $datos[0];
							$nombre_legal = $datos[1];
							$telf_hab = $datos[2];
							$telf_ofi = $datos[3];
							$telf_cel = $datos[4];
							$correo = $datos[5];
							$direccion = $datos[6];
							$cuenta = $datos[7];

							$registro = $con->registro($identificacion, $nombre_legal, $telf_hab, $telf_ofi, $telf_cel, $correo, $direccion, $cuenta, $_POST['servicio']);
							$i++;
						}else{
							echo "Archivo vacio";
						}
					}
					fclose($fp);
				}
				header('location:?view=configuracion&mode=cargaArchivo&mensaje=exito');
				break;

			case 'editarResultado':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $con->servicio();
				include(HTML_DIR . 'configuracion/editarResultado.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'buscar':
				$ejecucion = $con->busquedaResultados($_POST['cedula']);
				$i = 1;
				if ($ejecucion) {
					foreach ($ejecucion as $b) {
						if ($b) {
							$json['response'] = 'true';
							$json['id_resultado'] = $b['id'];
							$json['nombre'] = $b['nombre'];
							$json['apellido'] = $b['apellido'];
							$json['cedula'] = $b['cedula'];
							$json['telf_hab'] = $b['telf_hab'];
							$json['telf_ofi'] = $b['telf_ofi'];
							$json['telf_celular'] = $b['telf_celular'];
							$json['correo'] = $b['correo'];
							$json['cuenta'] = $b['cuenta'];
							$json['servicio'] = $b['descripcion'];
							$json['cod_servicio'] = $b['id'];
							$json['genero'] = $b['genero'];
							$json['nacimiento'] = $b['fecha_nacimiento'];
							$json['id_gestion'] = $b['gestion_id'];

							$i++;
						} else {
							$json['response'] = 'eliminado';
						}
					}
				} else {
					$json['response'] = 'false';
				}
				echo json_encode($json);
				break;

			case 'actualiza':
				$nombre = str_replace(',', ' ', $_POST['nombre']);
				$apellido = str_replace(',', ' ', $_POST['apellido']);
				$ejecucion = $con->updateResultados($_POST['id_resultado'], $nombre, $apellido, $_POST['cedula'], $_POST['telf_hab'], $_POST['telf_ofi'], $_POST['telf_cel'], $_POST['correo'], $_POST['cuenta'], $_POST['cod_servicio'], $_POST['genero'], $_POST['fecha_nac']);

				if ($ejecucion) {
					$json['response'] = 'true';
				} else {
					$json['response'] = 'false';
				}
				echo json_encode($json);
				break;

			case 'eliminar':
				$date = date('Y-m-d');
				$ejecucion = $con->eliminarVenta($_POST['rechazo'], $_POST['cedula'], $_POST['servicio'], $_POST['id_resultado'], $_SESSION['id'], $date, $_POST['id_gestion']);
				if ($ejecucion) {
					$json['response'] = 'true';
				} else {
					$json['response'] = 'false';
				}
				echo json_encode($json);
				break;

			case 'ciudad_':
				$id_estado = $_POST['id_estado'];
				$ciudad = $con->ciudad_($id_estado);
				$json['ciudad'] = "";
				if ($ciudad) {
					foreach ($ciudad as $c) {
						$json['response'] = 'true';
						$json['ciudad'] = $json['ciudad'] . $c['id_ciudad'] . "," . $c['ciudad'] . "|";
					}
				} else {
					$json['response'] = 'false';
				}
				echo json_encode($json);
				break;

			default:
				header('location:' . HTML_DIR . 'error.html');
				break;
		}
	}
}