<?php
/**
 * Shipper models: environment recognition model
 *
 * @package shipper
 */

/**
 * Environment model
 */
class Shipper_Model_Env {

	/**
	 * Whether we're running as part of a test suite
	 *
	 * @return bool
	 */
	public static function is_phpunit_test() {
		return ( defined( 'SHIPPER_IS_TEST_ENV' ) && SHIPPER_IS_TEST_ENV ) &&
			defined( 'SHIPPER_TESTS_DATA_DIR' ) &&
			class_exists( 'WP_UnitTestCase' ) &&
			function_exists( '_manually_load_plugin' );
	}

	/**
	 * Checks whether we're on WP Engine
	 *
	 * @return bool
	 */
	public static function is_wp_engine() {
		return defined( 'WPE_APIKEY' );
	}

	/**
	 * Whether we're in an environment that requires auth pings
	 *
	 * This generally means WP Engine.
	 *
	 * @return bool
	 */
	public static function is_auth_requiring_env() {

		/**
		 * Decide whether we're in an auth-requiring environment.
		 *
		 * Used in building ping request arguments to establish runner
		 * execution context.
		 *
		 * @param bool $is_auth Check result this far.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_is_auth_requiring_env',
			self::is_wp_engine()
		);
	}

	/**
	 * Checks whether we're on WPMU DEV Hosting
	 *
	 * @return bool
	 */
	public static function is_wpmu_hosting() {
		return isset( $_SERVER['WPMUDEV_HOSTED'] ) && ! empty( $_SERVER['WPMUDEV_HOSTED'] );
	}

	/**
	 * Checks whether we're on WPMU DEV Hosting
	 *
	 * @return bool
	 */
	public static function is_wpmu_staging() {
		if ( ! self::is_wpmu_hosting() ) {
			return false;
		}

		return isset( $_SERVER['WPMUDEV_HOSTING_ENV'] ) &&
			'production' !== $_SERVER['WPMUDEV_HOSTING_ENV'];
	}

	/**
	 * Checks whether we're dealing with Flywheel hosting
	 *
	 * @return bool
	 */
	public static function is_flywheel() {

		/**
		 * Whether or not we're dealing with Flywheel environment
		 *
		 * Used in tests.
		 *
		 * @since v1.0.2
		 *
		 * @param bool $is_flywheel Flywheel environment detected.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_env_is_flywheel',
			defined( 'FLYWHEEL_PLUGIN_DIR' )
		);
	}
}