<?php
include (PUBLIC_DIR . 'general/session.php');
if (empty($_SESSION)) {
	header('location:index.php');
} else {
	include_once (MODEL_DIR . 'formularioModel.php');
	$conn = new database();

	if (isset($_GET['mode'])) {
		switch ($_GET['mode']) {
			case 'index':
				$noefectivo = $conn->contactoNoEfectivo();
				$estado = $conn->estado($_SESSION['servicio_id']);
				$producto = $conn->ventaProducto($_SESSION['servicio_id']);
				$efectivo = $conn->contactoEfectivo($_SESSION['servicio_id']);
				$cuentas = $conn->cuentasBancarias();
				include (PUBLIC_DIR . 'general/header.php');
				include (PUBLIC_DIR . 'general/navbar.php');
				include (HTML_DIR . 'formulario/index.php');
				include (PUBLIC_DIR . 'general/footer.php');
				break;

			case 'cliente':
				$telefono = $_POST['telefono'];
				$servicio = $_POST['servicio'];
				$cliente = $conn->buscaCliente($telefono, $servicio);

				if ($cliente) {
					foreach ($cliente as $c) {
						if ($c['status_id'] == 7) {
							$json['response'] = 'atendido';
						} else {
							$json['response'] = 'true';
							$json['id_cliente'] = $c['id'];
							$json['nacionalidad'] = $c['nombre_legal'];
							$json['nombre'] = $c['identificacion'];
							$json['telf_hab'] = $c['telf_hab'];
							$json['cedula'] = $c['telf_ofi'];
							$json['tipo_cuenta'] = $c['telf_cel'];
							$json['genero'] = ($c['correo'] == 'M') ? "Masculino" : "Femenino";
							$json['edad'] = $c['direccion'];
							$json['correo'] = $c['cuenta'];
						}
					}
				} else {
					$json['response'] = 'false';
				}
				echo json_encode($json);
				break;

			case 'ciudad':
				$id_estado = $_POST['id_estado'];
				$ciudad = $conn->ciudad($id_estado);
				$json['ciudad'] = "";
				if ($ciudad) {
					foreach ($ciudad as $c) {
						$json['response'] = 'true';
						$json['ciudad'] = $json['ciudad'] . $c['id'] . "," . strtoupper($c['ciudad']) . "|";
					}
				} else {
					$json['response'] = 'false';
				}
				echo json_encode($json);
				break;

			case 'municipio':
				// $id_ciudad = $_POST['id_ciudad'];
				// $id_estado = $_POST['id_estado'];
				$municipio = $conn->municipio($_POST['id_estado'], $_SESSION['servicio_id']);
				$json['municipio'] = "";
				if ($municipio) {
					foreach ($municipio as $m) {
						$json['response'] = 'true';
						$json['municipio'] = $json['municipio'] . $m['id'] . "," . strtoupper($m['nombre']) . "|";
					}
				} else {
					$json['response'] = 'false';
				}
				echo json_encode($json);

				break;

			case 'registro':

				$id_cliente = $_POST['id_cliente'];
				$id_usuario = $_POST['usuario'];
				$contacto = $_POST['contacto'];
				$servicio = $_POST['servicio'];
				$date = date('Y-m-d');
				$hora = date('H:i:s');

				if (isset($_POST['nacionalidad'])) {
					$nacionalidad = $_POST['nacionalidad'];
				} else {
					$nacionalidad = null;
				}
				if (isset($_POST['cuenta2'])) {
					$cuenta = $_POST['cuenta2'];
				} else {
					$cuenta = '00000000000000000000';
				}
				if (isset($_POST['edificacion'])) {
					$edificacion = $_POST['edificacion'];
				} else {
					$edificacion = null;
				}
				if (isset($_POST['efectivo'])) {
					$efectivo = $_POST['efectivo'];
				} else {
					$efectivo = null;
				}
				if (isset($_POST['tipo_cuenta'])) {
					$tipocuenta = $_POST['tipo_cuenta'];
				} else {
					$tipocuenta = null;
				}
				if (isset($_POST['venta'])) {
					$producto = $_POST['venta'];
				} else {
					$producto = null;
				}

				if (isset($_POST['noefectivo'])) {
					$noefectivo = $_POST['noefectivo'];
					$status = 3;
					
					$registro = $conn->registroGestion($contacto, $efectivo, $producto, $noefectivo, $id_usuario, $date, $id_cliente, $status, $hora, $servicio);
				} else {
					$noefectivo = null;
					if ($efectivo == 1) {
						$nombre = str_replace(',', ' ', $_POST['nombre2']);
						$apellido = str_replace(',', ' ', $_POST['apellido2']);						
						$genero = $_POST['genero'];
						$fecha_nac = $_POST['fecha_nac'];
						$cedula = $_POST['cedula2'];
						$correo = $_POST['correo2'];
						$telf_hab = $_POST['telf_hab'];
						// $telf_ofi = $_POST['telf_ofi'];
						$telf_cel = $_POST['telf_cel'];
						$estado = $_POST['estado'];
						$ciudad = $_POST['ciudad'];
						$municipio = $_POST['municipio'];
						$obs = $_POST['observaciones'];
						$fecha = date('Ymd');
						$status = 7;
						$var = 0;

						$registro = $conn->registroResultados($contacto, $efectivo, $producto, $noefectivo, $id_usuario, $date, $nombre, $apellido, $genero, $fecha_nac, $nacionalidad, $cedula, $telf_hab, $telf_cel, $correo, $estado, $ciudad, $municipio, $cuenta, $tipocuenta, $obs, $fecha, $status, $id_cliente, $var, $hora, $servicio);
						
					} else {
						$status = 4;
						$registro = $conn->registroGestion($contacto, $efectivo, $producto, $noefectivo, $id_usuario, $date, $id_cliente, $status, $hora, $servicio);
					}
				}
				header('location:?view=formulario&mode=index');
				break;

			case 'nuevocliente':
				$telefono = $_POST['telefono'];
				$cliente = $conn->buscaNuevocliente($telefono);
				if ($cliente) {
					foreach ($cliente as $c) {
						if ($c['status'] == 1) {
							$json['response'] = 'atendido';
						} else {
							if ($c['genero'] == 'M') {
								$genero = 'Masculino';
							} else {
								$genero = 'Femenino';
							}
							$json['response'] = 'true';
							$json['id_cliente'] = $c['id_cliente'];
							$json['nombre'] = $c['nombre'] . ' ' . $c['apellido'];
							$json['cedula'] = $c['cedula'];
							$json['genero'] = $genero;
							$json['fecha_nac'] = $c['fecha_nacimiento'];
							$json['telf1'] = $c['telefono'];
							$json['telf2'] = $c['telefono2'];
							$json['telf3'] = $c['telefono3'];
							$json['estado'] = $c['estado'];
							$json['referido'] = $c['referido'];
						}
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