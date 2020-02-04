<?php
/**
 * Shipper codec: replace define statements
 *
 * @package shipper
 */

/**
 * Define replacer class
 */
class Shipper_Helper_Codec_Define extends Shipper_Helper_Codec {

	/**
	 * Gets a list of replacement pairs
	 *
	 * A replacement pair is represented like so:
	 * Define name as a key, define value as replacement macro.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		$defines = array(
			'DB_NAME'     => '{{SHIPPER_DB_NAME}}',
			'DB_HOST'     => '{{SHIPPER_DB_HOST}}',
			'DB_USER'     => '{{SHIPPER_DB_USER}}',
			'DB_PASSWORD' => '{{SHIPPER_DB_PASSWORD}}',
		);

		// Multisite.
		// Handles the simplest case - MS <=> MS migrations.
		$defines['DOMAIN_CURRENT_SITE'] = '{{SHIPPER_DOMAIN_CURRENT_SITE}}';
		$defines['PATH_CURRENT_SITE']   = '{{SHIPPER_PATH_CURRENT_SITE}}';

		return $defines;
	}

	/**
	 * Checks if the original define is present
	 *
	 * Codec implementation will not substitute with a value (and will remove
	 * the entire matcher from result) if the original is not present.
	 *
	 * @param string $original Define name.
	 *
	 * @return bool
	 */
	public function is_original_present( $original ) {
		$present = defined( $original );

		return $present;
	}

	/**
	 * Gets the define value
	 *
	 * @param string $original Define name.
	 *
	 * @return mixed
	 */
	public function get_original_value( $original ) {
		$value = defined( $original ) ? constant( $original ) : '';

		return $value;
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will match an entire define expression.
	 *
	 * @param string $string Define name.
	 * @param string $value Optional, empty on export, macro on import.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: '[^\'"]*?';

		// @codingStandardsIgnoreStart
		return '(?:^|\b)define\s?\(\s*' .
		       '(?:\'|")' .
		       preg_quote( $string, '/' ) .
		       '(?:\'|")' .
		       '\s*,\s*' .
		       '(?:\'|")' .
		       '(' . $value . ')' .
		       '(?:\'|")' .
		       '\s*' .
		       '\)\s*;\s*(?:\b|$)';
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Gets expansion replacement string
	 *
	 * @param string $name Define name.
	 * @param string $value Macro on export, define value on import.
	 *
	 * @return string
	 */
	public function get_replacement( $name, $value ) {
		$nquots = strpos( $name, "'" ) === false ? "'" : '"';
		$vquots = strpos( $value, "'" ) === false ? "'" : '"';

		return "define({$nquots}$name{$nquots}, {$vquots}{$value}{$vquots});";
	}
}