<?php // phpcs:ignore

/**
 * Admin pages controller
 */
class Snapshot_Controller_Full_Admin extends Snapshot_Controller_Full {

	const CODE_ERROR_BULK_DELETE = 'bdel';
	const CODE_ERROR_DOWNLOAD = 'download';

	/**
	 * Singleton instance
	 *
	 * @var object Snapshot_Controller_Full_Admin
	 */
	private static $_instance;

	/**
	 * View instance object
	 *
	 * @var object Snapshot_View_Full_Backup
	 */
	private $_view;

	/**
	 * Constructor - never to the outside world
	 */
	protected function __construct() {
		parent::__construct();
		$this->_view = Snapshot_View_Full_Backup::get();
	}

	/**
	 * Gets singleton instance
	 *
	 * @return object Snapshot_Controller_Full_Admin instance
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Serves the controller
	 */
	public function run() {
		$this->_view->run();
		add_action( 'current_screen', array( $this, 'process_submissions' ) );
	}

	/**
	 * Runs on plugin deactivation
	 *
	 * Removes config settings
	 */
	public function deactivate() {
		$this->_model->set_config( 'active', false );
		$this->_model->set_config( 'frequency', false );
		$this->_model->set_config( 'schedule_time', false );
		$this->_model->set_config( 'secret-key', false );

		$this->_model->remote()->remove_token();
	}

