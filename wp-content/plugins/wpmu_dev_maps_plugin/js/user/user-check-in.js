/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global _agmUci:false */
/*global _agmMaps:false */
/*global navigator:false */

/**
 * Plugin Name: User check-ins
 * Author:      Philipp Stracker (Incsub)
 *
 * Javascript component for the user-check-in addon.
 */

jQuery( function init_agm_user_check_in() {
	var doc = jQuery( document ),
		checkin_status = '',
		// By default the auto-checkin is activated.
		// During map_init it can be deactivated by any map.
		automatic_checkin = true,
		confirmation = null;


	// -----
	// ----- Start of map specific code.
	// -----


	/**
	 * Initialize the map.
	 * The data parameter contains all options that were specified for the map
	 * via the map-editor or as shortcode attribute.
	 * We add a "manual checkin" button below the map if admin wants it.
	 *
	 * @since  1.0.0
	 */
	var map_init = function map_init( event, map, data ) {
		// @see try_auto_checkin() - we can only DISABLE the feature in any map.
		automatic_checkin = (! data.automatic ? false : automatic_checkin);

		var container = jQuery( map.getDiv() ).closest( ".agm_google_maps" );
		if ( data.show_button && container.length ) {
			/*
			 * We add the button to #agm_mh_footer, because .agm_mh_container is
			 * overwritten each time a new marker is added (e.g. by the
			 * where-am-i addon)
			 */
			var footer = jQuery( '#agm_mh_footer', container );
			var checkin = jQuery( '<span class="agm-checkin-action"></span>' );

			// Display the button
			checkin.prependTo( footer );

			show_checkin_status();
		}
	};

	doc.bind( "agm_google_maps-user-map_initialized", map_init );


	// -----
	// ----- End of map specific code. Start of page global code.
	// -----


	/**
	 * Tries to check-in the current users position automatically by using
	 * the browser geolocation API. This function will evaluate settings of the
	 * browser, the addon and current user before actually submitting data.
	 *
	 * @since  1.0.0
	 */
	var try_auto_checkin = function try_auto_checkin() {
		if ( 'object' === typeof _agm && ! _agm.initialized ) {
			// When map is not yet loaded try again a bit later...
			window.setTimeout( try_auto_checkin, 100 );
			return false;
		}

		if ( !!! navigator.geolocation ) {
			// Browser cannot tell us the location. No point in continuing.
			return false;
		}

		if ( ! _agmUci.do_checkin ) {
			// Website does not want to know the location.
			return false;
		}

		if ( ! automatic_checkin ) {
			// The page explicitly did say that no background checkin is wanted.
			return false;
		}

		if ( 'on' === _agmUci.allow_checkin ) {
			submit_checkin();
		} else if ( 'ask' === _agmUci.allow_checkin ) {
			ask_user_confirmation();
		}
	};

	/**
	 * Do a manual checkin when the user clicks on the "Submit my location" button.
	 *
	 * @since  1.0.0
	 */
	var manual_checkin = function manual_checkin( ev ) {
		ev.stopPropagation();
		submit_checkin();
		return false;
	};

	/**
	 * The user wants to confirm the check-in first, so ask if we should submit
	 * data now. On approval this function will directly call "submit_checkin()".
	 *
	 * @since  1.0.0
	 */
	var ask_user_confirmation = function ask_user_confirmation() {
		/*
		 * We append some HTML to the current page that can be styled/positioned
		 * by the user via CSS to match the webpage theme and layout.
		 * The additional HTML code displays a confirmation message and two
		 * buttons from which the user can choose.
		 */
		var lang = _agmUci.lang;

		confirmation = jQuery( '<div class="agm-confirm"></div>' );
		var title = jQuery( '<div class="agm-confirm-title">' + lang.title + '</div>' );
		var msg = jQuery( '<div class="agm-confirm-msg">' + lang.ask_checkin + '</div>' );
		var btn_yes = jQuery( '<button type="button" class="agm-confirm-yes">' + lang.yes + '</button>' );
		var btn_no = jQuery( '<button type="button" class="agm-confirm-no">' + lang.no + '</button>' );
		var chk_ask = jQuery( '<input type="checkbox" id="agm-confirm-ask" />' );
		var lbl_ask = jQuery( '<label for="agm-confirm-ask" class="agm-confirm-ask">' + lang.remember + '</label>' );
		var btn_close = jQuery( '<span class="agm-confirm-close">&times;</span>' );
		var buttons = jQuery( '<div class="agm-confirm-buttons"></div>' );
		var modal = jQuery( '<div class="agm-modal"></div>' );
		var hook = jQuery( '.agm-map-uci-confirm' ).first();
		var body = jQuery( 'body' ).first();

		// Build the confirmation block.
		if ( ! _agmUci.guest ) {
			chk_ask.prependTo( lbl_ask );
			lbl_ask.appendTo( buttons );
		}

		btn_no.appendTo( buttons );
		btn_yes.appendTo( buttons );

		btn_close.appendTo( confirmation );
		title.appendTo( confirmation );
		msg.appendTo( confirmation );
		buttons.appendTo( confirmation );

		// Define event handlers.
		var cancel_checkin = function cancel_checkin( ev ) {
			ev.stopPropagation();
			confirmation.remove();
			modal.remove();

			if ( ! _agmUci.guest && chk_ask.is( ':checked' ) ) {
				// Change user setting to "never"
				set_user_permission( 'off' );
			}
		};

		var confirm_checkin = function confirm_checkin( ev ) {
			ev.stopPropagation();
			confirmation.remove();
			modal.remove();
			submit_checkin();

			if ( ! _agmUci.guest && chk_ask.is( ':checked' ) ) {
				// Change user setting to "always"
				set_user_permission( 'on' );
			}
		};

		// Attach events
		btn_close.click( cancel_checkin );
		btn_no.click( cancel_checkin );
		btn_yes.click( confirm_checkin );
		confirmation.on( 'agm:close_confirm', cancel_checkin );

		// Make sure we have a valid position to display the confirmation message
		if ( ! hook.length ) {
			hook = body;
		}
		hook.show();

		/*
		 * Modal is empty div and will not be visible by default. However, the
		 * theme can add CSS to make the modal div visible.
		 */
		modal.prependTo( hook );

		/*
		 * Append the confirmation block to the body.
		 * By default it will be appended to the
		 */
		confirmation.prependTo( hook );
	};

	/**
	 * This function is called by "try_auto_checkin()" and "ask_user_confirmation()"
	 * and will submit the current location to the website without doing any
	 * additional checking.
	 *
	 * @since  1.0.0
	 */
	var submit_checkin = function submit_checkin() {
		try {
			/**
			 * Send the current location data to WordPress.
			 *
			 * @since  1.0.0
			 */
			var do_submit_checkin = function do_submit_checkin(position) {
				var ajax_params = {
					"action": "agm_uci_checkin",
					"lat": position.coords.latitude,
					"lng": position.coords.longitude
				};

				// We notify the website via an ajax call of the checkin.
				jQuery.post(
					_agmUci.ajax_url,
					ajax_params,
					after_checkin
				);
			};

			/*
			 * Access the current location via browser API.
			 * The API will make an HTTP request to an webservice to get our
			 * current position. So it will take a short time until we know it.
			 */
			navigator.geolocation.getCurrentPosition( do_submit_checkin );

			checkin_status = 'send';
			show_checkin_status();

			// When checkin-process starts hide the confirmation box...
			confirmation.trigger( 'agm:close_confirm' );
		} catch(ex) {
			// In error case do nothing. The location simply was not submitted...
		}
	};

	/**
	 * Saves the user auto-checkin permission.
	 *
	 * @since 1.0.0
	 * @param string state New status. Possible values: 'on', 'off', 'ask'
	 */
	var set_user_permission = function set_user_permission( state ) {
		if ( jQuery.inArray( state, ['on', 'off', 'ask'] ) === -1 ) {
			// Invalid status.
			return false;
		}

		var ajax_params = {
			"action": "agm_uci_permission",
			"state": state
		};

		jQuery.post(
			_agmUci.ajax_url,
			ajax_params
		);
	};

	/**
	 * This is a callback that is executed after the current location was
	 * checked in via an ajax call.
	 *
	 * @since  1.0.0
	 * @param  string response Answer from WordPress.
	 */
	var after_checkin = function after_checkin( response ) {
		var resp = {};
		checkin_status = 'done';
		show_checkin_status();

		try {
			resp = jQuery.parseJSON( response );
		} catch( ignore ) {}

		if ( 'OK' === resp.status ) {
			add_marker( resp.data );
		}
	};

	/**
	 * Displays the checkin-status in case the manual-checkin is enabled.
	 *
	 * @since  1.0.0
	 */
	var show_checkin_status = function show_checkin_status() {
		var $checkin_box = jQuery( '#agm_mh_footer .agm-checkin-action' ),
			lang = _agmUci.lang;

		switch ( checkin_status ) {
			case 'done':
				$checkin_box.html( '<span class="agm-checkin-done">' + lang.status_saved + '</span>' );
				break;

			case 'send':
				$checkin_box.html( '<span class="agm-checkin-done">' + lang.status_sending + '</span>' );
				break;

			default:
				var $button = jQuery( '<a href="#" class="agm-checkin-add">' + lang.status_submit + '</a>' );
				$button.click( manual_checkin );
				$checkin_box.empty().append( $button );
				break;
		}
	};

	/**
	 * Displays a new checkin on the map.
	 *
	 * @since 1.0.0
	 */
	var add_marker = function add_marker( checkin ) {
		var map;

		// Add the checkin to all marker lists.
		for ( var i in _agmMaps ) {
			map = _agmMaps[i];
			if ( map.data['uci-automatic_checkin'] ) {
				jQuery( map.selector ).trigger( 'agm:update_marker', [checkin.marker.identifier, checkin.marker] );
			}
		}
	};

	/**
	 * Show form to edit a single marker.
	 *
	 * @since 1.0.0
	 */
	var load_editor = function load_editor( ev ) {
		ev.preventDefault();
		ev.stopPropagation();
		var me = jQuery( this ),
			map = me.parents( '.agm_google_maps' ).first(),
			item = jQuery( '.' + me.data( 'identifier' ), map ),
			footer = jQuery( '.agm_mh_footer', map ),
			id = me.data( 'id' );

		if ( me.hasClass( 'loading' ) ) {
			return false;
		}
		me.addClass( 'loading' );

		// Handle ajax response: item was saved.
		var update_checkins = function update_checkins( response ) {
			var resp = {}, marker, identifier;
			try {
				resp = jQuery.parseJSON( response );
			} catch( ignore ) {}

			switch ( resp.status ) {
				case 'OK':
					marker = resp.data.marker;
					identifier = marker.identifier;
					map.trigger( 'agm:update_marker', [identifier, marker] );
					break;

				case 'DEL':
					marker = resp.data.marker;
					identifier = marker.identifier;
					map.trigger( 'agm:remove_marker', [identifier] );
					break;
			}
		};

		// Click handler that hides the edit form again.
		var close_editor = function close_editor( ev ) {
			jQuery( '.agm-form', footer ).remove();
		};

		// Click handler that saves the changes and closes the form again.
		var save_changes = function save_changes( ev ) {
			var form = jQuery( '.agm-form', footer ),
				id = form.data( 'id' );

			var ajax_params = {
				"action": "agm_uci_checkin_save",
				"id": id
			};

			if ( jQuery( '.title', form ).length ) {
				ajax_params.title = jQuery( '.title', form ).val();
			}
			if ( jQuery( '.desc', form ).length ) {
				ajax_params.description = jQuery( '.desc', form ).val();
			}
			if ( jQuery( '.share', form ).length ) {
				ajax_params.share = jQuery( '.share', form ).val();
			}

			form.addClass( 'loading' );
			// We notify the website via an ajax call of the checkin.
			jQuery.post(
				_agmUci.ajax_url,
				ajax_params,
				update_checkins
			).always(close_editor);
		};

		// Click handler that deletes a checkin.
		var delete_checkin = function delete_checkin( ev ) {
			var form = jQuery( '.agm-form', footer ),
				id = form.data( 'id' );

			var ajax_params = {
				"action": "agm_uci_checkin_remove",
				"id": id
			};

			form.addClass( 'loading' );
			// We notify the website via an ajax call of the checkin.
			jQuery.post(
				_agmUci.ajax_url,
				ajax_params,
				update_checkins
			).always(close_editor);
		};

		// Handle ajax response: Display the editor to the user.
		var show_editor = function show_editor( response ) {
			var form = jQuery( response );
			close_editor();
			footer.prepend( form );

			form.on( 'click', '.agm-form-close, .agm-form-cancel', close_editor );
			form.on( 'click', '.agm-form-save', save_changes );
			form.on( 'click', '.agm-form-delete', delete_checkin );
			me.removeClass( 'loading' );
		};

		var ajax_params = {
			"action": "agm_uci_checkin_editor",
			"id": id
		};

		close_editor();
		// We notify the website via an ajax call of the checkin.
		jQuery.post(
			_agmUci.ajax_url,
			ajax_params,
			show_editor
		);

		return false;
	};

	// Click handler for the "Edit my checkin" link.
	doc.on( 'click', '.agm_google_maps .edit-marker', load_editor);

	// Try to check-in the current position.
	try_auto_checkin();



	// -----
	// ----- End of page global code.
	// -----

});