$(function(){
	
	var init = function(){
		
		window.Ev.login = {
			
			form_before : function( data ){
				var err = 0;
				
				if( FNC.in_array( 'login', data, 'name', false ) ){
					var login = FNC.in_array( 'login', data, 'name', true ); 
					if( login.value.length < 3 ){
						$( DOM.parent + ' form#AUTH input[name="login"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#AUTH input[name="login"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( FNC.in_array( 'password', data, 'name', false ) ){
					var pass = FNC.in_array( 'password', data, 'name', true );
					if( !FNC.validate( 'password', pass.value ) ){
						$( DOM.parent + ' form#AUTH input[name="password"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#AUTH input[name="password"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				
				if( FNC.in_array( 'captcha', data, 'name', false ) ){
					var captcha = FNC.in_array( 'captcha', data, 'name', true );
					if( !FNC.validate( 'integer', captcha.value ) ){
						$( DOM.parent + ' form#AUTH input[name="captcha"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#AUTH input[name="captcha"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( err < 1 ){
					$( DOM.parent + ' form#AUTH button[type="submit"]').prop('disabled', 'true');
					$( DOM.parent + ' form#AUTH button[type="submit"]').text( 'Идет отправка...' );
				}
			
				return ( err < 1 ) ? true : false;
			},
			
			form_success : function( response ){
				if( response.err < 1 ){
					FNC.alert('success', response.mess );
                    setTimeout(function(){
                        window.location.reload();
                    }, 3000);
				}else{
					FNC.alert('error', response.mess );
				}
				$( DOM.parent + ' form#AUTH button[type="submit"]').removeAttr('disabled');
				$( DOM.parent + ' form#AUTH button[type="submit"]').text( 'Войти' );
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