<?php
/**
 * Shipper codec: paths replacer
 *
 * @package shipper
 */

/**
 * Paths class
 */
class Shipper_Helper_Codec_Paths extends Shipper_Helper_Codec_Domain {

	/**
	 * Gets a list of replacement pairs
	 *
	 * A single replacement pair list, with current domain as key and
	 * replacement macro as value.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		$rpl = array(
			trailingslashit( WP_CONTENT_DIR ) => '{{SHIPPER_CONTENT_DIR}}',
			trailingslashit( ABSPATH )        => '{{SHIPPER_ABSPATH}}',
		);

		return $rpl;
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will match the whole path whenever possible.
	 *
	 * @param string $string Original path.
	 * @param string $value Macro on import, empty on export.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: preg_quote( $string, '/' );
		return $value;
	}
}