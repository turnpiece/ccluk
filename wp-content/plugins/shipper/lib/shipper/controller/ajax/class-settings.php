<?php
/**
 * Shipper AJAX controllers: settings controller class
 *
 * @package shipper
 */

/**
 * Settings AJAX controller class
 */
class Shipper_Controller_Ajax_Settings extends Shipper_Controller_Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false; }

		add_action(
			'wp_ajax_shipper_reset_settings',
			array( $this, 'json_reset_settings' )
		);
	}

	/**
	 * Resets all settings
	 */
	public function json_reset_settings() {
		$this->do_request_sanity_check( 'shipper_reset_settings' );

		$model = new Shipper_Model_Stored_Options();
		$model->clear()->save();

		wp_send_json_success();
	}

}