<script type="text/javascript">
$(document).ready(function() {
    $("#btn-buscar").click(function(){
      if($("#cliente").val() == ""){
        alert("Introduzca un número de cedula valido e intente de nuevo.");
      }else{
        $.ajax({
            type:'POST',
            url:'?view=reportes&mode=cliente',
            dataType: "json",
            data:{cliente: $("#cliente").val()},
            success:function(datos){
              if(datos.response == 'true'){
                if(datos.status == "Abierto"){
                  alert("Este cliente aún no ha sido contactado.");
                  return false;
                }else if(datos.status == "Eliminado"){
                  alert("Este cliente ha sido eliminado por una venta errada, no se puede liberar.");
                  return false;
                }else{
                  $("#datosCliente").show('fast');
                  $("#datosCliente").html('Nombre: <strong>' + datos.nombre + '</strong><br>Cedula: <strong>' + datos.cedula + '</strong><br>Telf_hab: <strong>' + datos.hab + '</strong><br>Telf_ofi: <strong>' + datos.ofi + '</strong><br>Telf_cel: <strong>' + datos.cel + '</strong><br>Servicio: <strong style="color: blue;">' + datos.servicio + '</strong><br>Estatus: <strong style="color:red;">' + datos.status + '</strong>');
                  $("#datos").show('fast');
                  $("#cedula").val(datos.cedula);
                }
              }
              else{
                alert('El número de cedula no existe, por favor valide e intente de nuevo.');
              }
            }
        })
      }
    });

    $("#btn-liberar").click(function(){
      cliente = $("#cedula").val();
      $.ajax({
          type:'POST',
          url:'?view=reportes&mode=disponible',
          dataType: "json",
          data:{cliente: cliente},
          success:function(datos){
            if(datos.response == 'true'){
              alert('Número liberado');
              $(location).attr('href','?view=reportes&mode=liberar');
            }
            else{
              alert('Este cliente fue una venta. No se puede desbloquear.');
            }
          }
        })
    });
})

</script>
<style>
.caja{margin-top: -150px;}
</style>
  <div class="loginbox">
   <div class="container">
    <div class="caja">
     <div class="col-md-offset-3 col-lg-6">
      <div class="login-panel panel panel-default">
       <div class="panel-heading">
        Desbloqueo de clientes
       </div>
       <div class="panel-body">
         <div class="form-group">
          <label>Introduzca el número de cedula del cliente a liberar</label>
           <div class="form-group">
              <input type="text" class="form-control" name="cliente" id="cliente" maxlength="11" autofocus />
           </div>
           <div class="form-group">
              <button type="button" class="btn btn-md btn-success btn-block" id="btn-buscar" name="btn-buscar">Buscar</button>
           </div>
           <div id="datosCliente" class="form-group" hidden>
           </div>
           <div id="cedula" class="form-group" hidden>
           </div>
           <div id="datos" class="form-group" hidden>
            <button class="btn btn-md btn-info btn-block" id="btn-liberar" name="btn-liberar">Liberar</button>
           </div>
         </div>
       </div>
      <div>
     </div>
    </div>
   </div>
  </div>

