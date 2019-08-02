$(function(){
	
	var init = function(){
		
		window.Ev.category_states_remove = {
			remove_item : function( itemId ){
				$.ajax({
					type: "post",
					url: "/category/remove_states",
					data: { id: itemId },
					success: function(){
						window.history.back();
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