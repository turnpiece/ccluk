<?php
/**
 * Shipper models: permanent exclusions list
 *
 * Holds list of files *not* to be included in a migration.
 *
 * @package shipper
 */

/**
 * Stored exclusions model class
 */
class Shipper_Model_Stored_Exclusions extends Shipper_Model_Stored {

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'exclusions' );
	}
}