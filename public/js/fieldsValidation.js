$(function () {
	$('[data-toggle="tooltip"]').tooltip()
})

function mayus(e) {
	e.value = e.value.toUpperCase();
}

function onlyNumbers(e) {
	const input = e.target.value;
	const numbers = input.replace(/\D/g, '');
	if(e.target.value!=e.target.value.replace(/\D/g, '')){
		alert('Solo se permiten números');
		window.location.hash = "#cedula2";
	}
}

function fecha_actual() {
	var tiempo = new Date();
	var hora = tiempo.getHours();
	var minuto = tiempo.getMinutes();
	var segundo = tiempo.getSeconds();
	var horaactual = hora + ':' + minuto + ':' + segundo;
	$("#hora_actuall").val(horaactual);
}

function validateEmail(e) {
	valueForm = e.value;
	var patron = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/)
	if (valueForm.search(patron) != 0) {
		alert('La dirección de correo es invalida, el formato debe coincidir con DIRECCION@DOMINIO.COM intente de nuevo.');
		window.location.hash = "#correo2";
	}
}

function validaTelf(e) {
	valueForm = e.value
	var patron = /^[(]\d{4}[)]\d{3}.\d{2}.\d{2}$/;
	if (valueForm.search(patron) != 0) {
		alert("El formato del número de telefono debe coincidir con (XXXX)XXX.XX.XX. Por favor verifique.");
	}
}

function validaCuenta(e) {
	valueForm = e.value
	if (valueForm.length < 20) {
		alert("El número de cuenta tiene menos de 20 digitos");
	}
}

function accountField(e) {
	valueForm = e.value;
	var patrona = new RegExp(/^\d{6}[*][*][*][*][*][*][*][*][*][*]\d{4}$/)
	var patronb = new RegExp(/^\d{20}$/)
	if (valueForm.search(patrona) != 0 && valueForm.search(patronb) != 0) {
		alert('El campo no cumple con la condición de solo números o de encriptación');
	}
}

function onlyLetters(e) {
	key = e.keyCode || e.which;
	teclado = String.fromCharCode(key).toLowerCase();
	letras = "qwertyuiopasdfghjklñzxcvbnm";

	especiales = "32";

	if (letras.indexOf(teclado) == -1 && especiales != key) {
		alert("Solo se permite letras.")
		return false;
	}
}

function validaform() {
	if ($('#d_venta').is(':visible') && $('#formulario').is(':visible')) {
		var venta = document.getElementById('venta').value;
		var nombre2 = document.getElementById('nombre2').value;
		var apellido2 = document.getElementById('apellido2').value;
		var fecha_nac = document.getElementById('fecha_nac').value;
		var cedula2 = document.getElementById('cedula2').value;
		var telf_hab = document.getElementById('telf_hab').value;
		var telf_ofi = document.getElementById('telf_ofi').value;
		var telf_cel = document.getElementById('telf_cel').value;
		var correo2 = document.getElementById('correo2').value;
		var num_cuenta = document.getElementById('cuenta2').value;
		var patron_tlf = /^[(]\d{4}[)]\d{3}.\d{2}.\d{2}$/;
		var patron_correo = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/)
		var patron_nom_ape = /^([a-z ñáéíóú]{2,60})$/i;

		//VALIDO TIPO DE PRODUCTO
		if (venta == 0) {
			alert("Debe seleccionar un producto")
			return false;
		}

		//VALIDO PATRON DE NOMBRE
		if (nombre2.search(patron_nom_ape) != 0) {
			alert("El nombre es invalido " + nombre2 + "el formato debe coincidir con PEDRO LUIS ó PEDRO; solo letras");
			return false;
		}

		//VALIDO PATRON DE APELLIDO
		if (apellido2.search(patron_nom_ape) != 0) {
			alert("El apellido es invalido " + apellido2 + "el formato debe coincidir con LOPEZ CORREA ó LOPEZ; solo letras");
			return false;
		}   /**/

		//VALIDO LA FECHA
		var elem = fecha_nac.split('-');
		var año = elem[0];     //var dia = elem[0];   //var mes = elem[1]; 
		var fechHoy = new Date();
		var añoHoy = fechHoy.getFullYear();
		var resta = Math.abs(añoHoy - año)
		if (resta < 18 || resta > 64) {
			alert('La edad debe ser mayor a 18 o menor de 64. Edad: ' + resta)
			return false;
		}

		//VALIDO PATRON DE CEDULA 
		if (!/^([0-9])*$/.test(cedula2)) {
			alert("La cédula " + cedula2 + " no es un número");
			return false;
		}

		//VALIDO PATRON DE TELEFONO
		if (telf_hab.search(patron_tlf) != 0 || telf_ofi.search(patron_tlf) != 0 || telf_cel.search(patron_tlf) != 0) {
			alert("El formato del número de telefono debe coincidir con (XXXX)XXX.XX.XX. Por favor verifique.");
			return false;
		}

		//VALIDO PATRON DE CORREO
		if (correo2.search(patron_correo) != 0) {
			alert('La dirección de correo es invalida, el formato debe coincidir con DIRECCION@DOMINIO.COM intente de nuevo.');
			window.location.hash = "#correo2";
			return false;
		}

		// VALIDO LA LONGITUD Y ESCRITURA DEL NUMERO DE CUENTA     
		if (num_cuenta.length < 20) {
			alert("El número de cuenta tiene menos de 20 digitos");
			return false;
		}
	}
}

document.querySelectorAll('.telefono').forEach(function (element) {
	element.addEventListener('input', function (event) {
		let input = event.target.value.replace(/\D/g, ''); // Remueve todos los caracteres no numéricos
		let formattedInput = '';

		if (input.length > 0) {
			formattedInput += '(' + input.substring(0, 4);
		}
		if (input.length >= 5) {
			formattedInput += ')' + input.substring(4, 7);
		}
		if (input.length >= 8) {
			formattedInput += '.' + input.substring(7, 9);
		}
		if (input.length >= 10) {
			formattedInput += '.' + input.substring(9, 11);
		}

		event.target.value = formattedInput;
	});
});

