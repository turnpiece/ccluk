<?php
/**
 * Give Recurring Cron
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Recurring Reminders Class
 */
class Give_Recurring_Cron {

	protected $db;

	/**
	 * Get things started
	 *
	 * @since  1.0
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Set up our actions and properties
	 *
	 * @since  1.0
	 */
	public function init() {

		$this->db = new Give_Subscriptions_DB;

		add_action( 'give_daily_scheduled_events', array( $this, 'check_for_expired_subscriptions' ) );
		add_action( 'give_daily_scheduled_events', array( $this, 'delete_abandoned_subscriptions' ) );
		add_action( 'give_weekly_scheduled_events', array( $this, 'delete_old_sync_logs' ) );

	}

	/**
	 * Check for expired subscriptions once per day and mark them as expired
	 *
	 * @since  1.0
	 */
	public function check_for_expired_subscriptions() {

		$args = array(
			'status'     => 'active',
			'number'     => 999999,
			'expiration' => array(
				'start' => date( 'Y-n-d 00:00:00', strtotime( '-1 day', current_time( 'timestamp' ) ) ),
				'end'   => date( 'Y-n-d 23:59:59', strtotime( '-1 day', current_time( 'timestamp' ) ) )
			)

		);

		$subs = $this->db->get_subscriptions( $args );

		if ( ! empty( $subs ) ) {

			foreach ( $subs as $sub ) {

				// TODO: Run sync here.

			}

		}

	}


	/**
	 * Deletes pending subscription records older than 1 week.
	 *
	 * @since 1.3
	 * @return void
	 */
	public function delete_abandoned_subscriptions() {

		$db = new Give_Subscriptions_DB;

		$args = array(
			'status' => 'pending',
			'number' => 1000,
			'date'   => array(
				'end' => '-1 week'
			),
		);

		$subscriptions = $db->get_subscriptions( $args );

		if ( $subscriptions ) {

			foreach ( $subscriptions as $subscription ) {

				delete_post_meta( $subscription->parent_payment_id, '_give_subscription_payment' );
				$db->delete( $subscription->id );

			}

		}

	}


	/**
	 * Deletes old Sync log data.
	 *
	 * @since 1.3
	 */
	public function delete_old_sync_logs() {

	}

}