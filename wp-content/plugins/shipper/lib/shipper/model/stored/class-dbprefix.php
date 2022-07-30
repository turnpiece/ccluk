<?php
/**
 * Author: Hoang Ngo
 *
 * @package shipper
 */

/**
 * Class Shipper_Model_Stored_Dbprefix
 */
class Shipper_Model_Stored_Dbprefix extends Shipper_Model_Stored {
	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'dbprefix' );
	}

	/**
	 * Check if this has been set
	 *
	 * @return bool
	 */
	public function has_value() {
		return $this->get( 'option', false ) !== false;
	}
}