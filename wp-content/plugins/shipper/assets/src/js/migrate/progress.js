/**
 * Migration progress related JS
 */
;(function($) {

	var _ticker;

	/**
	 * Shows appropriate screen
	 *
	 * @param {String} page Screen to show
	 */
	function show_page( page ) {
		$('.shipper-migration-content').hide();
		if ( 'done' === page ) {
			$( '.shipper-actions' )
				.find( ' .shipper-actions-left' ).hide().end()
				.find( ' .shipper-actions-right' ).show().end();
		} else {
			$( '.shipper-actions' )
				.find( ' .shipper-actions-left' ).show().end()
				.find( ' .shipper-actions-right' ).hide().end();
		}
		$('.shipper-migration-' + page + '-content').show();
	}

	function show_done_with_errors() {
		var $root = $('.shipper-migration-done-content');
		$.post(ajaxurl, {
			action: 'shipper_migration_errors'
		})
			.done(function(rsp) {
				$('.shipper-migration-done-content').replaceWith(rsp);
			})
			.always(function() {
				show_page('done');
			})
		;
	}

	/**
	 * Updates progress bar UI
	 *
	 * @param {Object} state State object to update UI with.
	 */
	function update_progress_bar( state ) {
		var raw_progress = (parseInt((state || {}).progress, 10) || 0);
		var progress = raw_progress + '%';
		var msg = (state || {}).msg || false;

		/*
		if (raw_progress > 0 && msg) {
			console.log(msg, progress);
		}
		*/

		if (raw_progress > 0) {
			$('.sui-progress-bar span').css('width', progress);
			$('.sui-progress-text span').text(progress);
		}
		if (msg) {
			$('.sui-progress-state-text').text(msg);
		}
	}

	/**
	 * Stop progress checking
	 */
	function stop_progress() {
		_ticker.stop();
	}

	/**
	 * Starts progress checking
	 */
	function start_progress() {
		_ticker.start(heartbeat_request, heartbeat_response, heartbeat_error);
	}

	function heartbeat_error() {
		var $el = $('.sui-progress-state-text'),
			msg = $el.attr( 'data-progress_stalled' )
		;
		if ( msg ) {
			$el.text( msg );
		}
	}

	/**
	 * Toggles the notifications message based on migration health status
	 */
	function toggle_migration_health_message( is_slow ) {
		var $msg = $( '.shipper-migration-health' ),
			is_visible = !!$msg.is( ':visible' )
		;
		if ( is_slow && !is_visible ) {
			return $msg.show();
		}

		if ( !is_slow && is_visible ) {
			return $msg.hide();
		}
	}

	/**
	 * Adds Shipper flag to heartbeat request
	 */
	function heartbeat_request( event, data ) {
		data['shipper-migration'] = true;
	}

	function heartbeat_response( event, data ) {
		if ( ! data['shipper-migration'] ) {
			return;
		}

		var is_done = !!(data['shipper-migration'] || {}).is_done,
			is_slow = !!(data['shipper-migration'] || {}).is_slow,
			percentage = parseInt((data['shipper-migration'] || {}).progress, 10) || 0,
			msg = (data['shipper-migration'] || {}).message || false,
			errors = (data['shipper-migration'] || {}).errors || []
		;
		update_progress_bar( { progress: percentage, msg: msg });

		toggle_migration_health_message( is_slow );

		if ( percentage >= 100 || is_done ) {
			if ( errors.length ) {
				show_done_with_errors();
			} else {
				show_page( 'done' );
			}
			stop_progress();
		} else {
			_ticker.ping();
		}
	}

	/**
	 * Pops the cancel migration modal
	 */
	function handle_stop_progress() {
		stop_progress();
		$('#shipper-migration-cancel').attr('aria-hidden', false);
	}

	/**
	 * Handle migration cancel button click
	 *
	 * @param {Object} e Click event
	 */
	function handle_migration_cancel( e ) {
		if (e && e.preventDefault) e.preventDefault();

		var $me = $(this),
			url = $(this).attr('href')
		;
		$me
			.attr('disabled', true)
			.html('&nbsp;<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>')
		;
		migration_cancel(url);

		return false;
	}

	function migration_cancel( url ) {
		$.post(ajaxurl, {
			action: 'shipper_cancel_migration'
		}).done(function (rsp) {
			var status = !!(rsp || {}).success;
			if (!!status) {
				window.location = url;
			} else {
				setTimeout(function () {
					migration_cancel(url);
				}, 1000);
			}
		});
	}

	/**
	 * Handle migration continue button click (in cancellation dialog)
	 *
	 * @param {Object} e Click event
	 */
	function handle_migration_continue( e ) {
		if (e && e.preventDefault) e.preventDefault();

		start_progress();
		$('#shipper-migration-cancel').attr('aria-hidden', true);

		return false;
	}

	/**
	 * Handle refresh page button click
	 *
	 * @param {Object} e Click event
	 */
	function handle_refresh_page( e ) {
		window.location.reload();
	}

	/**
	 * Boots the progress page
	 */
	function boot_migration_progress_page() {
		_ticker = new window._shipper.Ticker( 'shipper-migration' );

		show_page( 'progress' );
		update_progress_bar({
			progress: 0
		});

		$('.sui-progress-close').on('click', handle_stop_progress);
		$('a.shipper-migration-cancel').on('click', handle_migration_cancel);
		$('a.shipper-migration-continue').on('click', handle_migration_continue);
		$('button.shipper-refresh-page').on('click', handle_refresh_page);

		start_progress();
	}

	$(function() {
		if ($('.shipper-page-migrate-progress').length) {
			boot_migration_progress_page();
		}
	});
})(jQuery);
