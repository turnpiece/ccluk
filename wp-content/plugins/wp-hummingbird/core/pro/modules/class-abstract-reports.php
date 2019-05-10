<?php
/**
 * Abstract module for reports modules.
 *
 * Supports Performance reports and Uptime reports.
 * Also used for sending out email reports after performance scans
 *
 * @since 1.9.4
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Module_Reports
 */
abstract class WP_Hummingbird_Module_Reports extends WP_Hummingbird_Module {

	/**
	 * Module slug. It's used in the database calls. Accepted values: performance, uptime.
	 *
	 * @var string $module
	 */
	protected static $module;

	/**
	 * Initialize the module
	 *
	 * @since 1.9.4
	 */
	public function init() {
		add_action( 'wphb_activate', array( $this, 'on_activate' ) );

		// Default settings.
		add_filter( 'wp_hummingbird_default_options', array( $this, 'add_default_options' ) );

		// Process report cron task.
		add_action( 'wphb_' . $this::$module . '_report', array( $this, 'process_report' ) );
	}

	/**
	 * Execute the module actions.
	 */
	public function run() {}

	/**
	 * Implement abstract parent method for clearing cache.
	 */
	public function clear_cache() {}

	/**
	 * Function to process cron report task.
	 */
	abstract protected function process_report();

	/**
	 * Send email report.
	 *
	 * @since 1.4.5
	 *
	 * @param mixed $last_report  Last report data.
	 * @param array $recipients   List of recipients.
	 */
	abstract protected function send_email_report( $last_report, $recipients = array() );

	/**
	 * Add a set of default options to Hummingbird settings.
	 *
	 * @since 1.9.3
	 * @since 1.9.4  Moved to abstract class
	 *
	 * @param array $options  List of default Hummingbird settings.
	 *
	 * @return array
	 */
	public function add_default_options( $options ) {
		$modules = array( 'performance', 'uptime' );

		$week_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);

		foreach ( $modules as $module ) {
			$options[ $module ]['reports']['frequency']  = 7;
			$options[ $module ]['reports']['day']        = $week_days[ array_rand( $week_days, 1 ) ];
			$options[ $module ]['reports']['time']       = wp_rand( 0, 23 ) . ':00';
			$options[ $module ]['reports']['recipients'] = array();
		}

		$options['performance']['reports']['type']     = 'desktop'; // desktop, mobile or both.
		$options['performance']['reports']['metrics']  = true;
		$options['performance']['reports']['audits']   = true;
		$options['performance']['reports']['historic'] = true;

		return $options;
	}

	/**
	 * Enable cron task. Triggered during plugin activation.
	 *
	 * @since 1.9.4
	 */
	public function on_activate() {
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			return;
		}

		if ( ! $module = WP_Hummingbird_Utils::get_module( $this::$module ) ) {
			return;
		}

		$options = $module->get_options();

		// Try to schedule next scan.
		if ( $options['reports']['enabled'] ) {
			wp_schedule_single_event( self::get_scheduled_time( $this::$module ), 'wphb_' . $this::$module . '_report' );
		}
	}

	/**
	 * Get the schedule time for a scan.
	 *
	 * @since 1.4.5
	 *
	 * @param string $module      Module slug.
	 * @param bool   $clear_cron  Force to clear scanning cron.
	 *
	 * @return false|int
	 */
	public static function get_scheduled_time( $module, $clear_cron = true ) {
		if ( $clear_cron ) {
			wp_clear_scheduled_hook( "wphb_{$module}_report" );
		}

		$options = WP_Hummingbird_Settings::get_setting( 'reports', $module );

		switch ( $options['frequency'] ) {
			case '1':
				// Check if the time is over or not, then send the date.
				$time_string      = date( 'Y-m-d' ) . ' ' . $options['time'] . ':00';
				$next_time_string = date( 'Y-m-d', strtotime( 'tomorrow' ) ) . ' ' . $options['time'] . ':00';
				break;
			case '7':
			default:
				$time_string      = date( 'Y-m-d', strtotime( $options['day'] . ' this week' ) ) . ' ' . $options['time'] . ':00';
				$next_time_string = date( 'Y-m-d', strtotime( $options['day'] . ' next week' ) ) . ' ' . $options['time'] . ':00';
				break;
			case '30':
				$time_string      = date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( 'first day of this month' ) ) . ' +' . ( $options['day'] - 1 ) . ' days ' ) ) . ' ' . $options['time'] . ':00';
				$next_time_string = date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( 'first day of next month' ) ) . ' +' . ( $options['day'] - 1 ) . ' days ' ) ) . ' ' . $options['time'] . ':00';
				break;
		}

		$to_utc = WP_Hummingbird_Utils::local_to_utc( $time_string );
		if ( $to_utc < time() ) {
			return WP_Hummingbird_Utils::local_to_utc( $next_time_string );
		}

		return $to_utc;
	}

	/**
	 * Build issues html table.
	 *
	 * @access private
	 * @param  mixed $last_test  Latest test data.
	 * @param  array $params     Additional data for report.
	 * @return string            HTML for email.
	 * @since  1.4.5
	 */
	protected static function issues_list_html( $last_test, $params ) {
		ob_start();
		self::load_template( 'index', compact( 'last_test', 'params' ) );
		return ob_get_clean();
	}

	/**
	 * Try to load a single reporting template.
	 *
	 * @param string $template  Template name. It should match the filename without extension.
	 * @param array  $args      Variables to pass to the templates.
	 */
	public static function load_template( $template, $args = array() ) {
		$dirs = apply_filters(
			'wphb_reporting_templates_folders',
			array(
				'stylesheet' => get_stylesheet_directory() . '/wphb/',
				'template'   => get_template_directory() . '/wphb/',
				'plugin'     => WPHB_DIR_PATH . 'core/pro/modules/reporting/templates/',
			)
		);

		foreach ( (array) $dirs as $dir ) {
			$file = trailingslashit( $dir ) . "$template.php";
			if ( is_readable( $file ) ) {
				extract( $args );
				/* @noinspection PhpIncludeInspection */
				include $file;
				break;
			}
		}
	}

}
