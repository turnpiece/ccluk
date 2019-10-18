<?php // phpcs:ignore

class Snapshot_Model_Fileset_Htaccess extends Snapshot_Model_Fileset {

	public function get_base () {
		return '';
	}

	public function get_files ($chunk = false) {
		$path = $this->get_root();
		if (empty($path)) return array();

		$files = array();
		$tests = array(
			'.htaccess',
			'web.config',
		);

		foreach ($tests as $test) {
			$file = trailingslashit($path) . basename($test);
			if (file_exists($file))
				$files[] = $file;
		}

		return $this->_process_file_list($files, $chunk);
	}
}