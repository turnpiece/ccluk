<?php // phpcs:ignore

class Snapshot_Local_Backups {
	public static $instance = null;

	const OPTION_SHOW_NOTICE = 'snapshot-local-backups-notification-pending';
	const OPTION_NOTICE_DISMISSED = 'snapshot-local-backups-notice-dismissed';
	const MANAGED_BACKUPS_QUERY_VAR = 'snapshot_pro_managed_backups';
	const HOURLY_SCHEDULED_EVENT = 'snapshot_local_backup_check';

	private function __construct() {
	}

	public static function serve() {
		self::get()->add_hooks();
	}

	public static function stop() {
		self::get()->remove_hooks();
	}

	/**
	 * @return null|Snapshot_Local_Backups
	 */
	public static function get() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function add_hooks() {
		add_action( self::HOURLY_SCHEDULED_EVENT, array( $this, 'check_for_local_backups' ) );
		add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array(
			$this,
			'display_local_backups_warning',
		) );
		add_action( 'wp_ajax_dismiss_local_backups_notice', array(
			$this,
			'snapshot_ajax_dismiss_local_backups_notice',
		) );
		add_action( 'admin_head', array( $this, 'snapshot_backups_notice_style' ) );
		add_action( 'admin_footer', array( $this, 'print_dismiss_notice_script' ) );

