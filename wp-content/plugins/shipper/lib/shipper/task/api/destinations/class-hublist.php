<?php
/**
 * Shipper tasks: list all Hub-connected sites
 *
 * @package shipper
 */

/**
 * Hub-connected sites API getter class
 */
class Shipper_Task_Api_Destinations_Hublist extends Shipper_Task_Api {

	/**
	 * Priority cache
	 *
	 * @var bool
	 */
	protected $priority_cache = true;

	/**
	 * Gets maximum API cache time for this task
	 *
	 * This can be a bit longer.
	 *
	 * @return int
	 * @since v1.0.3
	 */
	public function get_api_cache_ttl() {
		return 300;
	}

	/**
	 * Gets a list of all Hub-connected sites
	 *
	 * @param array $args Unused.
	 *
	 * @return array List of destination hashes.
	 */
	public function apply( $args = array() ) {
		if ( isset( $args['priority_cache'] ) ) {
			$this->priority_cache = $args['priority_cache'];
			apply_filters( 'shipper_check_api_communication_health_state', '__return_true' );
		}
		$status       = $this->get_response( 'destinations-listall' );
		$destinations = array();

		if ( empty( $status['success'] ) ) {
			$this->record_non_success(
				'destinations-listall',
				self::ERR_SERVICE,
				__( 'Error listing Hub sites: service encountered an error', 'shipper' )
			);

			return $destinations;
		}

		$destinations = ! empty( $status['data'] ) && is_array( $status['data'] )
			? $status['data']
			: array();

		if ( empty( $destinations ) ) {
			$this->record_non_success(
				'destinations-listall',
				self::ERR_SERVICE,
				__( 'Error listing Hub sites: service responded with an empty list', 'shipper' )
			);

			return $destinations;
		}

		$this->record_success( 'destinations-listall' );

		return $destinations;
	}

	/**
	 * Is cacheable.
	 *
	 * @return bool
	 */
	public function is_cacheable() {
		return $this->priority_cache;
	}
}