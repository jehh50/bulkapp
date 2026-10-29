<?php
include(PUBLIC_DIR.'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));
if (!security_usuario_autenticado()) { header('location:index.php'); exit; } else {

	include_once(MODEL_DIR.'csvModel.php');
	$conexion = new database();
	if (isset($_GET['mode'])) {
		try {
  		switch ($_GET['mode']) {
  			case 'descargar':
  				$fechaD = input_date(isset($_POST['fecha_d']) ? $_POST['fecha_d'] : null);
  				$fechaH = input_date(isset($_POST['fecha_h']) ? $_POST['fecha_h'] : null);
  				if ($fechaD === null || $fechaH === null) {
  					header('location:' . HTML_DIR . 'error.html');
  					exit;
  				}
  				$desde = str_replace('-', '', $fechaD);
  				$hasta = str_replace('-', '', $fechaH);

  			    header("Content-type: application/vnd.ms-excel");
  				header("Content-Disposition: attachment; filename=emisiones_NOTIFIACION.xls");
  				$gestion = $conexion->gestionTotal($desde,$hasta);
  					echo '
  						<div class="row">
  					    <div class="col-md-12">
  			            <table class="table table-responsive table-hover table-condensed">
  		                    <thead>
  		                        <tr>
  		                          <th style="text-align: center;"><strong>CODPROD</strong></th>
  		                          <th style="text-align: center;"><strong>TIPOVIG</strong></th>
  		                          <th style="text-align: center;"><strong>FECINIVIG</strong></th>
  		                          <th style="text-align: center;"><strong>FECEMI</strong></th>
  		                          <th style="text-align: center;"><strong>CODOFISUSC</strong></th>
  		                          <th style="text-align: center;"><strong>CODOFIEMI</strong></th>
  		                          <th style="text-align: center;"><strong>TIPOSUSC</strong></th>
  		                          <th style="text-align: center;"><strong>CODFORMPAGO</strong></th>
  		                          <th style="text-align: center;"><strong>CODMONEDA</strong></th>
  		                          <th style="text-align: center;"><strong>CODPLAN</strong></th>
  		                          <th style="text-align: center;"><strong>REVPLAN</strong></th>
  		                          <th style="text-align: center;"><strong>TIPOID</strong></th>
  		                          <th style="text-align: center;"><strong>NUMID</strong></th>
  		                          <th style="text-align: center;"><strong>INDNACIONAL</strong></th>
  		                          <th style="text-align: center;"><strong>NOMTER</strong></th>
  		                          <th style="text-align: center;"><strong>APETER</strong></th>
  		                          <th style="text-align: center;"><strong>CODPAISORIG</strong></th>
  		                          <th style="text-align: center;"><strong>TIPHAB</strong></th>
  		                          <th style="text-align: center;"><strong>DESCHAB</strong></th>
  		                          <th style="text-align: center;"><strong>TIPVIA</strong></th>
  		                          <th style="text-align: center;"><strong>DESCVIA</strong></th>
  		                          <th style="text-align: center;"><strong>TIPZONA</strong></th>
  		                          <th style="text-align: center;"><strong>DESCZONA</strong></th>
  		                          <th style="text-align: center;"><strong>CODPAIS</strong></th>
  		                          <th style="text-align: center;"><strong>CODESTADO</strong></th>
  		                          <th style="text-align: center;"><strong>CODCIUDAD</strong></th>
  		                          <th style="text-align: center;"><strong>CODMUNICIPIO</strong></th>
  		                          <th style="text-align: center;"><strong>CODPARROQUIA</strong></th>
  		                          <th style="text-align: center;"><strong>CODSECTOR</strong></th>
  		                          <th style="text-align: center;"><strong>DIRECCION</strong></th>
  		                          <th style="text-align: center;"><strong>EMAIL</strong></th>
  		                          <th style="text-align: center;"><strong>CODTELOFI</strong></th>
  		                          <th style="text-align: center;"><strong>TELOFI</strong></th>
  		                          <th style="text-align: center;"><strong>CODTELHAB</strong></th>
  		                          <th style="text-align: center;"><strong>TELHAB</strong></th>
  		                          <th style="text-align: center;"><strong>CODTELCEL</strong></th>
  		                          <th style="text-align: center;"><strong>TELCEL</strong></th>
  		                          <th style="text-align: center;"><strong>FECNAC</strong></th>
  		                          <th style="text-align: center;"><strong>SEXO</strong></th>
  		                          <th style="text-align: center;"><strong>CUENTA</strong></th>
  		                        </tr>
  		                    </thead>
  			                <tbody>';
  			        if (!empty($gestion)){
  				array_walk_recursive($gestion, function (&$valor) {
  					if (is_string($valor)) {
  						$valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
  					}
  				});
  			foreach($gestion as $gestionados){
  				$codhab = substr($gestionados['telf_hab'],1,4);
  				$codofi = substr($gestionados['telf_ofi'],1,4);
  							$codcel = substr($gestionados['telf_celular'],1,4);
  							$hab = str_replace("/","",substr($gestionados['telf_hab'],6,10));
  				$ofi = str_replace("/","",substr($gestionados['telf_ofi'],6,10));
  							$cel = str_replace("/","",substr($gestionados['telf_celular'],6,10));
  				$cuenta = $gestionados['cuenta'];
  				echo ' <tr align="center">
  					   <td>'.$gestionados['codigo_producto'].'</td>
  					   <td>A</td>
  					   <td>'.$gestionados['fecha_venta'].'</td>
  					   <td>'.$gestionados['fecha_venta'].'</td>
  					   <td>020601</td>
  					   <td>020601</td>
  					   <td>I</td>
  					   <td>M</td>
  					   <td>DL</td>
  					   <td>'.$gestionados['codplan'].'</td>
  					   <td>001</td>
  					   <td>V</td>
  					   <td>'.$gestionados['cedula'].'</td>
  					   <td>N</td>
  					   <td>'.$gestionados['nombre'].'</td>
  					   <td>'.$gestionados['apellido'].'</td>
  					   <td>001</td>
  					   <td>'.$gestionados['id_edificacion'].'</td>
  					   <td>'.$gestionados['edificacion'].'</td>
  					   <td></td>
  					   <td></td>
  					   <td></td>
  					   <td></td>
  					   <td>001</td>
  					   <td>'.$gestionados['cod_estado'].'</td>
  					   <td>'.$gestionados['cod_ciudad'].'</td>
  		                           <td>'.$gestionados['cod_municipio'].'</td>
  		                           <td></td>
  		                           <td></td>
  		                           <td>'.$gestionados['municipio'].' '.$gestionados['ciudad'].' '.$gestionados['estado'].' VENEZUELA</td>
  		                           <td>'.$gestionados['correo'].'</td>
  		                           <td>'.$codhab.'</td>
  		                           <td>'.$hab.'</td>
  		                           <td>'.$codofi.'</td>
  		                           <td>'.$ofi.'</td>
  		                           <td>'.$codcel.'</td>
  		                           <td>'.$cel.'</td>
  		                           <td>'.$gestionados['fecha_nacimiento'].'</td>
  		                           <td>'.$gestionados['genero'].'</td>
  		                           <td style="mso-number-format:"@"">'.$cuenta.'</td>
  		                        </tr>';
  		                }
  		            }
  			        echo '
  			                </tbody>
  			            </table>
  			            </div>
  			            </div>';
  			break;
  #-------------------------------------------------------------------------------------------
  			case 'descarga2':
  				$fechaD = input_date(isset($_POST['fecha_d']) ? $_POST['fecha_d'] : null);
  				$fechaH = input_date(isset($_POST['fecha_h']) ? $_POST['fecha_h'] : null);
  				if ($fechaD === null || $fechaH === null) {
  					header('location:' . HTML_DIR . 'error.html');
  					exit;
  				}
  				$desde = date('d/m/Y', strtotime($fechaD));
  				$hasta = date('d/m/Y', strtotime($fechaH));
				
  			    #header("Content-type: application/vnd.ms-excel");
  				#header("Content-Disposition: attachment; filename=gestionfinal.xls");
  				$gestion = $conexion->gestionTotal2($desde,$hasta);
  					echo '
  						<div class="row">
  					    <div class="col-md-12">
  			            <table class="table table-responsive table-hover table-condensed">
  		                    <thead>
  		                        <tr>
  		                          <th style="text-align: center;"><strong>NOMBRE</strong></th>
  		                          <th style="text-align: center;"><strong>APELLIDO</strong></th>
  		                          <th style="text-align: center;"><strong>TELF. HAB</strong></th>
  		                          <th style="text-align: center;"><strong>TELF. CEL</strong></th>
  		                          <th style="text-align: center;"><strong>CORREO</strong></th>
  		                          <th style="text-align: center;"><strong>PERSONAS QUE NECESITAN COBERTURA MEDICA</strong></th>
  		                          <th style="text-align: center;"><strong>CUENTA BANCARIA</strong></th>
  		                          <th style="text-align: center;"><strong>INGRESOS 2020</strong></th>
  		                          <th style="text-align: center;"><strong>SEGURO SOCIAL</strong></th>
  		                          <th style="text-align: center;"><strong>COBERTURA MEDICA</strong></th>
  		                          <th style="text-align: center;"><strong>CODIGO POSTAL</strong></th>
  		                          <th style="text-align: center;"><strong>FECHA DE LA CITA</strong></th>
  		                          <th style="text-align: center;"><strong>HORARIO DE LA VISITA</strong></th>
  		                        </tr>
  		                    </thead>
  			                <tbody>';
  			        //if (!empty($gestion)){
  			//foreach($gestion as $gestionados){
  				echo ' <tr align="center">
  					   <td>'. 1 .'</td>
  		                           </tr>';
  		                //}
  		            //}
  			        echo '
  			                </tbody>
  			            </table>
  			            </div>
  			            </div>';


  			break;
  #-------------------------------------------------------------------------------------------
  			default:
  				header('location:'.HTML_DIR.'error.html');
  			break;	
  		}
		} catch (Throwable $e) {
		  app_log('error', 'Error en el controlador csv: ' . $e->getMessage(), array(
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