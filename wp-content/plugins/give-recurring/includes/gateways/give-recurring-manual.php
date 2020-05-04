<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $give_recurring_manual;

/**
 * Class Give_Recurring_Manual_Payments
 */
class Give_Recurring_Manual_Payments extends Give_Recurring_Gateway {

	public function init() {

		$this->id = 'manual';

		add_action( 'give_recurring_cancel_' . $this->id . '_subscription', array( $this, 'cancel' ), 10, 2 );
		add_action( 'give_daily_scheduled_events', array( $this, 'check_for_test_renewal_subscriptions' ), 1 );

	}

	/**
	 * Create Payment Profiles
	 */
	public function create_payment_profiles() {

		$this->subscriptions['profile_id']     = md5( $this->purchase_data['purchase_key'] . $this->subscriptions['id'] );
		$this->subscriptions['transaction_id'] = md5( uniqid( rand(), true ) );

	}

	/**
	 * Can cancel.
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if (
			$subscription->gateway == $this->id
			&& ! empty( $subscription->profile_id )
			&& $subscription->status == 'active'
		) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Cancels a subscription.
	 *
	 * Since this is manual gateway we don't have to do anything when cancelling.
	 *
	 * @param $subscription
	 * @param $valid
	 *
	 * @return bool
	 */
	public function cancel( $subscription, $valid ) {
		return true;
	}

	/**
	 * Get all the subscriptions that are going to expire today.
	 *
	 * @since 1.4
	 *
	 * return array.
	 */
	public function get_test_renewal_subscriptions() {
		global $wpdb;

		// Subscription database name.
		$subscriptions_table_name = $wpdb->prefix . 'give_subscriptions';
		$donation_meta_table_name = Give()->payment_meta->table_name;
		$donation_id_col_name     = Give()->payment_meta->get_meta_type() . '_id';

		// sql query.
		$sql_query = $wpdb->prepare(
			"SELECT a.id, a.customer_id, a.period, a.frequency, a.recurring_amount, a.bill_times, a.transaction_id, a.parent_payment_id, a.product_id, a.expiration, a.profile_id FROM 
$subscriptions_table_name as a INNER JOIN {$donation_meta_table_name} as b ON ( a.parent_payment_id = b.{$donation_id_col_name} ) WHERE  a.status = 'active' AND a.expiration BETWEEN '%s' AND '%s' AND ( ( b.meta_key = '_give_payment_gateway' AND b.meta_value = 'manual'  ) ) GROUP BY a.parent_payment_id  ORDER BY a.parent_payment_id DESC;",
			date( 'Y-n-d 00:00:00', current_time( 'timestamp' ) ),
			date( 'Y-n-d 23:59:59', current_time( 'timestamp' ) )
		);

		return $wpdb->get_results( $sql_query );
	}

	/**
	 * Check for the test renewal subscription and renew them.
	 *
	 * @since 1.4.
	 */
	public function check_for_test_renewal_subscriptions() {
		global $wpdb;

		// Get all the test renewal subscription that are going to expire today.
		$subscriptions = $this->get_test_renewal_subscriptions();

		// Check if subscription exists that is going to get expired today.
		if ( ! empty( $subscriptions ) ) {
			foreach ( $subscriptions as $subscription ) {
				// Default is false.
				$add_renewal = false;

				// Check if subscriptions is greater then ZERO, means limited
				if ( $subscription->bill_times ) {
					$query    = $wpdb->prepare(
						"SELECT COUNT( id ) as count FROM $wpdb->posts WHERE post_status='give_subscription' AND post_type = 'give_payment' AND post_parent = %d",
						$subscription->parent_payment_id
					);
					$payments = $wpdb->get_results( $query );

					// String to int.
					$payments = (int) $payments[0]->count;

					/**
					 * Check for total number of donations that have being made for the subscription.
					 *
					 * $subscription->bill_times = Total number of renewals that have to be made
					 *
					 * count( $payments ) + 1 ) = Total of subscription that has being made. Plus one because first donation is already made when subscription was being created.
					 */
					if ( $subscription->bill_times > ( $payments + 1 ) ) {
						$add_renewal = true;
					}
				} else {
					// Subscription will not end until cancelled by admin.
					$add_renewal = true;
				}

				// Check if renewal can be done for the subscription or not.
				if ( $add_renewal ) {
					$sub            = new Give_Subscription( absint( $subscription->id ) );
					$give_recurring = Give_Recurring();

					// Will remove the action that will send mail when subscription is to renew.
					remove_action( 'give_recurring_add_subscription_payment', array(
						$give_recurring->emails,
						'send_subscription_received_email',
					), 10 );

					$payment = $sub->add_payment( array(
						'amount'         => $subscription->recurring_amount,
						'transaction_id' => $subscription->transaction_id,
					) );

					add_action( 'give_recurring_add_subscription_payment', array(
						$give_recurring->emails,
						'send_subscription_received_email',
					), 10, 3 );

					if ( $payment ) {
						// Update the new renewal date.

						$frequency = ! empty( $subscription ) ? intval( $subscription->frequency ) : 1;
						$update_sub = array( 'expiration' => date( 'Y-n-d 23:58:59', strtotime( $this->get_interval( $subscription->period, $frequency ), current_time( 'timestamp' ) ) ),
						);

						// Update the subscription status.
						if ( ! empty( $subscription->bill_times ) && ( $payments + 2 ) === (int) $subscription->bill_times ) {
							$update_sub['status'] = 'completed';
						}
						$sub->update( $update_sub );
					}
				}
			}
		}
	}

}

$give_recurring_manual = new Give_Recurring_Manual_Payments();