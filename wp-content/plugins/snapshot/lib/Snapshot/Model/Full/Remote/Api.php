<?php // phpcs:ignore
/**
 * Deals with the remote DEV API connection handling.
 *
 * @package snapshot
 */

/**
 * DEV API handling model helper
 */
class Snapshot_Model_Full_Remote_Api extends Snapshot_Model_Full {

	/**
	 * Singleton instance
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * API info cache
	 *
	 * @var array
	 */
	private $_api_info;

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type() {
		return 'remote';
	}

	/**
	 * Constructor - never to the outside world.
	 */
	private function __construct() {}

	/**
	 * No public clones
	 */
	private function __clone() {}

	/**
	 * Gets the singleton instance
	 *
	 * @return Snapshot_Model_Full_Remote_Api
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Resets cached API info
	 *
	 * Mainly useful for tests.
	 *
	 * @return bool
	 */
	public function reset_api() {
		$this->_api_info = false;
		return true;
	}

	/**
	 * Check whether we even have API info
	 *
	 * @return bool
	 */
	public function has_api_info() {
		$cached = Snapshot_Model_Transient::get_any( $this->get_filter( 'api_info' ) );
		return ! empty( $this->_api_info ) || ! empty( $cached );
	}

	/**
	 * Check whether we had an API error along the way
	 *
	 * @return bool
	 */
	public function has_api_error() {
		$err = Snapshot_Model_Transient::get( $this->get_filter( 'api_error' ) ); // current value.
		if ( ! empty( $err ) ) {
return true; }

		$nfo = $this->has_api_info();
		if ( ! $nfo ) {
			$nfo = Snapshot_Model_Transient::get_any( $this->get_filter( 'api_info' ), false );
		}
		return ! $nfo || ! empty( $err );
	}

	/**
	 * Resets active token
	 *
	 * @return bool
	 */
	public function remove_token() {
		Snapshot_Model_Transient::delete( $this->get_filter( 'token' ) );
		Snapshot_Model_Transient::delete( $this->get_filter( 'api_error' ) );
		Snapshot_Model_Transient::delete( $this->get_filter( 'api_info' ) );
		Snapshot_Model_Transient::delete( $this->get_filter( 'backups' ) );
		Snapshot_Model_Transient::delete( $this->get_filter( 'help_urls' ) );
		return true;
	}

	/**
	 * Helper method to spawn default API meta key search error message
	 *
	 * @return string Default error message
	 */
	public function get_default_api_meta_error_message() {
		return sprintf(
			_x(
				'%1$s %2$s',
				'API error, then connection check message',
				SNAPSHOT_I18N_DOMAIN
			),
			Snapshot_View_Full_Backup::get_message( 'api_error' ),
			sprintf( Snapshot_View_Full_Backup::get_message( 'check_connection' ), $this->get_dev_remote_host() )
		);
	}

	/**
	 * Establish connection with remote storage API
	 *
	 * @return bool
	 */
	public function connect() {
		if ( ! empty( $this->_api_info ) ) {
return true; // Already connected.
		}
		$body = Snapshot_Model_Transient::get( $this->get_filter( 'api_info' ), false );

		if ( empty( $body ) ) {
			Snapshot_Model_Transient::delete( $this->get_filter( 'api_info' ) );

			$domain = $this->get_domain();
			if ( empty( $domain ) ) {
return false; }

			$response = $this->get_dev_api_response(
                'credentials', array(
				'domain' => $domain,
			)
                );

			$error = sprintf(
				_x(
					'%1$s %2$s <br /> %3$s',
					'Request error, then connection check message, then open ticket message',
					SNAPSHOT_I18N_DOMAIN
				),
				Snapshot_View_Full_Backup::get_message( 'request_error' ),
				sprintf( Snapshot_View_Full_Backup::get_message( 'check_connection' ), $this->get_dev_remote_host() ),
				sprintf( Snapshot_View_Full_Backup::get_message( 'open_ticket' ), 'https://premium.wpmudev.org/forums/tags/snapshot-pro' )
			);

			if ( is_wp_error( $response ) ) {
				$this->_set_error( $error );
				return false;
			}

			$body = wp_remote_retrieve_body( $response );

			$response_code = (int) wp_remote_retrieve_response_code( $response );
			if ( 200 !== $response_code ) {
				// Deal with the API errors here.
				if ( ! empty( $body ) ) {
					$body = json_decode( $body, true );
				}
				if ( ! empty( $body['message'] ) ) {
					$error = $body['message'];
				}

				$this->_set_error( $error );
				Snapshot_Helper_Log::warn( "Unable to connect to the API: {$response_code}", 'Remote' );

				return false;
			}

			Snapshot_Model_Transient::set(
				$this->get_filter( 'api_info' ),
				$body,
				Snapshot_Model_Transient::ttl( Snapshot_Model_Transient::TTL_CACHE )
			);
		}

		$this->_api_info = json_decode( $body, true );

		return true;
	}

