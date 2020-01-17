;( function( $ ) {

	/**
	 * Building state flag
	 *
	 * @var {Boolean}
	 */
	var _building_paused = false;

	function stop_prop( e ) {
		if ( e && e.stopPropagation ) e.stopPropagation();
		if ( e && e.preventDefault ) e.preventDefault();
		return false;
	}

	function get_modal( modal ) {
		return $( '.shipper-package-build-' + modal );
	}

	function show_modal( modal ) {
		var $modal = get_modal( modal );
		if ( $modal.length ) {
			$( '.sui-dialog' ).attr( 'aria-hidden', true );
			$modal.attr( 'aria-hidden', false )
		}
		return $modal;
	}

	function send_request( action, obj ) {
		var dfr = new $.Deferred();
		obj = obj || {};
		obj.action = 'shipper_packages_build_' + action;
		$.post( ajaxurl, obj )
			.done( function( resp ) {
				var status = ( resp || {} ).success,
					data = ( resp || {} ).data;
				if ( status ) {
					return dfr.resolveWith( this, [data] );
				}
				return dfr.rejectWith( this, [data] );
			} )
			.fail( function( resp, type, error ) {
				var json = resp.responseJSON || {},
					msg = ( json || {} ).data || error;
				if ( typeof "" !== typeof msg ) {
					msg = error;
				}
				dfr.rejectWith( this, [msg] );
			} );
		return dfr.promise();
	}

	function update_progress( percentage, msg ) {
		percentage = ( parseInt( percentage, 10 ) || 1 ) + '%';
		get_modal( 'migration' )
			.find( '.shipper-progress-label' ).text( percentage ).end()
			.find( '.shipper-progress-bar' ).css( 'width', percentage ).end();
		if ( msg ) {
			get_modal( 'migration' ).find( '.shipper-progress-status' ).text( msg );
		}
	}

	function finalize_package() {
		var $active = get_modal( 'migration' )
			.find( '.shipper-progress-check' )
				.removeClass( 'active' )
				.last().addClass( 'active' );
		update_progress(
			50,
			$active.find( '.shipper-progress-title' ).text() + '...'
		);
		send_request( 'done' )
			.done( function() {
				update_progress( 100, 'Done' );
				window.location.reload();
			} )
			.fail( function( error ) {
				show_error_dialog( error );
			} );
	}

	function process_package() {
		if ( _building_paused ) {
			return false;
		}

		send_request( 'build' )
			.done( function( percentage ) {
				update_progress( percentage );
				if ( percentage < 100 ) {
					return setTimeout( process_package, 100 );
				}

				return setTimeout( finalize_package, 100 );
			} )
			.fail( function( error ) {
				show_error_dialog( error );
			} );
	}

	function start_building( e ) {
		_building_paused = false;

		show_modal( 'migration' );
		send_request( 'prepare' )
			.done( function( percentage ) {
				update_progress( percentage );
				process_package();
			} )
			.fail( function( error ) {
				show_error_dialog( error );
			} );
		return stop_prop( e );
	}

	function cancel_building( e ) {
		send_request( 'cancel' )
			.done( function() {
				window.location.reload();
			} )
			.fail( function( error ) {
				show_error_dialog( error );
			} );
		return stop_prop( e );
	}

	function continue_building( e ) {
		_building_paused = false;
		show_modal( 'migration' );
		process_package();
		return stop_prop( e );
	}

	function show_cancel_dialog( e ) {
		_building_paused = true;

		show_modal( 'cancel' )
			.find( '.shipper-goback' )
				.off( 'click' )
				.on( 'click', continue_building ).end()
			.find( '.shipper-cancel' )
				.off( 'click' )
				.on( 'click', cancel_building ).end()
		return stop_prop( e );
	}

	function show_error_dialog( error ) {
		show_modal( 'fail' )
			.find( '.shipper-error-message-wrapper' ).hide().end()
			.find( '.shipper-restart' )
			.off( 'click' )
			.on( 'click', start_building );

		if ( ( error || {} ).length ) {
			get_modal( 'fail' ).find( '.shipper-error-message-wrapper' ).show()
				.find( '.shipper-error-message' )
					.html(
						'<code>' + error + '</code>'
					);
		}
	}

	function init() {
		if ( ! $( '.shipper-packages-migration-main' ).length ) {
			return false;
		}
		$( document ).on(
			'shipper-package-build',
			start_building
		);
		$( document ).on(
			'click',
			'#shipper-package-build .shipper-cancel, #shipper-package-build .sui-dialog-close',
			show_cancel_dialog
		);
	}

	$( init );

} )( jQuery );
