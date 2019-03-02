/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global console:false*/
/*global wp:false*/
/*global document:false */
/*global wpmUi:false */
/*global _agm:false */
/*global _agmConfig:false */
/*global l10nEditor:false */
/*global navigator:false */

jQuery(function() {

	/**
	 * Individual (currently active) map handler.
	 */
	var _mapHandler = false;

	/**
	 * The WpmUiWindow object that displays the map popup.
	 */
	var _popup = null;

	/**
	 * The WpmUiWindow jQuery context.
	 */
	var wnd = null;

	/**
	 * Find Media Buttons strip and add the new one
	 */
	var _mbuttons = jQuery( '#wp-content-media-buttons' );

	if ( ! _mbuttons.length && ! (wp || {}).element ) {
		return;
	}


	// --- Helper functions ---

	/**
	 * Resizes the _popup window.
	 */
	function updateSizes() {
		_popup.size(
			740,
			jQuery( window ).height() - 100
		);
	}

	/**
	 * Open a new popup with the map editor.
	 */
	function open_map_editor() {
		var attach = function attach() {
			jQuery( document ).trigger( 'agm_map_editor_open' );
			jQuery( window ).on( 'resize', updateSizes );
		};

		var detach = function detach() {
			jQuery( window ).off( 'resize', updateSizes );
			jQuery( document ).trigger( 'agm_map_editor_close' );

			wnd = null;
			_popup = null;
		};

		if ( null === _popup ) {
			_popup = wpmUi.popup();
			// Proxy the API to what's being used elsewhere too
			if (_popup && !_popup.close && _popup.hide) {
				_popup.close = _popup.hide;
			}
		} else {
			_popup.close();
		}

		_popup.title( l10nEditor.google_maps )
			.set_class( 'agm-editor' )
			.modal( true )
			.content( jQuery( '#map_container_src' ) )
			.onclose( detach )
			.show();

		wnd = _popup.$();

		wpmUi.upgrade_tooltips();

		attach();

		return false;
	}


	/**
	 * Requests a fresh list of existing maps from the server.
	 */
	function requestMapList() {
		var data = {
			'action': 'agm_list_maps'
		};

		jQuery.post(
			window.ajaxurl,
			data,
			loadMaps
		);
	}

	/**
	 * Renders the HTML for list of maps from server JSON response.
	 */
	function loadMaps( data ) {
		if ( null === wnd ) { return; }

		var html, load_next, load_prev,
			current = data.maps ? data.maps.length : 0,
			total = data.total ? parseInt( data.total, 10 ) : 0,
			paging = jQuery( '.agm-paging_bar', wnd ),
			results = jQuery( '.maps_existing_result', wnd ),
			incr = paging.length ? parseInt( paging.attr('data-increment'), 10 ) : 0,
			next_increment = incr + 1,
			prev_increment = incr - 1;

		if ( total ) {
			html = jQuery( '<ul></ul>' );
			for ( var ind = 0; ind < data.maps.length; ind += 1 ) {
				var el = data.maps[ind];

				html.append( '<li class="existing_map_item">' +
					'<div class="map_item_title"><a href="#" class="edit_map_item" title="' + l10nEditor.preview_or_edit + '">' + el.title + '</a></div>' +
					'<a href="#" title="' + l10nEditor.use_this_map + '"><code class="map-shortcode add_map_item">[' + _agmConfig.shortcode + ' id="<strong>' + el.id + '</strong>"]</code></a>' +
					'<input type="hidden" value="' + el.id + '" />' +
					'<div class="map_item_actions">' +
						'<a href="#" class="edit_map_item">' +
							l10nEditor.preview_or_edit +
						'</a>' +
						'&nbsp;|&nbsp;' +
						'<a href="#" class="add_map_item">' +
							l10nEditor.use_this_map +
						'</a>' +
						'<a href="#" class="delete_map_item"><i class="dashicons dashicons-trash"></i>' +
							l10nEditor.delete_map +
						'</a>' +
					'</div>' +
				'</li>' );
			}
		} else {
			html = jQuery( '<div class="tc"></div>' );
			html.append( l10nEditor.no_maps );
		}

		results.empty().append( html );

		if ( total > current ) {
			paging = jQuery( '<div class="agm-paging_bar" data-increment="' + incr + '" />' );
			paging.appendTo( results );

			if ( prev_increment >= 0 ) {
				load_prev = jQuery( '<a href="#" class="agm-load_prev_maps">' + l10nEditor.load_prev_maps + '</a>' );

				load_prev
					.appendTo( paging )
					.bind( 'click', function prev_click() {
						paging.attr( 'data-increment', prev_increment );
						jQuery.post(
							window.ajaxurl,
							{
								'action': 'agm_list_maps',
								'increment': prev_increment
							},
							loadMaps
						);
						return false;
					});
			}

			if ( current ) {
				load_next = jQuery( '<a href="#" class="agm-load_next_maps">' + l10nEditor.load_next_maps + '</a>' );

				load_next
					.appendTo( paging )
					.bind( 'click', function next_click() {
						paging.attr( 'data-increment', next_increment );
						jQuery.post(
							window.ajaxurl,
							{
								"action": 'agm_list_maps',
								"increment": next_increment
							},
							loadMaps
						);
						return false;
					});
			}
		}

		if ( ! current && ! total ) {
			show_create_map();
		}

		jQuery( window ).trigger( 'resize' );
	}

	/**
	 * Ask user to confirm deletion of a single map.
	 */
	function confirm_delete_map() {
		if ( null === wnd ) { return; }

		var me = jQuery( this ),
			map_id = me.parents( 'li' ).find( 'input:hidden' ).val();

		_popup.confirm({
			'message': l10nEditor.delete_confirmation,
			'buttons': [l10nEditor.confirm_delete, l10nEditor.confirm_cancel],
			'callback': function( key ) {
				if ( key === 0 ) {
					delete_map( map_id );
				}
			}
		});
	}

	/**
	 * Requests deleting of a map.
	 */
	function delete_map( map_id ) {
		jQuery.post(
			window.ajaxurl,
			{
				'action': 'agm_delete_map',
				'id': map_id
			},
			function( data ) {
				requestMapList();
			}
		);
	}

	/**
	 * Creates tag markup.
	 */
	function createMapIdMarkerMarkup( id, text ) {
		if ( null === wnd ) { return ''; }

		if ( ! id ) { return ''; }

		if ( text && text.length ) {
			return ' [' + _agmConfig.shortcode + ' id="' + id + '"]' + text + '[/' + _agmConfig.shortcode + '] ';
		} else {
			return ' [' + _agmConfig.shortcode + ' id="' + id + '"] ';
		}
	}

	/**
	 * Handles map list item insert click.
	 */
	function insertMapItem () {
		if ( null === wnd ) { return; }

		var me = jQuery(this),
			map_id = me.parents('li').find('input:hidden').val(),
			mapMarker = createMapIdMarkerMarkup( map_id, getSelectedText());

		updateEditorContents( mapMarker );
		_popup.close();

		return false;
	}

	/**
	 * Inserts the map marker into editor.
	 * Supports TinyMCE and regular editor (textarea).
	 */
	function updateEditorContents( mapMarker ) {
		if ( null === wnd ) { return; }

		mapMarker = wpmUi.apply_filters('agm-insert-marker', mapMarker) || '';
		if (!mapMarker.length) { return false; }

		var text;

		if ( ( wp || {} ).blocks ) {
			jQuery(document).trigger( 'agm-map-inserted', mapMarker );
			return false;
		}

		if ( window.tinyMCE && ! jQuery( '#content' ).is( ':visible' ) ) {
			text = window.tinyMCE.activeEditor.selection.getContent();
			window.tinyMCE.execCommand( 'mceInsertContent', true, mapMarker );
		} else {
			insertAtCursor( jQuery( '#content' ).get(0), mapMarker );
		}
	}

	/**
	 * Returns the currently selected text of the post content
	 */
	function getSelectedText() {
		if ( null === wnd ) { return; }

		var field, sel, startPos, endPos,
			text = '';

		if ( window.tinyMCE && ! jQuery( '#content' ).is( ':visible' ) ) {
			var selection = ( window.tinyMCE.activeEditor || {} ).selection;
			text = selection && selection.getContent() ? selection.getContent() : '';
		} else {
			field = jQuery( '#content' ).get(0);

			// IE version
			if ( undefined !== document.selection ) {
				field.focus();
				sel = document.selection.createRange();
				text = sel.text;
			}
			// Mozilla version
			else if ( undefined !== field.selectionStart ) {
				startPos = field.selectionStart;
				endPos = field.selectionEnd;
				text = field.value.substring(startPos, endPos);
			}
		}
		return text;
	}

	/**
	 * Inserts map marker into regular (textarea) editor.
	 */
	function insertAtCursor( fld, text ) {
		if ( null === wnd ) { return; }

		var sel, startPos, endPos;

		// IE
		if ( document.selection && ! window.opera ) {
			fld.focus();
			sel = window.opener.document.selection.createRange();
			sel.text = text;
		}

		// Rest
		else if ( undefined !== fld.selectionStart && ! isNaN( fld.selectionStart ) ) {
			startPos = fld.selectionStart;
			endPos = fld.selectionEnd;

			fld.value = fld.value.substring(0, startPos) +
				text +
				fld.value.substring(endPos, fld.value.length);
		} else {
			fld.value += text;
		}
	}

	/**
	 * Ajax handler
	 */
	function display_map_details( data ) {
		if ( null === wnd ) { return; }

		if ( undefined !== data.title && data.title.length ) {
			_popup.title( l10nEditor.edit_map );
		} else {
			_popup.title( l10nEditor.new_map );
		}

		var container = jQuery( '.map_preview_container', wnd );

		if ( _mapHandler ) {
			_mapHandler.destroy();
		}

		_mapHandler = new window.AgmMapHandler( container, data );
	}

	/**
	 * Loads a map and opens it for preview/editing.
	 */
	function updateMapPreview () {
		if ( null === wnd ) { return; }

		var me = jQuery( this ),
			id = me.parents( 'li' ).find( 'input' ).val();

		jQuery.post(
			window.ajaxurl,
			{
				'action': 'agm_load_map',
				'id': id
			},
			display_map_details
		);

		jQuery( '.maps_new_switch_container', wnd ).hide();
		jQuery( '.maps_advanced_container', wnd ).hide();
		jQuery( '.maps_existing', wnd ).hide();
	}

	/**
	 * Opens a fresh map.
	 */
	function createMap () {
		if ( null === wnd ) { return; }

		jQuery.post(
			window.ajaxurl,
			{
				'action': 'agm_new_map'
			},
			display_map_details
		);

		jQuery( '.maps_new_switch_container', wnd ).hide();
		jQuery( '.maps_advanced_container', wnd ).hide();
		jQuery( '.maps_existing', wnd ).hide();
	}

	/**
	 * Toggles the advanced list mode.
	 */
	function toggle_advanced_mode() {
		if ( null === wnd ) { return; }

		if ( wnd.hasClass( 'advanced-mode' ) ) {
			advanced_mode_off();
		} else {
			advanced_mode_on();
		}
	}

	/**
	 * Enable the advanced list mode.
	 */
	function advanced_mode_on() {
		if ( null === wnd ) { return; }

		wnd.addClass( 'advanced-mode' );

		jQuery( 'li.existing_map_item' ).each(function () {
			var me = jQuery(this),
				mid = me.find( 'input:hidden' ).val(),
				adv = jQuery(
					'<div class="map_advanced_checkbox_container">' +
						'<input type="checkbox" class="map_advanced_checkbox" value="' + mid + '" />' +
						'<div style="clear:both"></div>' +
					'</div>'
				);

			me.addClass( 'advanced-mode' );

			jQuery( '.map_advanced_checkbox', adv ).click(function() {
				if ( jQuery(this).prop( 'checked' ) ) {
					me.addClass('selected');
				} else {
					me.removeClass('selected');
				}
				check_adv_buttons();
			});

			me.prepend(adv);
		});



		jQuery( '.advanced_mode_buttons', wnd ).show();
		jQuery( '.maps_advanced_switch .text', wnd ).text( l10nEditor.advanced_off );

		// Bind events
		jQuery( '.maps_merge_locations', wnd ).click( adv_merge_locations );
		jQuery( '.maps_batch_delete', wnd ).click( adv_confirm_batch_delete );

		check_adv_buttons();
	}

	/**
	 * Enables/Disables the advanced buttons depending whether any map is
	 * checked or not.
	 */
	function check_adv_buttons() {
		var checked = jQuery( 'li.existing_map_item .map_advanced_checkbox:checked' ),
			no_select = checked.length === 0;

		jQuery( '.maps_merge_locations', wnd ).prop( 'disabled', no_select );
		jQuery( '.maps_batch_delete', wnd ).prop( 'disabled', no_select );
	}

	/**
	 * Turn off the advanced mode.
	 */
	function advanced_mode_off() {
		if ( null === wnd ) { return; }

		wnd.removeClass( 'advanced-mode' );

		jQuery( '.map_advanced_checkbox_container', wnd ).remove();
		jQuery( '.advanced_mode_buttons', wnd ).hide();

		jQuery( '.existing_map_item.selected', wnd ).removeClass( 'selected' );
		jQuery( '.existing_map_item.advanced-mode', wnd ).removeClass( 'advanced-mode' );

		jQuery( '.maps_merge_locations', wnd ).unbind( 'click' );
		jQuery( '.maps_batch_delete', wnd ).unbind( 'click' );
		jQuery( '.maps_advanced_mode_help_container', wnd ).html( l10nEditor.advanced_mode_activate_help );
		jQuery( '.maps_advanced_switch .text', wnd ).text( l10nEditor.advanced );
	}

	/**
	 * Advanced function: Merge locations
	 */
	function adv_merge_locations() {
		var checked = jQuery( 'li.existing_map_item .map_advanced_checkbox:checked' ),
			map_ids = [];

		checked.each(function () {
			map_ids.push( jQuery( this ).val() );
		});

		jQuery.post(
			window.ajaxurl,
			{
				'action': 'agm_merge_maps',
				'ids': map_ids
			},
			function( data ) {
				var container = jQuery( '.map_preview_container', wnd );

				advanced_mode_off();
				exit_list();

				if ( _mapHandler ) { _mapHandler.destroy(); }
				_mapHandler = new window.AgmMapHandler( container, data );

				jQuery( '.maps_existing', wnd ).hide();
			}
		);
	}

	/**
	 * Ask user to confirm deletion of a single map.
	 */
	function adv_confirm_batch_delete() {
		if ( null === wnd ) { return; }

		var checked = jQuery( 'li.existing_map_item .map_advanced_checkbox:checked' ),
			map_ids = [];

		checked.each(function () {
			map_ids.push( jQuery( this ).val() );
		});

		_popup.confirm({
			'message': l10nEditor.batch_delete_confirmation,
			'buttons': [l10nEditor.confirm_delete, l10nEditor.confirm_cancel],
			'callback': function( key ) {
				if ( key === 0 ) {
					adv_batch_delete( map_ids );
				}
			}
		});
	}

	/**
	 * Delete multiple maps at once.
	 */
	function adv_batch_delete( map_ids ) {
		jQuery.post(
			window.ajaxurl,
			{
				'action': 'agm_batch_delete',
				'ids': map_ids
			},
			function( data ) {
				advanced_mode_off();

				// Refresh the map-list.
				requestMapList();
			}
		);
	}

	/**
	 * Shows the buttons for the Map-List view.
	 */
	function enter_list() {
		var el_preview = jQuery( '.map_preview_container', wnd ),
			el_existing = jQuery( '.maps_existing', wnd ),
			el_create = jQuery( '.maps_new_switch_container', wnd ),
			el_advanced = jQuery( '.maps_advanced_container', wnd );

		if ( _mapHandler ) {
			_mapHandler.destroy();
		}

		el_existing.show();
		el_create.show();
		el_advanced.show();

		_popup.title( l10nEditor.existing_map );

		if ( jQuery.browser.webkit ) {
			el_preview.css({
				'height': 0,
				'width': 0
			});
		}
	}

	/**
	 * Hides the dialog buttons for the Map-List view.
	 */
	function exit_list() {
		var el_preview = jQuery( '.map_preview_container', wnd ),
			el_existing = jQuery( '.maps_existing', wnd ),
			el_create = jQuery( '.maps_new_switch_container', wnd ),
			el_advanced = jQuery( '.maps_advanced_container', wnd );

		if ( _mapHandler ) { _mapHandler.destroy(); }

		el_existing.hide();
		el_create.hide();
		el_advanced.hide();

		if ( jQuery.browser.webkit ) {
			el_preview.css({
				'height': 0,
				'width': 0
			});
		}
	}

	/**
	 * Show the Existing-Maps-List form.
	 */
	function show_existing_maps() {
		if ( null === wnd ) { return; }

		enter_list();

		// Load fresh map list on Existing Maps tab selection
		requestMapList();
	}

	/**
	 * Show the Create-New-Map form.
	 */
	function show_create_map() {
		if ( null === wnd ) { return; }

		exit_list();

		createMap();
	}

	/**
	 * Insert the map-shortcode into the current content editor.
	 */
	function insert_shortcode( ev, id ) {
		if ( null === wnd ) { return; }

		var mapMarker = createMapIdMarkerMarkup( id );

		updateEditorContents( mapMarker );
		_popup.close();

		return false;
	}


	// --- Create Elements ---

	_mbuttons.append(
		'<a title="' + l10nEditor.google_maps + '" class="button add_map" href="#">' +
			'<img alt="' + l10nEditor.google_maps + '" src="' + _agm.root_url + '/img/system/globe-button.gif">' +
		'</a>'
	);

	// Create the needed editor container HTML
	jQuery( 'body' ).append(
		'<div id="map_container_src" style="display:none"><div id="map_container">' +
			( _agm.is_multisite ?
				'' :
				'<p class="agm_less_important">' +
				'For more detailed instructions on how to use refer to ' +
				'<a target="_blank" href="http://premium.wpmudev.org/project/wordpress-google-maps-plugin/#usage">' +
				'WPMU DEV Maps Installation and Use instructions</a>.' +
				'</p>'
			) +
			'<div class="agm_container maps_existing">' +
				'<div class="maps_existing_result">' +
					'<div class="tc">' +
						'<img src="' + _agm.root_url + '/img/system/loading.gif" />' +
					'</div>' +
					'<div class="tc">' +
						l10nEditor.loading +
					'</div>' +
				'</div>' +
			'</div>' +
			'<div class="map_preview_container"><div class="map_preview"></div></div>' +

			'<div class="buttons">' +
				'<span class="maps_advanced_container">' +
					'<button type="button" class="button-secondary action maps_advanced_switch"' +
						'data-tooltip="' + l10nEditor.advanced_mode_activate_help + '" data-width="500"' +
					'>' +
						'<i class="dashicons dashicons-admin-tools"></i> ' +
						'<span class="text">' + l10nEditor.advanced + '</span>' +
					'</button>' +
					'<span class="advanced_mode_buttons" style="display:none">' +
						'<button type="button" class="button-secondary action maps_merge_locations" ' +
							'data-tooltip="' + l10nEditor.advanced_mode_help + '" data-width="500"' +
						'>' +
							l10nEditor.merge_locations +
						'</button>' +
						'<button type="button" class="button-secondary action maps_batch_delete">' +
							l10nEditor.batch_delete +
						'</button>' +
					'</span>' +
				'</span>' +
				'<span class="maps_new_switch_container">' +
					'<button type="button" class="button-secondary action maps_new_switch" ' +
						'data-tooltip="' + l10nEditor.new_map_intro + '" data-width="500"' +
					'>' +
						'<i class="dashicons dashicons-plus"></i> ' +
						l10nEditor.new_map +
					'</button>' +
				'</span>' +
			'</div>' +
		'</div></div>'
	);


	// --- Bind events: Page Editor ---

	// Show the Map-Editor when user clicks the Map-Button.
	jQuery( document ).on( 'click', '.add_map', open_map_editor );


	// --- Bind events: Map Editor Popup ---

	// Show the add-new form when user clicks the button.
	jQuery( document ).on( 'click', '.agm-editor .maps_new_switch', show_create_map );

	// Show map-list when map popup is opened.
	jQuery( document ).on( 'agm_map_editor_open', show_existing_maps );

	// Show map-list when map is closed (button 'Go Back').
	jQuery( document ).on( 'agm_map_close', '.agm-editor .map_preview_container', show_existing_maps );

	// On map addition, update editor.
	jQuery( document )
		.on( 'click', 'li.existing_map_item .add_map_item', insertMapItem)
		.on( 'click', 'li.existing_map_item .edit_map_item', updateMapPreview)
		.on( 'click', 'li.existing_map_item .delete_map_item', confirm_delete_map);

	// Bind map editor insert event to map insert
	jQuery( document ).on( 'agm_map_insert', '.agm-editor .map_preview_container', insert_shortcode );

	// Bind advanced mode switching
	jQuery( document ).on( 'click', '.agm-editor .maps_advanced_switch', toggle_advanced_mode );

	// Highlight the active existing map item
	jQuery( 'body' )
		.on( 'mouseover', 'li.existing_map_item', function () { jQuery(this).addClass( 'agm_active_item' ); })
		.on( 'mouseout', 'li.existing_map_item', function () { jQuery(this).removeClass( 'agm_active_item' ); });
});
