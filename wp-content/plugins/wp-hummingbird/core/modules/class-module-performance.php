<?php
/**
 * Performance module.
 *
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Module_Performance
 */
class WP_Hummingbird_Module_Performance extends WP_Hummingbird_Module {

	/**
	 * Initializes the module. Always executed even if the module is deactivated.
	 *
	 * Do not use __construct in subclasses, use init() instead
	 */
	public function init() {
		// TODO: Make sure this is only loaded for admins and the DIV is hidden, so it doesn't mess with the layout.
		if ( ! is_admin() ) {
			//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_global' ) );
			// Generate markup for the modal.
			//add_action( 'wp_footer', array( $this, 'enqueue_modal_html' ) );
		}
	}

	/**
	 * Execute the module actions. It must be defined in subclasses.
	 */
	public function run() {}

	/**
	 * Implement abstract parent method for clearing cache.
	 *
	 * @since 1.7.1
	 */
	public function clear_cache() {
		WP_Hummingbird_Settings::delete( 'wphb-last-report' );
		WP_Hummingbird_Settings::delete( 'wphb-doing-report' );
		WP_Hummingbird_Settings::delete( 'wphb-stop-report' );
	}

	/**
	 * Initializes the Performance Scan
	 *
	 * @param bool|string $url  Custom URL for scan.
	 *
	 * @since 1.7.1 Removed static property.
	 */
	public function init_scan( $url = false ) {
		// Clear the cache.
		if ( ! $url ) {
			$this->clear_cache();
		}

		// Start the test.
		self::set_doing_report( true );
		$api = WP_Hummingbird_Utils::get_api();
		$api->performance->ping( $url );

		// Clear dismissed report.
		if ( ! $url && self::report_dismissed() ) {
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
		$api    = WP_Hummingbird_Utils::get_api();
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
		return $report ? $report : false;
	}

	/**
	 * Check if WP Hummingbird is currently doing a Performance Scan
	 *
	 * @return false|int Timestamp when the report started, false if there's no report being executed
	 */
	public static function is_doing_report() {
		$stopped = WP_Hummingbird_Settings::get( 'wphb-stop-report' );
		return $stopped ? false : WP_Hummingbird_Settings::get( 'wphb-doing-report' );
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
	 * @param bool $status If set to true, it will start a new Performance Report, otherwise it will stop the current one.
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
	 *
	 * @param bool|string $url  Custom URL for report.
	 *
	 * @return array|WP_Error
	 */
	public static function refresh_report( $url = false ) {
		self::set_doing_report( false );
		self::dismiss_report( false );
		$api     = WP_Hummingbird_Utils::get_api();
		$results = $api->performance->results( $url );

		$skip_db_save = false;

		if ( is_wp_error( $results ) ) {
			$error_message = $results->get_error_message();

			if ( 200 === $results->get_error_code() && 'Performance Results not found' === $error_message ) {
				if ( ! $url ) {
					$skip_db_save = true;
				}
				$message = __( 'Whoops, looks like we were unable to grab your test results this time round. Please wait a few moments and try again...', 'wphb' );
			} else {
				$message = __(
					"The performance test didn't return any results. This could be because you're on a local website
					(which we can't access) or something went wrong trying to connect with the testing API. Give it
					another go and if the problem persists, please open a ticket with our support team.",
					'wphb'
				);
			}

			// It's an actual error.
			$results = new WP_Error( 'performance-error', $message, array( 'details' => $error_message ) );
		}

		/**
		 * Fires after getting the latest report.
		 *
		 * @since 1.9.2
		 *
		 * @see https://developers.google.com/speed/docs/insights/v4/reference/pagespeedapi/runpagespeed
		 *
		 * @param object $report  Object with report data.
		 */
		if ( isset( $results->data ) && ! is_wp_error( $results->data ) ) {
			do_action( 'wphb_get_performance_report', $results->data );
		}

		if ( $url || $skip_db_save ) {
			return $results;
		}

		// Only save reports from Performance module.
		WP_Hummingbird_Settings::update( 'wphb-last-report', $results );
	}

	/**
	 * Check if time enough has passed to make another test ( 5 minutes )
	 *
	 * @param bool|wp_error|array|object $last_report  Last report.
	 *
	 * @return bool|integer True if a new test is available or the time in minutes remaining for next test
	 */
	public static function can_run_test( $last_report = false ) {
		if ( ! $last_report || is_wp_error( $last_report ) ) {
			$last_report = self::get_last_report();
		}

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
			// Ignore report in the Hub.
			$api     = WP_Hummingbird_Utils::get_api();
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
	 * @param bool|wp_error|array|object $last_report  Last report.

	 * @return bool True if user dismissed report or false of there's no site option
	 */
	public static function report_dismissed( $last_report = false ) {
		if ( WP_Hummingbird_Settings::get_setting( 'dismissed', 'performance' ) ) {
			return true;
		}

		if ( ! $last_report || is_wp_error( $last_report ) ) {
			$last_report = self::get_last_report();
		}

		if ( isset( $last_report->data->ignored ) && $last_report->data->ignored ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the class for a specific type. Used to select proper icons and styles.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $score  Score value.
	 * @param string $type   Type of item. Accepts: score, icon.
	 *
	 * @return string
	 */
	public static function get_impact_class( $score = 0, $type = 'score' ) {
		if ( ! in_array( $type, array( 'score', 'icon' ), true ) ) {
			return '';
		}

		// Make this method universal - either use 1 as a 100%, or 100 as 100%.
		if ( 0 < $score && 1 >= $score && is_float( $score ) ) {
			$score = $score * 100;
		}

		$impact_score_class = 'error';
		$impact_icon_class  = 'warning-alert';

		if ( 90 <= (int) $score ) {
			$impact_score_class = 'success';
			$impact_icon_class  = 'check-tick';
		} elseif ( 50 <= (int) $score ) {
			$impact_score_class = 'warning';
			$impact_icon_class  = 'warning-alert';
		}

		return 'score' === $type ? $impact_score_class : $impact_icon_class;
	}

	/**
	 * Get the lowest score from a list of audits and return the appropriate class.
	 *
	 * @since 2.0.0
	 *
	 * @param array $audits  Array with audits.
	 *
	 * @return string
	 */
	public static function get_audits_class( $audits ) {
		if ( is_null( $audits ) ) {
			return 'warning';
		}

		$lowest_score = 1;

		foreach ( $audits as $audit ) {
			if ( ! isset( $audit->score ) ) {
				continue;
			}

			if ( $lowest_score > $audit->score ) {
				$lowest_score = $audit->score;
			}
		}

		return self::get_impact_class( $lowest_score );
	}

	/**
	 * Enqueue styles for the modal.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_global() {
		wp_enqueue_style(
			'wphb-global',
			WPHB_DIR_URL . 'admin/assets/css/wphb-global.min.css',
			array(),
			WPHB_VERSION
		);
	}

	/**
	 * Enqueue one-off performance report modal.
	 * Loads only on the front end.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_modal_html() {
		?>
		<div class="<?php echo esc_attr( WPHB_SUI_VERSION ); ?>">
			<div class="sui-wrap">
				<?php include WPHB_DIR_PATH . 'admin/views/modals/performance-modal.php'; ?>
			</div>
		</div>
		<?php
	}

}
