<?php
/**
 * Shipper controllers: timeouts overrides
 *
 * @package shipper
 */

/**
 * Timeouts overrides controller class
 */
class Shipper_Controller_Override_Timeouts extends Shipper_Controller_Override {

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		if ( $this->get_constants()->is_defined( 'SHIPPER_RUNNER_PING_TIMEOUT' ) ) {
			add_filter(
				'shipper_runner_ping_timeout',
				array( $this, 'apply_runner_ping_timeout' )
			);
		}
	}

	/**
	 * Applies runner ping request timeout define value
	 *
	 * @param int|float $timeout Timeout this far.
	 *
	 * @return float Timeout
	 */
	public function apply_runner_ping_timeout( $timeout ) {
		$tm = $this->get_constants()->get( 'SHIPPER_RUNNER_PING_TIMEOUT' );
		return is_numeric( $tm )
			? (float) $tm
			: $timeout
		;
	}
}