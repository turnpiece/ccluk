<?php
/**
 * Shipper controllers: migration heartbeat
 *
 * Updates migration status
 *
 * @package shipper
 */

/**
 * Migration heartbeat controller
 */
class Shipper_Controller_Heartbeat_Migration extends Shipper_Controller_Heartbeat {

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
		if ( empty( $data['shipper-migration'] ) ) {
			unset( $response['shipper-migration'] );
			return $response;
		}

		$migration = new Shipper_Model_Stored_Migration();
		$info      = $migration->get( 'progress', array() );
		$errors    = $migration->get( 'errors', array() );

		$failed = ! empty( $errors );
		$data   = array(
			'is_done'  => $migration->get( 'tasks_completed' ),
			'progress' => ( ! empty( $info['percentage'] ) ? (int) $info['percentage'] : 0 ),
			'message'  => ( ! empty( $info['message'] ) ? $info['message'] : '' ),
		);

		if ( $failed ) {
			$data['errors'] = $errors;
		} elseif ( empty( $data['message'] ) && empty( $data['progress'] ) ) {
			// Still initializing, no errors this far.
			$data['message'] = __( 'Initializing migration, please be patient.', 'shipper' );
		}

		$data['kickstart'] = $this->get_kickstart_info();

		$health          = new Shipper_Model_Stored_Healthcheck();
		$data['is_slow'] = $health->is_slow_migration();

		$response['shipper-migration']  = $data;
		$response['heartbeat_interval'] = Shipper_Helper_Assets::get_update_interval();

		return $response;
	}

	/**
	 * Gets the kickstart debug info to include in response.
	 *
	 * As a side-effect, also acts as cron failsafe on problematic hosts.
	 * Hint: GoDaddy, DreamHost.
	 *
	 * @return array
	 */
	public function get_kickstart_info() {
		$event = wp_next_scheduled(
			Shipper_Controller_Runner_Migration::get()->kickstarter->get_kickstart_action()
		);
		$this->attempt_cron_respawn_if_needed( $event );
		$this->reschedule_kickstart_cron( $event );
		return array(
			'when' => gmdate( 'r', $event ),
			'in'   => ( ! empty( $event ) ? $event - time() : 'never' ),
		);
	}

	/**
	 * GoDaddy fix attempt.
	 * Observation: cron jobs take _a lot_ of time to execute.
	 *
	 * @param int $event_ts Kickstart event timestamp.
	 *
	 * @return bool
	 */
	public function attempt_cron_respawn_if_needed( $event_ts ) {
		if ( empty( $event_ts ) ) {
			return false; }

		$fire_in = $event_ts - time();
		if ( $fire_in >= 0 ) {
			return false; }
		if ( abs( $fire_in ) < Shipper_Helper_System::get_max_exec_time_capped() ) {
			return false;
		}

		// If we got this far, this means that we should have fired cron long ago.
		// So, let's try to do so now.
		Shipper_Helper_Log::debug( 'Took too long waiting for cron, re-firing' );

		/**
		 * Triggers just before cron respawning.
		 *
		 * Used in tests
		 */
		do_action( 'shipper_heartbeat_cron_restart' );
		spawn_cron();

		return true;
	}

	/**
	 * DreamHost fix attempt.
	 * Hypothesis: _something_ clears up cron events, so let's pick up when we can.
	 *
	 * @param int $event_ts Kickstart event timestamp.
	 *
	 * @return bool
	 */
	public function reschedule_kickstart_cron( $event_ts ) {
		if ( false !== $event_ts ) {
			return false; }

		$migration = new Shipper_Model_Stored_Migration();
		if ( ! $migration->is_active() ) {
			return false;
		}

		// If we got this far, we don't have kickstart scheduled.
		// Let's reschedule.
		Shipper_Helper_Log::write( 'Apparently no scheduled reboot, rescheduling' );
		Shipper_Controller_Runner_Migration::get()->kickstarter->schedule_reboot();

		return true;
	}
}