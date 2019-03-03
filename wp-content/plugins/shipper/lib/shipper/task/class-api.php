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
	const ERR_REQFORMAT = 'hub_request_format_error';
	const ERR_RESPFORMAT = 'hub_response_format_error';
	const ERR_SERVICE = 'hub_service_response_error';

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';

	const LOG_TIME_CUTOFF = 5;

	/**
	 * Holds API model instance
	 *
	 * @var object Shipper_Model_Api instance
	 */
	protected $_model;

	/**
	 * Constructor
	 *
	 * Sets up API model instance
	 */
	public function __construct() {
		$this->_model = new Shipper_Model_Api;
	}

	/**
	 * Gets service URL
	 *
	 * @return string
	 */
	public function get_url() {
		$base = defined( 'WPMUDEV_CUSTOM_API_SERVER' ) && WPMUDEV_CUSTOM_API_SERVER
			? WPMUDEV_CUSTOM_API_SERVER
			: 'https://premium.wpmudev.org/'
		;
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

		$url = trailingslashit( $this->get_url() ) . $endpoint;
		$data = array();
		$timeout = 30; // In seconds.

		if ( 'migration-start' === $endpoint ) {
			// Starting a migration can take a while.
			// Let's extend the timeout for that one.
			$timeout = 95;
		}

		$args = array(
			'timeout' => $timeout,
			'sslverify' => defined( 'WPMUDEV_API_SSLVERIFY' ) ? WPMUDEV_API_SSLVERIFY : true,
			'headers' => array(
				'user-agent' => shipper_get_user_agent(),
			),
		);

		$model = new Shipper_Model_Api;
		$key = $model->get( 'api_key' );
		if ( ! empty( $key ) ) {
			$args['headers']['Authorization'] = sprintf(
				'Basic %s',
				$key
			);
		}

		if ( ! empty( $request_data ) ) {
			if ( self::METHOD_POST === $method ) {
				$args['body'] = $request_data;
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

		$timer = Shipper_Helper_Timer_Basic::get();
		$timer->start( $endpoint );
		$resp = wp_remote_request( $url, $args );
		$timer->stop( $endpoint );
		$status_code = wp_remote_retrieve_response_code( $resp );

		$diff = Shipper_Helper_Timer_Basic::get()->diff( $endpoint );
		$msg = sprintf(
			'(Called %s: %.02f, status: %d)',
			$endpoint, $diff, $status_code
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

		if ( ! empty( $raw ) ) {
			$data = json_decode( $raw, true );
			if ( ! is_array( $data ) ) {
				$data = array();
				$this->add_error(
					self::ERR_RESPFORMAT,
					sprintf(
						__( 'Error parsing service response, invalid JSON: %s', 'shipper' ),
						$raw
					)
				);
			}
		}

		if ( ! empty( $is_connection_error ) ) {
			$error_msg = ! empty( $data )
				? $this->get_error_message( $data )
				: $raw
			;
			$this->add_error(
				self::ERR_CONNECTION,
				sprintf(
					__( 'Error talking to %1$s, the service responded with %2$d: %3$s', 'shipper' ),
					$endpoint, $status_code, $error_msg
				)
			);
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
		if ( is_string( $data ) ) { return $data; }

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
		if ( ! is_array( $data ) && ! is_object( $data ) ) { return $error; }

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