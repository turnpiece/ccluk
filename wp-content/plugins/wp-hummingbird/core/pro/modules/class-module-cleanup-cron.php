<?php

/**
 * Class WP_Hummingbird_Module_Cleanup_Cron
 *
 * Only for premium members.
 *
 * @since 1.8
 *
 * @package Hummingbird
 */
class WP_Hummingbird_Module_Cleanup_Cron extends WP_Hummingbird_Module {

	/**
	 * Initialize the module.
	 */
	public function init() {
		// Process cron cleanup.
		add_action( 'wphb_database_cleanup', array( $this, 'database_cleanup' ) );

		// Default settings.
		add_filter( 'wp_hummingbird_default_options', array( $this, 'add_default_options' ) );

		add_action( 'wphb_activate', array( $this, 'on_activate' ) );
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
	 * Triggered during plugin activation.
	 */
	public function on_activate() {
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			return;
		}

		// Try to schedule next scan.
		self::reschedule_cron();
	}

	/**
	 * Reschedule next cron job.
	 */
	public static function reschedule_cron() {
		// Clean all cron.
		wp_clear_scheduled_hook( 'wphb_database_cleanup' );

		/* @var WP_Hummingbird_Module_Performance $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options = $adv_module->get_options();

		if ( true === (bool) $options['db_cleanups'] ) {
			wp_schedule_single_event( self::get_scheduled_scan_time(), 'wphb_database_cleanup' );
		}
	}

	/**
	 * Add a set of default options to Hummingbird settings.
	 *
	 * @param array $options  List of default Hummingbird settings.
	 *
	 * @return array
	 */
	public function add_default_options( $options ) {
		$options['advanced']['db_frequency'] = 7;
		$options['advanced']['db_tables']    = array(
			'revisions'          => true,
            'drafts'             => true,
            'trash'              => true,
            'spam'               => true,
            'trash_comment'      => true,
            'expired_transients' => true,
		);

		return $options;
	}

	/**
	 * Return number of seconds until next cleanup.
	 *
	 * @return int
	 */
	public static function get_scheduled_scan_time() {
		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options = $adv_module->get_options();

		$seconds = DAY_IN_SECONDS * (int) $options['db_frequency'];

		return time() + $seconds;
	}

	/**
	 * Process cron task to clean the database.
	 */
	public function database_cleanup() {
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			// Clean all cron.
			wp_clear_scheduled_hook( 'wphb_database_cleanup' );
			return;
		}

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options = $adv_module->get_options();

		if ( ! isset( $options['db_tables'] ) ) {
			// Try to schedule next scan.
			self::reschedule_cron();
			return;
		}

		foreach ( $options['db_tables'] as $type => $value ) {
			if ( false === (bool) $value ) {
				continue;
			}

			$adv_module->delete_db_data( $type );
		}

		// Try to schedule next scan.
		self::reschedule_cron();
	}

}