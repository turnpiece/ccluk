/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {
	jQuery(".agm_google_maps").each(function () {
		var me = jQuery(this),
			cache = me.find( ".mlm-cached,.agm_google_maps-loading_message" );

		if ( ! cache.length ) { return true; }

		if ( cache.is(".mlm-cached") ) {
			me.addClass("agm-mlm-cached_map");
		} else {
			me.attr("data-mlm-cache-key", cache.attr("data-mlm-cache-key"));
		}
	});
});

jQuery(document).on("agm_google_maps-user-map_initialized", function (e, map, data) {
	var el = jQuery(map.getDiv()).closest(".agm_google_maps");
	if ( el.is(".agm-mlm-cached_map") ) { return false; }

	window.google.maps.event.addListener(map, "tilesloaded", function () {
		var parent = jQuery(map.getDiv()).closest(".agm_google_maps");
		if ( ! parent.length ) { return false; }

		var pc = parent.clone();
		pc.find(".agm_mh_container,.agm_panoramio_container").remove();

		var div = jQuery("<div />").append(pc);
		jQuery.post(
			_agm.ajax_url,
			{
				action: "agm-mlm-store-cache",
				map_id: data.id,
				cache: div.html(),
				key: parent.attr("data-mlm-cache-key")
			}
		);
	});
});
