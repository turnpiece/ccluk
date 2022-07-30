<?php
/**
 * Shipper controllers: preflight heartbeat
 *
 * Updates preflight status
 *
 * @package shipper
 */

/**
 * Preflight heartbeat controller
 */
class Shipper_Controller_Heartbeat_Preflight extends Shipper_Controller_Heartbeat {

	/**
	 * Process Heartbeat API request
	 *
	 * Used for progress page updates.
	 *
	 * @param array $response Heartbeat response data to pass back to front end.
	 * @param array $data Data received from the front end (unslashed).
	 *
	 * @return array Response
	 */
	public function heartbeat( $response, $data ) {
		// If the request didn't originate from the migration page, no need to proceed.
		// If we're ever to do a global progress bar, kill this part.
		if ( empty( $data['shipper-preflight'] ) ) {
			unset( $response['shipper-preflight'] );

			return $response;
		}

		$ping           = new Shipper_Model_Stored_Ping();
		$preflight_ctrl = Shipper_Controller_Runner_Preflight::get();
		$is_stuck       = false;

		if ( $ping->maybe_show_package_migration_notice() ) {
			/**
			 * Seems like it's stuck. So try to cancel the preflight check and show an error notice.
			 *
			 * @since 1.2.6
			 */
			$is_stuck = true;
			$preflight_ctrl->attempt_cancel();
			$preflight_ctrl->clear();
		}

		$preflight = $preflight_ctrl->get_status();
		if ( ! $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE ) ) {
			$this->check_locks();
			$data = $preflight->get_data();
		}

		$system_checks = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM );
		$remote_checks = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE );
		$all_done      = $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE );

		$sections = array(
			'system_checks' => array(
				'title'   => __( 'Checking <b>Source Server\'s configurations</b>...', 'shipper' ),
				'checks'  => $system_checks,
				'is_done' => ! empty( $system_checks ),
			),
			'remote_checks' => array(
				'title'   => __( 'Checking <b>Destination Server\'s configurations</b>...', 'shipper' ),
				'checks'  => $remote_checks,
				'is_done' => ! empty( $remote_checks ),
			),
		);

		$migration = new Shipper_Model_Stored_Migration();
		if ( Shipper_Model_Stored_Migration::TYPE_IMPORT === $migration->get_type() ) {
			$checks = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG );
		} else {
			$checks = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES );
		}
		$sections['files_check'] = array(
			'title'   => __( 'Checking <b>files</b>...', 'shipper' ),
			'checks'  => $checks,
			'is_done' => $all_done,
		);

		$response['shipper-preflight']  = array(
			'is_done'  => $all_done,
			'sections' => $sections,
			'is_stuck' => $is_stuck,
		);
		$response['heartbeat_interval'] = Shipper_Helper_Assets::get_update_interval();

		if ( $all_done ) {

			/**
			 * Fire an action when preflight checking is done.
			 *
			 * @since 1.2
			 *
			 * @uses Shipper_Model_Stored_Migration
			 */
			do_action( 'shipper_preflight_checking_done' );
		}

		return $response;
	}

	/**
	 * Start or ping, depending on lock state
	 *
	 * @uses Shipper_Controller_Runner_Preflight
	 * @uses Shipper_Helper_Locks
	 * @since v1.0.3
	 */
	public function check_locks() {
		$locks   = new Shipper_Helper_Locks();
		$ctrl    = Shipper_Controller_Runner_Preflight::get();
		$process = $ctrl->get_process_lock();

		if ( $locks->has_lock( $process ) && $locks->is_old_lock( $process ) ) {
			return $ctrl->ping();
		}

		return $ctrl->start();
	}
}