$(document).ready(function(){
  $('#btn-eliminar').hide();
  $('#btn-actualizar').hide();
  $('#btn-actualizar_').hide();
  $('#resultado').hide();

  // FUNCIÓN QUE BUSCA LOS DATOS DE LA VENTA

  $('#btn-buscar').click(function(){
    if ($('#b-telf').val() == "") {
      alert('Ingrese un número de teléfono valido.');
    }
    else{
      $.ajax({
        type:'POST',
        url:'?view=configuracion&mode=buscarInv',
        dataType: "json",
        data:{telefono: $('#b-telf').val()},
        success:function(datos){
          if(datos.response == 'true'){
            //BOTONES
            $('#b-telf').attr('readonly','readonly');
            $('#btn-buscar').hide();
            $('#btn-actualizar').show();
            $('#btn-eliminar').show();
            //RESULTADO
            $('#resultado').show();
            $('#formulario').show();
            $('#id_resultado').val(datos.id_resultado);
            $('#cod_servicio').val('oc');
            $('#gestion').val(datos.id_gestion);
            $('select#contacto').val('1');
            $('#nombre').val(datos.nombre);
            $('#apellido').val(datos.apellido);
            $('#edad').val(datos.edad);
            $('#telf_hab').val(datos.telf_hab);
            $('#telf_cel').val(datos.telf_cel);
            $('#correo').val(datos.correo);
            $('select#cobertura').val(datos.cobertura);
            $('select#compania').val(datos.seguro);
            $('select#trabaja').val(datos.trabaja);
            $('select#banco').val(datos.banco);
            $('select#ciudadano').val(datos.ciudadano);
            $('select#estado').val(datos.estado);
            $('select#ciudad').val(datos.ciudad);
            $('#zipcode').val(datos.zipcode);
            $('#fecha_cita').val(datos.fecha_cita);
            $('#hora_cita').val(datos.hora_cita);
          }else if(datos.response == 'eliminado'){
            alert('Esta venta ya se encuentra rechazada');
          }else{
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
    }else{
      a = $('select#eliminar_venta').val();
      b = $('#b-telf').val();
      c = $('#cod_servicio').val();
      d = $('#id_resultado').val();
      e = $('#gestion').val();

      $.ajax({
        type:'POST',
        url:'?view=configuracion&mode=eliminar',
        dataType: "json",
        data:{rechazo: a, cedula: b, servicio: c, id_resultado: d, id_gestion: e},
        success:function(datos){
          if(datos.response == 'true'){
            $('#modalRechazo').modal('hide');
            $('#modalConfirm').modal('toggle');
            setTimeout(function(){ $('#modalConfirm').modal('show') }, 1000);
            setTimeout(function(){$(location).attr('href','?view=configuracion&mode=editarResultadoInverglobe')}, 2000);
          }else{
            alert('NO ENTRO');
          }
        }
      });
    }
  });

  // FIN FUNCION ELIMINA
  // FUNCION QUE ACTUALIZA LA INFORMACIÓN

  $('select#contacto').change(function(){
    if($('select#contacto').val() > 1){
      $('#formulario').hide();
      $('#id_resultado').val();
      $('#gestion').val();
    }else{
      $('#formulario').show();
    }
  });

  $('#btn-actualizar').click(function(){
    if($('select#contacto').val() > 13){
      $('#id_resultado').val();
      $('#gestion').val();  
      $.ajax({
        type:'POST',
        url:'?view=configuracion&mode=actualiza2',
        dataType: "json",
        data:{contacto: $('select#contacto').val(), resultado: $('#id_resultado').val(), gestion: $('#gestion').val(),tipo:1},
        success:function(datos){
          if(datos.response == 'true'){
            $('#modalActualiza').modal('toggle');
            setTimeout(function(){ $('#modalActualiza').modal('show') }, 1000);
            setTimeout(function(){$(location).attr('href','?view=configuracion&mode=editarResultadoInverglobe')}, 2000);
          }else{
            alert('Error. Por favor contacte al administrador del sistema.');
          }
        }
      });
    }else{   
      $.ajax({
        type:'POST',
        url:'?view=configuracion&mode=actualiza2',
        dataType: "json",
        data:{
              resultado:  $('#id_resultado').val(),
              nombre:     $('#nombre').val(),
              apellido:   $('#apellido').val(),
              edad:       $('#edad').val(),
              telf_hab:   $('#telf_hab').val(),
              telf_cel:   $('#telf_cel').val(),
              correo:     $('#correo').val(),
              cobertura:  $('#cobertura').val(),
              compania:   $('#compania').val(),
              trabaja:    $('#trabaja').val(),
              banco:      $('#banco').val(),
              ciudadano:  $('#ciudadano').val(),
              estado:     $('#estado').val(),
              ciudad:     $('#ciudad').val(),
              zipcode:    $('#zipcode').val(),
              fecha_cita: $('#fecha_cita').val(),
              hora_cita:  $('#hora_cita').val(),
              tipo:       2
            },
        success:function(datos){
        if(datos.response == 'true'){
          $('#modalActualiza').modal('toggle');
          setTimeout(function(){ $('#modalActualiza').modal('show') }, 1000);
          setTimeout(function(){$(location).attr('href','?view=configuracion&mode=editarResultadoInverglobe')}, 2000);
          }else{
            alert('Error. Por favor contacte al administrador del sistema.');
          }
        }
      });   
    }
  });

  // FIN FUNCION ACTUALIZA
  // FUNCION REFRESCA
  $('#btn-limpiar').click(function(){
    $(location).attr('href','?view=editar&mode=editarResultadoInverglobe');
  });
  // FIN FUNCION REFRESCA

  $("#estado").change(function(){
    id_estado = $("#estado").val();
    $('#ciudad').empty();

    $.ajax({
      type:'POST',
      url:'?view=configuracion&mode=ciudad_',
      dataType: "json",
      data:{id_estado: id_estado},
      success:function(datos){
        if(datos.response == 'true'){
          $('#ciudad').append('<option value="">Seleccione...</option>');
          ciudades = String(datos.ciudad);
          var res = ciudades.split("|");
          var obj = "";
          var obj_a = "";
          for (var i = 0; i < res.length - 1; i++) {
            var res_1 = res[i].split(",");
            $('#ciudad').append('<option value="' + res_1[0] + '">' + res_1[1] + '</option>')
          }
        }else{
          alert('No entro');
        }
      }
    })
  });

});