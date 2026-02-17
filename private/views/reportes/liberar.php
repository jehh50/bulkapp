<script type="text/javascript">
$(document).ready(function() {

  $("#btn-buscar").click(function(){

    if($("#cliente").val().trim() == ""){
      alert("Introduzca un número de cédula válido e intente de nuevo.");
      return;
    }

    $.ajax({
      type:'POST',
      url:'?view=reportes&mode=cliente',
      dataType: "json",
      data:{cliente: $("#cliente").val()},
      success:function(datos){

        if(datos.response == 'true'){

          if(datos.status == "Abierto"){
            alert("Este cliente aún no ha sido contactado.");
            return;
          }

          if(datos.status == "Eliminado"){
            alert("Este cliente fue eliminado por una venta errada. No se puede liberar.");
            return;
          }

          $("#datosCliente").html(
            'Nombre: <strong>' + datos.nombre + '</strong><br>' +
            'Cédula: <strong>' + datos.cedula + '</strong><br>' +
            'Telf_hab: <strong>' + datos.hab + '</strong><br>' +
            'Telf_ofi: <strong>' + datos.ofi + '</strong><br>' +
            'Telf_cel: <strong>' + datos.cel + '</strong><br>' +
            'Servicio: <strong class="text-primary">' + datos.servicio + '</strong><br>' +
            'Estatus: <strong class="text-danger">' + datos.status + '</strong>'
          ).slideDown();

          $("#datos").slideDown();
          $("#cedula").val(datos.cedula);

        } else {
          alert('La cédula no existe. Verifique e intente nuevamente.');
        }

      }
    });

  });


  $("#btn-liberar").click(function(){

    const cliente = $("#cedula").val();

    $.ajax({
      type:'POST',
      url:'?view=reportes&mode=disponible',
      dataType: "json",
      data:{cliente: cliente},
      success:function(datos){

        if(datos.response == 'true'){
          alert('Número liberado correctamente');
          window.location.href='?view=reportes&mode=liberar';
        } else {
          alert('Este cliente fue una venta. No se puede desbloquear.');
        }

      }
    });

  });

});
</script>


<style>
.login-wrapper {
  margin-top: 60px;
}

@media (max-width: 768px) {
  .login-wrapper {
    margin-top: 20px;
  }
}
</style>


<div class="container login-wrapper">
  <div class="row">
    <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">

      <div class="panel panel-default">
        <div class="panel-heading text-center">
          <strong>Desbloqueo de Clientes</strong>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label>Introduzca el número de cédula del cliente a liberar</label>
            <input type="text" class="form-control" name="cliente" id="cliente" maxlength="11" autofocus>
          </div>

          <div class="form-group">
            <button type="button" class="btn btn-success btn-block" id="btn-buscar">
              Buscar
            </button>
          </div>

          <div id="datosCliente" class="alert alert-info" style="display:none;"></div>

          <!-- input oculto REAL -->
          <input type="hidden" id="cedula">

          <div id="datos" style="display:none;">
            <button type="button" class="btn btn-info btn-block" id="btn-liberar">
              Liberar
            </button>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
