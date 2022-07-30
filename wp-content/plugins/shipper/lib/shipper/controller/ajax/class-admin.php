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
		if ( ! is_admin() ) {
			return false;
		}

		add_action( 'wp_ajax_shipper_modal_closed', array( $this, 'json_modal_closed' ) );
		add_action( 'wp_ajax_shipper_download_log', array( $this, 'handle_log_download' ) );
		add_action( 'wp_ajax_shipper_download_data', array( $this, 'handle_data_download' ) );
		add_action( 'wp_ajax_shipper_new_features_modal_closed', array( $this, 'handle_modal_close' ) );
		add_action( 'wp_ajax_shipper_hide_black_friday', array( $this, 'json_hide_black_friday' ) );
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

		$filename = Shipper_Model_Stored_Destinations::get_current_domain() . '-shipper-data-' . gmdate( 'Y-m-d.H-i-s' ) . '.csv';

		@header( 'Content-Description: File Transfer' );
		@header( 'Content-Type: text/plain' );
		@header( 'Content-Type: text/plain' );
		@header( "Content-Disposition: attachment; filename={$filename}" );

		$path = Shipper_Helper_Log::get_file_path( 'debug', 'csv' );
		$fs   = Shipper_Helper_Fs_File::open( $path );

		if ( ! $fs ) {
			return false;
		}

		echo $fs->fread( $fs->getSize() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
	}

	/**
	 * Handles log download attempts
	 */
	public function handle_log_download() {
		$this->do_request_sanity_check( 'shipper_log_download', self::TYPE_GET );

		$filename = Shipper_Model_Stored_Destinations::get_current_domain() . '-shipper-log-' . gmdate( 'Y-m-d.H-i-s' ) . '.txt';

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
			: false;

		if ( 'system' === $modal ) {
			$system = new Shipper_Model_System();
			$check  = new Shipper_Task_Check_System();
			$check->apply( $system->get_data() );
			if ( $check->has_checks_with_errors() ) {
				// Do not record system modal as seen if it has errors.
				$modal = false;
			}
		}

		if ( ! empty( $modal ) ) {
			$modals = new Shipper_Model_Stored_Modals();
			$modals->set( $modal, Shipper_Model_Stored_Modals::STATE_CLOSED );
			$modals->save();
		}
		wp_send_json_success();
	}

	/**
	 * Store modal new feature modal close state
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function handle_modal_close() {
		$this->do_request_sanity_check( 'shipper_new_features_modal_closed' );

		$model = new Shipper_Model_Newfeatures();
		$model->set( 'new-feature-version', SHIPPER_VERSION );
		$model->save();
	}

	/**
	 * Hide Black Friday banner.
	 *
	 * @since 1.2.10
	 */
	public function json_hide_black_friday() {
		$this->do_request_sanity_check( 'shipper_hide_black_friday' );
		update_site_option( 'shipper_bf_banner_seen', 1 );
		wp_send_json_success();
	}
}