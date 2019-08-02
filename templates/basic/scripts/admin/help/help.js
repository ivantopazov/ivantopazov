$(function(){
	
	var init = function(){
		
		window.Ev.stores_help = {
            
            /* init : function(){
                
             },*/
            
            FORM : {
                
                callBack : {   
                    before : function ( data ){                          
                        var err = 0;
                        for (var key in data) {
                            var val = data [key];
                            if( val.name === 'phone' ){
                                if( FNC.validate( 'phone', val.value ) !== true ){
                                    err++;
                                    $( DOM.parent + ' form#callBack input[name="'+val.name+'"]').css('border', '1px solid red');
                                    setTimeout(function(){
                                        $( DOM.parent + ' form#callBack input[name="'+val.name+'"]').css('border', 'auto');
                                    },5000);
                                }
                            }
                        }
                        return ( err < 1 ) ? true : false;                             
                    },
                    
                    success : function ( response ){
                        
                        FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                        if( response.err < 1 ){
                            setTimeout(function(){
                                $( DOM.parent + ' #callBack')[0].reset();
                            }, 2000);
                        }
                        
                    }              
                },
                
                supportBack : {
                    success : function ( response ){
                        FNC.alert( ( response.err > 0 ) ? 'error' : 'success', response.mess );
                        if( response.err < 1 ){
                            setTimeout(function(){
                                $( DOM.parent + ' #supportBack')[0].reset();
                            }, 2000);
                        }
                    }         
                }
            }
        };
        
        //window.Ev.stores_sett.init();
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});