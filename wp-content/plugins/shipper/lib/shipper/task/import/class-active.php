<?php
/**
 * Shipper tasks: import, active files copier
 *
 * This moves over active content, defined as plugins and mu-plugins.
 * The reasoning behind it is that moving a chunk of active content needs to be
 * done as an atomic action. Each plugin needs to be moved completely within a
 * request, in case it gets triggered in the next one.
 *
 * @package shipper
 */

/**
 * Active content files copying class
 */
class Shipper_Task_Import_Active extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		$current = ! empty( $this->current_section )
			? $this->current_section
			: '';

		return sprintf(
			/* translators: %s: active content name. */
			__( 'Moving active content: %s', 'shipper' ),
			$current
		);
	}

	/**
	 * Storage object getter
	 *
	 * @return object Shipper_Model_Stored_Filelist instance.
	 */
	public function get_fs_storage() {
		if ( ! isset( $this->storage ) ) {
			$this->storage = new Shipper_Model_Stored_Filelist();
		}

		return $this->storage;
	}

	/**
	 * Get done sections
	 *
	 * @return false|mixed
	 */
	public function get_done_sections() {
		return $this
			->get_fs_storage()
			->get( Shipper_Model_Stored_Filelist::KEY_ACTIVE_DONE, array() );
	}

	/**
	 * Update done sections
	 *
	 * @param string $sections section name.
	 *
	 * @return bool
	 */
	public function update_done_sections( $sections ) {
		return $this
			->get_fs_storage()
			->set( Shipper_Model_Stored_Filelist::KEY_ACTIVE_DONE, $sections )
			->save();
	}

	/**
	 * Gets paths classified into sections
	 *
	 * @param object $storage Optional Shipper_Model_Stored_Filelist instance (used in tests).
	 *
	 * @return array
	 */
	public function get_active_sections( $storage = false ) {
		if ( empty( $storage ) ) {
			$storage = $this->get_fs_storage();
		}
		$active_paths = $storage->get( Shipper_Model_Stored_Filelist::KEY_ACTIVE_FILES, array() );
		$sections     = array();

		foreach ( $active_paths as $path ) {
			$key = $this->get_active_section( $path );
			if ( empty( $key ) ) {
				continue;
			}

			if ( empty( $sections[ $key ] ) ) {
				$sections[ $key ] = array();
			}
			$sections[ $key ][] = $path;
		}

		return $sections;
	}

	/**
	 * Classifies path as one atomic section member
	 *
	 * Either a catch-all muplugin|theme, or a section name based on plugin name.
	 *
	 * @param string $path Absolute path to a file.
	 *
	 * @return string
	 */
	public function get_active_section( $path ) {
		if ( Shipper_Helper_Fs_Path::is_muplugin_file( $path ) ) {
			return 'mu-plugins';
		}
		if ( Shipper_Helper_Fs_Path::is_theme_file( $path ) ) {
			$theme_rx = preg_quote( trailingslashit( WP_CONTENT_DIR ) . 'themes/', '/' );
			$relpath  = preg_replace( "/^{$theme_rx}/", '', $path );
			$dirs     = array_filter( explode( '/', wp_normalize_path( $relpath ) ) );

			return empty( $dirs[0] ) || basename( $path ) === $dirs[0]
				? 'themes'
				: $dirs[0];
		}
		$plugin_rx = '/^' . preg_quote( trailingslashit( WP_PLUGIN_DIR ), '/' ) . '/';

		if ( ! preg_match( $plugin_rx, $path ) ) {
			// Not a plugin either.
			return '';
		}

		$relpath = preg_replace( $plugin_rx, '', $path );
		$dirs    = array_filter( explode( '/', wp_normalize_path( $relpath ) ) );

		return empty( $dirs[0] ) || basename( $path ) === $dirs[0]
			? 'plugins'
			: $dirs[0];
	}

	/**
	 * Deploy section
	 *
	 * @param string $section section name.
	 * @param string $paths file path.
	 *
	 * @return bool
	 */
	public function deploy_section( $section, $paths ) {
		$this->current_section = $section;

		if ( 'shipper' === $section ) {
			Shipper_Helper_Log::write( 'Not deploying self' );

			return false;
		}

		foreach ( $paths as $path ) {
			$this->deploy_file( $path );
		}

		return true;
	}

	/**
	 * Deploy file
	 *
	 * @param string $destination destination file path.
	 *
	 * @return bool
	 */
	public function deploy_file( $destination ) {
		if ( $this->is_hosted_object_cache_file( $destination ) ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %s: skipping file name. */
					__( 'Hosted object cache helper, skipping [%s]', 'shipper' ),
					$destination
				)
			);

			// Do not overwrite hosted helper.
			return false;
		}

		$replacement = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) . trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_FS );
		$root_rx     = preg_quote( trailingslashit( ABSPATH ), '/' );
		$source      = preg_replace( "/^{$root_rx}/", $replacement, $destination );

		if ( ! file_exists( $source ) ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %s: missing file name. */
					__( 'WARNING: unable to deploy the missing file: %s', 'shipper' ),
					$source
				)
			);

			return false;
		}

		/**
		 * Whether we're in import mocking mode, defaults to false.
		 *
		 * In files import mocking mode, none of the files will be.
		 * actually copied over to their final destination.
		 *
		 * @param bool $is_mock_import Whether we're in mock import mode.
		 *
		 * @return bool
		 */
		$is_mock_import = apply_filters(
			'shipper_import_mock_files',
			false
		);
		if ( ! $is_mock_import ) {
			// @TODO: tighten up.
			$destpath = dirname( $destination );
			if ( ! is_dir( $destpath ) ) {
				wp_mkdir_p( $destpath );
				if ( ! is_dir( $destpath ) ) {
					$this->add_error(
						self::ERR_ACCESS,
						'Unable to create directory'
					);
				}
			}

			if ( ! copy( $source, $destination ) ) {
				Shipper_Helper_Log::write(
					sprintf(
						/* translators: %1$s %2$s: source and destination path. */
						__( 'WARNING: unable to copy staged file %1$s to %2$s', 'shipper' ),
						$source,
						$destination
					)
				);
			}
		}

		return shipper_delete_file( $source );
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {

		$active_sections = $this->get_active_sections();
		$done_sections   = $this->get_done_sections();
		$done            = count( $done_sections );
		foreach ( $active_sections as $section => $paths ) {
			if ( in_array( $section, $done_sections, true ) ) {
				continue;
			}
			$this->deploy_section( $section, $paths );
			$done_sections[] = $section;
			break;
		}

		if ( count( $done_sections ) > $done ) {
			$this->update_done_sections( $done_sections );

			return false;
		}

		return count( $done_sections ) === count( $active_sections );
	}

	/**
	 * Check whether we're dealing with WPMU DEV hosting and object caching.
	 *
	 * @param string $file File about to be deployed.
	 *
	 * @return bool
	 */
	public function is_hosted_object_cache_file( $file ) {
		if ( ! Shipper_Model_Env::is_wpmu_hosting() ) {
			return false;
		}

		return (bool) preg_match(
			'/' . preg_quote( trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php', '/' ) . '/',
			$file
		);
	}
}