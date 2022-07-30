<?php
/**
 * Shipper tasks: migration status getter task
 *
 * @package shipper
 */

/**
 * Migration getting task class
 */
class Shipper_Task_Api_Migrations_Get extends Shipper_Task_Api {

	/**
	 * Asks Hub API for the destination export archive status
	 *
	 * @param array $args Uses the domain key to hold domain info.
	 *
	 * @return array Migration status info
	 */
	public function apply( $args = array() ) {
		$domain = ! empty( $args['domain'] )
			? $args['domain']
			: false;

		if ( empty( $domain ) ) {
			$this->clear_cached_api_response( 'migration-get' );
			$this->add_error(
				self::ERR_REQFORMAT,
				__( 'Missing domain to check', 'shipper' )
			);
			return false;
		}

		$status = $this->get_response(
			'migration-get',
			self::METHOD_GET,
			array(
				'domain'  => $domain,
				'version' => SHIPPER_VERSION,
			)
		);

		if ( empty( $status['success'] ) ) {
			$this->record_non_success(
				'migration-get',
				self::ERR_SERVICE,
				sprintf(
					/* translators: error message. */
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);
			return array();
		}

		$data = ! empty( $status['data'] )
			? $status['data']
			: array();

		$this->record_success( 'migration-get' );
		return $data;
	}

}