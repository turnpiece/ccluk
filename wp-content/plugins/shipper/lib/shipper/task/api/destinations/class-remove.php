<?php
/**
 * Shipper tasks: destination removing task
 *
 * @package shipper
 */

/**
 * Destination remover task class
 */
class Shipper_Task_Api_Destinations_Remove extends Shipper_Task_Api {

	/**
	 * Removes a destination from the Shipper API
	 *
	 * @param array $args Uses the site_id key to remove the actual site ID.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$site_id = ! empty( $args['site_id'] )
			? $args['site_id']
			: false;

		$status = $this->get_response(
			'destinations-remove',
			self::METHOD_POST,
			array(
				'site_id' => $site_id,
			)
		);

		if ( empty( $status['success'] ) ) {
			$this->record_non_success(
				'destinations-remove',
				self::ERR_SERVICE,
				sprintf(
					/* translators: %s: domain name. */
					__( 'Error removing domain %s from API list', 'shipper' ),
					$domain
				)
			);
			return false;
		}

		$this->record_success( 'destinations-remove' );
		return true;
	}

}