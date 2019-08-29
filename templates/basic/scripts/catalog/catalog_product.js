$(function () {

	var init = function () {

		$('.rating_item').rating({
			fx: 'full',
			stars: 5,
			image: '/uploads/rating/rating_stars.png',
			readOnly: true,
		});

		window.Ev.catalog_product = {

			runCounter: function (timeCount) {

				//console.log('......');

				var y = function (t) {
					$(DOM.parent + ' .data-counter-h').html(t.h);
					$(DOM.parent + ' .data-counter-m').html(t.m);
					$(DOM.parent + ' .data-counter-s').html(t.s);
				};

				y(FNC._timer_convert(timeCount));

				var tSi = setInterval(function () {
					timeCount--;
					if (timeCount < 1) {
						clearInterval(tSi);
						y({h: '--', m: '--', s: '--'});
					} else {
						y(FNC._timer_convert(timeCount));
					}
				}, 1000);

			},

			rating: function (this_item) {
				$(this_item).rating({
					fx: 'full',
					stars: 5,
					titles: ['голос', 'голоса', 'голосов'],
					minimal: 1,
					image: '/uploads/rating/rating_stars.png',
					click: function (a) {
						$(this_item).append('<input type="hidden" name="set_rating" value="' + a + '" /> ');
					},
				});
			},

			setReview: {

				getLSListReviews: function () {
					var saveReviewsList = [];
					if (localStorage.getItem('Reviews')) {
						var items = localStorage.getItem('Reviews');
						var saveReviewsList = $.parseJSON(items);
					}

					var listReviews = [];
					if (saveReviewsList) {

						for (var kSML in saveReviewsList) {
							var vSML = saveReviewsList[kSML];

							var addList = {
								code: vSML.code,
								prod_id: vSML.PID,
							};

							listReviews.push(addList);
						}
					}

					return listReviews;
				},

				setLSReview: function (code, PID) {

					var items = this.getLSListReviews();
					var math = false;

					if (items.length > 0) {
						for (var key in items) {
							var val = items [key];
							if (val.code == code) {
								math = true;
							}
						}
					}
					if (math !== true) {
						items.push({
							code: code,
							prod_id: PID,
						});
						localStorage.setItem('Reviews', LS.fnc._convert_value(items));
					}

				},

				reviewVisibled: function () {
					var list = this.getLSListReviews();

					console.log(list);

					$('#reviwsList > div').map(function (a, e) {
						var code_item = $(e).attr('data-review-code');
						for (var key in list) {
							var val = list [key];
							if (val.code == code_item) {
								$(e).removeClass('hidden');
							}
						}
					});

				},

				before: function (data) {

					var err = 0;
					for (var key in data) {
						var val = data [key];

						if (val.name === 'name') {
							if (val.value.length < 3) {
								err++;
								$(DOM.parent + ' form#form_setReview input[name="' + val.name + '"]').css('border', '1px solid red');
								setTimeout(function () {
									$(DOM.parent + ' form#form_setReview input[name="' + val.name + '"]').css('border', 'auto');
								}, 5000);
							}
						}

						if (val.name === 'author') {
							if (val.value.length < 3) {
								err++;
								$(DOM.parent + ' form#form_setReview input[name="' + val.name + '"]').css('border', '1px solid red');
								setTimeout(function () {
									$(DOM.parent + ' form#form_setReview input[name="' + val.name + '"]').css('border', 'auto');
								}, 5000);
							}
						}

						if (val.name === 'description') {
							if (val.value.length < 0) {
								err++;
								$(DOM.parent + ' form#form_setReview textarea[name="' + val.name + '"]').css('border', '1px solid red');
								setTimeout(function () {
									$(DOM.parent + ' form#form_setReview textarea[name="' + val.name + '"]').css('border', 'auto');
								}, 5000);
							}
						}

					}

					return ( err < 1 ) ? true : false;

				},

				success: function (response) {
					FNC.alert(( response.err > 0 ) ? 'error' : 'success', response.mess);
					if (response.err < 1) {
						$(DOM.parent + ' form#form_setReview')[0].reset();
						$(DOM.parent + ' #modal_new_otziv').modal('hide');

						Ev.catalog_product.setReview.setLSReview(response.code, response.PID);

						setTimeout(function () {
							window.location.reload();
						}, 1100);

					}
				},

			},

		};

		Ev.catalog_product.setReview.reviewVisibled();

	};

	var e = setInterval(function () {
		if (window.CORE) {
			clearInterval(e);
			init();
		}
	}, 10);

	// Зум изображения при наведении
	$('.zoom-img').imagezoomsl({
		zoomrange: [1.2, 1.2],
		zoomstart: 1.2,
		loopspeedanimate: 5,
		cursorshadeborder: '1px solid black',
		magnifiereffectanimate: 'slideIn'
	});

});
