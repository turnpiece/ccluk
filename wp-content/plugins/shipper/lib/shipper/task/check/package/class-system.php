<?php
/**
 * Shipper tasks: general system prerequisites check (package migrations)
 *
 * @since v1.1
 * @package shipper
 */

/**
 * System prerequisites check task
 */
class Shipper_Task_Check_Package_System extends Shipper_Task_Check_System {

	/**
	 * Overridden to ensure this always passes.
	 *
	 * Package migrations do not use AWS.
	 */
	public function is_php_aws_support_valid( $value ) {
		return parent::is_php_aws_support_valid( true );
	}

	/**
	 * Do not update the Hub
	 */
	public function update_hub( $data ) {
		return false;
	}
}