	/**
	 * Centralized domain getter
	 *
	 * @return string
	 */
	public function get_domain() {
		return apply_filters(
			$this->get_filter( 'domain' ),
			network_site_url()
		);
	}

	/**
	 * Gets fresh timed token
	 *
	 * Sends out token refreshing request
	 *
	 * @return string
	 */
	public function get_token() {
		$local_token = Snapshot_Model_Transient::get_any( $this->get_filter( 'token' ), false );
		// If we have a token *and* it's not locally expired, we're good.
		if ( ! empty( $local_token ) && ! Snapshot_Model_Transient::is_expired( $this->get_filter( 'token' ) ) ) {
			Snapshot_Helper_Log::info( 'Use non-expired local token', 'Remote' );
			return $local_token;
		}

		// Otherwise, we'll need to (obtain|exchange) it. So, here we go.
		Snapshot_Helper_Log::info( 'Initiate token exchange', 'Remote' );

		$key = $this->get_config( 'secret-key', false );
		if ( empty( $key ) ) {
			$this->_set_error( sprintf( Snapshot_View_Full_Backup::get_message( 'missing_secret_key' ), Snapshot_Model_Full_Remote_Help::get()->get_current_site_management_link() ) );
			Snapshot_Helper_Log::info( 'Missing secret key', 'Remote' );
			return false;
		}

		$domain = $this->get_domain();
		if ( empty( $domain ) ) {
return false; }

		$args = array(
			'domain' => $domain,
		);

		if ( ! empty( $local_token ) ) {
			$args['token'] = $local_token;
		}

		// Sign the request with key hash and timestamp.
		if ( ! empty( $key ) ) {
			$model = new Snapshot_Model_Full_Remote_Signature();
			$timestamp = (int) gmdate( 'U' );
			$key_data = array(
				'key' => $key,
				'stamp' => $timestamp,
				'urls' => $model->get_raw_signature( Snapshot_Model_Full_Remote_Help::get()->get_help_urls() ),
			);
			$args['hash'] = $model->get_signature( $key_data, $this->get_dashboard_api_key() );
			$args['timestamp'] = $timestamp;
		}

		$error = sprintf(
			_x(
				'%1$s %2$s <br /> %3$s',
				'Request error, then connection check message, then open ticket message',
				SNAPSHOT_I18N_DOMAIN
			),
			Snapshot_View_Full_Backup::get_message( 'request_error' ),
			sprintf( Snapshot_View_Full_Backup::get_message( 'check_connection' ), $this->get_dev_remote_host() ),
			sprintf( Snapshot_View_Full_Backup::get_message( 'open_ticket' ), 'https://premium.wpmudev.org/forums/tags/snapshot-pro' )
		);
		Snapshot_Model_Transient::delete( $this->get_filter( 'api_error' ) );

		$response = $this->get_dev_api_response( 'get-token', $args );

		if ( is_wp_error( $response ) ) {
			$this->_set_error( $error );
			Snapshot_Helper_Log::warn( 'Unable to communicate with the token exchange', 'Remote' );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			// Deal with the API errors here.
			if ( ! empty( $body ) ) {
				$body = json_decode( $body, true );
			}
			if ( ! empty( $body['message'] ) ) {
				$error = $body['message'];
			}

			if ( ! empty( $body['code'] ) && 'invalid_secret_key' === $body['code'] ) {
				$error = sprintf(
					_x(
						'%1$s <br /> %2$s <br /> %3$s',
						'Request error message, then key reset message, then open ticket message',
						SNAPSHOT_I18N_DOMAIN
					),
					$body['message'],
					sprintf( Snapshot_View_Full_Backup::get_message( 'reset_secret_key' ), Snapshot_Model_Full_Remote_Help::get()->get_current_site_management_link() ),
					sprintf( Snapshot_View_Full_Backup::get_message( 'open_ticket' ), 'https://premium.wpmudev.org/forums/tags/snapshot-pro' )
				);
				$this->set_config( 'secret-key', false );
				$this->set_config( 'disable_cron', true ); // Also kill all crons.
			}
			$this->_set_error( $error );
			if ( ! empty( $args['token'] ) ) {
				Snapshot_Helper_Log::warn( 'Unable to exchange token', 'Remote' );
			} else {
				Snapshot_Helper_Log::warn( 'Unable to get fresh token', 'Remote' );
			}
			return false;
		}

		$result = json_decode( $body, true );
		$remote_token = ! empty( $result['token'] )
			? $result['token']
			: false
		;
		if ( ! empty( $remote_token ) ) {
			Snapshot_Helper_Log::info( 'Update token with remote response', 'Remote' );
			Snapshot_Model_Transient::set(
				$this->get_filter( 'token' ),
				$remote_token,
				Snapshot_Model_Transient::ttl( Snapshot_Model_Transient::TTL_SHORT )
			);
		} else {
			Snapshot_Helper_Log::warn( 'Token exchange did not respond with parseable data.', 'Remote' );
		}

		return $remote_token;
	}

