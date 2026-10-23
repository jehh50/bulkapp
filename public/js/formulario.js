$(document).ready(function () {

  $('#formGestion').hide();
  $('#d_efectivo').hide();
  $('#d_venta').hide();
  $('#d_noefectivo').hide();
  $('#formulario').hide();

  // Campos requeridos del formulario de venta (se activan solo si efectivo == 1)
  var ventaRequeridos = ['nombre2', 'apellido2', 'genero', 'fecha_nac', 'nacionalidad', 'cedula2',
    'telf_hab', 'telf_cel', 'correo2', 'estado', 'ciudad', 'municipio', 'tipo_cuenta', 'pan', 'venta'];

  function requerirVenta() {
    ventaRequeridos.forEach(function (id) {
      $('#' + id).attr('required', 'required');
    });
  }

  function quitarRequeridos() {
    ventaRequeridos.forEach(function (id) {
      $('#' + id).removeAttr('required').val('');
    });
  }

  function mostrarError(msg) {
    $('#searchError').text(msg).show();
  }

  // Datos del cliente (panel 1 · Cliente) — lista de texto
  function limpiarCliente() {
    ['name', 'dni', 'phone1', 'phone2', 'phone3', 'birthday', 'account', 'email'].forEach(function (id) {
      $('#' + id).text('—');
    });
    $('#initials').text('?');
    $('#profileName').text('Sin cliente');
    $('#profileSub').text('Busca un número de teléfono');
    $('#fgProfile').removeClass('fg-profile--found');
  }

  // Reset completo de la gestión
  function reset() {
    $('#b_telf').val('');
    $('#contacto').val('');
    $('#efectivo').val('');
    $('#noefectivo').val('');
    $('#venta').val('0');
    $('#id_cliente').val('');
    $('#searchError').hide();
    $('#d_efectivo').hide();
    $('#d_venta').hide();
    $('#d_noefectivo').hide();
    $('#formulario').hide();
    quitarRequeridos();
    limpiarCliente();
    $('#formGestion').hide();
    $('#emptyState').show();
  }

  $('.fg-reset').on('click', function () {
    reset();
  });

  // Buscar cliente por teléfono
  $('#btn-buscar').on('click', function (e) {
    var telefono = $.trim($('#b_telf').val());

    if (telefono.length < 5) {
      mostrarError('Introduce un número de teléfono válido (10 a 11 dígitos).');
      return;
    }

    $('#searchError').hide();

    $.ajax({
      type: 'POST',
      url: '?view=formulario&mode=cliente',
      dataType: "json",
      data: { telefono: telefono, servicio: $('#idUser').val() },
      success: function (datos) {
        if (datos.response == 'true') {
          var nombre = String(datos.name || '');
          var partes = nombre.trim().split(/\s+/).filter(Boolean);
          var iniciales = partes.slice(0, 2).map(function (p) { return (p[0] || '').toUpperCase(); }).join('');

          $('#initials').text(iniciales);
          $('#profileName').text(nombre || '—');
          $('#profileSub').text('Tel. ' + (datos.phone3 || telefono));
          $('#fgProfile').addClass('fg-profile--found');

          $('#name').text(nombre);
          $('#dni').text(datos.dni);
          $('#phone1').text(datos.phone1);
          $('#phone2').text(datos.phone2);
          $('#phone3').text(datos.phone3);
          $('#birthday').text(datos.birthday);
          $('#account').text(datos.account);
          $('#email').text(datos.email);

          $('#id_cliente').val(datos.id_cliente);
          $('#contacto').val('');
          $('#efectivo').val('');
          $('#noefectivo').val('');
          $('#venta').val('0');
          quitarRequeridos();

          $('#emptyState').hide();
          $('#formGestion').show();
        }
        else if (datos.response == 'atendido') {
          limpiarCliente();
          $('#formGestion').hide();
          $('#emptyState').show();
          mostrarError('El número ingresado ya fue contactado.');
        }
        else {
          limpiarCliente();
          $('#formGestion').hide();
          $('#emptyState').show();
          mostrarError('No se encontraron datos para el teléfono ' + telefono + '.');
        }
      }
    });
  });

  // Tipo de contacto: 1 = Efectivo, 0 = No efectivo
  $('#contacto').on('change', function () {
    if ($(this).val() == '1') {
      $('#noefectivo').val('');
      $('#d_efectivo').show();
      $('#d_venta').hide();
      $('#d_noefectivo').hide();
      $('#formulario').hide();
      quitarRequeridos();
    } else {
      $('#d_noefectivo').show();
      $('#d_efectivo').hide();
      $('#d_venta').hide();
      $('#formulario').hide();
      $('#efectivo').val('');
      quitarRequeridos();
    }
  });

  // Resultado del contacto: 1 = Gestión efectiva (abre el formulario de venta)
  $('#efectivo').on('change', function () {
    if ($(this).val() == '1') {
      $('#d_venta').show();
      $('#formulario').show();
      $('#d_noefectivo').hide();
      requerirVenta();
    } else {
      $('#formulario').hide();
      $('#d_venta').hide();
      $('#d_noefectivo').hide();
      quitarRequeridos();
    }
  });

  // Estado → Ciudad
  $('#estado').on('change', function () {
    id_estado = $('#estado').val();
    $('#ciudad').empty();

    $.ajax({
      type: 'POST',
      url: '?view=formulario&mode=ciudad',
      dataType: "json",
      data: { id_estado: id_estado },
      success: function (datos) {
        if (datos.response == 'true') {
          $('#ciudad').append('<option value="">Seleccione...</option>');
          ciudades = String(datos.ciudad);
          var res = ciudades.split("|");
          for (var i = 0; i < res.length - 1; i++) {
            var res_1 = res[i].split(",");
            $('#ciudad').append('<option value="' + res_1[0] + '">' + res_1[1] + '</option>');
          }
        }
      }
    });
  });

  // Ciudad → Municipio
  $('#ciudad').on('change', function () {
    servicio = $('#idUser').val();
    id_estado = $('#estado').val();
    $('#municipio').empty();

    $.ajax({
      type: 'POST',
      url: '?view=formulario&mode=municipio',
      dataType: "json",
      data: { id_ciudad: $('#ciudad').val(), id_estado: id_estado, servicio: servicio },
      success: function (datos) {
        if (datos.response == 'true') {
          $('#municipio').append('<option value="">Seleccione...</option>');
          municipio = String(datos.municipio);
          var res = municipio.split("|");
          for (var i = 0; i < res.length - 1; i++) {
            var res_1 = res[i].split(",");
            $('#municipio').append('<option value="' + res_1[0] + '">' + res_1[1] + '</option>');
          }
        }
      }
    });
  });
});
