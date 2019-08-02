$(function(){
	
	var init = function(){
		
        $( DOM.parent + ' .bx-slider').bxSlider({
            controls : true,
            pager : true,
            mode: 'fade',
            auto: true,
            speed: 200,
            adaptiveHeight : true,
            tickerHover : true,
            preloadImages: true,
            onSliderLoad : function(){
                $( DOM.parent + ' #sliderHome').removeClass('vhidden');
            }
        });
        
        
		window.Ev.home = {
		};
		
	};
	
	var e = setInterval(function(){
		if( window.CORE ){
			clearInterval( e );
			init();
		}
	}, 10);
	
});