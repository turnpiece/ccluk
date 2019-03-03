/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {
	function directions_click_scroll (e, position, marker, container) {
		var directions = container.find(".agm_mh_directions_container");
		if ( ! directions.length ) { return false; }
		jQuery(window).scrollTo(directions.offset().top);
	}

	function marker_click_popup (e, marker, map) {
		marker._agmInfo.open(map, marker);
	}

	var data = _agm.additional_behaviors || {};

	if ( data.directions_click_scroll ) {
		jQuery(document).on("agm_google_maps-user-directions-waypoint_populated", directions_click_scroll);
	}
	if ( data.marker_click_popup ) {
		jQuery(document).on("agm_google_maps-user-map_centered_to_marker", marker_click_popup);
	}
});
