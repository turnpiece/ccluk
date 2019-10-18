<?php // phpcs:ignore

/**
 * Authenticated AJAX action controller
 */
class Snapshot_Controller_Hosting_Ajax extends Snapshot_Controller_Hosting {

	const AJAX_NONCE = 'snapshot-new-hosting-backup-ajax-nonce';

	/**
	 * Internal instance reference
	 *
	 * @var object Snapshot_Controller_Hosting_Ajax instance
	 */
	private static $_instance;

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_Controller_Hosting_Ajax instance
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
		add_action( 'wp_ajax_snapshot-hosting_backup-create', array( $this, 'json_start_hosting_backup' ) );
		add_action( 'wp_ajax_snapshot-hosting_backup-restore', array( $this, 'json_restore_hosting_backup' ) );
		add_action( 'wp_ajax_snapshot-hosting_backup-list', array( $this, 'json_list_hosting_backups' ) );
		add_action( 'wp_ajax_snapshot-hosting_backup-dashboard-list', array( $this, 'json_list_hosting_backups_dashboard' ) );
		add_action( 'wp_ajax_snapshot-deal_with_current_backup', array( $this, 'json_get_current_state' ) );
		add_action( 'wp_ajax_snapshot-hosting_backup-export', array( $this, 'json_export_hosting_backup' ) );
		add_action( 'wp_ajax_snapshot-ongoing_backup_after_restore', array( $this, 'json_ongoing_backup_after_restore' ) );

		add_site_option( Snapshot_Controller_Hosting::OPTIONS_BACKUP_FLAG, '' );
	}

	/**
	 * Backup start JSON handler
	 */
	public function json_start_hosting_backup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$current = $this->get_currently_running_action( get_site_option( Snapshot_Controller_Hosting::OPTIONS_BACKUP_FLAG ) );

		$args = $this->deal_with_running_backup( $current, true);
		wp_send_json( $args );
	}

	/**
	 * Backup restore JSON handler
	 */
	public function json_restore_hosting_backup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$backup_id = false;

		if( isset( $_POST['backup_id'] ) && ! empty( $_POST['backup_id'] ) ) {
			$backup_id = sanitize_text_field( $_POST['backup_id'] );
		}

		$args = $this->deal_with_backup_restore( $backup_id );
		$args['backup_id'] = $backup_id;
		$args['api_key'] = $this->logged_in_wpmudev_user();
		$args['site_id'] = $this->get_site_id();
		wp_send_json( $args );
	}

	/**
	 * Add timestamp key to managed backups array
	 *
	 * @param array $backups
	 *
	 * @return array
	 */
	private function update_date_values( $backups = array() ) {
		foreach ( $backups as $key => $backup ) {
			$backups[ $key ]['creation_time'] = date_format( date_timestamp_set( new DateTime(), $backup['timestamp'] ), 'c' );
		}

		return $backups;
	}

	/**
	 * Sort array
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	private function order_backups( $array1, $array2 ) {
		$date1 = strtotime( $array1['creation_time'] );
		$date2 = strtotime( $array2['creation_time'] );

		return $date2 - $date1;
	}

	/**
	 * Return combined backups
	 */
	private function get_backups() {
		$raw_hosting_backups = $this->get_backups_list();
		if ( is_wp_error( $raw_hosting_backups ) ) {
			return $raw_hosting_backups;
		}
		$raw_managed_backups = $this->update_date_values( $this->_managed_model->get_backups() );
		$raw_backups = array_merge( $raw_hosting_backups, $raw_managed_backups );

		usort( $raw_backups, array( $this, 'order_backups' ) );

		return $raw_backups;
	}

	/**
	 * Backup list JSON handler
	 */
	public function json_list_hosting_backups() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$raw_backups = $this->get_backups();

		if ( is_wp_error( $raw_backups ) ) {
			wp_send_json_error( $raw_backups );
		}

		$current = $this->get_currently_running_action( get_site_option( Snapshot_Controller_Hosting::OPTIONS_BACKUP_FLAG ) );
		$args = $this->deal_with_running_backup( $current, false);

		$dashboard = false;

		$args['backups'] = $this->deal_with_listing_backups( $raw_backups, $dashboard );
		wp_send_json_success($args);
	}

	/**
	 * Backup list JSON handler
	 */
	public function json_list_hosting_backups_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$raw_backups = $this->get_backups();

		if ( is_wp_error( $raw_backups ) ) {
			wp_send_json_error( $raw_backups );
		}

		$dashboard = true;

		$backups = $this->deal_with_listing_backups( $raw_backups, $dashboard );

		wp_send_json_success( array(
			'backups' => $backups,
			)
		);
	}

	/**
	 * Current backup's state on page load
	 */
	public function json_get_current_state() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$current = $this->get_currently_running_action( get_site_option( Snapshot_Controller_Hosting::OPTIONS_BACKUP_FLAG ) );
		$args = $this->deal_with_running_backup( $current, false);

		wp_send_json( $args );
	}

	/**
	 * Backup export JSON handler
	 */
	public function json_export_hosting_backup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		if( isset( $_POST['backup_id'] ) && ! empty( $_POST['backup_id'] ) ) {
			$backup_id = sanitize_text_field( $_POST['backup_id'] );
		}

		$export_result = $this->_export_backup( $backup_id );

		if ( is_wp_error( $export_result ) ) {
			$error_message = wp_kses_post( sprintf( __( 'We couldn\'t send the backup to your email address. Please <a class="%1$s" href="#" data-backup-id="%2$s">try again</a>, and if the issue persist, you can <a href="%3$s" target="_blank">contact our support</a> for help.', SNAPSHOT_I18N_DOMAIN ), 'snapshot-hosting-backup-export', $backup_id, 'https://premium.wpmudev.org/hub/support/#get-support' ) );
			wp_send_json_error( $error_message );
		}

		$success_message = wp_kses_post( sprintf( __( 'We\'re preparing your backup for <strong>%s.wpmudev.host</strong>, we\'ll email you when it\'s finished and ready.', SNAPSHOT_I18N_DOMAIN ), $this->get_site_id() ) );
		wp_send_json_success( $success_message );
	}

	/**
	 * JSON handler for deleting any db entries saying that a backup is underway
	 * Used only at the end of successsful restores.
	 */
	public function json_ongoing_backup_after_restore() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

		$result = update_site_option( Snapshot_Controller_Hosting::OPTIONS_BACKUP_FLAG, false );
		wp_send_json_success( $result );
	}
}