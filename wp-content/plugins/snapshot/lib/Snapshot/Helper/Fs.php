<?php // phpcs:ignore

class Snapshot_Helper_Fs {

	public function __construct () {

	}

	/**
	 * Get full path to current home directory
	 *
	 * @return mixed (string)Full path to directory if it's writable, (bool)false on failure
	 */
	public function get_home_dir () {
		$path = realpath('~/');
		if (empty($path))
			$path = getenv('HOME');
		if (empty($path)) return false;

		$path = wp_normalize_path($path);
		return is_writable($path)
			? $path
			: false
		;
	}

	/**
	 * Get full path to document root directory
	 *
	 * @return mixed (string)Full path to directory if it's writable, (bool)false on failure
	 */
	public function get_webroot () {
		$path = getenv('DOCUMENT_ROOT');
		if (empty($path))
			$path = $_SERVER['DOCUMENT_ROOT'];
		if (empty($path)) return false;

		$path = wp_normalize_path($path);
		return is_writable($path)
			? $path
			: false
		;
	}

	/**
	 * Gets WP uploads directory
	 *
	 * @return string WordPress uploads directory
	 */
	public function get_uploads () {
		$wp_upload_dir = wp_upload_dir();
		return wp_normalize_path(realpath($wp_upload_dir['basedir']));
	}

	/**
	 * Determine writeable directory default location
	 *
	 * Try to set up the directory outside server root, if at all possible,
	 * and fall back to uploads if nothing else works out
	 *
	 * 1) user dir
	 * 2) one level above the server root
	 * 3) uploads
	 *
	 * @return string Writeable home directory
	 */
	public function get_snapshot_base_dir () {
		$directory = false;

		$path = $this->get_home_dir();
		if (empty($path)) {
			$path = $this->get_webroot();
			if (!empty($path)) {
				$path = realpath("{$path}/../");
			}
		}
		if (empty($path))
			$path = $this->get_uploads();

		$directory = !empty($path) && is_writable($path)
			? wp_normalize_path($path)
			: false
		;

		return $directory;
	}
}