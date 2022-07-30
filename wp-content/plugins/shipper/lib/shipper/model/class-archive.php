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

	/**
	 * Archive path
	 *
	 * @var $archive_path
	 */
	private $archive_path;

	/**
	 * Static implementation getter
	 *
	 * @param string $archive_path Full path to the archive.
	 *
	 * @return object Shipper_Model_Archive implementation object
	 */
	public static function get( $archive_path ) {
		// detect if we supper phar.
		return new Shipper_Model_Archive_Zip( $archive_path );
	}

	/**
	 * Add file to archive
	 *
	 * @param string $source_fullpath source path.
	 * @param string $destination destination path.
	 *
	 * @return mixed
	 */
	abstract public function add_file( $source_fullpath, $destination );

	/**
	 * Open the archive
	 */
	public function open() {}

	/**
	 * Close the archive
	 */
	public function close() {}

	/**
	 * Constructor
	 *
	 * @param string $archive_path Full path to the archive.
	 */
	public function __construct( $archive_path ) {
		$this->archive_path = wp_normalize_path( $archive_path );
	}

	/**
	 * Get path
	 *
	 * @return mixed
	 */
	public function get_path() {
		return $this->archive_path;
	}
}