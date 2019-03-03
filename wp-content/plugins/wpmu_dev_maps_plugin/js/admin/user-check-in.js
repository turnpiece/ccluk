/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

/**
 * Plugin Name: User check-ins
 * Author:      Philipp Stracker (Incsub)
 *
 * Javascript component for ADMIN page of the user-check-in addon.
 */

/**
 * Add functions to the profile page.
 *
 * @since  1.0.0
 */
jQuery(function init_profile_page() {
	if ( ! jQuery( 'body.profile-php' ).length ) {
		return false;
	}

	var checkins = jQuery( '.lst-checkins' );

	/*
	 * Switch a single checkin-item to readonly or edit mode.
	 * @param jquery el An element inside the checkin-item (i.e. the "edit" button)
	 * @param string mode Either 'readonly' or 'edit'.
	 */
	var switch_mode = function switch_mode( el, mode ) {
		var me = jQuery( el ),
			item = me.parents( '.item' ).first(),
			readonly = jQuery( '.read-only', item ),
			form = jQuery( '.form', item );

		if ( 'readonly' === mode ) {
			readonly.show();
			form.hide();
		} else if ( 'edit' === mode ) {
			readonly.hide();
			form.show();
		}
	};

	// Saves the current input values of the checkin-item.
	var save_state = function save_state( el ) {
		var me = jQuery( el ),
			item = me.parents( '.item' ).first(),
			form = jQuery( '.form', item ),
			input = jQuery( 'input, select', form );

		input.each(function () {
			jQuery( this ).data( 'val', jQuery( this ).val() );
		});
	};

	// Reverts the saved values of the checkin-item.
	var reset_state = function reset_state( el ) {
		var me = jQuery( el ),
			item = me.parents( '.item' ).first(),
			form = jQuery( '.form', item ),
			input = jQuery( 'input, select', form );

		input.each(function () {
			jQuery( this ).val( jQuery( this ).data( 'val' ) );
		});
	};

	// Clears all values of a single checkin item.
	var clear_values = function clear_values( el ) {
		var me = jQuery( el ),
			item = me.parents( '.item' ).first(),
			form = jQuery( '.form', item ),
			input = jQuery( 'input, select', form );

		input.each(function () {
			jQuery( this ).val( '' );
		});
	};

	// Updates the checkin-details in readonly mode to display the values from edit form.
	var update_readonly = function update_readonly( el ) {
		var me = jQuery( el ),
			item = me.parents( '.item' ).first(),
			readonly = jQuery( '.read-only', item ),
			form = jQuery( '.form', item ),
			input = jQuery( 'input, select', form );

		input.each(function() {
			var val = jQuery( this ).val(),
				sel = jQuery( this ).data( 'readonly' );
			jQuery( sel, readonly ).text( val );
		});

		update_preview.apply( item );
	};

	// Edit a single checkin: Change from read only to edit mode.
	var edit_item = function edit_item( ev ) {
		ev.preventDefault();

		switch_mode( this, 'edit' );
		save_state( this );
	};

	// Undo the changes made in a single item and change to read only mode again.
	var undo_edit_item = function undo_edit_item( ev ) {
		ev.preventDefault();

		switch_mode( this, 'readonly' );
		reset_state( this );
	};

	// Confirm changes of a single checkin and change to read only mode again.
	var confirm_edit = function confirm_edit( ev ) {
		ev.preventDefault();

		switch_mode( this, 'readonly' );
		update_readonly( this );
	};

	// Mark a single checkin item for deletion.
	var trash_item = function trash_item( ev ) {
		ev.preventDefault();

		var me = jQuery( this ),
			item = me.parents( '.item' ).first(),
			trash_flag = jQuery( '.trash-flag', item ),
			trash = jQuery( '.trash', item ),
			restore = jQuery( '.restore', item ),
			share = jQuery( '.share', item ),
			edit = jQuery( '.edit', item );

		save_state( this );
		clear_values( this );

		item.addClass( 'del' );
		trash_flag.val( 1 );
		trash.hide();
		restore.show();
		share.hide();
		edit.hide();
	};

	// Un-Mark a single checkin item from deletion.
	var restore_item = function restore_item( ev ) {
		ev.preventDefault();

		var me = jQuery( this ),
			item = me.parents( '.item' ).first(),
			trash_flag = jQuery( '.trash-flag', item ),
			trash = jQuery( '.trash', item ),
			restore = jQuery( '.restore', item ),
			share = jQuery( '.share', item ),
			edit = jQuery( '.edit', item );

		reset_state( this );

		item.removeClass( 'del' );
		trash_flag.val( 0 );
		trash.show();
		restore.hide();
		share.show();
		edit.show();
	};

	// Updates the sharing icon when the value in the select box is changed.
	var update_icon = function update_icon( ev ) {
		var me = jQuery( this ),
			item = me.parents( '.item' ).first(),
			share = jQuery( '.share', item ),
			icon = jQuery( '.share-icon > i', item ),
			share_icon = 'dashicons-visibility pub';

		if ( share.val() === 'member' ) {
			share_icon = 'dashicons-admin-users member';
		} else if ( share.val() === 'priv' ) {
			share_icon = 'dashicons-lock priv';
		}

		icon.removeClass();
		icon.addClass( 'dashicons ' + share_icon );
	};

	// Update the small preview map on the right side.
	var update_preview = function update_preview() {
		var me = jQuery( this ),
			preview_s = jQuery( '.the-preview-s', me ),
			preview_l = jQuery( '.the-preview-l', me ),
			lat = jQuery( '.read-only .lat', me ).first().text(),
			lng = jQuery( '.read-only .lng', me ).first().text(),
			preview_url = 'http://maps.googleapis.com/maps/api/staticmap' +
				'?maptype=roadmap' +
				'&markers=' + lat + ',' + lng +
				'&sensor=false';

		preview_s.html( '<img src="' + preview_url + '&zoom=12&size=128x64" />' );
		preview_l.html( '<img src="' + preview_url + '&zoom=14&size=240x195" />' );
	};

	// Initialize the map previews when page is loaded
	var init_previews = function init_previews() {
		jQuery( '.item', checkins ).each( update_preview );
	};

	checkins.on( 'click', '.edit', edit_item );
	checkins.on( 'click', '.cancel', undo_edit_item );
	checkins.on( 'click', '.save', confirm_edit );
	checkins.on( 'click', '.trash', trash_item );
	checkins.on( 'click', '.restore', restore_item );
	checkins.on( 'change', '.share', update_icon );

	init_previews();
});