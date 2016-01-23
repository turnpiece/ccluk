jQuery(function( $ ){
	
	$('.hidden').each(function(){
		
		$(this).waypoint( function(direction) {
	
			$(this.element).removeClass('hidden');
		
		}, {
		  
		  offset: '75%',
		  triggerOnce: true
		  
		});
		
	});

});