$('span[data-type="accept"]').click(function () {
	var id = $(this).data('id');

	$.ajax({
		type: 'post',
		url: '/admin/reviews/home/accept',
		data: {
			id: id,
		},
		dataType: 'json',
		success: function (c) {
			if (c.err == 0) $('div[data-id="' + id + '"]').hide(200);
			else if (c.err > 0) alert('Что-то пошло не так...');
		},
	});
});

$('span[data-type="deny"]').click(function () {
	var id = $(this).data('id');

	$.ajax({
		type: 'post',
		url: '/admin/reviews/home/deny',
		data: {
			id: id,
		},
		dataType: 'json',
		success: function (c) {
			if (c.err == 0) $('div[data-id="' + id + '"]').hide(200);
			else if (c.err > 0) alert('Что-то пошло не так...');
		},
	});
});