(function( $ ) {
	"use strict";
	
	var Optin = window.Optin || {};
	
	Optin.module_log_view = Backbone.Model.extend({
		url: inc_opt.ajaxurl + '?action=module_viewed',
		defaults: {
			page_type: inc_opt.page_type,
			page_id: inc_opt.page_id,
			type: '',
			uri: encodeURI( window.location.href )
		},
		parse: function( res ) {
			if ( res.success ) {
				console.log('Log success!');
			} else {
				console.log('Log failed!');
			}
		}
	});
	
	/**
	 * Log module view when it's being viewed
	 */
	$(document).on("hustle:module:displayed", function( e, data ) {
		if ( typeof data === 'object' ) {
			var type = ( typeof data.type !== 'undefined' )
				? data.type
				: data.module_type;
			
			// set cookies used for "show less than" display condition
			var show_count_key = Hustle.consts.Module_Show_Count + type + "-" + data.module_id,
					current_show_count = Hustle.cookie.get( show_count_key );
				Hustle.cookie.set( show_count_key, current_show_count + 1, 30 );
			
			// Log number of times this module type has been shown so far
			var log_type = ( typeof data.display_type !== 'undefined' && type === 'embedded' )
				? data.display_type
				: type;
				
			if ( data.tracking_types != null && _.isTrue( data.tracking_types[log_type] ) ) {
				if ( typeof Optin.module_log_view != 'undefined' ) {
					var logView = new Optin.module_log_view();
					logView.set( 'type', log_type );
					logView.set( 'module_type', type );
					logView.set( 'module_id', data.module_id );
					logView.save();
				}
			}
		}
		
		
	});

}(jQuery));
