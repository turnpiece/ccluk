;(function($) {

	var CHECK_TIMEOUT = 500;

	var _ticker;

	function is_working_dialog() {
		return ! ! $( '#shipper-preflight-check' ).is( '.shipper-working' );
	}

	/**
	 * Initiates preflight checks
	 */
	function begin_checks() {
		$( '#shipper-preflight-check' ).attr( 'aria-hidden', false );
		if ( ! is_working_dialog()) { return false; }
		start_receiving();
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
		if ( ! data['shipper-preflight']) {
			return;
		}
		var is_done = ! ! (data['shipper-preflight'] || {}).is_done,
			checks_sections = (data['shipper-preflight'] || {}).sections || {},
			dfr = new $.Deferred(),
			total_checks = 0,
			tasks = []
		;
		if (heartbeat_response.is_working) {
			return ping_or_finish( is_done );
		}

		heartbeat_response.is_working = true;
		$.each( checks_sections, function( section, data ) {
			tasks.push( data );
			total_checks += (data.checks || []).length;
		});
		tasks.reduce( function( promises, task ) {
			return promises.then( function() {
				return render_check_result( task.checks, task.title, task.is_done, total_checks );
			} );
		}, dfr ).then( function () {
			heartbeat_response.is_working = false;
			return ping_or_finish( is_done );
		});
		setTimeout( dfr.resolve );
		if ( ! is_done ) return _ticker.ping();
	}

	function ping_or_finish( is_done ) {
		if ( is_done && is_working_dialog() ) {
			stop_receiving(); // Yeah, we're done.
			window.location.reload();
			return false;
		} else _ticker.ping();
	}

	function preflight_cancel_popup( e ) {
		if (e && e.preventDefault) { e.preventDefault; }

		if ( !_ticker.is_running() ) {
			return stop_check_and_back();
		}

		var $dlg = $( '#shipper-preflight-cancel-dialog' ),
			dlg = new A11yDialog( $dlg.get( 0 ) )
		;
		dlg.show();

		$dlg.find( '.shipper-preflight-continue' )
			.off( 'click' )
			.on( 'click', function () { dlg.hide(); } );
		$dlg.find( '.shipper-preflight-cancel' )
			.off( 'click' )
			.on( 'click', stop_check_and_back );

		return false;
	}

	function stop_check_and_back(e) {
		if (e && e.preventDefault) { e.preventDefault; }

		var back_url = $( '.shipper-select-check a.shipper-button-back' ).attr( 'href' );
		if ( ! back_url) { return false; }
		stop_receiving(); // Stop checking, please.

		preflight_cancel(back_url);

		return false;
	}

	function preflight_cancel( url ) {
		$.post(ajaxurl, {
			action: 'shipper_preflight_cancel',
		}).done(function( rsp ) {
			var status = !!(rsp || {}).success;
			if (!!status) {
				window.location = url;
			} else {
				setTimeout(function () {
					preflight_cancel(url);
				}, 1000);
			}
		});
	}

	function complete_section( section ) {
		if ( is_section_done( section ) ) return true;
		var done = $('.sui-progress-bar').data( 'shipper-sections-done' ) || [];
		done.push( section );
		$( '.sui-progress-bar' ).data( 'shipper-sections-done', done );
	}

	function is_section_done( section ) {
		var done = $('.sui-progress-bar').data( 'shipper-sections-done' ) || [];
		return done.indexOf( section ) >= 0;
	}

	function render_check_result( checks, section, is_done, total ) {
		var check_percentage = 0
			checks_length = !!( checks || [] ).length,
			dfr = new $.Deferred()
		;
		if ( ! checks_length || is_section_done( section ) ) {
			return dfr.resolve();
		}

		render_checks_section( section, checks );
		checks.forEach(function ( check, idx, checks ) {
			check_percentage++;
			setTimeout(function () {
				update_progress_bar( {
					relative_progress: 100 / (total||1),
					section: section,
					check: check.title,
					status: check.status,
					is_done: is_done
				} );
				if ( idx === checks.length - 1 ) {
					setTimeout( function () {
						if ( !!is_done ) {
							complete_section( section );
						}
						dfr.resolve();
					}, CHECK_TIMEOUT);
				}
			}, check_percentage*CHECK_TIMEOUT);
		});

		return dfr;
	}

	function render_checks_section( section, checks ) {
		$( '.sui-progress-state-subtext' ).empty();
		$('.sui-progress-state-text').html( section );

		checks.forEach( function( check ) {
			var cid = get_check_node_id( check.title );
			var node = '<div class="shipper-check-node" id="' + cid + '">' +
				'<span class="shipper-check-node-title">' + check.title + '</span>' +
				'<i class="sui-icon-loader sui-loading"></i>' +
				'</div>'
			;
			$( '.sui-progress-state-subtext' ).append( node );
		} );
	}

	/**
	 * Updates progress bar UI
	 *
	 * @param {Object} state State object to update UI with.
	 */
	function update_progress_bar( state ) {
		var raw_progress = (parseInt((state || {}).progress, 10) || 0);
		if ( ! raw_progress && 'relative_progress' in state ) {
			var relprogress = parseInt( state.relative_progress, 10 ) || 0;
			raw_progress = parseInt( $( '.sui-progress-bar' ).data( 'raw_progress' ), 10 ) || 0;
			raw_progress += relprogress;
		}
		if ( raw_progress >= 100 ) raw_progress = 99;

		var progress = raw_progress + '%';
		var msg = (state || {}).msg || false;
		var section = (state || {}).section || false;
		var check = (state || {}).check || false;
		var status = (state || {}).status || false;
		var is_done = !!(state || {}).is_done;

		if (raw_progress > 0) {
			$( '.sui-progress-bar' ).data( 'raw_progress', raw_progress );
			$('.sui-progress-bar span').css('width', progress);
			$('.sui-progress-text span').text(progress);
		}

		if (section) {
			$('.sui-progress-state-text').html(section);
		}

		if (msg) {
			$('.sui-progress-state-subtext').text(msg);
		} else if ( !!check && !!status ) {
			update_check_node_status( check, status, is_done );
		}
	}

	function update_check_node_status( check, status, is_done ) {
		var cid = get_check_node_id( check ),
			$node = $( '#' + cid )
		;
		if ( ! $node.length ) { return false; }

		if ( is_done ) {
			$node.find( 'i' ).remove();
			$node.append(
				get_status_icon( status )
			);
		}
	}

	function get_check_node_id( check ) {
		return 'shipper-' + check.replace(/[^a-z]/ig, '').toLowerCase() + '-check';
	}

	function get_status_icon( status ) {
		var icon = '';
		if ( 'ok' === status ) {
			icon = 'sui-icon-check-tick sui-success';
		} else if ( 'warning' === status ) {
			icon = 'sui-icon-warning-alert sui-warning';
		} else {
			icon = 'sui-icon-warning-alert sui-error';
		}

		return '<i class="' + icon + '"></i>';
	}

	/**
	 * Re-initiates the preflight check
	 *
	 * @param {Object} e Event.
	 *
	 * @return bool
	 */
	function refresh(e) {
		if (e && e.preventDefault) { e.preventDefault; }

		$.post(ajaxurl, {
			action: 'shipper_preflight_restart',
		}).done(function( rsp ) {
			window.location.reload();
		});

		return false;
	}

	/**
	 * Dispatches checks event listeners and boots the checks
	 */
	function bootstrap_check_page() {
		_ticker = new window._shipper.Ticker( 'shipper-preflight' );
		// show_page( 'progress' );
		update_progress_bar({
			progress: 0
		});
		begin_checks();

		$( '.shipper-wizard-tab a[href="#reload"]' ).on( 'click', refresh );

		$( '.shipper-select-check a.shipper-button-back' ).on('click', preflight_cancel_popup);
		$( '.shipper-select-check .sui-progress-close' ).on('click', preflight_cancel_popup);
	}

	/**
	 * Boots the UI for "ready" state
	 */
	function bootstrap_ready_page() {
		$( '#shipper-migration-ready' ).attr( 'aria-hidden', false );
		$( '#shipper-migration-ready button[data-a11y-dialog-hide]' )
			.off('click')
			.on('click', function () {
				var $el = $( '#shipper-migration-ready [data-cancel-url]' ),
					url = $el.attr('data-cancel-url'),
					wpnonce = $el.attr('data-wpnonce')
				;
				if( url ) {
					$.post( ajaxurl, {
						action: 'shipper_reset_migration',
						_wpnonce: wpnonce
					}).always(function () {
						window.location = url;
					});
				}
			})
		;
	}

	$( window ).on('load', function() {
		if ($( '#shipper-preflight-check' ).length) {
			bootstrap_check_page();
		}
		if ($( '#shipper-migration-ready' ).length) {
			bootstrap_ready_page();
		}
	});
})(jQuery);
