<?php
/**
 * Shipper tasks: migration canceling Hub action
 *
 * @package shipper
 */

/**
 * Migration remote canceling task class
 */
class Shipper_Task_Api_Migrations_Cancel extends Shipper_Task_Api {

	/**
	 * Asks Hub API for the remote migration cancel on the destination URL
	 *
	 * @param array $args Uses the domain key to hold domain info.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$source = ! empty( $args['domain'] )
			? $args['domain']
			: false;

		if ( empty( $source ) ) {
			$this->clear_cached_api_response( 'migration-cancel' );
			$this->add_error(
				self::ERR_REQFORMAT,
				__( 'Missing domain to cancel migration', 'shipper' )
			);
			return false;
		}

		$status = $this->get_response(
			'migration-cancel',
			self::METHOD_POST,
			array(
				'domain'  => $source,
				'version' => SHIPPER_VERSION,
			)
		);

		if ( empty( $status['status'] ) ) {
			$this->record_non_success(
				'migration-cancel',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: error message. */
					__( 'Service error: %s', 'shipper' ),
					$this->get_formatted_error( $status )
				)
			);
			return false;
		}

		$this->record_success( 'migration-cancel' );
		return true;
	}

}