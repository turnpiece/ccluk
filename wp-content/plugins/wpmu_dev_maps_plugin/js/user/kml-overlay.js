/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {

// Load KML overlay
jQuery(document).bind("agm_google_maps-user-map_initialized", function (e, map, data) {
	var url = '';

	try { url = data.kml_url ? data.kml_url : ''; }
	catch (ex) { url = ''; }

	if ( ! url ) { return false; }

	var kml = new window.google.maps.KmlLayer(url);
	jQuery(document).data("kml_overlay", kml);
	kml.setMap(map);
});

});
