<?php
/**
 * Give Recurring - Stripe Subscription Helper Functions.
 *
 * @package    Give
 * @subpackage Recurring
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.9.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function is used to check whether the subscription is completed or not.
 *
 * @param Give Subscription $subscription   Subscription object of Give.
 * @param int               $total_payments Total payments count of a subscription.
 * @param int               $bill_times     Number of times the subscription is actually billed.
 *
 * @since 1.9.3
 *
 * @return bool
 */
function give_recurring_stripe_is_subscription_completed( $subscription, $total_payments, $bill_times ) {

	if ( $total_payments >= $bill_times && 0 !== $bill_times ) {

		// Cancel subscription in stripe if the subscription has run its course.
		give_recurring_stripe_cancel_subscription( $subscription );

		// Complete the subscription w/ the Give_Subscriptions class.
		$subscription->complete();

		return true;
	}

	return false;
}

/**
 * This function will be used to check whether a subscription can be cancelled or not.
 *
 * @param bool              $default      Set the default option. Default: false.
 * @param Give_Subscription $subscription Subscription object of Give.
 *
 * @since 1.9.3
 *
 * @return bool
 */
function give_recurring_stripe_can_cancel( $default, $subscription ) {

	if (
		! empty( $subscription->profile_id ) &&
		'active' === $subscription->status
	) {
		$default = true;
	}

	return $default;
}

/**
 * This function is used to cancel the subscription from Give as well as Stripe.
 *
 * @param Give_Subscription $subscription Subscription object of Give.
 *
 * @since 1.9.3
 *
 * @return bool
 */
function give_recurring_stripe_cancel_subscription( $subscription ) {

	// Get the Stripe customer ID.
	$stripe_customer_id = give_recurring_stripe_get_customer_id( $subscription->donor->email );

	// Must have a Stripe customer ID.
	if ( ! empty( $stripe_customer_id ) ) {
		$stripe_subscription = new Give_Recurring_Stripe_Subscription();
		return $stripe_subscription->cancel( $subscription->profile_id );
	}

	return false;
}

/**
 * This function is used to get customer id.
 *
 * @param string $donor_email Donor Email.
 * @param string $gateway     Gateway used.
 *
 * @since 1.9.3
 *
 * @return string
 */
function give_recurring_stripe_get_customer_id( $donor_email, $gateway = 'stripe' ) {

	// First check user meta to see if they have made a previous donation
	// w/ Stripe via non-recurring donation so we don't create a duplicate Stripe customer for recurring.
	$customer_id = give_stripe_get_customer_id( $donor_email );

	// If no data found check the subscribers profile to see if there's a recurring ID already.
	if ( empty( $customer_id ) ) {
		$subscriber  = new Give_Recurring_Subscriber( $donor_email );
		$customer_id = $subscriber->get_recurring_donor_id( $gateway );
	}

	return $customer_id;
}
