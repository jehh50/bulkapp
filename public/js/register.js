//VALIDACIONES DE CAMPOS
$(document).ready(function(){
	$('#btn-register').click(function(){
		nombre 		= $('#nombre').val();
		apellido 	= $('#apellido').val();
		user 		= $('#user').val();
		pass 		= $('#password').val();
		tipo		= $('#tipo_usuario').val();
		cliente 	= $('#servicio').val();

		$.ajax({
		      type:'POST',
		      url:'?view=usuarios&mode=registro',
		      dataType: "json",
		      data:{cliente: cliente, nombre: nombre, apellido: apellido, user: user, pass: pass, tipo: tipo},
		      success:function(datos){
		        if(datos.response == 'true'){
		          alert('REGISTRO EXITOSO');
		          $(location).attr('href','?view=usuarios&mode=index');
		        }
		        else{
		          alert('El usuario ya existe...');
		        }
		      }
		})
	});

	$('#update').click(function(){
		userid		=	$('#userid').val();
		nombre		=	$('#nombre').val();
		apellido	=	$('#apellido').val();
		rol			=	$('#rol').val();
		status		=	$('#status').val();
		servicio 	= 	$('#servicio').val();

		$.ajax({
		      type:'POST',
		      url:'?view=usuarios&mode=update',
		      dataType: "json",
		      data:{userid: userid, nombre: nombre, apellido: apellido, rol: rol, status: status, servicio: servicio},
		      success:function(datos){
		        if(datos.response == 'true'){
		          alert('USUARIO ACTUALIZADO');
		          $(location).attr('href','?view=usuarios&mode=index');
		        }
		        else{
		          alert('ERROR');
		        }
		      }
		})
	});

	$('#password').click(function(){
		userid	=  $('#userid').val();
		$.ajax({
		      type:'POST',
		      url:'?view=usuarios&mode=password',
		      dataType: "json",
		      data:{userid: userid},
		      success:function(datos){
		        if(datos.response == 'true'){
		          alert('PASSWORD ACTUALIZADO DEBE INGRESAR \nCON LA CLAVE 123456');
		          $(location).attr('href','?view=usuarios&mode=index');
		        }
		        else{
		          alert('ERROR');
		        }
		      }
		})
	});
})
