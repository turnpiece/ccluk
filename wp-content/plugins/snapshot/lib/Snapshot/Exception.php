<?php // phpcs:ignore

/**
 * General snapshot exception
 */
class Snapshot_Exception extends Exception {

	/**
	 * Gets the current error key
	 *
	 * @uses Exception::getMessage()
	 *
	 * @return string
	 */
	public function get_error_key () {
		return $this->getMessage();
	}
}