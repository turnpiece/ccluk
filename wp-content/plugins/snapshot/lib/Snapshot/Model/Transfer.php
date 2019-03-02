<?php // phpcs:ignore
/**
 * Snapshot models: multipart transfer model
 *
 * @package snapshot
 */

/**
 * Multipart transfer class
 */
abstract class Snapshot_Model_Transfer {

	/**
	 * Bootstraps the transfer
	 *
	 * @param string $transfer_id Transfer ID to use.
	 */
	abstract public function initialize( $transfer_id );

	const TYPE_UPLOAD = 'upload';
	const TYPE_DOWNLOAD = 'download';

	const KEY_TRANSFER_ID = 'transfer_id';
	const KEY_PARTS = 'parts';

	/**
	 * Storage reference
	 *
	 * @var object Snapshot_Model_Storage instance
	 */
	private $_storage;

	/**
	 * Transfer ID
	 *
	 * @var string
	 */
	private $_transfer_id;

	/**
	 * Local path
	 *
	 * @var string
	 */
	private $_local_path;

	/**
	 * Transfer parts
	 *
	 * @var array
	 */
	private $_parts = array();

	/**
	 * Constructor
	 *
	 * @param string $type Transfer type.
	 * @param string $path Local file path.
	 */
	public function __construct( $type, $path = false ) {
		$this->_type = $type;
		$storage_key = "transfer_{$type}";

		if ( ! empty( $path ) ) {
			$this->_local_path = $path;
			$filename = md5( basename( $path ) );
			$storage_key .= "_{$filename}";
		}
		$this->_storage = new Snapshot_Model_Storage_Sitemeta(
			$storage_key
		);
		$this->load();
	}

	/**
	 * Loads transfer data
	 */
	public function load() {
		$this->_storage->load();
		$this->_transfer_id = $this->_storage->get_value(
			self::KEY_TRANSFER_ID
		);
		$parts = $this->_storage->get_value(
			self::KEY_PARTS,
			array()
		);
		foreach ( $parts as $part ) {
			$this->_parts[] = Snapshot_Model_Transfer_Part::from_data( $part );
		}
	}

	/**
	 * Saves transfer data
	 */
	public function save() {
		$this->_storage->set_value(
			self::KEY_TRANSFER_ID,
			$this->get_transfer_id()
		);

		$parts = array();
		foreach ( $this->_parts as $part ) {
			$parts[] = $part->get_data();
		}

		$this->_storage->set_value(
			self::KEY_PARTS,
			$parts
		);
		$this->_storage->save();
	}

	/**
	 * Gets path
	 *
	 * @return string
	 */
	public function get_path() {
		return (string) $this->_local_path;
	}

	/**
	 * Explicitly sets parts
	 *
	 * Used in tests.
	 *
	 * @param array $parts Parts.
	 */
	public function set_parts( $parts ) {
		$this->_parts = $parts;
	}

	/**
	 * Gets transfer parts
	 *
	 * @return array
	 */
	public function get_parts() {
		return (array) $this->_parts;
	}

	/**
	 * Gets the next part to transfer
	 *
	 * @return bool|object Snapshot_Model_Transfer_Part or false
	 */
	public function get_next_part() {
		foreach ( $this->get_parts() as $part ) {
			if ( ! $part->is_done() ) {
				return $part;
			}
		}
		return false;
	}

	/**
	 * Completes a part keyed under index
	 *
	 * @param int $idx Part ID to complete.
	 *
	 * @return bool
	 */
	public function complete_part( $idx ) {
		if ( ! isset( $this->_parts[ $idx ] ) ) {
			return false;
		}
		$this->_parts[ $idx ]->complete();
		return true;
	}

	/**
	 * Checks if the transfer is ready to start
	 *
	 * @return bool
	 */
	public function is_initialized() {
		$tid = $this->get_transfer_id();
		$parts = $this->get_parts();
		return ! empty( $tid ) && ! empty( $parts );
	}

	/**
	 * Checks to see if there are any completed parts
	 *
	 * @return bool
	 */
	public function has_completed_parts() {
		$parts = $this->get_parts();
		foreach ( $parts as $part ) {
			if ( $part->is_done() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if we have more unfinished parts
	 *
	 * @return bool
	 */
	public function has_next_part() {
		$next = $this->get_next_part();
		return ! empty( $next );
	}

	/**
	 * Gets transfer type
	 *
	 * @return string
	 */
	public function get_type() {
		return (string) $this->_type;
	}

	/**
	 * Sets transfer ID
	 *
	 * @param string $tid Transfer ID.
	 */
	public function set_transfer_id( $tid ) {
		$this->_transfer_id = $tid;
	}

	/**
	 * Gets transfer ID
	 *
	 * @return string
	 */
	public function get_transfer_id() {
		return $this->_transfer_id;
	}

	/**
	 * Gets a list of part objects for a given transfer size
	 *
	 * @param int $total_size Total size to break apart.
	 * @param int $increment Individual part size.
	 *
	 * @return array A list of Snapshot_Model_Transfer_Part objects
	 */
	public function get_transfer_parts( $total_size, $increment = false ) {
		$parts = array();

		$increment = $this->get_increment( $increment );

		if ( ! (int) $increment || ! (int) $total_size ) {
			return $parts;
		}

		$total_parts = ceil( (int) $total_size / (int) $increment );

		for ( $i = 0; $i < $total_parts; $i++ ) {
			$part = new Snapshot_Model_Transfer_Part( $i );
			$part->set_seek( $i * $increment );
			$part->set_length( $increment );
			$parts[] = $part;
		}

		return $parts;
	}

	/**
	 * Gets valid part size increment
	 *
	 * @param int $increment Individual part size.
	 *
	 * @return int
	 */
	public function get_increment( $increment = false ) {
		if ( ! (int) $increment ) {
			$increment = 50 * 1024 * 1024;
		}

		/**
		 * Multipart transfer part size, in bytes
		 *
		 * @param int $increment Transfer part size.
		 * @param string $type Transfer type.
		 * @param object $transfer Transfer implementation object.
		 *
		 * @return int Transfer part size, in bytes
		 */
		return (int) apply_filters(
			'snapshot_model_transfer_part_size_increment',
			$increment,
			$this->get_type(),
			$this
		);
	}

	/**
	 * Completes and deactivates a transfer
	 *
	 * @return bool
	 */
	public function complete() {
		$this->set_transfer_id( false );
		$this->set_parts( array() );
		$this->_storage->clear();
		$this->save();

		// Also try to remove all leftover traces.
		$this->_storage->remove_trace();

		return true;
	}

	/**
	 * Whether we have unfinished parts
	 *
	 * @return bool
	 */
	public function is_done() {
		foreach ( $this->get_parts() as $part ) {
			if ( ! $part->is_done() ) {
				return false;
			}
		}
		return true;
	}
}