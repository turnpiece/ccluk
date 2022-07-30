<?php
/**
 * Shipper tasks: API abstraction
 *
 * Provides helpers for communicating with the Shipper Hub API.
 * All Shipper API tasks will inherit from this.
 *
 * @package shipper
 */

/**
 * API task abstraction class
 */
abstract class Shipper_Task_Api extends Shipper_Task {

	const ERR_CONNECTION = 'hub_connection_error';
	const ERR_REQFORMAT  = 'hub_request_format_error';
	const ERR_RESPFORMAT = 'hub_response_format_error';
	const ERR_SERVICE    = 'hub_service_response_error';

	const METHOD_GET  = 'GET';
	const METHOD_POST = 'POST';

	const LOG_TIME_CUTOFF = 5;

	/**
	 * Holds API model instance
	 *
	 * @var object Shipper_Model_Api instance
	 */
	protected $model;

	/**
	 * Constructor
	 *
	 * Sets up API model instance
	 */
	public function __construct() {
		$this->model = new Shipper_Model_Api();
	}

	/**
	 * Gets service URL
	 *
	 * @return string
	 */
	public function get_url() {
		$base = defined( 'WPMUDEV_CUSTOM_API_SERVER' ) && WPMUDEV_CUSTOM_API_SERVER
			? WPMUDEV_CUSTOM_API_SERVER
			: 'https://wpmudev.com/';

		$service_url = trailingslashit( $base ) . 'api/shipper/' . $this->get_namespace();

		/**
		 * DEV API service URL
		 *
		 * @param string $service_url Shipper DEV API service URL.
		 *
		 * @return string
		 */
		return apply_filters(
			'shipper_api_service_url',
			$service_url
		);
	}

	/**
	 * Gets service namespace
	 *
	 * @return string
	 */
	public function get_namespace() {
		return 'v1.0';
	}

	/**
	 * Checks if we had previous communication errors.
	 *
	 * If we did, checks if we're to try again at this time or not.
	 * If not, adds an error describing the issue.
	 *
	 * @return bool
	 */
	public function check_api_communication_health_state() {
		$model    = new Shipper_Model_Api();
		$previous = $model->get_previous_api_fails();

		if ( empty( $previous ) || count( $previous ) <= 2 ) {
			return true;
		}

		$last_err = end( $previous );
		if ( empty( $last_err ) ) {
			return true;
		}

		$backoff = $this->get_api_backoff_time( $previous, 2 );
		$backoff = apply_filters( 'shipper_backoff', $backoff );
		if ( $last_err + $backoff >= time() ) {
			Shipper_Helper_Log::write(
				sprintf(
					'Backoff cooldown: %1$s ( %2$s remaining )',
					$backoff,
					( ( $last_err + $backoff ) - time() )
				)
			);
			$this->add_error(
				self::ERR_CONNECTION,
				sprintf(
					/* translators: %d: error message code */
					__( 'Too many communication errors, cooldown %ds', 'shipper' ),
					$backoff
				)
			);

			return false;
		}

		// If we're at max backoff, and it's past that - reset.
		if ( $backoff >= $this->get_max_backoff() ) {
			$model->reset_api_fails();
		}

		return true;
	}

	/**
	 * Get api backoff time.
	 *
	 * @param array $errors error messages.
	 * @param int   $cutoff cut off time.
	 *
	 * @return int|mixed
	 */
	public function get_api_backoff_time( $errors, $cutoff = 1 ) {
		if ( empty( $errors ) || count( $errors ) <= $cutoff ) {
			return 0;
		}
		$err_exponent = count( $errors ) - min( $cutoff, count( $errors ) );
		$backoff      = min( pow( 5, $err_exponent ), $this->get_max_backoff() );

		return $backoff;
	}

	/**
	 * Get max backoff time in seconds.
	 *
	 * @return float|int
	 */
	public function get_max_backoff() {
		return HOUR_IN_SECONDS;
	}

