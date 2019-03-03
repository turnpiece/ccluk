<?php
/**
 * Shipper controllers: preflight heartbeat
 *
 * Updates preflight status
 *
 * @package shipper
 */

/**
 * Preflight heartbeat controller
 */
class Shipper_Controller_Heartbeat_Preflight extends Shipper_Controller_Heartbeat {

	/**
	 * Process Heartbeat API request
	 *
	 * Used for progress page updates.
	 *
	 * @param array $response Heartbeat response data to pass back to front end.
	 * @param array $data Data received from the front end (unslashed).
	 *
	 * @return array Response
	 */
	public function heartbeat( $response, $data ) {
		// If the request didn't originate from the migration page, no need to proceed.
		// If we're ever to do a global progress bar, kill this part.
		if ( empty( $data['shipper-preflight'] ) ) {
			unset( $response['shipper-preflight'] );
			return $response;
		}

		$ctrl = Shipper_Controller_Runner_Preflight::get();

		$preflight = $ctrl->get_status();
		$data = $preflight->get_data();
		if ( empty( $data ) ) {
			$migration = new Shipper_Model_Stored_Migration;
			$ctrl->start( $migration->get_destination() );
			$data = $preflight->get_data();
		}

		$system_checks = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM );
		$remote_checks = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE );
		$files_checks = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES );
		$all_done = $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE );

		$response['shipper-preflight'] = array(
			'is_done' => $all_done,
			'sections' => array(
				'system_checks' => array(
					'title' => __( 'Checking <b>Source Server\'s configurations</b>...', 'shipper' ),
					'checks' => $system_checks,
					'is_done' => ! empty( $system_checks ),
				),
				'remote_checks' => array(
					'title' => __( 'Checking <b>Destination Server\'s configurations</b>...', 'shipper' ),
					'checks' => $remote_checks,
					'is_done' => ! empty( $remote_checks ),
				),
				'files_checks' => array(
					'title' => __( 'Checking <b>files</b>...', 'shipper' ),
					'checks' => $files_checks,
					'is_done' => $all_done,
				),
			)
		);

		return $response;
	}
}