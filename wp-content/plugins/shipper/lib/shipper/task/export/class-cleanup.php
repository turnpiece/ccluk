<?php
/**
 * Shipper export tasks: cleanup
 *
 * Triggers as the last task, and cleans up any leftover intermediate stuff.
 * Clean up temp data before and after the migration.
 *
 * @package shipper
 */

/**
 * Export upload class
 */
class Shipper_Task_Export_Cleanup extends Shipper_Task_Export {

	/**
	 * Actually uploads the exported archive
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->has_done_anything = true;

		// Clean up temp dir.
		Shipper_Helper_Fs_Path::rmdir_r(
			Shipper_Helper_Fs_Path::get_temp_dir(),
			''
		);

		$exclusion = new Shipper_Model_Stored_Exclusions();
		$exclusion->clear();
		$exclusion->save();

		return true;
	}

	/**
	 * Gets total steps for this task.
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return 1;
	}

	/**
	 * Gets current step for this task
	 *
	 * @return int
	 */
	public function get_current_step() {
		return $this->has_done_anything() ? 1 : 0;
	}

	/**
	 * Gets the task source path
	 *
	 * Proxies archive path getting.
	 *
	 * @param string $path Unused.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string.
	 */
	public function get_source_path( $path, $migration ) {
		return $this->get_archive_path( $migration->get( 'destination' ) );
	}

	/**
	 * Satisfy interface
	 *
	 * @return string
	 */
	public function get_destination_type() {
		return '';
	}

	/**
	 * Gets task job description
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Clean up', 'shipper' );
	}
}