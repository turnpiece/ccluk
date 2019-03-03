/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global navigator:false */

jQuery(function () {
	function open_location_map_editor () {
		var lat = parseFloat( jQuery("#agm-latitude").val() ),
			lng = parseFloat( jQuery("#agm-longitude").val() ),
			root = jQuery( "#agm-gwa-bp_map_editor" ),
			el_map = jQuery( "#agm-gwa-bp_map_editor-map" ),
			center = new window.google.maps.LatLng( lat, lng ),
			height = parseInt( jQuery(window).height() / 3, 10 );

		if ( ! root.length ) {
			jQuery("body").append(
				'<div id="agm-gwa-bp_map_editor" style="display:none">' +
				'<div id="agm-gwa-bp_map_editor-map" style="width:100%; height:' + height + 'px" ></div>' +
				'</div>'
			);
			root = jQuery("#agm-gwa-bp_map_editor");
			el_map = jQuery("#agm-gwa-bp_map_editor-map");
		}

		window.tb_show('Edit Location', '#TB_inline?width=640&height=' + height + '&inlineId=agm-gwa-bp_map_editor');
		var map = new window.google.maps.Map(el_map.get(0), {
			"zoom": 12,
			"minZoom": 1,
			"center": center,
			"mapTypeId": window.google.maps.MapTypeId["ROADMAP"]
		});

		var marker = new window.google.maps.Marker({
			title: "Me",
			map: map,
			icon: _agm.root_url + '/img/system/marker.png',
			draggable: true,
			clickable: false,
			position: center
		});

		window.google.maps.event.addListener(marker, 'dragend', function() {
			var location = marker.getPosition();
			jQuery("#agm-latitude").val(location.lat());
			jQuery("#agm-longitude").val(location.lng());
			geolocate_coordinates(location.lat(), location.lng());
		});
		return false;
	}

	function geolocate_coordinates (lat, lng) {
		if ( undefined === window.google) { return false; }
		var geocoder = new window.google.maps.Geocoder();
		geocoder.geocode(
			{
				'latLng': new window.google.maps.LatLng(lat, lng)
			},
			function (results, status) {
				if ( status !== window.google.maps.GeocoderStatus.OK ) {
					return false;
				}

				geolocate_coordinates_ui(results[0].formatted_address);
			}
		);
	}

	// Right, so now we have coords - make them show nicely, and make it editable.
	function geolocate_coordinates_ui (address_val) {
		var root = jQuery("#agm-gwp-location_root"),
			address = root.find('label[for="agm-address"]'),
			link = root.find("#agm-gwp-formatted_address"),
			geocoder = new window.google.maps.Geocoder();

		if ( ! link.length ) {
			address.after('<a href="#change-address" id="agm-gwp-formatted_address" />');
			link = root.find("#agm-gwp-formatted_address");
			link.unbind("click").bind("click", open_location_map_editor);
		}
		link.text( address_val );
	}

	function _get_user_location (lat, lng) {
		var root = jQuery("#agm-gwp-location_root"),
			address = root.find('label[for="agm-address"]');

		jQuery("#agm-latitude").val(lat);
		jQuery("#agm-longitude").val(lng);
		address.hide();
		geolocate_coordinates(lat, lng);
	}

	function init_bp_form () {
		var lat = parseFloat( jQuery("#agm-latitude").val() ),
			lng = parseFloat( jQuery("#agm-longitude").val() );

		if ( !! lat && !! lng ) {
			return _get_user_location( lat, lng );
		}

		// No previously stored fields
		if ( !! navigator.geolocation ) {
			navigator.geolocation.getCurrentPosition(function(position) {
				_get_user_location( position.coords.latitude, position.coords.longitude );
			});
		}

		jQuery.ajaxSetup({
			"beforeSend": function( jqXHR, settings ) {
				if ( ! settings.data.match( /\baction=post_update\b/ ) &&
					! settings.data.match( /\baction=bpfb_update_activity_contents\b/ )
				) {
					return true;
				}

				var lat = parseFloat(jQuery("#agm-latitude").val()),
					lng = parseFloat(jQuery("#agm-longitude").val()),
					address = jQuery("#agm-address").val(),
					request = ( !! lat && !! lng ) ?
						'&agm-latitude=' + lat + '&agm-longitude=' + lng :
						'&agm-address=' + encodeURIComponent( address );

				settings.data += request;
			}
		});

		// Check for BP default theme JS... sigh
		if ( jQuery("#whats-new-options").length ) {
			// Assume default BP theme.
			jQuery("body").append(
				jQuery("<div id='agm-bp-height_test' />").append(jQuery("#whats-new-options").html())
			);

			var height = jQuery("#agm-bp-height_test").height();
			jQuery("#agm-bp-height_test").remove();

			var _int = window.setInterval(function () {
				var parent = jQuery('#whats-new-options[style*="height"]'); // Y u no use classes?
				if ( ! parent.length ) { return false; }
				if ( parent.height() <= 39 ) { return false; }
				if ( parent.height() > height ) {
					window.clearInterval( _int );
					return false;
				}
				parent.height( height );
			}, 500);
		}
	}

	function init () {
		if ( jQuery("#_wpnonce_post_update").length ||
			jQuery("#whats-new-post-object").length
		) {
			init_bp_form();
		}
	}


	init();
});

jQuery(document).bind(
	"agm_google_maps-user-adding_marker",
	function (e, marker, idx, map, original) {
		if (undefined === original) { return false; }
		if ( ! ( "disposition" in original ) ) { return false; }
		if ("activity_marker" !== original.disposition) { return false; }

		marker._agm_disposition = "activity_type";
	}
);
