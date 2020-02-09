$(function () {

	var sendFilter = function() {
		var db_block = $(this).parents('div[data-parent]').attr('data-parent');

		setTimeout(function(){
			Ev.catalog.getFiltersParam( db_block );
		},100);
	};

	$.getScript('/addons/scripts/plugins/iCheck/icheck.min.js', function () {
		$('input.i-checks')
			.on('ifChanged', sendFilter)
			.iCheck({
				checkboxClass: 'icheckbox_square-green',
				radioClass: 'iradio_square-green',
			});
	});

	$('input.form-control').on('blur', sendFilter);

	$('.expandable').expander({
		slicePoint: 70,
		widow: 2,
		expandText: '&hellip;',
		expandPrefix: '',
		userCollapse: false,
		expandEffect: 'show',
		startExpanded: false,
	});

	var init = function () {

		window.Ev.catalog = {

			viewHide: function (blockName) {
				var block = $(DOM.parent + ' div.slideHide[data-item="' + blockName + '"]');

				var blockAttr = block.attr('data-status');

				if (blockAttr === 'hide') {
					block.addClass('auto');
					block.attr('data-status', 'show');
				} else {
					block.removeClass('auto');
					block.attr('data-status', 'hide');
				}

			},

			setOption_item: function (filter_option) {
				var filter_option = filter_option || false;
				if (filter_option !== false) {
					$(DOM.parent + ' ul[data-parent="filter-option"] span[data-filter_option]').removeClass('active');
					$(DOM.parent + ' ul[data-parent="filter-option"] span[data-filter_option="' + filter_option + '"]').addClass('active');
					setTimeout(function () {
						Ev.catalog.getFiltersParam();
					}, 100);
				}
			},

			getFiltersParam: function (db_block) {

				var db_block = db_block || 'filter-block';

				var getParam = FNC.GET_parse(FNC.parseURL(location.href).search.replace('?', ''));

				var arr = {
					// f: {},
					// s: 'pop',
					// l: 44,
					// t: '',
				};

				var filter = {};

				$(DOM.parent + ' div[data-parent="' + db_block + '"] input[type="checkbox"]:checked').map(function (a, e) {
					var parentBlock = $(e).parents('div[data-block_name]').attr('data-block_name');
					if (!filter[parentBlock]) {
						filter[parentBlock] = [];
					}
					filter[parentBlock].push($(e).attr('name'));
				});

				var priceFrom = $(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="price-ot"]').val();
				var priceTo = $(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="price-do"]').val();
				if (priceFrom || priceTo) {
					filter['price'] = [priceFrom, priceTo];
				}

				var weightFrom = $(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="weight-ot"]').val();
				var weightTo = $(DOM.parent + '  div[data-parent="' + db_block + '"] input[name="weight-do"]').val();
				if (weightFrom || weightTo) {
					filter['weight'] = [weightFrom, weightTo];
				}

				// for (var key in filter) {
				// 	var val = filter[key];
				// 	filter[key] = val.length ? val.join('|') : '';
				// }

				var t = $(DOM.parent + ' input[name="t"]').val();
				if (t) {
					arr.t = t;
				}

				var s = $(DOM.parent + ' ul[data-parent="filter-option"] span[data-filter_option].active').attr('data-filter_option');
				if (s) {
					arr.s = s;
				}

				if (getParam['l']) arr.l = getParam.l;

				var setUrl = getPathWithFilter(filter) + '?' + decodeURIComponent($.param(arr));

				FNC._set_url(setUrl);

				setTimeout(function () {
					window.location.reload();
				}, 100);

			},

		};

	};

	var getPathWithFilter = function (filter) {
		// значения фильтра в урле бывают в виде val-1-i-val2-filter1_val3-i-val4-filter2
		// то есть группы фильтров разделены '_', имя фильтра в группе идет в конце через дефис
		// значения фильтров разделены '-i-', сами значения могут содержать дефис

		var pathParts = location.pathname.split('/').filter(Boolean);
		var lastPathPart = pathParts.pop(); // последняя часть урла может содержать значения фильтра

		// парсим последнюю часть урла, чтобы понять, есть ли в ней значения фильтров
		var filtersFromUrl = lastPathPart.split('_').filter(function (pathPart) {
			pathPart = pathPart.trim();

			if (!pathPart) {
				return false;
			}

			var filterParts = pathPart.split('-');
			var filterName = filterParts.pop();

			return filter[filterName] && filterParts.length;
		});

		if (!filtersFromUrl.length) { // в урле нет значений фильтров
			pathParts.push(lastPathPart); // возвращаем последнюю часть урла
		}

		var pathNoFilters = pathParts.join('/'); // урл без фильтров

		var filtersToUrl = [];

		Object.keys(filter).forEach(function (filterKey) {
			var filterSettings = filter[filterKey];
			var filterSettingsString = '';
			if (filterKey === 'price' || filterKey === 'weight') {
				filterSettingsString = filterSettings[0] ? `from-${filterSettings[0]}` : '';
				filterSettingsString += filterSettings[1] ? (filterSettingsString ? '-' : '') + `to-${filterSettings[1]}` : '';
			} else {
				filterSettingsString = filterSettings.join('-i-');
			}
			filtersToUrl.push(`${filterSettingsString}-${filterKey}`);
		});
		return `/${pathNoFilters}/${filtersToUrl.join('_')}`;
	};

	var e = setInterval(function () {
		if (window.CORE) {
			clearInterval(e);
			init();
		}
	}, 10);

});