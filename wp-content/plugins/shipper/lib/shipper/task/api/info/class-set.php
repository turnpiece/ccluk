<?php
/**
 * Shipper tasks: system info setting task
 *
 * @package shipper
 */

/**
 * Info adding task class
 */
class Shipper_Task_Api_Info_Set extends Shipper_Task_Api {

	/**
	 * Sets Hub API info for the current system
	 *
	 * @param array $args Domain info.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration;

		$status = $this->get_response( 'info-set', self::METHOD_POST, array(
			'domain' => Shipper_Model_Stored_Destinations::get_current_domain(),
			'dst' => $migration->get_destination(),
			'info' => wp_json_encode( $args ),
		));

		if ( empty( $status['success'] ) ) {
			$this->add_error(
				self::ERR_SERVICE,
				sprintf(
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);
			return false;
		}

		return true;
	}

}