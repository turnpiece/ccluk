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
		$this->set_data(array(
			'api_key' => $this->get_api_key(),
			'api_secret' => $this->get_api_secret(),
		));
	}

	/**
	 * Gets site-specific API secret
	 *
	 * @return string
	 */
	public function get_api_secret() {
		$key = shipper_get_site_uniqid( shipper_network_home_url() );
		$hasher = new Shipper_Helper_Hash;
		$algo = $hasher->get_default_algo();

		return substr(hash_hmac(
			$algo,
			$key,
			$hasher->get_default_secret()
		), 0, 16);
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
	 *
	 * Used in tests
	 */
	public function reset_api_fails() {
		delete_site_option( 'shipper-storage-api-health-failures' );
	}
}