$(function(){
	
	var init = function(){
		
        //$('.fooTabled').footable();
		window.Ev.products_list = {
            /*
            parseItem : function( art ){
                var articul = art || false;
                
                console.log( articul );
                
                if( articul !== false ){
                    $.ajax({
                        type: "post",
                        url: "/parser/parseProductSokolovtmToItem/" + articul,
                        dataType: 'json',
                        success: function( data ){
                            
                            alert( 'Парсинг окончен!' );
                            console.log( data );
                            
                        }
                    });
                }
                
                
            }
            */
            
        };
        
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});