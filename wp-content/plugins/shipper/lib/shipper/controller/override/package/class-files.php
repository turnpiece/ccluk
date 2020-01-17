<?php
/**
 * Shipper package controllers: package files overrides.
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package file overrides implementation class
 */
class Shipper_Controller_Override_Package_Files
	extends Shipper_Controller_Override_Package {

	public function get_scope() {
		return Shipper_Model_Stored_Package::KEY_EXCLUSIONS_FS;
	}

	public function apply_overrides() {
		$exclusions = $this->get_exclusions();
		if ( empty( $exclusions ) ) {
			return false;
		}

		add_filter(
			'shipper_path_include_file',
			array( $this, 'maybe_include' ), 10, 2
		);
	}

	/**
	 * Excludes files according to package settings
	 *
	 * @param bool $include Whether to include a path.
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function maybe_include( $include, $path ) {
		if ( empty( $include ) ) {
			return $include;
		}

		$exclusions = $this->get_exclusions();

		if ( empty( $exclusions ) ) {
			return $include;
		}

		$result = false;
		foreach ( $exclusions as $exclusion ) {
			$result = (bool) stristr( $path, $exclusion );
			if ( ! empty( $result ) ) {
				break;
			}
		}

		return ! $result;
	}
}