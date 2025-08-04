$(function () {
	$('[data-toggle="tooltip"]').tooltip()
})

function onlyLetters(e) {
    // Convertir el texto a mayúsculas y permitir solo letras y números
    e.value = e.value
        .toUpperCase() // Convertir a mayúsculas
        .replace(/[^A-Z\s]/g, ''); // Eliminar caracteres que no sean letras ni números
}

function onlyNumbers(e) {
    // Permitir solo números y un solo punto decimal
    e.value = e.value.replace(/[^0-9.]/g, '');
    // Solo permite un punto decimal
    let parts = e.value.split('.');
    if (parts.length > 2) {
        e.value = parts[0] + '.' + parts.slice(1).join('');
    }
}

function upperCase(e){
	e.value = e.value
		.toUpperCase()
}

function validateEmail(e) {
	valueForm = e.value;
	let patron = new RegExp(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/)
	if (valueForm.search(patron) != 0) {
		alert('La dirección de correo es invalida, el formato debe coincidir con DIRECCION@DOMINIO.COM intente de nuevo.');
		window.location.hash = "#correo2";
	}
}

function validaTelf(e) {
	valueForm = e.value
	let patron = /^[(]\d{4}[)]\d{3}.\d{2}.\d{2}$/;
	if (valueForm.search(patron) != 0) {
		alert("El formato del número de telefono debe coincidir con (XXXX)XXX.XX.XX. Por favor verifique.");
	}
}

function formatDate(e){
      let value = e.value.replace(/\D/g, ''); // Eliminar todo lo que no sea dígito
      let day, month, year;

      if (value.length >= 1) {
        day = value.substring(0, 2); // Obtener los primeros 2 dígitos (día)
      }

      if (value.length >= 3) {
        month = value.substring(2, 4); // Obtener los siguientes 2 dígitos (mes)
      }

      if (value.length >= 5) {
        year = value.substring(4, 8); // Obtener los últimos 4 dígitos (año)
      }

    if (year) {
        e.value = `${day}-${month}-${year}`;
    } else if (month) {
    	e.value = `${day}-${month}`;
    } else if (day) {
    	e.value = day;
    }
}

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


function validateForm() {
    console.log('Comienza la validación...');
    
    // Solo procede si ambos elementos son visibles
    if ($('#d_venta').is(':visible') && $('#formulario').is(':visible')) {
        let venta = document.getElementById('venta').value;
        let nombre2 = document.getElementById('nombre2').value;
        let apellido2 = document.getElementById('apellido2').value;
        let fecha_nac = document.getElementById('fecha_nac').value;
        let cedula2 = document.getElementById('cedula2').value;
        let telf_hab = document.getElementById('telf_hab').value;
        let telf_cel = document.getElementById('telf_cel').value;
        let correo2 = document.getElementById('correo2').value;

        let patron_tlf = /^[(]\d{4}[)]\d{3}.\d{2}.\d{2}$/;
        let patron_correo = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9]{2,4}$/;
        let patron_nom_ape = /^[a-zA-Z ñáéíóú]{2,60}$/i;
        let patron_fecha = /^\d{2}\-\d{2}\-\d{4}$/;  // Expresión regular para validar formato dd/mm/yyyy

        // Validación de selección de producto
        if (venta == 0) {
            alert("Debe seleccionar un producto");
            return false;
        }

		if(cedula2 < 6){
			alert('El campo cédula no puede estar vacio');
			return false;
		}

        // Función para validar nombres y apellidos
        function validarNombreApellido(valor, tipo) {
            if (valor.search(patron_nom_ape) !== 0) {
                alert(`${tipo} inválido: ${valor}. El formato debe ser solo letras (ej. Pedro Luis).`);
                document.getElementById(tipo.toLowerCase() + '2').focus();
                return false;
            }
            return true;
        }

        // Validación de nombre
        if (!validarNombreApellido(nombre2, 'Nombre')) return false;

        // Validación de apellido
        if (!validarNombreApellido(apellido2, 'Apellido')) return false;

        // Validación de fecha de nacimiento y cálculo de edad
        if (!fecha_nac.match(patron_fecha)) {
            alert('El formato de la fecha de nacimiento debe ser dd-mm-yyyy.');
            document.getElementById('fecha_nac').focus();
            return false;
        }

        console.log('Cálculo de edad');
        let [dia, mes, año] = fecha_nac.split('-').map(Number);  // Cambiado para dd/mm/yyyy
        let fechaNacimiento = new Date(año, mes - 1, dia);
        let fechaHoy = new Date();

        let edad = fechaHoy.getFullYear() - año;
        let mesDiff = fechaHoy.getMonth() - (mes - 1);
        if (mesDiff < 0 || (mesDiff === 0 && fechaHoy.getDate() < dia)) {
            edad--;
        }

        console.log('Edad calculada: ' + edad);
        if (edad < 18 || edad > 64) {
            alert('La edad debe ser mayor a 18 o menor de 64. Edad: ' + edad);
            return false;
        }

        // Validación de teléfono
        if (!telf_hab.match(patron_tlf) || !telf_cel.match(patron_tlf)) {
            alert("El formato del número de teléfono debe coincidir con (XXXX)XXX.XX.XX. Por favor verifique.");
            document.getElementById('telf_hab').focus();
            return false;
        }

        // Validación de correo electrónico
        if (!correo2.match(patron_correo)) {
            alert('La dirección de correo es inválida. El formato debe ser dirección@dominio.com.');
            document.getElementById('correo2').focus();
            return false;
        }

        // Si todo está validado correctamente
        console.log('Formulario validado con éxito');
        return true;
    }

    return false;
}

function validateForm2() {
    console.log('Comienza la validación del segundo formulario...');

    // let paymentPlan = document.getElementById('paymentPlan').value;
    // // let paymentDate = document.getElementById('paymentDate').value;
    // let fullName = document.getElementById('fullName').value;
    // let relationship = document.getElementById('relationship').value;

    // let patron_name = /^[a-zA-Z ñáéíóú]{2,60}$/i;

    // // Validación de nombre
    // // if (!fullName.match(patron_name)) {
    // //     alert("Nombre inválido: " + fullName + ". El formato debe ser solo letras (ej. Pedro Luis).");
    // //     document.getElementById('fullName').focus();
    // //     return false;
    // // }

    // // // Validación de payment plan
    // // if (paymentPlan === "") {
    // //     alert("Debe seleccionar un plan de pago.");
    // //     document.getElementById('paymentPlan').focus();
    // //     return false;
    // // }
    // // Validación de fecha de pago
    // // if (paymentDate === ""  ) {
    // //     alert('Debe seleccionar una fecha de compromiso.');
    // //     document.getElementById('paymentDate').focus();
    // //     return false;
    // // }
    // // Validación de relación
    // if (relationship === "") {
    //     alert("Debe seleccionar un parentesco.");
    //     document.getElementById('relationship').focus();
    //     return false;
    // }

    // return true; // Si todas las validaciones pasan, permite el envío del formulario
}