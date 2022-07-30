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
	public static function get_root_path() {
		return trailingslashit( Shipper_Helper_Fs_Path::get_log_dir() ) . self::DIRECTORY;
	}

	/**
	 * Returns full path to package root directory
	 *
	 * Creates the path if necessary.
	 *
	 * @return string
	 */
	public static function get_root() {
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
	public static function has_package() {
		$files = self::get_packages();
		return ! empty( $files );
	}

	/**
	 * Gets a package path
	 *
	 * @return string|bool Absolute path to a package zip, or (bool)false on failure
	 */
	public static function get_package() {
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
	public static function get_packages() {
		$files = shipper_glob_all( self::get_root() );
		$zips  = array();
		foreach ( $files as $file ) {
			if ( ! preg_match( '/\.zip$/', $file ) ) {
				continue;
			}
			$zips[] = $file;
		}
		return $zips;
	}
}