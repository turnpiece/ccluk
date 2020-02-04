<?php
/**
 * Shipper models: updates cache model class
 *
 * Holds data about last API updates
 *
 * This is a timed abstraction.
 *
 * @package shipper
 */

/**
 * Updates cache model
 */
class Shipper_Model_Stored_Updates extends Shipper_Model_Stored {

	const KEY_PERCENT = 'percentage';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'updates' );
	}

	/**
	 * Gets time to live for this storage bucket
	 *
	 * This data will be re-synced to the Hub periodically.
	 *
	 * @return int Time to live, in seconds
	 */
	public function get_ttl() {
		return Shipper_Model_Stored::TTL_SHORT;
	}
}