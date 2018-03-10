<?php

class WP_Hummingbird_Module_Performance extends WP_Hummingbird_Module {

	public function init() {}

	public function run() {}

	/**
	 * Implement abstract parent method for clearing cache.
	 *
	 * @since 1.7.1
	 */
	public function clear_cache() {
		$last_report = WP_Hummingbird_Settings::get( 'wphb-last-report' );
		if ( $last_report && isset( $last_report->data->score ) ) {
			// Save latest score.
			WP_Hummingbird_Settings::update_setting( 'last_score', $last_report->data->score,'performance' );
		}
		WP_Hummingbird_Settings::delete( 'wphb-last-report' );
		WP_Hummingbird_Settings::delete( 'wphb-doing-report' );
		WP_Hummingbird_Settings::delete( 'wphb-stop-report' );
	}

	/**
	 * Initializes the Performance Scan
	 *
	 * @since 1.7.1 Removed static property.
	 */
	public function init_scan() {
		// Clear the cache.
		$this->clear_cache();

		// Start the test.
		self::set_doing_report( true );
		$api = WP_Hummingbird_Utils::get_api();
		$api->performance->ping();

		// Clear dismissed report.
		if ( self::report_dismissed() ) {
			self::dismiss_report( false );
		}

		// TODO: this creates a duplicate task from cron.
		do_action( 'wphb_init_performance_scan' );
	}

	/**
	 * Do a cron scan.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public static function cron_scan() {
		// Start the test.
		self::set_doing_report( true );
		$api = WP_Hummingbird_Utils::get_api();
		$report = $api->performance->check();
		// Stop the test.
		self::set_doing_report( false );

		// Return the results.
		return $report;
	}

	/**
	 * Return the last Performance scan done data
	 *
	 * @return bool|mixed|WP_Error Data of the last scan or false of there's not such data
	 */
	public static function get_last_report() {
		$report = WP_Hummingbird_Settings::get( 'wphb-last-report' );

		if ( $report ) {
			$last_score = WP_Hummingbird_Settings::get_setting( 'last_score', 'performance' );

			if ( $last_score && ! is_wp_error( $report ) ) {
				$report->data->last_score = array( 'score' => $last_score );
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
		if ( WP_Hummingbird_Settings::get( 'wphb-stop-report' ) ) {
			return false;
		}
		return WP_Hummingbird_Settings::get( 'wphb-doing-report' );
	}

	/**
	 * Check if Performance Scan is currently halted
	 *
	 * @return bool
	 */
	public static function stopped_report() {
		return (bool) WP_Hummingbird_Settings::get( 'wphb-stop-report' );
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
			WP_Hummingbird_Settings::delete( 'wphb-doing-report' );
			WP_Hummingbird_Settings::update( 'wphb-stop-report', true );
		} else {
			// Set time when we started the report.
			WP_Hummingbird_Settings::update( 'wphb-doing-report', current_time( 'timestamp' ) );
			WP_Hummingbird_Settings::delete( 'wphb-stop-report' );
		}
	}

	/**
	 * Get latest report from server
	 */
	public static function refresh_report() {
		self::set_doing_report( false );
		$api = WP_Hummingbird_Utils::get_api();
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
		WP_Hummingbird_Settings::update( 'wphb-last-report', $results );
	}

	/**
	 * Check if time enough has passed to make another test ( 5 minutes )
	 *
	 * @return bool|integer True if a new test is available or the time in minutes remaining for next test
	 */
	public static function can_run_test() {
		$last_report = self::get_last_report();
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
	 * Set last report dismissed status to true
	 *
	 * @since 1.8
	 *
	 * @param bool $dismiss  Enable or disable dismissed report status.
	 *
	 * @return bool
	 */
	public static function dismiss_report( $dismiss ) {
		WP_Hummingbird_Settings::update_setting( 'dismissed', (bool) $dismiss, 'performance' );

		if ( (bool) $dismiss ) {
			// Ignore report in the Hub
			$api = WP_Hummingbird_Utils::get_api();
			$results = $api->performance->ignore();

			if ( is_wp_error( $results ) ) {
				return $results->get_error_message();
			}
		}

		return true;
	}

	/**
	 * Return whether the last report was dismissed
	 *
	 * @since 1.8
	 *
	 * @return bool True if user dismissed report or false of there's no site option
	 */
	public static function report_dismissed() {
		if ( WP_Hummingbird_Settings::get_setting( 'dismissed', 'performance' ) ) {
			return true;
		}

		$last_report = self::get_last_report();
		if ( isset( $last_report->data->ignored ) && $last_report->data->ignored ) {
			return true;
		}

		return false;
	}

}