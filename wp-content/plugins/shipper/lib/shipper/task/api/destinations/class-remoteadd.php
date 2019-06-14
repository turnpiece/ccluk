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
		$status = $this->get_response( 'destinations-preparelist', self::METHOD_POST, array(
			'domain' => $domain,
		));

		if ( empty( $status['success'] ) ) {
			Shipper_Helper_Log::write(
				sprintf(
					__( 'Error triggering add for site %s: %s', 'shipper' ),
					$domain, $this->get_formatted_error( $status )
				)
			);
			return false;
		}

		return true;
	}
}
