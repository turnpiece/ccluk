;(function( $ ) {

	var _ticker;

	function stop_prop( e ) {
		if ( e && e.preventDefault ) e.preventDefault();
		if ( e && e.stopPropagation ) e.stopPropagation();
		return false;
	}

	function get_modals_class() {
		return '.shipper-preflight';
	}

	function get_modal( modal ) {
		var modal_class = modal ? [ get_modals_class(), modal ].join( '-' ) : get_modals_class(),
			$modal = $( modal_class );
		return $modal;
	}

	function open_modal( modal ) {
		var $modal = get_modal( modal );
		if ( ! $modal.length ) {
			console.log( 'No such modal', modal );
			return false;
		}

		$( get_modals_class() ).attr( 'aria-hidden', true );
		$modal.attr( 'aria-hidden', false );
	}

	/**
	 * Starts listening to heartbeat ticks
	 */
	function start_receiving() {
		_ticker.start(heartbeat_request, heartbeat_response);
	}

	/**
	 * Stop listening to heartbeat ticks
	 */
	function stop_receiving() {
		_ticker.stop();
	}

	/**
	 * Adds Shipper flag to heartbeat request
	 */
	function heartbeat_request( event, data ) {
		data['shipper-preflight'] = true;
	}

	/**
	 * Deals with heartbeat response from the back-end
	 */
	function heartbeat_response( event, data ) {
		var preflight = ( data || {} )['shipper-preflight'] || {};

		if ( preflight.is_done ) {
			stop_receiving();
			window.location.reload();
			return false;
		}

		update_preflight_state( preflight );
	}

	function update_preflight_state( data ) {
		var $preflight = get_modal( 'loading' ),
			sum = function( a, b ) { return a + b; },
			local_done = !! ( ( ( data || {} ).sections || {} ).system_checks || {} ).is_done,
			remote_done = !! ( ( ( data || {} ).sections || {} ).remote_checks || {} ).is_done,
			files_done = !! ( ( ( data || {} ).sections || {} ).files_check || {} ).is_done,
			percentage = [ local_done, remote_done, files_done ].reduce( sum ) * 33,
			activate_preflight_step = function( step ) {
				var $current = $preflight.find( '.shipper-step-active' ),
					$loader = $current.find( 'i' ),
					$next = $preflight.find( '[data-step="' + step + '"]' ),
					domain = $next.attr( 'data-domain' );
				$preflight.find( '.sui-progress-state .shipper-preflight-target' ).text( domain );
				$current.removeClass( 'shipper-step-active' );
				$next.addClass( 'shipper-step-active' ).append( $loader );
			};

		$preflight
			.find( '.sui-progress-bar span' ).width( percentage + '%' ).end()
			.find( '.sui-progress-text' ).text( percentage + '%' ).end();

		if ( local_done ) return activate_preflight_step( 'remote' );
		if ( remote_done ) return activate_preflight_step( 'sysdiff' );
	}

	function update_section_status( section ) {
		var sect_select = !! section ? '[data-section="' + section + '"]' : '[data-section]',
			$sections = $( sect_select );
		$sections.each( function () {
			var $s = $( this );
			if ( $s.find( '[data-section]' ).length ) {
				return update_parent_section( $s );
			} else {
				return update_normal_section( $s );
			}
		} );
		if ( $( '[data-section] i.sui-error, [data-section] .sui-tag-error' ).length ) {
			disable_migration_start();
		} else {
			enable_migration_start();
		}
	}

	function update_normal_section( $section ) {
		var $titles = $section.find( '.sui-accordion-item-title' ),
			warnings = $titles.find( 'i.sui-warning' ).length,
			errors = $titles.find( 'i.sui-error' ).length,
			service_errors = $section.find( '.shipper-service-error.sui-notice-error' ).length,
			issues = warnings + errors + service_errors,
			kind_class = !! ( service_errors + errors ) ? 'error' : ( warnings ? 'warning' : 'success' );

		$section.find( '.sui-accordion-item-title .sui-tag' )
			.removeClass( 'sui-tag-success sui-tag-warning sui-tag-error' )
			.addClass( 'sui-tag-' + kind_class )
			.text( issues );

		if ( issues ) {
			$section
				.addClass( 'shipper-has-issues' )
				.removeClass( 'shipper-no-issues' )
				.find( '>.sui-accordion-item-header' )
					.find( '.sui-accordion-item-title .sui-tag' ).show().end()
					.find( '.sui-accordion-item-title>.sui-icon-check-tick' ).hide();
		} else {
			$section
				.removeClass( 'shipper-has-issues' )
				.addClass( 'shipper-no-issues' )
				.find( '>.sui-accordion-item-header' )
					.find( '.sui-accordion-item-title .sui-tag' ).hide().end()
					.find( '.sui-accordion-item-title>.sui-icon-check-tick' ).show();
		}
		$section.find( '.shipper-rechecked-success .sui-icon-check-tick' ).show();
	}

	function update_parent_section( $parent ) {
		var $working = $parent.clone();
		$working.find( '[data-section]' ).each( function() {
			update_normal_section( $( this ) );
			$( this ).remove();
		} );
		var $own_titles = $working.find( '.sui-accordion-item-title' ),
			$all_titles = $parent.find( '.sui-accordion-item-title' ),
			own_warnings = $own_titles.find( 'i.sui-warning' ).length,
			all_warnings = $all_titles.find( 'i.sui-warning' ).length,
			own_errors = $own_titles.find( 'i.sui-error' ).length,
			all_errors = $all_titles.find( 'i.sui-error' ).length,
			own_ses = $working.find( '.shipper-service-error.sui-notice-error' ).length,
			all_ses = $parent.find( '.shipper-service-error.sui-notice-error' ).length,
			own_issues = own_errors + own_warnings + own_ses,
			all_issues = all_errors + all_warnings + all_ses,
			kind_class = !! ( all_ses + all_errors ) ? 'error' : ( all_warnings ? 'warning' : 'success' );

		$parent.find( '.sui-accordion-item-title .sui-tag' )
			.removeClass( 'sui-tag-success sui-tag-warning sui-tag-error' )
			.addClass( 'sui-tag-' + kind_class )
			.text( all_issues );

		if ( all_issues ) {
			$parent
				.addClass( 'shipper-has-issues' )
				.removeClass( 'shipper-no-issues' )
				.find( '>.sui-accordion-item-header' )
					.find( '.sui-accordion-item-title .sui-tag' ).show().end()
					.find( '.sui-accordion-item-title>.sui-icon-check-tick' ).hide()
		} else {
			$parent
				.removeClass( 'shipper-has-issues' )
				.addClass( 'shipper-no-issues' )
				.find( '>.sui-accordion-item-header' )
					.find( '.sui-accordion-item-title .sui-tag' ).hide().end()
					.find( '.sui-accordion-item-title>.sui-icon-check-tick' ).show()
		}

		if ( own_issues ) {
			$parent.find( '.sui-accordion-item-body>.sui-accordion>.sui-accordion-item' )
				.find( '.sui-accordion-item-title .sui-tag' ).show().end()
				.find( '.sui-accordion-item-title>.sui-icon-check-tick' ).hide();
		} else {
			$parent.find( '.sui-accordion-item-body>.sui-accordion>.sui-accordion-item' )
				.find( '.sui-accordion-item-title .sui-tag' ).hide().end()
				.find( '.sui-accordion-item-title>.sui-icon-check-tick' ).show();
		}
		$parent.find( '.shipper-rechecked-success .sui-icon-check-tick' ).show();
	}

	function disable_migration_start() {
		var $start = get_modal( 'results' ).find( '.shipper-migration-start' ),
			href = $start.attr( 'data-start' );
		$start
			.attr( 'href', '#start' )
			.addClass( 'shipper-disabled shipper-tooltip' )
			.on( 'click', stop_prop );
	}

	function enable_migration_start() {
		var $start = get_modal( 'results' ).find( '.shipper-migration-start' ),
			href = $start.attr( 'data-start' );
		$start
			.attr( 'href', href )
			.removeClass( 'shipper-disabled sui-tooltip' )
			.off( 'click', stop_prop );
	}

	function show_results() {
		open_modal( 'results' );
		setTimeout( update_section_status, 300 );
	}

	function track_preflight_progress() {
		if ( ! get_modal( 'loading' ).is( ':visible' ) ) {
			open_modal( 'loading' );
		}
		start_receiving();
	}

	function handle_reset_preflight( e ) {
		var $me = $( this ),
			section = $me.attr( 'data-section' );
		$.post( ajaxurl, {
			action: 'shipper_preflight_restart',
			section: section
		}, function () {
			window.location.reload();
		} );
		return stop_prop( e );
	}

	function handle_individual_check_reset( e ) {
		var $me = $( this ),
			content = $me.html(),
			check_id = $me.attr( 'data-check' ),
			$section = $me.closest( '[data-section]' ),
			$check = $me.closest( '[data-check_item="' + check_id + '"]' ),
			section = $section.attr( 'data-section' ),
			debounce_timeout = 'local' === section ? 3 : parseInt( _shipper.update_interval, 10 ),
			update_preflight = _.debounce( function() {
				$.post(
					ajaxurl,
					{ action: 'shipper_preflight_get_results_markup' },
					function ( resp ) {
						var status = ( resp || {} ).success,
							msg = ( resp || {} ).data;
						if ( ! status ) {
							return update_preflight();
						}

						var $new_section = $( msg ).find( '[data-section="' + section + '"]' );
						if ( check_id && $check.length ) {
							var $new_check = $new_section
									.find( '[data-check_item="' + check_id + '"]' ),
								$notification = $new_check
									.find( '.shipper-recheck-unsuccessful' ).clone();
							if ( $new_check.length ) {
								// Issue is still present
								$check.replaceWith( $new_check );
								SUI.suiTabs();
								if ( $notification.length ) {
									get_modal( 'results' )
										.find( '.shipper-recheck-active' ).remove().end()
										.append(
											$notification
												.removeClass( 'shipper-recheck-unsuccessful' )
												.addClass( 'shipper-recheck-active' )
										);
									$notification.show();
									setTimeout( function() {
										$notification.remove();
									}, 5000 );
								}
							} else {
								// We could not find this check - it's done now.
								$check
									.addClass( 'shipper-rechecked-success' )
									.find( '.sui-accordion-item-title i' )
										.removeClass( 'sui-icon-warning-alert' )
										.removeClass( 'sui-error sui-warning' )
										.addClass( 'sui-icon-check-tick' )
										.addClass( 'sui-success' )
										.end()
									.find( '.sui-accordion-open-indicator' ).remove().end()
									.find( '.sui-accordion-item-body' )
										.remove()
								;
							}
							update_section_status( section );
							$section.addClass( 'shipper-rechecked' );
						} else if ( $section.length ) {
							var $goods = $new_section
								.find( '>.sui-accordion-item-body>.sui-accordion' )
									.find( '.shipper-rechecked-success' );
							if ( $goods.length > 1 ) {
								// This is so we don't show tons of new successes.
								$goods.remove();
							}
							$section.replaceWith( $new_section );
							update_section_status();
							$section.addClass( 'shipper-rechecked' );
							SUI.suiTabs();
						} else {
							window.location.reload();
						}

						$me.html( content ).removeClass( 'sui-button-onload' );
						$( document ).on(
							'click',
							'.shipper-check-result a[href="#reload"]',
							handle_individual_check_reset
						);
						$( '.shipper-check-result a[href="#reload"]' ).attr( 'disabled', false );
				});
			}, debounce_timeout * 1000, true ); // #1 - do this immediately

		$me.html(
			'<i class="sui-icon-loader sui-loading"></i>&nbsp;'
		).addClass( 'sui-button-onload' );
		$( document ).off(
			'click',
			'.shipper-check-result a[href="#reload"]',
			handle_individual_check_reset
		);
		$( '.shipper-check-result a[href="#reload"]' ).attr( 'disabled', true );

		$.post( ajaxurl, {
			action: 'shipper_preflight_restart',
			section: section
		}, function ( resp ) {
			var status = ( resp || {} ).success,
				msg = ( resp || {} ).data;

			setTimeout( update_preflight, 3000 ); // #2 - BUT, wait a bit before we do this initially

		} );

		return stop_prop( e );
	}

	function handle_preflight_cancel( e ) {
		$.post( ajaxurl, { action: 'shipper_preflight_cancel' }, function () {
			window.location.search = '?page=shipper';
		} );
		return stop_prop( e );
	}

	function show_error( msg ) {
		msg = msg || 'Error!';
		console.log( msg );
	}

	function init() {
		if ( ! $( '.sui-box.shipper-select-check' ).length ) {
			return false;
		}
		_ticker = new window._shipper.Ticker( 'shipper-preflight' );

		$( '.shipper-reset-preflight' )
			.off( 'click' )
			.on( 'click', handle_reset_preflight );
		$( document ).on(
			'click',
			'.shipper-check-result a[href="#reload"]',
			handle_individual_check_reset
		);
		$( document ).on( 
			'shipper:preflight-files:status',
			function () { update_section_status(); }
		);

		$( '.shipper-preflight .sui-dialog-close' ).on( 'click', handle_preflight_cancel );
		$( document ).on(
			'click',
			'.shipper-preflight .shipper-progress button',
			handle_preflight_cancel
		);

		var callback = $( '.shipper-content.shipper-select-check-done' ).length
			? show_results
			: track_preflight_progress;
		setTimeout( callback );
	}
	$( init );
})( jQuery );
