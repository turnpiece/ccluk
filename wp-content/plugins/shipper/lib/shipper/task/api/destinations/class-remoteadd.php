<?php
/**
 * Shipper tasks: trigger an add action on a Hub-connected website
 *
 * @package shipper
 */

/**
 * API Add action triggering API call
 */
class Shipper_Task_Api_Destinations_Remoteadd extends Shipper_Task_Api {

	/**
	 * Triggers add action on a Hub-connected website
	 *
	 * @param array $args Array with site ID as the only member.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$domain = reset( $args );
		$status = $this->get_response(
			'destinations-preparelist',
			self::METHOD_POST,
			array(
				'domain' => $domain,
			)
		);

		if ( empty( $status['success'] ) ) {
			$this->clear_cached_api_response( 'destinations-preparelist' );
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %1$s %2$s: domain name and error message. */
					__( 'Error triggering add for site %1$s: %2$s', 'shipper' ),
					$domain,
					$this->get_formatted_error( $status )
				)
			);
			return false;
		}

		$this->record_success( 'destinations-preparelist' );
		return true;
	}
}