/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

/**
 * Plugin Name: Traffic-overlay
 * Author:      Philipp Stracker (Incsub)
 *
 * Javascript component for the traffic-overlay addon.
 */

jQuery(function init_addon() {
	var doc = jQuery( document );

	var init_overlay = function init_overlay( event, map, data ) {
		var traffic_layer, has_traffic = false;

		try {
			has_traffic = !! data.show_traffic;
		} catch( ignore ) {}

		if ( has_traffic ) {
			traffic_layer = new window.google.maps.TrafficLayer();
			traffic_layer.setMap( map );
		}
	};

	doc.bind( 'agm_google_maps-user-map_initialized', init_overlay );
});
