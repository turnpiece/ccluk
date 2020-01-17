<?php
/**
 * Shipper package tasks: cleanup
 *
 * @package shipper
 */

/**
 * Package post-run cleanup class
 */
class Shipper_Task_Package_Cleanup extends Shipper_Task_Package {

	/**
	 * Cleans up the staging area
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		// Clean up temp dir.
		Shipper_Helper_Fs_Path::rmdir_r(
			Shipper_Helper_Fs_Path::get_temp_dir(),
			''
		);
		return true;
	}

}