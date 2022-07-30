<?php
/**
 * Shipper helpers: runner kickstart scheduler
 *
 * Deals with runner kickstart scheduling and action dispatching.
 *
 * @package shipper
 */

/**
 * Shipper kickstart helper class
 */
class Shipper_Helper_Kickstarter {

	const MAX_RESCHEDULE_ATTEMPTS = 75;

	/**
	 * Holds reference to differentiating action
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Holds callback reference
	 *
	 * @var callable
	 */
	private $callback;

	/**
	 * Shipper_Helper_Kickstarter constructor.
	 *
	 * @param string   $action action name.
	 * @param callable $callback callback function.
	 */
	public function __construct( $action, $callback ) {
		$this->action   = $action;
		$this->callback = $callback;

		if ( ! (bool) has_action( $action, $callback ) ) {
			add_action(
				$this->get_kickstart_action(),
				$callback
			);
		}
	}

	/**
	 * Gets differentiating action string
	 *
	 * @return string
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * Gets cron kickstart action
	 *
	 * Some setups will force the requests to be blocking without telling us.
	 * And those requests might also be capped, ending the cascade with request
	 * failure. This action will force-resume a job using one-time cron action.
	 *
	 * @return string
	 */
	public function get_kickstart_action() {
		return sprintf( 'cron_%s', $this->get_action() );
	}

	/**
	 * Unschedules reboot event.
	 */
	public function unschedule_reboot() {
		$action = $this->get_kickstart_action();
		$event  = wp_next_scheduled( $action );
		if ( ! empty( $event ) ) {
			wp_unschedule_event( $event, $action );
		}
	}

	/**
	 * Schedules the reboot event.
	 *
	 * @return bool
	 */
	public function schedule_reboot() {
		$this->unschedule_reboot();
		$grace_period = apply_filters(
			'shipper_kickstart_grace_period',
			5
		);
		$delay        = Shipper_Helper_System::get_max_exec_time_capped() + $grace_period;
		$action       = $this->get_kickstart_action();
		wp_schedule_single_event( time() + $delay, $action );
		$has_schedule = wp_next_scheduled( $action );

		if ( empty( $has_schedule ) ) {
			for ( $i = 0; $i < self::MAX_RESCHEDULE_ATTEMPTS; $i++ ) {
				// delay 5-37 ms.
				usleep( 5000 + rand( 1, 32000 ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_rand
				wp_schedule_single_event( time() + $delay, $action );
				$has_schedule = wp_next_scheduled( $action );
				if ( ! empty( $has_schedule ) ) {
					Shipper_Helper_Log::write( "Rescheduled at: {$i}" );
					break;
				}
			}
			if ( empty( $has_schedule ) ) {
				Shipper_Helper_Log::write( 'Out of rescheduling attempts' );
			}
		}

		return (bool) $has_schedule;
	}
}