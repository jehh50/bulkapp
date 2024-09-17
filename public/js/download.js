$(document).ready(function () {

    $('#fecha_h').change(function () {
        d = ($('#fecha_d').val());
        h = ($('#fecha_h').val());
        if (h < d) {
            alert('La fecha "HASTA" es menor a la fecha "DESDE". Por favor verifique');
        }
    });

    $("#btn-download").click(function () {
        console.log('Incoming');
        console.log('Id=>'+$('#serv').val());
        $.ajax({
            type: 'POST',
            url: '?view=reportes&mode=download&type=ventas',
            dataType: "json",
            data: { from: $("#from").val(), to: $("#to").val(), servicio: $("#serv").val() },
            success: function (datos) {
                if (datos.response == 'true') {
                    console.log('Descargando...');
                }
                else {
                    alert('Error al procesar la descarga');
                }
            }
        })
    });
})