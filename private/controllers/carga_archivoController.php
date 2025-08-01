<?php
include(PUBLIC_DIR.'general/session.php');
if(empty($_SESSION)){header('location:index.php');}else{

	include_once(MODEL_DIR.'carga_archivoModel.php');
	$conexion = new database();
	if (isset($_GET['mode'])) {

	switch ($_GET['mode']) {
		case 'index':
			include(PUBLIC_DIR.'general/header.php');
			include(PUBLIC_DIR.'general/navbar.php');
			$servicio = $conexion->servicio();
			include(HTML_DIR.'carga_archivo/index.php');
			include(PUBLIC_DIR.'general/footer.php');
		break;
	
		case 'registro':
			$cod_servicio 		=	$_POST['servicio']; 
			$archivo 			=	$_FILES["archivo"]['name'];
			$resultado 			= 	str_replace(" ", "", $archivo);
			$array				= 	explode (".",$resultado);
			$nombre_aleatorio	= 	$array[0].".".$array[1];	
			$nombrearchivo 		= 	"public/carga_bbdd/".$nombre_aleatorio;	
			$subida				=	move_uploaded_file($_FILES["archivo"]["tmp_name"],$nombrearchivo);

			if(($fp = fopen("public/carga_bbdd/".$nombre_aleatorio, "r"))!== false){									
				$nombre_campo=fgetcsv($fp,0,";");
				while (($datos=fgetcsv($fp,0,";"))!==false){	
					for ($i=0; $i <count($datos) ; $i++) { 
						if (empty($datos[$i])) {
							$datos[$i]=0;
						}
					}
					
					if(isset($datos[0])){
						$tipo_persona 		=  $datos[0];
						$tipo_documento 	=  $datos[1];
						$identificacion 	=  $datos[2];
						$nombre_legal  		=  $datos[3];
						$telf_hab  			=  $datos[4];
						$telf_ofi  			=  $datos[5];
						$telf_cel 			=  $datos[6];
						$correo  			=  $datos[7];
						$direccion  		=  $datos[8];
						$cuenta  			=  $datos[9];
						$fecha				=  date('Y-m-d');
						$registro = $conexion->registro($tipo_persona, $tipo_documento, $identificacion, $nombre_legal, $telf_hab, $telf_ofi, $telf_cel,$correo,$direccion,$cuenta,$fecha,$cod_servicio);
					}																
				}
				fclose($fp);
			}
			header('location:?view=carga_archivo&mode=index&mensaje=exito');
		break;

		default:
			header('location:'.HTML_DIR.'error.html');
		break;
		}	
	}
}
?>