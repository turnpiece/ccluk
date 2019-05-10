<?php
/**
 * Uptime module.
 *
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Module_Uptime
 */
class WP_Hummingbird_Module_Uptime extends WP_Hummingbird_Module {

	/**
	 * Initialize module.
	 */
	public function init() {}

	/**
	 * Execute module actions.
	 */
	public function run() {}

	/**
	 * Implement abstract parent method for clearing cache.
	 *
	 * @since 1.7.1
	 */
	public function clear_cache() {
		delete_site_transient( 'wphb-uptime-last-report' );
		delete_site_transient( 'wphb-uptime-last-error' );
	}

	/**
	 * Get last report.
	 *
	 * @since 1.7.1 Removed static property.
	 * @param string $time   Report period.
	 * @param bool   $force  Force refresh.
	 *
	 * @return bool|WP_Error
	 */
	public function get_last_report( $time = 'week', $force = false ) {
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			return new WP_Error( 'uptime-membership', __( 'You need to be a WPMU DEV Member', 'wphb' ) );
		}

		$current_reports = get_site_transient( 'wphb-uptime-last-report' );
		if ( ! isset( $current_reports[ $time ] ) || $force ) {
			$current_reports = $this->refresh_report( $time );
		}

		if ( ! isset( $current_reports[ $time ] ) ) {
			return false;
		}

		return $current_reports[ $time ];
	}

	/**
	 * Get latest report from server
	 *
	 * @since 1.7.1 Removed static property.
	 * @since 1.8.1 Access changed to private. Added $current_reports param.
	 *
	 * @access private
	 *
	 * @param string     $time             Report period.
	 * @param bool|array $current_reports  Current reports.
	 *
	 * @return array|mixed
	 */
	private function refresh_report( $time = 'day', $current_reports = false ) {
		$results = WP_Hummingbird_Utils::get_api()->uptime->check( $time );

		if ( is_wp_error( $results ) && 412 === $results->get_error_code() ) {
			// Uptime has been deactivated.
			$this->disable_locally();
			delete_site_transient( 'wphb-uptime-last-report' );
			return false;
		}

		if ( ! $current_reports ) {
			$current_reports = array();
		}

		$current_reports[ $time ] = $results;
		// Save for 2 minutes.
		set_site_transient( 'wphb-uptime-last-report', $current_reports, 120 );

		return $current_reports;
	}

	/**
	 * Check if Uptime is remotely enabled
	 *
	 * @return bool
	 */
	public static function is_remotely_enabled() {
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			return false;
		}

		$cached = get_site_transient( 'wphb-uptime-remotely-enabled' );
		if ( 'yes' === $cached ) {
			return true;
		} elseif ( 'no' === $cached ) {
			return false;
		}

		$api    = WP_Hummingbird_Utils::get_api();
		$result = $api->uptime->is_enabled();
		// Save for 5 minutes.
		set_site_transient( 'wphb-uptime-remotely-enabled', $result ? 'yes' : 'no', 300 );

		return $result;
	}

	/**
	 * Enable Uptime local and remotely
	 *
	 * @since 1.7.1 Remove static property
	 */
	public function enable() {
		$this->clear_cache();
		$this->enable_locally();
		return self::enable_remotely();
	}

	/**
	 * Disable Uptime local and remotely
	 *
	 * @since 1.7.1 Removed static property
	 */
	public function disable() {
		$this->clear_cache();
		$this->disable_locally();
		self::disable_remotely();
	}

	/**
	 * Enable locally.
	 */
	public function enable_locally() {
		$options            = $this->get_options();
		$options['enabled'] = true;
		$this->update_options( $options );
		// Save for 3 minutes.
		set_site_transient( 'wphb-uptime-remotely-enabled', 'yes', 180 );
	}

	/**
	 * Enable remotely.
	 *
	 * @return mixed|WP_Error
	 */
	public static function enable_remotely() {
		delete_site_transient( 'wphb-uptime-remotely-enabled' );
		return WP_Hummingbird_Utils::get_api()->uptime->enable();
	}

	/**
	 * Disable locally.
	 */
	public function disable_locally() {
		$options            = $this->get_options();
		$options['enabled'] = false;

		// Disable reports and notifications.
		$options['notifications']['enabled'] = false;
		$options['reports']['enabled']       = false;

		// Clean all cron.
		wp_clear_scheduled_hook( 'wphb_uptime_report' );

		$this->update_options( $options );
		// Save for 3 minutes.
		set_site_transient( 'wphb-uptime-remotely-enabled', 'no', 180 );
	}

	/**
	 * Disable remotely.
	 *
	 * @return mixed|WP_Error
	 */
	public static function disable_remotely() {
		delete_site_transient( 'wphb-uptime-remotely-enabled' );
		return WP_Hummingbird_Utils::get_api()->uptime->disable();
	}

	/**
	 * Set error.
	 *
	 * @param WP_Error $error  Error.
	 */
	public static function set_error( $error ) {
		set_site_transient( 'wphb-uptime-last-error', $error );
	}

	/**
	 * Get error.
	 *
	 * @return mixed
	 */
	public static function get_error() {
		return get_site_transient( 'wphb-uptime-last-error' );
	}

}
