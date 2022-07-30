<?php
/**
 * Shipper tasks: system info request task
 *
 * @package shipper
 */

/**
 * Info getting task class
 */
class Shipper_Task_Api_Info_Get extends Shipper_Task_Api {

	/**
	 * Asks Hub API for the system info about the destination system
	 *
	 * @param array $args Optional, uses domain key if set, or migration.
	 *
	 * @return array
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration();
		$data      = array();
		$domain    = false;

		if ( ! empty( $args['domain'] ) ) {
			$domain = Shipper_Model_Stored_Destinations::get_normalized_domain(
				$args['domain']
			);
		} else {
			$domain = $migration->get_destination();
		}

		if ( ! $domain ) {
			return $data;
		}

		$status = $this->get_response(
			'info-get',
			self::METHOD_GET,
			array(
				'domain' => $domain,
			)
		);

		if ( empty( $status['success'] ) ) {
			$this->record_non_success(
				'info-get',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: error message. */
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);

			return $data;
		}

		$this->record_success( 'info-get' );

		return $status['data'];
	}

}