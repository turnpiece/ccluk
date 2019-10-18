<?php // phpcs:ignore

class Snapshot_Model_Fileset_Null extends Snapshot_Model_Fileset {

	public function get_base () {
		return '';
	}

	public function get_files ($chunk = false) {
		return $this->_process_file_list(array(), $chunk);
	}
}