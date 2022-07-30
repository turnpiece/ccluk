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

		// add a flag to show flash notice.
		( new Shipper_Model_Stored_PackageMeta() )->set( 'show_flash', true )->save();
		( new Shipper_Model_Stored_Exclusions() )->clear()->save();

		return true;
	}

}