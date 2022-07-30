<?php
/**
 * Shipper models: cached API credentials class
 *
 * Holds API credentials response.
 *
 * This is a timed creds abstraction.
 * It will get updated from the Hub periodically, in an appropriate task.
 *
 * @package shipper
 */

/**
 * Stored creds model class
 */
class Shipper_Model_Stored_Creds extends Shipper_Model_Stored {

	const KEY_ID     = 'AccessKeyId';
	const KEY_SECRET = 'SecretAccessKey';
	const KEY_TOKEN  = 'SessionToken';
	const KEY_BUCKET = 'Bucket';
	const KEY_PREFIX = 'Prefix';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		// Store the model in the database.
		parent::__construct( 'creds', true );
	}

	/**
	 * Gets time to live for this storage bucket
	 *
	 * This data will be re-synced from the Hub periodically.
	 * It can be also refreshed on demand, so it can fairly long lifetime.
	 *
	 * @return int Time to live, in seconds
	 */
	public function get_ttl() {
		return Shipper_Model_Stored::TTL_SHORT;
	}
}