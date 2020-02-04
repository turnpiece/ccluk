<?php
/**
 * Shipper codec: domain replacer
 *
 * @package shipper
 */

/**
 * Domain replacer class
 */
class Shipper_Helper_Codec_Domain extends Shipper_Helper_Codec {

	/**
	 * Gets a list of replacement pairs
	 *
	 * A single replacement pair list, with current domain as key and
	 * replacement macro as value.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		$destinations = new Shipper_Model_Stored_Destinations;
		$current = $destinations->get_current();
		$rpl = array();

		/**
		 * Allows for domain-with-scheme macro opting out
		 *
		 * @param bool $include_scheme_macro Whether to include the scheme macro.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'shipper_codec_domain_include_scheme', true ) ) {
			// This has to come first in the list.
			// That is so we expand URLs with scheme first.
			// Should fix the 840279109797148 issue.
			$rpl[ get_bloginfo( 'wpurl' ) ] = '{{SHIPPER_WPURL_WITH_SCHEME}}';
			$rpl[ get_bloginfo( 'url' ) ] = '{{SHIPPER_URL_WITH_SCHEME}}';
		}

		$rpl[ $current['domain'] ] = '{{SHIPPER_DOMAIN}}';

		if ( $current['domain'] !== $current['home_url'] ) {
			$rpl[ $current['home_url'] ] = '{{SHIPPER_HOME_URL}}';
		}

		$has_domain_in_admin = preg_match(
			'/' . preg_quote( $current['domain'], '/' ) . '/',
			$current['admin_url']
		);
		$has_home_in_admin = preg_match(
			'/' . preg_quote( $current['home_url'], '/' ) . '/',
			$current['admin_url']
		);
		if ( ! $has_domain_in_admin && ! $has_home_in_admin ) {
			$rpl[ $current['admin_url'] ] = '{{SHIPPER_ADMIN_URL}}';
		}

		return $rpl;
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will match only the domain expression.
	 *
	 * @param string $string Original domain.
	 * @param string $value Macro on import, empty on export.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: '\b' . preg_quote( $string, '/' ) . '(\b|$)';
		return $value;
	}

	/**
	 * Gets expansion replacement string
	 *
	 * @param string $name Original domain.
	 * @param string $value Process-dependent domain representation.
	 *                      (macro on export, original on import).
	 *
	 * @return string
	 */
	public function get_replacement( $name, $value ) {
		return $value;
	}
}