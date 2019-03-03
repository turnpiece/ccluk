/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {
	var doc = jQuery( document );

	// Add options
	function add_markup( event, options, data ) {
		var url = '';

		try { url = data.kml_url ? data.kml_url : ''; }
		catch ( ignore ) { url = ''; }

		options.append(
			'<fieldset id="agm-kml_url_overlay">' +
				'<legend>KML Overlay</legend>' +
				'<label for="agm-kml_url">KML file URL</label>' +
				'<input type="text" id="agm-kml_url" class="widefat" value="" />' +
			'</fieldset>'
		);

		options.find( '#agm-kml_url' ).val( url );
	}

	// Save KML URL
	function save_data( event, request ) {
		request.kml_url = jQuery( '#agm-kml_url' ).val();
	}

	// Load KML overlay
	function init_map( event, map, data ) {
		var url = '';

		try { url = data.kml_url ? data.kml_url : ''; }
		catch ( ignore ) { url = ''; }

		jQuery( '#agm-kml_url' ).val( url );
		if ( ! url ) {
			return false;
		}

		var kml = new window.google.maps.KmlLayer( url );
		doc.data( 'kml_overlay', kml );
		kml.setMap( map );
	}

	// Update hte KML overlay
	function close_dialog( event, map ) {
		var oldKml = doc.data( 'kml_overlay' );
		if ( oldKml ) {
			oldKml.setMap(null);
		}

		var url = jQuery( '#agm-kml_url' ).val();
		if ( ! url ) {
			return false;
		}

		var kml = new window.google.maps.KmlLayer( url );
		kml.setMap(map);
	}


	doc.bind('agm_google_maps-admin-options_initialized', add_markup );
	doc.bind("agm_google_maps-admin-save_request", save_data );
	doc.bind("agm_google_maps-admin-map_initialized", init_map );
	doc.bind("agm_google_maps-admin-options_dialog-closed", close_dialog );
});
