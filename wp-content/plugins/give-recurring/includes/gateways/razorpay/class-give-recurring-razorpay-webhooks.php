<?php
/**
 * Give - Razorpay | Process Webhooks for Recurring support
 *
 * @since 1.9.5
 *
 * @package    Give
 * @subpackage Recurring
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Recurring_Razorpay_Webhooks' ) ) {

	/**
	 * Class Give_Recurring_Razorpay_Webhooks
	 *
	 * @since 1.9.5
	 */
	class Give_Recurring_Razorpay_Webhooks {

		/**
		 * Give_Razorpay_Webhooks constructor.
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			// Webhook trigger for subscription cancellation.
			add_action( 'give_razorpay_event_subscription.cancelled', array( $this, 'process_cancel_subscription' ) );

			// Webhook trigger for subscription renewals.
			add_action( 'give_razorpay_event_invoice.paid', array( $this, 'process_renewal_subscription' ) );
		}

		/**
		 * Process Renewal Subscription.
		 *
		 * @param object $event Event object from Razorpay.
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_renewal_subscription( $event ) {

			// Bailout, if invoice data is not set.
			if ( ! isset( $event->payload->invoice->entity ) ) {
				return;
			}

			$razorpay_invoice = $event->payload->invoice->entity;

			if ( ! empty( $razorpay_invoice->entity ) && 'invoice' === $razorpay_invoice->entity ) {

				$subscription = new Give_Subscription( $razorpay_invoice->subscription_id, true );

				// Check for subscription ID.
				if ( 0 === $subscription->id ) {
					return;
				}

				$total_payments = intval( $subscription->get_total_payments() );
				$bill_times     = intval( $subscription->bill_times );

				if ( $subscription->can_cancel() ) {

					// If subscription is ongoing or bill_times is less than total payments.
					if (
						0 === $bill_times ||
						$total_payments < $bill_times
					) {

						// We have a new invoice payment for a subscription.
						$amount         = give_razorpay_unformat_amount( $razorpay_invoice->amount_paid );
						$transaction_id = $razorpay_invoice->payment_id;

						// Look to see if we have set the transaction ID on the parent payment yet.
						if ( ! $subscription->get_transaction_id() ) {
							// This is the initial transaction payment aka first subscription payment.
							$subscription->set_transaction_id( $transaction_id );

						} else {

							$donation_id = give_get_purchase_id_by_transaction_id( $transaction_id );

							// Check if donation id empty that means renewal donation not made so please create it.
							if ( empty( $donation_id ) ) {

								$args = array(
									'amount'         => $amount,
									'transaction_id' => $transaction_id,
									'post_date'      => date_i18n( 'Y-m-d H:i:s', $razorpay_invoice->created_at ),
								);
								// We have a renewal.
								$subscription->add_payment( $args );
								$subscription->renew();
							}

							// Check if this subscription is complete.
							give_razorpay_is_subscription_completed( $subscription, $total_payments, $bill_times );

						}
					} else {

						// Check if this subscription is complete.
						give_razorpay_is_subscription_completed( $subscription, $total_payments, $bill_times );
					}
				} else {
					give_record_gateway_error(
						__( 'Razorpay Recurring Webhook Error', 'give-recurring' ),
						__( 'The Stripe Gateway returned an error while canceling the subscription. Details: Subscription can\'t be canceled.', 'give-recurring' )
					);
				}
			}
		}

		/**
		 * Process Cancel Subscription.
		 *
		 * @param object $event Event object from Razorpay.
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_cancel_subscription( $event ) {
			$razorpay_subscription = $event->payload->subscription->entity;

			if ( ! empty( $razorpay_subscription->entity ) && 'subscription' === $razorpay_subscription->entity ) {
				$subscription = new Give_Subscription( $razorpay_subscription->id, true );

				// Check subscription status to confirm it is cancelled.
				if ( 'cancelled' ===  $razorpay_subscription->status ) {

					// Cancel the subscription in Give.
					$subscription->cancel();

					// Cancel the parent payment.
					give_update_payment_status( $subscription->parent_payment_id, 'cancelled' );
				}
			}
		}

	}

	// Initialize Recurring Razorpay Webhooks.
	new Give_Recurring_Razorpay_Webhooks();
}
