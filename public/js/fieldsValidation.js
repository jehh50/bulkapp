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

function onlyPanNumber(e) { //Validación del campo número de cuenta
    //
    // Campo PAN: permite solo números y asteriscos, máximo 20 caracteres
    // (ej. 123456789******1234 o 12345678901234567890)
    e.value = e.value.replace(/[^0-9*]/g, '').slice(0, 20);
}

function upperCase(e) {
    e.value = e.value
        .toUpperCase()
}

// Detecta dominios con etiquetas repetidas: gmail.com.gmail.com, hotmail.com.com, etc.
function dominioDuplicado(correo) {
    let dominio = correo.split('@')[1];
    if (!dominio) return false;
    let etiquetas = dominio.toLowerCase().split('.');
    return new Set(etiquetas).size !== etiquetas.length;
}

function validateEmail(e) {
    let valueForm = e.value.trim();
    let patron = /^[a-zA-Z0-9_.+-]+@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,4}$/;

    if (!patron.test(valueForm)) {
        alert('La dirección de correo es invalida, el formato debe coincidir con DIRECCION@DOMINIO.COM intente de nuevo.');
        e.focus();
        return false;
    }

    if (dominioDuplicado(valueForm)) {
        alert('El dominio del correo está repetido: ' + valueForm + '. Escriba el dominio una sola vez (ej. DIRECCION@GMAIL.COM).');
        e.focus();
        return false;
    }

    return true;
}

function validaTelf(e) {
    valueForm = e.value
    let patron = /^[(]\d{4}[)]\d{3}.\d{2}.\d{2}$/;
    if (valueForm.search(patron) != 0) {
        alert("El formato del número de telefono debe coincidir con (XXXX)XXX.XX.XX. Por favor verifique.");
    }
}

function formatDate(e) {
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
        let pan = document.getElementById('pan').value;

        let patron_tlf = /^[(]\d{4}[)]\d{3}.\d{2}.\d{2}$/;
        let patron_correo = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9]{2,4}$/;
        let patron_nom_ape = /^[a-zA-Z ñáéíóú]{2,60}$/i;
        let patron_fecha = /^\d{2}\-\d{2}\-\d{4}$/;  // Expresión regular para validar formato dd/mm/yyyy

        // Validación de selección de producto
        if (venta == 0) {
            alert("Debe seleccionar un producto");
            return false;
        }

        if (nombre2 == '' || nombre2.length < 3) {
            alert("Debe ingresar el nombre");
            return false;
        }

        if (apellido2 == '' || apellido2.length < 3) {
            alert("Debe ingresar el apellido");
            return false;
        }

        let campoGenero = document.getElementById('genero');
        let genero = campoGenero ? campoGenero.value : '';
        if (genero == '') {
            alert("Debe seleccionar el género");
            return false;
        }

        if (fecha_nac == '' || fecha_nac.length < 10) {
            alert("Debe ingresar la fecha de nacimiento");
            return false;
        }

        if (telf_hab == '' || telf_hab.length < 15) {
            alert("Debe ingresar el teléfono");
            return false;
        }

        if (telf_cel == '' || telf_cel.length < 15) {
            alert("Debe ingresar el teléfono");
            return false;
        }

        if (cedula2 == '' || cedula2.length < 6) {
            alert('El campo cédula no puede estar vacio o no está completo');
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

        let [dia, mes, año] = fecha_nac.split('-').map(Number);  // Cambiado para dd/mm/yyyy
        let fechaNacimiento = new Date(año, mes - 1, dia);
        let fechaHoy = new Date();

        let edad = fechaHoy.getFullYear() - año;
        let mesDiff = fechaHoy.getMonth() - (mes - 1);
        if (mesDiff < 0 || (mesDiff === 0 && fechaHoy.getDate() < dia)) {
            edad--;
        }

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

        // El dominio no puede repetirse (ej. juan@gmail.com.gmail.com)
        if (dominioDuplicado(correo2)) {
            alert('El dominio del correo está repetido: ' + correo2 + '. Escriba el dominio una sola vez.');
            document.getElementById('correo2').focus();
            return false;
        }

        // Validación de PAN: exactamente 20 caracteres, solo números y asteriscos
        let patron_pan_number = /^[0-9*]{20}$/;
        if (!patron_pan_number.test(pan)) {
            alert('El campo número de cuenta debe tener exactamente 20 caracteres (números y asteriscos). Ej: 123456789******1234');
            document.getElementById('pan').focus();
            return false;
        }

        // Si todo está validado correctamente
        return true;
    }

}
