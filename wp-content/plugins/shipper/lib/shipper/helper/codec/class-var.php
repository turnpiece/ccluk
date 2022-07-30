<?php
/**
 * Shipper codec: replace global variable values
 *
 * @package shipper
 */

/**
 * Var value replacer class
 */
class Shipper_Helper_Codec_Var extends Shipper_Helper_Codec {

	/**
	 * Gets a list of replacement pairs
	 *
	 * A replacement pair is represented like so:
	 * Global variable name as a key, variable value as replacement macro.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		$defines = array(
			'table_prefix' => '{{SHIPPER_TABLE_PREFIX}}',
		);
		return $defines;
	}

	/**
	 * Checks if the original define is present
	 *
	 * Codec implementation will not substitute with a value (and will remove
	 * the entire matcher from result) if the original is not present.
	 *
	 * @param string $original Variable name.
	 *
	 * @return bool
	 */
	public function is_original_present( $original ) {
		return isset( $GLOBALS[ $original ] );
	}

	/**
	 * Gets the define value
	 *
	 * @param string $original Variable name.
	 *
	 * @return mixed
	 */
	public function get_original_value( $original ) {
		return $GLOBALS[ $original ];
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will match an entire define expression.
	 *
	 * @param string $string Variable name.
	 * @param string $value Optional, empty on export, macro on import.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: '[-_a-zA-Z0-9]+';
		// @codingStandardsIgnoreStart
		return '(?:^|\b)\$' .
			preg_quote( $string, '/' ) .
			'\s*=\s*' .
			'(?:\'|")' .
				'(' . $value . ')' .
			'(?:\'|")' .
			'\s*' .
		'\s*;(\s*)(?:\b|$)';
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Gets expansion replacement string
	 *
	 * @param string $name Variable name.
	 * @param string $value Macro on export, variable value on import.
	 *
	 * @return string
	 */
	public function get_replacement( $name, $value ) {
		$varstart = '$';
		$vquots   = strpos( $value, "'" ) === false ? "'" : '"';

		return "{$varstart}$name = {$vquots}{$value}{$vquots};\$2";
	}
}