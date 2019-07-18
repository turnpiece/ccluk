;(function( $, undefined ) {

	var MSG_TIMEOUT = 3000;


	function stop_prop( e ) {
		if ( e && e.preventDefault ) { e.preventDefault(); }
		if ( e && e.stopPropagation ) { e.stopPropagation(); }

		return false;
	}

	function reveal_add( e ) {
		$( '#shipper-add-user' ).attr( 'aria-hidden', false );
		return stop_prop( e );
	}

	function hide_add( e ) {
		var $me = $( '#shipper-add-user' ),
			$ins = $me.find( ':input' );

		$me.attr( 'aria-hidden', true );
		$ins.val( '' );

		return stop_prop( e );
	}

	function handle_verify_user( e ) {
		var $me = $( '#shipper-add-user' ),
			$input = $me.find( '#shipper-permissions-add' ),
			$nonce = $me.find( 'button.shipper-add' );

		$.post( ajaxurl, {
			action: 'shipper_permissions_verify',
			user_id: $input.val(),
			_wpnonce: $nonce.attr( 'data-add' )
		} ).done( function( rsp ) {
			var status = ! ! (rsp || {}).success,
				msg = (rsp || {}).data || '';

			hide_add();
			if ( ! status ) {
				return show_error( msg );
			}

			if ( msg ) {
				add_user_row( msg );
			}
		} );

		return stop_prop( e );
	}

	function remove_user( e ) {
		$( e.target ).closest( '.shipper-user-item' ).remove();
		return stop_prop( e );
	}

	function enter_to_verify_user ( e ) {
		var key = e.which;
		if ( 13 === key ) {
			handle_verify_user( e );
		}
	}

	function show_error( msg ) {
		var $error = $( '.shipper-permissions-notice.sui-notice-error' ),
			msg = msg || $error.find( 'p' ).text();
		$( '.shipper-permissions-notice' ).hide();
		$error
			.find( 'p' ).text( msg ).end()
			.show();
		setTimeout(function() {
			$error.hide();
		}, MSG_TIMEOUT);
	}

	function show_success( username ) {
		username = username || 'User';
		var $success = $( '.shipper-permissions-notice.sui-notice-success' ),
			msg = $success.find( 'p' ).text();
		$( '.shipper-permissions-notice' ).hide();
		$success
			.find( 'p' ).text( msg.replace( /\{\{USER\}\}/g, username ) ).end()
			.show();
		setTimeout(function() {
			$success.hide();
		}, MSG_TIMEOUT);
	}

	function add_user_row( row_html ) {
		var $me = $( '.shipper-page-settings-permissions .shipper-users-list' )
			$row = $( row_html ),
			user_id = $row.find( ':hidden' ).val(),
			username = $row.find( '.shipper-user' ).text();
		if ( ! user_id ) {
			return false;
		}
		if ( $me.find( ':hidden[value="' + user_id + '"]' ).length ) {
			return false;
		}
		$me.append( row_html );
		show_success( username );
	}

	function init() {
		if ( ! $( '.shipper-page-settings-permissions' ).length ) {
			return false;
		}


		var $add = $( '#shipper-permissions-add' ),
			wpnonce = $add.attr( 'data-wpnonce' );

		$add.SUIselect2({
			allowClear: true,
			dropdownCssClass: 'sui-select-dropdown',
			dropdownParent: $('#shipper-add-user'),
			ajax: {
				url: window.ajaxurl,
				type: "POST",
				data: function (params) {
					return {
						action: 'wdp-usersearch',
						hash: wpnonce,
						q: params.term,
					};
				},
				processResults: function (data) {
					return {
						results: data.data
					};
				},
			},
			templateResult: function (result) {
				if (typeof result.id !== 'undefined' && typeof result.label !== 'undefined') {
					return $(result.label);
				}
				return result.text;
			},
			templateSelection: function (result) {
				return result.display || result.text;
			},
		});

		$( document ).on( 'click', 'button.shipper-reveal-add', reveal_add );
		$( document ).on( 'click', '#shipper-add-user a[href="#close"]', hide_add );
		$( document ).on( 'click', '#shipper-add-user .shipper-cancel', hide_add );

		$( document ).on( 'click', '#shipper-add-user button.shipper-add', handle_verify_user );
		$( document ).on( 'keydown', '#shipper-add-user input', enter_to_verify_user );

		$( document ).on( 'click', '.shipper-user-item a.shipper-rmv', remove_user );
	}
	$( init );
} )( jQuery );
