<?php

class WP_Hummingbird_Module_Performance extends WP_Hummingbird_Module {

	public function init() {}

	public function run() {}

	/**
	 * Initializes the Performance Scan
	 */
	public static function init_scan() {
		// Clear the cache.
		self::clear_cache();

		// Start the test.
		self::set_doing_report( true );
		$api = wphb_get_api();
		$api->performance->ping();
	}

	/**
	 * Do a cron scan.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public static function cron_scan() {
		// Start the test.
		self::set_doing_report( true );
		$api = wphb_get_api();
		$report = $api->performance->check();
		// Stop the test.
		self::set_doing_report( false );

		// Return the results.
		return $report;
	}

	/**
	 * Return the last Performance scan done data
	 *
	 * @return false|array|WP_Error Data of the last scan or false of there's not such data
	 */
	public static function get_last_report() {

		$report = get_site_option( 'wphb-last-report' );
		if ( $report ) {
			$last_score = get_site_option( 'wphb-last-report-score' );
			if ( $last_score && ! is_wp_error( $report ) ) {
				$report->data->last_score = $last_score;
			} elseif ( is_object( $report ) && ! is_wp_error( $report ) ) {
				$report->data->last_score = false;
			}
			return $report;
		}

		return false;
	}


	/**
	 * Check if WP Hummingbird is currently doing a Performance Scan
	 *
	 * @return false|int Timestamp when the report started, false if there's no report being executed
	 */
	public static function is_doing_report() {
		if ( get_site_option( 'wphb-stop-report' ) ) {
			return false;
		}

		return get_site_option( 'wphb-doing-report' );
	}

	/**
	 * Check if Performance Scan is currently halted
	 *
	 * @return bool
	 */
	public static function stopped_report() {
		return (bool) get_site_option( 'wphb-stop-report' );
	}

	/**
	 * Start a new Performance Scan
	 *
	 * It sets the new status for the report
	 *
	 * @param bool $status If set to true, it will start a new Performance Report, otherwise it will stop the current one
	 */
	public static function set_doing_report( $status = true ) {
		if ( ! $status ) {
			delete_site_option( 'wphb-doing-report' );
			update_site_option( 'wphb-stop-report', true );
		} else {
			// Set time when we started the report.
			update_site_option( 'wphb-doing-report', current_time( 'timestamp' ) );
			delete_site_option( 'wphb-stop-report' );
		}
	}

	/**
	 * Get latest report from server
	 */
	public static function refresh_report() {
		self::set_doing_report( false );
		$api = wphb_get_api();
		$results = $api->performance->results();

		if ( is_wp_error( $results ) ) {
			// It's an error.
			$results = new WP_Error(
				'performance-error',
				__( "The performance test didn't return any results. This probably means you're on a local website (which we can't scan) or something went wrong trying to access WPMU DEV. Try again and if this error continues to appear please open a ticket with our support heroes", 'wphb' ),
				array(
					'details' => $results->get_error_message(),
				)
			);
		}

		update_site_option( 'wphb-last-report', $results );
	}

	/**
	 * Check if time enough has passed to make another test ( 5 minutes )
	 *
	 * @return bool|integer True if a new test is available or the time in minutes remaining for next test
	 */
	public static function can_run_test() {
		$last_report = wphb_performance_get_last_report();
		$current_gmt_time = current_time( 'timestamp', true );
		if ( $last_report && ! is_wp_error( $last_report ) ) {
			$data_time = $last_report->data->time;
			if ( ( $data_time + 300 ) < $current_gmt_time ) {
				return true;
			} else {
				$remaining = ceil( ( ( $data_time + 300 ) - $current_gmt_time ) / 60 );
				return absint( $remaining );
			}
		}

		return true;
	}

	/**
	 * Clear Performance Module cache
	 */
	public static function clear_cache() {
		$last_report = get_site_option( 'wphb-last-report' );
		if ( $last_report && isset( $last_report->data->score ) ) {
			// Save latest score.
			update_site_option( 'wphb-last-report-score', array(
				'score' => $last_report->data->score,
			));
		}

		delete_site_option( 'wphb-last-report' );
		delete_site_option( 'wphb-doing-report' );
		delete_site_option( 'wphb-stop-report' );
	}

}