$(function(){
    
    window.CORE = {
        
        APP : {
            config : {
                GET : ''
            },
            storage: {
                TPL : {
                    tpl_list : []
                }
            },
            Ev : {},
			DOM : {
				parent : ' body '
			},
        },
        
        SYSTEM : {
            
            AUTOLOAD : function(){
                
                EV.link();
                EV.unload();
                EV.form_action();
                
            },
            
            TPL : {
                
                GET_TPL : function( _NAME_ , _DATA_, _RESPONSE_ ){
                    
                    var _name_ = _NAME_ || false;
                    var _data_ = _DATA_ || false;
                    var RESPONSE = _RESPONSE_ || false;

                    if( _name_ ){
                        var patt = FNC.in_array( _name_ , MEM.TPL.tpl_list, 'name', true);
                        if( patt === false ){

                             $.ajax({
                                type: "post",
                                url : '/helper/get_tpl_data/',
                                data : { tpl_name : _name_ },
                                async: true,
                                context : { name: _name_ },
                                success: function(response){
                                    var save = {
                                        name : _name_,
                                        modify : FNC._get_date(),
                                        rend : response
                                    };
                                    MEM.TPL.tpl_list.push(save);
                                    
                                    var html = TPL.TPL_TRANSFORM( response, _data_ );
                                    if(RESPONSE) RESPONSE(html);
                                    
                                },
								error: function(jqXHR, textStatus, errorThrown){
									FNC.alert( 'error', ' Ошибка сервера. ' );
								}
                            });

                        }else{
                            var html = TPL.TPL_TRANSFORM( patt.rend, _data_ );
                            if(RESPONSE) RESPONSE(html);
                        }
                    }

                },
                
                TPL_TRANSFORM : function ( tpl_name , array_data, completted ){
                    
                    var string = tpl_name || false;
                    var data = array_data || {};
                    var completted = completted || false;
                    
                    var return_html = '';
                    
                    if( string.length > 0 ){
                        var template = twig({
                            data: string
                        });
                        var return_html = template.render(data);
                    }
                    if( completted ){
                        completted(return_html);
                    }else{
                        return return_html;
                    }
                }
                
            },
                        
            EVENTS : {


                link : function (){
                    
                    $(document).off('click','*[click]');
                    $(document).on('click','*[click]',function( e ){
                        e.preventDefault();
                        var name = $(this).attr('click');
                        eval('window.'+ name);
                    });
                    $(document).on('click','a[href^="#"]',function( e ){
                        e.preventDefault(); 
                    });
					
                },

                unload : function(){
                    setTimeout( function(){
						$(DOM.parent + ' *[unload]').map(function( a, b ){
							eval('window.' + $(b).attr('unload')); 
						});
					} ,1000);
                    //$(document).off('click','[unload]');
                    //$(document).on('click','[unload]',function(){
                     //   eval('window.'+$(this).attr('unload'));
                    //});
                },
                
                form_action : function (){
                    
					$( DOM.parent + '*[type="submit"]').prop('disabled', false);
					
                    $( DOM.parent ).off('submit');
                    $( DOM.parent ).on('submit', 'form[action!="#"][static!=true]', function( e ){
                        e.preventDefault();
                        
                        var link = $(this).attr('action') || false;
                        var staticus = $(this).attr('static') || false;
                        var before = $(this).attr('before') || false;
                        var requare = $(this).attr('requare') || false;
                        var requare_error = ( $(this).attr('requare_error') ) ? ( $(this).attr('requare_error') === 'false' ) ? false : $(this).attr('requare_error') : 'При заполнении формы была допущена ошибка.';
                        var success = $(this).attr('success') || false;
                        var data_type = $(this).attr('data_type') || 'html';
                        var method = ( $(this).attr('method') || 'get' ).toLowerCase();
                        var data = (function( m, e ){
                            return ( m === 'get' )? e.serialize() : e.serializeArray();
                        })( method, $(this) );
                        
						var form_next = function( data ){
							if( method === 'get' ) {
								FNC._set_url( link + '?' + data );
								setTimeout(function(){
								  ROUTE.page_get();
								});
							}else{
								
								$.ajax({
									type: "post",
									url: link,
									data: data,
									dataType : data_type,
									context : { name:success, before:before, data:data },
									beforeSend : function(){
										if( this.before === false ){
											return true;
										}else{
											return (eval( 'window.' + this.before ))( this.data );
										}
									}, 
									success: function( response ){
										if( this.name !== false ){
											(eval( 'window.' + this.name ))( response );
										}else{
											if( requare_error === false ){
                                                if( data_type === 'json' ){
                                                    FNC.alert( (( response.err < 1 ) ? 'success' : 'error'), response.mess );
                                                }else{
                                                    FNC.alert( 'success', 'Данные формы были успешно отправлены.' );
                                                }
                                                
                                            }else{
												FNC.alert( 'error', requare_error );
											}
										}
									},
									error: function(jqXHR, textStatus, errorThrown){
										FNC.alert( 'error', 'Ошибка сервера' );
									}
								});
							}
                        };
						
                        if( requare !== false ){
                            (eval( 'window.' + requare ))( data, function( status, data_response ){
                                if( status ){
                                    form_next( data_response );
                                }else{
                                    FNC.alert('error', requare_error);
                                }
                            });
                        }else{
                            form_next( data );
                        }
                        return false;
                    
                    });
                    
                }

            },
            
            LOCAL_STORAGE : {
                
                run : {
                    
                    getListTpl : function (){
                        var response = [];
                        if( ! localStorage.getItem('tpl') )  localStorage.setItem('tpl', '[]');
                        var tpl = localStorage.getItem('tpl'); 
                        var arr_tpl = $.parseJSON(tpl);
                        if( arr_tpl.length > 0 ){
                            for (var key in arr_tpl) {
                                var val = arr_tpl[key];
                                var x = ( LS.fnc._type_of(val) === 'object')?val:$.parseJSON(val);
                                response.push(x);
                            } 
                        }
                        return response;
                    },
                    
                    get_list_tpl : function (){
                        
                        if( ! localStorage.getItem('tpl') )  localStorage.setItem('tpl', '[]');
                        var tpl = localStorage.getItem('tpl'); 
                        var arr_tpl = $.parseJSON(tpl);
                        if( arr_tpl.length > 0 ){
                            for (var key in arr_tpl) {
                                var val = arr_tpl[key];
                                var x = ( LS.fnc._type_of(val) === 'object')?val:$.parseJSON(val);
                                MEM.TPL.tpl_list[key] = x;
                            } 
                            
                            MEM.TPL.extractLocStor_status = true;
                        }else{
                            MEM.TPL.extractLocStor_status = true;
                        }
                        
                    },
                    set_tpl : function ( name, html, mod ){
                        var modif = mod || FNC._get_date();
                        if( ! localStorage.getItem('tpl') )  localStorage.setItem('tpl', '[]');
                        var tpl = localStorage.getItem('tpl'); 
                        var arr_tpl = $.parseJSON(tpl);
                        
                        var save = {
                            name : name,
                            modify : modif,
                            rend : html
                        };
                        
                        arr_tpl.push(LS.fnc._convert_value(save));
                        localStorage.setItem('tpl', LS.fnc._convert_value(arr_tpl));
                        
                    }
                },
                
                fnc : {
                    _convert_value : function ( value ){
                        var to = LS.fnc._type_of(value);
                        if(to){
                            return LS.fnc._value_convert( value, to );
                        }else{
                            return false;
                        }
                    },
                    _type_of : function ( math ){
                        var _typeof = 'string';
                        if(typeof math === 'function') _typeof = 'function';
                        if(typeof math === 'object') _typeof = 'object';
                        if(typeof math === 'number') _typeof = 'number';
                        return _typeof;
                    },
                    _value_convert : function ( _value , _typeof ){
                        var response = _value;
                            if(_typeof === 'function') response = $.toJSON(_value);
                            if(_typeof === 'object') response = $.toJSON(_value);
                            if(_typeof === 'number') response = _value;
                        return response;
                    }
                } 
                
            },
            
            CART : { 
                
                header_update : function(){
                    
                    var items = {
                        tovarov : 0,
                        prices : 0,
                        prices_rub : 0,
                        prices_cop : 0,
                        counts : 0
                    };
                    
                    var list = this.list( false );
                    if( list.length > 0 ){
                        for ( var key in list ) {
                            var val = list[key];           
                            items.counts = items.counts + val.qty;
                            items.prices_cop = ( items.prices_cop + ( val.price * val.qty ));
                            items.tovarov++;
                        }
                        items.prices_rub = ( items.prices_cop / 100 );
                    }
                    
                    if( items.tovarov > 0 ){
                        $('#count_cart').text( items.tovarov + ' товар(ов)');
                        $('#countPrices').text( items.prices_rub + ' руб.');
                    }else{
                        $('#count_cart').text('Товаров нет.');
                        $('#countPrices').text( '0 руб.');
                    }
                    
                    if( items.prices_cop > 700000 ){
                        var t = '';
                        t += 'Вы получаете бесплатную доставку';
                        $('#messageCartSumma').html( t );
                        $('#messageCartSumma').removeClass('hidden');
                    }else{
                        var t = '';
                        t += 'Получите бесплатную доставку при заказе от 7000 рублей! <br/>';
                        t += 'Сумма вашей корзины: ' + items.prices_rub + ' руб.';
                        $('#messageCartSumma').html( t );
                        $('#messageCartSumma').removeClass('hidden');
                    }
                    
                    
                    console.log( 'Содержимое корзины', items, list );
                    
                },
                
                list : function( callFnc ){
                    
                    var callFnc = callFnc || false;
                    var response = [];
                    
                    if( localStorage.getItem('cart') === 'undefined' )  localStorage.setItem('cart', '[]'); 
                    if( !localStorage.getItem('cart') )  localStorage.setItem('cart', '[]');  
                    
                    var _cart = localStorage.getItem('cart'); 
                    var cart = $.parseJSON( _cart );
                    
                    if( cart.length > 0 ){
                        for (var key in cart) {
                            var val = cart[key];
                            var x = ( LS.fnc._type_of(val) === 'object')?val:$.parseJSON(val);
                            response.push(x);
                        } 
                    }
                    
                    if( callFnc ){
                        callFnc( response );
                    }else{
                        return response;
                    }
                    
                },
                
                /* 
                    Варианты атрибута CLICK
                    ------------------
                    Пример: CART.add({id:1, title:'Чёрные брюки', price:13200, qty:{type:'fixed', input:'#count_qty', value:false }})
                    
                    Параметры:************
                    id : ИД товара
                    title : Название товара
                    price : Цена в копейках
                    qty : Свои параметры ( есть тонкости ) [ Если параметра qty несуществует - ставится еденица ]
                        type : 'fixed' - Фиксированное значение / 'plus' - Добавить кол-во к текущему
                        input : Значение из Поля с указанным атрибутом которое необходимо установить или добавить в зависимости от type ( выше )
                        value : Значение которое необходимо установить или добавить в зависимости от type ( выше ) - задаётся вручную в параметрах
                    orig : {} /// пользов. значения    
                        
                */
                add : function( AddItem, callFnc ){
                    
                    var AddItem = AddItem || false;
                    var callFnc = callFnc || false;
                    
                    if( AddItem != false ){
                        
                        var list = this.list( false );
                        
                        var ID = ( AddItem['id'] ) ? AddItem.id : false;
                        var TITLE = ( AddItem['title'] ) ? AddItem.title : false;
                        var PRICE = ( AddItem['price'] ) ? AddItem.price : false;
                        var QTY = ( AddItem['qty'] ) ? AddItem.qty : false;
                        var ORIG = ( AddItem['orig'] ) ? AddItem.orig : false;
                        
                        var math = 0;
                        if( list.length > 0 ){
                            for ( var key in list ) {
                                var val = list[key];                            
                                if( val.id.toString() === ID.toString() ){
                                    
                                    var issetQTY = false;
                                    if( QTY.type === 'fixed' ){
                                        if( QTY.value != false ){
                                             list[key].qty = QTY.value;
                                        }else{
                                            list[key].qty = ( $( QTY.input ) ) ? Number( $( QTY.input ).val()) : 1;
                                        }
                                        issetQTY = true;
                                    }
                                    
                                    if( QTY.type === 'plus' ){
                                        if( QTY.value != false ){
                                             list[key].qty = val.qty + QTY.value;
                                        }else{
                                            list[key].qty = ( $( QTY.input ) ) ? val.qty + Number( $( QTY.input ).val()) : val.qty + ( QTY.value ) ? QTY.value : 1;
                                        }
                                        issetQTY = true;
                                    }
                                    
                                    if( issetQTY === false ){
                                        list[key].qty = 1;
                                    }
                                    
                                    math=1;
                                }
                            }
                        }
                        
                        if( math < 1 ){
                            
                            var addQty = 1;
                            
                            if( QTY.type === 'fixed' ){
                                if( QTY.value != false ){
                                    addQty = QTY.value;
                                }else{
                                    addQty = ( $( QTY.input ) ) ? $( QTY.input ).val() : 1;
                                }
                            }
                            
                            list.push({
                                id : ID,
                                title : TITLE,
                                qty : addQty,
                                price : PRICE,
                                orig : ORIG
                            });
                        }
                        
                        
                        
                        localStorage.setItem('cart', LS.fnc._convert_value( list ));                    
                        this.header_update();
                        
                        if( callFnc !== false ) callFnc( list );
                        
                    }
                },
                
                remove : function( ID, callFnc ){
                    
                    var callFnc = callFnc || false;
                    var list = this.list( false );                    
                    var newList = [];
                    
                    if( list.length > 0 ){
                        for ( var key in list ) {
                            var val = list[key];                            
                            if( val.id.toString() !== ID.toString() ){
                                newList.push( val );
                            }
                        }                        
                        localStorage.setItem('cart', LS.fnc._convert_value( newList ));                    
                        if( callFnc !== false ) callFnc( newList );                        
                    }      
                    
                    this.header_update();
                    
                    if( callFnc ){
                        callFnc( newList );
                    }else{
                        return newList;
                    }
                    
                },
                
                updateCount : function( ID, count, callFnc ){
                    
                    var callFnc = callFnc || false;   
                    
                    if( count < 1 ){
                        CART.remove( ID, function( list ){
                            localStorage.setItem('cart', LS.fnc._convert_value( list ));                    
                            if( callFnc !== false ){
                                callFnc( list );
                            }
                        });
                    }else{
                        
                        var list = [];
                        var list = this.list( false );
                        if( list.length > 0 ){
                            for ( var key in list ) {
                                var val = list[key];                            
                                if( val.id.toString() === ID.toString() ){
                                    list[key].qty = count;
                                }
                            }
                        }
                        
                        localStorage.setItem('cart', LS.fnc._convert_value( list ));                    
                        if( callFnc !== false ) callFnc( list );
                        
                    }     
                    
                    this.header_update();
                    
                },
                
                removeAll : function( callFnc ){
                    var callFnc = callFnc || false;     
                    var list = [];
                    localStorage.setItem('cart', LS.fnc._convert_value( list ));   
                    this.header_update();                    
                    if( callFnc ){
                        callFnc( list );
                    }else{
                        return list;
                    }
                }
            },
            
            FNC : {
				
				errorConnect : function( errorThrown ){
					$('body .dis-connected').removeClass('hidden');
					$('#dis-connected-clouse').off('click');
					$('#dis-connected-clouse').on('click', function (){
						$('body .dis-connected').addClass('hidden');
					});
				},
				
				alert : function ( type, mess, completed ){
					var completed = completed || false;
					var type = type || 'error';
					var mess = mess || 'Системная ошибка';
					
					if( type === 'success' ){
                        
                        window.toastr.options = {
                            closeButton : true,
                            debug : false,
                            progressBar : true,
                            positionClass : "toast-bottom-right",
                            onclick : true,
                            showDuration : "400",
                            hideDuration : "1000",
                            timeOut : "7000",
                            extendedTimeOut : "1000",
                            showEasing : "swing",
                            hideEasing : "linear",
                            showMethod : "fadeIn",
                            hideMethod : "fadeOut"
                        };
                        window.toastr.success( mess );
                        if( completed ) completed();
                    }
                    
                    if( type === 'error' ){
                        
                        window.toastr.options = {
                            closeButton : true,
                            debug : false,
                            progressBar : true,
                            positionClass : "toast-bottom-right",
                            onclick : true,
                            showDuration : "400",
                            hideDuration : "1000",
                            timeOut : "7000",
                            extendedTimeOut : "1000",
                            showEasing : "swing",
                            hideEasing : "linear",
                            showMethod : "fadeIn",
                            hideMethod : "fadeOut"
                        };
                        window.toastr.error( mess );
                        if( completed ) completed();
                    }
				},
				
				captcha : function ( el, fnc ){
					var fnc = fnc || false;
					$.ajax({
						type: "post",
						url: "/helper/get_captcha",
						dataType: 'text',
						success: function(data){
							if( !fnc ){
								$(el).attr('src', data);
							}else{
								fnc( data );
							}
						},
						error: function(jqXHR, textStatus, errorThrown){
							FNC.errorConnect( errorThrown );
						}
					});
				},
				
                preload : function( change ){
                    var change = change || false;
                    if( change ){
                        $(DOM.parent + ' .preload').removeClass('hidden');
                    }else{
                        $(DOM.parent + ' .preload').addClass('hidden');
                    }
                },
                
                SCRIPT : function ( event, name, url, success ){
                    
                    var EVENT = event || false;
                    var NAME = name || false;
                    var URL = url || false;
                    
                    var complette = success || false;
                    
                    if( EVENT ) {
                        
                        switch (EVENT) {
                          case 'get':
                            if( !FNC.SCRIPT('query', NAME) ){
                                $.ajaxSetup({cache: false});
                                /*$.getScript(URL, function(){
                                    MEM.ScriptsControlUnloads.push(NAME);
                                    if(complette){
                                        complette( true );
                                    }else{
                                        return true;
                                    }
                                });*/
								
								$.ajax({
									url: URL,
									cache: false,
									dataType: "script",
									success: function(){
										MEM.ScriptsControlUnloads.push(NAME);
										if(complette){
											complette( true );
										}else{
											return true;
										}
									},
									error: function(jqXHR, textStatus, errorThrown){
										FNC.errorConnect( errorThrown );
									}
								});
								
								
								
                            }else{
                                if(complette){
                                    complette( true );
                                }else{
                                    return true;
                                }
                            }
                            break
                          case 'query':
                            return FNC.in_array( NAME, MEM.ScriptsControlUnloads, false, false );
                            break
                          case 'add':
                            if( !FNC.SCRIPT('query', NAME) ){
                                MEM.ScriptsControlUnloads.push(NAME);
                            }
                            return true;
                            break
                          case 'del':                          
                            if( FNC.SCRIPT('query', NAME) ){
                                FNC.remove_array(NAME, MEM.ScriptsControlUnloads, false, function( e ){
                                    MEM.ScriptsControlUnloads = e;
                                    return true;
                                });
                            }else{
                                return true;
                            }
                            break
                          default:
                            return true;
                        }
                    }else{
                        return false;
                    }
                    
                },

                category_tree : function ( LIST ){

                    return (function( LIST ){
                        var lev = 1;
                        var category = [];

                        var HelperTree = function( id, arr, lev){
                            var ch = [];
                            lev++;
                            for (var k in arr) {
                                 var v = arr[k];
                                 if( v.parent_category  ===  id ){
                                    ch.push({
                                        id : v.id,
                                        filter_id : v.filter_id,
                                        name_cat : v.name_cat,
                                        image : v.image,
                                        view : v.view,
                                        weight: v.weight,
                                        seo_title : v.seo_title,
                                        seo_content : v.seo_content,
                                        seo_key : v.seo_key,
                                        seo_desc : v.seo_desc,
                                        lavel : lev,
                                        lavel_string : FNC.category_tree_symbol( '+',  lev),
                                        category : HelperTree(v.id, arr, lev)
                                    });

                                    FNC.procedure_array(v.id.toString(), MEM.categories, 'id', function(e){
                                        e.lavel = lev;
                                        e.lavel_string = FNC.category_tree_symbol( '+',  lev);
                                    });

                                }
                            }
                            return ch;
                        };

                        for (var key in LIST) {
                            var val = LIST[key];
                            lev = 1;
                            if( Number(val.parent_category) < 1 ){

                                var add = {
                                    id : val.id,
                                    name : val.name_cat,
                                    name_cat : val.name_cat,
                                    image : val.image,
                                    view : val.view,
                                    weight: val.weight,
                                    lavel : lev,
                                    lavel_string : FNC.category_tree_symbol( '+',  lev),
                                    category : HelperTree(val.id, LIST, lev)
                                };
                                category.push(add);

                                FNC.procedure_array(val.id.toString(), MEM.categories, 'id', function(e){
                                    e.lavel = lev;
                                    e.lavel_string = FNC.category_tree_symbol( '+',  lev);
                                });

                            }
                        }
                        return category;
                    })(LIST);
                },

                category_tree_symbol : function ( simb, level ){

                    var simb = simb || '-';
                    var level = level || 0;
                    var result = '';  
                    for( var i=0; i < level; i++ ){
                        result += simb;
                    }
                    return ' ' + result + ' ';

                },

                _set_title : function( string ){
                    $('title').html( string );
                },

                _set_url : function( string ){
                    history.pushState(null, null, window.location.protocol +'//' + window.location.host + string );
                },
                
                _step : function( variabled, completted ){
                    var a = setInterval(function(){
                        if( variabled ){
                            clearInterval(a);
                            completted();
                        }
                    }, 10);
                },
                
                _load_data : function ( get_list, complitte ){
                     
                    var GET_LIST = get_list || [];
                    var final_data = [];
                    var completted = complitte || false;
                    
                    var loads_start = 0;
                    var all_loads = GET_LIST.length;
                    
                    for (var key in GET_LIST ) {
                        var getItem = GET_LIST[key];
						
						var save_param = ( getItem['save'] ) ? getItem.save : false;
						
						var math_LoadDataSave = false;
						if( save_param ){
							var get_item_data = FNC.in_array( getItem.name, MEM.LoadDataSave, 'name', true );
							console.log( getItem.name , ' brws ->' , get_item_data );
							if( get_item_data ){
								final_data.push({
                                    name: get_item_data.name,
                                    response : get_item_data.response
                                });
								math_LoadDataSave = true;
                                loads_start++;
							}
						}
						
						if( ! math_LoadDataSave ){
							
							$.ajax({
								type: "post",
								url: getItem.url,
								dataType: getItem.type || 'json',
								context : getItem,
								data : getItem.data || {},
								success: function( response ){
									var add = {
										name: this.name,
										response : response
									};
									final_data.push( add );
									MEM.LoadDataSave.push( add );
									loads_start++;
								},
								error: function(jqXHR, textStatus, errorThrown){
									FNC.errorConnect( errorThrown );
								}
							});
						}
                    }
                    
                    var a = setInterval(function(){
                      if( all_loads == loads_start ){
						  console.log( ' completed ', all_loads, loads_start );
                        clearInterval(a);
                        completted( final_data );
                      }
                    }, 10);
                },
                
                _load_list_tpl : function( tpl_list, data, complitte ){
  
                    var TPL_LIST = tpl_list || [];
                    var DATA = data || false;
                    var completted = complitte || false;
                    
                    var basic_list = [
                      'basic/basic_one_collumn',
                      'basic/tpl_left_bar',
                      'basic/tpl_home',
                      'basic/basic_tpl_1'
                    ];
                    
                    // + переданные с контроллера
                    for (var key in TPL_LIST) {
                       if( !FNC.in_array( TPL_LIST[key], basic_list, false, false ) ){
                           basic_list.push( TPL_LIST[key] );
                       } 
                    } 
                    
                    var helpers_list = [
                        'blocks/header',
                        'blocks/menu',
                        'blocks/catalog',
                        'blocks/scroll_top',
                        'blocks/footer',
                        'blocks/head',
                        'blocks/load',
                        'blocks/breadcrumb'
                    ];
                    
                    var helperLength = helpers_list.length;
                    var helperStart = 0;
                    
                    // + переданные с обязательных
                    for (var key in helpers_list) {
                       if( !FNC.in_array( helpers_list[key], basic_list, false, false ) ){
                           basic_list.push( helpers_list[key] );
                       } 
                    } 
                    
                    var loads_start = 0;
                    var all_loads = basic_list.length;


                    for (var key in basic_list ) {
                      var tplItem = basic_list[key];
                      
                      TPL.GET_TPL( tplItem , false, function( a ){
                          /*
                          console.log( tplItem );

                                MEM.TPL.tpl_list.push({
                                    name : tplItem,
                                    rend : a
                                });  
                        */
                          
//                        if( !FNC.in_array( tplItem, MEM.TPL.tpl_list, 'name', false ) ){
//                            LS.run.set_tpl( tplItem, a);
//                            
//                            MEM.TPL.tpl_list.push({
//                                name : tplItem,
//                                rend : a
//                            });
//                        }
                        loads_start++;
                        
                      });
                    } 
                    
                    
                    
                    var a = setInterval(function(){
                      if( all_loads == loads_start ){
                        clearInterval(a);
                            
                            //console.log( MEM.TPL.tpl_list );
                            
                            var getHeader = function(){
							
								var _data = DATA.header || { 
                                    site : window.siteConfig,
                                    fconfig : window.filesConfig,
                                    time : (function( LPU ){
                                        var d = FNC._set_date( ((LPU)?LPU:1445933585), true );
                                        return d.d+'.'+d.m_+'.'+d.g;
                                    })(window.siteConfig.last_prices_update)
                                };
								
								var _tpl_name = (function( d ){
									var dTpl = FNC.in_array('blocks/header', MEM.TPL.tpl_list, 'name', true);
									if( d['tpl'] ){
										var dTpl = FNC.in_array( d.tpl , MEM.TPL.tpl_list, 'name', true);
									}
									return  (dTpl) ? dTpl.rend : '';
								})( _data );
								
								
								TPL.TPL_TRANSFORM( _tpl_name , _data , function( e ){
                                    MEM.TPL.header = e;
                                    helperStart++;
                                });
								/*
                                TPL.TPL_TRANSFORM( ( FNC.in_array('blocks/header', MEM.TPL.tpl_list, 'name', true) )['rend'] , { 
                                    site : window.siteConfig,
                                    fconfig : window.filesConfig,
                                    time : (function( LPU ){
                                        var d = FNC._set_date( ((LPU)?LPU:1445933585), true );
                                        return d.d+'.'+d.m_+'.'+d.g;
                                    })(window.siteConfig.last_prices_update)
                                }, function( e ){
                                    MEM.TPL.header = e;
                                    helperStart++;
                                });*/
                            };
                            getHeader();
                           
                            var getMenu = function (){
								var _data = DATA.menu || {};
								var _tpl_name = (function( d ){
									var dTpl = FNC.in_array('blocks/menu', MEM.TPL.tpl_list, 'name', true);
									if( d['tpl'] ){
										var dTpl = FNC.in_array( d.tpl , MEM.TPL.tpl_list, 'name', true);
									}
									return  (dTpl) ? dTpl.rend : '';
								})( _data );
								console.log( _data );
                                TPL.TPL_TRANSFORM( _tpl_name , _data , function( e ){
                                    MEM.TPL.menu = e;
                                    helperStart++;
                                });
                            };
                            getMenu();
                            
                            var getCatalog = function (){
                                TPL.TPL_TRANSFORM( ( FNC.in_array('blocks/catalog', MEM.TPL.tpl_list, 'name', true) )['rend'] , ( DATA.catalog || {} ) , function( e ){
                                    MEM.TPL.catalog = e;
                                    helperStart++;
                                });
                            };
                            getCatalog();
                            
                            var getScrollTop = function (){
                                TPL.TPL_TRANSFORM( ( FNC.in_array('blocks/scroll_top', MEM.TPL.tpl_list, 'name', true) )['rend'] , ( DATA.scroll_top || {} ) , function( e ){
                                    MEM.TPL.ScrollTop = e;
                                    helperStart++;
                                });
                            };
                            getScrollTop();
                            
                            var getFooter = function (){
                                TPL.TPL_TRANSFORM( ( FNC.in_array('blocks/footer', MEM.TPL.tpl_list, 'name', true) )['rend'] , ( DATA.footer || {} ) , function( e ){
                                    MEM.TPL.footer = e;
                                    helperStart++;
                                });
                            };
                            getFooter();
                            
                            var getHead = function (){
                                TPL.TPL_TRANSFORM( ( FNC.in_array('blocks/head', MEM.TPL.tpl_list, 'name', true) )['rend'] , ( DATA.head || {} ) , function( e ){
                                    MEM.TPL.head = e;
                                    helperStart++;
                                });
                            };
                            getHead();
                            
                            var getLoad = function (){
                                TPL.TPL_TRANSFORM( ( FNC.in_array('blocks/load', MEM.TPL.tpl_list, 'name', true) )['rend'] , ( DATA.load || {} ) , function( e ){
                                    MEM.TPL.load = e;
                                    helperStart++;
                                });
                            };
                            getLoad();
                            
                            var getBreadcrumb = function (){
                                if( DATA.breadcrumb ){
                                    
                                    //var brb = FNC.in_array('blocks/breadcrumb', MEM.TPL.tpl_list, 'name', true);
                                    //console.log( 'brb EXT ::', brb );
                                    
                                    TPL.TPL_TRANSFORM( ( FNC.in_array('blocks/breadcrumb', MEM.TPL.tpl_list, 'name', true) )['rend'] , { brb: DATA.breadcrumb } , function( e ){
                                        //console.log('itog', e );
                                        MEM.TPL.breadcrumb = e;
                                        helperStart++;
                                    });
                                    
                                    
//                                    MEM.TPL.breadcrumb = '';
//                                    helperStart++;
                                }else{
                                    MEM.TPL.breadcrumb = '';
                                    helperStart++;
                                }
                            };
                            getBreadcrumb();
                            
                            var getCompleted = function(){
                                completted({
                                    helpers : {
                                        header : MEM.TPL.header,
                                        menu : MEM.TPL.menu,
                                        catalog : MEM.TPL.catalog,
                                        scrollTop : MEM.TPL.ScrollTop,
                                        footer : MEM.TPL.footer,
                                        head : MEM.TPL.head,
                                        load : MEM.TPL.load,
                                        brb : MEM.TPL.breadcrumb
                                    },
                                    listTpl : MEM.TPL.tpl_list
                                });  
                            };
                            
                            var b = setInterval(function(){
                                if( helperLength == helperStart ){
                                    clearInterval( b );
                                    //$.getScript("/" + window.filesConfig.config_scripts_path + "/autoload.js");
                                    getCompleted();
                                }
                            });
                      }
                    }, 10);

                },
                
                _set_basic_tpl : function( tpl_list, complitte, data ){

                    var get_tpl_list = tpl_list || {};
                    var DATA = data || false;
                    var TPL_LIST = $.extend( {
                        base:'basic/basic_home', 
                        header:'basic/header',
                        menu : 'basic/menu',
                        category : 'basic/category',
                        footer : 'basic/footer'
                    }, get_tpl_list);

                    var final = complitte || false;

                    TPL.GET_TPL( TPL_LIST.base , { get: DATA }, function( a ){
                        var time = 0;
                        $( DOM._base_() ).html( a );

                        DOM._header = ' .jq-header';
                        DOM._nav = ' .jq-nav';
                        DOM._category = ' .jq-category';
                        DOM._content = ' .jq-content';
                        DOM._footer = ' .jq-footer';

                        if( TPL_LIST.header ){                      

                            var _date =(function( lpu ){
                                var d = FNC._set_date( ((lpu)?lpu:1445933585), true );
                                return d.d+'.'+d.m_+'.'+d.g;
                            })( CONFIG.site_info.last_prices_update ); 

                            TPL.GET_TPL( TPL_LIST.header , { data : CORE.APP.config, site:CONFIG.site_info , get: DATA, time:_date} , function( a ){
                                $( DOM._base_() + DOM._header ).html( a );
                                $( '.root' ).on('click', '.return_call', function () {

                                    swal({
                                        title: "Заказать обратный звонок",
                                        text: "Введите свой номер телефона",
                                        type: "input",
                                        showCancelButton: true,
                                        confirmButtonColor: "#aedef4",
                                        confirmButtonText: "Да, Заказать звонок!",
                                        closeOnConfirm: false,
                                        cancelButtonText: 'Отмена',
                                        allowEscapeKey: true,
                                        allowOutsideClick: true,
                                    }, function (number) {
                                        //console.log(number);

                                        if (FNC.validate('integer', number)) {

                                            swal.close();
                                            var send = false;

                                            $.ajax({
                                                type: "post",
                                                url: '/index.php?c=Settings&m=get_load&find=call_send',
                                                data: {call_number: number},
                                                success: function () {
                                                    send = true;
                                                }
                                            });

                                            var ssxx = setInterval(function () {
                                                if (send === true) {
                                                    clearInterval(ssxx);
                                                    swal("Отправлено!", "Наш менеджер скоро перезвонит вам, ожидайте.", "success");
                                                }
                                            }, 100);

                                        } else {
                                            swal.showInputError("Поле должно содержать только цифры.");
                                        }

                                    });
                                }); 
                                time++;
                            });
                        }else{
                            $( DOM._base_() + DOM._header ).empty();
                            time++;
                        }


                        if( TPL_LIST.menu ){
                            TPL.GET_TPL( TPL_LIST.menu , { cats: MEM.category, active:CONFIG.active_controller, get: DATA } , function( a ){
                                $( DOM._base_() + DOM._nav ).html( a );
                                time++;
                            });
                        }else{
                            $( DOM._base_() + DOM._nav ).empty();
                            time++;
                        }

                        if( TPL_LIST.category ){
                            TPL.GET_TPL( TPL_LIST.category , { cats:MEM.category, get: DATA } , function( a ){
                                $( DOM._base_() + DOM._category ).html( a );
                                FNC.SCRIPT('get', 'metisMenu', '/scripts/plugins/metisMenu/jquery.metisMenu.js', function(){  
                                //$.getScript('/scripts/plugins/metisMenu/jquery.metisMenu.js', function(){
                                    $( DOM._base_() + ' #side-menu' ).metisMenu();
                                    time++;
                                });
                            });
                        }else{
                            $( DOM._base_() + DOM._category ).empty();
                            time++;
                        }

                        if( TPL_LIST.footer ){
                            TPL.GET_TPL( TPL_LIST.footer , { get: DATA } , function( a ){
                                $( DOM._footer ).html( a );
                                $( DOM._footer ).removeClass('hidden');
                                time++;
                            });
                        }else{
                            $( DOM._footer ).empty();
                            time++;
                        }

                        $( ".preload" ).addClass('hidden');

                        var int_id = setInterval(function(){
                            if(time > 3){
                                clearInterval(int_id);
                                MEM.basic_tpl = true;
                                if(final) final();
                            }
                        }, 100);

                    });   

                },

                _get_server_time : function ( b, compltt ){
                   var complette = compltt || false;
                   if( CONFIG.server_time === false ){

                        $.ajax({
                            type: "post",
                            url: "/index.php?c=Settings&m=get_load&find=server_config",
                            success: function(data){
                                var response = $.parseJSON(data);

                                CORE.APP.config.id = response.id;
                                CORE.APP.config.server_time = response.time;
                                CORE.APP.config.public = response.pub;
                                CORE.APP.config.site_info = response.config;

                                if( CORE.APP.config.id ){
                                    LOGIN.auth( response.user );
                                }

                                setInterval(function(){
                                    CORE.APP.config.server_time++;
                                }, 1000);

                                TPL.clear_tpl(response.file_modify, function(){
                                    if (b === true)  CONFIG.server_time;
                                    if( complette ) complette( true );
                                });
                            }
                        });

                   }





                },

                _get_rand : function(){
                    var result       = '';
                    var words        = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
                    var max_position = words.length - 1;
                    for( i = 0; i < 5; ++i ) {
                        position = Math.floor ( Math.random() * max_position );
                        result = result + words.substring(position, position + 1);
                    }
                    var a = new Date().getTime();
                    var b = Math.floor(Date.now() / 1000);         

                    return result + b;
                },
                
                _get_rand_dual : function( min, max ){
                    return Math.random() * (max - min) + min;
                },

                procedure_array : function( value, array, index, complete ){

                    var response = response || false;
                    var index = index || false;

                    for(var i = 0; i < array.length; i++){

                        var variabled = (!index)?array[i]:array[i][index];
                        if(variabled === value){
                            complete(array[i]);
                        } 

                    }
                    return false;
                },

                x_price_format : function ( _number, separ, decpo, decim ){
                    var decimal = decim || 2;
                    var separator = separ || ' ';
                    var decpoint = decpo || '.';
                    var format_string = '# ';
                    var r = parseFloat( _number );
                    var exp10 = Math.pow( 10, decimal );
                    r = Math.round( r * exp10 ) / exp10;
                    rr = Number(r).toFixed(decimal).toString().split('.');
                    b = rr[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1"+separator);
                    r = ( rr[1]? b+ decpoint +rr[1]:b );
                    return format_string.replace('#', r);
                },

                /** Найти значение в ассоц массиве */ 
                in_array : function (value, array, index, response){
                    var response = response || false;
                    var index = index || false;
                    for(var i = 0; i < array.length; i++){
                        var variabled = (!index)?array[i]:array[i][index];
                        if(variabled == value){
                            if(response){
                                return array[i];
                            }else{
                                return true;
                            }
                        } 
                    }
                    return false;
                },

                // от минимального к максимальному
                sort_min_to_max : function( sort_list, item ){
                    var min = 10000*10000;
                    var max = 0;
                    var SORT = sort_list || false;
                    var ITEM = item || false;
                    var SORT_NEW = [];
                    if( SORT ){
                      for (var key in SORT) {
                        var val = SORT[key];
                        var math = ( !ITEM )?val:val[ITEM];
                        min = ( parseInt(math) < parseInt(min) )?parseInt(math):min;  
                      } 
                      for (var key in SORT) {
                        var val = SORT[key];
                        var math = ( !ITEM )?val:val[ITEM];
                        max = ( parseInt(math) > parseInt(max) )?parseInt(math):max;  
                      }
                      for ( var i = min; i <= max; i++ ){
                          for(var s = 0; s < SORT.length; s++){
                            var math = ( !ITEM )?SORT[s]:SORT[s][ITEM];
                            if(parseInt(math) === i){
                              SORT_NEW.push( SORT[s] );
                            }
                          }
                      }
                    } 
                    return SORT_NEW;
                },

                // от маскимального к минимального
                sort_max_to_min : function( sort_list, item ){
                    var min = 10000 * 10000;
                    var max = 0;
                    var SORT = sort_list || false;
                    var ITEM = item || false;
                    var SORT_NEW = [];
                    if (SORT) {
                        for (var key in SORT) {
                            var val = SORT[key];
                            var math = (!ITEM) ? val : val[ITEM];
                            min = (parseInt(math) < parseInt(min)) ? parseInt(math) : min;
                        }
                        for (var key in SORT) {
                            var val = SORT[key];
                            var math = (!ITEM) ? val : val[ITEM];
                            max = (parseInt(math) > parseInt(max)) ? parseInt(math) : max;
                        }
                        for (var i = max; i >= min; i--) {
                            for (var s = 0; s < SORT.length; s++) {
                                var math = (!ITEM) ? SORT[s] : SORT[s][ITEM];
                                if (parseInt(math) === i) {
                                    SORT_NEW.push(SORT[s]);
                                }
                            }
                        }
                    }
                    return SORT_NEW;
                },

                /** Найти значение в одномерн массиве */ 
                in_arr : function ( array , value , on_return ){
                    var result = false; 
                    var ret = on_return || false;

                    for( var key in array ){
                        if(key == value){
                            result = (ret) ? array[key] : true;
                        }
                    }

                    return result;
                },

                HelperInput : function ( validate, ellement ){

                    var el = ellement;
                    var value = $.trim(el.val());

                    if (value.length < 1) {
                        el.css('outline', '1px solid red');
                        setTimeout(function () {
                            el.css('outline', 'none');
                        }, 3000);

                        return false;
                    } else {
                        if(validate === false){
                            return value;
                        }else{
                            if (!FNC.validate(validate, value)) {

                                el.css('outline', '1px solid red');
                                setTimeout(function () {
                                    el.css('outline', 'none');
                                }, 3000);

                            } else {
                                return (value)?value:false;
                            }
                        }
                    }
                },

                /** Выполнить запрос в массив */ 
                query_array : function (value, array, index){
                    var response = response || false;
                    var ind = index || false;
                    var new_arr = [];
                    for(var i = 0; i < array.length; i++){
                        if(array[i][ind] === value){
                            new_arr.push(array[i]);
                        }
                    }
                    return new_arr;
                },

                /** Получить дату UNIX */
                _get_date : function (){
                    return Math.round(new Date().getTime()/1000.0);
                },

                /** Получить формат. Дату из UNIX */
                _set_date : function (dates, type){

                    var t = type || false;
                    var response  = new Date(parseInt(dates) * 1000);    
                    var a = response.getDate();
                    var b = response.getMonth() + 1;
                    var c = response.getFullYear();    
                    var d = response.getHours();
                    var e = response.getMinutes();
                    var f = response.getSeconds();  

                    var a = (a < 10)?'0'+a:a;
                    var b = (b < 10)?'0'+b:b;
                    var c = (c < 10)?'0'+c:c;        
                    var d = (d < 10)?'0'+d:d;
                    var e = (e < 10)?'0'+e:e;
                    var f = (f < 10)?'0'+f:f;

                    if(t){
                        return {
                            d : a.toString(),
                            m_ : b.toString(),
                            g : c.toString(),
                            h : d.toString(),
                            m : e.toString(),
                            s : f.toString()
                        };
                    }else{
                        return a+'.'+b+'.'+c+' в '+d+':'+e+':'+f;
                    }

                },

                _get_date_is_string : function ( date ){
                    var d = {
                        d: (date.slice(0,2))?date.slice(0,2):'00',
                        me: (date.slice(3,5))?date.slice(3,5):'00',
                        y: (date.slice(6,10))?date.slice(6,10):'0000',
                        h: (date.slice(11,13))?date.slice(11,13):'00',
                        mi: (date.slice(14,16))?date.slice(14,16):'00',
                        s: (date.slice(17,19))?date.slice(17,19):'00'
                    };

                    var toDate = new Date();
                    var a = toDate.getTimezoneOffset() / 60;
                    toDate.setDate(d.d);
                    toDate.setMonth(d.me - 1);
                    toDate.setFullYear(d.y);
                    toDate.setHours(d.h);
                    toDate.setMinutes(d.mi);
                    toDate.setSeconds(d.s);
                    return Math.floor(toDate.valueOf()/1000);
                },

                /** Вернуть сколько времени осталось до заданной даты */
                _timer_convert : function( countdown ) {
                    var countdown = (function (countdown){
                        var countdown = countdown || false;
                        if(countdown){
                            if(countdown > 0){
                                return countdown;
                            }else{
                                return false;
                            }
                        }else{
                            return false;
                        }
                    })(countdown);
                    if(countdown){
                        var secs = countdown % 60;
                        var countdown1 = (countdown - secs) / 60;
                        var mins = countdown1 % 60;
                        countdown1 = (countdown1 - mins) / 60;
                        var hours = countdown1 % 24;
                        var days = (countdown1 - hours) / 24;
                        return {
                            d: (days < 10)?'0'+days:days,
                            h: (hours < 10)?'0'+hours:hours,
                            m: (mins < 10)?'0'+mins:mins,
                            s: (secs < 10)?'0'+secs:secs
                        };
                    }else{
                        return false;
                    }
                },

                /** кодировать в base64 */
                b64_enc : function ( data ){

                    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
                    var o1, o2, o3, h1, h2, h3, h4, bits, i=0, enc='';
                    do { 
                            o1 = data.charCodeAt(i++);
                            o2 = data.charCodeAt(i++);
                            o3 = data.charCodeAt(i++);
                            bits = o1<<16 | o2<<8 | o3;
                            h1 = bits>>18 & 0x3f;
                            h2 = bits>>12 & 0x3f;
                            h3 = bits>>6 & 0x3f;
                            h4 = bits & 0x3f;
                            enc += b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
                    } while (i < data.length);
                    switch( data.length % 3 ){
                            case 1:
                                    enc = enc.slice(0, -2) + '==';
                            break;
                            case 2:
                                    enc = enc.slice(0, -1) + '=';
                            break;
                    }
                    
                    
                    enc.replace('+', '-');
                    enc.replace('/', '_');
                    enc.replace('=', ',');
                    
                    return enc;

                },
                 
                /** ДЭкодировать из base64 */
                b64_dec : function ( data ){
                    
                    data.replace('-', '+');
                    data.replace('_', '/');
                    data.replace(',', '=');
                    
                    
                    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
                    var o1, o2, o3, h1, h2, h3, h4, bits, i=0, enc='';
                    do {
                            h1 = b64.indexOf(data.charAt(i++));
                            h2 = b64.indexOf(data.charAt(i++));
                            h3 = b64.indexOf(data.charAt(i++));
                            h4 = b64.indexOf(data.charAt(i++));
                            bits = h1<<18 | h2<<12 | h3<<6 | h4;
                            o1 = bits>>16 & 0xff;
                            o2 = bits>>8 & 0xff;
                            o3 = bits & 0xff;
                            if (h3 == 64)	  enc += String.fromCharCode(o1);
                            else if (h4 == 64) enc += String.fromCharCode(o1, o2);
                            else			   enc += String.fromCharCode(o1, o2, o3);
                    } while (i < data.length);
                    return enc;
                },

                validate     : function ( type, string ){
                    if(type == 'email'){
                        var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
                        return pattern.test(string);
                    }
                    if(type == 'string_plus'){
                        var pattern = new RegExp(/^[a-zA-Z][a-zA-Z0-9-_]+$/ig);
                        return pattern.test(string);
                    }
                    if(type == 'string_cylric'){
                        var pattern = new RegExp(/^[а-яА-ЯёЁa-zA-Z0-9-_\s\.\,]+$/ig);
                        return pattern.test(string);
                    }
                    if(type == 'string_cylric_plus'){
                        var pattern = new RegExp(/^[а-яА-ЯёЁa-zA-Z0-9-_\s\.\,\/]+$/ig);
                        return pattern.test(string);
                    }
                    if(type == 'login'){
                        var pattern = new RegExp(/^[a-zA-Z][a-zA-Z0-9-_\.]{2,20}$/);
                        return pattern.test(string);
                    }
                    if(type == 'password'){
                        var pattern = new RegExp(/^[a-zA-Z0-9]{2,20}$/ig);
                        return pattern.test(string);
                    }
                    if(type == 'number'){
                        var pattern = new RegExp(/^\.[0-9]+$/ig);
                        return pattern.test(string);
                    }
                    if(type == 'integer'){
                        var pattern = new RegExp(/^[\d]+$/g);
                        return pattern.test(string);
                    }
                    if(type == 'phone'){
                        var pattern = new RegExp(/^([+]?[0-9\s-\(\)]{3,25})*$/i);
                        return pattern.test(string);
                    }
                    if( type == 'price' ){

                        var err = 0;
                        var pattern1 = new RegExp(/^[\d]+$/g);
                        var pattern2 = new RegExp(/^[\d]+[.][\d]+$/g);
                        var pattern3 = new RegExp(/^[\d]+[,][\d]+$/g);

                        if(!pattern1.test(string)) err++;
                        if(!pattern2.test(string)) err++;
                        if(!pattern3.test(string)) err++;

                        return (err < 3)?true:false;
                    }
                    if( type === 'http' ){
                        var pattern = new RegExp('^(?:(?:ht|f)tps?://)?(?:[\\-\\w]+:[\\-\\w]+@)?(?:[0-9a-z][\\-0-9a-z]*[0-9a-z]\\.)+[a-z]{2,6}(?::\\d{1,5})?(?:[?/\\\\#][?!^$.(){}:|=[\\]+\\-/\\\\*;&~#@,%\\wА-Яа-я]*)?');
                        return pattern.test(string);
                    }

                },

                remove_array : function( value, array, ind, comp ){

                    var complette = comp || false;
                    var response = response || false;
                    var index = ind || false;
                    var narr = [];

                    for(var i = 0; i < array.length; i++){

                        var variabled = (!index)?array[i]:array[i][index];
                        if(variabled !== value){
                            narr.push(array[i]);
                        } 

                    }
                    if(complette){
                        complette ( narr );
                    }else{
                        return narr;
                    }

                },

                GET_parse: function (query) {
                    var pars = (query != null ? query : "").replace(/&+/g, "&").split('&'),
                        par, key, val, re = /^([\w]+)\[(.*)\]/i, ra, ks, ki, i = 0,
                        params = {};
                    while ((par = pars.shift()) && (par = par.split('=', 2))) {
                        i = 0;
                        key = decodeURIComponent(par[0]);
                        val = decodeURIComponent(par[1] || "").replace(/\+/g, " ");
                        if (ra = re.exec(key)) {
                            ks = ra[1];
                            if (!(ks in params)) {
                                params[ks] = {};
                            }
                            ki = (ra[2] != "") ? ra[2] : i++;
                            params[ks][ki] = val;
                            continue;
                        }
                        params[key] = val;
                    }
                    return params;
                },

                clear_tag : function ( t ){
                    return t.replace(/<\/?[^>]+>/g,'');
                },

                set_cookie : function ( name, value, lavel ){
                    var lav = lavel || 30;
                    $.cookie(name, value, {
                        expires: parseInt(lav),
                        path: "/"
                    });
                    return true;
                },

                get_cookie : function ( name ){
                    return ($.cookie(name))?$.cookie(name):false;
                },

                remove_cookie : function ( name ){
                    $.cookie(name, null, {
                        expires: 1,
                        path: "/"
                    });
                },

                search : function ( value, arr, index, str_tag ){
                    var result_array = [];
                    var tag = str_tag || 'tred';
                    var array = ( arr.length > 0 )?arr:false;
                    var value_searh =  value || false;        
                    if( array && value_searh ){
                        var pattern = new RegExp( eval('/'+value_searh+'+/ig') );
                        for (var key in array) {
                            var val = array[key];


                            if( pattern.test(val[index]) ){
                                var math_is = val[index];
                                var regV = '/' + value_searh + '/gi';
                                if(math_is.match(eval(regV))){
                                    val[index + '_push'] = val[index].replace(eval(regV), '<'+tag+'>' + value_searh + '</'+tag+'>');
                                    result_array.push(val);
                                }
                            }

                        } 
                    }        
                    return result_array;
                },

                parseURL : function ( url ){

                    url = url || {};
                    var pattern = "^(([^:/\\?#]+):)?(//(([^:/\\?#]*)(?::([^/\\?#]*))?))?([^\\?#]*)(\\?([^#]*))?(#(.*))?$";
                    var rx = new RegExp(pattern);
                    var parts = rx.exec(url);

                    var url_ret = {};
                    url_ret.href = parts[0] || "";
                    url_ret.protocol = parts[1] || "";
                    url_ret.host = parts[4] || "";
                    url_ret.hostname = parts[5] || "";
                    url_ret.port = parts[6] || "";
                    url_ret.pathname = parts[7] || "/";
                    url_ret.search = parts[8] || "";
                    url_ret.hash = parts[10] || "";

                    return url_ret;

                }
            }
            
        }
    };
    
    window.MEM = {};
    
    
}); 

var CONFIG = CORE.APP.config;
var DOM = CORE.APP.DOM;
var Ev = CORE.APP.Ev;
var EV =    CORE.SYSTEM.EVENTS;
var FNC =   CORE.SYSTEM.FNC;
var MEM = CORE.APP.storage;
var TPL =   CORE.SYSTEM.TPL;

var CART =   CORE.SYSTEM.CART;
var LS =     CORE.SYSTEM.LOCAL_STORAGE;
        
CART.header_update();
        
CORE.SYSTEM.AUTOLOAD();