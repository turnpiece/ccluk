<?php // phpcs:ignore

/**
 * Authenticated AJAX action controller
 */
class Snapshot_Controller_Full_Ajax extends Snapshot_Controller_Full {

	const OPTIONS_FLAG = 'snapshot_ajax_backup_run';

	/**
	 * Internal instance reference
	 *
	 * @var object Snapshot_Controller_Full_Ajax instance
	 */
	private static $_instance;

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_Controller_Full_Ajax instance
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Dispatch AJAX actions handling.
	 */
	public function run() {
		add_action( 'wp_ajax_snapshot-full_backup-check_requirements', array( $this, 'json_check_requirements' ) );

		add_action( 'wp_ajax_snapshot-full_backup-download', array( $this, 'json_download_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-delete', array( $this, 'json_delete_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-get_log', array( $this, 'json_get_log' ) );

		add_action( 'wp_ajax_snapshot-full_backup-reload', array( $this, 'json_reload_backups' ) );
		add_action( 'wp_ajax_snapshot-full_backup-reset_api', array( $this, 'json_reset_api' ) );

		add_action( 'wp_ajax_snapshot-full_backup-start', array( $this, 'json_start_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-estimate', array( $this, 'json_estimate_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-process', array( $this, 'json_process_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-finish', array( $this, 'json_finish_backup' ) );
		add_action( 'wp_ajax_snapshot-full_backup-abort', array( $this, 'json_finish_backup' ) );

		add_action( 'wp_ajax_snapshot-full_backup-restore', array( $this, 'json_start_restore' ) );

		add_action( 'wp_ajax_snapshot-full_backup-exchange_key', array( $this, 'json_remote_key_exchange' ) );
		add_action( 'wp_ajax_snapshot-full_backup-deactivate', array( $this, 'json_deactivate' ) );

		add_site_option( self::OPTIONS_FLAG, '' );
	}

	/**
	 * Deactivate
	 */
	public function json_deactivate() {
		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can reload */
		}
		return wp_send_json_success( Snapshot_Controller_Full_Admin::get()->deactivate() );
	}

	/**
	 * Runs on deactivation
	 */
	public function deactivate() {
		delete_site_option( self::OPTIONS_FLAG );
	}

	/**
	 * Outputs log file content
	 */
	public function json_get_log() {
		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can reload */
		}
		$response = __( 'Your log file is empty', SNAPSHOT_I18N_DOMAIN );
		$content = Snapshot_Helper_Log::get()->get_log();
		if ( ! empty( $content ) ) {
			$response = '<textarea readonly style="width:100%; height:100%">' . esc_textarea( $content ) . '</textarea>';
		}

		die( wp_kses_post( $response ) );
	}

	/**
	 * Sets up backup key exchange
	 */
	public function json_remote_key_exchange() {
		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can reload */
		}
		$rmt = Snapshot_Model_Full_Remote_Key::get();

		$token = $rmt->get_remote_key();
		if ( empty( $token ) ) {
			return wp_send_json_error( __( 'Unable to get exchange token', SNAPSHOT_I18N_DOMAIN ) );
		}

		$key = $rmt->get_remote_key( $token );
		if ( empty( $key ) ) {
			return wp_send_json_error( __( 'Unable to exchange key', SNAPSHOT_I18N_DOMAIN ) );
		}

		$status = $rmt->set_key( $key );
		if ( $status ) {
			$this->_model->set_config( 'active', true ); /* Also activate */
		}
		return wp_send_json_success( $status );
	}

	/**
	 * Forces backup list reloads
	 */
	public function json_reload_backups() {
		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can reload */
		}

		$status = $this->_model->remote()->reset_backups_cache( true );

		wp_send_json(array(
			'status' => $status,
		));
	}

	/**
	 * Forces S3 API info refresh
	 *
	 * @since v3.0.5-BETA-6
	 */
	public function json_reset_api() {
		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can do this */
		}

		$hub = Snapshot_Controller_Full_Hub::get();
		$status = $hub->clear_api_cache();

		$status = is_wp_error( $status )
			? $status->get_error_message()
			: 0
		;

		wp_send_json(array(
			'status' => $status,
		));
	}

