$(function(){
	
	var init = function(){
		
		window.Ev.category_groups_remove = {
			remove_item : function( itemId ){
				$.ajax({
					type: "post",
					url: "/category/remove_group",
					data: { id: itemId },
					success: function(){
						window.location.replace('/category');
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