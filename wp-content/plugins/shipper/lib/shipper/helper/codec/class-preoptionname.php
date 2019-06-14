<?php
/**
 * Shipper codec: options tables option name table prefix replacer
 *
 * @package shipper
 */

/**
 * Option name replacer class
 */
class Shipper_Helper_Codec_Preoptionname extends Shipper_Helper_Codec_Domain {

	/**
	 * Gets a list of replacement pairs
	 *
	 * @uses $wpdb global
	 *
	 * A single replacement pair list, with current domain as key and
	 * replacement macro as value.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		global $wpdb;

		return array(
			$wpdb->base_prefix => '{{SHIPPER_TABLE_PREFIX}}',
		);
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will match only the root blog path expression.
	 *
	 * @param string $string Original table prefix.
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