	/**
	 * Gets DEV Shipper API service response
	 *
	 * @param string $endpoint Endpoint to ping.
	 * @param string $method Request type, post or get, defaults to GET.
	 * @param array  $request_data Optional request data.
	 *
	 * @return array
	 */
	public function get_response( $endpoint, $method = false, $request_data = array() ) {
		if ( Shipper_Model_Env::is_phpunit_test() && ! has_filter( 'pre_http_request' ) ) {
			// We are in test env and we're _not_ mocking request.
			// This'll fail anyway, so may as well save some time.
			return array();
		}

		// So here we check if we had previous failed API calls.
		// If we did and we're not ready to deal, back off.
		if ( ! add_filter(
			'shipper_check_api_communication_health_state',
			$this->check_api_communication_health_state()
		) ) {
			return array();
		}

		$url     = trailingslashit( $this->get_url() ) . $endpoint;
		$data    = array();
		$timeout = 30; // In seconds.

		if ( 'migration-start' === $endpoint ) {
			// Starting a migration can take a while.
			// Let's extend the timeout for that one.
			$timeout = 95;
		}

		$args = array(
			'timeout'   => $timeout,
			'sslverify' => defined( 'WPMUDEV_API_SSLVERIFY' ) ? WPMUDEV_API_SSLVERIFY : true,
			'headers'   => array(
				'user-agent' => shipper_get_user_agent(),
			),
		);

		$model = new Shipper_Model_Api();
		$key   = $model->get( 'api_key' );
		if ( ! empty( $key ) ) {
			$args['headers']['Authorization'] = sprintf(
				'Basic %s',
				$key
			);
		}

		if ( ! empty( $request_data ) ) {
			if ( self::METHOD_POST === $method ) {
				$args['body']   = $request_data;
				$args['method'] = 'POST';
			} else {
				$args['method'] = 'GET';
				foreach ( $request_data as $key => $val ) {
					$url = add_query_arg( $key, $val, $url );
				}
			}
		}

		/**
		 * Pre-process the service call args
		 *
		 * @param array $args Remote request arguments.
		 * @param string $endpoint Requested endpoint.
		 *
		 * @return array
		 */
		$args = apply_filters(
			'shipper_api_request_args',
			$args,
			$endpoint
		);

		$data = $this->get_cached_api_response( $endpoint, $args );
		if ( false === $data ) {
			$data = $this->get_api_response( $url, $endpoint, $args );
			$model->set_cached_api_response( $endpoint, $args, $data );
		}

		return $data;
	}

	/**
	 * Gets maximum API cache time for this task
	 *
	 * Is to be overridden as needed in concrete API task implementations.
	 *
	 * @return int
	 * @since v1.0.3
	 */
	public function get_api_cache_ttl() {
		return 120;
	}

	/**
	 * Whether or not to cache this API request result
	 *
	 * Is to be overridden as needed in concrete API task implementations.
	 *
	 * @return bool
	 * @since v1.0.3
	 */
	public function is_cacheable() {
		return true;
	}

	/**
	 * Whether to return raw response or not
	 *
	 * Is to be overridden as needed in concrete API task implementations.
	 *
	 * @since 1.2
	 *
	 * @return bool
	 */
	public function return_raw_response() {
		return false;
	}

	/**
	 * Gets cached API request data.
	 *
	 * Proxies the API model cache getter to inject the TTL data.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $payload API request params.
	 *
	 * @return false|array Cached data, or (bool)false on failure.
	 * @since v1.0.3
	 */
	public function get_cached_api_response( $endpoint, $payload ) {
		if ( ! $this->is_cacheable() ) {
			return false;
		}

		$model = new Shipper_Model_Api();

		return $model->get_cached_api_response(
			$endpoint,
			$payload,
			$this->get_api_cache_ttl()
		);
	}

	/**
	 * Clears cached API response
	 *
	 * Proxied the API model cache clearing so it can be called from the
	 * concrete implementations without instantiating the model.
	 *
	 * @param string      $endpoint API endpoint.
	 * @param array|false $payload Optional payload.
	 *
	 * @since v1.0.3
	 */
	public function clear_cached_api_response( $endpoint, $payload = false ) {
		$model = new Shipper_Model_Api();

		return $model->clear_cached_api_response( $endpoint, $payload );
	}

	/**
	 * Records a non-success response from a task implementation
	 *
	 * Clears endpoint cache so we're primed for next attempt.
	 * Also records an API call error if needed.
	 * This is so that the API protection mechanism can kick in.
	 * Record the API call error only if needed - as in, we didn't have an API error earlier.
	 * This is so we don't double down on API errors.
	 * Non-http errors is optional.
	 *
	 * @param string $endpoint API endpoint.
	 * @param string $error_type Error suffix to be added to error type.
	 * @param string $error_message Optional error message.
	 *
	 * @since v1.0.3
	 */
	public function record_non_success( $endpoint, $error_type, $error_message ) {
		if ( $this->get_constants()->get( 'RECORD_NONHTTP_ERRORS' ) && ! $this->has_errors() ) {
			// Record API failure if we have't errored out earlier.
			$model = new Shipper_Model_Api();
			$model->record_api_fail();
		}
		$this->add_error( $error_type, $error_message );
		$this->clear_cached_api_response( $endpoint );
	}

