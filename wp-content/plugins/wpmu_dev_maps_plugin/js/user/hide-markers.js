/*! Google Maps - v2.9.3
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2017; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(document).bind("agm_google_maps-user-map_initialized", function (e, map, data, markers) {
	if ( ! data.hide_map_markers ) { return false; }

	jQuery.each(markers, function () {
		this.setVisible(false);
	});
});