	/**
	 * Prepare backup for download
	 */
	public function json_download_backup() {
		check_admin_referer( 'snapshot_download_backup' );

		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can restore */
		}
		if ( ! $this->_is_backup_processing_ready() ) {
			die;
		}

		$data = stripslashes_deep( $_POST );
		$timestamp = ! empty( $data['idx'] ) && is_numeric( $data['idx'] )
			? $data['idx']
			: false
		;
		if ( ! $timestamp ) {
			wp_send_json(array(
				'task' => 'clearing',
				'status' => false,
			));
		}

		$archive_path = $this->_model->local()->get_backup( $timestamp );
		if ( empty( $archive_path ) || ! file_exists( $archive_path ) ) {
			// No local backup, try and get the remote URL instead.
			$archive_path = $this->_model->remote()->get_backup_link( $timestamp );
			if ( empty( $archive_path ) ) {
				// Something went wrong with determining the remote URL.
				wp_send_json(array(
					'task' => 'fetching',
					'error' => ! ! $this->_model->has_errors(),
					'status' => false,
				));
			} else {
				// All good.
				wp_send_json(array(
					'task' => 'clearing',
					'status' => true,
					'nonce' => wp_create_nonce( 'snapshot-full_backups-download' ),
				));
			}
		} else {
			// If we don't have the full archive path yet, we're still fetching the file.
			if ( ! file_exists( $archive_path ) ) {
				wp_send_json(array(
					'task' => 'fetching',
					'error' => ! ! $this->_model->has_errors(),
					'status' => false,
				));
			} else {
				wp_send_json(array(
					'task' => 'clearing',
					'status' => true,
					'nonce' => wp_create_nonce( 'snapshot-full_backups-download' ),
				));
			}
		}

