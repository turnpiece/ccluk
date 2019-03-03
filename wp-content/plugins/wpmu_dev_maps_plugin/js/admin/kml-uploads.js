/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {

// Add options
jQuery(document).bind(
	'agm_google_maps-admin-options_initialized',
	function( ev, options, data ) {

		var html = '<label for="agm-kml_uploaded_file">Select a KML file</label>';

		jQuery.post(
			window.ajaxurl,
			{
				'action': 'agm_list_kml_uploads'
			},
			function( data ) {
				html += '<select class="widefat" id="agm-kml_uploaded_file">';
				html += '<option value="">Select a KML file</option>';

				jQuery.each(data, function( file, url ) {
					html += '<option value="' + url + '">' + file + '</option>';
				});

				html += '</select>';

				if ( ! jQuery( '#agm-kml_uploader' ).length ) {
					// We append the options of the KML Uploader to the existing
					// KML Overlay options box.
					jQuery( '#agm-kml_url_overlay' ).append( '<div id="agm-kml_uploader"></div>' );
				}

				jQuery( '#agm-kml_uploader' ).html( html );
				jQuery( '#agm-kml_uploaded_file' ).change(function () {
					jQuery( '#agm-kml_url' ).val( jQuery(this).val() );
				});
			}
		);
	}
);

});
