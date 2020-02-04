<?php
/**
 * Breadth-first filesystem lister helper
 *
 * @package shipper
 */

/**
 * Breadth-first filesystem lister model helper
 */
class Shipper_Helper_Fs_List {

	/**
	 * Root directory to start the crawl from
	 *
	 * Defaults to ABSPATH
	 *
	 * @var string
	 */
	private $_root;

	/**
	 * Whether to process exclusions or not
	 *
	 * Defaults to true (is exclude-capable).
	 *
	 * @var bool
	 */
	private $_excludable = true;

	/**
	 * Internal data storage, used for flags and such
	 *
	 * @var Shipper_Model_Storage
	 */
	private $_storage;

	/**
	 * Files in current run storage
	 *
	 * @var array
	 */
	private $_files = array();

	/**
	 * Constructor.
	 *
	 * @param object $storage Shipper_Model_Stored instance.
	 */
	public function __construct( Shipper_Model_Stored $storage ) {
		$this->_storage = $storage;
		$this->_storage->load();
	}

	/**
	 * Makes the current iteration exclude-capable or not
	 *
	 * @param bool $excludable Whether we're to be exclude-capable or not.
	 *
	 * @return Shipper_Helper_Fs_List
	 */
	public function set_excludable( $excludable ) {
		$this->_excludable = ! ! $excludable;

		return $this;
	}

	/**
	 * Checks whether we're exclude-capable
	 *
	 * @return bool
	 */
	public function is_excludable() {
		return ! ! $this->_excludable;
	}

	/**
	 * Sets root path
	 *
	 * @param string $path Absolute path to use as root path.
	 *
	 * @return Shipper_Helper_Fs_List
	 */
	public function set_root( $path ) {
		$this->_root = wp_normalize_path( $path );

		return $this;
	}

	/**
	 * Gets root path
	 *
	 * @return string
	 */
	public function get_root() {
		$root = ABSPATH;

		if ( Shipper_Model_Env::is_flywheel() ) {
			// Flywheel has separate dirs.
			$root = WP_CONTENT_DIR;
		}

		return ! empty( $this->_root )
			? (string) $this->_root
			: $root;
	}

	/**
	 * Resets the lister state to initial
	 *
	 * Preserves total step estimation, though.
	 *
	 * @return bool
	 */
	public function reset() {
		$total = $this->get_total_steps();
		$this->_storage->clear();
		$this->_storage->set( Shipper_Model_Stored_Filelist::KEY_TOTAL, $total );

		return $this->_storage->save();
	}

	/**
	 * Whether we're done here
	 *
	 * @return bool
	 */
	public function is_done() {
		return ! ! $this->_storage->get( Shipper_Model_Stored_Filelist::KEY_DONE, false );
	}

	/**
	 * Gets current step
	 *
	 * @return int
	 */
	public function get_current_step() {
		return (int) $this->_storage->get( Shipper_Model_Stored_Filelist::KEY_STEP, 1 );
	}

	/**
	 * Gets total steps
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return (int) $this->_storage->get( Shipper_Model_Stored_Filelist::KEY_TOTAL, 1 );
	}

	/**
	 * Gets files gathered this far
	 *
	 * Or, loads the next batch
	 *
	 * @return array
	 */
	public function get_files() {
		if ( ! empty( $this->_files ) ) {
			return $this->_files;
		}

		return $this->process_files();
	}

	/**
	 * Gets paths limitation
	 *
	 * @return int
	 */
	public function get_paths_limit() {
		$limit = $this->is_excludable()
			? 50
			: 250;

		/**
		 * Max number of paths to be processed per step
		 *
		 * Number of files processed is directly dependent on this.
		 *
		 * @param int $paths_count Number of paths to process.
		 * @param bool $is_excludable Whether we're excluding files (false means preflight).
		 *
		 * @return int
		 */
		return (int) apply_filters(
			'shipper_path_process_paths_limit',
			$limit,
			$this->is_excludable()
		);
	}

