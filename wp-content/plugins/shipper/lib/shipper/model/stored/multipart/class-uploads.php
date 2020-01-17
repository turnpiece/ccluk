<?php
/**
 * Shipper models: multipart upload model
 *
 * @package shipper
 */

/**
 * Multipart download model class
 */
class Shipper_Model_Stored_Multipart_Uploads extends Shipper_Model_Stored_Multipart {

	public function __construct() {
		return parent::__construct( 'multipart-uploads', true );
	}
}