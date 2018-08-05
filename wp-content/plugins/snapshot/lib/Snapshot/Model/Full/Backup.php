<?php // phpcs:ignore

/**
 * Overall full backup model
 */
class Snapshot_Model_Full_Backup extends Snapshot_Model_Full_Abstract {

	/**
	 * Remote model instance reference
	 *
	 * @var object Snapshot_Model_Full_Remote
	 */
	private $_storage;

	/**
	 * Local model instance reference
	 *
	 * @var object Snapshot_Model_Full_Local
	 */
	private $_local;

	/**
	 * Create a new model instance
	 *
	 * Also populates internal facade references
	 */
	public function __construct() {
		$this->_storage = new Snapshot_Model_Full_Remote();
		$this->_local = new Snapshot_Model_Full_Local();
	}

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type() {
		return 'backup';
	}

	/**
	 * Gets local handler instance
	 *
	 * @return Snapshot_Model_Full_Local Local handler instance
	 */
	public function local() {
		return $this->_local;
	}

	/**
	 * Gets remote handler instance
	 *
	 * @return Snapshot_Model_Full_Remote Remote handler instance
	 */
	public function remote() {
		return $this->_storage;
	}

	/**
	 * Check for existence of any errors
	 *
	 * @return bool
	 */
	public function has_errors() {
		if ( $this->_storage->has_errors() ) {
			return true;
		}
		if ( $this->_local->has_errors() ) {
			return true;
		}
		return empty( $this->_errors );
	}

	/**
	 * Get errors as array of strings ready for showing.
	 *
	 * @return array
	 */
	public function get_errors() {
		$from_storage = $this->_storage->get_errors();
		$from_local = $this->_storage->get_errors();
		$errors = is_array( $this->_errors )
			? $this->_errors
			: array();
		return array_merge( $from_storage, $from_local, $errors );
	}

	/**
	 * Proxy remote API info check
	 *
	 * @return bool
	 */
	public function has_api_info() {
		return $this->_storage->has_api_info();
	}

	/**
	 * Proxy remote API error check
	 *
	 * @return bool
	 */
	public function has_api_error() {
		return $this->_storage->has_api_error();
	}

	/**
	 * Proxy the current site DEV management link
	 *
	 * @return string
	 */
	public function get_current_site_management_link() {
		return $this->_storage->get_current_site_management_link();
	}

	/**
	 * Proxy the current site DEV secret key link
	 *
	 * @return string
	 */
	public function get_current_secret_key_link() {
		return $this->_storage->get_current_secret_key_link();
	}

	/**
	 * Updates the schedule frequencies on remote DEV side to their current local values
	 *
	 * Proxies the Remote schedule update method
	 *
	 * @param int $timestamp Optional last backup timestamp (to be passed on verbatim)
	 *
	 * @return bool
	 */
	public function update_remote_schedule( $timestamp = false ) {
		$frequency = $this->get_frequency();
		$time = $this->get_schedule_time();

		return $this->_storage->update_schedule( $frequency, $time, $timestamp );
	}


	/**
	 * Check if we have dashboard installed
	 *
	 * @return bool
	 */
	public function has_dashboard() {
		return (bool) apply_filters(
			$this->get_filter( 'has_dashboard' ),
			$this->is_dashboard_active() && $this->has_dashboard_key()
		);
	}

	/**
	 * Check if we have WPMU DEV Dashboard plugin installed and activated
	 *
	 * @return bool
	 */
	public function is_dashboard_active() {
		return (bool) apply_filters(
			$this->get_filter( 'is_dashboard_active' ),
			class_exists( 'WPMUDEV_Dashboard' )
		);
	}

	/**
	 * Checks if Dashboard plugin is installed, but not activated
	 *
	 * @return bool
	 */
	public function is_dashboard_installed() {
		if ( $this->is_dashboard_active() ) {
			return true;
		}
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		if ( ! is_array( $plugins ) || empty( $plugins ) ) {
			return false;
		}

		return ! empty( $plugins['wpmudev-updates/update-notifications.php'] );
	}

	/**
	 * Check if we have our API key
	 *
	 * If we do, this means the user has logged into the dashboard
	 *
	 * @return bool
	 */
	public function has_dashboard_key() {
		$key = $this->get_dashboard_api_key();
		return (bool) apply_filters(
			$this->get_filter( 'has_dashboard_key' ),
			! empty( $key )
		);
	}

