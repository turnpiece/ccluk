<?php
/**
 * Shipper tasks: preflight info request task
 *
 * @package shipper
 */

/**
 * Info getting task class
 */
class Shipper_Task_Api_Info_Preflight extends Shipper_Task_Api {

	/**
	 * Get api cache ttl.
	 *
	 * @return int
	 */
	public function get_api_cache_ttl() {
		return 30;
	}

	/**
	 * Asks Hub API for the system info about the destination system
	 *
	 * @param array $args Not used.
	 *
	 * @return array
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration();
		$data      = array(
			'target' => Shipper_Model_Stored_Destinations::get_current_domain(),
			'domain' => $migration->get_destination(),
		);

		$status = $this->get_response( 'info-preflight', self::METHOD_GET, $data );

		if ( ! isset( $status['is_done'] ) ) {
			$this->record_non_success(
				'info-preflight',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: error message. */
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);
			return array();
		}

		$this->record_success( 'info-preflight' );
		return $status;
	}

}