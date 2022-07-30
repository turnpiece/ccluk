<?php
/**
 * Shipper codec: MS tables domain replacer
 *
 * @package shipper
 */

/**
 * Rewrite replacer class
 */
class Shipper_Helper_Codec_Msdomain extends Shipper_Helper_Codec_Domain {

	/**
	 * Gets a list of replacement pairs
	 *
	 * A single replacement pair list, with current domain as key and
	 * replacement macro as value.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		$current = trailingslashit(
			'http://' . Shipper_Model_Stored_Destinations::get_current_domain()
		);
		// @TODO: generalize this (ports and stuff).
		$path = wp_parse_url( $current, PHP_URL_HOST );
		return array(
			$path => '{{SHIPPER_MS_DOMAIN}}',
		);
	}
}