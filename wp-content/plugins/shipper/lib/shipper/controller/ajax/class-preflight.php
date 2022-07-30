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
		if ( ! is_admin() ) {
			return false;
		}

		add_action(
			'wp_ajax_shipper_preflight_restart',
			array( $this, 'json_restart_preflight' )
		);
		add_action(
			'wp_ajax_shipper_preflight_get_results_markup',
			array( $this, 'json_get_results_markup' )
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
			'wp_ajax_shipper_bulk_process_paths',
			array( $this, 'json_bulk_process_paths' )
		);
		add_action(
			'wp_ajax_shipper_get_path_exclusions',
			array( $this, 'json_get_path_exclusions' )
		);
		add_action(
			'wp_ajax_shipper_get_package_size_message',
			array( $this, 'json_get_package_size_message' )
		);
	}

	/**
	 * Sends package size message back to client
	 */
	public function json_get_package_size_message() {
		$this->do_request_sanity_check();

		$chk          = new Shipper_Task_Check_Files();
		$package_size = $chk->get_updated_package_size();
		$threshold    = Shipper_Model_Stored_Migration::get_package_size_threshold();
		$exclusions   = new Shipper_Model_Stored_Exclusions();

		$tpl    = new Shipper_Helper_Template();
		$markup = $tpl->get(
			'pages/preflight/wizard-files-package_size-summary',
			array(
				'package_size' => $package_size,
				'threshold'    => $threshold,
			)
		);
		wp_send_json_success(
			array(
				'excluded'     => count( $exclusions->get_data() ),
				'package_size' => size_format( $package_size ),
				'oversized'    => $package_size > $threshold,
				'markup'       => $markup,
			)
		);
	}

	/**
	 * Send path exclusions back to client
	 */
	public function json_get_path_exclusions() {
		$this->do_request_sanity_check();
		$exclusions = new Shipper_Model_Stored_Exclusions();
		wp_send_json_success( $exclusions->get_data() );
	}

	/**
	 * Toggles path exclusion state for a migration
	 */
	public function json_toggle_path_exclusion() {
		$this->do_request_sanity_check( 'shipper_path_toggle' );
		// @codingStandardsIgnoreLine Nonce already checked in `do_request_sanity_check`
		$data = stripslashes_deep( $_POST );

		$exclusions = new Shipper_Model_Stored_Exclusions();
		$ex_include = new Shipper_Model_Stored_ExcludeInclude();
		$paths      = $exclusions->get_data();

		if ( ! empty( $data['path'] ) ) {
			$path = wp_normalize_path( realpath( $data['path'] ) );

			if ( ! empty( $path ) ) {
				if ( ! in_array( $path, array_keys( $paths ), true ) ) {
					$exclusions->set( $path, md5( $path ) )->save();
					$ex_include->set_excludes( array( $path ) );
				} else {
					$exclusions->remove( $path )->save();
					$ex_include->set_includes( array( $path ) );
				}
			}
		}

		wp_send_json_success( $exclusions->get_data() );
	}

	/**
	 * Bulk path actions processing method
	 *
	 * @since v1.0.3
	 */
	public function json_bulk_process_paths() {
		$this->do_request_sanity_check();
		// @codingStandardsIgnoreLine Nonce already checked in `do_request_sanity_check`
		$data       = stripslashes_deep( $_POST );
		$data_paths = ! empty( $data['paths'] ) && is_array( $data['paths'] )
			? $data['paths']
			: array();

		if ( empty( $data['apply'] ) ) {
			return wp_send_json_error();
		}

		$action     = 'exclude' === $data['apply'] ? 'exclude' : 'include';
		$exclusions = new Shipper_Model_Stored_Exclusions();
		$ex_include = new Shipper_Model_Stored_ExcludeInclude();
		$paths      = $exclusions->get_data();

		foreach ( $data_paths as $path_item ) {
			if ( empty( $path_item['path'] ) || empty( $path_item['_wpnonce'] ) ) {
				continue;
			}

			$path = wp_normalize_path( realpath( $path_item['path'] ) );

			if ( empty( $path ) ) {
				continue;
			}

			if ( 'exclude' === $action && ! in_array( $path, array_keys( $paths ), true ) ) {
				$exclusions->set( $path, md5( $path ) );
				$ex_include->set_excludes( array( $path ) );
			}
			if ( 'include' === $action && in_array( $path, array_keys( $paths ), true ) ) {
				$exclusions->remove( $path );
				$ex_include->set_includes( array( $path ) );
			}
		}

		$exclusions->save();
		wp_send_json_success( $exclusions->get_data() );
	}

	/**
	 * Restarts preflight checks
	 */
	public function json_restart_preflight() {
		$this->do_request_sanity_check();

		/**
		 * Clear previously stored ping
		 *
		 * Since 1.2.6
		 */
		( new Shipper_Model_Stored_Ping() )->clear()->save();

		$ctrl = Shipper_Controller_Runner_Preflight::get();
		if ( ! $ctrl->get_status()->get( Shipper_Model_Stored_Preflight::KEY_DONE ) ) {
			wp_send_json_error( __( 'Preflight still running', 'shipper' ) );
		}

		$data    = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- alrady checked
		$section = ! empty( $data['section'] )
			? $data['section']
			: false;
		if ( 'sysdiff' === $section ) {
			// System differences are processed on remote parsing.
			$section = 'remote';
		}

		if ( 'local' === $section || empty( $section ) ) {
			$task = new Shipper_Task_Check_System();
			$task->restart();
		}

		if ( 'remote' === $section || empty( $section ) ) {
			$task = new Shipper_Task_Check_RSystem();
			$task->restart();

			$task = new Shipper_Task_Check_Sysdiff();
			$task->restart();
		}

		if ( 'files' === $section || empty( $section ) ) {
			$task = new Shipper_Task_Check_Files();
			$task->restart();

			$task = new Shipper_Task_Check_Rpkg();
			$task->restart();
		}

		if ( empty( $section ) ) {
			$ctrl->clear();
			$ctrl->ping();
		} else {
			$key = false;
			if ( 'local' === $section ) {
				$key = Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM;
			} elseif ( 'remote' === $section ) {
				$key = Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE;
			}

			$changed = false;
			if ( $key ) {
				$changed = true;
				$ctrl->get_status()
					->set_check( $key, false )
					->clear_check_errors( $key )
					->set( Shipper_Model_Stored_Preflight::KEY_DONE, false )
					->save();
			} elseif ( 'files' === $section ) {
				$changed = true;
				$ctrl->get_status()
					->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES, false )
					->clear_check_errors( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES )
					->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG, false )
					->clear_check_errors( Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG )
					->set( Shipper_Model_Stored_Preflight::KEY_DONE, false )
					->save();
			}

			if ( $changed ) {
				$ctrl->ping();
			}
		}

		return wp_send_json_success();
	}

	/**
	 * Sends preflight results markup gathered this far
	 *
	 * @since v1.0.3
	 */
	public function json_get_results_markup() {
		$this->do_request_sanity_check();
		$ctrl = Shipper_Controller_Runner_Preflight::get();
		if ( ! $ctrl->get_status()->get( Shipper_Model_Stored_Preflight::KEY_DONE ) ) {
			return wp_send_json_error(); // Not done yet.
		}
		$tpl      = new Shipper_Helper_Template();
		$response = $tpl->get(
			'modals/preflight',
			array(
				'modal'      => 'results',
				'is_recheck' => true,
			)
		);

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
		$data      = $preflight->get_data();
		$ctrl->clear();

		return empty( $data )
			? wp_send_json_success()
			: wp_send_json_error();
	}

}