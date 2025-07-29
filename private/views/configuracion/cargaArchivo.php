<script type="text/javascript">
//_____Para bloquear tecla F5_____
function checkKeyCode(evt){
	var evt = (evt) ? evt : ((event) ? event : null);
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	if(event.keyCode==116){
		evt.keyCode=0;
		return false
	}
}

document.onkeydown=checkKeyCode; 

function valida_extension(archivo){
	extensiones = archivo.substring(archivo.lastIndexOf("."));
	if( !extensiones.match(/\.(csv)$/) ){
		$("#botonS").hide();
		$("#error2").show();	
	}else{
		$("#error2").hide();
		$("#botonS").show();
	}
}

function validacion(){	
	var archivo=document.getElementById('archivo').value;
	if (archivo==0) {
		$("#error2").hide();
		$("#error1").show();
	}else if (valida_extension(archivo)==true) {
		return false;
	}else{
		return true;
	}

	formulario.submit();
}
</script>

<div class="container">
<?php
if (isset($_GET['estatus'])==200) {
  echo '  <script type="text/javascript">alert("REGISTRO EXITOSO");</script>';
}
?>
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-10 col-lg-12">
	    	<section class="container">
	        	<header><h1></h1></header>
	      	</section>
	      	<div class="panel panel-default">
	        	<div class="panel-body">
	          		<div class="form-group">
			            <section>
			              	<label><h1>Carga de base de datos</h1></label>
			            </section>
						<div class="alert alert-info alert-dismissable" style="text-align: center; ">
							<span class="">Recuerda que se debe guardar el archivo con extensión .csv</span>
							<center><a href="?view=configuracion&mode=download">Descarga de archivo modelo</a></center>
						</div>
	          			<div class="row">
						</div>
						<br>
						<form id="formulario" onsubmit="return validacion();" action="?view=configuracion&mode=registro" method="POST" class="form-horizontal" enctype="multipart/form-data">
							<center>
							<div class="form-row">
								<div class="col-md-offset-4 col-lg-offset-4 col-md-4 col-lg-4"> 
									<div class="form-row">
											<div class="md-form">
								                    <div class="row">
									                    <select class="form-control" name="servicio" id="servicio" required>
										                      <?php foreach ($servicio as $s) { ?>
									    	                  <option value='0' disabled selected style='display:none;'>Seleccione...</option>
									        	              <option value='<?php echo $s['id'];?>'><?php echo $s['descripcion'];}?></option>
									                    </select>
								                    </div>
													<br>
							                    <div class="btn btn-success btn-lg">
							                        <span><h5>Adjunte el archivo</h5></span>
							                        <h5><input id="archivo" type="file" name="archivo" required onchange="valida_extension(this.value);"></h5>
							                    </div>
							                    <center><h5 class="text-danger" id="error1" style="display:none;">No se ha seleccionado ningún archivo</h5></center>
							                    <center><h5 class="text-danger" id="error2" style="display:none;">Extension Incorrecta</h5></center>
							                </div>
									</div>
									<br>
									<div class="form-row">
										<center><button  id="botonS" class="btn btn-success">Subir</button></center>
									</div>
								</div>
							</center>

							</div>
						</form>		
	          		</div>
	        	</div>
	      	</div>
	    </div>
	</div>
</div>

</body>
</html>


