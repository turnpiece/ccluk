<?php
/**
 * Shipper models: archive handling factory
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package handling abstraction and factory class
 */
abstract class Shipper_Model_Archive {

	private $_archive_path;

	/**
	 * Static implementation getter
	 *
	 * @param string $archive_path Full path to the archive.
	 *
	 * @return object Shipper_Model_Archive implementation object
	 */
	static public function get( $archive_path ) {
		return new Shipper_Model_Archive_Zip( $archive_path );
	}

	abstract public function add_file( $source_fullpath, $destination );
	public function open() {}
	public function close() {}

	/**
	 * Constructor
	 *
	 * @param string $archive_path Full path to the archive.
	 */
	public function __construct( $archive_path ) {
		$this->_archive_path = wp_normalize_path( $archive_path );
	}

	public function get_path() {
		return $this->_archive_path;
	}
}