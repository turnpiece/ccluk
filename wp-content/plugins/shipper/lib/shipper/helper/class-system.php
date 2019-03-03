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
	static private $_max_exec_time;

	/**
	 * Optimize system for performance, as much as possible
	 *
	 * @return bool
	 */
	static public function optimize() {
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
	static public function optimize_memory_constraints() {
		$size = @ini_get( 'memory_limit' );
		if ( false === self::is_changeable( 'memory_limit' ) ) {
			Shipper_Helper_Log::write(
				sprintf( 'WARNING: Unable to change memory limit. Currently it is %s', $size )
			);
			return false;
		}

		// Shut up and take all my memory.
		return false !== @ini_set( 'memory_limit', -1 );
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
	static public function get_max_exec_time() {
		if ( empty( self::$_max_exec_time ) ) {
			self::$_max_exec_time = @ini_get( 'max_execution_time' );
		}
		return self::$_max_exec_time;
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
	static public function get_max_exec_time_capped() {
		$time = (int) self::get_max_exec_time();
		if ( $time <= 0 ) {
			return 180;
		}
		return $time <= 180 ? $time : 180;
	}

	/**
	 * Attempt to shift the time limit as much as possible
	 *
	 * @return bool
	 */
	static public function optimize_time_limit() {
		if ( self::is_disabled( 'set_time_limit' ) ) {
			Shipper_Helper_Log::write( 'WARNING: set_time_limit is disabled or not available.' );
			return false;
		}
		// Set the cached value *prior* to the shift attempt.
		if ( empty( self::$_max_exec_time ) ) {
			self::$_max_exec_time = @ini_get( 'max_execution_time' );
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
	static public function is_disabled( $func ) {
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
	static public function is_changeable( $what ) {
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
	 *
	 * @return bool
	 */
	static public function is_in_safe_mode() {
		$is_safe_mode = strtolower( ini_get( 'safe_mode' ) );

		/**
		 * Checks whether we're in PHP safe mode.
		 *
		 * Used in tests.
		 *
		 * @param bool $is_in_safe_mode Whether we're in safe mode.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_helper_system_safemode',
			( ! empty( $is_safe_mode ) && 'off' !== $is_safe_mode )
		);
	}

}