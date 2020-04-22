var count = 0;
var current_str = 1; // Первую строку не учитываем
var err = 0;
var success = 1; // Первая строка всегда ок
var double = 0;
var postavchik = '';

$('#get').click(function () {
	$.ajax({
		type: 'post',
		url: '/admin/parser/' + parser + '/count',
		data: 1,
		dataType: 'json',
		success: function (c) {
			count = c.count;
			$('#count').html(count);
		},
	});
});

$('#upload').click(function () {
	if (count === 0) {
		alert('Вначале нажмите "Извлечь данные"!');
		return false;
    }
	var $postavchikInput = $('#postavchikInput');
	if ($postavchikInput.length > 0 && $postavchikInput.val().trim().length === 0) {
		alert('Укажите код поставщика!');
		return false;
	}
	postavchik = $postavchikInput.length ? $.trim($postavchikInput.val()) : '';
	var interval = setInterval(function () {
		if (current_str === count - 1 || count === 0) clearInterval(interval);
		if (count === 0) return false;

		$.ajax({
			type: 'post',
			url: '/admin/parser/' + parser + '/parse',
			data: {
				str: current_str,
				postavchik: postavchik,
			},
			dataType: 'json',
			success: function (c) {
				if (c.err == 0) $('#success').html(++success);
				else $('#err').html(++err);

				if (c.double > 0) $('#double').html(++double);
			},
		});

		current_str++;
	}, 100);
});
