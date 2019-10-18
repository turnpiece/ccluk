<?php // phpcs:ignore

class Snapshot_Model_Fileset_Full extends Snapshot_Model_Fileset {

	public function get_base () {
		return '';
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
}