		// We shouldn't be getting here but oh well.
		die;
	}

	/**
	 * Delete remote backup and force cache cleanup.
	 */
	public function json_delete_backup() {
		check_admin_referer( 'snapshot_delete_backup' );
		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can restore */
		}
		if ( ! $this->_is_backup_processing_ready() ) {
			die;
		}

		$data = stripslashes_deep( $_POST );
		$timestamp = ! empty( $data['idx'] ) && is_numeric( $data['idx'] )
			? $data['idx']
			: false
		;
		if ( ! $timestamp ) {
			wp_send_json(array(
				'task' => 'clearing',
				'status' => false,
			));
		}

		$status = $this->_model->delete_backup( $timestamp );

		if ( ! empty( $status ) ) {
			// Update all settings, new list included.
			$this->_model->update_remote_schedule();
		}

		wp_send_json(array(
			'task' => 'clearing',
			'status' => $status,
		));
	}

	/**
	 * Check requirements
	 */
	public function json_check_requirements() {
		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die;
		}

		$minimum_exec_time = 150;

		// Check WP version.
		wp_version_check();
		$wp_state_response = get_site_transient( 'update_core' );
		$wp_state = ! empty( $wp_state_response->updates[0]->response )
			? ('latest' === $wp_state_response->updates[0]->response)
			: false
		;

		if ( ! $wp_state ) {
			Snapshot_Helper_Log::note( 'There has been an issue with determining WordPress state' );
		}

		// Fileset.
		$set = Snapshot_Model_Fileset::get_source( 'full' );
		$location = $set->get_root();

		if ( empty( $location ) || ! file_exists( $location ) ) {
			Snapshot_Helper_Log::note( 'There has been an issue with determining location' );
		}

		// Tables.
		$tables = Snapshot_Model_Queue_Tableset::get_all_tables();
		if ( empty( $tables ) ) {
			Snapshot_Helper_Log::note( 'There has been an issue with determining your database setup' );
		}

		$open_basedir = ini_get( 'open_basedir' );
		if ( $open_basedir ) {
			Snapshot_Helper_Log::note( 'It seems that open_basedir is in effect' );
		}

		$exec_time = ini_get( 'max_execution_time' );
		$runtime = (int) $exec_time >= $minimum_exec_time;
		if ( ! $runtime ) {
			Snapshot_Helper_Log::note( "Run time might not be enough: {$exec_time}" );
		}

		$mysqli = (bool) function_exists( 'mysqli_connect' );
		if ( ! $mysqli ) {
			Snapshot_Helper_Log::note( 'We do not seem to have mysqli available' );
		}

		wp_send_json(array(
			'webserver' => array(
				'system' => array(
					'value' => $_SERVER['SERVER_SOFTWARE'],
					'result' => true,
				),
			),
			'php' => array(
				'basedir' => array(
					'value' => $open_basedir ? __( 'Enabled', SNAPSHOT_I18N_DOMAIN ) : __( 'Disabled', SNAPSHOT_I18N_DOMAIN ),
					'result' => ! $open_basedir,
				),
				'maxtime' => array(
					'value' => $exec_time,
					'result' => $runtime,
				),
				'mysqli' => array(
					'value' => (int) $mysqli,
					'result' => (bool) $mysqli,
				),
			),
			'wordpress' => array(
				'version' => array(
					'value' => get_bloginfo( 'version' ),
					'result' => (bool) $wp_state,
				),
			),
			'fileset' => array(
				'location' => array(
					'value' => basename( $location ),
					'result' => file_exists( $location ),
				),
			),
			'tableset' => array(
				'quantity' => array(
					'value' => count( $tables ),
					'result' => (bool) count( $tables ),
				),
			),
		));
	}

	/**
	 * Process restore requests
	 */
	public function json_start_restore() {
		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		if ( ! current_user_can( Snapshot_View_Full_Backup::get()->get_page_role() ) ) {
			die; /* Only some users can restore. */
		}
		if ( ! $this->_is_backup_processing_ready() ) {
			die;
		}

		$data = stripslashes_deep( $_POST );
		$archive = ! empty( $data['archive'] ) && is_numeric( $data['archive'] )
			? $data['archive']
			: false
		;
		$restore_path = ! empty( $data['restore'] ) && file_exists( $data['restore'] )
			? $data['restore']
			: false
		;

		$credentials = ! empty( $data['credentials'] )
			? stripslashes_deep( $data['credentials'] )
			: true
		;

		// Signal intent - starting action.
		Snapshot_Helper_Log::start();

		if ( ! WP_Filesystem( $credentials ) ) {
			wp_send_json(array(
				'task' => 'clearing',
				'status' => false,
			));
		}

		if ( empty( $archive ) ) {
			wp_send_json(array(
				'task' => 'clearing',
				'status' => false,
			));
		}

		if ( empty( $restore_path ) ) {
			$restore_path = apply_filters( 'snapshot_home_path', get_home_path() );
		}

		if ( empty( $restore_path ) || ! file_exists( $restore_path ) ) {
			wp_send_json(array(
				'task' => 'clearing',
				'status' => false,
			));
		}

		$archive_path = $this->_model->get_backup( $archive );

		// If we don't have the full archive path yet, we're still fetching the file.
		if ( ! file_exists( $archive_path ) ) {
			wp_send_json(array(
				'task' => 'fetching',
				'error' => ! ! $this->_model->has_errors(),
				'status' => false,
			));
		}

		$restore = Snapshot_Helper_Restore::from( $archive_path );

		$rqueues = $restore->get_current_queues();
		if ( empty( $rqueues ) ) {
			wp_send_json(array(
				'task' => 'clearing',
				'status' => false,
			));
		}

		$restore->to( $restore_path );

		$task = $restore->is_done()
			? 'clearing'
			: 'restoring'
		;

		$status = 'clearing' === $task
			? $restore->clear()
			: $restore->process_files();

		if ( ! $status && $restore->get_copy_warning() ) {
			wp_send_json(array(
				'task' => 'clearing',
				'status' => false,
			));
		}

		// TODO: this should be handled separately.
		if ( 'clearing' === $task ) {
			unlink( $archive_path );
		}

		wp_send_json(array(
			'task' => $task,
			'status' => $status,
		));
	}

	/**
	 * Sends back backup size estimate
	 */
	public function json_estimate_backup() {
		if ( ! $this->_is_backup_processing_ready() ) {
			die;
		}

		$idx = $this->_get_backup_type();
		$backup = Snapshot_Helper_Backup::load( $idx );

		$total = $backup
			? $backup->get_total_steps_estimate()
			: 0
		;

		wp_send_json(array(
			'total' => $total,
		));
	}

	/**
	 * Backup start JSON handler
	 *
	 * First in the cascade of requests actually performing the backup
	 */
	public function json_start_backup() {
		if ( ! $this->_is_backup_processing_ready() ) {
			die;
		}

		// Signal intent - starting action.
		Snapshot_Helper_Log::start();

		$idx = $this->_get_backup_type();
		$this->_start_backup( $idx );

		update_site_option( self::OPTIONS_FLAG, true );

		wp_send_json(array(
			'id' => $idx,
		));
	}

	/**
	 * Backup processing JSON handler
	 *
	 * This will get called repeatedly, as long as the backup isn't ready
	 */
	public function json_process_backup() {
		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$data = stripslashes_deep( $_POST );
		$idx = ! empty( $data['idx'] ) ? $data['idx'] : $this->_get_backup_type();

		$status = false;
		try {
			$status = $this->_process_backup( $idx );
		} catch (Snapshot_Exception $e) {
			$key = $e->get_error_key();
			$msg = Snapshot_Model_Full_Error::get_human_description( $key );

			Snapshot_Helper_Log::error( "Error processing manual backup: {$key}" );
			Snapshot_Helper_Log::note( $msg );

			delete_site_option( self::OPTIONS_FLAG );

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 3.0-beta-12
			 *
			 * @param string Action type indicator (process or finish).
			 * @param string $key Error message key.
			 * @param string $msg Human-friendly message description.
			 */
			do_action( $this->get_filter( 'ajax_error_stop' ), 'process', $key, $msg ); // Notify anyone interested.

			die( esc_js( $msg ) );
		}

		wp_send_json(array(
			'done' => $status,
		));
	}

	/**
	 * Backup end JSON handler
	 *
	 * The last in the cascade of requests actually performing the backup
	 */
	public function json_finish_backup() {
		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$data = stripslashes_deep( $_POST );
		$idx = ! empty( $data['idx'] ) ? $data['idx'] : $this->_get_backup_type();

		try {
			$status = $this->_finish_backup( $idx );
		} catch (Snapshot_Exception $e) {
			$key = $e->get_error_key();
			$msg = Snapshot_Model_Full_Error::get_human_description( $key );

			Snapshot_Helper_Log::error( "Error finalizing manual backup: {$key}" );
			Snapshot_Helper_Log::note( $msg );

			delete_site_option( self::OPTIONS_FLAG );

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 3.0-beta-12
			 *
			 * @param string Action type indicator (process or finish).
			 * @param string $key Error message key.
			 * @param string $msg Human-friendly message description.
			 */
			do_action( $this->get_filter( 'ajax_error_stop' ), 'finish', $key, $msg ); // Notify anyone interested.

			die( esc_js( $msg ) );
		}

		delete_site_option( self::OPTIONS_FLAG );

		if ( ! $status && ! $this->_model->has_api_info() ) {
			$response = array(
				'status' => true,
				'msg' => __( 'Could not communicate with remote service', SNAPSHOT_I18N_DOMAIN ),
			);
		} else {
			$response = array(
				'status' => $status,
			);
		}

		wp_send_json( $response );
	}

}