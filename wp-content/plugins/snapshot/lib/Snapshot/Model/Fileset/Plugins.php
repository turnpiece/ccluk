<?php // phpcs:ignore

class Snapshot_Model_Fileset_Plugins extends Snapshot_Model_Fileset {

	public function get_base () {
		$abs = $this->_get_site_root();
		$content = wp_normalize_path(WP_CONTENT_DIR);

		$content = preg_replace('/^' . preg_quote($abs, '/') . '/', '', $content);
		$path = trailingslashit($content) . 'plugins';

		return $path;
	}

	public function get_files ($chunk = false) {
		$path = $this->get_root();
		if (empty($path)) return array();

		return $this->_process_file_list(Snapshot_Helper_Utility::scandir($path), $chunk);
	}
}