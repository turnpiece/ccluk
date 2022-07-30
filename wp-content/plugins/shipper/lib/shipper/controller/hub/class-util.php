<?php
/**
 * Shipper controllers: Utilities Hub actions Hub
 *
 * @package shipper
 */

/**
 * Hub utilities action class
 */
class Shipper_Controller_Hub_Util extends Shipper_Controller_Hub {

	/**
	 * Gets the list of known Hub actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::ACTION_PING,
			self::ACTION_PREFLIGHT,
			self::ACTION_RESET_CREDS,
		);
		return $known;
	}

	/**
	 * Resets credentials cache
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 */
	public function json_reset_creds_cache( $params, $action, $request = false ) {
		$model = new Shipper_Model_Stored_Creds();
		$model->clear();
		$model->set_timestamp( false );
		$model->save();
		return $this->send_response_success( true, $request );
	}

	/**
	 * Fetches preflight data
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 */
	public function json_preflight( $params, $action, $request = false ) {
		$phase = 'domain_validation';
		if ( ! is_object( $params ) ) {
			return $this->send_response_error(
				new WP_Error(
					$phase,
					'Invalid parameters'
				),
				$request
			);
		}

		if ( empty( $params->domain ) ) {
			return $this->send_response_error(
				new WP_Error(
					$phase,
					'Required parameter missing: domain'
				),
				$request
			);
		}

		$migration = new Shipper_Model_Stored_Migration();
		if ( $migration->is_active() ) {
			return $this->send_response_error(
				new WP_Error(
					$phase,
					'There is a migration already running'
				),
				$request
			);
		}

		$migration->prepare(
			Shipper_Model_Stored_Destinations::get_current_domain(),
			$params->domain,
			Shipper_Model_Stored_Migration::TYPE_EXPORT,
			true
		);

		$ctrl      = Shipper_Controller_Runner_Preflight::get();
		$preflight = $ctrl->get_status();
		$data      = $preflight->get_data();
		$result    = array(
			'is_done'                => false,
			'estimated_package_size' => 0,
		);

		if ( empty( $data ) ) {
			$ctrl->start();
		} else {
			$ctrl->ping();
		}

		if ( $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE ) ) {
			$result['is_done'] = true;

			$estimate                         = new Shipper_Model_Stored_Estimate();
			$result['estimated_package_size'] = $estimate->get( 'package_size' );

			// One and done. Restart next time.
			$ctrl->get_status()
				->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES, false )
				->clear_check_errors( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES )
				->save();
			$migration->clear()->save();
		}

		return $this->send_response_success( $result, $request );
	}

	/**
	 * Handles a new migration start request
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 */
	public function json_ping( $params, $action, $request = false ) {
		return $this->send_response_success(
			array( 'status' => true ),
			$request
		);
	}
}