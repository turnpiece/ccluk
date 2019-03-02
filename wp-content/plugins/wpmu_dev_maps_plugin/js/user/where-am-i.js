/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global _agmWmi:false */
/*global navigator:false */

jQuery(function () {

// Check geolocation API and bail out if needed
if ( !!! navigator.geolocation ) { return false; }

if ( _agmWmi.add_marker ) {
	jQuery( document ).bind(
		'agm_google_maps-user-map_postprocess_markers',
		function (e, data, markers, callback) {
			if ( _agmWmi.shortcode_only && ! data.visitor_location ) {
				return false;
			}

			navigator.geolocation.getCurrentPosition(
				function( position ) {
					var icon = '',
						pos = new window.google.maps.LatLng(
						position.coords.latitude,
						position.coords.longitude
					);

					if ( _agmWmi.icon ) {
						if ( _agmWmi.icon.match(/^https?:\/\//) ) {
							icon = _agmWmi.icon;
						} else {
							icon = _agm.root_url + '/img/' + _agmWmi.icon;
						}
					} else {
						icon = _agm.root_url + '/img/system/marker.png';
					}
					callback( _agmWmi.marker_label, pos, '', icon );
				}
			);
		}
	);
} else {
	jQuery( document ).bind(
		'agm_google_maps-user-map_initialized',
		function (e, map, data) {
			if ( _agmWmi.shortcode_only && ! data.visitor_location ) {
				return false;
			}

			navigator.geolocation.getCurrentPosition(
				function(position) {
					var icon = '',
						pos = new window.google.maps.LatLng(
							position.coords.latitude,
							position.coords.longitude
						);

					if ( _agmWmi.icon ) {
						if ( _agmWmi.icon.match(/^https?:\/\//) ) {
							icon = _agmWmi.icon;
						} else {
							icon = _agm.root_url + '/img/' + _agmWmi.icon;
						}
					} else {
						icon = _agm.root_url + '/img/system/marker.png';
					}

					var marker = new window.google.maps.Marker({
						"title": _agmWmi.marker_label,
						"map": map,
						"icon": icon,
						"draggable": false,
						"clickable": true,
						"position": pos
					});

					var info = new window.google.maps.InfoWindow({
						"content": _agmWmi.marker_label,
						"maxWidth": 200
					});

					window.google.maps.event.addListener(
						marker,
						'click',
						function() {
							info.open( map, marker );
						}
					);

					if ( _agmWmi.auto_center ) { map.setCenter( pos ); }
				}
			);
		}
	);
}

});
