<?php
/**
 * Shipper tasks: migration status setter task
 *
 * @package shipper
 */

/**
 * Migration setting task class
 */
class Shipper_Task_Api_Migrations_Set extends Shipper_Task_Api {

	/**
	 * Updates migration status within the Hub API
	 *
	 * @param array $args Uses the domain key to hold domain info, and the rest for status.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$domain = ! empty( $args['domain'] )
			? $args['domain']
			: false;
		$file   = ! empty( $args['file'] )
			? $args['file']
			: false;
		$status = ! empty( $args['status'] )
			? $args['status']
			: false;
		$type   = ! empty( $args['type'] )
			? $args['type']
			: false;

		if ( empty( $domain ) ) {
			$this->clear_cached_api_response( 'migration-set' );
			$this->add_error(
				self::ERR_REQFORMAT,
				__( 'Missing domain to update', 'shipper' )
			);
			return false;
		}

		$args   = array(
			'domain'  => $domain,
			'file'    => $file,
			'type'    => $type,
			'status'  => $status,
			'version' => SHIPPER_VERSION,
		);
		$status = $this->get_response( 'migration-set', self::METHOD_POST, $args );

		if ( empty( $status['success'] ) ) {
			$this->record_non_success(
				'migration-set',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: error message. */
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);
			return false;
		}

		$this->record_success( 'migration-set' );
		return true;
	}

}