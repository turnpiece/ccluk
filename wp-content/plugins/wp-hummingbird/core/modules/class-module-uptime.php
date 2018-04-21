<?php


class WP_Hummingbird_Module_Uptime extends WP_Hummingbird_Module {

	public function init() {}
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
	 * @param $time
	 * @param bool $force
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
	 * @param string     $time
	 * @param bool|array $current_reports
	 *
	 * @return array|mixed
	 */
	private function refresh_report( $time = 'day', $current_reports = false ) {
		/* @var WP_Hummingbird_API $api */
		$api = WP_Hummingbird_Utils::get_api();
		$results = $api->uptime->check( $time );

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

		$api = WP_Hummingbird_Utils::get_api();
		$result = $api->uptime->is_enabled();
		set_site_transient( 'wphb-uptime-remotely-enabled', $result ? 'yes' : 'no', 300 ); // save for 5 minutes
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

	public function enable_locally() {
		$options = $this->get_options();
		$options['enabled'] = true;
		$this->update_options( $options );
		set_site_transient( 'wphb-uptime-remotely-enabled', 'yes', 180 ); // save for 3 minutes
	}

	public static function enable_remotely() {
		/* @var WP_Hummingbird_API $api */
		$api = WP_Hummingbird_Utils::get_api();
		delete_site_transient( 'wphb-uptime-remotely-enabled' );
		return $api->uptime->enable();
	}

	public function disable_locally() {
		$options = $this->get_options();
		$options['enabled'] = false;
		$this->update_options( $options );
		set_site_transient( 'wphb-uptime-remotely-enabled', 'no', 180 ); // save for 3 minutes
	}

	public static function disable_remotely() {
		/* @var WP_Hummingbird_API $api */
		$api = WP_Hummingbird_Utils::get_api();
		delete_site_transient( 'wphb-uptime-remotely-enabled' );
		return $api->uptime->disable();
	}

	/**
	 * @param WP_Error $error
	 */
	public static function set_error( $error ) {
		set_site_transient( 'wphb-uptime-last-error', $error );
	}

	public static function get_error() {
		return get_site_transient( 'wphb-uptime-last-error' );
	}

}