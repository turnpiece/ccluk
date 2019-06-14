<?php
/**
 * Shipper tasks: destination adding task
 *
 * @package shipper
 */

/**
 * Destination adder task class
 */
class Shipper_Task_Api_Destinations_Add extends Shipper_Task_Api {

	/**
	 * Adds a destination via the Hub API
	 *
	 * @param array $args Unused.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$model = new Shipper_Model_Api;
		$status = $this->get_response( 'destinations-add', self::METHOD_POST, array(
			'domain' => Shipper_Model_Stored_Destinations::get_current_domain(),
			'key' => $model->get_api_secret(),
		));

		if ( empty( $status['success'] ) ) {
			$this->add_error(
				self::ERR_SERVICE,
				__( 'Error adding current site', 'shipper' )
			);
			return false;
		}

		return true;
	}

}
