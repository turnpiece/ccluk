<?php
/**
 * Shipper controllers: package heartbeat
 *
 * Updates package status
 *
 * @package shipper
 */

/**
 * Package heartbeat controller
 */
class Shipper_Controller_Heartbeat_Package extends Shipper_Controller_Heartbeat_Migration {

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
			Shipper_Controller_Runner_Package::get()->kickstarter->get_kickstart_action()
		);

		$this->reschedule_kickstart_cron( $event );

		return array(
			'when' => gmdate( 'r', $event ),
			'in'   => ( ! empty( $event ) ? $event - time() : 'never' ),
		);
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
		Shipper_Controller_Runner_Package::get()->kickstarter->schedule_reboot();

		return true;
	}
}