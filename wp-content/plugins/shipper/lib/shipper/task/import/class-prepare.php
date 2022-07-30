<?php
/**
 * Shipper tasks: import, staging area cleanup task
 *
 * This is the same as the cleanup task, with additional options data
 * jettisoning which is being done in an effort to preserve options on import.
 *
 * @package shipper
 */

/**
 * Shipper import preparation (cleanup) class
 */
class Shipper_Task_Import_Prepare extends Shipper_Task_Import_Cleanup {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Prepare the staging area', 'shipper' );
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$options = new Shipper_Model_Stored_Options();
		$options->jettison_data();

		return parent::apply( $args );
	}
}