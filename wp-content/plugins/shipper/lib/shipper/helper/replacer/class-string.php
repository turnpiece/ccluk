<?php
/**
 * Shipper helpers: string replacer
 *
 * Handles low level string replacement transformations.
 *
 * @package shipper
 */

/**
 * String replacer class
 */
class Shipper_Helper_Replacer_String extends Shipper_Helper_Replacer {

	/**
	 * Applies migration transformations to a string
	 *
	 * @param string $source Source string to process.
	 *
	 * @return string
	 */
	public function transform( $source ) {
		if ( ! is_string( $source ) ) {
			// Can't deal with this, pass through.
			return $source;
		}
		$xforms = $this->get_codec_list();

		foreach ( $xforms as $codec ) {
			$source = $codec->transform( $source, $this->get_direction() );
		}

		return $source;
	}
}