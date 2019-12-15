window.speedCart = [];
window.MAIN = {

	setEvents: function () {
		$('#cart').off('change');
		$('#cart').on('change', 'input[type="text"].qtyChange', function () {
			// console.log($(this).attr('itemid'), $(this).val(), 'update 1 1 ');
			CART.updateCount($(this).attr('itemid'), $(this).val(), MAIN.cartPages);
		});
	},

	updateHeaderInfo: function () {
		CART.list(function (response) {
			MAIN.updateView(response);
		});
	},

	messagesDeliver: function () {
		CART.list(function (response) {
			var summaCop = [];
			var count = 0;
			for (var key in response) {
				var val = response [key];
				var summaCop = Number(summaCop) + (val.qty * Number(val.price));
				var count = Number(count + val.qty);
			}
			var summaRUB = Number(summaCop / 100);
			if (summaCop > 700000) {
				var t = '';
				t += 'Вы получаете бесплатную доставку';
				$('#DeliverInfo').html(t);
				$('#DeliverInfo').removeClass('hidden');
			} else {
				var t = '';
				t += 'Получите бесплатную доставку при заказе от 7000 рублей с учетом всех скидок на изделия. ';
				t += 'Сумма вашей корзины: ' + summaRUB + ' руб.';
				$('#DeliverInfo').html(t);
				$('#DeliverInfo').removeClass('hidden');
			}
		});
	},

	// Обновить информацию в шапке корзины
	updateView: function (response) {
		var summaCop = 0;
		var count = 0;
		for (var key in response) {
			var val = response [key];
			summaCop = Number(summaCop) + (val.qty * Number(val.price));
			count = Number(count + val.qty);
		}
		var summaRUB = Number(summaCop / 100);
		$('.cartSumma').html(count);

		this.messagesDeliver();
	},

	//Выполнить после обавления товара в корзину
	addCartCallback: function (response) {
		MAIN.updateView(response);
		$('#modal_cart_addet').modal();
	},

	speedAddCart: function (item) {
		window.speedCart = [];
		window.speedCart.push(item);
		// console.log('-<<<-', window.speedCart, item);

	},

	// CreditAddCart : function ( item ){
	//     window.creditCart = [];
	//         creditCart.push( item );
	// },

	// Загрузка позиций корзины ( страница - корзина )
	cartPages: function () {
		CART.list(function (response) {
			MAIN.updateView(response);
			MAIN.erectCartList(response);
			MAIN.setEvents();
		});
		CART.promocode(function (response) {
			MAIN.setPromocodeInfo(response);
		});
	},

	// Загрузка позиций корзины с предварительным обновлением цен
	cartPagesWithRefresh: function () {
		var promocode = CART.promocode();
		CART.list(function (response) {
			$.ajax({
				type: 'post',
				url: '/cart/refresh',
				data: {
					cart: response,
					promocodeCode: promocode.code,
				},
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						CART.update(data.prices, data.promocode, MAIN.cartPages);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					FNC.errorConnect(errorThrown);
				},
			});
		});
	},

	// Формирование списка позиций ( страница - корзина )
	erectCartList: function (LIST) {
		var itog = 0;
		for (var key in LIST) {
			var val = LIST[key];
			itog += parseInt(((val.qty * val.price) / 100));

			LIST[key]['pr'] = parseInt((LIST[key]['qty'] * LIST[key]['price']) / 100);
		}
		$('#itog').html(itog + ' руб.');

		TPL.GET_TPL('pages/cart/items', {items: LIST}, function (t) {
			$('.cartBlock .cartList').html(t);
		});

		if (LIST.length > 0) {
			$('.cartBlock').removeClass('hidden');
			$('#order_info').removeClass('hidden');
			$('#emptyCart').addClass('hidden');
		} else {
			$('.cartBlock').addClass('hidden');
			$('#order_info').addClass('hidden');
			$('#emptyCart').removeClass('hidden');
		}

		//MAIN.cartList();
		this.messagesDeliver();
	},

	usePromocode: function () {
		var code = $.trim($('#promocode').val());
		if (code) {
			$.ajax({
				type: 'post',
				url: '/cart/use_promocode',
				data: {
					code: code,
					cartTotal: parseInt($('#itog').html()),
				},
				dataType: 'json',
				success: function (data) {
					if (data.success && data.promocode && data.promocode.code) {
						CART.setPromocode(data.promocode);
						MAIN.setPromocodeInfo(data.promocode);

						$('#modal_promocode_success').modal();
					} else {
						CART.setPromocode({});
						MAIN.setPromocodeInfo({});
						$('#promocodeError').html(data.error || 'Произошла ошибка, попробуйте позднее.');
						$('#modal_promocode_failed').modal();
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					FNC.errorConnect(errorThrown);
					$('#promocodeError').html('Произошла ошибка, попробуйте позднее.');
					$('#modal_promocode_failed').modal();
				},
			});
		}
	},

	removePromocode: function () {
		CART.setPromocode({});
		MAIN.setPromocodeInfo({});
		$('#modal_promocode_removed').modal();
	},

	setPromocodeInfo: function (promocode) {
		if (promocode && promocode.code) {
			var cartTotal = parseInt($('#itog').html());
			cartTotalWithPromocode = +promocode.percent ?
				Math.round(cartTotal * (100 - promocode.percent) / 100) :
				cartTotal - promocode.amount;
			var promocodeValue = +promocode.percent ? promocode.percent + '%' :
				promocode.amount + ' р.';

			$('#itogWithPromocode').html(cartTotalWithPromocode + ' руб.');
			$('#itog').addClass('oldPrice');

			$('#promocode').val(promocode.code);
			$('.promocodeCode').html(promocode.code);
			$('.promocodeValue').html(promocodeValue);
			$('#promocodeInfo').removeClass('hidden');
			$('#promocodeRemove').removeClass('hidden');
		} else {
			$('#itogWithPromocode').html('');
			$('#itog').removeClass('oldPrice');

			$('#promocode').val('');
			$('#promocodeInfo').addClass('hidden');
			$('#promocodeRemove').addClass('hidden');
		}
	},

	// ОФОРМЛЕНИЕ КОРЗИНЫ
	FORM: {

		requare: function (data, callback) {

			var cartArray = CART.list();
			var promocode = CART.promocode();
			var success = true;
			var postData = {};

			if (cartArray.length > 0) {
				var postData = {
					type: 'Обычная покупка',
					cart: cartArray,
					promocode: promocode,
					info: data,
				};
			} else {
				success = false;
			}
			callback(success, postData);

		},

		before: function (data) {

			var err = 0;
			var saveMenu = false;

			for (var key in data.info) {
				var val = data.info [key];

				if (val.name === 'fio') {
					if (!FNC.validate('string_cylric_plus', val.value.trim())) {
						$(DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="fio"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}

				if (val.name === 'phone') {
					if (val.value.trim().length < 10) {
						$(DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="phone"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}

				if (val.name === 'email') {
					if (val.value.trim().length < 1) {
						$(DOM.parent + ' input[name="email"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="email"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}

				if (val.name === 'times') {
					if (val.value.trim().length < 1) {
						$(DOM.parent + ' input[name="times"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="times"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}

				if (val.name === 'city') {
					if (val.value.trim().length < 1) {
						$(DOM.parent + ' input[name="city"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="city"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}

				if (val.name === 'address') {
					if (val.value.trim().length < 1) {
						$(DOM.parent + ' input[name="address"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="address"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}

			}

			CART.list(function (response) {
				var summaCop = [];
				var count = 0;
				for (var key in response) {
					var val = response [key];
					var summaCop = Number(summaCop) + (val.qty * Number(val.price));
					var count = Number(count + val.qty);
				}
				var summaRUB = Number(summaCop / 100);
				/*if( summaCop < 700000 ){
				 err++;
				 }*/ // Здесь стоял лимит на минимальную сумму заказа: 7к рублей в копейках
			});

			if (err < 1) {
				$(DOM.parent + ' #order_info button[type="submit"]').prop('disabled', true);
			}

			return (err < 1) ? true : false;

		},

		success: function (json) {
			if (json.err < 1) {
				//yaCounter41905564.reachGoal('new_order');

				//CART.list(function( response ){

				/*var pp = {
				 "ecommerce": {
				 "purchase": {
				 "actionField": {
				 "id" : json.order_id,
				 "goal_id" : 27016149,
				 "revenue" : json.revenue
				 },
				 "products": ( function(response){
				 var setItems = [];
				 for ( var key in response ) {
				 var val = response[key];
				 var add = {
				 id : Number(val.id),
				 name : val.title,
				 price : Number( ( val.price / 100 ).toFixed(2)),
				 quantity :  Number( val.qty )
				 };
				 setItems.push( add );
				 }
				 return setItems;
				 })( response )
				 }
				 }
				 };

				 console.log( '230', response, pp );

				 dataLayer.push( pp );
				 */

				//});

				CART.removeAll(function () {
					window.location.replace('/cart/thanks');
				});
			} else {
				alert('При заполнении формы заказы, была допущена ошибка.');
				$(DOM.parent + ' #order_info button[type="submit"]').removeAttr('disabled');
			}
		},

	},

	// ОФОРМЛЕНИЕ КОРЗИНЫ
	SpeedFORM: {

		requare: function (data, callback) {

			var cartArray = window.speedCart;
			var success = true;
			var postData = {};
			if (window.speedCart.length > 0) {
				var postData = {
					type: 'Быстрая покупка',
					cart: window.speedCart,
					info: data,
				};
			} else {
				success = false;
			}

			callback(success, postData);

		},

		before: function (data) {
			var err = 0;
			var saveMenu = false;
			for (var key in data.info) {
				var val = data.info [key];
				if (val.name === 'fio') {
					if (!FNC.validate('string_cylric_plus', val.value)) {
						$(DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="fio"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
				if (val.name === 'phone') {
					if (FNC.validate('phone', val.value) === false) {
						$(DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="phone"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
				if (val.name === 'adress') {
					if (val.value.length < 1) {
						$(DOM.parent + ' textarea[name="adress"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' textarea[name="adress"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
			}

			return (err < 1) ? true : false;

		},

		success: function (json) {
			if (json.err < 1) {
				window.speedCart = [];
				$('#modal_speedOrder').modal('hide');
				FNC.alert('success', 'Ваш заказ был успешно отправлен.');
			} else {
				alert('При заполнении формы заказы, была допущена ошибка.');
			}
		},

	},

	// ОФОРМЛЕНИЕ КОРЗИНЫ
	CreditFORM: {

		requare: function (data, callback) {

			var cartArray = window.creditCart;
			var success = true;
			var postData = {};
			if (cartArray.length > 0) {
				var postData = {
					type: 'Покупка в кредит',
					cart: window.creditCart,
					info: data,
				};
			} else {
				success = false;
			}
			callback(success, postData);

		},

		before: function (data) {
			var err = 0;
			var saveMenu = false;
			for (var key in data.info) {
				var val = data.info [key];
				if (val.name === 'fio') {
					if (!FNC.validate('string_cylric_plus', val.value)) {
						$(DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="fio"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
				if (val.name === 'phone') {
					if (FNC.validate('phone', val.value) === false) {
						$(DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="phone"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
				if (val.name === 'adress') {
					if (val.value.length < 1) {
						$(DOM.parent + ' textarea[name="adress"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' textarea[name="adress"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
			}
			return (err < 1) ? true : false;

		},

		success: function (json) {
			if (json.err < 1) {
				window.creditCart = [];
				$('#modal_creditOrder').modal('hide');
				FNC.alert('success', 'Ваша заявка на кредит была успешно отправлена.');
			} else {
				alert('При заполнении формы заказы, была допущена ошибка.');
			}
		},

	},

	// Обратный звонок
	callBackFORM: {

		before: function (data) {
			var err = 0;
			var saveMenu = false;
			data.forEach(function (val) {
				if (val.name === 'fio') {
					if (!FNC.validate('string_cylric_plus', val.value.trim())) {
						$(DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="fio"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
				if (val.name === 'phone') {
					if (!FNC.validate('phone', val.value.trim()) || val.value.trim().length < 10) {
						$(DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
						setTimeout(function () {
							$(DOM.parent + ' input[name="phone"]').css('outline', 'none');
						}, 5000);
						err++;
					}
				}
			});
			if (err < 1) {
				FNC.set_cookie('callBackModalCount', '2');
				$('#modal_callback').modal('hide');
				return true;
			}
			return false;
		},

		success: function (json) {
			if (json.err < 1) {
				window.creditCart = [];
				// $('#modal_callback').modal('hide');
				FNC.alert('success', 'Ваша заявка на обратный звонок была успешно отправлена.');
			} else {
				alert('При заполнении формы заявки, была допущена ошибка.');
			}
		},

	},

};

/*
 scrollTopMenu : function(){
 var scrollItem = function(){
 var top = $(document).scrollTop();
 if (top < 210){
 $('#menu > div').removeClass('navbar-nav').removeClass('navbar-fixed-top');
 }else{
 $('#menu > div').addClass('navbar-nav').addClass('navbar-fixed-top');
 }
 };

 scrollItem();
 $(window).scroll(function () {
 scrollItem();
 });
 }
 */
