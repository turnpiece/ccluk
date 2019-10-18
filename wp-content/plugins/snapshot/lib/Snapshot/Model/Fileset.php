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
	 * @param bool    $format Optional format parameter - (bool)true for find, (bool)false for zip.
	 *
	 * @return array
	 */
	public static function get_excluded_paths ( $format = false ) {
		$config = WPMUDEVSnapshot::instance()->config_data['config'];

		if ( ! isset( $config['managedBackupExclusions'] ) || "global" === $config['managedBackupExclusions'] ) {
			$current_exclusions = 'filesIgnore';
		} else {
			$current_exclusions = 'filesManagedIgnore';
		}

		$exclusion = !empty($config[ $current_exclusions ]) ? $config[ $current_exclusions ] : array();
		$exclusion = !empty($exclusion) && is_array($exclusion)
			? array_values(array_unique(array_filter(array_map('trim', $exclusion))))
			: array()
		;

		// Always include backup base folder
		$exclusion[] = WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull');

		// Check if on WPMU DEV Hosting or WPEngine, in order to exclude non-editable files.
		$is_wpmu_hosting = Snapshot_Helper_Utility::is_wpmu_hosting();
		$is_wpengine_hosting = Snapshot_Helper_Utility::is_wpengine_hosting();

		// WPMU DEV Hosting specific!
		if ( $is_wpmu_hosting ) {
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting/misc-functions.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting/statsd.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting/wp-cli.php';
		}

		// WPEngine specific!
		if ( $is_wpengine_hosting ) {
			$exclusion[] = trailingslashit( ABSPATH ) . '_wpeprivate';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mysql.sql';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'advanced-cache.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/force-strong-passwords';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpengine-common';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/mu-plugin.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/slt-force-strong-passwords.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/stop-long-comments.php';
			$exclusion[] = trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpengine-security-auditor.php';
		}

		$exclusion[] = trailingslashit( ABSPATH ) . '.well-known/acme-challenge';

		if ( $format ) {
			// Check for unreadable files
			$unreadable_files = self::get_unreadable_files();

			//Add warning to log for excluded unreadable files
			if ( ! empty( $unreadable_files ) ) {
				$uf_string = implode( ', ', $unreadable_files );
				Snapshot_Helper_Log::warn( "Excluding unreadable files: {$uf_string}" );
			}

			//Check if any of the unreadable files not in there already
			$files_to_add = array_diff( $unreadable_files, $exclusion );
			if ( ! empty( $files_to_add ) ) {
				$exclusion = array_merge( $exclusion, $files_to_add );
			}
		}

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

	/**
	 * Get a list of unreadable
	 *
	 * @return array List of unreadable paths or empty array
	 */
	public static function get_unreadable_files() {
		$unreadable_files = array();

		//Get the Root Directory to check for unreadable files
		$base_path = apply_filters('snapshot_home_path', get_home_path());
		$root = trailingslashit(wp_normalize_path($base_path));

		if ( empty( $root ) ) {
			return $unreadable_files;
		}
		//Check if find command is available
		$find_path = Snapshot_Helper_System::get_command( 'find' );

		$command = "cd {$root} && {$find_path} . ! -perm -g+r -print";
		// phpcs:ignore
		$status = exec($command, $output, $status);

		//If only status has a return value, possible error
		if ( ! empty( $status ) && empty( $output ) ) {
			$msg = join( "\n", $output );
			Snapshot_Helper_Log::error( "Error running find command to check unreadable files: [{$msg}]" );

			return $unreadable_files;
		}

		//Process $output to get valid paths
		if( !empty( $output ) && is_array( $output ) ){
			foreach ( $output as $p ) {
				if( empty( $p ) ) {
					continue;
				}
				$path = realpath( path_join( $root, $p ) );
				if( file_exists( $path ) ) {
					$unreadable_files[] = $path;
				}
			}
		}

		//if both status and output are empty, no files found exit
		return $unreadable_files;

	}


}