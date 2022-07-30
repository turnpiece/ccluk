<?php
/**
 * Shipper tasks: import Hub cleanup task
 *
 * @package shipper
 */

/**
 * Shipper import Hub cleanup class
 */
class Shipper_Task_Import_Hubclean extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Clean up target Hub data', 'shipper' );
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
		$migration   = new Shipper_Model_Stored_Migration();
		$remote_site = $migration->get_destination();

		$task = new Shipper_Task_Api_Migrations_Set();
		$task->apply(
			array(
				'domain' => $remote_site,
				'type'   => Shipper_Model_Stored_Migration::TYPE_EXPORT,
				'status' => 0,
				'file'   => Shipper_Task_Import::ARCHIVE,
			)
		);

		return true;
	}
}