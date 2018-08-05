<?php // phpcs:ignore

class Snapshot_Helper_Log {

	const LEVEL_ERROR = 1;
	const LEVEL_WARNING = 2;
	const LEVEL_NOTICE = 3;
	const LEVEL_INFO = 4;

	const SECTION_DEFAULT = 'snapshot';
	const LEVEL_DEFAULT = 2;

	private static $_instance;

	private function __construct () {}

	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Start the logging action
	 *
	 * Clears the log in implicit logging environment.
	 * Used by controllers to indicate the action log start.
	 *
	 * @return bool
	 */
	public static function start () {
		$status = true;

		// Only clear the log if the logging is implicitly enabled
		if (Snapshot_Controller_Full_Log::get()->is_implicitly_enabled()) {
			$status = self::get()->clear_log();

			if (!empty($status)) self::info('Log file cleared');
			else self::warn('Unable to clear log file');
		}

		return $status;
	}

	/**
	 * Logs error message
	 *
	 * @param string $msg Message
	 * @param string $section Optional section argument
	 *
	 * @return bool
	 */
	public static function error ($msg, $section = false) {
		return self::get()->_log(self::LEVEL_ERROR, $msg, $section);
	}

	/**
	 * Logs warning message
	 *
	 * @param string $msg Message
	 * @param string $section Optional section argument
	 *
	 * @return bool
	 */
	public static function warn ($msg, $section = false) {
		return self::get()->_log(self::LEVEL_WARNING, $msg, $section);
	}

	/**
	 * Logs notice message
	 *
	 * @param string $msg Message
	 * @param string $section Optional section argument
	 *
	 * @return bool
	 */
	public static function note ($msg, $section = false) {
		return self::get()->_log(self::LEVEL_NOTICE, $msg, $section);
	}

	/**
	 * Logs info message
	 *
	 * @param string $msg Message
	 * @param string $section Optional section argument
	 *
	 * @return bool
	 */
	public static function info ($msg, $section = false) {
		return self::get()->_log(self::LEVEL_INFO, $msg, $section);
	}

	/**
	 * Public error level resolution interface
	 *
	 * @param int $level Severity level (see level constants)
	 *
	 * @return string Actual severity level name (as translatable string)
	 */
	public static function get_level_name ($level = false) {
		return self::get()->_get_level_name($level);
	}

	/**
	 * Returns known levels as type/label hash
	 *
	 * @return array
	 */
	public function get_known_levels () {
		static $levels;
		if (empty($levels))
			$levels = array(
				self::LEVEL_ERROR => __('Error', SNAPSHOT_I18N_DOMAIN),
				self::LEVEL_WARNING => __('Warning', SNAPSHOT_I18N_DOMAIN),
				self::LEVEL_NOTICE => __('Notice', SNAPSHOT_I18N_DOMAIN),
				self::LEVEL_INFO => __('Info', SNAPSHOT_I18N_DOMAIN),
			);
		return $levels;
	}

	/**
	 * Known logging sections getter
	 *
	 * Returns known full log sections as type/label hash
	 *
	 * @return array
	 */
	public function get_known_sections () {
		return array(
			self::SECTION_DEFAULT => __('Default', SNAPSHOT_I18N_DOMAIN),
			'Cron' => __('Cron', SNAPSHOT_I18N_DOMAIN),
			'Remote' => __('Remote', SNAPSHOT_I18N_DOMAIN),
			'Queue' => __('Queue', SNAPSHOT_I18N_DOMAIN),
		);
	}

	/**
	 * Gets section loggable define name
	 *
	 * @param string $section Section to convert
	 *
	 * @return string
	 */
	public function get_section_constant_name ($section) {
		$section = is_string($section) && !empty($section) && preg_match('/^[a-z]+$/i', $section)
			? $section
			: self::SECTION_DEFAULT
		;
		return 'SNAPSHOT_LOGGABLE_' . strtoupper($section);
	}

	/**
	 * Gets default level
	 *
	 * @return int
	 */
	public function get_default_level () {
		return self::LEVEL_DEFAULT;
	}

	/**
	 * Gets log file contents
	 *
	 * @return string
	 */
	public function get_log () {
		$file = $this->_get_log_file();
		if (empty($file) || !is_readable($file)) return '';

		// return file_get_contents($file);
		global $wp_filesystem;

		if( Snapshot_Helper_Utility::connect_fs() ) {
			return $wp_filesystem->get_contents( $file );
		} else {
			return new WP_Error( "filesystem_error", "Cannot initialize filesystem" );
		}
	}

