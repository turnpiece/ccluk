<?php
/**
 * Shipper AJAX controllers: admin controller class
 *
 * @package shipper
 */

/**
 * Admin AJAX controller class
 */
class Shipper_Controller_Ajax_Admin extends Shipper_Controller_Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) { return false; }

		add_action( 'wp_ajax_shipper_modal_closed', array( $this, 'json_modal_closed' ) );
		add_action( 'wp_ajax_shipper_download_log', array( $this, 'handle_log_download' ) );
		add_action( 'wp_ajax_shipper_download_data', array( $this, 'handle_data_download' ) );
	}

	/**
	 * Handles data download attempts
	 */
	public function handle_data_download() {
		$this->do_request_sanity_check( 'shipper_data_download', self::TYPE_GET );
		if ( ! Shipper_Controller_Data::get()->is_data_recording_enabled() ) {
			// Nope, no download if not recording.
			wp_die();
		}

		$filename = Shipper_Model_Stored_Destinations::get_current_domain() .
			'-shipper-data-' .
			date( 'Y-m-d.H-i-s' ) . '.csv';

		@header( 'Content-Description: File Transfer' );
		@header( 'Content-Type: text/plain' );
		@header( 'Content-Type: text/plain' );
		@header( "Content-Disposition: attachment; filename={$filename}" );

		$path = Shipper_Helper_Log::get_file_path( 'debug', 'csv' );
		readfile( $path );
		wp_die();
	}

	/**
	 * Handles log download attempts
	 */
	public function handle_log_download() {
		$this->do_request_sanity_check( 'shipper_log_download', self::TYPE_GET );

		$filename = Shipper_Model_Stored_Destinations::get_current_domain() .
			'-shipper-log-' .
			date( 'Y-m-d.H-i-s' ) . '.txt';

		@header( 'Content-Description: File Transfer' );
		@header( 'Content-Type: text/plain' );
		@header( 'Content-Type: text/plain' );
		@header( "Content-Disposition: attachment; filename={$filename}" );

		echo esc_html( Shipper_Helper_Log::get_contents() );
		wp_die();
	}

	/**
	 * Stores a modal closed state
	 */
	public function json_modal_closed() {
		$this->do_request_sanity_check( 'shipper_modal_close' );
		// @codingStandardsIgnoreLine Nonce already checked in `do_request_sanity_check`
		$data = stripslashes_deep( $_POST );
		$modal = ! empty( $data['target'] )
			? sanitize_text_field( $data['target'] )
			: false
		;
		if ( ! empty( $modal ) ) {
			$modals = new Shipper_Model_Stored_Modals;
			$modals->set( $modal, Shipper_Model_Stored_Modals::STATE_CLOSED );
			$modals->save();
		}
		wp_send_json_success();
	}

}