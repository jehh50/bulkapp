<?php
include(PUBLIC_DIR.'general/session.php');
if(empty($_SESSION)){header('location:index.php');}else{

include_once(MODEL_DIR.'editarModel.php');
$conexion = new database();

	if (isset($_GET['mode'])) {
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
				$ejecucion = $conexion->busquedaResultados($_POST['cedula']);
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
				$nombre 	= str_replace(',', ' ',$_POST['nombre']);
				$apellido 	= str_replace(',', ' ',$_POST['apellido']);
				$ejecucion 	= $conexion->updateResultados($_POST['id_resultado'], $nombre, $apellido, $_POST['cedula'], $_POST['telf_hab'], $_POST['telf_ofi'], $_POST['telf_cel'], $_POST['correo'], $_POST['cuenta'],$_POST['cod_servicio'],$_POST['genero'],$_POST['fecha_nac']);
				
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
				$ejecucion = $conexion->eliminarVenta($_POST['rechazo'], $_POST['cedula'], $_POST['servicio'], $_POST['id_resultado'],$_SESSION['id'],$date,$_POST['id_gestion']);
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
				$ejecucion 	= $conexion->busquedaResultadosInv($_POST['telefono']);
				$ciudad		= $conexion->ciudad($ejecucion[0]['id_ciudad']);
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
				if($_POST['tipo'] == 1){
					$ejecucion = $conexion->updateResulta2($_POST['contacto'],$_POST['resultado'], $_POST['gestion']);
				}else{
					$ejecucion = $conexion->updateResulta3( $_POST['resultado'],$_POST['nombre'],$_POST['apellido'],$_POST['edad'],$_POST['telf_hab'],$_POST['telf_cel'],$_POST['correo'],$_POST['cobertura'],$_POST['compania'],$_POST['trabaja'],$_POST['banco'],$_POST['ciudadano'],$_POST['estado'],$_POST['ciudad'],$_POST['zipcode'],$_POST['fecha_cita'],$_POST['hora_cita']); 
				}
				if ($ejecucion) {
					$json['response']	= 	'true';
				}else{
					$json['response'] 	= 	'false';
				}
				echo json_encode($json);
			break;


			case 'ciudad_':
				$id_estado = $_POST['id_estado'];
				$ciudad = $conexion->ciudad_($id_estado);
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
	}
}
?>