<?php
/**
 * Shipper models: tables list
 *
 * @package shipper
 */

/**
 * Stored exclusions model class
 */
class Shipper_Model_Stored_Tablelist extends Shipper_Model_Stored {

	const KEY_TABLES_LIST      = 'source_tables';
	const KEY_PROCESSED_TABLES = 'processed_tables';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'tablelist' );
	}
}