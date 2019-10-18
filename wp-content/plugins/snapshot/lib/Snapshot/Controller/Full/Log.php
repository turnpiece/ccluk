<?php // phpcs:ignore

class Snapshot_Controller_Full_Log extends Snapshot_Controller_Full {

	/**
	 * Implicitly enabled logging flag
	 *
	 * Used to determine if the logging has been
	 * explicitly disabled by the user.
	 *
	 * If it wasn't, we will assume that the logging is
	 * implicitly enabled.
	 *
	 * @since v3.0.2-beta-1
	 */
	const LOG_IMPLICIT = null;

	private static $_instance;

	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function run () {
		$this->dispatch_logging();
	}

	/**
	 * Checks to see whether the logging is enabled in any way
	 *
	 * Checks both implicit and explicit enabling
	 *
	 * @since v3.0.2-beta-1
	 *
	 * @return bool
	 */
	public function is_enabled () {
		return (bool)apply_filters(
			'snapshot_full_backups_log_enabled',
			$this->is_explicitly_enabled() || $this->is_implicitly_enabled()
		);
	}


	/**
	 * Checks to see whether the logging has been *explicitly* enabled by the user
	 *
	 * @uses config['full_log_enable']
	 * @since v3.0.2-beta-1
	 *
	 * @return bool
	 */
	public function is_explicitly_enabled () {
		return (bool)apply_filters(
			'snapshot_full_backups_log_enabled_explicit',
			(bool)$this->_model->get_config('full_log_enable', false)
		);
	}

	/**
	 * Checks to see whether the logging has been *implicitly* enabled
	 *
	 * Logging is implicitly enabled when there has been no user action
	 *
	 * @uses config['full_log_enable']
	 * @since v3.0.2-beta-1
	 *
	 * @return bool
	 */
	public function is_implicitly_enabled () {
		$enabled = $this->_model->get_config('full_log_enable', self::LOG_IMPLICIT);
		return (bool)apply_filters(
			'snapshot_full_backups_log_enabled_implicit',
			self::LOG_IMPLICIT === $enabled // Check if we're implicitly enabled
		);
	}

	/**
	 * Dispatches logging according to settings
	 *
	 * Either stored, and/or defaults
	 *
	 * @return bool
	 */
	public function dispatch_logging () {
		$updated = false;

		$explicit = $this->is_explicitly_enabled();
		$implicit = !$explicit && $this->is_implicitly_enabled();

		if (!(bool)$explicit && !$implicit) return $updated; // Logging explicitly disabled, continue

		$updated = $implicit
			? $this->_spawn_implicit_log_config()
			: $this->_spawn_saved_log_config()
		;

		return (bool)$updated;
	}

	/**
	 * Spawns the logs from saved config.
	 *
	 * Used when the log setup has been explicitly enabled in settings.
	 *
	 * @uses config['full_log_setup']
	 * @since v3.0.2-beta-1
	 *
	 * @return bool
	 */
	protected function _spawn_saved_log_config () {
		$updated = false;

		$log_setup = $this->_model->get_config('full_log_setup', array());
		$log = Snapshot_Helper_Log::get();
		$known_levels = $log->get_known_levels();
		$known_sections = $log->get_known_sections();

		foreach ($log_setup as $section => $level) {
			$level = (int)$level;
			if (empty($level)) continue; // No logging for this section, carry on
			if (!in_array($section, array_keys($known_sections), true)) continue; // Unknown section
			if (!in_array($level, array_keys($known_levels), true)) continue; // Unknown level

			$const = $log->get_section_constant_name($section);
			if (defined($const)) continue; // Already defined, let's not error

			define($const, $level);
			$updated = true;
		}
	}


	/**
	 * Spawns the logs from implicit (default) config.
	 *
	 * Used when the log setup has *NOT* been explicitly enabled in settings.
	 *
	 * @since v3.0.2-beta-1
	 *
	 * @return bool
	 */
	protected function _spawn_implicit_log_config () {
		$updated = false;

		$log = Snapshot_Helper_Log::get();
		$known_sections = $log->get_known_sections();

		foreach ($known_sections as $section => $name) {
			$const = $log->get_section_constant_name($section);
			if (defined($const)) continue; // Already defined, let's not error

			$level = Snapshot_Helper_Log::LEVEL_DEFAULT;

			define($const, $level);
			$updated = true;
		}
	}

	/**
	 * Actually stores the submitted log setup
	 *
	 * @uses config['full_log_setup']
	 * @uses config['full_log_enable']
	 *
	 * @param Snapshot_Model_Post $data Submitted data
	 *
	 * @return bool
	 */
	public function process_submissions ( Snapshot_Model_Post $data) {
		if (
			!current_user_can(Snapshot_View_Full_Backup::get()->get_page_role())
			||
			!$data->is_valid_action('snapshot-full_backups-log_setup')
		) return false;

		// Enable/disable logging
		if ($data->has('log-enable')) {
			$this->_model->set_config('full_log_enable', $data->is_true('log-enable'));
			if (!$data->is_true('log-enable')) {
				// No logging. Null out everything and short out
				$this->_model->set_config('full_log_setup', array());
				return true;
			}
		}

		// Log levels processing
		if (!$data->has('log_level')) return false; // No sensible data to process

		$known_levels = Snapshot_Helper_Log::get()->get_known_levels();
		$known_sections = Snapshot_Helper_Log::get()->get_known_sections();
		$submitted = $data->value('log_level');

		if (!empty($submitted) && is_array($submitted)) {
			$log_setup = $this->_model->get_config('full_log_setup', array());
			foreach ($submitted as $section => $level) {
				if (!in_array($section, array_keys($known_sections), true)) continue;

				// Set up default loggins
				$log_setup[$section] = Snapshot_Helper_Log::LEVEL_DEFAULT;

				$level = (int)$level;
				if ($level && !in_array($level, array_keys($known_levels), true)) continue;
				$log_setup[$section] = $level;

			}
			$this->_model->set_config('full_log_setup', $log_setup);
		}

		return true;
	}
}