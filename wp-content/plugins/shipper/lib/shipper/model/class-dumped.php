<?php
/**
 * Shipper models: dumped lists model
 *
 * Contains statements with size property.
 *
 * @package shipper
 */

/**
 * Dumped list abstraction class
 */
abstract class Shipper_Model_Dumped {

	const MODE_APPEND = 'a';
	const MODE_READ   = 'r';

	/**
	 * Implementation file name
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Stores SplFileObject instance.
	 *
	 * @var $fs
	 */
	private $fs;

	/**
	 * Current file handle access mode
	 *
	 * @var string
	 */
	private $mode;

	/**
	 * Constructor
	 *
	 * @param string $filename Implementation file name.
	 */
	public function __construct( $filename ) {
		$this->filename = $filename;
	}

	/**
	 * Appends a statement to the list
	 *
	 * @param array|mixed $statement Statement to append.
	 */
	public function add_statement( $statement ) {
		$this->acquire_fs( self::MODE_APPEND );

		$this->fs->flock( LOCK_EX );
		$this->fs->fwrite( wp_json_encode( $statement ) . "\n" );
		$this->fs->flock( LOCK_UN );
	}

	/**
	 * Gets total number of statements in the file
	 *
	 * @return int
	 */
	public function get_statements_count() {
		$this->acquire_fs( self::MODE_READ );
		$this->fs->rewind();

		$count = 0;
		while ( ! $this->fs->eof() ) {
			if ( trim( $this->fs->fgets() ) ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Gets a list of statements, limited in number and/or size
	 *
	 * The limits are applied at the same time - whichever comes first.
	 *
	 * @param int $position Position in the file where to begin.
	 * @param int $limit Optional number of statements limit - return _at most_ this much.
	 * @param int $allowed_mb Optional maximum cumulative size of the
	 * statements to return, in Mb (defaults to 50).
	 *
	 * @return array
	 */
	public function get_statements( $position, $limit = false, $allowed_mb = false ) {
		$this->acquire_fs( self::MODE_READ );
		$this->fs->rewind();

		$allowed_mb = (float) $allowed_mb;
		if ( empty( $allowed_mb ) ) {
			$allowed_mb = 50;
		}

		/**
		 * Maximum cumulative chunk size, in bytes
		 *
		 * @param int $allowed_bytesize Maximum bytes.
		 *
		 * @return int
		 * @since v1.1
		 */
		$allowed_bytesize = apply_filters(
			'shipper_dumped_statements_max_bytes',
			$allowed_mb * 1024 * 1024
		);

		/**
		 * Maximum number of dumped items to return
		 *
		 * @param int $limit Maximum number of items.
		 *
		 * @return int
		 * @since v1.1
		 */
		$limit = apply_filters(
			'shipper_dumped_statements_limit',
			$limit
		);

		$count      = 0;
		$queue_size = 0;
		$lines      = array();
		while ( ! $this->fs->eof() ) {
			$line = $this->fs->fgets();

			if ( ! trim( $line ) ) {
				continue;
			}

			$count++;

			if ( $count <= $position ) {
				continue;
			}

			if ( $limit && count( $lines ) >= $limit ) {
				break;
			}

			$stmt    = json_decode( $line, true );
			$lines[] = $stmt;

			if ( ! empty( $stmt['size'] ) ) {
				$queue_size += (int) $stmt['size'];
			}

			if ( $queue_size > $allowed_bytesize ) {
				Shipper_Helper_Log::debug( sprintf( 'Break out of loop because %sMB', round( $queue_size / 1024 / 1024 ) ) );
				break;
			}
		}

		return $this->get_size_corrected_queue( $lines, $queue_size, $allowed_bytesize );
	}

	/**
	 * Gets size-corrected queue
	 *
	 * Drops one element off of queue if overall size surpases the allowed size
	 * by more than what we deem to be acceptable (i.e. correction threshold).
	 *
	 * @param array $lines Queue to correct.
	 * @param int   $queue_size Overall queue size.
	 * @param int   $allowed_bytesize Allowed size, in bytes.
	 *
	 * @return array
	 */
	public function get_size_corrected_queue( $lines, $queue_size, $allowed_bytesize ) {
		$correction_threshold = $allowed_bytesize / 10;

		$will_correct = $queue_size > $allowed_bytesize &&
						( $queue_size - $allowed_bytesize ) > $correction_threshold;
		if ( count( $lines ) > 1 && $will_correct ) {
			Shipper_Helper_Log::debug(
				sprintf( 'Backing off one because %s', size_format( $queue_size ) )
			);
			array_pop( $lines );
		}

		return $lines;
	}

	/**
	 * Gets the file name without path
	 *
	 * @return string
	 */
	public function get_file_name() {
		return $this->filename;
	}

	/**
	 * Gets the full path to the file
	 *
	 * @return string
	 */
	public function get_file_path() {
		$manifest_root = trailingslashit( Shipper_Helper_Fs_Path::get_temp_dir() ) . Shipper_Model_Stored_Migration::COMPONENT_META;
		if ( ! file_exists( $manifest_root ) ) {
			wp_mkdir_p( $manifest_root );
		}

		return trailingslashit(
			$manifest_root
		) . $this->get_file_name();
	}

	/**
	 * Opens the file in appropriate mode
	 *
	 * @param string $mode One of the supported access modes.
	 */
	public function open( $mode ) {
		$filename = $this->get_file_path();
		if ( self::MODE_APPEND !== $mode && ! file_exists( $filename ) ) {
			$fs = Shipper_Helper_Fs_File::open( $filename, self::MODE_APPEND );

			if ( $fs ) {
				$fs->fwrite( '' );
			}
		}

		$this->fs   = Shipper_Helper_Fs_File::open( $filename, $mode );
		$this->mode = $mode;
	}

	/**
	 * Closes the file handle
	 */
	public function close() {
		$this->fs   = null;
		$this->mode = false;
	}

	/**
	 * Sets up internal file handle for the appropriate access mode
	 *
	 * @param string $mode One of the supported access modes.
	 */
	public function acquire_fs( $mode ) {
		if ( ! empty( $this->fs ) && $mode !== $this->mode ) {
			$this->close();
		}

		if ( empty( $this->fs ) ) {
			$this->open( $mode );
		}
	}
}