	/**
	 * Dispatch submission processing.
	 */
	public function process_submissions() {
		if ( is_multisite() && ! is_super_admin() && ! is_network_admin() ) {
			return false;
		}

		// phpcs:ignore
		if ( ! $this->_view->is_current_admin_page() && ! ( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), array( "snapshot_pro_settings", "snapshot_pro_managed_backups" ), true ) ) ) {
			return false;
		}

		if ( ! current_user_can( $this->_view->get_page_role() ) ) {
			return false;
		}

		// phpcs:ignore
		if ( isset( $_GET['action'] ) && 'snapshot_pro_managed_backups' === $_GET['page'] && 'delete' === $_GET['action'] ) {
			$_POST = $_GET; // phpcs:ignore
		}
		$data = new Snapshot_Model_Post();

		if ( $data->is_empty() ) {
			return false;
		}

		if ( $data->has( 'activate' ) ) {
			$this->_activate_backups( $data );
		}
		if ( $data->has( 'snapshot-disable-all' ) ) {
			$this->_deactivate_all( $data );
		}
		if ( $data->has( 'snapshot-settings' ) ) {
			$this->_update_settings( $data );
		}
		if ( $data->has( 'snapshot-schedule' ) ) {
			$this->_schedule_backups( $data );
		}
		if ( $data->has( 'snapshot-disable-cron' ) ) {
			$this->_deactivate_backups( $data );
		}
		if ( $data->has( 'snapshot-enable-cron' ) ) {
			$this->_reenable_cron_backups( $data );
		}
		if ( $data->has( 'snapshot-full_backups-list-nonce' ) && $data->has( 'delete-bulk' ) && $data->has( 'action' ) && 'delete' === $data->value( 'action' ) ) {
			$this->_bulk_delete( $data );
		}
	}

	/**
	 * Deletes the snapshots in bulk
	 *
	 * @param Snapshot_Model_Post $data Request data
	 *
	 * @return bool False on failure
	 */
	private function _bulk_delete( Snapshot_Model_Post $data ) {
		if (
			! current_user_can( $this->_view->get_page_role() )
			||
			! wp_verify_nonce( $data->value( 'snapshot-full_backups-list-nonce' ), 'snapshot-full_backups-list' )
		) {
			return false;
		}
		if ( ! $this->_is_backup_processing_ready() ) {
			return false;
		}

		$to_remove = $data->value( 'delete-bulk' );
		if ( empty( $to_remove ) || ! is_array( $to_remove ) ) {
			return false;
		} // Not valid data

		$status = true; // Assume all is good
		foreach ( $to_remove as $timestamp ) {
			$timestamp = (int) $timestamp;
			if ( ! $timestamp ) {
				continue;
			} // Not a valid timestamp

			$status = $this->_model->delete_backup( $timestamp );
			if ( ! $status ) {
				break;
			}
		}

		if ( ! empty( $status ) ) {
			// Update all settings, new list included
			$this->_model->update_remote_schedule();
		}

		$url = WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' );
		$url = ! empty( $status )
			? remove_query_arg( 'error', $url )
			: add_query_arg( 'error', self::CODE_ERROR_BULK_DELETE, $url );
		wp_safe_redirect( $url );
		die;
	}

	/**
	 * Save backup activation
	 *
	 * @param Snapshot_Model_Post $data Request data
	 *
	 * @return bool
	 */
	private function _activate_backups( Snapshot_Model_Post $data ) {
		if (
			! current_user_can( $this->_view->get_page_role() )
			||
			! $data->is_valid_action( 'snapshot-full_backups-activate' )
		) {
			return false;
		}
		// We have checked for nonces by using the is_valid_action function above.
		// phpcs:ignore
		if ( $this->_model->is_active() && ! ( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), array( "snapshot_pro_settings", "snapshot_pro_managed_backups" ), true ) ) ) {
			if ( $data->is_true( 'activate' ) ) {
				return false;
			} // Pleonasm
			$this->_model->set_config( 'active', false );
		} else {

			if ( ! $data->is_true( 'activate' ) ) {
				return false;
			} // Pleonasm
			if ( $data->has( 'secret-key' ) ) {

				$key = sanitize_text_field( $data->value( 'secret-key' ) );
				if ( empty( $key ) ) {
					return false;
				}

				$old_key = $this->_model->get_config( 'secret-key', false );
				$this->_model->set_config( 'secret-key', $key );
				if ( empty( $key ) || $key !== $old_key ) {
					$this->_model->remote()->remove_token();
				}

				// Require secret key to activate the backups
				$this->_model->set_config( 'active', true );

				// Set initial cron hooks if at all possible
				Snapshot_Controller_Full_Cron::get()->reschedule();

				// Send initial schedule update
				$this->_model->update_remote_schedule();
			}

			// We can't attempt to update remote schedule
			// when we have no secret key at activation time.
			// We can't handle the scenario descrobed here:
			// https://app.asana.com/0/11140230629075/163832507640609
			// as it is happening outside our control
		}

		return true;
	}

	/**
	 * Completely deactivates full backups
	 *
	 * @param Snapshot_Model_Post $data Request data
	 *
	 * @return bool
	 */
	private function _deactivate_backups( Snapshot_Model_Post $data ) {
		if ( ! current_user_can( $this->_view->get_page_role() ) ||
			! $data->is_valid_action( 'snapshot-full_backups-schedule' ) ) {
			return false;
		}

		$this->_model->set_config( 'frequency', false );
		$this->_model->set_config( 'schedule_time', false );
		$this->_model->set_config( 'disable_cron', true );
		Snapshot_Controller_Full_Cron::get()->stop();

		// Let the service know
		$this->_model->update_remote_schedule();

		return false;
	}

	private function _deactivate_all( Snapshot_Model_Post $data ) {
		if (
			! current_user_can( $this->_view->get_page_role() )
			||
			! $data->is_valid_action( 'snapshot-full_backups-schedule' )
		) {
			return false;
		}

		$this->_model->set_config( 'secret-key', '' );
		$this->_model->remote()->remove_token();

		$this->_model->set_config( 'frequency', false );
		$this->_model->set_config( 'schedule_time', false );
		$this->_model->set_config( 'disable_cron', true );
		Snapshot_Controller_Full_Cron::get()->stop();

		// Let the service know
		$this->_model->update_remote_schedule();

		return false;
	}

	/**
	 * Re-enables cron backups
	 *
	 * @param Snapshot_Model_Post $data Request data
	 *
	 * @return bool
	 */
	private function _reenable_cron_backups( Snapshot_Model_Post $data ) {
		if ( ! current_user_can( $this->_view->get_page_role() ) ||
		     ! $data->is_valid_action( 'snapshot-full_backups-schedule' ) ) {
			return false;
		}

		$this->_model->set_config( 'disable_cron', false );

		// Reset cron hooks
		Snapshot_Controller_Full_Cron::get()->reschedule();

		// Let the service know
		$this->_model->update_remote_schedule();

		return true;
	}

	/**
	 * Save backup overall settings
	 *
	 * @param Snapshot_Model_Post $data Request data
	 *
	 * @return bool
	 */
	private function _update_settings( Snapshot_Model_Post $data ) {
		if ( ! current_user_can( $this->_view->get_page_role() ) ||
		     ! $data->is_valid_action( 'snapshot-full_backups-settings' ) && ! $data->is_valid_action( 'snapshot-full_backups-schedule' ) ) {
			return false;
		}

		// Do the secret key part first
		if ( $data->has( 'secret-key' ) ) {
			$key = sanitize_text_field( $data->value( 'secret-key' ) );
			$old_key = $this->_model->get_config( 'secret-key', false );
			$this->_model->set_config( 'secret-key', $key );
			if ( empty( $key ) || $key !== $old_key ) {
				$this->_model->remote()->remove_token();
			}

			// Also stop cron when there's no secret key
			if ( empty( $key ) ) {
				$this->_model->set_config( 'frequency', false );
				$this->_model->set_config( 'schedule_time', false );
				$this->_model->set_config( 'disable_cron', true );
				Snapshot_Controller_Full_Cron::get()->stop();
			}
		}

		// Do the limit part
		if ( $data->has( 'backups-limit' ) ) {
			Snapshot_Model_Full_Remote_Storage::get()->set_max_backups_limit( $data->value( 'backups-limit' ) );
			// ... *then* update remote info
			$this->_model->update_remote_schedule();
		}

		// Do the logging part
		if ( $data->has( 'log-enable' ) ) {
			Snapshot_Controller_Full_Log::get()->process_submissions( $data );
		}
	}

	/**
	 * Save backup frequency settings
	 *
	 * @param Snapshot_Model_Post $data Request data
	 *
	 * @return bool
	 */
	private function _schedule_backups( Snapshot_Model_Post $data ) {
		if ( ! current_user_can( $this->_view->get_page_role() ) ||
			! $data->is_valid_action( 'snapshot-full_backups-schedule' ) ) {
			return false;
		}

		// Check validity
		if ( ! $data->has( 'frequency' ) || ! $data->has( 'schedule_time' ) ) {
			return false;
		}

		if ( ! $data->is_in_range( 'frequency', array_keys( $this->_model->get_frequencies() ) ) ) {
			return false;
		}
		if ( ! $data->is_in_numeric_range( 'schedule_time', array_keys( $this->_model->get_schedule_times() ) ) ) {
			return false;
		}

		$this->_model->set_config( 'frequency', $data->value( 'frequency' ) );
		$this->_model->set_config( 'schedule_time', $data->value( 'schedule_time' ) );

		$offset = 0;
		if ($data->has('offset') && $data->is_numeric('offset')) {
			$valid_freqs = array('weekly', 'monthly');
			if ($data->is_in_range('frequency', $valid_freqs))
				$offset = (int)$data->value('offset', 0);
		}
		$this->_model->set_config('schedule_offset', $offset);

		if ( $data->has( 'backups-limit' ) ) {
			$this->_update_settings( $data );
		}

		if ( ! $data->has( 'snapshot-disable-cron' ) ) {
			$this->_reenable_cron_backups( $data );
		}

		$this->_model->update_remote_schedule();

		return true;
	}
}