$(document).ready(function() {
    $('#formularioCliente').hide();
    $('#d_efectivo').hide();
    $('#d_venta').hide();
    $('#d_noefectivo').hide();
    $('#formulario').hide();
 
   $('#btn-buscar').click(function(e){
    if ($('#b_telf').val() == "") {
      alert('Debe ingresar uno de los números de teléfono del cliente, intente de nuevo.');
     $('#formularioCliente').hide();
    }
    else{
      $('#contacto').change(function(){
        if ($('#contacto').val() == '1') {
            $('#noefectivo').val("");
            $('#d_efectivo').show();
            $('#d_venta').hide();
            $('#d_noefectivo').hide();

            $('#efectivo').change(function(){
              if ($('#efectivo').val() == '1') {
                  $('#d_venta').show();
                  $('#formulario').show();
                  $('#d_noefectivo').hide();
                  //-------------------------------------------------
                  $('#nombre2').attr('required','required');
                  $('#apellido2').attr('required','required');
                  $('#genero').attr('required','required');
                  $('#fecha_nac').attr('required','required');
                  $('#nacionalidad').attr('required','required');
                  $('#cedula2').attr('required','required');
                  $('#telf_hab').attr('required','required');
                  $('#telf_ofi').attr('required','required');
                  $('#telf_cel').attr('required','required');
                  $('#correo2').attr('required','required');
                  $('#estado').attr('required','required');
                  $('#ciudad').attr('required','required');
                  $('#municipio').attr('required','required');
                  $('#tipo_cuenta').attr('required','required');
                  $('#cuenta2').attr('required','required');
                  $('#observaciones').attr('required','required');
              }
              else{
                $('#formulario').hide();
                $('#d_venta').hide();
                $('#d_noefectivo').hide();
                $('#d_venta').hide();
                $('#d_noefectivo').hide();
                //-------------------------------------------------
                $('#venta').removeAttr('required').val("");
                $('#nombre2').removeAttr('required').val("");
                $('#apellido2').removeAttr('required').val("");
                $('#genero').removeAttr('required').val("");
                $('#fecha_nac').removeAttr('required').val("");
                $('#nacionalidad').removeAttr('required').val("");
                $('#cedula2').removeAttr('required').val("");
                $('#telf_hab').removeAttr('required').val("");
                $('#telf_ofi').removeAttr('required').val("");
                $('#telf_cel').removeAttr('required').val("");
                $('#correo2').removeAttr('required').val("");
                $('#estado').removeAttr('required').val("");
                $('#ciudad').removeAttr('required').val("");
                $('#municipio').removeAttr('required').val("");
                $('#tipo_cuenta').removeAttr('required').val("");
                $('#cuenta2').removeAttr('required').val("");
                $('#observaciones').removeAttr('required').val("");
              }
          })
        }
        else{
         $('#d_noefectivo').show();
         $('#d_efectivo').hide();
         $('#d_venta').hide();
         $('#formulario').hide();
         //-------------------------------------------------------
         $('#efectivo').removeAttr('required').val("");
         $('#venta').removeAttr('required').val("");
         $('#nombre2').removeAttr('required').val("");
         $('#apellido2').removeAttr('required').val("");
         $('#genero').removeAttr('required').val("");
         $('#fecha_nac').removeAttr('required').val("");
         $('#nacionalidad').removeAttr('required').val("");
         $('#cedula2').removeAttr('required').val("");
         $('#telf_hab').removeAttr('required').val("");
         $('#telf_ofi').removeAttr('required').val("");
         $('#telf_cel').removeAttr('required').val("");
         $('#correo2').removeAttr('required').val("");
         $('#estado').removeAttr('required').val("");
         $('#ciudad').removeAttr('required').val("");
         $('#municipio').removeAttr('required').val("");
         $('#tipo_cuenta').removeAttr('required').val("");
         $('#cuenta2').removeAttr('required').val("");
         $('#observaciones').removeAttr('required').val("");
        }  
      });

      telefono = $('#b_telf').val();
      if($('#venta').val() == 0){
        alert("Debes seleccionar un tipo de producto");
      }

      e.preventDefault();
      $.ajax({
        type:'POST',
        url:'?view=formulario&mode=cliente',
        dataType: "json",
        data:{telefono: telefono,servicio: $('#idUser').val()},
        success:function(datos){
          if(datos.response == 'true'){
            console.log(datos);
            $('#formularioCliente').show();
            $('#name').val(datos.name).attr('readonly','readonly');
            $('#dni').val(datos.dni).attr('readonly','readonly');
            $('#phone1').val(datos.phone1).attr('readonly','readonly');
            $('#phone2').val(datos.phone2).attr('readonly','readonly');
            $('#phone3').val(datos.phone3).attr('readonly','readonly');
            $('#email').val(datos.email).attr('readonly','readonly');
            $('#birthday').val(datos.birthday).attr('readonly','readonly');
            $('#account').val(datos.account).attr('readonly','readonly');
            $('#oferta').val(datos.oferta).attr('readonly','readonly');
            $('#id_cliente').val(datos.id_cliente);
          }
          else if(datos.response == 'atendido'){
            alert('El número ingresado ya fue contactado');
            $('#formularioCliente').hide();
          }
          else{
            alert('El número ingresado no existe, verifique he intente de nuevo');
            $('#formularioCliente').hide();
          }
        }
     })
    }
  });

   $("#estado").change(function(){
    id_estado = $("#estado").val();
     $('#ciudad').empty();
    
    $.ajax({
      type:'POST',
      url:'?view=formulario&mode=ciudad',
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
        }
        else{
          alert('No entro');
        }
      }
    })
   });

   $("#ciudad").change(function(){
    servicio = $('#idUser').val(); 
    id_ciudad = $('#ciudad').val();
    id_estado = $('#estado').val();
    $('#municipio').empty();
    $.ajax({
      type:'POST',
      url:'?view=formulario&mode=municipio',
      dataType: "json",
      data:{id_ciudad: id_ciudad,id_estado:id_estado,servicio:servicio},
      success:function(datos){
        if(datos.response == 'true'){
          $('#municipio').append('<option value="">Seleccione...</option>');
            municipio = String(datos.municipio);
            var res = municipio.split("|");
            var obj = "";
            var obj_a = "";
            for (var i = 0; i < res.length - 1; i++) {
              var res_1 = res[i].split(",");
              $('#municipio').append('<option value="' + res_1[0] + '">' + res_1[1] + '</option>')
            }
        }
        else{
          alert('No entro');
        }
      }
    })
   });

})


