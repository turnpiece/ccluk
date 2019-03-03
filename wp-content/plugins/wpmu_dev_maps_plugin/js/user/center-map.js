/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {
	var doc = jQuery( document );

	// Center map
	var center_map = function center_map( event, map, data ) {
		var center, lat = NaN, lng = NaN;

		if ( undefined !== data.center ) {
			center = data.center;

			try {
				lat = parseFloat( center.latitude );
				lng = parseFloat( center.longitude );
			} catch ( ex ) {
				lat = NaN;
				lng = NaN;
			}
		} else if ( undefined !== data.map_center ) {
			center = data.map_center;

			try {
				lat = parseFloat( center[0] );
				lng = parseFloat( center[1] );
			} catch ( ex ) {
				lat = NaN;
				lng = NaN;
			}
		}

		if ( isNaN( lat ) || isNaN( lng ) ) {
			return false;
		}
		map.setCenter( new window.google.maps.LatLng( lat, lng ) );
	};

	doc.bind( 'agm:init', center_map );
});
