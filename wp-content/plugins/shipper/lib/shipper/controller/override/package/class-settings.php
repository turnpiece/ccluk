<?php
/**
 * Shipper package controllers: package advanced settings overrides.
 *
 * Responsible for setting PHP FS/DB limits
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package advanced overrides implementation class
 */
class Shipper_Controller_Override_Package_Settings extends Shipper_Controller_Override_Package {

	/**
	 * No scope - we're applying overrides ourself.
	 */
	public function get_scope() {
		return false;
	}

	/**
	 * Apply overrides.
	 *
	 * @return void
	 */
	public function apply_overrides() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			add_filter( 'shipper_is_safe_mode', array( $this, 'on_safe_mode' ) );
		}
	}

	/**
	 * Disable safe mode while running on the CLI
	 *
	 * @return false
	 */
	public function on_safe_mode() {
		return false;
	}
}