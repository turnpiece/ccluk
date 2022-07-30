<?php
/**
 * Shipper controllers: Destination Hub actions
 *
 * @package shipper
 */

/**
 * Destinations Hub actions handling controller class
 */
class Shipper_Controller_Hub_Destination extends Shipper_Controller_Hub {

	/**
	 * Gets the list of known Hub actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::ACTION_RESET_DESTINATIONS,
			self::ACTION_ADD,
		);
		return $known;
	}

	/**
	 * Adds destination remotely
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 */
	public function json_destination_add( $params, $action, $request = false ) {
		$task   = new Shipper_Task_Api_Destinations_Add();
		$status = $task->apply();

		if ( ! empty( $status ) ) {
			// Let's also refresh our systems info.
			$info_task = new Shipper_Task_Api_Info_Set();
			$system    = new Shipper_Model_System();
			$info_task->apply( $system->get_data() );
		}

		return ! empty( $status )
			? $this->send_response_success( true, $request )
			: $this->send_response_error( true, $request );
	}

	/**
	 * Resets destinations cache
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 */
	public function json_reset_destination_cache( $params, $action, $request = false ) {
		$model = new Shipper_Model_Stored_Destinations();
		$model->clear();
		$model->set_timestamp( false );
		$model->save();
		return $this->send_response_success( true, $request );
	}
}