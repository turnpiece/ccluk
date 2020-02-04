<?php
/**
 * Shipper models: migration state get cached result class
 *
 * Holds cached migration state list for a very brief time period.
 *
 * This is a timed destinations abstraction.
 * It will get updated from the Hub periodically, in an appropriate task.
 *
 * @package shipper
 * @since v1.0.2
 */

/**
 * Stored apicache migration get results class
 */
class Shipper_Model_Stored_Apicache_Mgrget extends Shipper_Model_Stored {

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'apicachemgrget', true );
	}

	/**
	 * Gets time to live for this storage bucket
	 *
	 * This data will be re-synced from the Hub periodically.
	 *
	 * @return int Time to live, in seconds
	 */
	public function get_ttl() {
		return 120;
	}

}