		$this->schedule_event();
	}

	public function remove_hooks() {
		remove_action( self::HOURLY_SCHEDULED_EVENT, array( $this, 'check_for_local_backups' ) );
		remove_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array(
			$this,
			'display_local_backups_warning',
		) );
		remove_action( 'wp_ajax_dismiss_local_backups_notice', array(
			$this,
			'snapshot_ajax_dismiss_local_backups_notice',
		) );
		remove_action( 'admin_footer', array( $this, 'print_dismiss_notice_script' ) );

		$this->unschedule_event();
	}

	public function check_for_local_backups() {
		// Get local backups older than 12 hours
		$timestamps = $this->get_local_backup_timestamps();
		if ( empty( $timestamps ) ) {
			return;
		}

		$this->set_local_backup_notice_flag( $timestamps );

		// @TODO Replace the following line with a line firing up the sending of appropriate info to the Hub.
		// $this->send_local_backup_email( $timestamps );
	}

	private function set_local_backup_notice_flag( $timestamps ) {
		$dismissed_timestamps = get_option( self::OPTION_NOTICE_DISMISSED, array() );
		$unhandled_timestamps = array_diff( $timestamps, $dismissed_timestamps );
		if ( empty( $unhandled_timestamps ) ) {
			return;
		}

		update_option( self::OPTION_SHOW_NOTICE, true );
	}

	public function display_local_backups_warning() {
		// phpcs:ignore
		if ( Snapshot_Helper_Utility::is_wpmu_hosting() || ( isset( $_GET['page'] ) && self::MANAGED_BACKUPS_QUERY_VAR === $_GET['page'] ) ) {
			return;
		}

		if ( ! $this->show_notice() ) {
			return;
		}

		$user_info = $this->get_user_data();
		$message_text = sprintf(
			__( "Hi %s, you have <strong>one or more full site backups that have failed to upload to The Hub</strong>. Locally hosted backups aren't very handy in the event you need to restore a website that's been taken down. <strong>We recommend visiting your managed backups and using the \"Retry Uploading\" option for any that are locally stored</strong>.", SNAPSHOT_I18N_DOMAIN ), $user_info
		);
		?>
		<div class="snapshot-notice-local-backups notice-error notice is-dismissible">
			<p><?php echo wp_kses_post( $message_text ); ?></p>
			<a class="button button-primary" href="<?php echo esc_url( $this->get_managed_backups_url() ); ?>"><?php esc_html_e('Manage Backups', SNAPSHOT_I18N_DOMAIN); ?></a>
		</div>
		<?php
	}

	public function snapshot_ajax_dismiss_local_backups_notice() {
		$this->dismiss_local_backups_notice();
		die;
	}

	public function dismiss_local_backups_notice() {
		// Hide the message right away
		delete_option( self::OPTION_SHOW_NOTICE );

		// Note the timestamps
		$timestamps = $this->get_local_backup_timestamps();
		update_option( self::OPTION_NOTICE_DISMISSED, $timestamps );
	}

	private function schedule_event() {
		if ( ! wp_next_scheduled( self::HOURLY_SCHEDULED_EVENT ) ) {
			wp_schedule_event( time(), 'hourly', self::HOURLY_SCHEDULED_EVENT );
		}
	}

	private function unschedule_event() {
		wp_clear_scheduled_hook( self::HOURLY_SCHEDULED_EVENT );
	}

	public function print_dismiss_notice_script() {
		if ( ! $this->show_notice() ) {
			return;
		}

		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$(document).on('click', '.snapshot-notice-local-backups .notice-dismiss', function () {
					jQuery.ajax({
						url: ajaxurl,
						data: {
							action: 'dismiss_local_backups_notice'
						}
					})
				});
			});
		</script>
		<?php
	}

	public function snapshot_backups_notice_style() {
		echo
'<style type="text/css">
	.toplevel_page_snapshot_pro_dashboard .snapshot-notice-local-backups, .snapshot_page_snapshot_pro_snapshots .snapshot-notice-local-backups, .snapshot_page_snapshot_pro_destinations .snapshot-notice-local-backups,
	.snapshot_page_snapshot_pro_import .snapshot-notice-local-backups, .snapshot_page_snapshot_pro_settings .snapshot-notice-local-backups {
		max-width: 980px;
		margin: 15px 10px 2px;
	}
	.snapshot-notice-local-backups a.button-primary{
		margin: 7px 0 12px;
		background: #0085ba;
		border-color: #0073aa #006799 #006799;
		box-shadow: 0 1px 0 #006799;
		color: #fff;
		text-decoration: none;
		text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
		font: inherit;
		font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
		font-size: 13px!important;
		text-transform: none;
		line-height: 26px;
		height: 28px;
		padding: 0 10px 1px;
		cursor: pointer;
		border-width: 1px;
		border-style: solid;
		border-radius: 3px;
		box-sizing: border-box;
	}
	.snapshot-notice-local-backups a.button-primary:hover{
		box-shadow: 0 1px 0 #006799!important;
		background: #008ec2;
		background-color: #008ec2!important;
    	border-color: #006799;
	}
	.snapshot-notice-local-backups .notice-dismiss{
		border: none!important;
		margin: 0!important;
		padding: 9px!important;
		background: 0 0!important;
		color: #72777c!important;
	}
	.snapshot-notice-local-backups .notice-dismiss:before{
		font: 400 20px/24px dashicons;
	}
	.snapshot-notice-local-backups p{
		margin: .5em 0!important;
		padding: 2px!important;
		font-size: 13px!important;
		line-height: 1.5!important;
		color: #4e4e4d!important;
	}
</style>
';
	}

	private function get_local_backup_timestamps() {
		$local_model = new Snapshot_Model_Full_Local();
		$backups = $local_model->get_backups();
		$timestamps = array();
		if ( empty( $backups ) ) {
			return $timestamps;
		}

		foreach ( $backups as $backup ) {
			if ( ! isset( $backup['timestamp'] ) ) {
				continue;
			}

			$timestamps[] = intval( $backup['timestamp'] );
		}

		// Sort newest to oldest
		rsort( $timestamps );
		// Only care about the ones older than 12 hours
		return array_filter( $timestamps, array( $this, 'is_timestamp_12_hours_old' ) );
	}

	private function is_timestamp_12_hours_old( $timestamp ) {
		$time_difference = time() - $timestamp;
		$stale_interval = $this->get_stale_interval();
		return $time_difference / 3600 >= $stale_interval;
	}

	private function get_stale_interval() {
		return defined( 'SNAPSHOT_STALE_BACKUP_INTERVAL' )
			? intval( SNAPSHOT_STALE_BACKUP_INTERVAL )
			: 12;
	}

	private function get_user_data() {
		$current_user = wp_get_current_user();

		if ( ! $current_user->exists() ) {
			return;
		}

		return ( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->display_name;

	}

	private function get_managed_backups_url() {
		return esc_url_raw( add_query_arg( 'page', self::MANAGED_BACKUPS_QUERY_VAR, network_admin_url( 'admin.php' ) ) );
	}

	private function show_notice() {
		$timestamps = $this->get_local_backup_timestamps();
		return get_option( self::OPTION_SHOW_NOTICE, false )
				&& ! empty( $timestamps )
				&& current_user_can( 'manage_options' );
	}
}