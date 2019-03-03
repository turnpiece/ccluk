<?php
/**
 * Shipper AJAX controllers: preflight controller class
 *
 * @package shipper
 */

/**
 * Preflight AJAX controller class
 */
class Shipper_Controller_Ajax_Preflight extends Shipper_Controller_Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) { return false; }

		add_action(
			'wp_ajax_shipper_preflight_restart',
			array( $this, 'json_restart_preflight' )
		);
		add_action(
			'wp_ajax_shipper_preflight_cancel',
			array( $this, 'json_cancel_preflight' )
		);
		add_action(
			'wp_ajax_shipper_toggle_path_exclusion',
			array( $this, 'json_toggle_path_exclusion' )
		);
		add_action(
			'wp_ajax_shipper_get_path_exclusions',
			array( $this, 'json_get_path_exclusions' )
		);
	}

	/**
	 * Send path exclusions back to client
	 */
	public function json_get_path_exclusions() {
		$this->do_request_sanity_check();
		$exclusions = new Shipper_Model_Stored_Exclusions;
		wp_send_json_success( $exclusions->get_data() );
	}

	/**
	 * Toggles path exclusion state for a migration
	 */
	public function json_toggle_path_exclusion() {
		$this->do_request_sanity_check( 'shipper_path_toggle' );
		// @codingStandardsIgnoreLine Nonce already checked in `do_request_sanity_check`
		$data = stripslashes_deep( $_POST );

		$exclusions = new Shipper_Model_Stored_Exclusions;
		$paths = $exclusions->get_data();

		$root_rx = preg_quote(
			realpath( ABSPATH ),
			'/'
		);

		if ( ! empty( $data['path'] ) ) {
			$path = wp_normalize_path( realpath( $data['path'] ) );
			$path_windows = realpath( $data['path'] ); // Check for Windows paths as well.
			if ( ! empty( $path ) ) {
				// Check if this is a sub-path of root.
				if ( ! preg_match( "/^{$root_rx}/", $path ) && ! preg_match( "/^{$root_rx}/", $path_windows ) ) {
					$path = false;
				}
			}
			if ( ! empty( $path ) ) {
				if ( ! in_array( $path, array_keys( $paths ), true ) ) {
					$exclusions->set( $path, md5( $path ) )->save();
				} else {
					$exclusions->remove( $path )->save();
				}
			}
		}

		wp_send_json_success( $exclusions->get_data() );
	}

	/**
	 * Restarts preflight checks
	 */
	public function json_restart_preflight() {
		$this->do_request_sanity_check();
		$task = new Shipper_Task_Check_System;
		$task->restart();

		$task = new Shipper_Task_Check_Files;
		$task->restart();

		$task = new Shipper_Task_Check_Sysdiff;
		$task->restart();

		$ctrl = Shipper_Controller_Runner_Preflight::get();
		$ctrl->clear();

		$tpl = new Shipper_Helper_Template;
		$response = array();
		foreach ( $ctrl->get_status()->get_check_types() as $type ) {
			$response[ $type ] = $tpl->get('modals/check/preflight-row', array(
				'type' => $type,
			));
		}
		return wp_send_json_success( $response );
	}

	/**
	 * Cancels preflight checks
	 */
	public function json_cancel_preflight() {
		$this->do_request_sanity_check();
		$ctrl = Shipper_Controller_Runner_Preflight::get();
		$ctrl->attempt_cancel();

		$preflight = $ctrl->get_status();
		$data = $preflight->get_data();
		$ctrl->clear();

		return ! empty( $data )
			? wp_send_json_success()
			: wp_send_json_error();
	}

}