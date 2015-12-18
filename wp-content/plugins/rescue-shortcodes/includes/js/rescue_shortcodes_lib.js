jQuery(function($){
	$(document).ready(function(){
		
		// Toggle
		$("h3.rescue-toggle-trigger").click(function(){
			$(this).toggleClass("active").next().slideToggle("fast");
			return false; //Prevent the browser jump to the link anchor
		});
					
		// UI tabs
		$( ".rescue-tabs" ).tabs();
		
		// UI accordion
		$(".rescue-accordion").accordion({autoHeight: false});		

	});
});