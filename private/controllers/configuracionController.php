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
				header("location:?view=configuracion&mode=index");
				break;

			case 'agregarProducto':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$servicio = $con->servicio();
				include(HTML_DIR . 'configuracion/agregarProducto.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'index':
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');
				$listarProducto = $con->listarProductos();
				include(HTML_DIR . 'configuracion/index.php');
				include(PUBLIC_DIR . 'general/footer.php');
				break;

			case 'guardarProductos':
				$servicio = $_POST['venta'];
				$gestion = $con->updateStatus($servicio);
				for ($i = 0; $i < count($_POST['producto']);) {
					$gestion = $con->guardarProductos($_POST['producto'][$i], $_POST['codigo'][$i], $_POST['costo'][$i], $servicio, $_POST['plan'][$i]);
					$i++;
				}
				header('location:?view=configuracion&mode=index&mensaje=exito');
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
							$response = $con->guardarRegistrosTemporalBatch($paramsBatch, $paramTypes, $servicio);
							$paramsBatch = []; // Reiniciar el lote para el siguiente grupo de filas
						}
					}

					// 4. CORRECCIÓN DE LÓGICA: Mover el cierre y la inserción final FUERA del bucle
					fclose($fp);

					// Insertar cualquier fila restante que no completó un lote
					if (!empty($paramsBatch)) {
						$response = $con->guardarRegistrosTemporalBatch($paramsBatch, $paramTypes, $servicio);
					}

					// Puedes usar una respuesta más informativa si lo deseas
					header('Location: ?view=configuracion&mode=preload&servicio=' . $servicio.'paginaActual=1');
				} else {
					echo json_encode(['error' => 'No se pudo abrir el archivo.']);
				}

				break;

			case 'preload':
				ob_clean();
				include(PUBLIC_DIR . 'general/header.php');
				include(PUBLIC_DIR . 'general/navbar.php');

				$paginaActual = isset($_GET['paginaActual']) ? $_GET['paginaActual'] : 1;
				$registrosPorPagina = 1; // Puedes ajustar este valor según tus necesidades
				
				$registros = $con->listarTemporal($_GET['servicio'],$paginaActual, $registrosPorPagina);
				var_dump($registros);
				 if (isset($registros['registros'])) {
					$registros = $registros['registros'];
					$totalPaginas = $registros['totalPaginas'];
					$paginaActual = $registros['paginaActual'];

				} else {
					$registros = [];
				}

				include(HTML_DIR . 'configuracion/confirmaCargaArchivo.php');
				include(PUBLIC_DIR . 'general/footer.php');

				//header('location:?view=configuracion&mode=cargaArchivo&mensaje=exito');
				break;


			case 'guardarRegistros':

				if (isset($_POST['data'])) {
					$registros = json_decode($_POST['data'], true);
					if (json_last_error() === JSON_ERROR_NONE) {
						if ($_POST['servicio'] == 1) {
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
				// $servicio = $con->servicio();
				// $productos = $con->productos($_SESSION['servicio_id']);
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
				$result = $con->updateResultados($_GET['id'], $_GET['nombre'], $_GET['apellido'], $_GET['cedula'], $_GET['sexo'], $_GET['nacimiento'], $_GET['hab'], $_GET['cel'], $_GET['correo'], $_GET['venta'],$_GET['productoId'],$_GET['cuenta']);

				if ($result == true){
					$json['response'] = 'true';
					header("Location: ?view=configuracion&mode=editarResultado&mensaje=exito");
				} else {
					$json['response'] = 'false';
					header("Location: ?view=configuracion&mode=editarResultado&mensaje=error");

				}
				break;

			case 'eliminar':
				$result = $con->eliminarVenta($_GET['id']);
				if ($result == true){
					$json['response'] = 'true';
					header("Location: ?view=configuracion&mode=editarResultado&mensaje=eliminado");
				} else {
					$json['response'] = 'false';
					header("Location: ?view=configuracion&mode=editarResultado&mensaje=error");

				}
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
