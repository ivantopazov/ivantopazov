$(function(){
	
	var init = function(){
		
		window.Ev.login_new_pass = {
			
			form_before : function( data ){
				var err = 0;
				
				if( FNC.in_array( 'email', data, 'name', false ) ){
					var email = FNC.in_array( 'email', data, 'name', true ); 
					if( !FNC.validate( 'email', email.value ) ){
						$( DOM.parent + ' form#RESTORER input[name="email"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#RESTORER input[name="email"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( FNC.in_array( 'pass1', data, 'name', false ) ){
					var pass = FNC.in_array( 'pass1', data, 'name', true );
					if( !FNC.validate( 'password', pass.value ) ){
						$( DOM.parent + ' form#RESTORER input[name="pass1"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#RESTORER input[name="pass1"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
                
				if( FNC.in_array( 'pass2', data, 'name', false ) ){
					var pass = FNC.in_array( 'pass2', data, 'name', true );
					if( !FNC.validate( 'password', pass.value ) ){
						$( DOM.parent + ' form#RESTORER input[name="pass2"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#RESTORER input[name="pass2"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				
				
				if( err < 1 ){
					$( DOM.parent + ' form#RESTORER button[type="submit"]').prop('disabled', 'true');
					$( DOM.parent + ' form#RESTORER button[type="submit"]').text( 'Идет отправка...' );
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
				$( DOM.parent + ' form#RESTORER button[type="submit"]').removeAttr('disabled');
				$( DOM.parent + ' form#RESTORER button[type="submit"]').text( 'Регистрация' );
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