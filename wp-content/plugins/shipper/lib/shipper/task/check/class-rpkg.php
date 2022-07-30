<?php
/**
 * Shipper tasks: Remote package size check
 *
 * Checks the other system for package size calculated in preflight.
 *
 * @package shipper
 */

/**
 * Systems differences check class
 */
class Shipper_Task_Check_Rpkg extends Shipper_Task_Check {

	const ERR_BLOCKING = 'issue_blocking';
	const ERR_WARNING  = 'issue_warning';

	/**
	 * Runs the diff checks suite.
	 *
	 * @param array $remote Other system info, as created by Shipper_Model_System::get_data on the other end.
	 *
	 * @return bool
	 */
	public function apply( $remote = array() ) {
		if ( empty( $remote ) ) {
			$this->add_error(
				self::ERR_BLOCKING,
				__( 'No remote data to process', 'shipper' )
			);
			return false;
		}

		$this->add_check(
			$this->is_remote_package_size_acceptable( $remote )
		);

		return true;
	}

	/**
	 * Checks for remote package size
	 *
	 * @param array $remote Remote data, including package size.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_remote_package_size_acceptable( $remote ) {
		$check     = new Shipper_Model_Check( __( 'Package size', 'shipper' ) );
		$status    = Shipper_Model_Check::STATUS_OK;
		$threshold = Shipper_Model_Stored_Migration::get_package_size_threshold();

		$size                = ! empty( $remote['estimated_package_size'] )
			? (int) $remote['estimated_package_size']
			: 0;
		$has_existing_export = ! empty( $remote['existing_export'] )
			? (bool) $remote['existing_export']
			: false;

		$tpl  = new Shipper_Helper_Template();
		$args = array(
			'size'                => $size,
			'has_existing_export' => $has_existing_export,
		);
		if ( $size > $threshold ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
			$check->set( 'title', __( 'Package size is large', 'shipper' ) );
			$check->set( 'message', $tpl->get( 'checks/remote-package-large', $args ) );
		} elseif ( 0 === $size ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
			if ( $has_existing_export ) {
				$check->set( 'title', __( 'Using existing remote export', 'shipper' ) );
			} else {
				$check->set( 'title', __( 'Remote package size', 'shipper' ) );
			}
			$check->set( 'message', $tpl->get( 'checks/remote-package-zero', $args ) );
		}
		$check->set( 'estimated_package_size', $size );

		return $check->complete( $status );
	}

}