/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

/**
 * Plugin Name: Center Map
 * Author:      Philipp Stracker (Incsub)
 *
 * Javascript component for ADMIN page of the center-map addon.
 */

jQuery(function () {
	if ( undefined === window._agm ) { return; }

	var doc = jQuery( document ),
		_map = null,
		_center = null,
		_icon = _agm.root_url + '/img/system/map_center.png';

	var init_map = function init_map( event, map, data ) {
		_map = map;

		if ( null !== _center ) {
			_center.setMap( null );
			_center = null;
		}

		if ( undefined !== data.map_center ) {
			var pos = new window.google.maps.LatLng( data.map_center[0], data.map_center[1] );
			map.setCenter( pos );
			set_center_marker();
		}
	};

	var center_button = function center_button( event, details, data ) {
		var marker = jQuery( '#agm_map_drop_marker', details ),
			center = jQuery( '<button type="button" class="button-secondary"></button>' );

		center.text( 'Set Center' );
		center.prepend( '<img src="' + _icon + '" style="height:20px;margin:3px;vertical-align:top" />' );
		center.insertAfter( marker );
		center.click( set_center_marker );
	};

	var set_center_marker = function set_center_marker() {
		if ( null === _map ) {
			return false;
		}

		var pos = _map.getCenter();

		if ( null !== _center ) {
			_center.setMap( null );
		}

		_center = new window.google.maps.Marker({
			title: 'Center',
            map: _map,
            icon: _icon,
            draggable: true,
            clickable: false,
            position: pos,
            zIndex: 999
        });
        _center.setMap( _map );
	};

	var save_map = function save_map( event, request ) {
		if ( null !== _center ) {
			request.map_center = [
				_center.position.lat(),
				_center.position.lng()
			];
		}
	};

	doc.bind( 'agm_google_maps-admin-map_initialized', init_map );
	doc.bind( 'agm_google_maps-admin-markup_created', center_button );
	doc.bind( 'agm_google_maps-admin-save_request', save_map );
});
