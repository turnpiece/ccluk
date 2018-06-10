(function ($, doc){
	"use strict";
	if( pagenow !== 'hustle_page_hustle_settings' ) return;

	var E_News = Hustle.get("Settings.E_News"),
		Modules_Activity = Hustle.get("Settings.Modules_Activity"),
		Services = Hustle.get("Settings.Services");

	var e_new = new E_News();
	var m_activity = new Modules_Activity();
	var service = new Services();

	// Accordion functionality.
	$("#wpmudev-settings-widget-modules .wpmudev-box-head").on('click', function(e) {
		var $this = $(e.target),
			$body = $this.parents('.wpmudev-box').find(".wpmudev-box-body"),
			$head = $this.parents('.wpmudev-box').find(".wpmudev-box-head")
		;
			
		$body.slideToggle( 'fast', function(){
			$head.toggleClass('wpmudev-collapsed');
			$body.toggleClass('wpmudev-hidden');
		} );
	});

}(jQuery, document));
