<?php
/**
 * Shipper stored models: stored package meta representation
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Shipper stored package meta data class
 */
class Shipper_Model_Stored_Package extends Shipper_Model_Stored {

	const KEY_NAME = 'package_name';
	const KEY_DATE = 'package_date';
	const KEY_CREATED = 'package_created';
	const KEY_PWD = 'package_password';
	const KEY_EXCLUSIONS_FS = 'fs_exclusions';
	const KEY_EXCLUSIONS_DB = 'db_exclusions';
	const KEY_EXCLUSIONS_XX = 'xx_exclusions';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		// Store the model in the database.
		parent::__construct( 'package', true );
	}

	/**
	 * Whether or not we have a package
	 *
	 * Proxies `Shipper_Model_Fs_Package`.
	 *
	 * @return bool
	 */
	public function has_package() {
		return Shipper_Model_Fs_Package::has_package();
	}

	/**
	 * Gets the package file path.
	 *
	 * Proxies `Shipper_Model_Fs_Package`.
	 *
	 * @return string|bool
	 */
	public function get_package() {
		return Shipper_Model_Fs_Package::get_package();
	}

	public function get_package_path() {
		if ( $this->has_package() ) {
			return $this->get_package();
		}

		static $hasher;
		if ( empty( $hasher ) ) {
			$hasher = new Shipper_Helper_Hash;
		}

		$name = sanitize_file_name(
			$hasher->get_concealed( $this->get( self::KEY_NAME ) )
		);
		return trailingslashit(
			Shipper_Model_Fs_Package::get_root()
		) . $name . '.zip';
	}

	/**
	 * Returns the package size, in bytes
	 *
	 * @return int
	 */
	public function get_size() {
		if ( $this->has_package() ) {
			return filesize( $this->get_package() );
		}
		return 0;
	}

	/**
	 * Extends the clear method to also clean up the package
	 *
	 * @return object Shipper_Model instance
	 */
	public function clear() {
		if ( $this->has_package() ) {
			unlink( $this->get_package() );
		}
		return parent::clear();
	}
}