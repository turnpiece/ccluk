/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */


// Load selected Places
jQuery(document).bind("agm_google_maps-user-map_initialized", function (e, map, data, markers) {
	function initialize_all_markers_places (map, show, distance, types) {
		var show_places = show,
			places_radius = distance,
			place_types = types;

		if ( ! show_places ) { return false; }

		var service = new window.google.maps.places.PlacesService( map ),
			markers = map._agm_get_markers(),
			request = {
				"radius": places_radius
			};

		if ( place_types ) { request.types = place_types; }

		jQuery.each(markers, function () {
			var marker = this;
			request.location = marker.getPosition();
			service.search(request, function (response) {
				update_marker_places(map, marker, response);
			});
		});
	}

	function update_marker_places (map, marker, places) {
		var pos = marker.getPosition().toString();
		jQuery.each(places, function () {
			var place = this,
				place_icon = new window.google.maps.MarkerImage(
					place.icon.toString(),
					null, null, null, new window.google.maps.Size(32, 32)
				),
				place_marker = new window.google.maps.Marker({
					"title": place.name,
					"map": map,
					"icon": place_icon,
					"draggable": false,
					"clickable": true,
					"position": place.geometry.location
				}),
				info = new window.google.maps.InfoWindow({
					"content": '<b>' + place.name + '</b><br />' + '<p>' + place.vicinity + '</p>',
					"maxWidth": 400
				});

			window.google.maps.event.addListener(
				place_marker,
				'click',
				function() {
					info.open( map, place_marker );
				}
			);
		});
	}

	if ( typeof window.google.maps.places !== 'object' ) { return false; }

	var show = data.show_places ? parseInt( data.show_places ) : false,
		distance = data.places_radius || 1000,
		place_types = data.place_types;

	initialize_all_markers_places( map, show, distance, place_types );
});