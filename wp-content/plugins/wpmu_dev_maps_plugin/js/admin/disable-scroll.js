/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global _agmDS:false */
/*global navigator:false */

// Add options
jQuery(document).bind(
	'agm_google_maps-admin-options_initialized',
	function( ev, options, data ) {
		var has_scroll = false;

		try {
			has_scroll = data.disable_scroll ? data.disable_scroll : false;
		} catch ( ex ) { has_scroll = false; }

		options.append(
			'<fieldset id="agm-disable_scroll-box">' +
				'<legend>' + _agmDS.lang.disable_scroll + '</legend>' +
				'<input type="checkbox" id="agm-disable_scroll" value="1" />&nbsp;' +
				'<label for="agm-disable_scroll">' + _agmDS.lang.disable_scroll + '</label>' +
			'</fieldset>'
		);

		options.find( '#agm-disable_scroll' ).prop( 'checked', has_scroll );
	}
);

// Save
jQuery(document).bind(
	'agm_google_maps-admin-save_request',
	function( ev, request ) {
		request.disable_scroll = jQuery('#agm-disable_scroll').is(':checked');
	}
);
