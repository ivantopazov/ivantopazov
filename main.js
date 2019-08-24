window.speedCart = [];
window.MAIN = {

    setEvents : function(){
        $('#cart').off('change');
        $('#cart').on('change','input[type="text"].qtyChange', function(){
            console.log($(this).attr('itemid'),  $(this).val(), 'update 1 1 ');
            CART.updateCount( $(this).attr('itemid'),  $(this).val(), MAIN.cartPages );
        });
    },

    updateHeaderInfo : function(){
        CART.list(function( response ){
            MAIN.updateView( response );
        });
    },

    messagesDeliver : function(){
        CART.list(function( response ){
            var summaCop = []; var count = 0;
            for (var key in response) {
                var val = response [key];
                var summaCop = Number( summaCop ) + ( val.qty * Number( val.price ));
                var count = Number( count + val.qty );
            }
            var summaRUB = Number( summaCop / 100 );
            /*if( summaCop > 500000 ){
                var t = '';
                t += 'Вы получаете бесплатную доставку';
                $('#DeliverInfo').html( t );
                $('#DeliverInfo').removeClass('hidden');
            }else{
                var t = '';
                t += 'Получите бесплатную доставку при заказе от 5000 рублей! ';
                t += 'Сумма вашей корзины: ' + summaRUB + ' руб.';
                $('#DeliverInfo').html( t );
                $('#DeliverInfo').removeClass('hidden');
            }*/ // Здесь было сообщение о минимальной сумме заказа для бесплатной доставки
        });
    },

    // Обновить информацию в шапке корзины
    updateView : function( response ){
        var summaCop = []; var count = 0;
        for (var key in response) {
            var val = response [key];
            var summaCop = Number( summaCop ) + ( val.qty * Number( val.price ));
            var count = Number( count + val.qty );
        }
        var summaRUB = Number( summaCop / 100 );
        $('.cartSumma').html( count );

        this.messagesDeliver();
    },

    //Выполнить после обавления товара в корзину
    addCartCallback : function ( response ){
        MAIN.updateView( response );
        $('#modal_cart_addet').modal();
    },

    speedAddCart : function ( item ){
        window.speedCart = [];
        window.speedCart.push( item );
        console.log('-<<<-', window.speedCart, item );

    },

    // CreditAddCart : function ( item ){
    //     window.creditCart = [];
    //         creditCart.push( item );
    // },

    // Загрузка позиций корзины ( страница - корзина )
    cartPages : function(){
        CART.list(function( response ){
            MAIN.updateView( response );
            MAIN.erectCartList( response );
            MAIN.setEvents();
        });
    },

    // Формирование списка позиций ( страница - корзина )
    erectCartList : function ( LIST ){

        TPL.GET_TPL('pages/cart/items', { items: LIST }, function( t ){
            $('.cartBlock .cartList').html( t );
        });

        var itog = 0;
        for ( var key in LIST ) {
            var val = LIST[key];
            itog += ( ( val.qty * val.price  ) / 100 );
        }
        $('#itog').html( itog + ' руб.' );

        if( LIST.length > 0 ){
           $('.cartBlock').removeClass('hidden');
           $('#order_info').removeClass('hidden');
           $('#emptyCart').addClass('hidden');
        }else{
           $('.cartBlock').addClass('hidden');
           $('#order_info').addClass('hidden');
           $('#emptyCart').removeClass('hidden');
        }

        //MAIN.cartList();
        this.messagesDeliver();
    },

    // ОФОРМЛЕНИЕ КОРЗИНЫ
    FORM : {

        requare : function( data, callback ){

            var cartArray = CART.list( false );
            var success = true;
            var postData = {};

            if( cartArray.length > 0  ){
                var postData = {
                    type : 'Обычная покупка',
                    cart : CART.list( false ),
                    info : data
                };
            }else{
                success = false;
            }
            callback( success, postData );

        },

        before : function( data ){

            var err = 0;
            var saveMenu = false;

            for (var key in data.info) {
                var val = data.info [key];

                if( val.name === 'fio' ){
                     if( !FNC.validate('string_cylric_plus', val.value ) ){
                        $( DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="fio"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }

                if( val.name === 'phone' ){
                    if( val.value.length < 1  ){
                        $( DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="phone"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }

                if( val.name === 'adress' ){
                    if( val.value.length < 1  ){
                        $( DOM.parent + ' textarea[name="adress"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' textarea[name="adress"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }

            }

            CART.list(function( response ){
              var summaCop = []; var count = 0;
              for (var key in response) {
                var val = response [key];
                var summaCop = Number( summaCop ) + ( val.qty * Number( val.price ));
                var count = Number( count + val.qty );
              }
              var summaRUB = Number( summaCop / 100 );
              /*if( summaCop < 700000 ){
                  err++;
              }*/ // Здесь стоял лимит на минимальную сумму заказа: 7к рублей в копейках
            });

            if( err < 1 ){
                $( DOM.parent + ' #order_info button[type="submit"]').prop('disabled', true);
            }

            //console.log( 'xx', MAIN.sendSuccess() );

            //return false;
            return ( err < 1 ) ? true : false;

        },

        success : function( json ){
            if( json.err < 1 ){
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

                CART.removeAll(function(){
                   console.log('thanks');
                   window.location.replace('/cart/thanks');
                });
            }else{
                alert('При заполнении формы заказы, была допущена ошибка.');
                $( DOM.parent + ' #order_info button[type="submit"]').removeAttr('disabled');
            }
        }

    },

    // ОФОРМЛЕНИЕ КОРЗИНЫ
    SpeedFORM : {

        requare : function( data, callback ){

            var cartArray = window.speedCart;
            var success = true;
            var postData = {};
            if( window.speedCart.length > 0  ){
                var postData = {
                    type : 'Быстрая покупка',
                    cart : window.speedCart,
                    info : data
                };
            }else{
                success = false;
            }

            callback( success, postData );

        },

        before : function( data ){
            var err = 0;
            var saveMenu = false;
            for (var key in data.info) {
                var val = data.info [key];
                if( val.name === 'fio' ){
                     if( !FNC.validate('string_cylric_plus', val.value ) ){
                        $( DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="fio"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
                if( val.name === 'phone' ){
                    if( FNC.validate( 'phone', val.value ) === false ){
                        $( DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="phone"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
                if( val.name === 'adress' ){
                    if( val.value.length < 1  ){
                        $( DOM.parent + ' textarea[name="adress"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' textarea[name="adress"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
            }

            return ( err < 1 ) ? true : false;

        },

        success : function( json ){
            if( json.err < 1 ){
                window.speedCart = [];
                $('#modal_speedOrder').modal('hide');
                FNC.alert('success', 'Ваш заказ был успешно отправлен.');
            }else{
                alert('При заполнении формы заказы, была допущена ошибка.');
            }
        }

    },

    // ОФОРМЛЕНИЕ КОРЗИНЫ
    CreditFORM : {

        requare : function( data, callback ){

            var cartArray = window.creditCart;
            var success = true;
            var postData = {};
            if( cartArray.length > 0  ){
                var postData = {
                    type : 'Покупка в кредит',
                    cart : window.creditCart,
                    info : data
                };
            }else{
                success = false;
            }
            callback( success, postData );

        },

        before : function( data ){
            var err = 0;
            var saveMenu = false;
            for (var key in data.info) {
                var val = data.info [key];
                if( val.name === 'fio' ){
                     if( !FNC.validate('string_cylric_plus', val.value ) ){
                        $( DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="fio"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
                if( val.name === 'phone' ){
                    if( FNC.validate( 'phone', val.value ) === false ){
                        $( DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="phone"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
                if( val.name === 'adress' ){
                    if( val.value.length < 1  ){
                        $( DOM.parent + ' textarea[name="adress"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' textarea[name="adress"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
            }
            return ( err < 1 ) ? true : false;

        },

        success : function( json ){
            if( json.err < 1 ){
                window.creditCart = [];
                $('#modal_creditOrder').modal('hide');
                FNC.alert('success', 'Ваша заявка на кредит была успешно отправлена.');
            }else{
                alert('При заполнении формы заказы, была допущена ошибка.');
            }
        }

    },

    // ОФОРМЛЕНИЕ КОРЗИНЫ
    callBackFORM : {

        before : function( data ){
            var err = 0;
            var saveMenu = false;
            data.forEach (function(val) {
                if( val.name === 'fio' ){
                    if( !FNC.validate('string_cylric_plus', val.value.trim() ) ){
                        $( DOM.parent + ' input[name="fio"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="fio"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
                if( val.name === 'phone' ){
                    if( !FNC.validate( 'phone', val.value.trim() ) || val.value.trim().length < 10 ){
                        $( DOM.parent + ' input[name="phone"]').css('outline', '1px solid red');
                        setTimeout(function(){
                             $( DOM.parent + ' input[name="phone"]').css('outline', 'none');
                        },5000);
                        err++;
                    }
                }
            });
            return ( err < 1 ) ? true : false;

        },

        success : function( json ){
            if( json.err < 1 ){
                window.creditCart = [];
                $('#modal_callback').modal('hide');
                FNC.alert('success', 'Ваша заявка на обратный звонок была успешно отправлена.');
            }else{
                alert('При заполнении формы заявки, была допущена ошибка.');
            }
        }

    }

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
