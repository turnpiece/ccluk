<?php
/**
 * Shipper storage: DB implementation (alternative)
 *
 * @package shipper
 */

/**
 * Database storage implementation
 */
class Shipper_Helper_Storage_Db extends Shipper_Helper_Storage {

	/**
	 * Loads current state from storage medium
	 *
	 * @return bool
	 */
	public function load() {
		$str        = get_site_option( $this->get_namespace(), '[]', false );
		$this->data = (array) $this->decode( $str );

		return ! empty( $this->data );
	}

	/**
	 * Saves current state to implementation-specific storage medium
	 *
	 * @return bool
	 */
	public function save() {
		$str = $this->encode( $this->data );
		return ! ! update_site_option( $this->get_namespace(), $str );
	}
}