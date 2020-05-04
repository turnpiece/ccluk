<?php
/**
 * Give Recurring - Listen to Stripe Webhooks
 *
 * @package    Give
 * @subpackage Recurring
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Recurring_Stripe_Webhooks' ) ) {

	/**
	 * Class Give_Recurring_Stripe_Webhooks
	 *
	 * @since 1.9.0
	 */
	class Give_Recurring_Stripe_Webhooks {

		/**
		 * Recurring Stripe to call Give_Recurring_Stripe class.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @var Give_Recurring_Stripe
		 */
		public $recurring_stripe;

		/**
		 * Give_Recurring_Stripe_Webhooks Constructor.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			$this->recurring_stripe = new Give_Recurring_Stripe_Gateway();

			add_action( 'give_stripe_event_invoice.payment_succeeded', array( $this, 'process_invoice_payment_succeeded_event' ) );
			add_action( 'give_stripe_event_invoice.payment_failed', array( $this, 'process_invoice_payment_failed_event' ) );
			add_action( 'give_stripe_event_customer.subscription.deleted', array( $this, 'process_customer_subscription_deleted' ) );
			add_action( 'give_stripe_process_checkout_session_completed', array( $this, 'process_checkout_session_completed' ), 10, 2 );
		}

		/**
		 * Processes invoice.payment_succeeded event.
		 *
		 * @param \Stripe\Event $stripe_event Stripe Event received via webhooks.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @return bool|Give_Subscription
		 */
		public function process_invoice_payment_succeeded_event( $stripe_event ) {

			// Bail out, if incorrect event type received.
			if ( 'invoice.payment_succeeded' !== $stripe_event->type ) {
				return false;
			}

			$invoice = $stripe_event->data->object;

			// Make sure we have an invoice object.
			if ( 'invoice' !== $invoice->object ) {
				return false;
			}

			$subscription_profile_id = $invoice->subscription;
			$subscription            = new Give_Subscription( $subscription_profile_id, true );

			// Check for subscription ID.
			if ( 0 === $subscription->id ) {
				return false;
			}

			/**
			 * This action hook will be used to extend processing the invoice payment succeeded event.
			 *
			 * @since 1.9.4
			 */
			do_action( 'give_recurring_stripe_process_invoice_payment_succeeded', $stripe_event );

			$total_payments = intval( $subscription->get_total_payments() );
			$bill_times     = intval( $subscription->bill_times );

			if ( give_recurring_stripe_can_cancel( false, $subscription ) ) {

				// If subscription is ongoing or bill_times is less than total payments.
				if ( 0 === $bill_times || $total_payments < $bill_times ) {

					// We have a new invoice payment for a subscription.
					$amount         = give_stripe_cents_to_dollars( $invoice->total );
					$transaction_id = $invoice->charge;

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
								'post_date'      => date_i18n( 'Y-m-d H:i:s', $invoice->created ),
							);
							// We have a renewal.
							$subscription->add_payment( $args );
							$subscription->renew();
						}

						// Check if this subscription is complete.
						give_recurring_stripe_is_subscription_completed( $subscription, $total_payments, $bill_times );

					}
				} else {

					give_recurring_stripe_is_subscription_completed( $subscription, $total_payments, $bill_times );
				}

				return $subscription;

			} else {
				give_record_gateway_error(
					__( 'Stripe Subscription Can Cancel Error', 'give-recurring' ),
					__( 'The Stripe Gateway returned an error while canceling the subscription. Details: Subscription can\'t be canceled.', 'give-recurring' )
				);
			}

			return false;

		}

		/**
		 * Processes invoice.payment_failed event.
		 *
		 * @param object $event Stripe Event received via webhooks.
		 *
		 * @since  2.2.0
		 * @access public
		 *
		 * @return bool|Give_Subscription
		 */
		public function process_invoice_payment_failed_event( $event ) {

			// Bail out, if incorrect event type received.
			if ( 'invoice.payment_failed' !== $event->type ) {
				return false;
			}

			$invoice = $event->data->object;

			// Make sure we have an invoice object.
			if ( 'invoice' !== $invoice->object ) {
				return false;
			}

			/**
			 * This action hook will be used to extend processing the invoice payment failed event.
			 *
			 * @since 1.9.4
			 */
			do_action( 'give_recurring_stripe_process_invoice_payment_failed', $event );

			if (
				$invoice->attempted &&
				! $invoice->paid &&
				null !== $invoice->next_payment_attempt
			) {

				$subscription = give_recurring_get_subscription_by( 'profile', $invoice->subscription );

				// Send email notification to donor for updating the payment method.
				do_action( 'give_donor-subscription-payment-failed_email_notification', $subscription, $invoice );

				// Log the invoice object for debugging purpose.
				give_stripe_record_log(
					__( 'Subscription - Renewal Payment Failed', 'give-recurring' ),
					print_r( $invoice, true )
				);

				// Change status of subscription to failing.
				if ( $subscription->id > 0 ) {
					give_recurring_update_subscription_status( $subscription->id, 'failing' );
				}

				return true;

			}

			return false;

		}

		/**
		 * Process customer.subscription.deleted event posted to webhooks.
		 *
		 * @param \Stripe\Event $stripe_event Stripe Event.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @return bool
		 */
		public function process_customer_subscription_deleted( $stripe_event ) {

			if ( $stripe_event instanceof \Stripe\Event ) {

				// Sanity Check.
				if ( 'customer.subscription.deleted' !== $stripe_event->type ) {
					return false;
				}

				$subscription = $stripe_event->data->object;

				/**
				 * This action hook will be used to extend processing the customer subscription deleted event.
				 *
				 * @since 1.9.4
				 */
				do_action( 'give_recurring_stripe_process_customer_subscription_deleted', $stripe_event );

				if ( 'subscription' === $subscription->object ) {

					$profile_id   = $subscription->id;
					$subscription = new Give_Subscription( $profile_id, true );

					// Sanity Check: Don't cancel already completed subscriptions or empty subscription objects.
					if ( empty( $subscription ) || 'completed' === $subscription->status ) {

						return false;

					} elseif ( 'cancelled' !== $subscription->status ) {

						// Cancel the subscription.
						$subscription->cancel();

						return true;
					}
				}
			}

			return false;
		}

		/**
		 * This function is used to process webhook `checkout.session.completed` for recurring donations.
		 *
		 * @param int           $donation_id Donation ID.
		 * @param \Stripe\Event $event       Event object sent by Stripe.
		 *
		 * @since  1.9.4
		 * @access public
		 *
		 * @return void
		 */
		public function process_checkout_session_completed( $donation_id, $event ) {
			/* @var stdClass $checkout_session */
			$checkout_session = $event->data->object;

			// Make sure we have an invoice object.
			if ( 'checkout.session' !== $checkout_session->object ) {
				return;
			}

			$donation_id = absint( Give()->payment_meta->get_column_by( 'donation_id', 'meta_value', $checkout_session->id ) );


			// Exit if not any donation attached with checkout session id.
			if( ! $donation_id ) {
				return;
			}

			// Update payment status to donation.
			give_update_payment_status( $donation_id, 'publish' );

			// Insert donation note to inform admin that charge succeeded.
			give_insert_payment_note( $donation_id, __( 'Charge succeeded in Stripe.', 'give-recurring' ) );

			$subscription      = give_recurring_get_subscription_by( 'payment', $donation_id );
			$give_subscription = new Give_Subscription($subscription->id);
			$give_subscription->update( array(
				'profile_id' => $checkout_session->subscription,
			) );

			// Change status of subscription to failing.
			if ( $subscription->id > 0 ) {
				give_recurring_update_subscription_status( $subscription->id, 'active' );
			}
		}
	}
}

new Give_Recurring_Stripe_Webhooks();
