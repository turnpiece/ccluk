<?php
/**
 * Shipper controllers: behavior overrides abstraction
 *
 * All behaavior overriding controllers extend from this.
 *
 * @package shipper
 */

/**
 * Overrides controller abstraction
 */
abstract class Shipper_Controller_Override extends Shipper_Controller {

	/**
	 * Holds constants context
	 *
	 * Used in tests.
	 *
	 * @var object Shipper_Model_Constants_Shipper
	 */
	private $constants;

	/**
	 * Check whether we're in debug mode
	 *
	 * @return bool
	 */
	public function is_in_debug_mode() {
		/**
		 * Debug mode filter
		 *
		 * Debug mode affects FS storage implementation and
		 * cancels out the concealments/obfuscation for easier
		 * debugging.
		 *
		 * @param $is_in_debug_mode bool Defaults to false.
		 *
		 * @return bool Whether we're in debug mode
		 */
		return apply_filters(
			'shipper_internals_is_in_debug_mode',
			false
		);
	}

	/**
	 * Sets constants object to use
	 *
	 * Used in tests.
	 *
	 * @param object $constants Shipper_Model_Constants_Shipper instance.
	 */
	public function set_constants( $constants ) {
		$this->constants = $constants;
	}

	/**
	 * Gets constants model to use as context
	 *
	 * @return object Shipper_Model_Constants_Shipper instance
	 */
	public function get_constants() {
		if ( empty( $this->constants ) ) {
			$this->constants = new Shipper_Model_Constants_Shipper();
		}
		return $this->constants;
	}
}