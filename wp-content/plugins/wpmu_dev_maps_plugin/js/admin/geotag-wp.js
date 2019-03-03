/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global l10nStrings:false */
/*global navigator:false */

jQuery(function () {
	function _draw_centered_map (lat, lng) {
		var root = jQuery( '#agm-gwp-location_root' ),
			address = root.find( 'label[for="agm-address"]' ),
			center = new window.google.maps.LatLng( lat, lng );

		address
			.hide()
			.after( '<div id="agm-gwp-target_map" style="width:100%; height:300px"></div>' );

		var map = new window.google.maps.Map(
			jQuery( '#agm-gwp-target_map' ).get(0),
			{
				'zoom': 12,
				'minZoom': 1,
				'center': center,
				'mapTypeId': window.google.maps.MapTypeId['ROADMAP']
			}
		);

		var marker = new window.google.maps.Marker({
			title: 'Me',
			map: map,
			icon: _agm.root_url + '/img/system/marker.png',
			draggable: true,
			clickable: false,
			position: center
		});

		window.google.maps.event.addListener(marker, 'dragend', function() {
			var geocoder = new window.google.maps.Geocoder();

			geocoder.geocode({'latLng': marker.getPosition()}, function (results, status) {
				if (status === window.google.maps.GeocoderStatus.OK) {
					var location = results[0].geometry.location;

					marker.setPosition(location);
					jQuery( '#agm-latitude' ).val( location.lat() );
					jQuery( '#agm-longitude' ).val( location.lng() );
				} else {
					window.alert( l10nStrings.geocoding_error );
				}
			});
		});
	}

	function _wait_for_maps () {
		if ( ! window._agmMapIsLoaded ) {
			window.setTimeout( _wait_for_maps, 100 );
		} else {
			init();
		}
	}

	function init () {
		var lat = parseFloat( jQuery( '#agm-latitude').val() ),
			lng = parseFloat( jQuery( '#agm-longitude').val() );

		if ( !! lat && !! lng ) { return _draw_centered_map(lat, lng); }

		// No previously stored fields
		navigator.geolocation.getCurrentPosition(
			function( position ) {
				_draw_centered_map( position.coords.latitude, position.coords.longitude );
			}
		);
	}


	if ( !!! navigator.geolocation ) { return false; }

	_wait_for_maps();
});

