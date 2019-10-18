<?php // phpcs:ignore

class Snapshot_Model_Fileset_Config extends Snapshot_Model_Fileset {

	public function get_base () {
		return '';
	}

	public function get_files ($chunk = false) {
		$path = $this->get_root();
		if (empty($path)) return array();

		$config = trailingslashit($path) . "wp-config.php";

		return $this->_process_file_list(
			(file_exists($config) ? array($config) : array()),
			$chunk
		);
	}
}