<?php
include(PUBLIC_DIR.'general/session.php');
security_requerir_autenticacion();
if (!security_usuario_autenticado()) { header('location:index.php'); exit; } else {

include_once(MODEL_DIR.'usuariosModel.php');
$conexion = new database();
if (isset($_GET['mode'])) {
	$modoUsuarios = $_GET['mode'];
	if (in_array($modoUsuarios, ['index', 'new', 'registro', 'edituser', 'update', 'password', 'eliminar'], true)) {
		security_requerir_rol(array(Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));
	}

	try {
		switch ($modoUsuarios) {
		case 'index':
			$nav_crumb = 'Configuración / Usuarios';
			$nav_title = 'Bandeja de usuarios';
			$nav_subtitle = 'Gestiona los usuarios, roles y servicios de cada campaña.';
			include(PUBLIC_DIR.'general/nheader.php');
			include(PUBLIC_DIR.'general/nnavbar.php');
			$listUser = $conexion->listUser();
			$servicio = $conexion->servicio(); // para el alta inline (diseño unified)
			include(HTML_DIR.'usuarios/index.php');
			include(PUBLIC_DIR.'general/nfooter.php');
			break;
	#----------------------------------------------------------
		case 'new':
			include(PUBLIC_DIR.'general/header.php');
			include(PUBLIC_DIR.'general/navbar.php');
			$servicio = $conexion->servicio();
			include(HTML_DIR.'usuarios/newuser.php');
			include(PUBLIC_DIR.'general/footer.php');
			break;
	#----------------------------------------------------------	
		case 'registro':
			$nombre 	= input_letters(isset($_POST['nombre']) ? $_POST['nombre'] : '', 50);
			$apellido 	= input_letters(isset($_POST['apellido']) ? $_POST['apellido'] : '', 50);
			$user 		= input_string(isset($_POST['user']) ? $_POST['user'] : '', 50);
			$user 		= preg_replace('/[^A-Za-z0-9._-]/', '', $user);
			$passPlain 	= isset($_POST['pass']) && is_string($_POST['pass']) ? $_POST['pass'] : '';
			$tipo 		= input_enum(isset($_POST['tipo']) ? $_POST['tipo'] : null, Constantes::tiposUsuario());
			$cliente 	= input_int(isset($_POST['cliente']) ? $_POST['cliente'] : null, 1);

			if ($nombre === '' || $apellido === '' || $user === '' || mb_strlen($passPlain) < 8 || $tipo === null || $cliente === null) {
				$json['response'] = 'false';
				$json['message'] = 'Datos inválidos. La contraseña debe tener al menos 8 caracteres.';
				echo json_encode($json);
				break;
			}

			if ($conexion->existsUser($user)) {
				$json['response'] = 'false';
				$json['message'] = 'El usuario ya existe.';
				echo json_encode($json);
				break;
			}

			$pass = security_hash_password($passPlain);
			$registro = $conexion->newUser($nombre,$apellido,$user,$pass,$tipo,$cliente);
			if ($registro){
				$json['response'] = 'true';
				echo json_encode($json);
			}else{
				$json['response'] = 'false';
				$json['message'] = 'El usuario ya existe.';
				echo json_encode($json);
			}
			break;
	#----------------------------------------------------------	
		case 'edituser':
			$id_user = input_int(isset($_GET['id']) ? $_GET['id'] : null, 1);
			if ($id_user === null) {
				header('location:'.HTML_DIR.'error.html');
				exit;
			}
			$registro = $conexion->editUser($id_user);
			foreach ($registro as $u) {
				$user 		= $u['user'];
				$nombre 	= $u['nombre'] ;
				$apellido 	= $u['apellido'];
				$rol 		= $u['name'];
				$status 	= strtoupper($u['status']);
				$cliente	= $u['descripcion'];
				$id_user 	= $u['id'];
				$codserv    = $u['id_servicio'];
				$status_id  = $u['status_id'];
			}
			if($rol == 2){$e='selected';$f="";$g="";}elseif($rol == 3){$f='selected';$e="";$g="";}else{$g='selected';$f="";$e="";}
			
			include(PUBLIC_DIR.'general/header.php');
			include(PUBLIC_DIR.'general/navbar.php');
			$servicio = $conexion->servicio();
			include(HTML_DIR.'usuarios/edituser.php');
			include(PUBLIC_DIR.'general/footer.php');
			break;
	#------------------------------------------------------------------------------------------------------------------------------	
			case 'update':
				$id 		= input_int(isset($_POST['userid']) ? $_POST['userid'] : null, 1);
				$nombre 	= input_letters(isset($_POST['nombre']) ? $_POST['nombre'] : '', 50);
				$apellido 	= input_letters(isset($_POST['apellido']) ? $_POST['apellido'] : '', 50);
				$rol 		= input_enum(isset($_POST['rol']) ? $_POST['rol'] : null, [1, 2, 3]);
				$status 	= input_int(isset($_POST['status']) ? $_POST['status'] : null, 1);
				$servicio 	= input_int(isset($_POST['servicio']) ? $_POST['servicio'] : null, 1);

				// Guard defensivo: el nombre de usuario no es editable
				if (isset($_POST['user']) && trim((string)$_POST['user']) !== '') {
					$json['response'] = 'false';
					$json['message'] = 'El nombre de usuario no puede modificarse.';
					echo json_encode($json);
					break;
				}

				if ($id === null || $nombre === '' || $apellido === '' || $rol === null || $status === null || $servicio === null) {
					$json['response'] = 'false';
					$json['message'] = 'Datos inválidos.';
					echo json_encode($json);
					break;
				}

				$update = $conexion->updateUser($id,$nombre,$apellido,$rol,$status,$servicio);
			
			if ($update){
				$json['response'] = 'true';
				echo json_encode($json);
			}else{
				$json['response'] = 'false';
				echo json_encode($json);
			}
			break;
	#-------------------------------------------------------------------------------------------------------------------------------
		case 'password':
			$id = input_int(isset($_POST['userid']) ? $_POST['userid'] : null, 1);
			if ($id === null) {
				$json['response'] = 'false';
				$json['message'] = 'Usuario inválido.';
				echo json_encode($json);
				break;
			}

			$passwordTemporal = security_random_password(14);
			$password = $conexion->updatePass($id, security_hash_password($passwordTemporal));
			if ($password){
				$json['response'] = 'true';
				$json['password'] = $passwordTemporal;
				echo json_encode($json);
			}else{
				$json['response'] = 'false';
				echo json_encode($json);
			}
			break;
		#-------------------------------------------------------------------------------------------------------------------------------
		case 'changepass':
			$nav_crumb = 'Usuarios / Seguridad';
			$nav_title = 'Cambio de contraseña';
			$nav_subtitle = 'Actualiza tu clave de acceso a Bulksales.';
			include(PUBLIC_DIR.'general/nheader.php');
			include(PUBLIC_DIR.'general/nnavbar.php');
			include(HTML_DIR.'usuarios/changepass.php');
			include(PUBLIC_DIR.'general/nfooter.php');
			break;
		#-------------------------------------------------------------------------------------------------------------------------------
		case 'actualizaPassword':
			$id = input_int(isset($_SESSION['id']) ? $_SESSION['id'] : null, 1);
			$passwordNueva = isset($_POST['password_']) && is_string($_POST['password_']) ? $_POST['password_'] : '';
			$passwordConfirma = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

			if ($id === null || mb_strlen($passwordNueva) < 8 || $passwordNueva !== $passwordConfirma || $passwordNueva === '123456') {
				header('location:?view=usuarios&mode=changepass&mensaje=error');
				exit;
			}

			$conexion->updatePass($id, security_hash_password($passwordNueva));
			header('location:?view=session&mode=disconect');
			exit;
			break;
		#-------------------------------------------------------------------------------------------------------------------------------
		case 'eliminar':
			// Borrado lógico: marca el usuario como INACTIVO (los gestiones/ventas conservan su referencia)
			$userId = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
			if ($userId === null) {
				echo json_encode(['response' => 'false', 'message' => 'Usuario inválido.']);
				break;
			}
			$result = $conexion->deleteUser($userId);
			if ($result) {
				echo json_encode(['response' => 'true']);
			} else {
				echo json_encode(['response' => 'false', 'message' => 'No se pudo eliminar el usuario.']);
			}
			break;
		#-------------------------------------------------------------------------------------------------------------------------------
			default:
				header('location:'.HTML_DIR.'error.html');
			break;
			}
	} catch (Throwable $e) {
		app_log('error', 'Error en el controlador de usuarios: ' . $e->getMessage(), array(
			'excepcion' => get_class($e),
			'archivo' => $e->getFile(),
			'linea' => $e->getLine(),
			'modo' => isset($modoUsuarios) ? $modoUsuarios : '',
		));

		app_responder_error();
	}
}
}
?>
