/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(document).bind(
	"agm_google_maps-user-adding_marker",
	function (e, marker, idx, map, original) {
		if (original === undefined || ! ("disposition" in original) ) { return false; }
		if ("post_marker" !== original.disposition) { return false; }

		marker._agm_disposition = "post_type";
	}
);

jQuery(document).bind(
	"agm_google_maps-user-map_initialized",
	function (e, map, data) {
		// Short-circuit from marker iteration if nothing to do
		if ( data.nearby_posts_in_list && ! data.nearby_boundaries ) { return false; }

		var markers = map._agm_get_markers();
		jQuery.each(markers, function (idx, marker) {
			if ( "_agm_disposition" in marker ) {
				if ( ! data.nearby_posts_in_list ) {
					jQuery('.agm_mh_marker_list a[href="#agm_mh_marker-' + idx + '"]')
						.parents("li").remove();
				}
				return true;
			}

			if ( ! data.nearby_boundaries) { return true; }
			var circle = new window.google.maps.Circle({
				"map": map,
				"center": marker.getPosition(),
				"radius": data.nearby_within,
				"strokeWeight": 2,
				"strokeColor": "#000",
				"strokeOpacity": 0.4,
				"fillColor": "#000",
				"fillOpacity": 0.1
			});
		});
	}
);
