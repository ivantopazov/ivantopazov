$(function(){
	
	var init = function(){
		
		window.Ev.proects_remove = {
			remove_item : function( itemId ){
				$.ajax({
					type: "post",
					url: "/proects/remove_item",
					data: { id: itemId },
					success: function(){
						window.location.replace('/proects');
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