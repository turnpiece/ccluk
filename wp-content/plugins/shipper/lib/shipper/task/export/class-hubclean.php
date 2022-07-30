<?php
/**
 * Shipper export tasks: Hub cleanup on target site
 *
 * @package shipper
 */

/**
 * Hub Cleanup task class
 */
class Shipper_Task_Export_Hubclean extends Shipper_Task_Export {

	/**
	 * Actually triggers the Hub cleanup
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->has_done_anything = true;
		$migration               = new Shipper_Model_Stored_Migration();
		$remote_site             = $migration->get_destination();

		$task = new Shipper_Task_Api_Migrations_Set();
		$task->apply(
			array(
				'domain' => $remote_site,
				'type'   => Shipper_Model_Stored_Migration::TYPE_IMPORT,
				'status' => 0,
				'file'   => '',
			)
		);

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
	 * Unused.
	 *
	 * @param string $path Unused.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string.
	 */
	public function get_source_path( $path, $migration ) {
		return $migration->get( 'source' );
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
		return __( 'Clean up target Hub data', 'shipper' );
	}
}