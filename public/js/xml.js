$(document).ready(function () {
	$('#btn-download-bancamiga').click(function () {
		var file = $('#file_bancamiga').val();
		$.ajax({
			url: "public/archivos/bancamiga/" + file,
			type: "GET",
			dataType: 'binary',
			success: function (result) {
				var url = URL.createObjectURL(result);
				var a = $('<a />', {
					'href': url,
					'download': file,
					'text': "click"
				}).hide().appendTo("body")[0].click();
				setTimeout(function () {
					URL.revokeObjectURL(url);
				}, 10000);
			}
		});
	});

	$('#btn-download-bancaribe').click(function () {
		var file = $('#file_bancaribe').val();
		$.ajax({
			url: "public/archivos/bancaribe/" + file,
			type: "GET",
			dataType: 'binary',
			success: function (result) {
				var url = URL.createObjectURL(result);
				var a = $('<a />', {
					'href': url,
					'download': file,
					'text': "click"
				}).hide().appendTo("body")[0].click();
				setTimeout(function () {
					URL.revokeObjectURL(url);
				}, 10000);
			}
		});
	});

	$('#btn-download-bancoactivo').click(function () {
		var file = $('#file_bancoactivo').val();
		$.ajax({
			url: "public/archivos/bancoactivo/" + file,
			type: "GET",
			dataType: 'binary',
			success: function (result) {
				var url = URL.createObjectURL(result);
				var a = $('<a />', {
					'href': url,
					'download': file,
					'text': "click"
				}).hide().appendTo("body")[0].click();
				setTimeout(function () {
					URL.revokeObjectURL(url);
				}, 10000);
			}
		});
	});
})