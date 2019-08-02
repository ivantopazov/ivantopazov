$(function(){
	
	var init = function(){
		
		window.Ev.proects = {
			select_item : function( itemId ){
				$.ajax({
					type: "post",
					url: "/proects/set_select",
					data: { id: itemId },
					success: function(){
						window.location.reload();
					}
				});
				
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