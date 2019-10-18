<?php // phpcs:ignore
/**
 * Breadth-first filesystem lister model
 *
 * @package snapshot
 */

/**
 * Breadth-first filesystem lister model class
 */
class Snapshot_Model_Bflister {

	const KEY_DONE = 'is_done';
	const KEY_PATHS = 'paths';
	const KEY_STEP = 'current_step';
	const KEY_TOTAL = 'total_steps';

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
	 * @var Snapshot_Model_Storage
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
	 * @param object $storage Snapshot_Model_Storage instance.
	 */
	public function __construct( Snapshot_Model_Storage $storage ) {
		$this->_storage = $storage;
		$this->_storage->load();
	}

	/**
	 * Makes the current iteration exclude-capable or not
	 *
	 * @param bool $excludable Whether we're to be exclude-capable or not.
	 *
	 * @return Snapshot_Model_Bflister
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
	 * @return Snapshot_Model_Bflister
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
		return ! empty( $this->_root )
			? (string) $this->_root
			: ABSPATH
		;
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
		$this->_storage->set_value( self::KEY_TOTAL, $total );
		return $this->_storage->save();
	}

	/**
	 * Whether we're done here
	 *
	 * @return bool
	 */
	public function is_done() {
		return ! ! $this->_storage->get_value( self::KEY_DONE, false );
	}

	/**
	 * Gets current step
	 *
	 * @return int
	 */
	public function get_current_step() {
		return (int) $this->_storage->get_value( self::KEY_STEP, 1 );
	}

	/**
	 * Gets total steps
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return (int) $this->_storage->get_value( self::KEY_TOTAL, 1 );
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
		$limit = defined( 'SNAPSHOT_FILESET_CHUNK_SIZE' ) && is_numeric( SNAPSHOT_FILESET_CHUNK_SIZE )
			? intval( SNAPSHOT_FILESET_CHUNK_SIZE )
			: 250
		;
		return (int) apply_filters( 'snapshot_model_bflister_paths_limit', $limit );
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

		$processed = 0;
		$limit = $this->get_paths_limit();
		$limit_files = $limit * 6;
		$file_size_threshold = (float) Snapshot_Model_Queue_Bhfileset::get_size_threshold();
		$exclusions = $this->is_excludable()
			? Snapshot_Model_Fileset::get_excluded_paths()
			: array()
		;
		$paths = $this->_storage->get_value( self::KEY_PATHS, array( $this->get_root() ) );
		while ( ! empty( $paths ) ) {
			$path = array_pop( $paths );
			$processed++;

			$contents = glob( trailingslashit( $path ) . '{,.}[!.,!..]*', GLOB_BRACE );
			foreach ( $contents as $item ) {

				if ($this->is_excludable()) {
					$include_this_file = true;
					foreach ( $exclusions as $excl ) {
						if ( stristr( $item, $excl ) ) {
							$include_this_file = false;
							break;
						}
					}
					if ( ! $include_this_file ) {
						continue;
					}
				}

				if ( is_file( $item ) && ! is_link( $item ) ) {
					if ( ! is_readable( $item ) ) {
						// We will have issues with this!
						// @TODO log as error...
						// ...
						continue;
					}

					$size = filesize( $item );
					if ( $this->is_excludable() && $size > $file_size_threshold ) {
						continue;
					}

					//Snapshot_Helper_Log::info("Adding file to queue: {$item}");
					$this->_files[] = $item;
				} elseif ( is_dir( $item ) && ! is_link( $item ) ) {
					if ( ! in_array( $item, $paths, true ) ) {
						$paths[] = $item;
					}
				}
			}
			$this->_storage->set_value( self::KEY_PATHS, $paths );

			if ( count( $this->_files ) >= $limit_files ) {
				break;
			}

			if ( $processed >= $limit ) {
				break;
			}
		}

		$paths = $this->_storage->get_value( self::KEY_PATHS );
		if ( empty( $paths ) ) {
			// So we are done. Say so.
			$this->_storage->set_value( self::KEY_DONE, true );
		}

		$step = $this->_storage->get_value( self::KEY_STEP, 0 );
		$step++;
		$this->_storage->set_value( self::KEY_STEP, $step );

		$total = $this->get_total_steps();
		if ( $total <= $step ) {
			$total++;
			$this->_storage->set_value( self::KEY_TOTAL, $total );
		}

		$this->_storage->save();

		if ( empty( $this->_files ) ) {
			// So we could be now iterating through the exclusions...
			// If we're coming up short, add a passthrough file.
			$this->_files[] = 'passthrough';
		}

		return $this->_files;
	}
}