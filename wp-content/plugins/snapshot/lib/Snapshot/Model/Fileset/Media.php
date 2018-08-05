<?php // phpcs:ignore

class Snapshot_Model_Fileset_Media extends Snapshot_Model_Fileset {

	private $_blog_id = 0;

	public function get_base () {
		return Snapshot_Helper_Utility::get_blog_upload_path($this->get_blog_id());
	}

	public function get_files ($chunk = false) {
		$path = $this->get_root();
		if (empty($path)) return array();

		$files = Snapshot_Helper_Utility::scandir($path);
		$exclusion_path = WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull');
		$exclusion_rx = preg_quote($exclusion_path, '/');

		foreach ($files as $idx =>$file) {
			if (preg_match("/^{$exclusion_rx}/", $file)) unset($files[$idx]);
		}

		return $this->_process_file_list(array_filter(array_values($files)), $chunk);
	}

	/**
	 * Returns internal blog ID flag
	 *
	 * @return int Blog ID
	 */
	public function get_blog_id () {
		return (int)$this->_blog_id;
	}

	/**
	 * Sets internal blog ID flag
	 *
	 * @param mixed (int)Blog ID on success, (bool)false on failure
	 */
	public function set_blog_id ($blog_id) {
		if (!is_numeric($blog_id)) return false;
		$this->_blog_id = (int)$blog_id;
		return $this->_blog_id;
	}
}