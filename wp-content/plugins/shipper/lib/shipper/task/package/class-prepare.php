<?php
/**
 * Shipper package tasks: preparation cleanup
 *
 * @package shipper
 */

/**
 * Package pre-run cleanup class
 */
class Shipper_Task_Package_Prepare extends Shipper_Task_Package {

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

		// Clean up packaging dir.
		$path = Shipper_Model_Fs_Package::get_root();
		Shipper_Helper_Fs_Path::rmdir_r( $path, '' );
		@rmdir( $path );

		return true;
	}

}