	/**
	 * Remote facade for dashboard key getting.
	 *
	 * @return string API key
	 */
	public function get_dashboard_api_key() {
		return $this->_storage->get_dashboard_api_key();
	}

	/**
	 * Check if full backups are activated
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->has_dashboard() && apply_filters(
				$this->get_filter( 'is_active' ),
				$this->get_config( 'active' )
			);
	}

	/**
	 * Gets a list of known scheduled frequencies
	 *
	 * @param bool $title_case if true, will return capitalized frequency names, otherwise, lowercase
	 *
	 * @return array List of frequencies as key => label pairs
	 */
	public function get_frequencies( $title_case = true ) {

		if ( $title_case ) {
			$frequencies = array(
				'daily' => __( 'Daily', SNAPSHOT_I18N_DOMAIN ),
				'weekly' => __( 'Weekly', SNAPSHOT_I18N_DOMAIN ),
				'monthly' => __( 'Monthly', SNAPSHOT_I18N_DOMAIN ),
			);
		} else {
			$frequencies = array(
				'daily' => __( 'daily', SNAPSHOT_I18N_DOMAIN ),
				'weekly' => __( 'weekly', SNAPSHOT_I18N_DOMAIN ),
				'monthly' => __( 'monthly', SNAPSHOT_I18N_DOMAIN ),
			);
		}

		return apply_filters( $this->get_filter( 'schedule_frequencies' ), $frequencies, $title_case );
	}

	/**
	 * Gets the currently set schedule frequency
	 *
	 * @return string Schedule frequency key
	 */
	public function get_frequency() {
		$default = 'weekly';
		$value = $this->get_config( 'frequency', $default );
		$value = ! empty( $value ) ? $value : $default;
		return apply_filters(
			$this->get_filter( 'schedule_frequency' ),
			$value
		);
	}

	/**
	 * Gets a list of known schedule times
	 *
	 * @return array A list of schedule times, as key => label pairs
	 */
	public function get_schedule_times() {
		$times = array();
		$midnight = strtotime( date( "Y-m-d 00:00:00" ) );
		$tf = get_option( 'time_format' );
		$offset = Snapshot_Model_Time::get()->get_utc_diff();
		for ( $i = 0; $i < DAY_IN_SECONDS; $i += HOUR_IN_SECONDS ) {
			$seconds = $i - $offset; // Deal with seconds, not hours
			if ( $seconds < 0 ) {
				$seconds += DAY_IN_SECONDS;
			}
			if ( $seconds >= DAY_IN_SECONDS ) {
				$seconds -= DAY_IN_SECONDS;
			}
			if ( 0 === intval( $seconds ) ) {
				$seconds = 1;
			} // Because 0 will show current time in Hub :(
			$times[ $seconds ] = date_i18n( $tf, $midnight + $i );
		}
		return apply_filters(
			$this->get_filter( 'schedule_times' ),
			$times
		);
	}

	/**
	 * Gets the currently set schedule time
	 *
	 * @return int Relative schedule time
	 */
	public function get_schedule_time() {
		$default = Snapshot_Model_Time::get()->convert_to_local_timestamp( 3600 );
		$value   = $this->get_config( 'schedule_time', $default );
		$value   = is_numeric( $value ) ? (int) $value : $default;

		return (int) apply_filters(
			$this->get_filter( 'schedule_time' ),
			$value
		);
	}

	/**
	 * Gets offset base value
	 *
	 * @param string $frequency Optional frequency for the offset base.
	 *
	 * @return int
	 */
	public function get_offset_base ($frequency= false) {
		$offset = $this->get_config('schedule_offset', 0);
		if (empty($frequency))
			$frequency = $this->get_frequency();
		if ('weekly' === $frequency && $offset > 6)
			return 0;

		return (int)$offset;
	}

