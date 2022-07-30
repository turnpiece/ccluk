<?php
/**
 * Shipper package export tasks: files gathering task
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Class Shipper_Task_Package_Gather
 */
class Shipper_Task_Package_Gather extends Shipper_Task_Export_Files {

	/**
	 * Run the task
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		do_action( 'shipper_package_migration_gather_tick_before' );
		( new Shipper_Helper_Fs_Package_Filelist( new Shipper_Model_Stored_Filelist() ) )->create();
		do_action( 'shipper_package_migration_gather_tick_after' );

		return true;
	}
}