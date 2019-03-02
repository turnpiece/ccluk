/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global _agmTraffic:false */
/*global navigator:false */

/**
 * Plugin Name: Traffic-overlay
 * Author:      Philipp Stracker (Incsub)
 *
 * Javascript component for ADMIN page of the traffic-overlay addon.
 */

jQuery(function init_addon() {
	var doc = jQuery( document );

	// Add the new checkbox to the maps option popup.
	function init_options( ev, options, data ) {
		var has_traffic = false;

		try {
			has_traffic = data.show_traffic ? data.show_traffic : false;
		} catch( ignore ) { }

		options.append(
			'<fieldset id="agm-show_traffic-box">' +
				'<legend>' + _agmTraffic.lang.show_traffic + '</legend>' +
				'<input type="checkbox" id="agm-show_traffic" value="1" />&nbsp;' +
				'<label for="agm-show_traffic">' + _agmTraffic.lang.show_traffic + '</label>' +
			'</fieldset>'
		);

		jQuery( '#agm-show_traffic' ).prop( 'checked', has_traffic );
	}

	// Set the current option value when saving the options.
	function sanitize_options( ev, request ) {
		request.show_traffic = jQuery( '#agm-show_traffic' ).is( ':checked' );
	}

	// Add options.
	doc.bind( 'agm_google_maps-admin-options_initialized', init_options );

	// Save options.
	doc.bind( 'agm_google_maps-admin-save_request', sanitize_options );
});
