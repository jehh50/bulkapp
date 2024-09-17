$(document).ready(function(){
	$("#btn-guardar").click(function(){
		  contacto        = $("#contacto").val();
	      efectivo        = $("#efectivo").val();
	      noefectivo      = $("#noefectivo").val();
	      producto        = $("#venta").val();
	      nombre          = $("#nombre2").val();
	      apellido        = $("#apellido2").val();
	      genero          = $("#genero").val();
	      fecha_nac       = $("#fecha_nac").val();
	      nacionalidad    = $("#nacionalidad").val();
	      cedula2         = $("#cedula2").val();
	      telf_hab        = $("#telf_hab").val();
	      telf_ofi        = $("#telf_ofi").val();
	      telf_cel        = $("#telf_cel").val();
	      correo2         = $("#correo2").val();
	      estado          = $("#estado").val();
	      ciudad          = $("#ciudad").val();
	      municipio       = $("#municipio").val();
	      tipo_cuenta     = $("#tipo_cuenta").val();
	      cuenta2         = $("#cuenta2").val();
	      observaciones   = $("#observaciones").val();

	      $.ajax({
	      type:'POST',
	      url:'?view=formulario&mode=registro',
	      dataType: "json",
	      data:{contacto:contacto,efectivo:efectivo,noefectivo:noefectivo,producto:producto,nombre:nombre,apellido:apellido,genero:genero,
	            fecha_nac:fecha_nac,nacionalidad:nacionalidad,cedula2:cedula2,telf_hab:telf_hab,telf_ofi:telf_ofi,telf_cel:telf_cel,
	            correo2:correo2,estado:estado,ciudad:ciudad,municipio:municipio,tipo_cuenta:tipo_cuenta,cuenta2:cuenta2,observaciones:observaciones},
	      success:function(datos){
	        if(datos.response == 'true'){
	          //$(location).attr('href','?view=formulario&mode=index');
	        }
	        else{
	          alert('No entro');
	        }
	      }
	    })
	});
})