<?php // phpcs:ignore

class Snapshot_Model_Post extends Snapshot_Model_Request {

	public function __construct() {
		$this->_data = stripslashes_deep( $_POST ); // phpcs:ignore
	}

}