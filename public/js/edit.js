$(document).ready(function(){
  $('#btn-eliminar').hide();
  $('#btn-actualizar').hide();
  $('#servicio').hide();
  $('#d_genero').hide();
  $('#d_producto').hide();


  document.querySelectorAll('.telefono').forEach(function (element) {
    element.addEventListener('input', function (event) {
      let input = event.target.value.replace(/\D/g, ''); // Remueve todos los caracteres no numéricos
      let formattedInput = '(0'; // Asegura el '0' en la primera posición

      // Controla la longitud del input para evitar concatenaciones incorrectas
      if (input.length > 1) {
        formattedInput += input.substring(1, 4); // Extrae los primeros 3 dígitos reales
      }
      if (input.length >= 5) {
        formattedInput += ')' + input.substring(4, 7); // Extrae los siguientes 3 dígitos
      }
      if (input.length >= 8) {
        formattedInput += '.' + input.substring(7, 9); // Extrae los siguientes 2 dígitos
      }
      if (input.length >= 10) {
        formattedInput += '.' + input.substring(9, 11); // Extrae los últimos 2 dígitos
      }

      event.target.value = formattedInput;
    });
  });
// FUNCIÓN QUE BUSCA LOS DATOS DE LA VENTA

  $('#btn-buscar').click(function(){
    if ($('#cedula').val() == "") {
      alert('Ingrese un número de cedula valido.');
    }
    else{
       a = $('#telf_hab').val(); 
       b = $('#telf_ofi').val(); 
       c = $('#telf_cel').val(); 
       d = $('#correo').val(); 
       e = $('#cuenta').val(); 
       f = $('#servicio').val(); 
       g = $('#cedula').val(); 
       h = '<h3 style="color: #ffffff; padding: 8px; background-color: #4cb957 ;height: 40px;border-radius: 5px;">SERVICIO: <strong>';
       i = '<section><span class="form-group-addon">Sexo</span></section><select class="form-control" id="genero">';
       p = '<span class="form-group-addon">Tipo de producto</span><select class="form-control" name="producto" id="producto">';

       $.ajax({
            type:'POST',
            url:'?view=configuracion&mode=buscar',
            dataType: "json",
            data:{telf_hab:a, telf_ofi:b, telf_cel:c, correo:d, cuenta:e, servicio:f, cedula:g },
            success:function(datos){
              if(datos.response == 'true'){
                $('#btn-buscar').hide();
                $('#btn-actualizar').show();
                $('#btn-eliminar').show();
                $('#servicio').show().html(h + datos.servicio + '</strong></h3>');
                if(datos.genero == 'F'){$('#d_genero').show().html(i + '<option selected value=F selected>FEMENINO</option><option value=M>MASCULINO</option></select>');}
                else{$('#d_genero').show().html(i + '<option selected value=F>FEMENINO</option><option value=M selected>MASCULINO</option></select>');}
                $('#d_nacimiento').removeAttr('readonly').val(datos.nacimiento);
                $('#id_resultado').val(datos.id_resultado);
                $('#cod_servicio').val(datos.cod_servicio);
                $('#telf_hab').removeAttr('readonly').val(datos.telf_hab);
                $('#telf_cel').removeAttr('readonly').val(datos.telf_celular); 
                $('#correo').removeAttr('readonly').val(datos.correo); 
                $('#cuenta').removeAttr('readonly').val(datos.cuenta); 
                $('#cedula').val(datos.cedula);
                $('#nombre').removeAttr('readonly').val(datos.nombre);
                $('#apellido').removeAttr('readonly').val(datos.apellido);
                $('#gestion').val(datos.id_gestion);
                $('#saleDate').removeAttr('readonly').val(datos.fecha_venta);
                $('#d_producto').show();
              }
              else if(datos.response == 'eliminado'){
                alert('Esta venta ya se encuentra rechazada');
              }
              else{
                alert('Esta venta no existe.');
              }
            }
      });
     }
    });

// FIN FUNCION BUSCAR
// FUNCION QUE ELIMINA LA VENTA

    $('#btn-guardar').click(function(){
      if($('select#eliminar_venta').val() == null){
        alert('Debe elegir un motivo de rechazo de venta');
      }
      else{
        console.log($('#cod_servicio').val());
        $.ajax({
              type:'POST',
              url:'?view=configuracion&mode=eliminar',
              dataType: "json",
              data:{rechazo: $('select#eliminar_venta').val(), cedula: $('#cedula').val(), servicio: $('#cod_servicio').val(), id_resultado: $('#id_resultado').val(), id_gestion: $('#gestion').val()},
              success:function(datos){
                if(datos.response == 'true'){
                  $('#modalRechazo').modal('hide');
                  $('#modalConfirm').modal('toggle');
                  setTimeout(function(){ $('#modalConfirm').modal('show') }, 1000);
                  setTimeout(function(){$(location).attr('href','?view=configuracion&mode=editarResultado')}, 2000);
                }
                else{
                  alert('NO ENTRO');
                }
              }
        });
      }
    })

// FIN FUNCION ELIMINA
// FUNCION QUE ACTUALIZA LA INFORMACIÓN

  $('#btn-actualizar').click(function(){
    $.ajax({
            type:'POST',
            url:'?view=configuracion&mode=actualiza',
            dataType: "json",
            data:{fecha_venta:$('#saleDate').val(),cedula:$('#cedula').val(), nombre:$('#nombre').val(), apellido:$('#apellido').val(), telf_hab:$('#telf_hab').val(), telf_cel: $('#telf_cel').val(), correo:$('#correo').val(), cod_servicio:$('#cod_servicio').val(),id_resultado:$('#id_resultado').val(),genero:$('select#genero').val(),fecha_nac:$('#d_nacimiento').val()},
            
            success:function(datos){
              if(datos.response == 'true'){
                $('#modalActualiza').modal('toggle');
                setTimeout(function(){ $('#modalActualiza').modal('show') }, 1000);
                setTimeout(function(){$(location).attr('href','?view=configuracion&mode=editarResultado')}, 1000);
              }
              else{
                alert('Error. Por favor contacte al administrador del sistema.');
              }
            }
      });   



  });

// FIN FUNCION ACTUALIZA
// FUNCION REFRESCA
  $('#btn-limpiar').click(function(){
    $(location).attr('href','?view=configuracion&mode=editarResultado');
  });
// FIN FUNCION REFRESCA
})