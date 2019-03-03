;(function( $ ) {
	window._shipper = window._shipper || {};

	var UPDATE_INTERVAL = window._shipper.update_interval || 'fast';
	var _timers = {};
	var _request_lock = false;

	var Ticker = function( name ) {

		var _request_lock = false;
		var _is_running = false;

		/**
		 * Starts the heartbeat update cycle with given callbacks
		 *
		 * @param {Function} request_callback On request.
		 * @param {Function} response_callback On response.
		 * @param {Function} error_callback On error (optional).
		 */
		function start(request_callback, response_callback, error_callback) {
			stop();

			_is_running = true;

			$(document).on('heartbeat-send.' + name, function( event, data ) {
				report_elapsed_time( name );
				clearTimeout(_request_lock);
				request_callback( event, data );
			});

			$(document).on('heartbeat-tick.' + name, function ( event, data ) {
				if ( ! data[ name ] ) {
					return;
				}

				start_timer( name );
				clearTimeout(_request_lock);
				response_callback( event, data );
			});

			if ( error_callback ) {
				$(document).on('heartbeat-error', error_callback);
			}

			$(document).on('heartbeat-tick.wp-auth-check', check_auth_context);

			update_interval( UPDATE_INTERVAL );

			// Send first tick immediately
			if (((wp || {}).heartbeat || {}).connectNow) {
				wp.heartbeat.connectNow();
			}
		}

		/**
		 * Filthy hack around WP login iframe
		 *
		 * Because the entire re-logging thing is as filthy as it gets anyways.
		 */
		function check_auth_context( event, data ) {
			// Did we just get logged out?
			if ( ! ( 'wp-auth-check' in data ) ) {
				return false;
			}
			if (data['wp-auth-check']) {
				return true;
			}
			console.log('We just got logged out :(');

			var parent = window,
				$root = $('#wp-auth-check-wrap'),
				$iframe = $root.find("iframe")
			;
			if ( $root.length && !$root.is('.shipper-bound') && $iframe.length ) {
				$root.addClass('shipper-bound');
				$iframe.on('load', function() {
					var $body = $(this).contents().find('body');
					if ( $body.is('.interim-login-success') ) {
						setTimeout(function() {
							parent.location.reload();
						});
					}
				});
			}
		}

		/**
		 * Stops the heartbeat update
		 */
		function stop() {
			_is_running = false;

			$(document).off('heartbeat-send.' + name);
			$(document).off('heartbeat-tick.' + name);
			$(document).off('heartbeat-tick.wp-auth-check', check_auth_context);

			update_interval( 'standard' );
		}

		/**
		 * Sets heartbeat update interval
		 *
		 * @param {Number|String} interval Interval to set.
		 */
		function update_interval( interval ) {
			var reset_interval = 'standard' === interval,
				num = get_interval_value( interval )
			;

			clearTimeout(_request_lock);

			if ( ((wp || {}).heartbeat || {}).interval ) {
				wp.heartbeat.interval( interval, 10 );
			}
			if ( reset_interval ) {
				// No need to re-apply timeout:
				// whatever the heartbeat was doing is fine.
				return;
			}

			if (num) {
				_request_lock = setTimeout(function() {
					wp.heartbeat.connectNow();
				}, (num * 1000) + 500);
			}
		}

		/**
		 * Gets integer interval value, in seconds
		 *
		 * @param {Number|String} interval Raw interval.
		 *
		 * @return {Number}
		 */
		function get_interval_value( interval ) {
			if ( 'standard' === interval ) return 60;
			if ( 'fast' === interval ) return 5;

			var num = parseInt( interval, 10 );
			return num ? num : get_interval_value( 'standard' );
		}

		/**
		 * Starts internal timer
		 *
		 * @param {String} timer Timer to start.
		 */
		function start_timer( timer ) {
			_timers[timer] = (new Date()).getTime();
		}

		/**
		 * Reports elapsed interval time for a timer
		 *
		 * @param {String} timer Timer to report.
		 */
		function report_elapsed_time( timer ) {
			var end = (new Date()).getTime(),
				started = _timers[timer] || end,
				secs = (end - started) / 1000,
				elapsed = secs <= 0 ? 'immediate' : secs + 's'
			;
			console.log('Timer ' + timer + ': ' + elapsed);
		}

		return {
			start: start,
			stop: stop,
			is_running: function () {
				return !!_is_running;
			},
			ping: function () {
				update_interval( UPDATE_INTERVAL );
			}
		};
	};

	window._shipper.Ticker = Ticker;
})( jQuery );
