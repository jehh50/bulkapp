<script src="<?php echo asset('public/js/edit.js'); ?>"></script>
<script type="text/javascript">
  function mayus(e) {
    e.value = e.value.toUpperCase();
  }
  function soloNumeros(e){
    key=e.keyCode || e.which;
    teclado=String.fromCharCode(key).toLowerCase();
    numero="0123456789";
    if(numero.indexOf(teclado)==-1){
      alert('Solo se permiten números.')
      return false;
    }
  }

  function soloLetras(e) {
    key=e.keyCode || e.which;
    teclado=String.fromCharCode(key).toLowerCase();
    letras="qwertyuiopasdfghjklñzxcvbnm";
    especiales="32";
    if(letras.indexOf(teclado)==-1 && especiales!=key){
        alert("Solo se permite letras.")
        return false;
    }
  }

  function validaTelf(e){
    valueForm=e.value
    var patron = /^[(]\d{4}[)]\d{3}.\d{2}.\d{2}$/;
    if (valueForm.search(patron)!=0){
      alert("El formato del número de telefono debe coincidir con (XXXX)XXX.XX.XX. Por favor verifique.");
    }
  }

  function validateMail(e){
    valueForm=e.value;
    var patron = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/)
    if(valueForm.search(patron)!=0){
      alert('La dirección de correo es invalida, el formato debe coincidir con DIRECCION@DOMINIO.COM intente de nuevo.');
      window.location.hash = "#correo";
    }
  }
</script>

<?php
if (isset($_GET['mensaje'])=='exito') {
  echo '  <script type="text/javascript">alert("REGISTRO EXITOSO"); $(location).attr("href","?view=editar&mode=index");</script>';
}
?>
<div>
  <div class="row">
    <div class="col-sm-10 col-md-10 offset-lg-1 col-lg-10">
      <section class="container">
        <header><h1></h1></header>
      </section>
      <div class="card" >
        <div class="card-body">
          <div class="mb-3">
            <section>
              <label><h1>Edición de resultados</h1></label>
            </section>
              <div class="mb-3 col-lg-12" id="servicio" hidden>
              </div>
              <input type="hidden" id="id_resultado">
              <input type="hidden" id="cod_servicio">
              <input type="hidden" id="gestion">

              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Cedula</span>
                <input type="text" class="form-control" placeholder="12658457" aria-describedby="cedula" id="cedula"/>
              </div>
              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Nombres</span>
                <input type="text" class="form-control" placeholder="Julio Cesar" aria-describedby="nombre" id="nombre" onkeyup="mayus(this);" onchange="soloLetras(this)" readonly/>
              </div>
              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Apellidos</span>
                <input type="text" class="form-control" placeholder="Perez Gomez" aria-describedby="apellido" id="apellido" onkeyup="mayus(this);" onchange="soloLetras(this)" readonly/>
              </div>
              
              <div class="mb-3 col-lg-6" id="d_genero">                
              </div>

              <div class="mb-3 col-lg-6" id="d_nacimiento">
              </div>
              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Teléfono habitación</span>
                <input type="text" class="form-control" placeholder="(0212)345.67.89" aria-describedby="tlf_hab" id="telf_hab" onchange="validaTelf(this);" readonly/>
              </div>
              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Teléfono oficina</span>
                <input type="text" class="form-control" placeholder="(0212)345.67.89"  aria-describedby="tlf_ofic" id="telf_ofi" onchange="validaTelf(this);" readonly/>
              </div>
              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Teléfono celular</span>
                <input type="text" class="form-control" placeholder="(0424)234.56.78" aria-describedby="tlf_celu" id="telf_cel" onchange="validaTelf(this);" readonly/>
              </div>
              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Correo</span>
                <input type="text" class="form-control" placeholder="usario@dominio.com" aria-describedby="correo" id="correo" onkeyup="mayus(this);" onchange="validateMail(this);" readonly/>
              </div>
              <div class="mb-3 col-lg-6">
                <span class="input-group-text">Cuenta</span>
                <input type="text" class="form-control"  placeholder="00000000000000001111" maxlength="20" aria-describedby="cuenta" id="cuenta" onchange="soloNumeros(this)" readonly/>
              </div>
              
              <div class="container-fluid">
                <button class="btn btn-md btn-primary" id="btn-buscar"><i class="bi bi-search"></i> Buscar</button>
                <button class="btn btn-md btn-success" id="btn-actualizar"><i class="bi bi-save"></i> Actualizar</button>
                <button class="btn btn-md btn-warning" id="btn-limpiar"><i class="bi bi-arrow-clockwise"></i> Limpiar</button>
                <button class="btn btn-md btn-danger" id="btn-eliminar" data-bs-toggle="modal" data-bs-target="#modalRechazo"><i class="bi bi-x"></i> Eliminar</button>
              </div>
            <!--/form-->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalRechazo" tabindex="-1" role="dialog" aria-labelledby="modalRechazo">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        <h4 class="modal-title" id="modalRechazo">Seleccione el motivo por el cual esta eliminando la venta</h4>
      </div>
      <div class="modal-body">     
        <select class="form-select" id="eliminar_venta" required>
          <option value="0" disabled selected style="display:none;">Seleccione...</option>
          <option value="error">Venta con error</option>
          <option value="forzada">Venta con mala gestión o forzadas</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Descartar</button>
        <button type="button" class="btn btn-primary" id="btn-guardar"><i class="bi bi-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal confirmación de venta eliminada-->
<div class="modal fade" id="modalConfirm" tabindex="-1" role="dialog" aria-labelledby="modalConfirm">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" align="center">
      <div class="modal-header">
        <h3>Rechazo de venta procesado con exito</h3>
      </div>
      <div class="modal-body">     
       <h4>Por favor espere mientras se actualiza la página</h4>
        <img src="public/images/refresh.gif" alt="refresh" height="50px" width="50px"/>
      </div>
    </div>
  </div>
</div>

<!-- Modal confirmación de actualización -->
<div class="modal fade" id="modalActualiza" tabindex="-1" role="dialog" aria-labelledby="modalConfirm">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" align="center">
      <div class="modal-header">
        <h3>Registro actualizado con exito</h3>
      </div>
      <div class="modal-body">     
        <h4>Por favor espere mientras se actualiza la página</h4>
        <img src="public/images/refresh.gif" alt="refresh" height="50px" width="50px"/>
      </div>
    </div>
  </div>
</div>