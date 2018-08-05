<?php // phpcs:ignore

class Snapshot_Helper_Zip_Pclzip extends Snapshot_Helper_Zip_Abstract {

	public function initialize () {
		$path = dirname($this->_path);
		if (!defined('PCLZIP_TEMPORARY_DIR')) {
			define('PCLZIP_TEMPORARY_DIR', trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull')));
		}
		if (!class_exists('PclZip')) {
			require_once ABSPATH . '/wp-admin/includes/class-pclzip.php';
		}
		$this->_zip = new PclZip($this->_path);
	}

	public function add ($files = array(), $relative_path = false) {
		if (!is_array($files))
			$files = array($files);
		if (empty($files))
			return false;

		// Extend processing time.
		Snapshot_Helper_Utility::check_server_timeout();

		$this->_zip->add(
			$files,
			PCLZIP_OPT_REMOVE_PATH, $this->_get_root_path(),
			PCLZIP_OPT_ADD_PATH, $relative_path,
			PCLZIP_OPT_TEMP_FILE_THRESHOLD, 10,
			PCLZIP_OPT_ADD_TEMP_FILE_ON
		);

		return true;
	}

	public function has ($path) {
		$path = $this->_to_root_relative($path);
		if (empty($path)) return false;

		$contents = $this->_zip->listContent();
		if (empty($contents)) return false;

		foreach ($contents as $entry) {
			if (empty($entry['filename'])) continue;
			if ($path === $entry['filename']) return true;
		}

		return false;
	}

	public function extract ($destination) {
		if (empty($destination)) return false;

		$destination = wp_normalize_path($destination);
		if (empty($destination) || !file_exists($destination)) return false;

		$zip_contents = $this->_zip->listContent();
		if (empty($zip_contents)) return false;

		// Extend processing time.
		Snapshot_Helper_Utility::check_server_timeout();

		$extract_files = $this->_zip->extract(PCLZIP_OPT_PATH, $destination);

		return !empty($extract_files);
	}

	public function extract_specific ($destination, $files) {
		if (empty($destination)) return false;

		if (empty($files)) return false;
		if (!is_array($files)) return false;

		$destination = wp_normalize_path($destination);
		if (empty($destination) || !file_exists($destination)) return false;

		$zip_contents = $this->_zip->listContent();
		if (empty($zip_contents)) return false;

		// Extend processing time.
		Snapshot_Helper_Utility::check_server_timeout();

		$extract_files = $this->_zip->extract(PCLZIP_OPT_PATH, $destination, PCLZIP_OPT_BY_NAME, $files);

		return !empty($extract_files);
	}
}