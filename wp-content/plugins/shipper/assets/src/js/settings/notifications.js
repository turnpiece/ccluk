;(function( $ ) {

	var ERROR_TIMEOUT = 3000;

	/**
	 * Enables or disables email notifications
	 */
	function update_notifications_status() {
		var $send = $( '#shipper-email-notifications' ),
			action_pfx = 'shipper_notifications_'
		;
		if ( ! $send.length) { return false; }

		$.post(ajaxurl, {
			action: action_pfx + ($send.is( ':checked' ) ? 'enable' : 'disable'),
			_wpnonce: $send.val()
		}).done(function() {
			window.location.reload();
		});
	}

	/**
	 * Updates the shipper email options change button
	 */
	function update_options_change() {
		var $send = $( '.shipper-notification-options :checkbox' ),
			action_pfx = 'shipper_notifications_fail_only_'
		;
		if ( ! $send.length) { return false; }

		$.post(ajaxurl, {
			action: action_pfx + ($send.is( ':checked' ) ? 'enable' : 'disable'),
			_wpnonce: $send.val()
		}).done(function ( rsp ) {
			var status = ! ! (rsp || {}).success,
				msg = (rsp || {}).data || ''
			;

			if ( ! status) {
				$send.attr( 'checked', false );
				return false;
			}
		}).fail(function() {
				$send.attr( 'checked', false );
		});
	}

	/**
	 * Sends out an email addition request
	 */
	function add_email(e) {
		if (e && e.preventDefault) { e.preventDefault(); }
		if (e && e.stopPropagation) { e.stopPropagation(); }

		var $me = $( '#shipper-add-recipient' ),
			email = $me.find( ':input[type="email"]' ).val(),
			name = $me.find( ':input[type="text"]' ).val();
		$.post(ajaxurl, {
			action: 'shipper_notifications_add',
			name: name,
			email: email,
			_wpnonce: $me.find( 'button[data-add]' ).attr( 'data-add' )
		}).done(function ( rsp ) {
			var status = ! ! (rsp || {}).success,
				msg = (rsp || {}).data || ''
			;

			if ( ! status) {
				show_error( 'add', msg );
				return false;
			}

			show_success( 'add' );

			$( '.shipper-notifications-wrapper' ).html(
				$( msg ).find( '.shipper-notifications-wrapper' ).html()
			);
			$( '.shipper-notifications-status-notice' ).hide();
			hide_add();
		});

		return false;
	}

	/**
	 * Sends out an email removal request
	 */
	function rmv_email(e) {
		if (e && e.preventDefault) { e.preventDefault(); }
		if (e && e.stopPropagation) { e.stopPropagation(); }

		var $me = $( this ).closest( '.shipper-notification-item' ),
			email = $me.find( '.shipper-email[data-email]' ).attr( 'data-email' );
		$.post(ajaxurl, {
			action: 'shipper_notifications_rmv',
			email: email,
			_wpnonce: $me.find( 'a[data-rmv]' ).attr( 'data-rmv' )
		}).done(function( rsp ) {
			var status = ! ! (rsp || {}).success,
				msg = (rsp || {}).data || ''
			;

			if ( ! status) {
				show_error( 'rmv', msg );
				return false;
			}

			$( '.shipper-notifications-wrapper' ).html(
				$( msg ).find( '.shipper-notifications-wrapper' ).html()
			);
			$( '.shipper-notifications-status-notice' ).hide();
		});

		return false;
	}

	function show_error( cls, msg ) {
		$( '.shipper-recipient-notice' )
			.hide()
			.filter( '.shipper-' + cls )
				.find( 'p' ).text( msg ).end()
			.show();
		setTimeout(function() {
			$( '.shipper-recipient-notice' ).hide();
		}, ERROR_TIMEOUT);
	}

	function show_success( cls, msg ) {
		$( '.shipper-recipient-success' )
			.hide()
			.filter( '.shipper-' + cls )
				.find( 'p' ).text( msg ).end()
			.show();
		setTimeout(function() {
			$( '.shipper-recipient-success' ).hide();
		}, ERROR_TIMEOUT);
	}

	function reveal_add(e) {
		if (e && e.preventDefault) { e.preventDefault(); }
		if (e && e.stopPropagation) { e.stopPropagation(); }

		$( '#shipper-add-recipient' ).attr( 'aria-hidden', false );

		return false;
	}

	function hide_add(e) {
		if (e && e.preventDefault) { e.preventDefault(); }
		if (e && e.stopPropagation) { e.stopPropagation(); }

		var $me = $('#shipper-add-recipient'),
			$email = $me.find(':input[type="email"]'),
			$name = $me.find(':input[type="text"]');

		$me.attr('aria-hidden', true);
		$email.val('');
		$name.val('');

		return false;
	}

	function enter_to_add_email( e ) {
		var key = e.which;
		if ( 13 === key ) {
			add_email();
		}
	}

	function init() {
		if ($( '.shipper-page-settings-notifications' ).length) {
			$( '#shipper-email-notifications' ).on( 'change', update_notifications_status );
			$( document ).on( 'change', '.shipper-notification-options :checkbox', update_options_change );
			$( document ).on( 'click', '.shipper-notification-item a.shipper-rmv', rmv_email );

			$( document ).on( 'click', 'button.shipper-reveal-add', reveal_add );
			$( document ).on( 'click', '#shipper-add-recipient a[href="#close"]', hide_add );
			$( document ).on( 'click', '#shipper-add-recipient .shipper-cancel', hide_add );
			$( document ).on( 'click', '#shipper-add-recipient button.shipper-add', add_email );
			$( document ).on( 'keydown', '#shipper-add-recipient input', enter_to_add_email );

			$( document ).on('click', '.shipper-notifications-save', function() {
				window.location.reload();
			});
		}
	}

	$( init );
})( jQuery );
