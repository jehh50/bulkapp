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
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$registros = [];
				var_dump($_POST);
				$archivo = $_FILES["archivo"]['tmp_name'];
				if (($fp = fopen($archivo, "r")) !== false) {
					$encoding = mb_detect_encoding(file_get_contents($archivo), 'UTF-8, ISO-8859-1', true);
					$header = fgetcsv($fp, 0, ";");
					if($_POST['servicio'] == 1) {
						while (($datos = fgetcsv($fp, 0, ";")) !== false) {
							if ($datos) {
								$datos = array_map(function ($value) use ($encoding) {
									return mb_convert_encoding($value, 'UTF-8', $encoding);
								}, $datos);
								$registro = [
									'identificacion' => $datos[0],
									'nombre_legal' => $datos[1],
									'telf_hab' => $datos[2],
									'telf_ofi' => $datos[3],
									'telf_cel' => $datos[4],
									'correo' => $datos[5],
									'direccion' => $datos[6],
									'cuenta' => $datos[7],
									'oferta' => $datos[8]
								];
								$registros[] = $registro;
							} else {
								echo "Archivo vacio";
							}
						}
					}else{
						while (($datos = fgetcsv($fp, 0, ";")) !== false) {
							if ($datos) {
								$datos = array_map(function ($value) use ($encoding) {
									return mb_convert_encoding($value, 'UTF-8', $encoding);
								}, $datos);
								$registro = [
									'cedula' => $datos[0],
									'id_cuota' => $datos[1],
									'nombre_grupo' => $datos[2],
									'fecha_pagar' => $datos[3],
									'monto_cuota' => $datos[4],
									'numero_cuota' => $datos[5],
									'fee' => $datos[6],
									'plata_por_cobrar' => $datos[7],
									'capital_asignado' => $datos[8],
									'id_orden' => $datos[9],
									'identificacion_orden' => $datos[10],
									'fecha_creacion_orden' => $datos[11],
									'email' => $datos[12],
									'telefono' => $datos[13],
									'nombre_usuario' => $datos[14],
									'local_origen' => $datos[15],
									'estado_deuda' => $datos[16],
									'tramo_inicial' => $datos[17],
									'tramo_actual' => $datos[18],
									'segmento' => $datos[19]
								];
								$registros[] = $registro;
							} else {
								echo "Archivo vacio";
							}
						}
					}
					fclose($fp);
				}

				include(HTML_DIR . 'configuracion/confirmaCargaArchivo.php');
				include(PUBLIC_DIR . 'general/footer.php');

				//header('location:?view=configuracion&mode=cargaArchivo&mensaje=exito');
				break;

			case 'guardarRegistros':
				
				if (isset($_POST['data'])) {
					$registros = json_decode($_POST['data'], true);
					if (json_last_error() === JSON_ERROR_NONE) {
						if($_POST['servicio'] == 1) {
							foreach ($registros as $registro) {
								$con->registro(
									$registro['identificacion'],
									$registro['nombre_legal'],
									$registro['telf_hab'],
									$registro['telf_ofi'],
									$registro['telf_cel'],
									$registro['correo'],
									$registro['direccion'],
									$registro['cuenta'],
									$registro['oferta'],
									$_POST['servicio']
								);
							}
							$var = 200;
						} else {
							foreach ($registros as $registro) {
								$con->registroCashea(
									$registro['cedula'],
									$registro['id_cuota'],
									$registro['nombre_grupo'],
									$registro['fecha_pagar'],
									$registro['monto_cuota'],
									$registro['numero_cuota'],
									$registro['fee'],
									$registro['plata_por_cobrar'],
									$registro['capital_asignado'],
									$registro['id_orden'],
									$registro['identificacion_orden'],
									$registro['fecha_creacion_orden'],
									$registro['email'],
									$registro['telefono'],
									$registro['nombre_usuario'],
									$registro['local_origen'],
									$registro['estado_deuda'],
									$registro['tramo_inicial'],
									$registro['tramo_actual'],
									$registro['segmento'],
									$_POST['servicio']
								);
							}
							$var = 200;
						}
					} else {
						$var = 'Error al decodificar los datos JSON: ' . json_last_error_msg();
						header("Location: ?view=configuracion&mode=cargaArchivo&estatus=$var");
					}
				} else {
					$var = 'No se enviaron datos para guardar.';
					header("Location: ?view=configuracion&mode=cargaArchivo&estatus=$var");

				}
				header("Location: ?view=configuracion&mode=cargaArchivo&estatus=$var");
				break;

			case 'editarResultado':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $con->servicio();
				$productos = $con->productos($_SESSION['servicio_id']);
				include(HTML_DIR . 'configuracion/editarResultado.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'buscar':
				$resultados = $con->busquedaResultados($_POST['cedula']);
				if ($resultados) {
					$json = [
						'response' => 'true',
						'count' => count($resultados),
						'data' => []
					];

					foreach ($resultados as $resultado) {
						$json['data'][] = [
							'id_resultado' => $resultado['resultado_id'],
							'nombre' => $resultado['nombre'],
							'apellido' => $resultado['apellido'],
							'cedula' => $resultado['cedula'],
							'telf_hab' => $resultado['telf_hab'],
							'telf_celular' => $resultado['telf_celular'],
							'correo' => $resultado['correo'],
							'cuenta' => $resultado['cuenta'],
							'servicio' => $resultado['descripcion'],
							'cod_servicio' => $resultado['servicio_id'],
							'genero' => $resultado['genero'],
							'nacimiento' => $resultado['fecha_nacimiento'],
							'id_gestion' => $resultado['gestion_id'],
							'producto_id' => $resultado['producto_id'],
							'name_product' => $resultado['name_product'],
							'fecha_venta' => $resultado['fecha_venta']
						];
					}
				} else {
					$json = ['response' => 'false'];
				}
				echo json_encode($json);
				break;


			case 'actualiza':
				var_dump($_GET);echo '<br>';
				// echo $_GET['nombre'].'-'.$_GET['apellido'];

				$result = $con->updateResultados($_GET['id'],$_GET['nombre'],$_GET['apellido'],$_GET['cedula'], $_GET['sexo'],$_GET['nacimiento'],$_GET['hab'],$_GET['cel'],$_GET['correo'],$_GET['venta']);

				// if ($ejecucion) {
				// 	$json['response'] = 'true';
				// } else {
				// 	$json['response'] = 'false';
				// }
				// echo json_encode($json);
				break;

			case 'eliminar':
				$eliminar = $con->eliminarVenta($_GET['id']);
				$json['response'] = ($eliminar == 'true') ? 'true' : $eliminar;
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
			
			case 'productos':
				$productos = $con->listarProductos();
				// var_dump($productos);
				$json['productos'] = "";
					if ($productos) {
						foreach ($productos as $c) {
							$json['response'] = 'true';
							$json['productos'] = $json['productos'] . $c['id'] . "," . strtoupper($c['descripcion']) . "|";
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