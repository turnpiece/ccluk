<?php // phpcs:ignore

class Snapshot_Helper_System {

	/**
	 * Checks if a PHP function call call is available
	 *
	 * Loosely based on https://stackoverflow.com/a/12980534
	 *
	 * @param string $func Call to check
	 * @param bool $ignore_safe_mode Whether to skip safe mode check
	 *
	 * @return bool
	 */
	public static function is_available ($func, $ignore_safe_mode = false) {
		static $available = array();
		if (isset($available[$func])) return $available[$func];

		$not_in_safe_mode = !empty($ignore_safe_mode)
			? true
			: !ini_get('safe_mode')
		;

		$status = false;
		if (function_exists($func) && $not_in_safe_mode) {
			$disabled = sprintf(
				'%s,%s',
				ini_get('disable_functions'),
				ini_get('suhosin.executor.func.blacklist')
			);
			$status = !in_array(
				$func,
				preg_split('/,\s*/', $disabled),
				true
			);
		}

		$available[$func] = $status;
		return !!( $available[$func] );

	}

	/**
	 * Gets system command path
	 *
	 * @param string $cmd Command to query for
	 *
	 * @return string Empty string on failure, path on success
	 */
	public static function get_command ($cmd) {
		if (
			!self::is_available('escapeshellcmd')
			||
			!self::is_available('exec')
		) return '';

		$cmd = escapeshellcmd($cmd);

		// We have checked if system commands are available before this point.
		// phpcs:ignore
		return exec("command -v {$cmd}");
	}

	/**
	 * Checks whether a command is present
	 *
	 * @param string $cmd Command to check
	 *
	 * @return bool
	 */
	public static function has_command ($cmd) {
		$result = self::get_command($cmd);
		return !empty($result);
	}

	/**
	 * Executes a system command
	 *
	 * @uses SNAPSHOT_SYSTEM_DEBUG_OUTPUT define to add command info to log
	 *
	 * @param string $command Command to execute
	 * @param string $context What this command is meant to do
	 *
	 * @return bool|WP_Error instace on failure (with info), (bool)true on success
	 */
	public static function run ($command, $context = '') {
		$context = !empty($context)
			? "command meant to {$context}"
			: 'generic command'
		;

		$output = array();
		$status = 1;
		$cmd_string = defined('SNAPSHOT_SYSTEM_DEBUG_OUTPUT') && SNAPSHOT_SYSTEM_DEBUG_OUTPUT
			? ":\n\t> {$command}"
			: ''
		;

		Snapshot_Helper_Log::info("About to run {$context}{$cmd_string}");

		// We have checked if system commands are available before this point.
		// phpcs:ignore
		exec($command, $output, $status);

		if (!empty($status)) {
			$msg = join("\n", $output);
			Snapshot_Helper_Log::error("Error running {$context}: [{$msg}]");
			return new WP_Error(__CLASS__, $msg, $status);
		}

		Snapshot_Helper_Log::info("Successfully executed {$context}");

		return true;
	}

	/**
	 * Gets host part from the DB connection string
	 *
	 * Connection string is as defined in wp-config
	 *
	 * @param string $connection Connection string
	 *
	 * @return string Host part
	 */
	public static function get_db_host ($connection) {
		if (false === strpos($connection, ':')) return $connection; // No port info, use default

		$raw = explode(':', $connection);
		$host = $raw[0];

		if (count($raw) > 2) {
			$port = array_pop($raw);
			$host = join(':', $raw); // Apparently, host part has colon for whatever reason
		}

		return $host;
	}

	/**
	 * Gets port part from the DB connection string
	 *
	 * Connection string is as defined in wp-config
	 *
	 * @param string $connection Connection string
	 *
	 * @return int Port part
	 */
	public static function get_db_port ($connection) {
		$port = self::get_raw_db_mode($connection, 3306);

		return is_numeric($port)
			? (int)$port
			: 3306
		;
	}

	/**
	 * Checks whether we're dealing with the socket DB connection
	 *
	 * Connection string is as defined in wp-config
	 *
	 * @param string $connection Connection string
	 *
	 * @return bool
	 */
	public static function is_socket_connection ($connection) {
		$port = self::get_raw_db_mode($connection, false);
		if (false === $port) return false;

		$host = self::get_db_host($connection);
		if (!in_array($host, array(
			"",
			"localhost",
			"127.0.0.1",
			),
			true
		)) return false;

		return !is_numeric($port)
			? (false !== strpos($port, '/')) // Make sure it's actual full path, or not a socket
			: false
		;
	}

	/**
	 * Gets last part from colon-delimited connection string
	 *
	 * @param string $connection Connection string
	 * @param mixed $fallback Used if no port detected (defaults to (bool)false)
	 *
	 * @return string|mixed Extracted string, or fallback on failure
	 */
	public static function get_raw_db_mode ($connection, $fallback = false) {
		if (false === strpos($connection, ':')) return $fallback; // No port info, use default

		$raw = explode(':', $connection);
		return array_pop($raw);
	}

}