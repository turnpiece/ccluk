<?php
/**
 * Shipper check tasks: files preflight check (package migrations)
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Files check task class
 */
class Shipper_Task_Check_Package_Files extends Shipper_Task_Check_Files {

	/**
	 * Gets package size check specifically
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function get_package_size_check() {
		$check = new Shipper_Model_Check( __( 'Package size', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		$threshold = Shipper_Model_Stored_Migration::get_package_size_threshold();
		$package_size = $this->get_updated_package_size();

		if ( $package_size > $threshold ) {
			$check->set( 'title', __( 'Package size is large', 'shipper' ) );
			$status = Shipper_Model_Check::STATUS_WARNING;
		}
		$check->set( 'check_type', 'package_size' );

		$tpl = new Shipper_Helper_Template;
		$check->set(
			'message',
			$tpl->get('modals/packages/preflight/issue-package_size-full', array(
				'package_size' => $package_size,
				'threshold' => $threshold,
			))
		);
		$check->set(
			'short_message',
			$tpl->get('modals/packages/preflight/issue-package_size-summary', array(
				'package_size' => $package_size,
				'threshold' => $threshold,
			))
		);

		return $check->complete( $status );
	}
}