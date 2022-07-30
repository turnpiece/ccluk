<?php
/**
 * Shipper tasks: import, non-config files copier
 *
 * @package shipper
 */

/**
 * Files copying class
 */
class Shipper_Task_Import_Large extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		$total    = $this->get_total_files();
		$position = $this->get_initialized_position();
		$current  = '';
		if ( ! empty( $position ) ) {
			$current = sprintf(
				/* translators: %1$d %2$s: pointer position and total files.*/
				__( '( %1$d of %2$s total )', 'shipper' ),
				$position,
				$total
			);
		}
		return sprintf(
			/* translators: %s: current file name.*/
			__( 'Download and place large files %s', 'shipper' ),
			$current
		);
	}

	/**
	 * Get total files.
	 *
	 * @param false $migration instance of Shipper_Model_Stored_Migration.
	 *
	 * @return false|mixed
	 */
	public function get_total_files( $migration = false ) {
		if ( empty( $migration ) ) {
			$migration = new Shipper_Model_Stored_Migration();
		}
		return $migration->get( 'total-manifest-large-files' );
	}

	/**
	 * Get file statements
	 *
	 * @param int   $pos pointer position in a file.
	 * @param false $dumped dumped file lists.
	 *
	 * @return array
	 */
	public function get_file_statements( $pos, $dumped = false ) {
		if ( empty( $dumped ) ) {
			$dumped = new Shipper_Model_Dumped_Largelist();
		}
		return $dumped->get_statements( $pos, 1 );
	}

	/**
	 * Get destination domain name
	 *
	 * @param false $migration instance of Shipper_Model_Stored_Migration.
	 *
	 * @return string
	 */
	public function get_destination_domain( $migration = false ) {
		if ( empty( $migration ) ) {
			$migration = new Shipper_Model_Stored_Migration();
		}
		return $migration->get_source();
	}

	/**
	 * Get initialized position
	 *
	 * @param false $filelist Shipper_Model_Stored_Filelist instance.
	 *
	 * @return false|int|mixed
	 */
	public function get_initialized_position( $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$pos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		if ( false === $pos ) {
			$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, 0 );
			$filelist->save();
			$pos = 0;
		}

		return $pos;
	}

	/**
	 * Get individual destination
	 *
	 * @param string $relpath real path.
	 *
	 * @return string
	 */
	public function get_individual_destination( $relpath ) {
		$download_root = Shipper_Helper_Fs_Path::get_temp_dir();
		$destination   = trailingslashit(
			$download_root
		) . $relpath;
		if ( ! file_exists( dirname( $destination ) ) ) {
			wp_mkdir_p( dirname( $destination ) );
		}
		return $destination;
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
		$pos         = $this->get_initialized_position();
		$shipper_pos = $pos;

		$migration  = new Shipper_Model_Stored_Migration();
		$domain     = $migration->get_source();
		$s3_dirname = Shipper_Helper_Fs_Path::clean_fname( $domain );

		$statements = $this->get_file_statements( $pos );
		if ( empty( $statements ) ) {
			return true;
		}

		$data        = $statements[0];
		$source      = trailingslashit( $s3_dirname ) . $data['destination'];
		$destination = $this->get_individual_destination( $data['destination'] );

		$remote   = new Shipper_Helper_Fs_Remote();
		$progress = $remote->download( $source, $destination );

		if ( $progress->is_done() ) {
			$this->deploy_file( $destination, $data['destination'] );
			$shipper_pos ++;
			if ( ! $this->set_initialized_position( $shipper_pos ) ) {
				// List got re-initialized - cancel.
				return true;
			}
		}

		// We are not done until we deplete large files!
		return false;
	}

	/**
	 * Deploy file
	 *
	 * @param string $source source file path.
	 * @param string $dest_relpath destination real path.
	 *
	 * @return bool
	 */
	public function deploy_file( $source, $dest_relpath ) {
		$destination = trailingslashit( ABSPATH ) . preg_replace(
			'/^' . preg_quote(
				trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_FS ),
				'/'
			) . '/',
			'',
			$dest_relpath
		);

		/**
		 * Whether we're in import mocking mode, defaults to false.
		 *
		 * In files import mocking mode, none of the files will be
		 * Actually copied over to their final destination.
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
						/* translators: %1$s %2$s: destination and souce path.*/
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
	 * Is to be moved
	 *
	 * @param string $relpath real file path.
	 * @param string $abspath absolute file path.
	 *
	 * @return bool
	 */
	public function is_to_be_moved( $relpath, $abspath ) {
		if ( $this->is_sql_file( $relpath ) ) {
			// Classify files: just download SQL files.
			return false;
		}

		return true;
	}

	/**
	 * Is SQL file
	 *
	 * @param string $relpath file path to check.
	 *
	 * @return false|int
	 */
	public function is_sql_file( $relpath ) {
		$sql_rx = preg_quote(
			trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_DB ),
			'/'
		);
		return preg_match( "/^{$sql_rx}/", $relpath );
	}

	/**
	 * Set initialized position
	 *
	 * @param int   $position pointer position in a file.
	 * @param false $filelist list of files.
	 *
	 * @return bool
	 */
	public function set_initialized_position( $position, $filelist = false ) {
		if ( empty( $filelist ) ) {
			$filelist = new Shipper_Model_Stored_Filelist();
		}

		$newpos = $filelist->get( Shipper_Model_Stored_Filelist::KEY_CURSOR, false );
		if ( false === $newpos ) {
			return false;
		}

		$filelist->set( Shipper_Model_Stored_Filelist::KEY_CURSOR, $position );
		$filelist->save();

		return true;
	}
}