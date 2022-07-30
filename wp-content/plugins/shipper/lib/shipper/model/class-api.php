<?php
/**
 * Shipper models: API model
 *
 * Holds information for communication with Shipper Hub API.
 *
 * @package shipper
 */

/**
 * API model class
 */
class Shipper_Model_Api extends Shipper_Model {

	const MAX_FAILURES = 9;

	/**
	 * Constructor
	 *
	 * Sets up data.
	 */
	public function __construct() {
		$this->populate();
	}

	/**
	 * Initializes the data
	 */
	public function populate() {
		$this->set_data(
			array(
				'api_key'    => $this->get_api_key(),
				'api_secret' => $this->get_api_secret(),
			)
		);
	}

	/**
	 * Gets site-specific API secret
	 *
	 * @return string
	 */
	public function get_api_secret() {
		$key    = shipper_get_site_uniqid( shipper_network_home_url() );
		$hasher = new Shipper_Helper_Hash();
		$algo   = $hasher->get_default_algo();

		return substr(
			hash_hmac(
				$algo,
				$key,
				$hasher->get_default_secret()
			),
			0,
			16
		);
	}

	/**
	 * Gets WPMU DEV API key
	 *
	 * @return string
	 */
	public function get_api_key() {
		$api_key = defined( 'WPMUDEV_APIKEY' ) && WPMUDEV_APIKEY
			? WPMUDEV_APIKEY
			: get_site_option( 'wpmudev_apikey', false );
		return $api_key;
	}

	/**
	 * Gets a list of previous API communication failures.
	 *
	 * @return array
	 */
	public function get_previous_api_fails() {
		$failures = get_site_option( 'shipper-storage-api-health-failures', array() );
		if ( ! is_array( $failures ) ) {
			$failures = array();
		}
		return $failures;
	}

	/**
	 * Records an API failure
	 *
	 * Adds the error timestamp to failures record queue.
	 * In order for this to not grow overly long, only keep last few entries.
	 *
	 * @param int $timestamp Optional timestamp, used in tests.
	 */
	public function record_api_fail( $timestamp = false ) {
		$errors = $this->get_previous_api_fails();

		if ( count( $errors ) >= self::MAX_FAILURES ) {
			$errors = array_splice( $errors, -1 * ( self::MAX_FAILURES - 1 ) );
		}

		$errors[] = ! empty( $timestamp ) && is_numeric( $timestamp )
			? (int) $timestamp
			: time();
		update_site_option( 'shipper-storage-api-health-failures', $errors );
	}

	/**
	 * Resets the recorded API failures queue.
	 */
	public function reset_api_fails() {
		delete_site_option( 'shipper-storage-api-health-failures' );
	}

	/**
	 * Gets cached API response
	 *
	 * @since v1.0.3
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $payload Request arguments.
	 * @param int    $ttl Expected time to live for the cache.
	 *
	 * @return false|array Cached API response, or (bool)false on failure.
	 */
	public function get_cached_api_response( $endpoint, $payload, $ttl = 0 ) {
		$key  = $this->get_payload_cache_key( $payload );
		$data = get_site_option( 'shipper-storage-apicaches', array() );

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		if ( ! isset( $data[ $endpoint ][ $key ] ) ) {
			return false;
		}

		$cache_timestamp = ! empty( $data[ $endpoint ][ $key ]['timestamp'] )
			? (int) $data[ $endpoint ][ $key ]['timestamp']
			: false;
		$cache_data      = ! empty( $data[ $endpoint ][ $key ]['data'] )
			? $data[ $endpoint ][ $key ]['data']
			: array();

		if ( time() - $ttl > $cache_timestamp ) {
			// Purge old caches.
			$this->clear_cached_api_response( $endpoint, $payload );
			return false;
		}

		return $cache_data;
	}

	/**
	 * Sets the cache data for an endpoint/payload combo
	 *
	 * @since v1.0.3
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $payload Request arguments.
	 * @param array  $response Data to cache.
	 */
	public function set_cached_api_response( $endpoint, $payload, $response ) {
		$key  = $this->get_payload_cache_key( $payload );
		$data = get_site_option( 'shipper-storage-apicaches', array() );

		if ( ! is_array( $data ) ) {
			$data = array();
		}
		if ( empty( $data[ $endpoint ] ) ) {
			$data[ $endpoint ] = array();
		}

		$data[ $endpoint ][ $key ] = array(
			'timestamp' => time(),
			'data'      => $response,
		);

		update_site_option( 'shipper-storage-apicaches', $data );
	}

	/**
	 * Clears the data cache bucket
	 *
	 * If payload is supplied, clear just that particular payload bucket.
	 * Otherwise, clear all cache for that endpoint.
	 *
	 * @since v1.0.3
	 *
	 * @param string      $endpoint API endpoint.
	 * @param array|false $payload Optional request arguments.
	 */
	public function clear_cached_api_response( $endpoint, $payload = false ) {
		$key = false === $payload ? false : $this->get_payload_cache_key( $payload );

		$data = get_site_option( 'shipper-storage-apicaches', array() );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		if ( empty( $data[ $endpoint ] ) ) {
			$data[ $endpoint ] = array();
		}

		if ( false !== $key && isset( $data[ $endpoint ][ $key ] ) ) {
			unset( $data[ $endpoint ][ $key ] );
		} else {
			unset( $data[ $endpoint ] );
		}

		update_site_option( 'shipper-storage-apicaches', $data );
	}

	/**
	 * Gets cache key from request args.
	 *
	 * @param array $payload Request payload.
	 *
	 * @return string Cache key.
	 */
	public function get_payload_cache_key( $payload ) {
		$key = 'generic';
		if ( ! is_array( $payload ) ) {
			return $key;
		}

		return md5( wp_json_encode( $payload ) );
	}
}