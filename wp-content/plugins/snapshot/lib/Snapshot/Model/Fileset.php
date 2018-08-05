<?php // phpcs:ignore

/**
 * Fileset source class abstraction
 */
abstract class Snapshot_Model_Fileset {

	/**
	 * Gets base path
	 *
	 * @return string
	 */
	abstract public function get_base ();

	/**
	 * Gets a list of all files
	 *
	 * @return array
	 */
	abstract public function get_files ();

	/**
	 * List all known sources
	 *
	 * @return array List of known sources
	 */
	public static function sources () {
		return array(
			'full',
			'media',
			'themes',
			'plugins',
			'mu-plugins',
			'config',
			'htaccess',
			'null',
		);
	}

	/**
	 * Check if a given key is actually a source
	 *
	 * @param string $src Source key to check
	 *
	 * @return bool
	 */
	public static function is_source ($src) {
		$sources = self::sources();
		return in_array( $src, $sources, true );
	}

	/**
	 * Spawn a new sources instance
	 *
	 * @param string $src Source key
	 *
	 * @return mixed (Snapshot_Model_Fileset)instance on success, (bool)false on failure
	 */
	public static function get_source ($src) {
		if (!self::is_source($src)) return false;

		$class = self::to_class_name($src);
		if (empty($class)) return false;
		if (!class_exists($class)) return false;

		return new $class();
	}

	/**
	 * Map source key to class name
	 *
	 * @param string $src Source key
	 *
	 * @return mixed (string)Class name on success, (bool)false on failure
	 */
	public static function to_class_name ($src) {
		if (!self::is_source($src)) return false;

		return 'Snapshot_Model_Fileset_' . ucfirst(strtolower(preg_replace('/[^a-z0-9]/', '', $src)));
	}

	/**
	 * Return resolved root path
	 *
	 * @return string resolved root path
	 */
	public function get_root () {
		$base_path = $this->_get_site_root();
		return untrailingslashit(
            wp_normalize_path(
				$base_path . $this->get_base()
			)
        );
	}

	/**
	 * Return resolved site root path
	 *
	 * @return string resolved site root path
	 */
	public function get_site_root () {
		$base_path = $this->_get_site_root();
		return untrailingslashit(
            wp_normalize_path(
				$base_path
			)
		);
	}

	/**
	 * Gets total items count
	 *
	 * @return int
	 */
	public function get_items_count () {
		return count($this->get_files());
	}

	/**
	 * Fetches the list of excluded paths
	 *
	 * @return array
	 */
	public static function get_excluded_paths () {
		$config = WPMUDEVSnapshot::instance()->config_data['config'];
		$exclusion = !empty($config['filesIgnore']) ? $config['filesIgnore'] : array();
		$exclusion = !empty($exclusion) && is_array($exclusion)
			? array_values(array_unique(array_filter(array_map('trim', $exclusion))))
			: array()
		;

		// Always include backup base folder
		$exclusion[] = WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull');

		return $exclusion;
	}

	/**
	 * Post-process sources file list.
	 *
	 * Use mainly to apply file exclusion list.
	 *
	 * @param array $files Files to post-process
	 *
	 * @return array Processed files
	 */
	protected function _process_file_list ($files) {
		if (empty($files)) return array();

		$exclusion = self::get_excluded_paths();
		foreach ($files as $idx => $file) {
			foreach ($exclusion as $excl) {
				if (!stristr($file, $excl)) continue;
				unset($files[$idx]);
			}
		}

		$list = array_values(array_filter($files));
		asort($list);
		return $list;
	}

	/**
	 * Gets snapshot home root path
	 *
	 * @return string
	 */
	protected function _get_site_root () {
		$base_path = apply_filters('snapshot_home_path', get_home_path());
		return trailingslashit(wp_normalize_path($base_path));
	}


}