	/**
	 * Get the DEV remote API host URL.
	 *
	 * @return string
	 */
	public function get_dev_remote_host() {
		return defined( 'WPMUDEV_CUSTOM_API_SERVER' ) && WPMUDEV_CUSTOM_API_SERVER
			? WPMUDEV_CUSTOM_API_SERVER
			: 'https://premium.wpmudev.org/'
		;
	}

	/**
	 * Actually send out the API request to DEV
	 *
	 * @param string $endpoint API endpoint to check.
	 * @param array  $body Optional body array.
	 *
	 * @return mixed Array of results including HTTP headers or WP_Error if the request failed.
	 */
	public function get_dev_api_response( $endpoint, $body = array() ) {
		$key = $this->get_dashboard_api_key();
		if ( empty( $key ) ) {
			return new WP_Error(
				$this->get_filter( 'missing_api_key' ),
				__( 'Missing API key', SNAPSHOT_I18N_DOMAIN )
			);
		}

		$secret_key = $this->get_config( 'secret-key', false );
		if ( empty( $secret_key ) ) {
			$this->_set_error(
                sprintf(
				Snapshot_View_Full_Backup::get_message( 'missing_secret_key' ),
				Snapshot_Model_Full_Remote_Help::get()->get_current_site_management_link()
			)
                );
			return new WP_Error(
				$this->get_filter( 'missing_secret_key' ),
				sprintf(
					Snapshot_View_Full_Backup::get_message( 'missing_secret_key' ),
					Snapshot_Model_Full_Remote_Help::get()->get_current_site_management_link()
				)
			);
		}

		if ( ! in_array( $endpoint, array( 'credentials', 'backups-size', 'register-settings', 'get-token' ), true ) ) {
			return new WP_Error(
				$this->get_filter( 'invalid_endoint' ),
				__( 'Invalid endpoint', SNAPSHOT_I18N_DOMAIN )
			);
		}

		if ( Snapshot_Model_Transient::get( $this->get_filter( 'api_error' ), false ) ) {
			return new WP_Error(
				$this->get_filter( 'connection_error_cache' ),
				__( 'Persistent connection error', SNAPSHOT_I18N_DOMAIN )
			);
		}

		// Special case, when API responds with 200 OK but JSON is invalid.
		if ( Snapshot_Model_Transient::get( $this->get_filter( 'api_down' ), false ) ) {
			return new WP_Error(
				$this->get_filter( 'connection_error_cache' ),
				__( 'Persistent connection error', SNAPSHOT_I18N_DOMAIN )
			);
		}

		$remote_host = $this->get_dev_remote_host();

		$method = 'GET';
		$query_url = '';
		if ( 'register-settings' === $endpoint ) {
			$method = 'POST';
		}

		if ( is_array( $body ) && ! in_array( $endpoint, array( 'backups-size', 'get-token' ), true ) && empty( $body['token'] ) ) {
			$body['token'] = $this->get_token();
		}

		if ( 'GET' === $method ) {
			$query_url = ! empty( $body ) && is_array( $body )
				? http_build_query( $body, '', '&' )
				: ''
			;
			$query_url = $query_url && preg_match( '/^\?/', $query_url ) ? $query_url : "?{$query_url}";
		}

		$remote_url =
			trailingslashit( $remote_host ) .
			trailingslashit( 'api/snapshot/v1' ) .
			trim( $endpoint, '/' ) .
			$query_url
		;
		$request_arguments = array(
			'method' => $method,
			'timeout' => 15,
			'headers' => array(
				'Authorization' => "Basic {$key}",
			),
			'sslverify' => false,
		);

		if ( 'POST' === $method ) {
			$request_arguments['body'] = $body;
		}
		$result = wp_remote_request( $remote_url, $request_arguments );

		$response_code = apply_filters(
			'snapshot_mocks_api_response_code',
			(int) wp_remote_retrieve_response_code( $result )
		);
		if ( 200 !== $response_code ) {
			$error = wp_remote_retrieve_body( $result );
			if ( empty( $error ) ) {
				$error = time();
			} else {
				$error = json_decode( $error, true );
			}

			Snapshot_Helper_Log::warn( "API connection to {$endpoint} returned non-200: {$response_code}", 'Remote' );

			if ( ! empty( $error['code'] ) && 'invalid_signature' === $error['code'] ) {
				// Invalid signature, remove this one.
				$this->set_config( 'secret-key', false );
				$this->set_config( 'active', false ); // Also deactivate the whole thing.
				$this->set_config( 'disable_cron', true ); // Also kill all crons.
			}

			Snapshot_Model_Transient::set(
				$this->get_filter( 'api_error' ),
				$error,
				Snapshot_Model_Transient::ttl( Snapshot_Model_Transient::TTL_SHORT )
			); // No requests next short transient interval.
		} else {
			// First up, let's try parsing the response body. It should be valid JSON.
			// If it's not, we have a problem and we should be keeping/re-setting the error.
			$body = apply_filters(
				'snapshot_mocks_api_response_body',
				wp_remote_retrieve_body( $result )
			);
			if ( empty( $body ) || null === json_decode( $body ) ) {
				// Either empty response, or something went wrong parsing JSON.
				// Cry out loud and set API error cache.
				$json_error_code = json_last_error();
				Snapshot_Helper_Log::warn( "API connection to {$endpoint} returned invalid JSON: {$json_error_code}", 'Remote' );

				Snapshot_Model_Transient::set(
					$this->get_filter( 'api_error' ),
					time(),
					Snapshot_Model_Transient::ttl( Snapshot_Model_Transient::TTL_SHORT )
				); // No requests next short transient interval.
				Snapshot_Model_Transient::set(
					$this->get_filter( 'api_down' ),
					time(),
					Snapshot_Model_Transient::ttl( Snapshot_Model_Transient::TTL_SHORT )
				); // No requests next short transient interval.
			} else {
				// Valid JSON response served as 200 OK, yay.
				Snapshot_Model_Transient::delete( $this->get_filter( 'api_error' ) ); // Drop cache.
				Snapshot_Helper_Log::info( "Successful remote response for {$endpoint}", 'Remote' );
			}
		}

		return $result;
	}

