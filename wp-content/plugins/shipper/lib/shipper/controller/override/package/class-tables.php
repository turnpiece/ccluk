<?php
/**
 * Shipper package controllers: package tables overrides.
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package table overrides implementation class
 */
class Shipper_Controller_Override_Package_Tables
	extends Shipper_Controller_Override_Package {

	public function get_scope() {
		return Shipper_Model_Stored_Package::KEY_EXCLUSIONS_DB;
	}

	public function apply_overrides() {
		$exclusions = $this->get_exclusions();
		if ( empty( $exclusions ) ) {
			return false;
		}

		add_filter(
			'shipper_path_include_table',
			array( $this, 'maybe_include' ), 10, 2
		);
	}

	/**
	 * Excludes tables according to package settings
	 *
	 * @param bool $include Whether to include a table.
	 * @param string $table Table to check.
	 *
	 * @return bool
	 */
	public function maybe_include( $include, $table ) {
		if ( empty( $include ) ) { return $include; }

		$exclusions = $this->get_exclusions();
		if ( empty( $exclusions ) ) { return $include; }

		return ! in_array( $table, $exclusions, true );
	}
}