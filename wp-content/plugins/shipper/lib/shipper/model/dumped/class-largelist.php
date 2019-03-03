<?php
/**
 * Shipper models: dumped large files list model
 *
 * @package shipper
 */

/**
 * Large files dumped list implementation class
 */
class Shipper_Model_Dumped_Largelist extends Shipper_Model_Dumped {

	/**
	 * Constructor
	 *
	 * Sets up the parent class filename.
	 */
	public function __construct() {
		parent::__construct( 'large_files.txt' );
	}
}