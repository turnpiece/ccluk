<?php
/**
 * Shipper tasks: destinations API listing call
 *
 * @package shipper
 */

/**
 * Destinations API getter class
 */
class Shipper_Task_Api_Destinations_Get extends Shipper_Task_Api {

	/**
	 * Gets a list of known shipper destinations from the Hub
	 *
	 * @param array $args Unused.
	 *
	 * @return array List of destination hashes.
	 */
	public function apply( $args = array() ) {
		$status       = $this->get_response( 'destinations-get' );
		$destinations = array();

		if ( empty( $status['success'] ) ) {
			$this->clear_cached_api_response( 'destinations-get' );
			$this->add_error(
				self::ERR_SERVICE,
				__( 'Error listing destinations: service encountered an error', 'shipper' )
			);
			return $destinations;
		}

		$destinations = ! empty( $status['data'] ) && is_array( $status['data'] )
			? $status['data']
			: array();

		if ( empty( $destinations ) ) {
			$this->clear_cached_api_response( 'destinations-get' );
			$this->add_error(
				self::ERR_SERVICE,
				__( 'Error listing destinations: service responded with an empty list', 'shipper' )
			);
			return $destinations;
		}

		$this->record_success( 'destinations-get' );
		return $destinations;
	}
}