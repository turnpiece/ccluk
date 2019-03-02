/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*! Google Maps - v2.9.07
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2015; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global jQuery:false */

/**
 * Asynchrounously load Google Maps API.
 */


/**
 * Global API loaded flag.
 */
window._agmMapIsLoaded = false;


/**
 * Callback - triggers loaded flag setting.
 */
function agmInitialize () {
	window._agmMapIsLoaded = true;
	if ( undefined !== window.google.maps.Map._agm_get_markers ) {
		return true;
	}

	window.google.maps.Map.prototype._agm_markers = [];
	window.google.maps.Map.prototype._agm_get_markers = function () { return this._agm_markers; };
	window.google.maps.Map.prototype._agm_clear_markers = function () { this._agm_markers = []; };
	window.google.maps.Map.prototype._agm_add_marker = function (mrk) { this._agm_markers.push(mrk); };
	window.google.maps.Map.prototype._agm_remove_marker = function (idx) { this._agm_markers.splice(idx, 1); };
}

/**
 * Handles the actual loading of Google Maps API.
 */
function loadGoogleMaps () {
	if ( typeof window.google === 'object' &&
		typeof window.google.maps === 'object'
	) {
		// We're loaded and ready - albeit from a different source.
		return agmInitialize();
	}

	var protocol = '',
		language = '',
		src 	= '',
		script = document.createElement("script"),
		libs = _agm.libraries.join(","),
		api_key = ((window || {})._agm || {}).maps_api_key || false
	;

	try { protocol = document.location.protocol; }
	catch (ex) { protocol = 'http:'; }

	if ( window._agmLanguage !== undefined ) {
		try { language = '&language=' + window._agmLanguage; }
		catch (ex) { language = ''; }
	}
	script.type = "text/javascript";

	if (api_key) {
		api_key = "&key=" + api_key;
	}

	src = "//maps.google.com/maps/api/js?v=3" + api_key + "&libraries=";

	script.src = protocol +  src +
		libs +
		"&sensor=false" +
		language +
		"&callback=agmInitialize";
	document.body.appendChild(script);
}

jQuery( window ).on( 'load', loadGoogleMaps );
