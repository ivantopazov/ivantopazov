$(function () {

	/*
	var parseItem = function(){
			console.log("Парсинг товара");

			$.ajax({
				type: "post",
				url: "/parser/parseProductSokolovtm",
				dataType : 'json',
				success: function( e ){
					if( e.err < 1 ){
						parseItem();
					} else {
						console.log("Ошибка нет товара / Парсинг завершён");
							parseItem();
					}
				}
			});
		};
		$('#goooo').click(function (){
			parseItem();
		});

	*/

	var init = function () {

		MEM.CSV = {
			arrCsv: [],
			method: 'noadd',
			heads: [],
			labels: [
				{name: 'Артикул', key: 'articul'},
				{name: 'Вид изделия', key: 'title'},
				{name: 'Серия', key: 'cat'},
				{name: 'Размер', key: 'size'},
				{name: 'Хар-ка', key: 'optionLabel'},
				{name: 'Металл', key: 'seo_title'},
				{name: 'Проба', key: 'proba'},
				{name: 'Цвет металла', key: 'seo_desc'},
				{name: 'Гарнитурность', key: 'seo_keys'},
				{name: 'Кол-во', key: 'qty'},
				{name: 'Вес', key: 'weight'},
				{name: 'Цена', key: 'price'},
				{name: 'Фото', key: 'photo'},
			],
			extract: [],
		};

		window.Ev.parser_koborovsky = {

			query: function () {
				var textCsv = $('textarea[name="csv_text"]').val();
				var objStr = textCsv.split('\n');
				MEM.CSV.arrCsv = [];
				for (var key in objStr) {
					var val = objStr[key];
					MEM.CSV.arrCsv.push(val.split(';'));
				}
				this.setLabels();
			},

			setLabels: function () {

				var textCsv = $('textarea[name="csv_text"]').val();

				if (MEM.CSV.arrCsv.length > 0) {

					MEM.CSV.heads = MEM.CSV.arrCsv[0];

					var l = MEM.CSV.arrCsv.length;

					TPL.GET_TPL('pages/admin/parser/kaborovsky/setLabels', {
						lenght: l,
						list: MEM.CSV.arrCsv[0],
						labels: MEM.CSV.labels,
					}, function (e) {
						$(DOM.parent + ' #ev-setLabels').html(e);
					});

				}

			},

			setScenary: function () {

				var arr = [];

				$(DOM.parent + ' #ev-listLabels > div[itemid]').map(function (a, e) {
					var selVal = $(e).find('select > option:selected').val();
					if (selVal !== 'false') {
						arr.push({
							i: $(e).attr('itemid'),
							v: selVal,
						});
					}
				});

				MEM.CSV.arrCsv.splice(0, 1);

				var arr2 = [];
				for (var key in MEM.CSV.arrCsv) {
					var val = MEM.CSV.arrCsv[key];

					if (val.length > 0) {
						var add = {};
						for (var ak in arr) {
							var av = arr[ak];
							add[av.v] = val[av.i];
						}
						arr2.push(add);
					}
				}

				var arr3 = [];
				var arrUpd = [];
				for (var key in arr2) {
					var val = arr2[key];
					if (val['articul']) {
						arrUpd.push(val);
					}
				}

				MEM.CSV.extract = arrUpd;
				//console.log( ' EXTRACT !!!' );
				this.run_import();

			},

			run_import: function () {

				var send = {};
				send.package_size = 100;
				send.all_size = MEM.CSV.extract.length;
				send.package_steps = (parseInt(send.all_size / send.package_size) + 1);
				send.active_step = 1;
				send.package_arrays = [];
				send.package_success = [];
				send.package_errors = [];
				send.interval = {
					int: false,
					status: true,
				};

				$(DOM.parent + ' #pack_pr').html('Прогресс: <b>0 / ' + send.package_arrays.length + '</b>');

				var status_sender = function () {
					$(DOM.parent + ' #pack_pr').html('Прогресс: <b>' + ((send.active_step > send.package_steps) ? send.package_steps : send.active_step) + ' / ' + send.package_steps + '</b>');
					$(DOM.parent + ' #pack_succ').html('Успешно: <b>' + ((send.package_success.length > send.package_steps) ? send.package_steps : send.package_success.length) + '</b>');
					$(DOM.parent + ' #pack_err').html('Пакетов с ошибкой: <b>' + ((send.package_errors.length > send.package_steps) ? send.package_steps : send.package_errors.length) + '</b>');
				};

				var new_pack = [];
				for (var key in MEM.CSV.extract) {
					var val = MEM.CSV.extract[key];
					new_pack.push(val);
					if (new_pack.length == send.package_size || send.all_size == (parseInt(key) + 1)) {
						send.package_arrays.push(new_pack);
						new_pack = [];
					}
				}

				var setClear = 1;
				send.interval.intv = setInterval(function () {
					if (send.interval.status) {
						send.interval.status = false;

						if (send.package_arrays.length > 0) {
							$.ajax({
								type: 'post',
								url: '/admin/parser/kaborovsky/parseKaborovsky',
								data: {
									clear: setClear,
									method: MEM.CSV.method,
									pack: send.package_arrays[0],
								},
								dataType: 'json',
								success: function (stat) {
									if (stat.err < 1) {
										setClear++;
										console.log('success');
										send.package_success.push(send.package_arrays[0]);
										send.package_arrays.splice(0, 1);
										send.active_step++;
										status_sender();
										send.interval.status = true;
									} else {
										console.log('success / error');
										send.interval.status = true;
									}
								},
								error: function (jqXHR, textStatus, errorThrown) {
									console.log('error, try again');
									send.interval.status = true;
								},
							});

						} else {
							send.active_step++;
							status_sender();
							if (send.interval.intv) clearInterval(send.interval.intv);
						}
					}
				}, 100);

			},

		};

	};

	var e = setInterval(function () {
		if (window.CORE) {
			clearInterval(e);
			init();
		}
	}, 10);

});