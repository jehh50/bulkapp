<script type="text/javascript">
	// Validar extensión
	function valida_extension(input) {
		var archivo = input.value;
		var extension = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();

		if (extension !== ".csv") {
			$("#botonS").prop("disabled", true);
			$("#error2").show();
		} else {
			$("#error2").hide();
			$("#botonS").prop("disabled", false);
		}
	}

	// Validación general
	function validacion() {
		var archivo = $("#archivo").val();

		if (!archivo) {
			$("#error1").show();
			return false;
		}

		$("#error1").hide();
		return true;
	}
</script>

<div class="container" style="margin-top:30px;">

	<div class="row">
		<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">

			<div class="panel panel-default">

				<!-- HEADER -->
				<div class="panel-heading text-left">
					<strong><h3 class="panel-title">Carga de base de datos</h3></strong>
				</div>

				<div class="panel-body">

					<!-- ALERTA INFO -->
					<div class="alert alert-info text-center">
						Recuerda que el archivo debe tener extensión <strong>.csv</strong>
					</div>

					<?php
					$mensajeHtml = '';
					$tipo_alerta = '';
					
					if (isset($_GET['mensaje'])) {
						switch ($_GET['mensaje']) {
							case 'exito':
								$mensajeHtml = '<strong>¡Éxito!</strong> El archivo se ha procesado correctamente.';
								$tipo_alerta = 'success';
								break;
							case 'errorFormato':
								$mensajeHtml = '<strong>Error de Formato:</strong> El delimitador del archivo es incorrecto. Por favor, verifique que sea un archivo CSV válido.';
								$tipo_alerta = 'danger';
								break;
						}
						echo '<div class="alert alert-'.$tipo_alerta.' alert-dismissable" style="text-align:center;" role="alert">'.$mensajeHtml.'</div>';
					}
					
					
					?>

					<!-- FORM -->
					<form id="formulario"
						onsubmit="return validacion();"
						action="?view=carga_archivo&mode=registro"
						method="POST"
						enctype="multipart/form-data">

						<!-- SELECT -->
						<div class="form-group">
							<label>Servicio</label>
							<select class="form-control" name="servicio" required>
								<option value="" disabled selected>Seleccione...</option>
								<?php foreach ($servicio as $s) { ?>
									<option value="<?php echo $s['id']; ?>">
										<?php echo $s['descripcion']; ?>
									</option>
								<?php } ?>
							</select>
						</div>

						<!-- FILE INPUT -->
						<div class="form-group">
							<label>Adjunte el archivo</label>
							<input id="archivo"
								type="file"
								name="archivo"
								class="form-control"
								required
								onchange="valida_extension(this);">
						</div>

						<!-- ERRORES -->
						<p class="text-danger text-center" id="error1" style="display:none;">
							No se ha seleccionado ningún archivo
						</p>

						<p class="text-danger text-center" id="error2" style="display:none;">
							Extensión incorrecta. Solo se permite .csv
						</p>

						<!-- BOTÓN -->
						<div class="text-center">
							<button id="botonS" class="btn btn-success">
								<span class="glyphicon glyphicon-upload"></span> Subir archivo
							</button>
						</div>

					</form>

				</div>
			</div>

		</div>
	</div>
</div>