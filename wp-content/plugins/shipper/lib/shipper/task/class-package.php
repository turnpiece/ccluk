<?php
/**
 * Shipper tasks: package abstract class
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package export tasks hub class
 */
abstract class Shipper_Task_Package extends Shipper_Task {

	/**
	 * Get zip.
	 *
	 * @return object|\Shipper_Model_Archive_Zip
	 */
	public static function get_zip() {
		$model = new Shipper_Model_Stored_Package();
		return Shipper_Model_Archive::get(
			$model->get_package_path()
		);
	}

	/**
	 * Gets total amount of work to be done
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return 1;
	}

	/**
	 * Gets currently done work amount
	 *
	 * @return int
	 */
	public function get_current_step() {
		return 1;
	}

	/**
	 * Gets current task progress percentage
	 *
	 * @return float
	 */
	public function get_status_percentage() {
		$current = $this->get_current_step();

		$total = $this->get_total_steps();
		if ( empty( $total ) ) {
			return 1;
		}

		return ( $current * 100 ) / $total;
	}
}