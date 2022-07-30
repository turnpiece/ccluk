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
	 * @param bool $value true or false of value.
	 *
	 * Package migrations do not use AWS.
	 */
	public function is_php_aws_support_valid( $value ) {
		return parent::is_php_aws_support_valid( true );
	}

	/**
	 * For package migration, password protection is not an issue.
	 *
	 * @since 1.2.5
	 *
	 * @param bool $value true or false of value.
	 *
	 * @return object
	 */
	public function is_server_access_protected_valid( $value ) {
		return parent::is_server_access_protected_valid( false );
	}

	/**
	 * Do not update the Hub
	 *
	 * @param string $data data.
	 */
	public function update_hub( $data ) {
		return false;
	}
}