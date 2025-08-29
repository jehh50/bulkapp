<script type="text/javascript">
//_____Para bloquear tecla F5_____
function checkKeyCode(evt){
	var evt = (evt) ? evt : ((event) ? event : null);
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	if(event.keyCode==115){
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

<?php
if (isset($_GET['mensaje'])=='exito') {
  echo '  <script type="text/javascript">alert("Actualización procesada"); $(location).attr("href","?view=carga_archivo&mode=liberarCuotas");</script>';
}
?>

<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
        	<section class="container text-center">
            	<header><h1></h1></header>
          	</section>
          	<div class="panel panel-default">
            	<div class="panel-body text-center">
              		<div class="form-group">
                        <section>
                          	<label><h1>Liberación de cuotas Cashea</h1></label>
                        </section>
                        <div class="alert alert-info alert-dismissable" style="text-align: center;">
                            <span>Recuerda que se debe guardar el archivo con extensión .csv</span>
                        </div>
                        <br>
                        <form id="formulario" onsubmit="return validacion();" action="?view=carga_archivo&mode=freeQuotes" method="POST" class="form-horizontal" enctype="multipart/form-data">
                            <div class="form-row text-center">
                                <div class="col-xs-12">
                                    <div class="form-row">
                                        <div class="md-form">
                                            <div class="btn btn-success btn-lg">
                                                <span><h5>Adjunte el archivo</h5></span>
                                                <h5><input id="archivo" type="file" name="archivo" required onchange="valida_extension(this.value);"></h5>
                                            </div>
                                            <h5 class="text-danger" id="error1" style="display:none;">No se ha seleccionado ningún archivo</h5>
                                            <h5 class="text-danger" id="error2" style="display:none;">Extension Incorrecta</h5>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="form-row">
                                        <button id="botonS" class="btn btn-success">Cargar</button>
                                    </div>
                                </div>
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


