(function( $, doc, win ) {
	"use strict";
	if( inc_opt.is_upfront ) return;

	// Listen to resize event
	$(window).on("resize", _.debounce( function(e){
		Hustle.Events.trigger('hustle_resize');
	}, 300 ));
		
	_.each(Modules, function(module, key) {
		if ( module.module_type === 'popup' ) {
			var popup = new Optin.PopUp(module);
		}
		if ( module.module_type === 'slidein' ) {
			var slidein = new Optin.SlideIn(module);
		}
		if ( module.module_type === 'social_sharing' ) {
			var is_admin = hustle_vars.is_admin === '1';
			
			// if not admin and test mode enabled
			if ( typeof module.test_types !== 'undefined' && module.test_types !== null 
					&& typeof module.test_types.floating_social !== 'undefined'
					&& ( module.test_types.floating_social || module.test_types.floating_social === 'true' )
					&& !is_admin ) {
				return;
				
			} else if ( typeof module.test_types !== 'undefined' && module.test_types !== null
					&& typeof module.test_types.floating_social !== 'undefined'
					&& ( module.test_types.floating_social || module.test_types.floating_social === 'true' )
					&& is_admin ) {
				// bypass the enabled settings
				module.settings.floating_social_enabled = 'true';
			}
			
			if ( _.isTrue( module.settings.floating_social_enabled ) ) {
				var sshare = new Optin.SShare_floating(module);
			}
		}
	});
	
}(jQuery, document, window));
