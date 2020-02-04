<?php
/**
 * Shipper model: shipper-specific constants wrapper
 *
 * Used in testing
 *
 * @package shipper
 */

/**
 * Shipper prefixed constants class
 */
class Shipper_Model_Constants_Shipper extends Shipper_Model_Constants {

	public function __construct() {
		parent::__construct( 'SHIPPER_' );
	}
}