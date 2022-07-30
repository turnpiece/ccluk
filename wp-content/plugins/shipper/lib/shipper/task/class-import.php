<?php
/**
 * Shipper tasks: import task abstraction
 *
 * All import tasks will inherit from this.
 *
 * @package shipper
 */

/**
 * Import task abstraction class
 */
abstract class Shipper_Task_Import extends Shipper_Task {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	abstract public function get_work_description();

	const ERR_ZIP    = 'error_zip';
	const ERR_ACCESS = 'error_access';
	const ERR_SQL    = 'error_database';
	const ERR_REMOTE = 'error_remote';

	const PREFIX = 'import_tmp';

	const ARCHIVE = '::inherit::';

	/**
	 * Gets current task finalization status percentage
	 *
	 * @return float
	 */
	public function get_status_percentage() {
		$total   = max( 1, intval( $this->get_total_steps() ) );
		$current = (int) $this->get_current_step();

		return ( 100 / $total ) * $current;
	}

	/**
	 * Gets migration manifest data
	 *
	 * @param string $file File basename (sans extension) to export.
	 *
	 * @return array
	 */
	public function get_manifest( $file = Shipper_Model_Manifest::MANIFEST_BASENAME ) {
		$source = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() )
			. trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_META )
			. preg_replace( '/[^-_a-z0-9]/i', '', $file ) . '.json';

		return Shipper_Model_Manifest::from_source( $source );
	}
}