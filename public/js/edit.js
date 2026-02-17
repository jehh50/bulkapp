$(document).ready(function () {
  document.querySelectorAll('.telefono').forEach(function (element) {
    element.addEventListener('input', function (event) {
      let input = event.target.value.replace(/\D/g, ''); // Remueve todos los caracteres no numéricos
      let formattedInput = '(0'; // Asegura el '0' en la primera posición

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

  $('#btn-buscar').click(function () {
    $('#message').hide();
    if ($('#cedula').val() === "") {
      alert('Ingrese un número de cedula valido.');
    } else {
      let gender = '';

      $.ajax({
        type: 'POST',
        url: '?view=configuracion&mode=buscar',
        dataType: "json",
        data: { cedula: $('#cedula').val() },
        success: function (datos) {
          if (datos.response === 'true') {
            for (let i = 0; i < datos.count; i++) {
              if (datos.data[i].genero === 'F') {
                gender = '<option selected value="F">FEMENINO</option><option value="M">MASCULINO</option></select></div>';
              } else {
                gender = '<option value="F">FEMENINO</option><option selected value="M">MASCULINO</option></select></div>';
              }

              let openProduct = `<div class="form-group col-lg-4"><span class="form-group-addon">Producto</span><select class="form-control" id="producto${i}"></select>`;

              let divHeader = `
                <div class="panel panel-default">
                  <div class="panel-heading" role="tab" id="heading${i}">
                    <h4 class="panel-title">
                      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse${i}" aria-expanded="false" aria-controls="collapse${i}">
              `;

              let divBody = `</a></h4></div><div id="collapse${i}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading${i}"><div class="panel-body">`;

              let divForm = `
                ${openProduct}</div>
                <div class="form-group col-lg-4">
                  <span class="form-group-addon">Nombres</span>
                  <input type="text" class="form-control" placeholder="Julio Cesar" aria-describedby="nombre" id="nombre${i}" oninput="onlyLetters(this);" value="${datos.data[i].nombre}">
                </div>
                <div class="form-group col-lg-4">
                  <span class="form-group-addon">Apellidos</span>
                  <input type="text" class="form-control" placeholder="Perez Gomez" aria-describedby="apellido" id="apellido${i}" oninput="onlyLetters(this);" value="${datos.data[i].apellido}">
                </div>
                <div class="form-group col-lg-4">
                  <span class="form-group-addon">Cédula</span>
                  <input type="text" class="form-control" placeholder="12345678" aria-describedby="cedula" id="cedula${i}" oninput="onlyNumbers(this);" value="${datos.data[i].cedula}">
                </div>
                <div class="form-group col-lg-4">
                  <span class="form-group-addon">Sexo</span>
                  <select class="form-control" id="genero${i}">${gender}</select>
                  <div class="form-group col-lg-4">
                    <span class="form-group-addon">Fecha de nacimiento</span>
                    <input type="text" class="form-control" placeholder="01/01/2024" aria-describedby="d_nacimiento" id="d_nacimiento${i}" oninput="formatDate(this);" value="${datos.data[i].nacimiento}">
                  </div>
                  <div class="form-group col-lg-4">
                    <span class="form-group-addon">Teléfono habitación</span>
                    <input type="text" class="form-control telefono" placeholder="(0212)345.67.89" aria-describedby="tlf_hab" id="telf_hab${i}" value="${datos.data[i].telf_hab}">
                  </div>
                  <div class="form-group col-lg-4">
                    <span class="form-group-addon">Teléfono celular</span>
                    <input type="text" class="form-control telefono" placeholder="(0424)234.56.78" aria-describedby="tlf_celu" id="telf_cel${i}" value="${datos.data[i].telf_celular}">
                  </div>
                  <div class="form-group col-lg-4">
                    <span class="form-group-addon">Correo</span>
                    <input type="email" class="form-control" placeholder="usuario@dominio.com" aria-describedby="correo" id="correo${i}" onkeyup="mayus(this); validateMail(this);" value="${datos.data[i].correo}">
                  </div>
                  <div class="form-group col-lg-4">
                    <span class="form-group-addon">Fecha de venta</span>
                    <input type="text" class="form-control" placeholder="20240101" aria-describedby="saleDate" id="saleDate${i}" oninput="formatDate_(this)" value="${datos.data[i].fecha_venta}">
                  </div>
                  <input type="hidden" id="id_resultado${i}" value="${datos.data[i].id_resultado}">
                  <input type="hidden" id="gestion${i}" value="${datos.data[i].id_gestion}">
                </div>
              `;

              let buttonBack = `
                <div class="container" style="margin: -10px 0 10px 15px">
                  <div class="btn-group">
                    <a href="?view=configuracion&mode=editarResultado" class="btn btn-default" id="btn-limpiar"> Regresar</a>`;

              let buttonDelete = `
                <button class="btn btn-md btn-danger" id="btn-eliminar" data-toggle="modal" data-target="#modalRechazo${i}">
                  <span class="glyphicon glyphicon-remove"></span> Eliminar
                </button>`;

              let buttonEdit = `
              <button class="btn btn-md btn-success" id="btn-actualizar" data-toggle="modal" data-target="#modalActualiza${i}">
                      <span class="glyphicon glyphicon-floppy-disk"></span> Guardar
              </button>`;

              // let otherButton = `
              //   <button class="btn btn-md btn-success" id="btn-show">
              //     <span class="glyphicon glyphicon-pencil"></span> Show
              //   </button>
              // `;

              let divClose = `</div></div></div></div>`;

              $('#accordion').append(divHeader + datos.data[i].nombre + ' ' + datos.data[i].apellido + divBody + divForm +buttonBack + buttonEdit + buttonDelete + divClose);
              
              let idProducto = datos.data[i].producto_id;
              let selectId = `#producto${i}`;

              if ($(selectId).length) {

                $.ajax({
                  type: 'POST',
                  url: '?view=configuracion&mode=productos',
                  dataType: "json",
                  data: { id: 1 },
                  success: function (datos) {

                    if (datos.response === 'true') {

                      let select = $(selectId);
                      select.empty();

                      let result = String(datos.productos);
                      let res = result.split("|");

                      for (let j = 0; j < res.length - 1; j++) {

                        let res_1 = res[j].split(",");
                        let selected = (idProducto == res_1[0]) ? "selected" : "";

                        select.append(
                          `<option value="${res_1[0]}" ${selected}>
              ${res_1[1]}
            </option>`
                        );
                      }

                    } else {
                      alert('No entró');
                    }

                  }
                });

              } else {
                console.error(`No se encontró el elemento con ID ${selectId}`);
              }


              // function generarUrlActualizacion(i, datos) {
              //   let productoId = $("select#producto" + i).val();
              //   let nombre = $("#nombre" + i).val();
              //   let apellido = $("#apellido" + i).val();
              //   let cedula = $("#cedula" + i).val();
              //   let sexo = $("select#genero" + i).val();
              //   let nacimiento = $("#d_nacimiento" + i).val();
              //   let hab = $("#telf_hab" + i).val();
              //   let cel = $("#telf_cel" + i).val();
              //   let correo = $("#correo" + i).val();
              //   let venta = $("#saleDate" + i).val();

              //   return `?view=configuracion&mode=actualiza&productoId=${productoId}&id=${datos.data[i].id_resultado}&nombre=${nombre}&apellido=${apellido}&cedula=${cedula}&sexo=${sexo}&nacimiento=${nacimiento}&hab=${hab}&cel=${cel}&correo=${correo}&venta=${venta}`;
              // }

              // let url = generarUrlActualizacion(i, datos);

              let modalDelete = `
                <div class="modal fade" id="modalRechazo${i}" tabindex="-1" role="dialog" aria-labelledby="modalRechazo${i}">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">¿Está seguro de eliminar la venta?</h4>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <a href="?view=configuracion&mode=eliminar&id=${datos.data[i].id_resultado}" class="btn btn-md btn-danger" id="btn-eliminar">
                          <span class="glyphicon glyphicon-remove"></span> Eliminar
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              `;

              let modalUpdate = `
                <div class="modal fade" id="modalActualiza${i}" tabindex="-1" role="dialog" aria-labelledby="modalActualiza${i}">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">¿Está seguro de actualizar la venta de <strong>${datos.data[i].nombre} ${datos.data[i].apellido}</strong>?</h5>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="actualizarRegistro(${i}, ${datos.data[i].id_resultado})"><span class="glyphicon glyphicon-edit"></span> Guardar</button>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              `;

              $('#modals').append(modalDelete);
              $('#modals').append(modalUpdate);

              $(`#producto${i}`).on('click', function () {
                // Event handler for product select
                let updatedValues = {
                  // Suponiendo que tienes campos de entrada con IDs específicos
                  nombre: $(`#nombre${i}`).val(),
                };

                // console.log(updatedValues + '-' + url);
              });

            }
            $('#btn-buscar').hide();
          } else if (datos.response === 'eliminado') {
            alert('Esta venta ya se encuentra rechazada');
          } else {
            alert('Esta venta no existe.');
          }
        }
      });
    }
  });

  $('#btn-limpiar').click(function () {
    $(location).attr('href', '?view=configuracion&mode=editarResultado');
  });
});


function actualizarRegistro(i, idResultado) {

  let productoId = $("#producto" + i).val();
  let nombre = $("#nombre" + i).val();
  let apellido = $("#apellido" + i).val();
  let cedula = $("#cedula" + i).val();
  let sexo = $("#genero" + i).val();
  let nacimiento = $("#d_nacimiento" + i).val();
  let hab = $("#telf_hab" + i).val();
  let cel = $("#telf_cel" + i).val();
  let correo = $("#correo" + i).val();
  let venta = $("#saleDate" + i).val();

  let url = `?view=configuracion&mode=actualiza`
          + `&productoId=${productoId}`
          + `&id=${idResultado}`
          + `&nombre=${encodeURIComponent(nombre)}`
          + `&apellido=${encodeURIComponent(apellido)}`
          + `&cedula=${encodeURIComponent(cedula)}`
          + `&sexo=${sexo}`
          + `&nacimiento=${encodeURIComponent(nacimiento)}`
          + `&hab=${encodeURIComponent(hab)}`
          + `&cel=${encodeURIComponent(cel)}`
          + `&correo=${encodeURIComponent(correo)}`
          + `&venta=${encodeURIComponent(venta)}`;

  window.location.href = url;
}


