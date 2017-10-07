<?php

/**
 * Class WP_Hummingbird_Module_Cron is used for cron functionality.
 * Only for premium members.
 *
 * @since 1.5.0
 */
class WP_Hummingbird_Module_Reporting_Cron extends WP_Hummingbird_Module {

	/**
	 * Initialize the module
	 *
	 * @since 1.5.0
	 */
	public function init() {
		// Process scan cron.
		add_action( 'wphb_performance_scan', array( $this, 'process_scan_cron' ) );

		// Default settings.
		add_filter( 'wp_hummingbird_default_options', array( $this, 'add_default_options' ) );

		add_action( 'wphb_init_performance_scan', array( $this, 'on_init_performance_scan' ) );

		add_action( 'wphb_activate', array( $this, 'on_activate' ) );
	}

	/**
	 * Execute the module actions.
	 */
	public function run() {}

	/**
	 * Triggered during plugin activation
	 */
	public function on_activate() {

		if ( ! wphb_is_member() ) {
			return;
		}

		// Try to schedule next scan.
		if ( wphb_get_setting( 'email-notifications' ) ) {
			wp_schedule_single_event( WP_Hummingbird_Module_Reporting_Cron::get_scheduled_scan_time(), 'wphb_performance_scan' );
		}

	}

	/**
	 * Triggered when a performance scan is initialized
	 */
	public function on_init_performance_scan() {

		if ( wphb_is_member() ) {
			// Schedule first scan.
			wp_schedule_single_event( WP_Hummingbird_Module_Reporting_Cron::get_scheduled_scan_time(), 'wphb_performance_scan' );
		} else {
			wphb_update_setting( 'email-notifications', false );
		}

	}

	/**
	 * Add a set of default options to Hummingbird settings
	 *
	 * @param  array $settings  List of default Hummingbird settings.
	 * @return array
	 * @since  1.5.0
	 */
	public function add_default_options( $settings ) {
		$week_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);

		$hour = mt_rand( 0, 23 );

		$settings['email-notifications'] = false;
		$settings['email-recipients'] = array();
		$settings['email-frequency'] = 7;
		$settings['email-day'] = $week_days[ array_rand( $week_days, 1 ) ];
		$settings['email-time'] = $hour . ':00';

		return $settings;

	}

	/**
	 * Ajax action for processing a scan on page.
	 *
	 * TODO: this code needs to be refactored.
	 *
	 * @since 1.4.5
	 */
	public function process_scan_cron() {

		if ( ! wphb_is_member() ) {
			return;
		}

		// Clean all cron.
		wp_clear_scheduled_hook( 'wphb_performance_scan' );

		$limit = absint( get_site_option( 'wphb_cron_limit' ) );

		// Refresh the report and get the data.
		wphb_performance_refresh_report();
		$last_report = wphb_performance_get_last_report();

		// Time since last report.
		$time_difference = time() - (int) $last_report->data->time;

		// If no report is present or report is outdated, get new data.
		if ( ( ! $last_report || $time_difference > 300 ) && $limit < 3 ) {
			// First run. Init new report scan.
			if ( 0 === $limit ) {
				wphb_performance_init_scan();
			}

			// Update cron limit.
			update_site_option( 'wphb_cron_limit', ++$limit );
			// Reschedule in 1 minute to collect results.
			wp_schedule_single_event( strtotime( '+1 minutes' ), 'wphb_performance_scan' );
		} else {
			// Failed to fetch results in 3 attempts or less, cancel the cron.
			if ( 3 === $limit ) {
				delete_site_option( 'wphb_cron_limit' );
			}

			// Check to see it the email has been sent already.
			$last_sent_report = wphb_get_setting( 'wphb-last-sent-report' );
			$to_utc = self::get_scheduled_scan_time( false );

			// Schedule next test.
			if ( $time_difference < 300 && $last_report && ( $to_utc - time() - $last_sent_report ) > 0 ) {
				// Get the recipient list.
				$recipients = wphb_get_setting( 'email-recipients' );
				// Send the report.
				WP_Hummingbird_Module_Reporting::send_email_report( $last_report->data, $recipients );
				// Store the last send time.
				wphb_update_setting( 'wphb-last-sent-report', time() );
				delete_site_option( 'wphb_cron_limit' );
			}

			// Reschedule.
			$next_scan_time = self::get_scheduled_scan_time();
			wp_schedule_single_event( $next_scan_time, 'wphb_performance_scan' );
		} // End if().
	}

	/**
	 * Get the schedule time for a scan.
	 *
	 * @param  bool $clear_cron  Force to clear scanning cron.
	 * @return false|int
	 * @since  1.4.5
	 */
	public static function get_scheduled_scan_time( $clear_cron = true ) {

		if ( $clear_cron ) {
			wp_clear_scheduled_hook( 'wphb_performance_scan' );
		}
		$settings = wphb_get_settings();

		switch ( $settings['email-frequency'] ) {
			case '1':
				// Check if the time is over or not, then send the date.
				$time_string      = date( 'Y-m-d' ) . ' ' . $settings['email-time'] . ':00';
				$next_time_string = date( 'Y-m-d', strtotime( 'tomorrow' ) ) . ' ' . $settings['email-time'] . ':00';
				break;
			case '7':
			default:
				$time_string      = date( 'Y-m-d', strtotime( $settings['email-day'] . ' this week' ) ) . ' ' . $settings['email-time'] . ':00';
				$next_time_string = date( 'Y-m-d', strtotime( $settings['email-day'] . ' next week' ) ) . ' ' . $settings['email-time'] . ':00';
				break;
			case '30':
				$time_string      = date( 'Y-m-d', strtotime( $settings['email-day'] . ' this month' ) ) . ' ' . $settings['email-time'] . ':00';
				$next_time_string = date( 'Y-m-d', strtotime( $settings['email-day'] . ' next month' ) ) . ' ' . $settings['email-time'] . ':00';
				break;
		}

		$to_utc = self::local_to_utc( $time_string );
		if ( $to_utc < time() ) {
			return self::local_to_utc( $next_time_string );
		} else {
			return $to_utc;
		}

	}

	/**
	 * Local time to UTC.
	 *
	 * @param  string $time  Time string.
	 * @return false|int
	 * @since  1.4.5
	 */
	private static function local_to_utc( $time ) {

		$tz = get_option( 'timezone_string' );
		if ( ! $tz ) {
			$gmt_offset = get_option( 'gmt_offset' );
			if ( 0 === $gmt_offset ) {
				return strtotime( $time );
			}
			$tz = self::get_timezone_string( $gmt_offset );
		}

		if ( ! $tz ) {
			$tz = 'UTC';
		}
		$timezone = new DateTimeZone( $tz );
		$time     = new DateTime( $time, $timezone );

		// Had to switch because of PHP 5.2 compatibility issues.
		//return $time->getTimestamp();
		return $time->format( 'U' );

	}

	/**
	 * Get time zone string.
	 *
	 * @param  string $timezone  Time zone.
	 * @return false|string
	 * @since  1.4.5
	 */
	private static function get_timezone_string( $timezone ) {

		$timezone = explode( '.', $timezone );
		if ( isset( $timezone[1] ) ) {
			$timezone[1] = 30;
		} else {
			$timezone[1] = '00';
		}
		$offset = implode( ':', $timezone );
		list( $hours, $minutes ) = explode( ':', $offset );
		$seconds = $hours * 60 * 60 + $minutes * 60;
		$tz      = timezone_name_from_abbr( '', $seconds, 1 );
		if ( false === $tz ) {
			$tz = timezone_name_from_abbr( '', $seconds, 0 );
		}

		return $tz;

	}

}