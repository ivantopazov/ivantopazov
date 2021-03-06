$(function(){
	
	var init = function(){
		
		window.Ev.login_reg = {
			
			form_before : function( data ){
				var err = 0;
				
				if( FNC.in_array( 'email', data, 'name', false ) ){
					var email = FNC.in_array( 'email', data, 'name', true ); 
					if( !FNC.validate( 'email', email.value ) ){
						$( DOM.parent + ' form#REG-FIZ-LIC input[name="email"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#REG-FIZ-LIC input[name="email"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( FNC.in_array( 'password', data, 'name', false ) ){
					var pass = FNC.in_array( 'password', data, 'name', true );
					if( !FNC.validate( 'password', pass.value ) ){
						$( DOM.parent + ' form#REG-FIZ-LIC input[name="password"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#REG-FIZ-LIC input[name="password"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				
				if( FNC.in_array( 'name', data, 'name', false ) ){
					var name = FNC.in_array( 'name', data, 'name', true );
					if( name.value.length < 3 ){
						$( DOM.parent + ' form#REG-FIZ-LIC input[name="name"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#REG-FIZ-LIC input[name="name"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( FNC.in_array( 'captcha', data, 'name', false ) ){
					var captcha = FNC.in_array( 'captcha', data, 'name', true );
					if( !FNC.validate( 'integer', captcha.value ) ){
						$( DOM.parent + ' form#REG-FIZ-LIC input[name="captcha"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#REG-FIZ-LIC input[name="captcha"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( err < 1 ){
					$( DOM.parent + ' form#REG-FIZ-LIC button[type="submit"]').prop('disabled', 'true');
					$( DOM.parent + ' form#REG-FIZ-LIC button[type="submit"]').text( 'Идет отправка...' );
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
				$( DOM.parent + ' form#REG-FIZ-LIC button[type="submit"]').removeAttr('disabled');
				$( DOM.parent + ' form#REG-FIZ-LIC button[type="submit"]').text( 'Регистрация' );
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