<?php // phpcs:ignore

/**
 * Full backup error handling model
 */
class Snapshot_Model_Full_Error extends Snapshot_Model_Full {

	const ERRORS_OPTION_KEY = 'snapshot-full_backup-errors';

	const ERROR_GENERAL = 'backup';
	const ERROR_POSTPROCESS = 'postprocess';
	const ERROR_UPLOAD = 'upload';

	const PART_UNKNOWN = 'unknown';

	/**
	 * Singleton instance
	 *
	 * @var object Snapshot_Model_Full_Error
	 */
	private static $_instance;

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type () {
		return 'remote';
	}

	/**
	 * Constructor - never to the outside world.
	 */
	private function __construct () {
		add_site_option(self::ERRORS_OPTION_KEY, '');
	}

	/**
	 * No public clones
	 */
	private function __clone () {}

	/**
	 * Gets the singleton instance
	 *
	 * @return Snapshot_Model_Full_Error
	 */
	public static function get () {
		if (empty(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Clears all backup errors
	 *
	 * @return bool
	 */
	public function clear () {
		return delete_site_option(self::ERRORS_OPTION_KEY);
	}

	/**
	 * Gets backup errors hash
	 *
	 * @return array
	 */
	public function get_all () {
		$errors = get_site_option(self::ERRORS_OPTION_KEY, array(), false);
		return is_array($errors)
			? $errors
			: array()
		;
	}

	/**
	 * Returns the threshold value for errors to break
	 *
	 * @return int
	 */
	public function get_threshold () {
		return 3;
	}

	/**
	 * Gets the error key (if any) of the errors that exceed threshold
	 *
	 * @uses $this->get_all()
	 * @uses $this->get_threshold()
	 *
	 * @return string|false Error key, or (bool)false
	 */
	public function get_offending () {
		$errors = $this->get_all();
		$threshold = $this->get_threshold();

		foreach ($errors as $key => $value) {
			if ($value >= $threshold) return $key;
		}

		return false;
	}

	/**
	 * Adds a backup error to total count
	 *
	 * @uses $this->get_all()
	 *
	 * @param string $error_key Error key to increment
	 *
	 * @return bool
	 */
	public function add ($error_key) {
		$errors = $this->get_all();

		$value = isset($errors[$error_key])
			? (int)$errors[$error_key]
			: -1
		;
		$errors[$error_key] = $value + 1;

		return update_site_option(self::ERRORS_OPTION_KEY, $errors);
	}

	/**
	 * Decrements a backup error count for a key
	 *
	 * @uses $this->get_all()
	 *
	 * @param string $error_key Error key to decrement
	 *
	 * @return bool
	 */
	public function remove ($error_key) {
		$errors = $this->get_all();

		$value = isset($errors[$error_key])
			? (int)$errors[$error_key]
			: 0
		;
		$errors[$error_key] = $value - 1;
		if ($errors[$error_key] < 0) unset($errors[$error_key]); // Really no need to keep this one around

		return update_site_option(self::ERRORS_OPTION_KEY, $errors);
	}

	/**
	 * Gets the current error key from backup
	 *
	 * @param object $object Optional Snapshot_Helper_Backup instance
	 *
	 * @return string Current error key
	 */
	public function get_current_error_key ($object= false) {
		if (empty($object) || !($object instanceof Snapshot_Helper_Backup)) return self::ERROR_GENERAL;

		$error_key = array();
		$queue = $object->get_current_queue();
		if ($queue && $queue instanceof Snapshot_Model_Queue_Tableset) {
			$error_key[] = $queue->get_type();
			$source = $queue->get_current_source();
			if (is_array($source) && isset($source['chunk']))
				$error_key[] = $source['chunk'];
			else
				$error_key[] = self::PART_UNKNOWN;
		} else
			$error_key = array(self::PART_UNKNOWN, self::PART_UNKNOWN);
		$error_key = join(':', $error_key);

		return $error_key;
	}

	/**
	 * Converts error key into a human-friendly representation string
	 *
	 * @param string $error_key Error key
	 *
	 * @return string
	 */
	public static function get_human_description ($error_key) {
		$fallback = __('Other kind of backup error', SNAPSHOT_I18N_DOMAIN);

		if (empty($error_key) || !is_string($error_key)) return $fallback;

		if (self::ERROR_GENERAL === $error_key) return __('General backup error', SNAPSHOT_I18N_DOMAIN);
		if (self::ERROR_POSTPROCESS === $error_key) return __('Backup post-processing error', SNAPSHOT_I18N_DOMAIN);
		if (self::ERROR_UPLOAD === $error_key) return __('Backup upload error', SNAPSHOT_I18N_DOMAIN);

		if (false === strpos($error_key, ':')) return $fallback;

		// Okay, so we might have queue:source error format, let's try that
		$err = explode(':', $error_key);
		$source = false;
		$queue = $source;

		if (empty($err[0]) || self::PART_UNKNOWN === $err[0]) return $fallback; // Unknown queue, let's just fall back
		else
			$queue = $err[0];

		if (empty($err[1]) || self::PART_UNKNOWN === $err[1])
			$source = __('unknown source', SNAPSHOT_I18N_DOMAIN);
		else
			$source = $err[1];

		return sprintf(
			__('Error with %1$s queue, source: %2$s', SNAPSHOT_I18N_DOMAIN),
			$queue,
			$source
		);
	}
}