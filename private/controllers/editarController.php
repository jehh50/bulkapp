<?php
include(PUBLIC_DIR.'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_ADMINISTRADOR));
if (!security_usuario_autenticado()) { header('location:index.php'); exit; } else {

include_once(MODEL_DIR.'editarModel.php');
$conexion = new database();

	if (isset($_GET['mode'])) {
		try {
  		switch ($_GET['mode']) {
  			case 'index':
  				include(PUBLIC_DIR.'general/header.php');
  				include(PUBLIC_DIR.'general/navbar.php');
  				$servicio = $conexion->servicio();
  				include(HTML_DIR.'editar/index.php');
  				include(PUBLIC_DIR.'general/footer.php');
  			break;
  #----------------------------------------------------------------------------------------------------------------------
  			case 'buscar':
  				$cedulaBuscada = input_digits(isset($_POST['cedula']) ? $_POST['cedula'] : '', 10);
  				$ejecucion = ($cedulaBuscada === '') ? false : $conexion->busquedaResultados($cedulaBuscada);
  				$i = 1;
  				if ($ejecucion) {
  					foreach ($ejecucion as $b) {
  						if($b['status'] == 0){
  							$json['response'] 		= 	'true';
  							$json['id_resultado'] 	=	$b['id_resultado'];
  							$json['nombre']			=	$b['nombre'];
  							$json['apellido']		=	$b['apellido'];
  							$json['cedula'] 		=	$b['cedula'];
  							$json['telf_hab'] 		=	$b['telf_hab'];
  							$json['telf_ofi'] 		=	$b['telf_ofi'];
  							$json['telf_celular']	=	$b['telf_celular'];
  							$json['correo'] 		=	$b['correo'];
  							$json['cuenta'] 		=	$b['cuenta'];
  							$json['servicio']		=	$b['descripcion'];
  							$json['cod_servicio']	= 	$b['cod_servicio'];
  							$json['genero']			= 	$b['genero'];
  							$json['nacimiento']		= 	$b['fecha_nacimiento'];
  							$json['id_gestion']		=	$b['id_gestion'];

  							$i++;
  						}
  						else{
  							$json['response'] 	= 	'eliminado';	
  						}
  					}
  				}
  				else{
  					$json['response'] 	= 	'false';
  				}
  				echo json_encode($json);
  			break;
  #----------------------------------------------------------------------------------------------------------------------
  			case 'actualiza':
  				$idResultado = input_int(isset($_POST['id_resultado']) ? $_POST['id_resultado'] : null, 1);
  				$nombre = input_letters(isset($_POST['nombre']) ? $_POST['nombre'] : '', 100);
  				$apellido = input_letters(isset($_POST['apellido']) ? $_POST['apellido'] : '', 100);
  				$cedula = input_digits(isset($_POST['cedula']) ? $_POST['cedula'] : '', 10);
  				$telfHab = input_phone(isset($_POST['telf_hab']) ? $_POST['telf_hab'] : '');
  				$telfOfi = input_phone(isset($_POST['telf_ofi']) ? $_POST['telf_ofi'] : '');
  				$telfCel = input_phone(isset($_POST['telf_cel']) ? $_POST['telf_cel'] : '');
  				$correo = input_email(isset($_POST['correo']) ? $_POST['correo'] : '', 150);
  				$cuenta = input_pan(isset($_POST['cuenta']) ? $_POST['cuenta'] : '');
  				$codServicio = input_string(isset($_POST['cod_servicio']) ? $_POST['cod_servicio'] : '', 20);
  				$genero = input_enum(isset($_POST['genero']) ? $_POST['genero'] : null, ['M', 'F', '']);
  				$fechaNac = input_string(isset($_POST['fecha_nac']) ? $_POST['fecha_nac'] : '', 10);

  				if ($idResultado === null || $nombre === '' || $apellido === '' || $cedula === '' || $correo === null || $codServicio === '' || $genero === null || $fechaNac === '') {
  					echo json_encode(['response' => 'false']);
  					break;
  				}

  				$ejecucion 	= $conexion->updateResultados($idResultado, $nombre, $apellido, $cedula, $telfHab, $telfOfi, $telfCel, $correo, $cuenta, $codServicio, $genero, $fechaNac);

  				if ($ejecucion) {
  					$json['response']	= 	'true';
  				}
  				else{
  					$json['response'] 	= 	'false';
  				}
  				echo json_encode($json);

  			break;
  #----------------------------------------------------------------------------------------------------------------------
  			case 'eliminar':
  				$date = date('Y-m-d');
  				$motivo = input_int(isset($_POST['rechazo']) ? $_POST['rechazo'] : null, 1);
  				$cedula = input_digits(isset($_POST['cedula']) ? $_POST['cedula'] : '', 10);
  				$servicio = input_string(isset($_POST['servicio']) ? $_POST['servicio'] : '', 20);
  				$idResultado = input_int(isset($_POST['id_resultado']) ? $_POST['id_resultado'] : null, 1);
  				$idGestion = input_int(isset($_POST['id_gestion']) ? $_POST['id_gestion'] : null, 1);
  				if ($motivo === null || $cedula === '' || $servicio === '' || $idResultado === null || $idGestion === null) {
  					echo json_encode(['response' => 'false']);
  					break;
  				}
  				$ejecucion = $conexion->eliminarVenta($motivo, $cedula, $servicio, $idResultado, $_SESSION['id'], $date, $idGestion);
  				if ($ejecucion) {
  					$json['response']	= 	'true';
  				}
  				else{
  					$json['response'] 	= 	'false';
  				}
  				echo json_encode($json);
  			break;
  #----------------------------------------------------------------------------------------------------------------------
  			case 'editinv':
  				$efectivo   = $conexion->contactoEfectivo(13);
  				$estado		= $conexion->estado('oc');
  				$producto  	= $conexion->ventaProducto($_SESSION['cod_serv']);
  				$seguro  	= $conexion->seguro();
  				include(PUBLIC_DIR.'general/header.php');
  				include(PUBLIC_DIR.'general/navbar.php');
  				include(HTML_DIR.'editar/inver.php');
  				include(PUBLIC_DIR.'general/footer.php');
  			break;
  #----------------------------------------------------------------------------------------------------------------------
  			case 'buscarInv':
  				$telefono = input_string(isset($_POST['telefono']) ? $_POST['telefono'] : '', 30);
  				$ejecucion = ($telefono === '') ? false : $conexion->busquedaResultadosInv($telefono);
  				$ciudad = $ejecucion ? $conexion->ciudad($ejecucion[0]['id_ciudad']) : false;
  				if ($ejecucion) {
  					foreach ($ejecucion as $b) {
  						if($b['status'] == 0){
  							$json['response'] 		= 	"true";
  							$json['id_resultado'] 	=	$b['id_resultado'];
  							$json['id_gestion']		=	$b['id_gestion'];
  							$json['nombre']			=	$b['nombre'];
  							$json['apellido']		=	$b['apellido'];
  							$json['edad']			=	$b['edad'];
  							$json['telf_hab'] 		= 	$b['telf_hab'];
  							$json['telf_cel'] 		= 	$b['telf_cel'];
  							$json['correo'] 		= 	$b['correo'];
  							$json['banco'] 			= 	$b['banco'];
  							$json['seguro']			=	$b['seguro'];
  							$json['cobertura'] 		= 	$b['cobertura'];
  							$json['trabaja']		=	$b['trabaja'];
  							$json['ciudadano'] 		= 	$b['ciudadano'];
  							$json['estado'] 		= 	$b['id_estado'];
  							$json['ciudad'] 		= 	$ciudad[0][0];
  							$json['zipcode'] 		= 	$b['zipcode'];
  							$json['fecha_cita'] 	= 	$b['fecha_cita'];
  							$json['hora_cita'] 		= 	$b['hora_cita'];
  						}
  						else{
  							$json['response'] 	= 	'eliminado';	
  						}
  					}
  				}
  				else{
  					$json['response'] 	= 	'false';
  				}
  				echo json_encode($json);
  			break;
  #----------------------------------------------------------------------------------------------------------------------
  			case 'actualiza2':
  				$tipo = input_enum(isset($_POST['tipo']) ? $_POST['tipo'] : null, [1, 2]);
  				if ($tipo === null) {
  					echo json_encode(['response' => 'false']);
  					break;
  				}
  				if($tipo == 1){
  					$contacto = input_int(isset($_POST['contacto']) ? $_POST['contacto'] : null, 1);
  					$resultado = input_int(isset($_POST['resultado']) ? $_POST['resultado'] : null, 1);
  					$gestion = input_int(isset($_POST['gestion']) ? $_POST['gestion'] : null, 1);
  					if ($contacto === null || $resultado === null || $gestion === null) {
  						echo json_encode(['response' => 'false']);
  						break;
  					}
  					$ejecucion = $conexion->updateResulta2($contacto, $resultado, $gestion);
  				}else{
  					$resultado = input_int(isset($_POST['resultado']) ? $_POST['resultado'] : null, 1);
  					$nombre = input_letters(isset($_POST['nombre']) ? $_POST['nombre'] : '', 100);
  					$apellido = input_letters(isset($_POST['apellido']) ? $_POST['apellido'] : '', 100);
  					$edad = input_int(isset($_POST['edad']) ? $_POST['edad'] : null, 0);
  					$telfHab = input_phone(isset($_POST['telf_hab']) ? $_POST['telf_hab'] : '');
  					$telfCel = input_phone(isset($_POST['telf_cel']) ? $_POST['telf_cel'] : '');
  					$correo = input_email(isset($_POST['correo']) ? $_POST['correo'] : '', 150);
  					$cobertura = input_string(isset($_POST['cobertura']) ? $_POST['cobertura'] : '', 100);
  					$compania = input_string(isset($_POST['compania']) ? $_POST['compania'] : '', 100);
  					$trabaja = input_string(isset($_POST['trabaja']) ? $_POST['trabaja'] : '', 100);
  					$banco = input_string(isset($_POST['banco']) ? $_POST['banco'] : '', 100);
  					$ciudadano = input_string(isset($_POST['ciudadano']) ? $_POST['ciudadano'] : '', 100);
  					$estado = input_int(isset($_POST['estado']) ? $_POST['estado'] : null, 1);
  					$ciudad = input_int(isset($_POST['ciudad']) ? $_POST['ciudad'] : null, 1);
  					$zipcode = input_string(isset($_POST['zipcode']) ? $_POST['zipcode'] : '', 20);
  					$fechaCita = input_string(isset($_POST['fecha_cita']) ? $_POST['fecha_cita'] : '', 20);
  					$horaCita = input_string(isset($_POST['hora_cita']) ? $_POST['hora_cita'] : '', 20);
  					if ($resultado === null || $nombre === '' || $apellido === '' || $edad === null || $correo === null || $estado === null || $ciudad === null) {
  						echo json_encode(['response' => 'false']);
  						break;
  					}
  					$ejecucion = $conexion->updateResulta3($resultado, $nombre, $apellido, $edad, $telfHab, $telfCel, $correo, $cobertura, $compania, $trabaja, $banco, $ciudadano, $estado, $ciudad, $zipcode, $fechaCita, $horaCita);
  				}
  				if ($ejecucion) {
  					$json['response']	= 	'true';
  				}else{
  					$json['response'] 	= 	'false';
  				}
  				echo json_encode($json);
  			break;


  			case 'ciudad_':
  				$id_estado = input_int(isset($_POST['id_estado']) ? $_POST['id_estado'] : null, 1);
  				$ciudad = ($id_estado === null) ? false : $conexion->ciudad_($id_estado);
  				$json['ciudad'] ="";
  				if ($ciudad) {
  					foreach ($ciudad as $c) {
  						$json['response'] 	= 	'true';
  						$json['ciudad'] = $json['ciudad'] . $c['id_ciudad'] . "," . $c['ciudad'] . "|";
  					}
  				}
  				else{
  					$json['response'] 	= 	'false';
  				}
  				echo json_encode($json);
  			break;
  		}
		} catch (Throwable $e) {
		  app_log('error', 'Error en el controlador editar: ' . $e->getMessage(), array(
		    'excepcion' => get_class($e),
		    'archivo' => $e->getFile(),
		    'linea' => $e->getLine(),
		    'modo' => isset($_GET['mode']) ? $_GET['mode'] : '',
		  ));

		  app_responder_error();
		}
	}
}
?>