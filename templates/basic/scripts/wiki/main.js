$(function(){
	
	var init = function(){
		
        $.getScript("/addons/scripts/jquery.maskedinput.min.js?_=1", function(){
            $('.phone-mask').mask('+7(999)999-99-99'); 
        });
        
		window.Ev.wiki = {
            
            certificated : {
                
                setType : function( summa, typeText ){
                    
                    $( DOM.parent + ' #certSetSumma' ).html( summa );
                    $( DOM.parent + ' #sertSetType' ).html( typeText );
                    
                    console.log( summa, typeText );
                }
                
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