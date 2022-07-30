<?php
/**
 * Shipper codec: rewrite rule replacer
 *
 * @package shipper
 */

/**
 * Rewrite replacer class
 */
class Shipper_Helper_Codec_Rewrite extends Shipper_Helper_Codec_Domain {

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
		$path    = wp_parse_url( $current, PHP_URL_PATH );
		if ( ! empty( $path ) ) {
			$rpl[ "RewriteBase {$path}" ] = '{{SHIPPER_REWRITE_BASE}}';
		}

		return $rpl;
	}
}