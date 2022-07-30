<?php
/**
 * Shipper models: multipart download model
 *
 * @package shipper
 */

/**
 * Multipart download model class
 */
class Shipper_Model_Stored_Multipart_Downloads extends Shipper_Model_Stored_Multipart {

	/**
	 * Shipper_Model_Stored_Multipart_Downloads constructor.
	 */
	public function __construct() {
		return parent::__construct( 'multipart-downloads', true );
	}
}