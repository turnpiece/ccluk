/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */
/*global wpmUi:false */

jQuery(function () {
	var _places = {};

	function initialize_all_markers_places( map, show, distance, types ) {
		var show_places = show || jQuery( '#agm-show_places' ).is( ':checked' ),
			places_radius = distance || jQuery( '#agm-places_radius' ).val(),
			place_types = types && types.length ? types : false;

		if ( ! show_places ) {
			return clear_all_markers_places( map );
		}

		var service = new window.google.maps.places.PlacesService( map ),
			markers = map._agm_get_markers(),
			request = {
				"radius": places_radius
			};

		if ( place_types ) {
			request.types = place_types;
		}

		jQuery.each(markers, function () {
			var marker = this;
			request.location = marker.getPosition();
			service.search(request, function( response ) {
				update_marker_places(map, marker, response);
			});
		});
	}

	function clear_all_markers_places( map ) {
		var markers = map._agm_get_markers();

		jQuery.each(markers, function() {
			clear_marker_places( this );
		});
	}

	function update_marker_places( map, marker, places ) {
		clear_marker_places( marker );
		var pos = marker.getPosition().toString();

		jQuery.each(places, function () {
			var place = this,
				place_icon = new window.google.maps.MarkerImage(
					place.icon.toString(),
					null, null, null, new window.google.maps.Size( 32, 32 )
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

			window.google.maps.event.addListener( place_marker, 'click', function() {
				info.open( map, place_marker );
			});

			_places[pos].push( place_marker );
		});
	}

	function get_marker_places( marker ) {
		if ( _places[marker.getPosition().toString()] ) {
			return _places[marker.getPosition().toString()];
		} else {
			return [];
		}
	}

	function clear_marker_places( marker ) {
		var places = get_marker_places( marker );

		jQuery.each(places, function () {
			this.setMap( null );
		});

		_places[marker.getPosition().toString()] = [];
	}

	function init_options( ev, options, data ) {
		if ( typeof window.google.maps.places !== 'object' ) { return false; }

		var show_places = false,
			places_radius = 1000;

		try {
			show_places = data.show_places ? parseInt(data.show_places) : show_places;
		} catch ( ex ) {
			show_places = false;
		}

		try {
			places_radius = data.places_radius ? parseInt(data.places_radius) : places_radius;
		} catch ( ex ) {
			places_radius = 1000;
		}

		var markup = '<fieldset id="agm-places">' +
			'<legend>Google Places</legend>' +
			'<input type="checkbox" id="agm-show_places" value="1" ' + (show_places ? 'checked="checked"' : '' ) + ' />' +
			' <label for="agm-show_places">Show Google Places close to my map markers</label>' +
			'<br />' +
			'<label for="agm-places_radius">Show Google Places within ' +
				'<input type="text" size="6" id="agm-places_radius" value="' + places_radius + '" />' +
			' meters of the marker</label>' +
			'<br />' +
			'<label>Limit shown places to these types:</label><br />' +
			'<select multiple="multiple" id="agm-place-types">';

		jQuery.each(data.defaults.place_types, function( val, lbl ) {
			var selected = '';

			if ( data.place_types && data.place_types.length ) {
				selected = (data.place_types.indexOf(val) > -1 ? 'selected="selected"' : '' );
			}

			markup += '<option value="' + val + '" ' + selected + '>' + lbl + '</option>';
		});

		markup += '</select></fieldset>';

		options.append(markup);
	}

	function save_options( ev, request ) {
		if ( typeof window.google.maps.places !== 'object' ) { return false; }

		request.show_places = jQuery( '#agm-show_places' ).is( ':checked' ) ? 1 : 0;
		request.places_radius = jQuery( '#agm-places_radius' ).val();
		request.place_types = jQuery( '#agm-place-types' ).val();
	}

	function prepare_map( ev, map, data ) {
		if ( typeof window.google.maps.places !== 'object' ) { return false; }

		var show = data.show_places ? parseInt( data.show_places ) : false,
			distance = data.places_radius || 1000,
			place_types = data.place_types && data.place_types.length ? data.place_types : data.defaults.place_types;

		initialize_all_markers_places(map, show, distance, place_types);
	}

	function options_closed( ev, map ) {
		if ( typeof window.google.maps.places !== 'object' ) { return false; }

		var place_types = jQuery( '#agm-place-types' ).val();
		initialize_all_markers_places( map, null, null, place_types );
	}

	function marker_added( ev, marker, map ) {
		if ( typeof window.google.maps.places !== 'object' ) { return false; }

		var place_types = jQuery( '#agm-place-types' ).val();
		initialize_all_markers_places(map, null, null, place_types);
	}

	function marker_removed( ev, marker ) {
		if ( typeof window.google.maps.places !== 'object' ) { return false; }

		clear_marker_places( marker );
	}

	// ----- Hooks -----

	// Add options
	jQuery(document).bind( 'agm_google_maps-admin-options_initialized', init_options );

	// Save Places options
	jQuery(document).bind( 'agm_google_maps-admin-save_request', save_options);

	// Load Places
	jQuery(document).bind("agm_google_maps-admin-map_initialized", prepare_map);

	// Repaint locations on options close
	jQuery(document).bind('agm_google_maps-admin-options_dialog-closed', options_closed);

	// Repaint all places when adding a marker (inefficient, but easy)
	jQuery(document).bind( 'agm_google_maps-admin-marker_added', marker_added);

	// Null out places for removed marker
	jQuery(document).bind("agm_google_maps-admin-marker_removed", marker_removed);

});
