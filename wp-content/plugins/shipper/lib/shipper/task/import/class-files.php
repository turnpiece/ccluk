<?php
/**
 * Shipper tasks: import, non-config files copier
 *
 * This task iterates over all extracted files and classifies them to
 * config/non-config ones. The non-config files will be copied to their
 * respective new locations, while the config ones will be recorded for
 * processing in subsequent steps.
 *
 * @package shipper
 */

/**
 * Files copying class
 */
class Shipper_Task_Import_Files extends Shipper_Task_Import {

	/**
	 * Storage holder.
	 *
	 * @var $storage
	 */
	protected $storage;

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
				/* translators: %1$d %2$s: position and total files.*/
				__( '( %1$d of %2$s total )', 'shipper' ),
				$position,
				$total
			);
		}

		return sprintf(
			/* translators: %s: file name.*/
			__( 'Download and place files %s', 'shipper' ),
			$current
		);
	}

	/**
	 * Gets total files to process in this step
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 * @return int
	 */
	public function get_total_files( $migration = false ) {
		if ( empty( $migration ) ) {
			$migration = new Shipper_Model_Stored_Migration();
		}

		return $migration->get( 'total-manifest-files' );
	}

	/**
	 * Adds file to appropriate queue
	 *
	 * @param string $path File path to queue.
	 * @param string $queue Queue to add the path to.
	 * @param object $storage Optional storage instance to use.
	 *
	 * @return bool
	 */
	public function add_file_to_queue( $path, $queue, $storage = false ) {
		$added = false;
		if ( empty( $storage ) ) {
			$storage = new Shipper_Model_Stored_Filelist();
		}
		$files = $storage->get( $queue, array() );
		if ( ! in_array( $path, $files, true ) ) {
			$files[] = $path;
			$storage->set( $queue, $files );
			$added = $storage->save();
		}

		return $added;
	}

	/**
	 * Adds config file to storage
	 *
	 * @param string $path Absolute path to config file.
	 * @param object $storage Optional storage instance to use.
	 *
	 * @return bool Whether the file got added
	 */
	public function add_config_file( $path, $storage = false ) {
		Shipper_Helper_Log::write( 'add config ' . $path );

		return $this->add_file_to_queue(
			$path,
			Shipper_Model_Stored_Filelist::KEY_CONFIG_FILES,
			$storage
		);
	}

	/**
	 * Adds a file to active files queue
	 *
	 * @param string $path Path of the file to add.
	 * @param object $storage Optional storage instance to use.
	 *
	 * @return bool Whether the file got added
	 */
	public function add_active_file( $path, $storage = false ) {
		return $this->add_file_to_queue(
			$path,
			Shipper_Model_Stored_Filelist::KEY_ACTIVE_FILES,
			$storage
		);
	}

	/**
	 * Gets file entries to process
	 *
	 * @param int    $pos Position from where to start.
	 * @param object $dumped Optional dumped list model instance to use.
	 *
	 * @return array
	 */
	public function get_file_statements( $pos, $dumped = false ) {
		if ( empty( $dumped ) ) {
			$dumped = new Shipper_Model_Dumped_Filelist();
		}

		$max_statements = (int) apply_filters(
			'shipper_import_max_upload_statements',
			// fallback.
			100
		);

		return $dumped->get_statements( $pos, $max_statements );
	}

	/**
	 * Gets migration destination domain
	 *
	 * @param object $migration Optional migration model object.
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
	 * Gets the filelist position, initializing if necessary
	 *
	 * @param object $filelist Optional filelist model instance to use.
	 *
	 * @return int
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
	 * Gets file download command ready to be queued
	 *
	 * @param string $relpath Relative path to the file to download.
	 * @param object $remote Optional remote helper to use.
	 *
	 * @return array
	 */
	public function get_download_file_command( $relpath, $remote = false ) {
		if ( empty( $remote ) ) {
			$remote = new Shipper_Helper_Fs_Remote();
		}
		$domain = $this->get_destination_domain();

		$source      = trailingslashit( Shipper_Helper_Fs_Path::clean_fname( $domain ) ) . $relpath;
		$destination = $this->get_individual_destination( $relpath );

		return $remote->get_download_command( $source, $destination );
	}

	/**
	 * Gets local full destination path to a file
	 *
	 * Will create dirs along the way, if needed.
	 *
	 * @param string $relpath Relative path.
	 *
	 * @return string
	 */
	public function get_individual_destination( $relpath ) {
		$download_root = Shipper_Helper_Fs_Path::get_temp_dir();
		if ( pathinfo( $relpath, PATHINFO_EXTENSION ) !== 'sql' ) {
			$download_root = Shipper_Helper_Fs_Path::get_temp_dir();
		}
		$destination = trailingslashit(
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
		$pos           = $this->get_initialized_position();
		$shipper_pos   = $pos;
		$this->storage = new Shipper_Model_Stored_Filelist();
		$statements    = $this->get_file_statements( $pos );
		$batch         = array();
		$sources       = array();
		$queue_size    = 0;
		foreach ( $statements as $data ) {
			if ( empty( $data ) ) {
				continue;
			}
			if ( empty( $data['destination'] ) ) {
				continue;
			}
			/**
			 * If sql files we download from s3, but if it other files we'll do nothing.
			 */
			if ( pathinfo( $data['destination'], PATHINFO_EXTENSION ) === 'sql' ) {
				$cmd = $this->get_download_file_command( $data['destination'] );
				if ( $cmd ) {
					$batch[]                         = $cmd;
					$sources[ $data['destination'] ] = $this->get_individual_destination(
						$data['destination']
					);
					$queue_size                     += $data['size'];
				}
			} else {
				if ( pathinfo( $data['source'], PATHINFO_BASENAME ) !== pathinfo( $data['destination'], PATHINFO_BASENAME ) ) {
					// ths is config files.
					$dest                            = trailingslashit( pathinfo( $data['destination'], PATHINFO_DIRNAME ) ) . pathinfo( $data['source'], PATHINFO_BASENAME );
					$sources[ $data['destination'] ] = $this->get_individual_destination( $dest );
				} else {
					$sources[ $data['destination'] ] = $this->get_individual_destination( $data['destination'] );
				}
				$queue_size += $data['size'];
			}
		}

		if ( ! empty( $batch ) ) {
			$remote = new Shipper_Helper_Fs_Remote();
			$status = $remote->execute_batch_queue( $batch );
			Shipper_Helper_Log::debug(
				sprintf(
					'About to download queue with %d files, %s size',
					count( $batch ),
					size_format( $queue_size )
				)
			);
			$batch = null; // Give it our best shot in memory cleanup.
		}

		Shipper_Helper_Log::debug( 'Download done' );

		foreach ( $sources as $relpath => $source ) {
			if ( ! $this->is_to_be_moved( $relpath, $source ) ) {
				// SQLs and config files remain in place.
				continue;
			}
			$this->deploy_file( $source, $relpath );
		}
		Shipper_Helper_Log::debug( 'Batch deployed' );

		$is_done     = empty( $statements );
		$shipper_pos = $is_done
			? 0
			: $shipper_pos + count( $statements );

		if ( ! $this->set_initialized_position( $shipper_pos ) ) {
			// List got re-initialized - cancel.
			return true;
		}

		return $is_done;
	}

	/**
	 * Deploy files
	 *
	 * @param string $source source path.
	 * @param string $dest_relpath destination path.
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

		$time = microtime( true );
		if ( Shipper_Helper_Fs_Path::is_config_file( $destination ) ) {
			// We will move those, just not now.
			$this->add_config_file( $destination );

			return false;
		}

		$time = microtime( true );
		if ( Shipper_Helper_Fs_Path::is_active_file( $destination ) ) {
			// We will move those, just not now.
			$this->add_active_file( $destination, $this->storage );

			return false;
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
			// @TODO: tighten up.
			$time     = microtime( true );
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

			$time = microtime( true );
			if ( ! copy( $source, $destination ) ) {
				Shipper_Helper_Log::write(
					sprintf(
						/* translators: %1$s %2$s: dest and source path. */
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
	 * Checks whether the file is to be moved to its final destination
	 *
	 * @param string $relpath Relative file path.
	 * @param string $abspath The old absolute path to the file.
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
	 * Checks whether the file is an internal SQL file
	 *
	 * @param string $relpath Relative path to the file.
	 *
	 * @return bool
	 */
	public function is_sql_file( $relpath ) {
		$sql_rx = preg_quote(
			trailingslashit( Shipper_Model_Stored_Migration::COMPONENT_DB ),
			'/'
		);

		return preg_match( "/^{$sql_rx}/", $relpath );
	}

	/**
	 * Sets the position, shorting out if neccessary
	 *
	 * @param int    $position Position to set.
	 * @param object $filelist Optional filelist model instance to use.
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

	/**
	 * Get memory limit.
	 *
	 * @return float|int|string
	 */
	private function get_memory_limit() {
		$migration    = new Shipper_Model_Stored_Migration();
		$memory_limit = $migration->get( 'memory_limit' );
		// convert all to M.
		if ( preg_match( '/^(\d+)(.)$/', $memory_limit, $matches ) ) {
			if ( strtoupper( $matches[2] ) === 'M' ) {
				$memory_limit = $matches[1];
			} elseif ( strtoupper( $matches[2] ) === 'K' ) {
				$memory_limit = $matches[1] / 1024; // nnnK -> nnn KB.
			} elseif ( strtoupper( $matches[2] ) === 'G' ) {
				$memory_limit = $matches[1] * 1024;
			}
		}

		return $memory_limit;
	}

	/**
	 * Get total steps.
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return $this->get_total_files();
	}

	/**
	 * Get current step.
	 *
	 * @return int
	 */
	public function get_current_step() {
		return $this->get_initialized_position();
	}

	/**
	 * Cleanup data, move file cursor to it's initial position
	 *
	 * @since 1.1.4
	 *
	 * @return void
	 */
	public function cleanup() {
		$file_list = new Shipper_Model_Stored_Filelist();
		$file_list->cleanup();
	}
}