	/**
	 * Gets concrete offset, relative to timestamp
	 *
	 * @param int $timestamp Date to calculate offset relative to.
	 * @param string $frequency Optional frequency for the offset base.
	 *
	 * @return int
	 */
	public function get_offset ($timestamp, $frequency= false) {
		if (empty($frequency))
			$frequency = $this->get_frequency();
		$base = $this->get_offset_base($frequency);
		$offset = $timestamp;

		if ('weekly' === $frequency) {
			$monday = Snapshot_Model_Time::get()->get_next_monday();
			$next = $base * DAY_IN_SECONDS;

			if ($timestamp > $monday + $next) {
				// Ensure we're in the future.
				$monday += 7 * DAY_IN_SECONDS;
			}
			if ($monday + $next > $timestamp + (7 * DAY_IN_SECONDS)) {
				// Ensure we're not too far in the future.
				$monday -= 7 * DAY_IN_SECONDS;
			}

			$offset = $monday + $next;
		} else if ('monthly' === $frequency) {
			$offset = strtotime(date('Y-m-01 00:00:00', $timestamp));
			$next = $base > 1 ? ($base - 1) * DAY_IN_SECONDS : 0;
			// Ensure we're in the future
			if ($timestamp > $offset + $next) $offset += (int)date('t', $offset) * DAY_IN_SECONDS;
			$offset += $next;
		}

		return $offset;
	}

	/**
	 * Gets a list of offsets as offset base, offset weekday pairs
	 *
	 * @return array
	 */
	public function get_offsets ($frequency= false) {
		if (empty($frequency))
			$frequency = $this->get_frequency();
		$offsets = array();

		if ('weekly' === $frequency) {
			$monday = Snapshot_Model_Time::get()->get_next_monday();
			foreach (range(0, 6) as $wday) {
				$offsets[$wday] = date_i18n( 'l', $monday + ($wday*DAY_IN_SECONDS));
			}
		} else if ('monthly' === $frequency) {
			foreach (range(1, 30) as $mday) {
				$offsets[$mday] = $mday;
			}
		}

		return $offsets;
	}

	/**
	 * Check if we have any backups here
	 *
	 * @return bool
	 */
	public function has_backups() {
		$backups = $this->get_backups();
		return apply_filters(
			$this->get_filter( 'has_backups' ),
			! empty( $backups )
		);
	}

	/**
	 * Gets a list of backups
	 *
	 * @return array A list of full backup items
	 */
	public function get_backups() {
		return apply_filters(
			$this->get_filter( 'get_backups' ),
			array_merge(
				$this->_storage->get_backups(),
				$this->_local->get_backups()
			)
		);
	}

	/**
	 * Send finished backup file to remote destination.
	 *
	 * Facade method for storage action.
	 *
	 * @param Snapshot_Helper_Backup $backup Backup helper to send away
	 *
	 * @return bool
	 */
	public function send_backup( Snapshot_Helper_Backup $backup ) {
		return $this->_storage->send_backup( $backup );
	}

	/**
	 * Continue item upload for this backup
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function continue_item_upload( $timestamp ) {
		return $this->_storage->continue_item_upload( $timestamp );
	}

	/**
	 * Gets a (local) backup file instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return mixed Path to backup if local file exists, (bool)false otherwise
	 */
	public function get_backup( $timestamp ) {
		$local = $this->_local->get_backup( $timestamp );
		if ( ! empty( $local ) ) {
			return $local;
		}

		return $this->_storage->get_backup( $timestamp );
	}

	/**
	 * Deletes a remote backup instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function delete_backup( $timestamp ) {
		$local_result = $this->_local->delete_backup( $timestamp );
		$remote_result = $this->_storage->delete_backup( $timestamp );
		return $local_result || $remote_result;
	}

	/**
	 * Proxies local backups rotation
	 *
	 * @return bool
	 */
	public function rotate_local_backups() {
		return $this->_local->rotate_backups();
	}

	/**
	 * Gets the next scheduled automatic backup start
	 *
	 * @return mixed (int)UNIX timestamp on success, (bool)false on failure
	 */
	public function get_next_automatic_backup_start_time() {
		$cron = Snapshot_Controller_Full_Cron::get();
		$schedule = wp_next_scheduled( $cron->get_filter( 'start_backup' ) );

		return ! empty( $schedule )
			? $schedule
			: false;
	}

	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter( $filter = false ) {
		if ( empty( $filter ) ) {
			return false;
		}
		if ( ! is_string( $filter ) ) {
			return false;
		}
		return 'snapshot-model-full-backup-' . $filter;
	}

}