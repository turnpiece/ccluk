<?php
/**
 * Shipper models: dumped filelist model
 *
 * @package shipper
 */

/**
 * Regular files dumped list implementation class
 */
class Shipper_Model_Dumped_Filelist extends Shipper_Model_Dumped {

	/**
	 * Constructor
	 *
	 * Sets up the parent class filename.
	 */
	public function __construct() {
		parent::__construct( 'files.txt' );
	}
}