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
}