<?php
/**
 * Shipper package controllers: overrides abstraction
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package overrides abstraction class
 */
abstract class Shipper_Controller_Override_Package extends Shipper_Controller_Override {

	/**
	 * Shipper_Model_Stored_Package instance holder.
	 *
	 * @var Shipper_Model_Stored_Package
	 */
	private $model;

	/**
	 * Array of exclusions.
	 *
	 * @var array
	 */
	private $exclusions;

	/**
	 * Actually applies controller-specific overrides.
	 */
	abstract public function apply_overrides();

	/**
	 * Gets implementation-specific exclusion scope
	 *
	 * @return string One of the Shipper_Model_Stored_Package exclusion keys
	 */
	abstract public function get_scope();

	/**
	 * Boots the controller and sets up event listeners
	 */
	public function boot() {
		add_action(
			'shipper_package_migration_tick_before',
			array( $this, 'apply_overrides' )
		);
	}

	/**
	 * Gets the model instance
	 *
	 * @return object A Shipper_Model_Stored_Package instance
	 */
	public function get_model() {
		if ( empty( $this->model ) ) {
			$this->model = new Shipper_Model_Stored_PackageMeta();
		}

		return $this->model;
	}

	/**
	 * Gets the exclusions to apply
	 *
	 * @return array
	 */
	public function get_exclusions() {
		if ( empty( $this->exclusions ) ) {
			$tmp              = $this->get_model()->get( $this->get_scope(), array() );
			$this->exclusions = array_unique( array_filter( array_map( 'trim', $tmp ) ) );
		}

		return $this->exclusions;
	}
}