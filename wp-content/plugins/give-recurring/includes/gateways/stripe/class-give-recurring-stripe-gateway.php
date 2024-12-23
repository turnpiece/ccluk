<?php
/**
 * Give Recurring - Stripe Gateway
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

if ( ! class_exists( 'Give_Recurring_Stripe_Gateway' ) ) {

	/**
	 * Class Give_Recurring_Stripe_Gateway
	 * 
	 * @since 1.9.0
	 */
	class Give_Recurring_Stripe_Gateway extends Give_Recurring_Gateway {

		/**
		 * Stripe API secret key.
		 * 
		 * @since  1.9.0
		 * @access public
		 *
		 * @var string
		 */
		public $secret_key;

		/**
		 * Stripe API public key.
		 * 
		 * @since  1.9.0
		 * @access public
		 *
		 * @var string
		 */
		public $public_key;

		/**
		 * Stripe Gateway Object.
		 * 
		 * @since  1.9.0
		 * @access public
		 *
		 * @var Give_Stripe_Gateway
		 */
		public $stripe_gateway;

		/**
		 * Stripe Customer Object.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @var Give_Stripe_Customer
		 */
		public $stripe_customer;

		/**
		 * Stripe Invoice Object.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @var Give_Stripe_Invoice
		 */
		public $stripe_invoice;

		/**
		 * Stripe Payment Intent Object.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @var Give_Stripe_Payment_Intent
		 */
		public $stripe_payment_intent;

		/**
		 * Supported methods by Stripe.
		 *
		 * @since  1.9.0
		 * @access public
		 *
		 * @var array $supported_methods
		 */
		public $supported_methods = array();

		/**
		 * Init Recurring Stripe Gateway.
		 * 
		 * @since  1.9.0
		 * @access public
		 *
		 * @return bool|void
		 */
		public function init() {

			if (
				defined( 'GIVE_STRIPE_VERSION' ) &&
				version_compare( GIVE_STRIPE_VERSION, '2.2.0', '<' )
			) {
				add_action( 'admin_notices', array( $this, 'old_api_upgrade_notice' ) );

				// No Stripe SDK. Bounce.
				return false;
			}

			$this->secret_key            = give_stripe_get_secret_key();
			$this->public_key            = give_stripe_get_publishable_key();
			$this->stripe_gateway        = new Give_Stripe_Gateway();
			$this->stripe_invoice        = new Give_Stripe_Invoice();
			$this->stripe_payment_intent = new Give_Stripe_Payment_Intent();
			$this->supported_methods     = array( 'stripe', 'stripe_ach', 'stripe_google_pay', 'stripe_apple_pay' );

		}

		/**
		 * Upgrade notice.
		 *
		 * Tells the admin that they need to upgrade the Stripe gateway.
		 *
		 * @since  1.9.0
		 * @access public
		 */
		public function old_api_upgrade_notice() {

			$message = sprintf(
			/* translators: 1. GiveWP account login page, 2. GiveWP Account downloads page */
				__( '<strong>Attention:</strong> The Stripe Premium plugin requires the latest version of the Recurring donations add-on to process donations properly. Please update to the latest version of Recurring donations plugin to resolve this issue. If your license is active you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%2$s" target="_blank">your downloads</a> page on the Give website.', 'give-recurring' ),
				'https://givewp.com/my-account',
				'https://givewp.com/my-downloads/'
			);

			if ( class_exists( 'Give_Notices' ) ) {
				Give()->notices->register_notice(
					array(
						'id'          => 'give-activation-error',
						'type'        => 'error',
						'description' => $message,
						'show'        => true,
					)
				);
			} else {
				$class = 'notice notice-error';
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
			}
		}

		/**
		 * Can Cancel.
		 *
		 * @param $ret
		 * @param $subscription
		 * 
		 * @since  1.9.0
		 * @access public
		 *
		 * @return bool
		 */
		public function can_cancel( $ret, $subscription ) {

			if (
				in_array( $subscription->gateway, $this->supported_methods, true ) &&
				! empty( $subscription->profile_id ) &&
				'active' === $subscription->status
			) {
				$ret = true;
			}

			return $ret;
		}

		/**
		 * Is Subscription Completed?
		 *
		 * After a sub renewal comes in from Stripe we check to see if total_payments
		 * is greater than or equal to bill_times; if it is, we cancel the stripe sub for the customer.
		 *
		 * @param Give_Subscription $subscription   Subscription object created for Give.
		 * @param int               $total_payments Total payment count.
		 * @param int               $bill_times     Total billed count.
		 *
		 * @return bool
		 */
		public function is_subscription_completed( $subscription, $total_payments, $bill_times ) {

			if ( $total_payments >= $bill_times && $bill_times != 0 ) {
				// Cancel subscription in stripe if the subscription has run its course.
				$is_subscription_cancelled = $this->cancel( $subscription, true );

				if ( $is_subscription_cancelled ) {
					// Complete the subscription w/ the Give_Subscriptions class.
					$subscription->complete();
				}
				return true;
			} else {
				return false;
			}

		}

		/**
		 * Cancels a Stripe Subscription.
		 *
		 * @param  Give_Subscription $subscription Subscription Object.
		 * @param  bool              $now          If false, cancels subscription at end of period,
		 *                                         and If true, cancels immediately. Default true.
		 *
		 * @return bool
		 */
		public function cancel( $subscription, $now = true ) {

			try {

				// Proceed now as Stripe customer id exists.
				$stripe_sub = \Stripe\Subscription::retrieve( $subscription->profile_id );

				if ( $now ) {

					// Cancel Subscription immediately from stripe.
					$stripe_sub->cancel();
				} else {

					// Cancel Subscription after period end from stripe.
					$stripe_sub->cancel_at_period_end = true;
					$stripe_sub->save();
				}

				return true;

			} catch ( \Stripe\Error\Base $e ) {

				// There was an issue cancelling the subscription w/ Stripe.
				give_record_gateway_error(
					__( 'Stripe Error', 'give-recurring' ),
					sprintf(
						/* translators: 1. Error Message. */
						__( 'The Stripe Gateway returned an error while cancelling a subscription. Details: %s', 'give-recurring' ),
						$e->getMessage()
					)
				);
				give_set_error( 'Stripe Error', __( 'An error occurred while cancelling the donation. Please try again.', 'give-recurring' ) );

				return false;

			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error(
					__( 'Stripe Error', 'give-recurring' ),
					sprintf(
						/* translators: 1. Error Message. */
						__( 'The Stripe Gateway returned an error while cancelling a subscription. Details: %s', 'give-recurring' ),
						$e->getMessage()
					)
				);
				give_set_error( 'Stripe Error', __( 'An error occurred while cancelling the donation. Please try again.', 'give-recurring' ) );

				return false;

			} // End try().

		}

		/**
		 * Can update subscription details.
		 *
		 * @since 1.8
		 *
		 * @param bool   $ret
		 * @param object $subscription
		 *
		 * @return bool
		 */
		public function can_update_subscription( $ret, $subscription ) {

			if (
				in_array( $subscription->gateway, $this->supported_methods, true ) &&
				! empty( $subscription->profile_id ) &&
				in_array( $subscription->status, array(
					'active',
				), true )
			) {
				return true;
			}

			return $ret;
		}

		/**
		 * Can Sync.
		 *
		 * @param $ret
		 * @param $subscription
		 *
		 * @return bool
		 */
		public function can_sync( $ret, $subscription ) {

			if (
				in_array( $subscription->gateway, $this->supported_methods, true ) &&
				! empty( $subscription->profile_id ) &&
				'active' === $subscription->status
			) {
				$ret = true;
			}

			return $ret;
		}

		/**
		 * Link the recurring profile in Stripe.
		 *
		 * @since  1.9.0
		 *
		 * @param  string $profile_id   The recurring profile id.
		 * @param  object $subscription The Subscription object.
		 *
		 * @return string               The link to return or just the profile id.
		 */
		public function link_profile_id( $profile_id, $subscription ) {

			if ( ! empty( $profile_id ) ) {
				$payment    = new Give_Payment( $subscription->parent_payment_id );
				$html       = '<a href="%s" target="_blank">' . $profile_id . '</a>';
				$base_url   = 'live' === $payment->mode ? 'https://dashboard.stripe.com/' : 'https://dashboard.stripe.com/test/';
				$link       = esc_url( $base_url . 'subscriptions/' . $profile_id );
				$profile_id = sprintf( $html, $link );
			}

			return $profile_id;

		}

		/**
		 * Create Payment Profiles.
		 *
		 * Setup customers and plans in Stripe for the sign up.
		 * 
		 * @since 1.9.0
		 *
		 * @return void
		 */
		public function create_payment_profiles() {
			$payment_method_id = ! empty( $_POST['give_stripe_payment_method'] ) ? give_clean( $_POST['give_stripe_payment_method'] ) : $this->generate_source_dictionary();
			$email             = $this->purchase_data['user_email'];			
			
			// Add payment method to donation notes and meta.
			give_insert_payment_note( $this->payment_id, 'Stripe Payment Method ID: ' . $payment_method_id );
			give_update_payment_meta( $this->payment_id, '_give_stripe_source_id', $payment_method_id );

			$this->stripe_customer = new Give_Stripe_Customer( $email, $payment_method_id );
			$stripe_customer      = $this->stripe_customer->customer_data;
			$stripe_customer_id   = $this->stripe_customer->get_id();

			// Add donation note for customer ID.
			if ( ! empty( $stripe_customer_id ) ) {
				give_insert_payment_note( $this->payment_id, 'Stripe Customer ID: ' . $stripe_customer_id );
				$this->stripe_gateway->save_stripe_customer_id( $stripe_customer_id, $this->payment_id );
				give_update_meta( $this->payment_id, '_give_stripe_customer_id', $stripe_customer_id );
			}

			$plan_id = $this->get_or_create_stripe_plan( $this->subscriptions );

			// Add donation note for plan ID.
			if ( ! empty( $plan_id ) ) {
				give_insert_payment_note( $this->payment_id, 'Stripe Plan ID: ' . $plan_id );
			}

			// Save plan id to donation.
			give_update_meta( $this->payment_id, '_give_stripe_plan_id', $plan_id );

			$subscription  = $this->subscribe_customer_to_plan( $stripe_customer, $payment_method_id, $plan_id );
			
		}

		/**
		 * Gets a stripe plan if it exists otherwise creates a new one.
		 *
		 * @param  array  $subscription The subscription array set at process_checkout before creating payment profiles.
		 * @param  string $return       if value 'id' is passed it returns plan ID instead of Stripe_Plan.
		 *
		 * @return string|\Stripe\Plan
		 */
		public function get_or_create_stripe_plan( $subscription, $return = 'id' ) {

			$stripe_plan_name = give_recurring_generate_subscription_name( $subscription['form_id'], $subscription['price_id'] );
			$stripe_plan_id   = $this->generate_stripe_plan_id( $stripe_plan_name, give_maybe_sanitize_amount( $subscription['recurring_amount'] ), $subscription['period'], $subscription['frequency'] );

			try {
				// Check if the plan exists already.
				$stripe_plan = \Stripe\Plan::retrieve( $stripe_plan_id );

			} catch ( Exception $e ) {

				// The plan does not exist, please create a new plan.
				$args = array(
					'amount'         => give_stripe_dollars_to_cents( $subscription['recurring_amount'] ),
					'interval'       => $subscription['period'],
					'interval_count' => $subscription['frequency'],
					'currency'       => give_get_currency(),
					'id'             => $stripe_plan_id,
				);

				// Create a Subscription Product Object and Pass plan parameters as per the latest version of stripe api.
				$args['product'] = \Stripe\Product::create( array(
					'name'                 => $stripe_plan_name,
					'statement_descriptor' => give_stripe_get_statement_descriptor( $subscription ),
					'type'                 => 'service',
				) );

				$stripe_plan = $this->create_stripe_plan( $args );

			}

			if ( 'id' == $return ) {
				return $stripe_plan->id;
			} else {
				return $stripe_plan;
			}

		}

		/**	
		 * Creates a Stripe Plan using the API.	
		 *	
		 * @param  array $args	
		 * 
		 * @since 1.9.0
		 * @access public
		 *	
		 * @return bool|\Stripe\Plan	
		 */	
		public function create_stripe_plan( $args = array() ) {	
			
			$stripe_plan = false;	
			
			try {	
				$stripe_plan = \Stripe\Plan::create( $args );	
			} catch ( \Stripe\Error\Base $e ) {	
				// There was an issue creating the Stripe plan.	
				Give_Stripe_Logger::log_error( $e, $this->id );	
			} catch ( Exception $e ) {	
				
				// Something went wrong outside of Stripe.	
				give_record_gateway_error( __( 'Stripe Error', 'give-recurring' ), sprintf( __( 'The Stripe Gateway returned an error while creating a plan. Details: %s', 'give-recurring' ), $e->getMessage() ) );	
				give_set_error( 'Stripe Error', __( 'An error occurred while processing the donation. Please try again.', 'give-recurring' ) );	
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );	
			}

			return $stripe_plan;	
		}

		/**
		 * Generates a plan ID to be used with Stripe.
		 *
		 * @param  string $subscription_name Name of the subscription generated from
		 *                                   give_recurring_generate_subscription_name.
		 * @param  string $recurring_amount  Recurring amount specified in the form.
		 * @param  string $period            Can be either 'day', 'week', 'month' or 'year'. Set from form.
		 * @param  int    $frequency         Can be either 1,2,..6 Set from form.
		 *
		 * @return string
		 */
		public function generate_stripe_plan_id( $subscription_name, $recurring_amount, $period, $frequency ) {
			$subscription_name = sanitize_title( $subscription_name );

			return sanitize_key( $subscription_name . '_' . $recurring_amount . '_' . $period . '_' . $frequency );
		}

		/**
		 * Subscribes a Stripe Customer to a plan.
		 *
		 * @param  \Stripe\Customer      $stripe_customer Stripe Customer Object.
		 * @param  string|\Stripe\Source $source          Stripe Source ID/Object.
		 * @param  string                $plan_id         Stripe Plan ID.
		 *
		 * @return bool|\Stripe\Subscription
		 */
		public function subscribe_customer_to_plan( $stripe_customer, $source, $plan_id ) {

			if ( $stripe_customer instanceof \Stripe\Customer ) {

				try {

					$default_source_id = $this->stripe_customer->is_card_exists && ! empty( $this->stripe_customer->customer_data->default_source )
						? $this->stripe_customer->customer_data->default_source
						: $source;

					// Get metadata.
					$metadata = give_stripe_prepare_metadata( $this->payment_id, $this->purchase_data );

					$args = array(
						'customer' => $stripe_customer->id,
						'items'    => array(
							array(
								'plan' => $plan_id,
							),
						),
						'metadata' => $metadata,
					);

					// Set Application Information.
					give_stripe_set_app_info();

					$subscription                      = \Stripe\Subscription::create( $args, give_stripe_get_connected_account_options() );
					$this->subscriptions['profile_id'] = $subscription->id;

					// Need additional authentication steps as subscription is still incomplete.
					if ( ! give_stripe_is_checkout_enabled() && 'incomplete' ===  $subscription->status ) {

						// Verify the initial payment with invoice created during subscription.
						$invoice = $this->stripe_invoice->retrieve( $subscription->latest_invoice );

						// Set Payment Intent ID.
						give_insert_payment_note( $this->payment_id, 'Stripe Charge/Payment Intent ID: ' . $invoice->payment_intent );

						// Retrieve payment intent details.
						$intent_details = $this->stripe_payment_intent->retrieve( $invoice->payment_intent );

						$confirm_args = array(
							'return_url' => give_get_success_page_uri(),
						);

						if (
							give_stripe_is_source_type( $default_source_id, 'tok' ) ||
							give_stripe_is_source_type( $default_source_id, 'src' )
						) {
							$confirm_args['source'] = $default_source_id;
						} elseif ( give_stripe_is_source_type( $default_source_id, 'pm' ) ) {
							$confirm_args['payment_method'] = $default_source_id;
						}

						$intent_details->confirm( $confirm_args );

						// Record the subscription in Give.
						$this->record_signup();

						// Process additional authentication steps for SCA or 3D secure.
						give_stripe_process_additional_authentication( $this->payment_id, $intent_details );
					}

					return $subscription;

				} catch ( Exception $e ) {

					// Something went wrong outside of Stripe.
					give_record_gateway_error(
						__( 'Stripe Error', 'give-recurring' ),
						sprintf(
							/* translators: %s Exception Message. */
							__( 'An error while subscribing a customer to a plan. Details: %s', 'give-recurring' ),
							$e->getMessage()
						)
					);
					give_set_error( 'Stripe Error', __( 'An error occurred while processing the donation. Please try again.', 'give-recurring' ) );
					give_send_back_to_checkout( '?payment-mode=stripe' );

				} // End try().
			} // End if().

			return false;
		}
	}
}