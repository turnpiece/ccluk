<?php
/**
 * Shipper controllers: Migration Hub actions
 *
 * @package shipper
 */

/**
 * Migration Hub actions handling controller class
 */
class Shipper_Controller_Hub_Migration extends Shipper_Controller_Hub {

	/**
	 * Gets the list of known Hub actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::ACTION_MIGRATION_START,
			self::ACTION_MIGRATION_KICKSTART,
			self::ACTION_MIGRATION_CANCEL,
		);

		return $known;
	}

	/**
	 * Handles a migration cancel request
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 */
	public function json_migration_cancel( $params, $action, $request = false ) {
		$migration = new Shipper_Model_Stored_Migration();
		if ( ! $migration->is_active() ) {
			return $this->send_response_success(
				array( 'status' => true ),
				$request
			);
		}

		$ctrl = Shipper_Controller_Runner_Migration::get();
		$ctrl->attempt_cancel();

		$migration = new Shipper_Model_Stored_Migration();
		$status    = $migration->is_active();

		return $this->send_response_success(
			array( 'status' => $status ),
			$request
		);
	}

	/**
	 * Handles a new migration kickstart request
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 *
	 * @since v1.0-beta-15
	 */
	public function json_migration_kickstart( $params, $action, $request = false ) {
		$migration = new Shipper_Model_Stored_Migration();
		if ( ! $migration->is_active() ) {
			return $this->send_response_error(
				new WP_Error( 'migration_inactive', 'Migration is not active' ),
				$request
			);
		}

		Shipper_Controller_Runner_Migration::get()->kickstart();
		Shipper_Helper_Log::write( 'Action kickstarted remotely' );

		/**
		 * Fires on remote migration kickstart
		 *
		 * @since v1.0.1
		 */
		do_action( 'shipper_dev_ping' );

		return $this->send_response_success(
			array( 'status' => true ),
			$request
		);
	}

	/**
	 * Handles a new migration start request
	 *
	 * @param object $params Parameters passed in json body.
	 * @param string $action The action name that was called.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 */
	public function json_migration_start( $params, $action, $request = false ) {
		$domain = false;
		$type   = false;
		$phase  = 'params_validation';
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
		} else {
			$domain = $params->domain;
		}

		if ( empty( $params->type ) ) {
			return $this->send_response_error(
				new WP_Error(
					$phase,
					'Required parameter missing: type'
				),
				$request
			);
		} else {
			$type = $params->type;
		}

		$types = array(
			Shipper_Model_Stored_Migration::TYPE_EXPORT,
			Shipper_Model_Stored_Migration::TYPE_IMPORT,
		);
		if ( ! in_array( $type, $types, true ) ) {
			return $this->send_response_error(
				new WP_Error(
					$phase,
					sprintf( 'Migration type not recognized: %s', $type )
				),
				$request
			);
		}

		// Now, carry on booting the export...
		$phase = 'model_update';
		$model = new Shipper_Model_Stored_Destinations();

		// Update known destinations list first.
		$task   = new Shipper_Task_Api_Destinations_Get();
		$result = $task->apply();
		if ( ! empty( $result ) ) {
			// We got the listing result - update stored destinations cache.
			$model->set_data( $result );
			$model->save(); // We actually *really* have to save here...
		} else {
			$err_msgs = array( 'No known destinations' );
			if ( $task->has_errors() ) {
				$err_msgs = array(); // We have more specific messages, reset.
				foreach ( $task->get_errors() as $err ) {
					$err_msgs[] = $err->get_error_message();
				}
			}

			return $this->send_response_error(
				new WP_Error(
					$phase,
					join( '; ', $err_msgs )
				),
				$request
			);
		}

		// We're sufficiently up to date - continue...
		$phase = 'migration_bootstrap';
		$site  = $model->get_by_domain( $domain );
		if ( empty( $site ) ) {
			return $this->send_response_error(
				new WP_Error(
					$phase,
					sprintf( 'Unable to resolve domain to site ID: %s', $params->domain )
				),
				$request
			);
		}

		$migration = new Shipper_Model_Stored_Migration();
		if ( $migration->is_active() ) {
			if ( $migration->get_type() === $type ) {
				// Success, but we're already running.
				return $this->send_response_success( 'already_started', $request );
			} else {
				return $this->send_response_error(
					new WP_Error(
						$phase,
						'Migration already in progress'
					),
					$request
				);
			}
		}

		$ctrl = Shipper_Controller_Runner_Migration::get();
		$ctrl->prepare( $type, $site['site_id'], Shipper_Model_Stored_Migration::ORIG_HUB );

		$ctrl->begin();
		$ctrl->run();

		$migration = new Shipper_Model_Stored_Migration();
		$status    = $migration->is_active();

		return ! ! $status
			? $this->send_response_success( $status, $request )
			: $this->send_response_error( 'Unable to start migration', $request );
	}
}