<?php
/**
 * Shipper models: cached Hub sites class
 *
 * Holds the Hub sites API response cache.
 *
 * @package shipper
 */

/**
 * Stored Hub sites model class
 */
class Shipper_Model_Stored_Hublist extends Shipper_Model_Stored {

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'hublist' );
	}
}