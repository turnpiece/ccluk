<?php
/**
 * Shipper template helpers: sorting helper
 *
 * @since v1.0.3
 * @package shipper
 */

/**
 * Handles sorting in templates
 */
class Shipper_Helper_Template_Sorter {

	/**
	 * Sorts checks by their error statsu
	 * 
	 * Errors first, next warnings, success last
	 *
	 * @param array $checks A list of checks data hashes.
	 *
	 * @return array Sorted checks
	 */
	static public function checks_by_error_status( $checks ) {
		if ( ! is_array( $checks ) || empty( $checks ) ) {
			return array();
		}

		$errors = array();
		$warnings = array();
		$success = array();

		foreach( $checks as $check ) {
			if ( Shipper_Model_Check::STATUS_ERROR === $check['status'] ) {
				$errors[] = $check;
			} else if ( Shipper_Model_Check::STATUS_WARNING === $check['status'] ) {
				$warnings[] = $check;
			} else if ( Shipper_Model_Check::STATUS_OK === $check['status'] ) {
				$success[] = $check;
			}
		}

		return array_merge(
			$errors,
			$warnings,
			$success
		);
	}
}
