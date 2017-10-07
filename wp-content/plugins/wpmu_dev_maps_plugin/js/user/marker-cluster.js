/*! Google Maps - v2.9.3
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {

jQuery(document).bind("agm_google_maps-user-map_initialized", function (e, map, data, markers) {
	if ( ! markers || ! markers.length ) { return; }
	var markerCluster = new window.MarkerClusterer(
		map,
		markers, {
			"zoomOnClick": false, "gridSize": 30
		}
	);

	window.google.maps.event.addListener(markerCluster, "click", function (c) {
		var clustered = c.getMarkers();
		var contents = '';

		jQuery.each(clustered, function () {
			if ( '_agmInfo' in this ) {
				contents += this._agmInfo.getContent();
				contents += "<hr style='clear:both' />";
			}
		});

		var info = new window.google.maps.InfoWindow({
			content: contents
		});

		info.open(map, clustered[0]);
	});
});

});
