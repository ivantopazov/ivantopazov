$(function(){
	
	var init = function(){
		
		window.Ev.form_add_traffic = {
            
            date_picer : function(){
                $('body input.date-picer').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: true,
                    forceParse: true,
                    calendarWeeks: true,
                    autoclose: true,
                    language : 'ru',
                    format: 'mm.dd.yyyy',
                });
                $('.clockpicker').clockpicker();
            },
            
            form_before : function( data ){
				var err = 0;
                
				if( FNC.in_array( 'description', data, 'name', false ) ){
					var description = FNC.in_array( 'description', data, 'name', true ); 
					if( description.value.length < 2 ){
						$( DOM.parent + ' form#RECORD_TRAFFIC textarea[name="description"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#RECORD_TRAFFIC textarea[name="description"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
                
				if( FNC.in_array( 'GROUP', data, 'name', false ) ){
					var GROUP = FNC.in_array( 'GROUP', data, 'name', true ); 
					if( GROUP.value == '0' ){
						$( DOM.parent + ' form#RECORD_TRAFFIC textarea[name="description"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#RECORD_TRAFFIC textarea[name="description"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				
				if( FNC.in_array( 'date', data, 'name', false ) ){
					var date = FNC.in_array( 'date', data, 'name', true ); 
					if( date.value.length < 2 ){
						$( DOM.parent + ' form#RECORD_TRAFFIC input[name="date"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#RECORD_TRAFFIC input[name="date"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
                
				if( FNC.in_array( 'value', data, 'name', false ) ){
					var value = FNC.in_array( 'value', data, 'name', true ); 
					if( value.value.length < 2 ){
						$( DOM.parent + ' form#RECORD_TRAFFIC input[name="value"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#RECORD_TRAFFIC input[name="value"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				
				if( err < 1 ){
					$( DOM.parent + ' form#RECORD_TRAFFIC button[type="submit"]').prop('disabled', 'true');
					$( DOM.parent + ' form#RECORD_TRAFFIC button[type="submit"]').text( 'Идет отправка...' );
				}
			
				return ( err < 1 ) ? true : false;
			},
			
			form_success : function( response ){
				if( parseInt(response.err) == 0 ){
					FNC.alert('success', response.mess );
					setTimeout(function(){
						window.history.back();
					}, 1000);
				}else{
					FNC.alert('error', response.mess );
				}
				$( DOM.parent + ' form#RECORD_TRAFFIC button[type="submit"]').removeAttr('disabled');
				$( DOM.parent + ' form#RECORD_TRAFFIC button[type="submit"]').text( 'Обновить' );
			}
            
		};
		
        Ev.form_add_traffic.date_picer();
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});