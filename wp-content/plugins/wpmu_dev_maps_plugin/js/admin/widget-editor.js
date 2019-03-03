/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global wpmUi:false */
/*global l10nEditor:false */
/*global navigator:false */

jQuery(function() {

	/**
	 * Individual (currently active) map handler.
	 */
	var _mapHandler = false;

	/**
	 * Parent (widget form) container for the clicked link.
	 */
	var parent = false;

	/**
	 * Opens a fresh map.
	 */
	function createNewMap () {
		var dlg,
			me = jQuery( this );

		parent = me.parents( '.agm_widget_query_options' );

		dlg = wpmUi.popup()
			.size( 740, 640 )
			.title( l10nEditor.new_map )
			.content( '<div><div id="map_editor"><div id="map_preview_container"><div id="map_preview"></div></div></div><div class="buttons"></div></div>' )
			.modal( true )
			.loading( true )
			.set_class( 'agm-editor agm-no-back' )
			.show();

		jQuery.post(
			window.ajaxurl,
			{"action": 'agm_new_map'},
			function (data) {
				if ( _mapHandler ) { _mapHandler.destroy(); }
				_mapHandler = new window.AgmMapHandler("#map_editor", data, false);
				dlg.loading( false );
			}
		);
		return false;
	}

	/**
	 * Updates the map-list in the dropdown list
	 */
	function update_map_list( ev, mapId ) {
		if ( ! parent ) { return false; }

		jQuery.post(
			window.ajaxurl,
			{
				"action": "agm_list_maps"
			},
			function (data) {
				if ( typeof data !== 'object' || undefined === data.maps || ! data.total ) {
					return false;
				}

				var opts = '';
				jQuery.each(data.maps, function (idx, el) {
					opts += '<option value="' + el.id + '" ' +
						(el.id === mapId ? 'selected="selected"' : '') +
						'>' + el.title + '</option>';
				});

				parent.find('.map_id_switch').attr('checked', true);
				parent.find('.map_id_switch').click();
				parent.find('.map_id_target').html(opts);
			}
		);
	}

	// --- Bind events ---

	// Create a new map
	jQuery('body').on( 'click', '.agm_create_new_map', createNewMap );

	// Map saved; update the list and set selection
	jQuery( document ).bind( 'agm-map-saved', update_map_list );
});
