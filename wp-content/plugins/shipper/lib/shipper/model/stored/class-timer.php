<?php
/**
 * Shipper models: persistent timers
 *
 * @package shipper
 */

/**
 * Persistent timers model class
 */
class Shipper_Model_Stored_Timer extends Shipper_Model_Stored {

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'timer' );
	}
}