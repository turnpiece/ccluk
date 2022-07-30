<?php
/**
 * Shipper controllers: debug overrides
 *
 * @package shipper
 */

/**
 * Debug overrides controller class
 */
class Shipper_Controller_Override_Debug extends Shipper_Controller_Override {

	/**
	 * Prepares the filters for debugging internals
	 *
	 * @param object $constants Optional constants checking object.
	 */
	public function prepare_debugging_overrides( $constants = false ) {
		if ( ! is_object( $constants ) ) {
			$constants = new Shipper_Model_Constants_Shipper();
		}
		if ( $constants->is_defined( 'SHIPPER_I_KNOW_WHAT_IM_DOING' ) ) {
			if ( $constants->get( 'SHIPPER_I_KNOW_WHAT_IM_DOING' ) ) {
				add_filter( 'shipper_internals_is_in_debug_mode', '__return_true' );
			} else {
				// Maybe I really don't?
				add_filter( 'shipper_internals_is_in_debug_mode', '__return_false' );
			}
		}

		if ( $constants->is_defined( 'SHIPPER_DEBUG_LOG' ) ) {
			$callback = (bool) $constants->get( 'SHIPPER_DEBUG_LOG' )
				? '__return_true'
				: '__return_false';
			add_filter( 'shipper_log_debug_statements', $callback );
		}
	}

	/**
	 * Prepares the filters for data recording
	 *
	 * @param object $constants Optional constants checking object.
	 */
	public function prepare_data_overrides( $constants = false ) {
		if ( ! is_object( $constants ) ) {
			$constants = new Shipper_Model_Constants_Shipper();
		}

		$callback = $constants->get( 'SHIPPER_DATA_RECORDING' )
			? '__return_true'
			: '__return_false';
		add_filter(
			'shipper_enable_data_recording',
			$callback
		);
	}

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		$this->prepare_debugging_overrides();
		$this->prepare_data_overrides();
	}
}