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

	const KEY_NAME        = 'package_name';
	const KEY_DATE        = 'package_date';
	const KEY_CREATED     = 'package_created';
	const KEY_PWD         = 'package_password';
	const KEY_PACKAGE_ZIP = '.shipper.zip';

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

	/**
	 * Get shipper package name
	 *
	 * @since 1.2.5
	 *
	 * @return string
	 */
	public function get_package_name() {
		$name = $this->get( self::KEY_NAME );

		if ( empty( $name ) ) {
			$name = 'package-' . gmdate( 'YmdHis' );
		}

		return sanitize_file_name( $name . self::KEY_PACKAGE_ZIP );
	}

	/**
	 * Get package path
	 *
	 * @return bool|string
	 */
	public function get_package_path() {
		if ( $this->has_package() ) {
			return $this->get_package();
		}

		return trailingslashit( Shipper_Model_Fs_Package::get_root() ) . $this->get_package_name();
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

	/**
	 * Get file size of installer.php
	 *
	 * @since 1.2.5
	 *
	 * @return string|false on failure.
	 */
	public function get_installer_size() {
		$installer_path = plugin_dir_path( SHIPPER_PLUGIN_FILE ) . 'lib/installer/installer.php';

		if ( ! is_readable( $installer_path ) ) {
			return size_format( 0 );
		}

		return size_format( filesize( $installer_path ) );
	}
}