<?php
/**
 * Shipper tasks: general system prerequisites check (remote)
 *
 * @since v1.0.3
 *
 * @package shipper
 */

/**
 * System prerequisites check task
 */
class Shipper_Task_Check_Rsystem extends Shipper_Task_Check_System {

	/**
	 * Sidesteps the Hub updating
	 *
	 * @param array $data Data to send.
	 */
	public function update_hub( $data ) {
		return false;
	}

	/**
	 * Gets the domain of the current system check
	 *
	 * @return string
	 */
	public function get_domain() {
		$migration = new Shipper_Model_Stored_Migration();
		return $migration->get_destination();
	}
}