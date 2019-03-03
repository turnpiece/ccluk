/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

/**
 * Plugin Name: Custom Icons
 * Author:      Philipp Stracker (Incsub)
 *
 * Javascript component for ADMIN page of the custom icons addon.
 */

// Need the timeout, because the HTML of the settings section is rebuilt to use the vertical navigation.
window.setTimeout( function init_icon_admin() {

	var win = jQuery( window ),
		table = jQuery( 'table.icons' ),
		data = jQuery( '.custom-icon-list' ),
		btn_add = jQuery( '.add-custom-icon' ),
		btn_media = jQuery( '.add-media-image' ),
		txt_url = jQuery( '.custom-icon-url' ),
		img_preview = jQuery( '.custom-icon-preview' ),
		the_list = [];

	var initialze_table = function initialze_table() {
		var ind, raw = data.val();

		try { the_list = jQuery.parseJSON( raw ); }
		catch( ignore ) { }

		if ( null === the_list || typeof the_list !== 'object' ) {
			the_list = [];
		}

		for ( ind = 0; ind < the_list.length; ind += 1 ) {
			add_icon_to_table ( the_list[ ind ] );
		}

		win.resize();
	};

	var serialize_icons = function serialize_icons() {
		data.val( JSON.stringify( the_list ) );
	};

	var show_preview = function show_preview( event ) {
		if ( txt_url.val() !== txt_url.data( 'last' ) ) {
			disable_save();
			txt_url.data( 'last', txt_url.val() );

			var url = txt_url.val();
			img_preview.attr( 'src', url );
		}
	};

	var check_preview = function check_preview( event ) {
		enable_save();
	};

	var add_icon = function add_icon( event ) {
		var url = txt_url.val(),
			ind;

		txt_url.val( '' );
		txt_url.data( 'last', '' );
		disable_save();

		maybe_add_url( url );
	};

	var maybe_add_url = function maybe_add_url( url ) {
		var ind;

		// Ignore empty URLs
		if ( ! (url || '').length ) {
			return false;
		}

		// Ignore non http/https URLs
		if ( ! url.match(/^https?:\/\//) ) {
			return false;
		}

		// Ignore duplicate URLs
		for ( ind = 0; ind < the_list.length; ind += 1 ) {
			if ( url === the_list[ ind ] ) {
				return false;
			}
		}

		add_icon_to_table( url );
		the_list.push( url );
		serialize_icons();
		win.resize();
	};

	var alternate_rows = function alternate_rows() {
		jQuery( 'tr.alternate', table ).removeClass( 'alternate' );
		jQuery( 'tr:nth-child(even)', table ).addClass( 'alternate' );
	};

	var add_icon_to_table = function add_icon_to_table( url ) {
		var td_img, td_url, td_size, td_action, dummy_img,
			row = jQuery( '<tr></tr>' ).appendTo( table );

		dummy_img = jQuery( '<img style="position: absolute;left:-9999999px;top:-99999999px" />' );
		td_img = jQuery( '<td style="text-align:center"><img src="" class="marker-icon-32" /></td>' );
		td_url = jQuery( '<td class="url">' + url + '</td>' );
		td_size = jQuery( '<td>...</td>' );
		td_action = jQuery( '<td><button type="button" class="button remove">Remove</button></td></tr>' );

		td_img.appendTo( row );
		td_url.appendTo( row );
		td_size.appendTo( row );
		td_action.appendTo( row );

		var preview_loaded = function preview_loaded( event ) {
			td_size.text( dummy_img.width() + ' x ' + dummy_img.height() );
			dummy_img.remove();
		};

		dummy_img.load( preview_loaded ).attr( 'src', url ).appendTo( jQuery( 'body' ) );
		jQuery( 'img', td_img ).attr( 'src', url );

		alternate_rows();
	};

	var remove_icon = function remove_icon( event ) {
		var me = jQuery( this ),
			row = me.parents( 'tr' ).first(),
			url = row.find( '.url' ).text(),
			ind;

		for ( ind = the_list.length - 1; ind >= 0; ind -= 1 ) {
			if ( url === the_list[ ind ] ) {
				the_list.splice( ind, 1 );
				break;
			}
		}

		row.remove();
		alternate_rows();
		serialize_icons();
		win.resize();
	};

	var disable_save = function disable_save( event ) {
		img_preview.hide();
		btn_add.addClass( 'disabled' ).prop( 'disabled', true );
		btn_add.text( btn_add.data('disabled') );
	};

	var enable_save = function enable_save( event ) {
		img_preview.show();
		btn_add.removeClass( 'disabled' ).prop( 'disabled', false );
		btn_add.text( btn_add.data('enabled') );
	};

	var media_library = function media_library( event ) {
		//Prepare frame
		var frame = window.wp.media({
			title : 'Choose an image',
			multiple : false,
			library : { type : 'image'},
			button : { text : 'Use icon' },
		});

		frame.on('close',function() {
			// get selections and save to hidden input plus other AJAX stuff etc.
			var $image = frame.state().get('selection').first(),
				image = ($image || {}).toJSON ? $image.toJSON() : {},
				img_url = image.url;

			maybe_add_url( img_url );
		});

		frame.open();
	};

	table.on( 'click', '.remove', remove_icon );
	btn_add.click( add_icon );
	btn_media.click( media_library );
	txt_url.keyup( show_preview );
	txt_url.change( show_preview );
	img_preview.load( check_preview );
	initialze_table();
	disable_save();

}, 50);
