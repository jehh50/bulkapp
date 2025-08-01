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
      $('#d_noefectivo').hide();
      $('#noefectivo').val("");

      $('#efectivo').change(function () {
        console.log($('#efectivo').val());
        if ($('#efectivo').val() == '12') {
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
            $('#fullName').attr('required','required');
          $('#dRelationship').hide();
            $('#relationship').attr('required','required');
          $('#dObservaciones').show();
            $('#observaciones').attr('required','required');
        }
        else if($('#efectivo').val() == '13'){
          $('#d_noefectivo').hide();
          $('#noefectivo').val("");
          $('#formTitle').show();
          $('#dPaymentPlan').show();
          $('#dAmount').show();
          $('#dPaymentDate').show();
          $('#dFullName').show();
          $('#dRelationship').show();
          $('#dObservaciones').show();
        }
        else {
          $('#d_noefectivo').hide();
          $('#noefectivo').val("");
          $('#formTitle').show();
          $('#dPaymentPlan').hide();
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

});
