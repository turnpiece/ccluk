<?php
/**
 * Give Recurring - Stripe Subscriptions API
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

if ( ! class_exists( 'Give_Recurring_Stripe_Subscription' ) ) {

	/**
	 * Class Give_Recurring_Stripe_Subscription
	 *
	 * @since 1.9.3
	 */
	class Give_Recurring_Stripe_Subscription {

		/**
		 * This function will be used to retrieve subscription.
		 *
		 * @param string $id Subscription ID.
		 *
		 * @since  1.9.3
		 * @access public
		 *
		 * @return bool|\Stripe\Subscription
		 */
		public function retrieve( $id ) {

			try {
				return \Stripe\Subscription::retrieve( $id );
			} catch ( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Subscription Error', 'give-recurring' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while retrieving the subscription. Details: %s', 'give-recurring' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while retrieving the subscription from Stripe. Please try again.', 'give-recurring' ) );
				return false;
			}
		}

		/**
		 * This function will be used to retrieve subscription.
		 *
		 * @param string $id   Subscription ID.
		 * @param array  $args List of update arguments.
		 *
		 * @since  1.9.13
		 * @access public
		 *
		 * @return bool|\Stripe\Subscription
		 */
		public function update( $id, $args ) {

			try {
				return \Stripe\Subscription::update( $id, $args );
			} catch ( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Subscription Error', 'give-recurring' ),
					sprintf(
					/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while updating the subscription. Details: %s', 'give-recurring' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while updating the subscription from Stripe. Please try again.', 'give-recurring' ) );
				return false;
			}
		}

		/**
		 * This function will be used to retrieve subscription.
		 *
		 * @param string $id Subscription ID.
		 *
		 * @since  1.9.3
		 * @access public
		 *
		 * @return bool
		 */
		public function cancel( $id ) {

			// Retrieve subscription details.
			$subscription = $this->retrieve( $id );

			try {
				// Cancel the subscription.
				$result = $subscription->cancel();

				// Register error in logs, if subscription is not canceled.
				if ( 'canceled' !== $result->status ) {
					give_record_gateway_error(
						__( 'Stripe Subscription Cancellation Error', 'give-recurring' ),
						__( 'The Stripe Gateway returned an error while canceling the subscription. Details: No cancellation status.', 'give-recurring' )
					);
				}

				return $result;
			} catch ( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Subscription Error', 'give-recurring' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while canceling the subscription. Details: %s', 'give-recurring' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while canceling the subscription from Stripe. Please try again.', 'give-recurring' ) );
				return false;
			}

		}
	}
}
