$(function(){
	    
	var init = function(){
		
		window.Ev.contacts = {
            
            success : function ( response ){
                FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                $( DOM.parent + ' #support' )[0].reset();
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