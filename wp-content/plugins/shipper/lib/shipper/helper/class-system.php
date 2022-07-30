<?php
/**
 * Shipper helpers: system
 *
 * Deals with system stuff, such as timeout configs,
 * memory limits and such.
 *
 * @package shipper
 */

/**
 * Shipper system helper
 */
class Shipper_Helper_System {

	/**
	 * Cached max execution time.
	 *
	 * @var int
	 */
	private static $max_exec_time;

	/**
	 * Optimize system for performance, as much as possible
	 *
	 * @return bool
	 */
	public static function optimize() {
		if ( self::is_in_safe_mode() ) {
			Shipper_Helper_Log::write( 'WARNING: Safe mode on, skipping optimizations.' );

			return false;
		}

		self::optimize_time_limit();
		self::optimize_memory_constraints();

		return true;
	}

	/**
	 * Attempt to increase memory constraints
	 *
	 * @return bool
	 */
	public static function optimize_memory_constraints() {
		$size = @ini_get( 'memory_limit' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( false === self::is_changeable( 'memory_limit' ) ) {
			Shipper_Helper_Log::write(
				sprintf( 'WARNING: Unable to change memory limit. Currently it is %s', $size )
			);

			return false;
		}
		// Shut up and take all my memory.
		return false !== @ini_set( 'memory_limit', - 1 ); // phpcs:ignore
	}

	/**
	 * Gets maximum execution time
	 *
	 * This will use cached value, set *before* the time limit optimization.
	 * This is because set_time_limit can lie - exec time can be enforced.
	 * Apparently.
	 *
	 * @return int
	 */
	public static function get_max_exec_time() {
		if ( empty( self::$max_exec_time ) ) {
			self::$max_exec_time = @ini_get( 'max_execution_time' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		/**
		 * Max exec time before limit optimization
		 *
		 * Used in tests.
		 *
		 * @param int $time Maximum execution time.
		 *
		 * @return int
		 * @since v1.0.3
		 */
		return (int) apply_filters(
			'shipper_max_exec_time',
			self::$max_exec_time
		);
	}

	/**
	 * Gets maximum execution time capped to max value
	 *
	 * Used for kickstart scheduling and lock expiration.
	 * Caps the time at 3 mins.
	 * Basically, if it's not done by then - it won't get done.
	 *
	 * @return int
	 */
	public static function get_max_exec_time_capped() {
		$time = (int) self::get_max_exec_time();

		/**
		 * Maximum execution time
		 *
		 * @param int $time Maximum execution time.
		 *
		 * @return int
		 * @since v1.0.1
		 */
		$cap_time = (int) apply_filters(
			'shipper_max_exec_time_capped',
			180
		);
		if ( $time <= 0 ) {
			return $cap_time;
		}

		return min(
			max( 60, $time ),
			$cap_time
		);
	}

	/**
	 * Attempt to shift the time limit as much as possible
	 *
	 * @return bool
	 */
	public static function optimize_time_limit() {
		if ( self::is_disabled( 'set_time_limit' ) ) {
			Shipper_Helper_Log::write( 'WARNING: set_time_limit is disabled or not available.' );

			return false;
		}
		// Set the cached value *prior* to the shift attempt.
		if ( empty( self::$max_exec_time ) ) {
			self::$max_exec_time = @ini_get( 'max_execution_time' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		return set_time_limit( 0 );
	}

	/**
	 * Checks if a function is explicitly disabled
	 *
	 * @param string $func Function to check.
	 *
	 * @return bool
	 */
	public static function is_disabled( $func ) {
		$callable = is_callable( $func );
		if ( $callable ) {
			$disabled = array_map( 'trim', explode( ',', ini_get( 'disable_functions' ) ) );
			$callable = ! in_array( $func, $disabled, true );
		}

		/**
		 * Whether a function call is disabled.
		 *
		 * Used in tests.
		 *
		 * @param bool $disabled Whether a function call is disabled.
		 * @param string $func Function to check.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_helper_system_disabled',
			! $callable,
			$func
		);
	}

	/**
	 * Determines if the ini key is changeable
	 *
	 * Basically filterable wrapper around `wp_is_ini_value_changeable`.
	 * Used in tests.
	 *
	 * @param string $what An ini key to check.
	 *
	 * @return bool
	 */
	public static function is_changeable( $what ) {
		/**
		 * Whether an ini key is changeable.
		 *
		 * Used in tests.
		 *
		 * @param bool $changeable Whether the key is changeable.
		 * @param string $what Ini key to check.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_helper_system_changeable',
			wp_is_ini_value_changeable( $what ),
			$what
		);
	}

	/**
	 * Checks whether we're in PHP safe mode.
	 * Used in tests.
	 *
	 * @return bool
	 */
	public static function is_in_safe_mode() {
		return apply_filters( 'shipper_helper_system_safemode', false );
	}

	/**
	 * Checks if a PHP function call call is available
	 *
	 * Loosely based on https://stackoverflow.com/a/12980534
	 *
	 * @param string $func Call to check.
	 *
	 * @return bool
	 */
	public static function is_available( $func ) {
		static $available = array();
		if ( isset( $available[ $func ] ) ) {
			return (bool) $available[ $func ];
		}

		$status = false;
		if ( function_exists( $func ) && ! self::is_in_safe_mode() ) {
			$disabled = sprintf(
				'%s,%s',
				ini_get( 'disable_functions' ),
				ini_get( 'suhosin.executor.func.blacklist' )
			);
			$status   = ! in_array(
				$func,
				preg_split( '/,\s*/', $disabled ),
				true
			);
		}

		$available[ $func ] = $status;

		return (bool) $available[ $func ];
	}

	/**
	 * Whether or not we can call system binaries.
	 *
	 * @return bool
	 */
	public static function can_call_system() {
		return self::is_available( 'escapeshellcmd' ) && self::is_available( 'exec' );
	}

	/**
	 * Gets system command path
	 *
	 * @param string $cmd Command to query for.
	 *
	 * @return string Empty string on failure, path on success
	 */
	public static function get_command( $cmd ) {
		if ( ! self::can_call_system() ) {
			return '';
		}

		$cmd = escapeshellcmd( $cmd );

		// We have checked if system commands are available before this point.

		return exec( "command -v {$cmd}" ); // phpcs:ignore
	}

	/**
	 * Checks whether a command is present
	 *
	 * @param string $cmd Command to check.
	 *
	 * @return bool
	 */
	public static function has_command( $cmd ) {
		$result = self::get_command( $cmd );

		return ! empty( $result );
	}

	/**
	 * Check if it's wpmudev host.
	 *
	 * @return bool Check if this is WPMUDEV host.
	 */
	public static function is_wpmudev_host() {
		return isset( $_SERVER['WPMUDEV_HOSTED'] ) && ! empty( $_SERVER['WPMUDEV_HOSTED'] );
	}

	/**
	 * Get safe max execution time in seconds.
	 *
	 * @since 1.2.4
	 *
	 * @param int $trade_off 8 seconds trade off time.
	 *
	 * @return int
	 */
	public static function get_safe_max_execution_time( $trade_off = 8 ) {
		$max_time = (int) self::get_max_exec_time();

		if ( ! $max_time || $max_time > 30 ) {
			// Seems max time is set to unlimited. But we want to play safe, so settings it to 30 seconds.
			$max_time = 30;
		}

		return apply_filters( 'shipper_get_safe_max_execution_time', $max_time - $trade_off );
	}
}