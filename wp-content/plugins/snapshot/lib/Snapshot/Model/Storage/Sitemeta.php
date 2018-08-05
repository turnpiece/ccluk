<?php // phpcs:ignore
/**
 * Storage implementation in WP site options
 *
 * @package snapshot
 */

/**
 * Site options storage implementation class
 */
class Snapshot_Model_Storage_Sitemeta extends Snapshot_Model_Storage {

	public function load() {
		$str = get_site_option( $this->get_namespace() );
		$this->_data = (array) $this->decode( $str );

		return ! empty( $this->_data );
	}

	public function save() {
		$str = $this->encode( $this->_data );
		return !!update_site_option( $this->get_namespace(), $str );
	}
}