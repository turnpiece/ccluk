<?php
/**
 * Shipper controllers: deactivation setup controller
 *
 * Handles plugin deactivation.
 *
 * @package shipper
 */

/**
 * Setup deactivation class
 */
class Shipper_Controller_Setup_Deactivate extends Shipper_Controller_Setup {

	/**
	 * Runs on plugin deactivation
	 */
	public static function deactivate() {
		self::get()
			->clear_intermediate_tables()
			->clear_api_errors()
			->clear_fs_storage()
			->clear_stub_storage()
			->clear_req_checks_modal();
	}

	/**
	 * Clears out API errors cache
	 *
	 * @uses Shipper_Model_Api
	 * @since v1.0.3
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_api_errors() {
		$api = new Shipper_Model_Api();
		$api->reset_api_fails();

		return $this;
	}

	/**
	 * Clears requirements check
	 *
	 * Actually just clears the modal flag, this is always up to date anyway.
	 *
	 * @param object $options Optional Shipper_Model_Stored_Options instance (used in tests).
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_req_checks_modal( $options = false ) {
		if ( ! is_object( $options ) ) {
			$options = new Shipper_Model_Stored_Options();
		}
		if ( $options->get( Shipper_Model_Stored_Options::KEY_DATA ) ) {
			// Preserve data, we don't care.
			return $this;
		}

		$modals = new Shipper_Model_Stored_Modals();
		$modals->set( 'system', Shipper_Model_Stored_Modals::STATE_OPEN );
		$modals->save();

		return $this;
	}
}