<?php
/**
 * Shipper codec: MS tables path replacer
 *
 * @package shipper
 */

/**
 * Path replacer class
 */
class Shipper_Helper_Codec_Mspath extends Shipper_Helper_Codec_Domain {

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
		return array(
			$path => '{{SHIPPER_MS_PATH}}',
		);
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will match only the root blog path expression.
	 *
	 * @param string $string Original domain.
	 * @param string $value Macro on import, empty on export.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: '^' . preg_quote( $string, '/' );
		return $value;
	}
}