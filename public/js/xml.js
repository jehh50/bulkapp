$(document).ready(function() {
	$('#btn-download').click(function(){
		$('#file').val();
		$.ajax({
	      url: "public/archivos/bancamiga/" + $('#file').val(),
	      type: "GET",
	      dataType: 'binary',
	      success: function(result) {
	        var url = URL.createObjectURL(result);
	        var a = $('<a />', {
	          'href': url,
	          'download': $('#file').val(),
	          'text': "click"
	        }).hide().appendTo("body")[0].click();
	        setTimeout(function() {
	          URL.revokeObjectURL(url);
	        }, 10000);
	      }
	    });
	});
})