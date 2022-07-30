<?php
/**
 * Shipper tasks: import, config files copier
 *
 * This task will iterate over previously stowed config files and copy them
 * over to their proper place. It has to run *after* the regular files copying
 * task (which is what stowes the config files in the first place).
 *
 * @package shipper
 */

/**
 * Config files copying class
 */
class Shipper_Task_Import_Config extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return $this->is_config_deployment_prevented()
			? __( 'Skipping the config files deployment step', 'shipper' )
			: __( 'Move config files into place', 'shipper' );
	}

	/**
	 * Storage object getter
	 *
	 * @return object Shipper_Model_Stored_Filelist instance
	 */
	public function get_fs_storage() {
		return $this->get_fs_lister()->get_storage();
	}

	/**
	 * Files lister object getter
	 *
	 * @return object Shipper_Helper_Fs_List instance
	 */
	public function get_fs_lister() {
		if ( ! isset( $this->files ) ) {
			$storage     = new Shipper_Model_Stored_Filelist();
			$this->files = new Shipper_Helper_Fs_List( $storage );
		}

		return $this->files;
	}

	/**
	 * Check if we are to deploy config files at all
	 *
	 * By default, that's not something we'll be doing on WP Engine.
	 *
	 * @return bool
	 */
	public function is_config_deployment_prevented() {
		return (bool) apply_filters(
			'shipper_import_is_config_deployment_prevented',
			Shipper_Model_Env::is_auth_requiring_env()
		);
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
		if ( $this->is_config_deployment_prevented() ) {
			Shipper_Helper_Log::write(
				__( 'Notice: protective environment detected, skipping config files', 'shipper' )
			);

			return true; // We're done.
		}
		$temp = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() );
		$root = $temp . 'files/';
		if ( ! file_exists( $root ) || ! is_dir( $root ) ) {
			$this->add_error(
				self::ERR_ACCESS,
				/* translators: %s: file path. */
				sprintf( __( 'Temporary files directory doesn\'t seem to exist: %s', 'shipper' ), $root )
			);

			return true;
		}
		$config_files = $this->get_fs_storage()->get(
			Shipper_Model_Stored_Filelist::KEY_CONFIG_FILES,
			array()
		);

		$replacement = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) . trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_FS );
		$root_rx     = preg_quote( trailingslashit( ABSPATH ), '/' );
		$replacer    = new Shipper_Helper_Replacer_File( Shipper_Helper_Codec::DECODE );
		$replacer->add_codec( new Shipper_Helper_Codec_Rewrite() );
		$replacer->add_codec( new Shipper_Helper_Codec_Paths() );

		if ( ! is_multisite() ) {
			// Remove Multisite defined constants for sub-site => single site migration.
			$replacer->add_codec( new Shipper_Helper_Codec_MsDefine() );
		}

		foreach ( $config_files as $destination ) {
			$file = preg_replace( "/^{$root_rx}/", $replacement, $destination );

			if ( ! $this->is_config_file_deployable( $file ) ) {
				continue; // Do not deploy this file.
			}

			// Apply transformations to the file.
			$tmp_file = $replacer->transform( $file );

			if ( Shipper_Helper_Fs_Path::is_wp_config( $destination ) && $this->get_manifest()->get( 'is_wp_config_skipped' ) ) {
				Shipper_Helper_Log::write( __( 'Skipping wp-config deployment in import', 'shipper' ) );

				continue;
			}

			/**
			 * Whether we're in import mocking mode, defaults to false.
			 *
			 * In files import mocking mode, none of the files will be
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
				if ( ! copy( $tmp_file, $destination ) ) {
					$is_file_exists = file_exists( $tmp_file );

					Shipper_Helper_Log::write(
						sprintf(
							/* translators: %1$s %2$s %3$s: source and dest path file not exists. */
							__( 'Unable to copy staged config file %1$s to %2$s %3$s', 'shipper' ),
							$file,
							$destination,
							$is_file_exists ? null : __( 'File not exists', 'shipper' )
						)
					);
					// Actually, keep plowing - we're committed now.
					// return true; // Break, has error.
				}
			}

			if ( ! shipper_delete_file( $tmp_file ) ) {
				$this->add_error(
					self::ERR_ACCESS,
					sprintf(
						/* translators: %s: file name. */
						__( 'Error removing staged file %s', 'shipper' ),
						$file
					)
				);
				// Do not break, cleanup will hopefully catch this.
			}
		}

		// Now that we're done with this, also retrogress options.
		$options = new Shipper_Model_Stored_Options();
		$options->retrogress_data();

		return true;
	}

	/**
	 * Whether this file is to be deployed
	 *
	 * @param string $filepath Absolute path to the file.
	 *
	 * @return bool
	 */
	public function is_config_file_deployable( $filepath ) {
		if ( ! is_readable( $filepath ) ) {
			return false;
		}

		if ( ! Shipper_Helper_Fs_Path::is_config_file( $filepath ) ) {
			return false;
		}

		/**
		 * Whether the file is deployable
		 *
		 * @param bool $deployable Whether the file is deployable.
		 * @param string $basename Basename of the file.
		 * @param string $path Full path to the file.
		 *
		 * @return bool
		 */
		return (bool) apply_filters(
			'shipper_import_is_config_file_deployable',
			true,
			basename( $filepath ),
			$filepath
		);
	}
}