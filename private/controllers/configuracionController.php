<?php
ob_start();
include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));
if (!security_usuario_autenticado()) {
	header('location:index.php');
	exit;
} else {
	include_once(MODEL_DIR . 'configuracionModel.php');
	$con = new database();
	if (isset($_GET['mode'])) {
		try {
  		switch ($_GET['mode']) {
  			case 'index':
  				$nav_crumb = "Configuración / Productos";
  				$nav_title = "Productos activos";
  				$nav_subtitle = "Consulta, crea y edita los productos de cada servicio.";
  				include(PUBLIC_DIR . 'general/nheader.php');
  				include(PUBLIC_DIR . 'general/nnavbar.php');
  				$listarProducto = $con->listarProductos();
  				$servicio = $con->servicio(); // para el alta inline (diseño unified)
  				include(HTML_DIR . 'configuracion/index.php');
  				include(PUBLIC_DIR . 'general/nfooter.php');
  				break;

  			case 'actualizarProductos':
  				// Edición masiva (checkboxes o botón por fila): actualiza nombre, código, costo y plan
  				$ids     = input_int_array(isset($_POST['id']) ? $_POST['id'] : []);
  				$nombres = isset($_POST['nombre']) && is_array($_POST['nombre']) ? $_POST['nombre'] : [];
  				$codigos = isset($_POST['codigo']) && is_array($_POST['codigo']) ? $_POST['codigo'] : [];
  				$costos  = isset($_POST['costo']) && is_array($_POST['costo']) ? $_POST['costo'] : [];
  				$planes  = isset($_POST['plan']) && is_array($_POST['plan']) ? $_POST['plan'] : [];
  				foreach ($ids as $i => $id) {
  					if (!isset($nombres[$i], $codigos[$i], $costos[$i])) {
  						continue;
  					}
  					$plan = isset($planes[$i]) && $planes[$i] !== '' ? input_string($planes[$i], 50) : null;
  					$con->updateProd($id, input_string($nombres[$i], 255), input_string($codigos[$i], 100), input_amount($costos[$i]) ?? '0', $plan);
  				}
  				header("location:?view=configuracion&mode=index&mensaje=actualizado");
  				break;

  			case 'editarProducto':
  				$idProducto = input_int(isset($_GET['id']) ? $_GET['id'] : null, 1);
  				if ($idProducto === null) {
  					header('location:?view=configuracion&mode=index&mensaje=error');
  					exit;
  				}
  				include(PUBLIC_DIR . 'general/header.php');
  				include(PUBLIC_DIR . 'general/navbar.php');
  				$product = $con->products($idProducto);
  				include(HTML_DIR . 'configuracion/editarProducto.php');
  				include(PUBLIC_DIR . 'general/footer.php');
  				break;

  			case 'actualizarProducto':
  				$idProducto = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
  				$nombreProducto = input_string(isset($_POST['nombre']) ? $_POST['nombre'] : '', 255);
  				$codigoProducto = input_string(isset($_POST['codigo']) ? $_POST['codigo'] : '', 100);
  				$costoProducto = input_amount(isset($_POST['costo']) ? $_POST['costo'] : null);
  				if ($idProducto === null || $nombreProducto === '' || $costoProducto === null) {
  					header('location:?view=configuracion&mode=index&mensaje=error');
  					exit;
  				}
  				$res = $con->updateProd($idProducto, $nombreProducto, $codigoProducto, $costoProducto);
  				header("location:?view=configuracion&mode=index");
  				break;

  			case 'addProduct':
  				include(PUBLIC_DIR . 'general/header.php');
  				include(PUBLIC_DIR . 'general/navbar.php');
  				$servicio = $con->servicio();
  				include(HTML_DIR . 'configuracion/agregarProducto.php');
  				include(PUBLIC_DIR . 'general/footer.php');
  				break;

  			case 'guardarProductos':
  				$servicio = input_enum(isset($_POST['venta']) ? $_POST['venta'] : null, Constantes::servicios());
  				$productos = isset($_POST['producto']) && is_array($_POST['producto']) ? $_POST['producto'] : [];
  				$codigos = isset($_POST['codigo']) && is_array($_POST['codigo']) ? $_POST['codigo'] : [];
  				$costos = isset($_POST['costo']) && is_array($_POST['costo']) ? $_POST['costo'] : [];
  				$planes = isset($_POST['plan']) && is_array($_POST['plan']) ? $_POST['plan'] : [];
  				if ($servicio === null) {
  					header('location:?view=configuracion&mode=index&mensaje=error');
  					exit;
  				}
  				$servicio = (int) $servicio;
  				$gestion = $con->updateStatus($servicio);
  				foreach ($productos as $i => $producto) {
  					if (!isset($codigos[$i], $costos[$i])) {
  						continue;
  					}
  					$gestion = $con->guardarProductos(
  						input_string($producto, 255),
  						input_string($codigos[$i], 100),
  						input_amount($costos[$i]) ?? '0',
  						$servicio,
  						isset($planes[$i]) ? input_string($planes[$i], 50) : null
  					);
  				}
  				header('location:?view=configuracion&mode=index&mensaje=exito');
  				break;

  			case 'editarResultado':
  				$nav_crumb = 'Configuración / Resultados';
  				$nav_title = 'Edición de resultados';
  				$nav_subtitle = 'Busca una venta por cédula para corregir sus datos.';
  				include(PUBLIC_DIR . 'general/nheader.php');
  				include(PUBLIC_DIR . 'general/nnavbar.php');
  				include(HTML_DIR . 'configuracion/editarResultado.php');
  				include(PUBLIC_DIR . 'general/nfooter.php');
  				break;

  			case 'buscar':
  				$cedulaBuscada = input_string(isset($_POST['cedula']) ? $_POST['cedula'] : '', 30);
  				$resultados = ($cedulaBuscada === '') ? false : $con->busquedaResultados($cedulaBuscada);
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
  				$idResultado = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
  				$nombreResultado = input_string(isset($_POST['nombre']) ? $_POST['nombre'] : '', 100);
  				$apellidoResultado = input_string(isset($_POST['apellido']) ? $_POST['apellido'] : '', 100);
  				$cedulaResultado = input_digits(isset($_POST['cedula']) ? $_POST['cedula'] : '', 10);
  				$sexoResultado = input_enum(isset($_POST['sexo']) ? $_POST['sexo'] : null, ['M', 'F', '']);
  				$nacimientoResultado = input_string(isset($_POST['nacimiento']) ? $_POST['nacimiento'] : '', 10);
  				$habResultado = input_phone(isset($_POST['hab']) ? $_POST['hab'] : '');
  				$celResultado = input_phone(isset($_POST['cel']) ? $_POST['cel'] : '');
  				$correoResultado = input_email(isset($_POST['correo']) ? $_POST['correo'] : '', 150);
  				$ventaResultado = input_string(isset($_POST['venta']) ? $_POST['venta'] : '', 20);
  				$productoResultado = input_int(isset($_POST['productoId']) ? $_POST['productoId'] : null, 1);
  				$cuentaResultado = input_pan(isset($_POST['cuenta']) ? $_POST['cuenta'] : '');

  				if ($idResultado === null || $nombreResultado === '' || $apellidoResultado === '' || $cedulaResultado === '' || $sexoResultado === null || $nacimientoResultado === '' || $correoResultado === null || $productoResultado === null) {
  					header("Location: ?view=configuracion&mode=editarResultado&mensaje=error");
  					exit;
  				}

  				$result = $con->updateResultados($idResultado, $nombreResultado, $apellidoResultado, $cedulaResultado, $sexoResultado, $nacimientoResultado, $habResultado, $celResultado, $correoResultado, $ventaResultado, $productoResultado, $cuentaResultado);

  				if ($result == true){
  					$json['response'] = 'true';
  					header("Location: ?view=configuracion&mode=editarResultado&mensaje=exito");
  				} else {
  					$json['response'] = 'false';
  					header("Location: ?view=configuracion&mode=editarResultado&mensaje=error");

  				}
  				break;

  			case 'eliminar':
  				$idEliminar = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
  				if ($idEliminar === null) {
  					header("Location: ?view=configuracion&mode=editarResultado&mensaje=error");
  					exit;
  				}
  				$result = $con->eliminarVenta($idEliminar);
  				if ($result == true){
  					$json['response'] = 'true';
  					header("Location: ?view=configuracion&mode=editarResultado&mensaje=eliminado");
  				} else {
  					$json['response'] = 'false';
  					header("Location: ?view=configuracion&mode=editarResultado&mensaje=error");

  				}
  				break;

  			case 'ciudad_':
  				$id_estado = input_int(isset($_POST['id_estado']) ? $_POST['id_estado'] : null, 1);
  				$ciudad = ($id_estado === null) ? false : $con->ciudad_($id_estado);
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
		} catch (Throwable $e) {
		  app_log('error', 'Error en el controlador configuracion: ' . $e->getMessage(), array(
		    'excepcion' => get_class($e),
		    'archivo' => $e->getFile(),
		    'linea' => $e->getLine(),
		    'modo' => isset($_GET['mode']) ? $_GET['mode'] : '',
		  ));

		  app_responder_error();
		}
	}
}
