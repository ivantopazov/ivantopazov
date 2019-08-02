$(function(){
	
	var init = function(){
		
		window.Ev.login_reqovery = {
			
			form_before : function( data ){
				var err = 0;
				
				if( FNC.in_array( 'email', data, 'name', false ) ){
					var email = FNC.in_array( 'email', data, 'name', true ); 
					if( !FNC.validate( 'email', email.value ) ){
						$( DOM.parent + ' form#REQOVERY input[name="email"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#REQOVERY input[name="email"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
                
				if( FNC.in_array( 'captcha', data, 'name', false ) ){
					var captcha = FNC.in_array( 'captcha', data, 'name', true );
					if( !FNC.validate( 'integer', captcha.value ) ){
						$( DOM.parent + ' form#REQOVERY input[name="captcha"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#REQOVERY input[name="captcha"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( err < 1 ){
					$( DOM.parent + ' form#REQOVERY button[type="submit"]').prop('disabled', 'true');
					$( DOM.parent + ' form#REQOVERY button[type="submit"]').text( 'Идет отправка...' );
				}
			
				return ( err < 1 ) ? true : false;
			},
			
			form_success : function( response ){
				if( response.err < 1 ){
					FNC.alert('success', response.mess );
                    setTimeout(function(){
                        window.location.href = '/login';
                    }, 3000);
				}else{
					FNC.alert('error', response.mess );
				}
				$( DOM.parent + ' form#REQOVERY button[type="submit"]').removeAttr('disabled');
				$( DOM.parent + ' form#REQOVERY button[type="submit"]').text( 'Регистрация' );
			}
			
		};
		
		
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});