	/**
	 * Send out the API request to DEV, unprotected by token
	 *
	 * @param string $endpoint API endpoint to check.
	 * @param array  $body Optional body array.
	 *
	 * @return mixed Array of results including HTTP headers or WP_Error if the request failed.
	 */
	public function get_dev_api_unprotected_response( $endpoint, $body = array() ) {
		$key = $this->get_dashboard_api_key();
		if ( empty( $key ) ) {
			return new WP_Error(
				$this->get_filter( 'missing_api_key' ),
				__( 'Missing API key', SNAPSHOT_I18N_DOMAIN )
			);
		}

		if ( ! in_array( $endpoint, array( 'get-urls', 'backups-size', 'get-key', 'current-local-status' ), true ) ) {
			return new WP_Error(
				$this->get_filter( 'invalid_endoint' ),
				__( 'Invalid endpoint', SNAPSHOT_I18N_DOMAIN )
			);
		}

		if ( Snapshot_Model_Transient::get( $this->get_filter( 'api_error' ), false ) ) {
			if ( 'get-key' !== $endpoint ) {
				return new WP_Error(
					$this->get_filter( 'connection_error_cache' ),
					__( 'Persistent connection error', SNAPSHOT_I18N_DOMAIN )
				);
			}
		}

		$remote_host = $this->get_dev_remote_host();

		$method = 'GET';
		$query_url = '';

		if ( 'current-local-status' === $endpoint ) {
			$method = 'POST';
		}

		if ( 'GET' === $method ) {
			$query_url = ! empty( $body ) && is_array( $body )
				? http_build_query( $body, '', '&' )
				: ''
			;
			$query_url = $query_url && preg_match( '/^\?/', $query_url ) ? $query_url : "?{$query_url}";
		}

		$remote_url =
			trailingslashit( $remote_host ) .
			trailingslashit( 'api/snapshot/v1' ) .
			trim( $endpoint, '/' ) .
			$query_url
		;
		$request_arguments = array(
			'method' => $method,
			'timeout' => 15,
			'headers' => array(
				'Authorization' => "Basic {$key}",
			),
			'sslverify' => false,
		);
		if ( 'POST' === $method ) {
			$request_arguments['body'] = $body;
		}

		$result = wp_remote_request( $remote_url, $request_arguments );

		$response_code = (int) wp_remote_retrieve_response_code( $result );
		if ( 200 !== $response_code ) {
			$error = wp_remote_retrieve_body( $result );
			if ( empty( $error ) ) {
				$error = time();
			} else {
				$error = json_decode( $error, true );
			}

			Snapshot_Helper_Log::warn( "API connection to {$endpoint} returned non-200: {$response_code}", 'Remote' );

			if ( ! empty( $error['code'] ) && 'invalid_signature' === $error['code'] ) {
				// Invalid signature, remove this one.
				$this->set_config( 'secret-key', false );
				$this->set_config( 'active', false ); // Also deactivate the whole thing
				$this->set_config( 'disable_cron', true ); // Also kill all crons.
			}

			Snapshot_Model_Transient::set(
				$this->get_filter( 'api_error' ),
				$error,
				Snapshot_Model_Transient::ttl( Snapshot_Model_Transient::TTL_SHORT )
			); // No requests next short transient interval.
		} else {
			Snapshot_Model_Transient::delete( $this->get_filter( 'api_error' ) ); // Drop cache.
			Snapshot_Helper_Log::info( "Successful remote response for {$endpoint}", 'Remote' );
		}

		return $result;
	}

