$(function () {

	var init;
	init = function () {

		MEM.unload_photo = [];

		window.Ev.promocodes_edit = {

			init: function () {

				$.getScript('/addons/scripts/plugins/datapicker/bootstrap-datepicker.js', function () {
					$(DOM.parent + ' .datepicker').datepicker({
						format: 'dd.mm.yyyy',
						language: 'ru',
						container: '',
					}).on('keydown paste', function (e) {
						e.preventDefault();
						return false;
					});
				});

			},

			FORM: {

				set_info: {

					before: function (data) {
						var err = 0;
						for (var key in data) {
							var val = data [key];

							if (val.name === 'date_start') {
								if (!/\d\d\.\d\d\.\d\d\d\d/.test(val.value)) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').parent().addClass('has-error');
								}
							}

							if (val.name === 'date_end') {
								if (!/\d\d\.\d\d\.\d\d\d\d/.test(val.value)) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').parent().addClass('has-error');
								}
							}

							if (val.name === 'title') {
								if (val.value.length < 1) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').parent().addClass('has-error');
								}
							}

							if (val.name === 'code') {
								if (val.value.length < 1) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').parent().addClass('has-error');
								}
							}

							if (val.name === 'amount') {
								if (!FNC.validate('integer', val.value)) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').parent().addClass('has-error');
								}
							}

							if (val.name === 'percent') {
								if (!FNC.validate('integer', val.value)) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').parent().addClass('has-error');
								}
							}

							if (val.name === 'min_order') {
								if (!FNC.validate('integer', val.value)) {
									err++;
									$(DOM.parent + ' form#blockInfo input[name="' + val.name + '"]').parent().addClass('has-error');
								}
							}

						}
						setTimeout(function () {
							$(DOM.parent + ' form#blockInfo input').parent().removeClass('has-error');
						}, 2000);

						return (err < 1) ? true : false;
					},

					success: function (response) {
						FNC.alert((response.err > 0) ? 'error' : 'success', response.mess);
						var PID = Number($(DOM.parent + ' form#blockInfo input[name="promocode_id"]').val());
						if (response.err < 1 && PID < 1) {
							$(DOM.parent + ' .tabs-container ul.nav-tabs > li').removeClass('hidden');
							$(DOM.parent + ' .tabs-container .tab-content > div.tab-pane').removeClass('hidden');
							$(DOM.parent + ' input[name="promocode_id"]').val(response.response_id);
						}
					},

				},

			},
		};

		window.Ev.promocodes_edit.init();

	};

	var e = setInterval(function () {
		if (window.CORE) {
			clearInterval(e);
			init();
		}
	}, 10);

});