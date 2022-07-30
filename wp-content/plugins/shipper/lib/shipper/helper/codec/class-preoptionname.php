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
	 * Model instance
	 *
	 * @var \Shipper_Model_Stored_Migration
	 */
	private $model;

	/**
	 * Shipper_Helper_Codec_Preoptionname constructor.
	 */
	public function __construct() {
		$this->model = new Shipper_Model_Stored_Migration();
	}

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
		$prefix = $wpdb->prefix;

		if ( ! $this->model->is_package_migration() ) {
			$prefix = $this->model->get( 'destination_prefix' ) ? $this->model->get( 'destination_prefix' ) : $prefix;
		}

		return array(
			$prefix => '{{SHIPPER_TABLE_PREFIX}}',
		);
	}

	/**
	 * Get matcher
	 *
	 * @param string $string string to match.
	 * @param string $value replacement string.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		// While encoding sql strings.
		if ( empty( $value ) ) {
			/**
			 * Skip default fixed and private meta_keys for example `wp_` as prefix.
			 * _wp_|wp_page_for_privacy_policy.
			 * If prefix is `wp-`, skip `wp-smush` etc.
			 *
			 * @see https://incsub.atlassian.net/browse/SHI-227
			 *
			 * @since 1.2.2
			 */
			$fixed_keys = implode(
				'|',
				apply_filters(
					'shipper_get_fixed_meta_keys_matcher',
					array(
						'smush',
						'page_for_privacy_policy',
					)
				)
			);

			return "(?<![_]|[\w]){$string}(?!{$fixed_keys})";
		}

		// While decoding sql strings.
		return preg_quote( $value, '/' );
	}
}