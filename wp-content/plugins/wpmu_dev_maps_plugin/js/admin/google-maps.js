/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global wpmUi:false */
/*global _agm:false */
/*global l10nStrings:false */
/*global navigator:false */

/**
 * Global handler object variable.
 * Initiated as global, will be bound as object on document.load.
 */
window.AgmMapHandler = null;

jQuery(function( ) {

	/**
	 * Admin-side map handler object.
	 * Responsible for rendering markup for a particular map,
	 * and for requests related to a particular map editing.
	 *
	 * Has one public method: destroy(), which is used in Editor interface.
	 *
	 * @param selector Container element selector string.
	 * @param data Map data object.
	 */
	window.AgmMapHandler = function( selector, data, allowInsertion ) {

		var _imageSizes = ['medium', 'small', 'thumbnail', 'square', 'mini_square'],
			map,
			originalData,
			container = jQuery( selector ),
			_markers = [],
			_panoramioLayer = false;

		// Allow map insertion by default
		allowInsertion = (allowInsertion !== undefined ? allowInsertion : true);

		function populateDefaults() {
			originalData = data;

			if ( data.defaults === undefined ) {
				data.defaults = {};
			}

			data.zoom = data.zoom || data.defaults.zoom;
			data.zoom = parseInt(data.zoom) ? parseInt(data.zoom) : 1;
			data.show_map = ("show_map" in data) ? data.show_map : 1;
			data.show_posts = ("show_posts" in data) ? data.show_posts : 0;
			data.show_markers = ("show_markers" in data) ? data.show_markers : 1;
			data.show_images = ("show_images" in data) ? data.show_images : 0;

			data.snapping = ("snapping" in data.defaults) ? parseInt(data.defaults.snapping) : 1;

			data.show_panoramio_overlay = ("show_panoramio_overlay" in data) ? parseInt(data.show_panoramio_overlay) : 0;
			data.panoramio_overlay_tag = ("panoramio_overlay_tag" in data) ? data.panoramio_overlay_tag : '';

			data.street_view = ("street_view" in data) ? data.street_view : 0;
			data.street_view_pos = ("street_view_pos" in data) ? data.street_view_pos : 0;
			data.street_view_pov = ("street_view_pov" in data) ? data.street_view_pov : 0;

			data.map_type = data.map_type || data.defaults.map_type;
			data.map_alignment = data.map_alignment || data.defaults.map_alignment;
			data.post_ids = data.post_ids || [];

			data.image_size = data.image_size || data.defaults.image_size;
			data.image_limit = data.image_limit || data.defaults.image_limit;
		}

		function insertMap() {
			var id = jQuery('#agm_mh_map_id').val();

			if ( !id || jQuery(this).is(':disabled') ) {
				window.alert(l10nStrings.please_save_map);
				return false;
			}

			destroyMap();
			container.trigger( 'agm_map_insert', [id] );
		}

		function saveMap() {
			var status_pending = jQuery('#agm_save_status_pending'),
				status_done = jQuery('#agm_save_status_done'),
				title = jQuery( '#agm_map_title' ).val();

			if ( ! title ) {
				window.alert(l10nStrings.map_name_missing);
				return false;
			}

			status_pending.show();
			status_done.hide();

			var streetView = map.getStreetView();

			var width = jQuery('#agm_map_size_x').is(':enabled') ? jQuery('#agm_map_size_x').val() : 0;
			var height = jQuery('#agm_map_size_y').is(':enabled') ? jQuery('#agm_map_size_y').val() : 0;
			var alignment = jQuery('input.agm_map_alignment_element:checked').val();
			var request = {
				"action": "agm_save_map",
				"id": jQuery('#agm_mh_map_id').val(),
				"title": title,
				"height": height,
				"width": width,
				"zoom": map.getZoom(),
				"map_type": map.getMapTypeId(),
				"show_map": jQuery('#agm_map_show_map').is(':checked') ? 1 : 0,
				"show_posts": jQuery('#agm_map_show_posts').is(':checked') ? 1 : 0,
				"map_alignment": alignment,
				"show_markers": jQuery('#agm_map_show_markers').is(':checked') ? 1 : 0,
				"show_images": jQuery('#agm_map_show_images').is(':checked') ? 1 : 0,
				"image_size": jQuery('#agm_map_image_size').val(),
				"image_limit": jQuery('#agm_map_image_limit').val(),
				"show_panoramio_overlay": jQuery('#agm_map_show_panoramio_overlay').is(':checked') ? 1 : 0,
				"panoramio_overlay_tag": jQuery('#agm_map_panoramio_overlay_tag').val(),
				"street_view": streetView.getVisible() ? 1 : 0,
				"street_view_pos": streetView.getVisible() ? [streetView.getPosition().lat(), streetView.getPosition().lng()] : 0,
				"street_view_pov": streetView.getVisible() ? streetView.getPov() : 0,
				"post_ids": [],
				"markers": []
			};

			jQuery.each(jQuery('input.agm_mh_associated_id'), function ( idx, el ) {
				request.post_ids[request.post_ids.length] = jQuery(el).val();
			});
			if ( jQuery('#agm_map_size_associate').is(':checked') ) {
				request.post_ids[request.post_ids.length] = jQuery('#post_ID').val();
			}
			jQuery.each(_markers, function ( idx, marker ) {
				var position = marker.getPosition();
				if ( ! position ) { return true; }

				var data = {
					"title": marker.getTitle(),
					"body": marker._agmBody,
					"icon": marker.getIcon(),
					"position": [position.lat(), position.lng()]
				};
				request.markers[request.markers.length] = data;
			});
			jQuery(document).trigger('agm_google_maps-admin-save_request', [request]);

			jQuery.post(
				window.ajaxurl,
				request,
				function ( data ) {
					status_pending.hide();

					if ( ! data.status ) {
						window.alert( l10nStrings.map_not_saved );
					} else {
						status_done.show();
						jQuery('#agm_mh_map_id').val(parseInt(data.id));
						jQuery('#agm_insert_map').attr('disabled', false);

						jQuery( document ).trigger(
							'agm-map-saved',
							[parseInt(data.id)]
						);

						// Legacy trigger
						container.trigger(
							'agm_map_saved',
							[parseInt(data.id)]
						);

						window.setTimeout( function( ) { status_done.hide(); }, 3000 );
					}
				}
			);
		}

		function loadAssociatedPosts() {
			if ( !data.post_ids.length ) {
				jQuery('#agm_map_associated_posts').html('');
				jQuery('#agm_map_size_associate').attr('checked', false); // Not associated, default to false
				return false;
			}
			jQuery.post(
				window.ajaxurl,
				{
					"action": "agm_get_post_titles",
					"post_ids": data.post_ids
				},
				function ( data ) {
					if ( ! data.posts ) { return false; }

					var html = '<div class="agm_less_important">' +
						l10nStrings.already_associated_width +
						'<ul class="agm_associated_list">';
					var postId = jQuery('#post_ID').val();

					jQuery.each(data.posts, function ( idx, post ) {
						html +=  '<li>' + post.post_title + '</li>';
					});
					html += '</ul></div>';
					jQuery('#agm_map_associated_posts').html(html);
				}
			);
			return false;
		}

		function createMarkup() {
			var ids = '',
				sizes = '',
				top_container;

			jQuery.each( data.post_ids, function ( idx, id ) {
				ids += '<input type="hidden" class="agm_mh_associated_id" value="' + id + '" />';
			});

			jQuery.each(_imageSizes, function(idx, size ) {
				var selected = (size === data.image_size) ? 'selected="selected"' : '';
				sizes += '<option value="' + size + '" ' + selected + '>' + size + '</option>';
			});

			container.html(
				'<div class="agm-preview"></div>' +
				'<div class="agm-options" id="agm_mh_options" style="display:none"></div>'
			);

			container.find( '.agm-preview' ).append(
				'<div id="agm_mh_header">' +
					'<input type="hidden" id="agm_mh_map_id">' +
					'<div class="agm_mh_container">' +
						'<input type="text" size="32" class="widefat" id="agm_map_title" placeholder="' + l10nStrings.map_title + '" />' +
					'</div>' +
					'<div class="agm_mh_container marker-buttons">' +
						'<label for="agm_mh_location">' + l10nStrings.add_location + ' </label>' +
						'<input type="text" id="agm_mh_location" />' +
						'<button type="button" class="button-secondary action" id="agm_mh_location_trigger">' +
							'<i class="dashicons dashicons-location"></i> ' +
							l10nStrings.add +
						'</button>' +
						'<span id="agm_map_drop_marker_controls_container">' +
							'<button type="button" class="button-secondary action" id="agm_map_drop_marker"><i class="dashicons dashicons-location"></i> ' +
								l10nStrings.drop_marker +
							'</button>' +
							'<div id="agm_map_zoom_in_help" class="agm_less_important">' +
								l10nStrings.zoom_in_help +
							'</div>' +
						'</span>' +
					'</div>' +
				'</div>' +
				'<div class="map_preview"></div>' +
				'<div id="agm_mh_footer" class="agm_mh_footer">' +
					'<div class="agm_mh_container" id="agm_mh_markers">' +
					'</div>' +
				'</div>'
			);

			// Add the buttons
			top_container = container.parents( '.agm-editor' );
			top_container.find( '.buttons' ).append(
				'<div class="detail-buttons">' +
					(
						top_container.hasClass( 'agm-no-back' ) ?
						'' :
						'<button type="button" class="button-secondary action agm_go_back">' +
							'<i class="dashicons dashicons-arrow-left"></i> ' + l10nStrings.go_back +
						'</button>'
					) +
					'<span class="map-actions">' +
						'<span class="agm-status-text" id="agm_save_status_pending" style="display:none">' +
							l10nStrings.saving +
						'</span>' +
						'<span class="agm-status-text" id="agm_save_status_done" style="display:none">' +
							l10nStrings.saved +
						'</span>' +
						(allowInsertion ?
							'<button type="button" class="button-secondary action" id="agm_insert_map">' +
								'<i class="dashicons dashicons-pressthis"></i> ' + l10nStrings.insert +
							'</button>' :
							''
						) +
						'<button type="button" class="button-secondary action agm_map_options_open"'+
							'data-tooltip="' + l10nStrings.options_help + '" data-width="500"' +
						'>' +
							'<i class="dashicons dashicons-admin-generic"></i> ' +
							l10nStrings.options +
						'</button>' +
						'<button type="button" class="button-secondary action agm_map_options_close" style="display:none">' +
							'<i class="dashicons dashicons-yes"></i> ' +
							l10nStrings.apply_settings +
						'</button>' +
						'<button type="button" class="button-primary agm_save_map">' +
							'<i class="dashicons dashicons-yes"></i> ' + l10nStrings.save +
						'</button>' +
					'</span>' +
				'</div>'
			);

			// Create the options layer.
			container.find( '.agm-options' ).append(
				'<div>' +
					'<label for="agm_map_size_associate">' +
						'<input type="checkbox" id="agm_map_size_associate" value="' + jQuery( '#post_ID' ).val() + '" /> ' +
						l10nStrings.map_associate +
					'</label>' +
					'<div class="agm_less_important">' +
						l10nStrings.association_help +
					'</div>' +
					ids +
					'<div id="agm_map_associated_posts"></div>' +
				'</div>' +

				'<fieldset>' +
					'<legend>' + l10nStrings.map_size + '</legend>' +
					'<div>' +
						'<input type="text" size="3" id="agm_map_size_x" />' +
						'&nbsp;x&nbsp;' +
						'<input type="text" size="3" id="agm_map_size_y" />' +
					'</div>' +
					'<div>' +
						'<label>' +
							'<input type="checkbox" id="agm_map_size_default" /> ' +
							l10nStrings.use_default_size +
						'</label>' +
					'</div>' +
				'</fieldset>' +

				'<fieldset>' +
					'<legend>' + l10nStrings.map_appearance + '</legend>' +
					'<div>' +
						'<label>' +
							'<input type="checkbox" id="agm_map_show_posts" /> ' +
							l10nStrings.show_posts +
						'</label>' +
					'</div>' +
					'<div>' +
						'<label for="agm_map_show_map">' +
							'<input type="checkbox" id="agm_map_show_map" /> ' +
							l10nStrings.show_map +
						'</label>' +
					'</div>' +
					'<div>' +
						'<label for="agm_map_show_markers">' +
							'<input type="checkbox" id="agm_map_show_markers" /> ' +
							l10nStrings.show_markers +
						'</label>' +
					'</div>' +
				'</fieldset>' +

/*
				'<fieldset>' +
					'<legend>' + l10nStrings.images_strip + '</legend>' +
					'<div>' +
						'<label>' +
							'<input type="checkbox" id="agm_map_show_images" /> ' +
							l10nStrings.show_images +
						'</label>' +
					'</div>' +
					'<div>' +
						'<label for="agm_map_image_size">' +
							l10nStrings.image_size +
						'</label> ' +
						'<select id="agm_map_image_size">' +
							sizes +
						'</select>' +
					'</div>' +
					'<div>' +
						'<label for="agm_map_image_limit">' +
							l10nStrings.image_limit +
						'</label> ' +
						'<input type="text" size="2" id="agm_map_image_limit" value="' + data.image_limit + '" />' +
					'</div>' +
				'</fieldset>' +

				'<fieldset>' +
					'<legend>' + l10nStrings.panoramio_overlay + '</legend>' +
					'<div>' +
						'<label>' +
							'<input type="checkbox" id="agm_map_show_panoramio_overlay" /> ' +
							l10nStrings.show_panoramio_overlay +
						'</label>' +
					'</div>' +
					'<div>' +
						'<label for="agm_map_panoramio_overlay_tag">' +
							l10nStrings.panoramio_overlay_tag +
						'</label> ' +
						'<input type="text" size="12" id="agm_map_panoramio_overlay_tag" />' +
					'</div>' +
					'<div>' +
						'<small>' + l10nStrings.panoramio_overlay_tag_help + '</small>' +
					'</div>' +
				'</fieldset>' +

                */                
				'<fieldset>' +
					'<legend>' + l10nStrings.map_alignment + '</legend>' +
					'<div>' +
						'<label>' +
							'<input type="radio" id="agm_map_alignment_left" name="ama" class="agm_map_alignment_element" value="left" /> ' +
							'<img src="' + _agm.root_url + '/img/system/left.png" />' +
							l10nStrings.map_alignment_left +
						'</label>' +
					'</div>' +
					'<div>' +
						'<label>' +
							'<input type="radio" id="agm_map_alignment_center" name="ama" class="agm_map_alignment_element" value="center" /> ' +
							'<img src="' + _agm.root_url + '/img/system/center.png" />' +
							l10nStrings.map_alignment_center +
						'</label>' +
					'</div>' +
					'<div>' +
						'<label>' +
							'<input type="radio" id="agm_map_alignment_right" name="ama" class="agm_map_alignment_element" value="right" /> ' +
							'<img src="' + _agm.root_url + '/img/system/right.png" />' +
							l10nStrings.map_alignment_right +
						'</label>' +
					'</div>' +
				'</fieldset>' +
				'<p class="agm_less_important">Global defaults are configured in Settings &gt; WPMU DEV Maps</p>'
			);

			/**
			 * Filter: Allow Add-ons to add their own markup to the map dialog.
			 */
			jQuery( document ).trigger(
				'agm_google_maps-admin-markup_created',
				[container.find( '.agm-preview' ), data]
			);

			populate_map_details();
			populate_map_options();

			/**
			 * Filter: Allow Add-ons to add their own options to the map options.
			 */
			jQuery( document ).trigger(
				'agm_google_maps-admin-options_initialized',
				[container.find( '.agm-options' ), data]
			);

			wpmUi.upgrade_tooltips();
			wpmUi.upgrade_multiselect( container );

			jQuery( '.map_preview' )
				.width( 700 )
				.height( jQuery( window ).height() * 0.35 ); // Using a dynamic map height

			jQuery( document ).trigger(
				'agm_google_maps-admin-map_resized',
				[container, data]
			);
		}

		/**
		 * Set the values of the input fields in the Map-Details view.
		 */
		function populate_map_details() {
			jQuery( '#agm_mh_map_id' ).val( data.id );

			if ( ! data.id ) {
				jQuery( '#agm_insert_map' ).attr( 'disabled', true );
			}

			jQuery( '#agm_map_title' ).val( data.title );
		}

		/**
		 * Set the values of input fields in the Map-Options view.
		 */
		function populate_map_options() {
			var show_map = (data.show_map && parseInt(data.show_map) > 0),
				show_posts = (data.show_posts && parseInt(data.show_posts) > 0),
				show_markers = (data.show_markers && parseInt(data.show_markers) > 0),
				show_images = (data.show_images && parseInt(data.show_images) > 0),
				show_panoramio_overlay = data.show_panoramio_overlay;

			jQuery( '#agm_map_show_map' ).prop( 'checked', show_map );
			jQuery( '#agm_map_show_posts' ).prop( 'checked', show_posts );
			jQuery( '#agm_map_show_markers' ).prop( 'checked', show_markers );

			jQuery( '#agm_map_show_images' )
				.click( toggleShowImages )
				.prop( 'checked', show_images );

			toggleShowImages();

			// agm_map_show_panoramio_overlay
			jQuery( '#agm_map_show_panoramio_overlay' )
				.click( togglePanoramioLayer )
				.prop( 'checked', show_panoramio_overlay );

			jQuery( '#agm_map_panoramio_overlay_tag' )
				.change( changePanoramio )
				.val( data.panoramio_overlay_tag );

			switch ( data.map_alignment ) {
				case 'right':
					jQuery( '#agm_map_alignment_right' ).prop( 'checked', true );
					break;

				case 'center':
					jQuery( '#agm_map_alignment_center' ).prop( 'checked', true );
					break;

				default:
					jQuery( '#agm_map_alignment_left' ).prop( 'checked', true );
					break;
			}

			if ( ! data.width || ! parseInt( data.width ) ) {
				jQuery( '#agm_map_size_default' ).prop( 'checked', true );
			}

			toggleDefaultSize();
			jQuery.each(data.post_ids, function ( idx, el ) {
				if ( jQuery( '#post_ID' ).val() === el ) {
					jQuery( '#agm_map_size_associate' ).prop( 'checked', true );
				}
			});

			// Toggle post association/disassociation
			jQuery( '#agm_map_size_associate' ).click( togglePostAssociate );
		}

		/**
		 * Turns the Panoramio-Preview on or off
		 */
		function togglePanoramioLayer() {
			if ( typeof window.google.maps.panoramio !== 'object' ) { return false; }

			if ( jQuery( '#agm_map_show_panoramio_overlay' ).is( ':checked' ) ) {
				var tag = jQuery( '#agm_map_panoramio_overlay_tag' ).val();

				_panoramioLayer = new window.google.maps.panoramio.PanoramioLayer();
				if ( tag ) { _panoramioLayer.setTag( tag ); }

				_panoramioLayer.setMap( map );
			} else if ( _panoramioLayer ) {
				_panoramioLayer.setMap( null );
				_panoramioLayer = false;
			}
		}

		/**
		 * Toggles between the Map-Details and Map-Options view.
		 */
		function toggleOptions() {
			var opts = container.find( '.agm-options' ),
				preview = container.find( '.agm-preview' ),
				buttons = container.parents( '.agm-editor' ).find( '.detail-buttons' );

			if ( opts.is( ':visible' ) ) {
				jQuery( document ).trigger( 'agm_google_maps-admin-options_dialog-closed', [map] );

				// Toggle content.
				opts.hide();
				preview.show();

				// Toggle buttons.
				buttons.find( 'button' ).hide();
				buttons.find( '#agm_insert_map, .agm_go_back, .agm_map_options_open, .agm_save_map' ).show();
			} else {
				// Toggle content.
				opts.show();
				preview.hide();

				// Toggle buttons.
				buttons.find( 'button' ).hide();
				buttons.find( '.agm_map_options_close' ).show();
			}

			return false;
		}

		/**
		 * OPTIONS: Changes the state of the default-size input fields
		 */
		function toggleDefaultSize() {
			var width = data.defaults.width,
				height = data.defaults.height,
				use_default = jQuery( '#agm_map_size_default' ).is( ':checked' );

			if ( ! use_default ) {
				width = (parseInt( data.width ) > 0) ? data.width : width;
				height = (parseInt( data.height ) > 0) ? data.height : height;
			}

			jQuery( '#agm_map_size_x' ).val( width ).attr( 'disabled', use_default );
			jQuery( '#agm_map_size_y' ).val( height ).attr( 'disabled', use_default );
		}

		/**
		 * OPTIONS: Toggle the "Show images" flag
		 */
		function toggleShowImages() {
			var show_images = jQuery( '#agm_map_show_images' ).is( ':checked' );

			jQuery( '#agm_map_image_size' ).attr( 'disabled', ! show_images );
			jQuery( '#agm_map_image_limit' ).attr( 'disabled', ! show_images );
		}

		/**
		 * OPTIONS: Toggle the "Link to Post" flag.
		 */
		function togglePostAssociate() {
			var val = jQuery( '#agm_map_size_associate' ).val();

			if ( jQuery( '#agm_map_size_associate' ).is( ':checked' ) ) {
				if ( data.post_ids ) {
					data.post_ids.push( val );
				} else {
					data.post_ids = [val];
				}
			} else {
				jQuery( 'input.agm_mh_associated_id[value="' + val + '"]' ).remove();
				if ( data.post_ids) {
					data.post_ids = jQuery.grep(
						data.post_ids,
						function ( el ) { return el !== val; }
					);
				}
			}

			loadAssociatedPosts();
			return true;
		}

		/**
		 * OPTIONS: Change the value of the panoramio overlay tag
		 */
		function changePanoramio() {
			var tag = jQuery( '#agm_map_panoramio_overlay_tag' ).val();

			if ( ! _panoramioLayer ) { return true; }

			_panoramioLayer.setTag( tag );
		}

		function addMarkers() {
			if ( ! data.markers ) { return; }

			jQuery.each(data.markers, function ( idx, marker ) {
				addNewMarker(
					marker.title,
					new window.google.maps.LatLng(marker.position[0], marker.position[1]),
					marker.body,
					marker.icon
				);
			});
		}

		function dropNewMarker() {
			addNewMarker( 'Untitled marker', map.getCenter() );
			return false;
		}

		function addNewMarker( title, pos, body, icon ) {
			body = body || '';
			if ( undefined === icon || typeof icon !== 'string' ) {
				icon = _agm.root_url + '/img/system/marker.png';
			}
			if ( ! icon.match(/^https?:\/\//) ) {
				icon = _agm.root_url + '/img/' + icon;
			}
			var markerPosition = _markers.length;

			map.setCenter(pos);
			var marker = new window.google.maps.Marker({
				title: title,
				map: map,
				icon: icon,
				draggable: true,
				clickable: true,
				position: pos,
				zIndex: 100
			});
			var info = new window.google.maps.InfoWindow({
				"content": createInfoContent(title, body, icon, markerPosition),
				"maxWidth": 400
			});
			window.google.maps.event.addListener(marker, 'click', function( ) {
				info.open(map, marker);
			});
			marker._agmInfo = info;
			if ( data.snapping ) {
				window.google.maps.event.addListener(marker, 'dragend', function( ) {
					var geocoder = new window.google.maps.Geocoder();
					geocoder.geocode({'latLng': marker.getPosition()}, function ( results, status ) {
						if ( status === window.google.maps.GeocoderStatus.OK ) {
							marker.setPosition(results[0].geometry.location);
							marker.setTitle(results[0].formatted_address);
							info.setContent(createInfoContent(
								results[0].formatted_address,
								'',
								marker.getIcon(),
								markerPosition
							));
							updateMarkersListDisplay();
						} else {
							window.alert(l10nStrings.geocoding_error);
						}
					});
				});
			}
			marker._agmBody = body;
			_markers[markerPosition] = marker;
			jQuery(document).trigger('agm_google_maps-admin-marker_added', [marker, map]);
			map._agm_add_marker(marker);
			updateMarkersListDisplay();
		}

		function createInfoContent( title, body, icon, markerPosition ) {
			if ( undefined !== icon.url ) {
				icon = icon.url;
			} else {
				icon = icon.toString();
			}

			return '<div class="agm_mh_info_content">' +
				'<a href="#" class="agm_mh_info_icon_switch"><img agm:marker_id="' + markerPosition + '" src="' + icon + '" class="marker-icon-32" /><br /><small>Icon</small></a>' +
				'<div class="agm_mh_info_text">' +
					'<div><label for="">' + l10nStrings.title + '</label><br /><input type="text" agm:marker_id="' + markerPosition + '" class="agm_mh_info_title" value="' + title + '" /></div>' +
					'<label for="">' + l10nStrings.body + '</label><textarea class="agm_mh_info_body" agm:marker_id="' + markerPosition + '">' + body + '</textarea>' +
				'</div>' +
			'</div>';
		}

		function extractMarkerId( href ) {
			var id = href.replace(/[^0-9]+/, '');

			return parseInt(id);
		}

		function removeMarker() {
			var me = jQuery(this);
			var id = extractMarkerId(me.attr('href'));
			var marker = _markers.splice(id, 1);

			jQuery(document).trigger('agm_google_maps-admin-marker_removed', [marker[0]]);
			marker[0].setMap(null);
			map._agm_remove_marker(id);
			updateMarkersListDisplay();

			return false;
		}

		function centerToMarker() {
			var me = jQuery(this);
			var id = extractMarkerId(me.attr('href'));
			var m = _markers[id];

			map.setCenter(m.getPosition());

			if ( parseInt(data.street_view) ) {
				var panorama = map.getStreetView();
				panorama.setPosition(map.getCenter());
				panorama.setVisible(true);
			}

			return false;
		}

		function updateMarkersListDisplay() {
			var html = '<ul>';

			jQuery.each(_markers, function ( idx, mark ) {
				var item, icon = mark.getIcon();

				if ( undefined !== icon.url ) {
					icon = icon.url;
				} else {
					icon = icon.toString();
				}

				item = '<li>' +
					'<a href="#agm_mh_marker-' + idx + '" class="agm_mh_marker_item">' +
					'<img src="' + icon + '" class="marker-icon-32" />' +
					mark.getTitle() +
					'</a>' +
					'<span class="action">' +
						'<a href="#agm_mh_marker-' + idx + '" class="button-secondary agm_mh_marker_delete_item"><i class="dashicons dashicons-trash"></i> ' + l10nStrings.delete_item + '</a>' +
					'</span>' +
					'<span class="marker-infos">' +
						'Lat: <span class="click-sel">' + ( mark.position.lat().toFixed(5) ) + '</span> ' +
						'| Long: <span class="click-sel">' + ( mark.position.lng().toFixed(5) ) + '</span>' +
					'</span>' +
				'</li>';
				html += item;
			});
			html += '</ul>';
			jQuery('#agm_mh_markers').html(html);
		}

		function showMarkerIconsList() {
			var markerId = jQuery(this).find('img').attr('agm:marker_id');
			var parent = jQuery(this).parent('.agm_mh_info_content');
			var oldContent = parent.html();

			jQuery.post(
				window.ajaxurl,
				{"action": "agm_list_icons"},
				function ( data ) {
					var html = '';

					jQuery.each(data, function ( idx, el ) {
						html += '<a class="agm_new_icon" href="#"><img src="' + el + '" class="marker-icon-32" /></a> ';
					});

					parent.html(html);
					jQuery(".agm_new_icon").click(function () {
						var src = jQuery(this).find('img').attr('src');
						parent.html(oldContent);
						parent.find('a.agm_mh_info_icon_switch img').attr('src', src).addClass('marker-icon-32');
						//var iconImg = new window.google.maps.MarkerImage(src, new window.google.maps.Size(32, 32), null, null, new window.google.maps.Size(32, 32));
						var marker = _markers[markerId];
						marker.setIcon(src);
						updateMarkersListDisplay();
					});
				}
			);
		}

		function updateMarkerTitle() {
			var me = jQuery(this);
			var markerId = me.attr('agm:marker_id');
			var marker = _markers[markerId];
			marker.setTitle(me.val());
			marker._agmInfo.setContent(createInfoContent (marker.getTitle(), marker._agmBody, marker.getIcon(), markerId));
			updateMarkersListDisplay();
		}

		function updateMarkerBody() {
			var me = jQuery(this);
			var markerId = me.attr('agm:marker_id');
			var marker = _markers[markerId];
			marker._agmBody = me.val();
			marker._agmInfo.setContent(createInfoContent (marker.getTitle(), marker._agmBody, marker.getIcon(), markerId));
			updateMarkersListDisplay();
		}

		function searchNewLocation() {
			var loc = jQuery('#agm_mh_location').val();
			if ( ! loc ) {
				window.alert(l10nStrings.type_in_location);
				return false;
			}

			var geocoder = new window.google.maps.Geocoder();
			geocoder.geocode({'address': loc}, function ( results, status ) {
				if ( status === window.google.maps.GeocoderStatus.OK ) {
					addNewMarker(results[0].formatted_address, results[0].geometry.location);
				}
				else {
					window.alert(l10nStrings.geocoding_error);
				}
			});
		}

		function selectContainer(event ) {
			var range, el = jQuery(this)[0];

			if ( document.selection ) {
				range = document.body.createTextRange();
				range.moveToElementText(el);
				range.select();
			} else if ( window.getSelection ) {
				range = document.createRange();
				range.selectNode(el);
				window.getSelection().addRange(range);
			}
		}

		function init() {
			populateDefaults();
			createMarkup();
			map = new window.google.maps.Map(
				jQuery( '.map_preview', container ).get(0),
				{
					'zoom': parseInt(data.zoom) ? parseInt(data.zoom) : 1,
					'minZoom': 1,
					'center': new window.google.maps.LatLng(40.7171, -74.0039), // New York
					'mapTypeId': window.google.maps.MapTypeId[data.map_type]
				}
			);

			// Set initial location, if possible
			// and if not already queued to be set by markers
			if ( navigator.geolocation && ! data.markers ) {
				navigator.geolocation.getCurrentPosition(function(position ) {
					map.setCenter(
						new window.google.maps.LatLng(position.coords.latitude,position.coords.longitude)
					);
				});
			}

			addMarkers();
			togglePanoramioLayer();
			loadAssociatedPosts();

			if ( parseInt(data.street_view) ) {
				var panorama = map.getStreetView();
				var pos = null;

				if ( data.street_view_pos ) {
					pos = new window.google.maps.LatLng(data.street_view_pos[0], data.street_view_pos[1]);
				} else {
					pos = map.getCenter();
				}

				panorama.setPosition(pos);
				if ( data.street_view_pov ) {
					panorama.setPov({
						"heading": parseInt(data.street_view_pov.heading),
						"pitch": parseInt(data.street_view_pov.pitch),
						"zoom": parseInt(data.street_view_pov.zoom)
					});
				}
				panorama.setVisible(true);
			}
			jQuery(document).trigger('agm_google_maps-admin-map_initialized', [map, data]);

			jQuery( '#agm_map_drop_marker' ).click( dropNewMarker );
			jQuery( '.agm_go_back' ).click( destroyMap );
			jQuery( '#agm_insert_map' ).click( insertMap );
			jQuery( '.agm_map_options_open' ).click( toggleOptions );
			jQuery( '.agm_map_options_close' ).click( toggleOptions );
			jQuery( '.agm_save_map' ).click( saveMap );
			jQuery( '#agm_mh_location_trigger' ).click( searchNewLocation );
			jQuery( '#agm_map_size_default' ).click( toggleDefaultSize );

			jQuery( document ).on( 'click', '.click-sel', selectContainer );

			jQuery( 'body' ).on( 'click', '.agm_mh_marker_item', centerToMarker );
			jQuery( 'body' ).on( 'click', '.agm_mh_marker_delete_item', removeMarker );
			jQuery( 'body' ).on( 'click', '.agm_mh_info_icon_switch', showMarkerIconsList );
			jQuery( 'body' ).on( 'change', '.agm_mh_info_title', updateMarkerTitle );
			jQuery( 'body' ).on( 'change', '.agm_mh_info_body', updateMarkerBody );
		}

		function destroyMap() {
			destroy();
			container.trigger( 'agm_map_close' );
		}

		function destroy() {
			container.empty();
			container.parents( '.agm-editor' ).find( '.buttons .detail-buttons' ).remove();

			jQuery( 'body' ).off( 'click', '.agm_mh_marker_item' );
			jQuery( 'body' ).off( 'click', '.agm_mh_marker_delete_item' );
			jQuery( 'body' ).off( 'click', '.agm_mh_info_icon_switch' );
			jQuery( 'body' ).off( 'change', '.agm_mh_info_title' );
			jQuery( 'body' ).off( 'change', '.agm_mh_info_body' );
		}

		/**
		 * Uses global _agmMaps array to create the needed map objects.
		 * Deferres AgmMapHandler creation until Google Maps API is available.
		 */
		function waitForMaps() {
			if ( ! window._agmMapIsLoaded ) {
				window.setTimeout(waitForMaps, 100);
			} else {
				init();
			}
		}

		waitForMaps();


		// Return the public functions.
		return {
			'destroy': destroy
		};

	};

});
