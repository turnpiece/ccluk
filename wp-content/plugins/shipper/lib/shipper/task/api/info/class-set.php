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
	 * Get api cache ttl.
	 *
	 * @return int
	 */
	public function get_api_cache_ttl() {
		return 180;
	}

	/**
	 * Sets Hub API info for the current system
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration();

		$status = $this->get_response(
			'info-set',
			self::METHOD_POST,
			array(
				'domain' => Shipper_Model_Stored_Destinations::get_current_domain(),
				'dst'    => $migration->get_destination(),
				'info'   => wp_json_encode( $args ),
			)
		);

		if ( empty( $status['status'] ) && empty( $status['success'] ) ) {
			$this->record_non_success(
				'info-set',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: error message. */
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);
			return false;
		}

		$this->record_success( 'info-set' );
		return true;
	}

}