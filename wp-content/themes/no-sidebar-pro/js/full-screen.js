jQuery(function( $ ){

	// Calculate center of .front div
	var windowHeight = $(window).height();
	
	$('.full-screen') .css({'height': windowHeight +'px'});
		
	$(window).resize(function(){
	
	var windowHeight = $(window).height();
	
		$('.full-screen') .css({'height': windowHeight +'px'});
	
	});

});