	/**
	 * Gets the API info for the remote connection
	 *
	 * @return array API info for remote connection
	 */
	public function get_api_info() {
		if ( ! $this->has_api_info() ) {
			$this->_set_error( $this->get_default_api_meta_error_message() );
			return array();
		}
		return $this->get_api_meta( 'creds', array() );
	}

	/**
	 * Get a value from API response
	 *
	 * Valid key values (as of 2016-01-30):
	 * - creds
	 * - current_bytes
	 * - user_limit
	 * - manage_link
	 *
	 * @param string $key Key value to get.
	 * @param mixed  $fallback Fallback value, defaults to (bool)false.
	 *
	 * @return mixed
	 */
	public function get_api_meta( $key, $fallback = false ) {
		if ( ! $this->has_api_info() ) {
			$this->_set_error( $this->get_default_api_meta_error_message() );
			return $fallback;
		}
		return isset( $this->_api_info[ $key ] )
			? $this->_api_info[ $key ]
			: $fallback
		;
	}

	/**
	 * Cleans up and resets API
	 *
	 * @return bool
	 */
	public function clean_up_api() {
		Snapshot_Model_Transient::delete( $this->get_filter( 'api_info' ) );
		return $this->reset_api();
	}

	/**
	 * Actual dashborad API key getter.
	 *
	 * @return string Dashboard API key
	 */
	public function get_dashboard_api_key() {
		$api_key = defined( 'WPMUDEV_APIKEY' ) && WPMUDEV_APIKEY
			? WPMUDEV_APIKEY
			: get_site_option( 'wpmudev_apikey', false );
		return apply_filters(
			$this->get_filter( 'api_key' ),
			$api_key
		);
	}
}