	/**
	 * Gets chunk size limitation, in bytes
	 *
	 * @return int Size limitation, in bytes (zero means no limit)
	 */
	public function get_bytes_limit() {
		$limit = $this->is_excludable()
			? 2 * 1024 * 1024
			: 0;

		/**
		 * Max number of bytes to process per step
		 *
		 * @param int $bytes Number of bytes to process.
		 * @param bool $is_excludable Whether we're excluding files (false means preflight).
		 *
		 * @return int Number of bytes, zero means no limit
		 */
		return (int) apply_filters(
			'shipper_path_process_bytes_limit',
			$limit,
			$this->is_excludable()
		);
	}

	/**
	 * Processes a list of files
	 *
	 * @return array
	 */
	public function process_files() {
		if ( $this->is_done() ) {
			return $this->_files;
		}
		$processed   = 0;
		$limit       = $this->get_paths_limit();
		$limit_files = $limit * 6;
		$limit_bytes = $this->get_bytes_limit();

		$paths = $this->_storage->get(
			Shipper_Model_Stored_Filelist::KEY_PATHS, array( $this->get_root() )
		);

		$exclusions = false;
		if ( $this->is_excludable() ) {
			$exclusions = new Shipper_Model_Fs_Blacklist;
			// Log dir is likely publicly accessible.
			$exclusions->add_directory(
				Shipper_Helper_Fs_Path::get_log_dir()
			);
		}

		while ( ! empty( $paths ) ) {
			$path = array_pop( $paths );
			$processed ++;

			$contents = shipper_glob_all( $path );
			foreach ( $contents as $item ) {

				/**
				 * Whether to actually include this item in list processing
				 *
				 * @param bool $do_process_item Include item.
				 * @param string $item Item path.
				 *
				 * @return bool
				 */
				$do_process_item = apply_filters(
					'shipper_path_include_file',
					true,
					$item
				);

				if ( ! $do_process_item ) {
					continue;
				}

				if ( is_object( $exclusions ) && $exclusions->is_excluded( $item ) ) {
					Shipper_Helper_Log::write(
						sprintf(
							__( 'Skipping excluded item: %s', 'shipper' ),
							$item
						)
					);
					continue;
				}

				if ( is_file( $item ) && ! is_link( $item ) ) {
					if ( ! is_readable( $item ) ) {
						// We will have issues with this!
						if ( $this->is_excludable() ) {
							// Only log this if we're also excluding files.
							// This is so we stay log-silent during preflight check.
							Shipper_Helper_Log::write(
								sprintf(
									__( 'Skipping unreadable file: %s', 'shipper' ),
									$item
								)
							);
						}
						// Log or not, don't process this file.
						continue;
					}

					$size = filesize( $item );

					$this->_files[] = array(
						'path' => $item,
						'size' => $size,
					);
				} elseif ( is_dir( $item ) && ! is_link( $item ) ) {
					if ( ! in_array( $item, $paths, true ) ) {
						$paths[] = $item;
					}
				}
			}
			$this->_storage->set( Shipper_Model_Stored_Filelist::KEY_PATHS, $paths );

			if ( count( $this->_files ) >= $limit_files ) {
				break;
			}

			if ( $processed >= $limit ) {
				break;
			}

			if ( ! empty( $limit_bytes ) ) {
				$total_bytes = array_sum( wp_list_pluck( $this->_files, 'size' ) );
				if ( $total_bytes > $limit_bytes ) {
					break;
				}
			}
		}

		$paths = $this->_storage->get( Shipper_Model_Stored_Filelist::KEY_PATHS );
		if ( empty( $paths ) ) {
			// So we are done. Say so.
			$this->_storage->set( Shipper_Model_Stored_Filelist::KEY_DONE, true );
		}
		$step = $this->_storage->get( Shipper_Model_Stored_Filelist::KEY_STEP, 0 );
		$step ++;
		$this->_storage->set( Shipper_Model_Stored_Filelist::KEY_STEP, $step );

		$total = $this->get_total_steps();
		if ( $total <= $step ) {
			$total ++;
			$this->_storage->set( Shipper_Model_Stored_Filelist::KEY_TOTAL, $total );
		}

		$this->_storage->save();

		if ( empty( $this->_files ) ) {
			// So we could be now iterating through the exclusions...
			// If we're coming up short, add a passthrough file.
			$this->_files[] = 'passthrough';
		}

		return $this->_files;
	}

	/**
	 * Gets the primed storage object
	 *
	 * @return object Shipper_Model_Stored_Filelist instance
	 */
	public function get_storage() {
		return $this->_storage;
	}
}