	/**
	 * Records a success response from task implementation
	 *
	 * Clears previous API fails, optionally.
	 *
	 * @param string $endpoint API endpoint.
	 *
	 * @since v1.0.3
	 */
	public function record_success( $endpoint ) {
		if ( $this->get_constants()->get( 'RECORD_NONHTTP_ERRORS' ) && ! $this->has_errors() ) {
			// Record API failure if we have't errored out earlier.
			$model = new Shipper_Model_Api();
			$model->reset_api_fails();
		}
	}

	/**
	 * Gets constants model instance
	 *
	 * Either an overridden constants instance, as used in tests,
	 * or a brand new object instance
	 *
	 * @return object Shipper_Model_Constants_Shipper instance
	 * @since v1.0.3
	 */
	public function get_constants() {
		if ( isset( $this->constants ) ) {
			return $this->constants;
		}

		return new Shipper_Model_Constants_Shipper();
	}

	/**
	 * Sets overridden constants instance
	 *
	 * Used in tests.
	 *
	 * @param Shipper_Model_Constants $constants Shipper_Model_Constants_Shipper instance.
	 *
	 * @since v1.0.3
	 */
	public function set_constants( Shipper_Model_Constants $constants ) {
		$this->constants = $constants;
	}

	/**
	 * Actually call the API endpoint
	 *
	 * @param string $url API URL to send the request to.
	 * @param string $endpoint API endpoint to call.
	 * @param array  $args Actual request arguments.
	 *
	 * @return array
	 * @since v1.0.3
	 */
	public function get_api_response( $url, $endpoint, $args ) {
		$model = new Shipper_Model_Api();

		$timer = Shipper_Helper_Timer_Basic::get();
		$timer->start( $endpoint );
		$resp = wp_remote_request( $url, $args );
		$timer->stop( $endpoint );

		$status_code = wp_remote_retrieve_response_code( $resp );

		$diff = Shipper_Helper_Timer_Basic::get()->diff( $endpoint );
		$msg  = sprintf(
			'(Called %s: %.02f, status: %d)',
			$endpoint,
			$diff,
			$status_code
		);
		if ( $diff > self::LOG_TIME_CUTOFF ) {
			Shipper_Helper_Log::write( $msg );
		} else {
			Shipper_Helper_Log::debug( $msg );
		}

		$raw = wp_remote_retrieve_body( $resp );

		$is_connection_error = false;

		if ( 200 !== (int) $status_code ) {
			$is_connection_error = true;
		}

		if ( $this->return_raw_response() ) {
			return $raw;
		}

		$data = array();
		if ( ! empty( $raw ) ) {
			$data = json_decode( $raw, true );
			if ( ! is_array( $data ) ) {
				$data = array();
				$this->add_error(
					self::ERR_RESPFORMAT,
					sprintf(
						/* translators: %s: error message. */
						__( 'Error parsing service response, invalid JSON: %s', 'shipper' ),
						$raw
					)
				);
			}
		}

		if ( ! empty( $is_connection_error ) ) {
			$error_msg = ! empty( $data )
				? $this->get_error_message( $data )
				: $raw;
			$this->add_error(
				self::ERR_CONNECTION,
				sprintf(
					/* translators: %1$s %2$s %3$s: endpoint, status code and error message. */
					__( 'Error talking to %1$s, the service responded with %2$d: %3$s', 'shipper' ),
					$endpoint,
					$status_code,
					$error_msg
				)
			);
			$model->record_api_fail();

			return $data;
		}

		return $data;
	}

	/**
	 * Attempts to parse serialized WP_Error response
	 *
	 * Falls back to formatted error fetching.
	 *
	 * @param array $data Serialized WP_Error response data.
	 *
	 * @return string
	 */
	public function get_error_message( $data ) {
		if ( ! empty( $data['message'] ) ) {
			return $data['message'];
		}

		return $this->get_formatted_error( $data );
	}

	/**
	 * Formats the status error from service response JSON
	 *
	 * @param array $status Service response array - expected format follows wp_send_json_* conventions.
	 *
	 * @return string
	 */
	public function get_formatted_error( $status ) {
		$data = ! empty( $status['data'] )
			? $status['data']
			: __( 'Generic error', 'shipper' );
		if ( is_string( $data ) ) {
			return $data;
		}

		return $this->get_formatted_error_data( $data );
	}

	/**
	 * Recursively process error data and reduce it to a string
	 *
	 * @param array $data Error data.
	 *
	 * @return string
	 */
	public function get_formatted_error_data( $data ) {
		$error = '';
		if ( ! is_array( $data ) && ! is_object( $data ) ) {
			return $error;
		}

		foreach ( (array) $data as $key => $value ) {
			$error .= $key . ': ';

			if ( is_array( $value ) || is_object( $value ) ) {
				$error .= '[' . $this->get_formatted_error_data( $value ) . ']';
			} else {
				$error .= $value;
			}

			$error .= '; ';
		}

		return $error;
	}
}