	/**
	 * Clears log file contents
	 *
	 * @return bool
	 */
	public function clear_log () {

		$file = $this->_get_log_file();
		if (empty($file) || !is_readable($file)) return false;

		// return false !== file_put_contents($file, '');
		global $wp_filesystem;

		if( Snapshot_Helper_Utility::connect_fs() ) {
			return false !== $wp_filesystem->put_contents( $file, '', FS_CHMOD_FILE );
		} else {
			return false;
		}
	}

	/**
	 * Logs the message
	 *
	 * @param int $level Message severity level (see level constants)
	 * @param string $msg Message itself
	 * @param string $section Optional section argument
	 *
	 * @return bool
	 */
	protected function _log ($level, $msg, $section = false) {
		$section = !empty($section) ? $section : self::SECTION_DEFAULT;

		if (!$this->_is_loggable_level($level, $section)) return false;
		if (!$this->_is_loggable_section($section)) return false;

		$log_file = $this->_get_log_file();
		if (empty($log_file)) return false;

		$timestamp = date('Y-m-d H:i:s');
		$level_name = $this->_get_level_name($level);

		$line = "[{$section}][{$timestamp}][{$level_name}] {$msg}\n";
		// return !!file_put_contents($log_file, $line, FILE_APPEND|LOCK_EX);

		global $wp_filesystem;

		if( Snapshot_Helper_Utility::connect_fs() ) {
			return !!$wp_filesystem->put_contents( $log_file, $wp_filesystem->get_contents( $log_file ) . $line, FS_CHMOD_FILE );
		} else {
			return new WP_Error( "filesystem_error", "Cannot initialize filesystem" );
		}

	}

	/**
	 * Get full path to log file location
	 *
	 * @return mixed (string)Log file path on success, (bool)false on failure
	 */
	protected function _get_log_file () {
		$raw_path = WPMUDEVSnapshot::instance()->get_setting('backupLogFolderFull');
		if (empty($raw_path)) return false;

		$path = trailingslashit($raw_path) . Snapshot_Helper_String::conceal('snapshot.log');
		return $path;
	}

	/**
	 * Level to name resolution method
	 *
	 * @param int $level Severity level (see level constants)
 	 *
 	 * @return string Actual severity level name (as translatable string)
	 */
	protected function _get_level_name ($level) {
		$levels = $this->get_known_levels();
		/*
		static $levels;
		if (empty($levels)) $levels = array(
			Snapshot_Helper_Log::LEVEL_ERROR => __('Error', SNAPSHOT_I18N_DOMAIN),
			Snapshot_Helper_Log::LEVEL_WARNING => __('Warning', SNAPSHOT_I18N_DOMAIN),
			Snapshot_Helper_Log::LEVEL_NOTICE => __('Notice', SNAPSHOT_I18N_DOMAIN),
			Snapshot_Helper_Log::LEVEL_INFO => __('Info', SNAPSHOT_I18N_DOMAIN),
		);
		*/
		return !empty($levels[$level])
			? $levels[$level]
			: $levels[self::LEVEL_DEFAULT]
		;
	}

	/**
	 * Get floor severity log level assigned to section
	 *
	 * @param string $section Section
	 *
	 * @return int
	 */
	protected function _get_section_level ($section) {
		$level = self::LEVEL_DEFAULT;
		if (!empty($section)) {
			$const = $this->get_section_constant_name($section);
			if (defined($const))
				$level = constant($const);
		}
		return $level;
	}

	/**
	 * Check if the section is of loggable level
	 *
	 * @param string $section Section to check
	 *
	 * @return bool
	 */
	protected function _is_loggable_section ($section) {
		if (empty($section)) return false;
		$const = $this->get_section_constant_name($section);

		if (!defined($const)) return defined('SNAPSHOT_BACKTRACE_ALL');
		return true;
	}

	/**
	 * Check loggable level by section
	 *
	 * @param int $level Level to check
	 * @param string $section Section to check
	 *
	 * @return bool
	 */
	protected function _is_loggable_level ($level, $section) {
		if (defined('SNAPSHOT_BACKTRACE_ALL')) return true;

		if (empty($level) || !is_numeric($level)) return false;
		$section_level = $this->_get_section_level($section);

		return (int)$level <= (int)$section_level;
	}
}