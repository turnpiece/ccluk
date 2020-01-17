<?php
/**
 * Shipper tasks: prepare a Hub-connected website for migration
 *
 * @package shipper
 */

/**
 * Migration preparation API call
 */
class Shipper_Task_Api_Destinations_Hubprepare extends Shipper_Task_Api {

	/**
	 * Prepares a Hub-connected site for a migration
	 *
	 * @param array $args Array with site ID as the only member.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$domain = reset( $args );
		$status = $this->get_response( 'destinations-prepare', self::METHOD_POST, array(
			'domain' => $domain,
		));

		if ( empty( $status['success'] ) ) {
			$this->clear_cached_api_response( 'destinations-prepare' );
			Shipper_Helper_Log::write(
				sprintf(
					__( 'Error preparing site %s: %s', 'shipper' ),
					$domain, $this->get_formatted_error( $status )
				)
			);
			return false;
		}

		$this->record_success( 'destinations-prepare' );
		return true;
	}
}