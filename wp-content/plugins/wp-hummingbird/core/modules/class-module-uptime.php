<?php


class WP_Hummingbird_Module_Uptime extends WP_Hummingbird_Module {

	public function init() {}
	public function run() {}

	public static function get_last_report( $time, $force = false ) {

		if ( ! wphb_is_member() ) {
			return new WP_Error( 'uptime-membership', __( 'You need to be a WPMU DEV Member', 'wphb' ) );	   			 	 		  		   		
		}

		$current_reports = get_site_transient( 'wphb-uptime-last-report' );
		if ( ! isset( $current_reports[ $time ] ) || $force ) {
			self::refresh_report( $time );
			$current_reports = get_site_transient( 'wphb-uptime-last-report' );
		}

		if ( ! isset( $current_reports[ $time ] ) ) {
			return false;
		}

		return $current_reports[ $time ];
	}

	/**
	 * Get latest report from server
	 */
	public static function refresh_report( $time = 'day' ) {
		/* @var WP_Hummingbird_API $api */
		$api = wphb_get_api();
		$results = $api->uptime->check( $time );

		if ( is_wp_error( $results ) && 412 === $results->get_error_code() ) {
			// Uptime has been deactivated.
			self::disable_locally();
			delete_site_transient( 'wphb-uptime-last-report' );
			return;
		}

		$current_reports = get_site_transient( 'wphb-uptime-last-report' );
		if ( ! $current_reports ) {
			$current_reports = array();
		}

		$current_reports[ $time ] = $results;
		// Save for 2 minutes.
		set_site_transient( 'wphb-uptime-last-report', $current_reports, 120 );
	}

	/**
	 * Check if Uptime is remotely enabled
	 *
	 * @return bool
	 */
	public static function is_remotely_enabled() {
		if ( ! wphb_is_member() ) {
			return false;
		}

		$cached = get_site_transient( 'wphb-uptime-remotely-enabled' );
		if ( 'yes' === $cached ) {
			return true;
		} elseif ( 'no' === $cached ) {
			return false;
		}

		$api = wphb_get_api();
		$result = $api->uptime->is_enabled();
		set_site_transient( 'wphb-uptime-remotely-enabled', $result ? 'yes' : 'no', 300 ); // save for 5 minutes
		return $result;
	}


	/**
	 * Enable Uptime local and remotely
	 */
	public static function enable() {
		self::clear_cache();
		self::enable_locally();
		return self::enable_remotely();
	}

	/**
	 * Disable Uptime local and remotely
	 */
	public static function disable() {
		self::clear_cache();
		self::disable_locally();
		self::disable_remotely();
	}

	public static function enable_locally() {
		$options = wphb_get_settings();
		$options['uptime'] = true;
		wphb_update_settings( $options );
		set_site_transient( 'wphb-uptime-remotely-enabled', 'yes', 180 ); // save for 3 minutes
	}

	public static function enable_remotely() {
		/** @var WP_Hummingbird_API $api */
		$api = wphb_get_api();
		delete_site_transient( 'wphb-uptime-remotely-enabled' );
		return $api->uptime->enable();
	}

	public static function disable_locally() {
		$options = wphb_get_settings();
		$options['uptime'] = false;
		wphb_update_settings( $options );
		set_site_transient( 'wphb-uptime-remotely-enabled', 'no', 180 ); // save for 3 minutes
	}

	public static function disable_remotely() {
		/** @var WP_Hummingbird_API $api */
		$api = wphb_get_api();
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


	/**
	 * Clear Performance Module cache
	 */
	public static function clear_cache() {
		delete_site_transient( 'wphb-uptime-last-report' );
		delete_site_transient( 'wphb-uptime-last-error' );
	}
}
