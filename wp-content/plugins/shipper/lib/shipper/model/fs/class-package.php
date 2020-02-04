<?php
/**
 * Shipper filesystem models: package model
 *
 * Deals with shipper package paths and item representation.
 *
 * @since v1.1
 * @package shipper
 */


/**
 * Shipper filesystem package model class
 */
class Shipper_Model_Fs_Package {

	const DIRECTORY = 'packages';

	/**
	 * Returns the root path as string
	 *
	 * No side-effects.
	 *
	 * @return string
	 */
	static public function get_root_path() {
		$fallback = trailingslashit( Shipper_Helper_Fs_Path::get_log_dir() ) .
			self::DIRECTORY;
		$model = new Shipper_Model_Stored_Options;
		return $model->get(
			Shipper_Model_Stored_Options::KEY_PACKAGE_LOCATION,
			$fallback
		);
	}

	/**
	 * Returns full path to package root directory
	 *
	 * Creates the path if necessary.
	 *
	 * @return string
	 */
	static public function get_root() {
		$path = self::get_root_path();

		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
			if ( shipper_is_dir_visible( $path ) ) {
				// Our logs directory is web-accessible.
				// Let's attempt some protection with .htaccess here.
				Shipper_Helper_Fs_Path::attempt_htaccess_protect( $path );
			}
		}

		return trailingslashit( $path );
	}

	/**
	 * Whether or not we have a package
	 *
	 * @return bool
	 */
	static public function has_package() {
		$files = self::get_packages();
		return ! empty( $files );
	}

	/**
	 * Gets a package path
	 *
	 * @return string|bool Absolute path to a package zip, or (bool)false on failure
	 */
	static public function get_package() {
		$files = self::get_packages();
		return ! empty( $files )
			? reset( $files )
			: false;
	}

	/**
	 * Gets a list of package files in the packages root
	 *
	 * @return array
	 */
	static public function get_packages() {
		$files = shipper_glob_all( self::get_root() );
		$zips = array();
		foreach ( $files as $file ) {
			if ( ! preg_match( '/\.zip$/', $file ) ) {
				continue;
			}
			$zips[] = $file;
		}
		return $zips;
	}
}