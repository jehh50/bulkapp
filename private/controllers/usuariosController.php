<?php
include(PUBLIC_DIR.'general/session.php');
if(empty($_SESSION)){header('location:index.php');}else{

include_once(MODEL_DIR.'usuariosModel.php');
$conexion = new database();
if (isset($_GET['mode'])) {
	switch ($_GET['mode']) {
		case 'index':
			$listUser = $conexion->listUser();
			include(PUBLIC_DIR.'general/header.php');
			include(PUBLIC_DIR.'general/navbar.php');
			include(HTML_DIR.'usuarios/index.php');
			include(PUBLIC_DIR.'general/footer.php');
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
			$nombre 	= $_POST['nombre'];
			$apellido 	= $_POST['apellido'];
			$user 		= $_POST['user'];
			$pass 		= md5($_POST['pass']);
			$tipo 		= $_POST['tipo'];
			$cliente 	= $_POST['cliente'];
			$registro = $conexion->newUser($nombre,$apellido,$user,$pass,$tipo,$cliente);
			if ($registro){
				$json['response'] = 'true';
				echo json_encode($json);
			}else{
				$json['response'] = 'false';
				echo json_encode($json);
			}
		break;
#----------------------------------------------------------	
		case 'edituser':
			$id_user = $_GET['id'];
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
			$id 		= $_POST['userid'];
			$nombre 	= $_POST['nombre'];
			$apellido 	= $_POST['apellido'];
			$rol 		= $_POST['rol'];
			$status 	= $_POST['status'];
			$servicio 	= $_POST['servicio'];
			
			$update 	= $conexion->updateUser($id,$nombre,$apellido,$rol,$status,$servicio);
			
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
			$id = $_POST['userid'];
			$newpass = md5('123456');
			$password = $conexion->updatePass($id,$newpass);
			if ($password){
				$json['response'] = 'true';
				echo json_encode($json);
			}else{
				$json['response'] = 'false';
				echo json_encode($json);
			}
		break;
	#-------------------------------------------------------------------------------------------------------------------------------
		case 'changepass':
			include(PUBLIC_DIR.'general/header.php');
			include(PUBLIC_DIR.'general/navbar.php');
			include(HTML_DIR.'usuarios/changepass.php');
			include(PUBLIC_DIR.'general/footer.php');
		break;
	#-------------------------------------------------------------------------------------------------------------------------------
		case 'actualizaPassword':
			$p_md5 	= md5($_POST['password_']);
			$id 	= $_POST['user'];
			$update = $conexion->updatePass($id,$p_md5);

			header('location:?view=session&mode=disconect');
		break;
	#-------------------------------------------------------------------------------------------------------------------------------
		default:
				header('location:'.HTML_DIR.'error.html');
		break;
		}
	}
}
?>