$(document).ready(function () {
  $('#d_efectivo').hide();
  $('#d_noefectivo').hide();
  $('#dPaymentPlan').hide();
  $('#dAmount').hide();
  $('#dPaymentDate').hide();
  $('#dFullName').hide();
  $('#dRelationship').hide();
  $('#dObservaciones').hide();
  $('#formTitle').hide();

  $('#contacto').change(function () {
    if ($('#contacto').val() == '1') { // Contacto efectivo
      $('#d_efectivo').show();
      $('#d_subContacto').show();
      $('#d_noefectivo').hide();
      $('#noefectivo').val("");
      $('#efectivo').val("");

      $('#subContacto').change(function () {
        if ($('#subContacto').val() == '1' || $('#subContacto').val() == '10') {
          $('#d_noefectivo').hide();
          $('#noefectivo').val("");
          $('#formTitle').show();
          $('#dPaymentPlan').show();
            $('#paymentPlan').attr('required','required');
          $('#dAmount').show();
            $('#amount').attr('required','required');
          $('#dPaymentDate').show();
            $('#paymentDate').attr('required','required');
          $('#dFullName').hide();
            $('#fullName').removeAttr('required').val("");
          $('#dRelationship').hide();
            $('#relationship').removeAttr('required').val("")
          $('#dObservaciones').show();;
        }
        else if($('#subContacto').val() == '20' || $('#subContacto').val() == '21'){
          $('#d_noefectivo').hide();
          $('#noefectivo').val("");
          $('#formTitle').show();
          $('#dPaymentPlan').show();
          $('#paymentPlan').attr('required','required');
          $('#dAmount').show();
          $('#amount').attr('required','required');
          $('#dPaymentDate').show();
          $('#paymentDate').attr('required','required');
          $('#dFullName').show();
          $('#fullName').attr('required','required');
          $('#dRelationship').show();
          $('#relationship').attr('required','required');
          $('#dObservaciones').show();
        }
        else {
          $('#d_noefectivo').hide();
          $('#noefectivo').val("");
          $('#formTitle').show();
          $('#dPaymentPlan').hide();
          $('#paymentPlan').removeAttr('required').val("");
          $('#amount').removeAttr('required').val("");
          $('#paymentDate').removeAttr('required').val("");
          $('#fullName').removeAttr('required').val("");
          $('#relationship').removeAttr('required').val("");
          $('#dAmount').hide();
          $('#dPaymentDate').hide();
          $('#dFullName').hide();
          $('#dRelationship').hide();
          $('#dObservaciones').show();
        }
      })
    }
    else { //Contacto no efectivo
      $('#d_noefectivo').show();
      $('#d_subContacto').hide();
      $('#subContacto').val();
      $('#d_efectivo').hide();
      $('#formTitle').hide();
      $('#dPaymentPlan').hide();
      $('#dAmount').hide();
      $('#dPaymentDate').hide();
      $('#dFullName').hide();
      $('#dRelationship').hide();
      $('#dObservaciones').hide();
      $('#efectivo').val("");
      $('#paymentPlan').val("");
      $('#amount').val("");
      $('#paymentDate').val("");
      $('#fullName').val("");
      $('#relationship').val("");
      $('#observaciones').val("");
    }
  });

  $("#efectivo").change(function(){
    $('#subContacto').empty();
    $.ajax({
      type:'POST',
      url:'?view=formulario&mode=subContacto',
      dataType: "json",
      data:{efectivo_id: $('#efectivo').val()},
      success:function(datos){
        if(datos.response == 'true'){
          $('#subContacto').append('<option value="">Seleccione...</option>');
            subContacto = String(datos.subContacto);
            var res = subContacto.split("|");
            // var obj = "";
            // var obj_a = "";
            for (var i = 0; i < res.length - 1; i++) {
              var res_1 = res[i].split(",");
              $('#subContacto').append('<option value="' + res_1[0] + '">' + res_1[1] + '</option>')
            }
        }
        else{
          alert('No entro');
        }
      }
    })
  });

});
