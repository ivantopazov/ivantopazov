$(function(){
	
	var init = function(){
		
		window.Ev.category_groups_edit = {
			form_before : function( data ){
				var err = 0;
				
				if( FNC.in_array( 'title', data, 'name', false ) ){
					var title = FNC.in_array( 'title', data, 'name', true ); 
					if( title.value.length < 2 ){
						$( DOM.parent + ' form#GROUPS_UPD input[name="title"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#GROUPS_UPD input[name="title"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				if( FNC.in_array( 'description', data, 'name', false ) ){
					var description = FNC.in_array( 'description', data, 'name', true ); 
					if( description.value.length < 2 ){
						$( DOM.parent + ' form#GROUPS_UPD textarea[name="description"]').css( 'outline', '1px solid red' );
						setTimeout(function (){
							$( DOM.parent + ' form#GROUPS_UPD textarea[name="description"]').css( 'outline', 'none' );
						},1000);
						err++;
					} 
				}
				
				if( err < 1 ){
					$( DOM.parent + ' form#GROUPS_UPD button[type="submit"]').prop('disabled', 'true');
					$( DOM.parent + ' form#GROUPS_UPD button[type="submit"]').text( 'Идет отправка...' );
				}
			
				return ( err < 1 ) ? true : false;
			},
			
			form_success : function( response ){
				if( parseInt(response.err) == 0 ){
					FNC.alert('success', response.mess );
					setTimeout(function(){
						window.location.replace('/category');
					}, 1000);
				}else{
					FNC.alert('error', response.mess );
				}
				$( DOM.parent + ' form#GROUPS_UPD button[type="submit"]').removeAttr('disabled');
				$( DOM.parent + ' form#GROUPS_UPD button[type="submit"]').text( 'Обновить' );
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