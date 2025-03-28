<?php
/**
 * Give Recurring - Add support for Stripe BECS Direct Debit
 *
 * @since 1.10.1
 *
 * @package    Give-Recurring
 * @subpackage Stripe
 * @copyright  Copyright (c) 2020, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

use GiveRecurring\Gateways\Stripe\Plan;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Stripe_Becs
 *
 * @since 1.10.1
 */
class Give_Recurring_Stripe_Becs extends Give_Recurring_Gateway {

	/**
	 * Invoice Object.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @var $invoice
	 */
	public $invoice;

	/**
	 * Payment Intent Object.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @var $payment_intent
	 */
	public $payment_intent;

	/**
	 * Call Give Stripe Plan Class for processing recurring donations.
	 *
	 * @since  1.10.3
     * @access public
	 *
	 * @var $plan
	 */
	public $plan;

	/**
	 * Call Give Stripe Plan Class for processing recurring donations.
	 *
	 * @since  1.10.3
     * @access public
	 *
	 * @var $subscription
	 */
	public $subscription;

	/**
	 * Get Stripe Started.
	 *
	 * @since 1.10.1
	 *
	 * @return void
	 */
	public function init() {

		$this->id = 'stripe_becs';

		if (
			defined( 'GIVE_STRIPE_VERSION' ) &&
			version_compare( GIVE_STRIPE_VERSION, '2.2.0', '<' )
		) {
			add_action( 'admin_notices', array( $this, 'old_api_upgrade_notice' ) );

			// No Stripe SDK. Bounce.
			return;
		}

		// Bailout, if gateway is not active.
		if ( ! give_is_gateway_active( $this->id ) ) {
			return;
		}

		$this->stripe_gateway = new Give_Stripe_Gateway();
		$this->invoice        = new Give_Stripe_Invoice();
		$this->payment_intent = new Give_Stripe_Payment_Intent();
		$this->plan           = new Plan();
		$this->subscription   = new Give_Recurring_Stripe_Subscription();

		add_action( 'give_pre_refunded_payment', array( $this, 'process_refund' ) );
		add_action( 'give_recurring_cancel_stripe_becs_subscription', array( $this, 'cancel' ), 10, 2 );
	}

	/**
	 * Upgrade notice.
	 *
	 * Tells the admin that they need to upgrade the Stripe gateway.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return void
	 */
	public function old_api_upgrade_notice() {

		$message = sprintf(
		/* translators: 1. GiveWP account login page, 2. GiveWP Account downloads page */
			__( '<strong>Attention:</strong> The Recurring Donations plugin requires the latest version of the Stripe gateway add-on to process donations properly. Please update to the latest version of Stripe to resolve this issue. If your license is active you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%1$s" target="_blank">your downloads</a> page on the Give website.', 'give-recurring' ),
			'https://givewp.com/wp-login.php',
			'https://givewp.com/my-account/#tab_downloads'
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
	 * Create Payment Profiles.
	 *
	 * Setup customers and plans in Stripe for the sign up.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return void
	 */
	public function create_payment_profiles() {

		$payment_method_id = ! empty( $_POST['give_stripe_payment_method'] ) ? give_clean( $_POST['give_stripe_payment_method'] ) : $this->generate_source_dictionary();
		$email  = $this->purchase_data['user_email'];

		$payment_method = $this->stripe_gateway->payment_method->retrieve( $payment_method_id );

		// Add source to donation notes and meta.
		give_insert_payment_note( $this->payment_id, 'Stripe Source ID: ' . $payment_method_id );
		give_update_payment_meta( $this->payment_id, '_give_stripe_source_id', $payment_method_id );

		$this->stripe_customer = new Give_Stripe_Customer( $email, $payment_method_id );
		$stripe_customer       = $this->stripe_customer->customer_data;
		$stripe_customer_id    = $this->stripe_customer->get_id();

		// Add donation note for customer ID.
		if ( ! empty( $stripe_customer_id ) ) {
			give_insert_payment_note( $this->payment_id, 'Stripe Customer ID: ' . $stripe_customer_id );

			// Save Stripe Customer ID into Donor meta.
			$this->stripe_gateway->save_stripe_customer_id( $stripe_customer_id, $this->payment_id );

			// Save customer id to donation.
			give_update_meta( $this->payment_id, '_give_stripe_customer_id', $stripe_customer_id );
		}

		$plan_id = $this->get_or_create_stripe_plan( $this->subscriptions );

		// Add donation note for plan ID.
		if ( ! empty( $plan_id ) ) {
			give_insert_payment_note( $this->payment_id, 'Stripe Plan ID: ' . $plan_id );

			// Save plan id to donation.
			give_update_meta( $this->payment_id, '_give_stripe_plan_id', $plan_id );
		}

		$subscription  = $this->subscribe_customer_to_plan( $stripe_customer, $payment_method, $plan_id );
	}

	/**
	 * Subscribes a Stripe Customer to a plan.
	 *
	 * @param  \Stripe\Customer      $stripe_customer Stripe Customer Object.
	 * @param  string|\Stripe\Source $source          Stripe Source ID/Object.
	 * @param  string                $plan_id         Stripe Plan ID.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return bool|\Stripe\Subscription
	 */
	public function subscribe_customer_to_plan( $stripe_customer, $source, $plan_id ) {

		if ( $stripe_customer instanceof \Stripe\Customer ) {
			try {

				$default_source_id = $this->stripe_customer->is_card_exists
					? $this->stripe_customer->customer_data->default_source
					: $source->id;

				// Get metadata.
				$metadata = give_stripe_prepare_metadata( $this->payment_id, $this->purchase_data );

				$setup_intent = \Stripe\SetupIntent::create([
					'payment_method_types' => [ 'au_becs_debit' ],
					'confirm'              => true,
					'customer'             => $stripe_customer->id,
					'payment_method'       => $default_source_id,
					'usage'                => 'off_session',
					'mandate_data'         => [
						'customer_acceptance' => [
							'type'   => 'online',
							'online' => [
								'ip_address' => give_stripe_get_ip_address(),
								'user_agent' => give_get_user_agent(),
							],
						],
					],
				]);

				// Set Setup Intent ID.
				give_insert_payment_note( $this->payment_id, 'Stripe Setup Intent ID: ' . $setup_intent->id );
				give_insert_payment_note( $this->payment_id, 'Stripe Mandate ID: ' . $setup_intent->mandate );

				$args     = array(
					'customer'               => $stripe_customer->id,
					'items'                  => array(
						array(
							'plan' => $plan_id,
						),
					),
					'metadata'               => $metadata,
					'payment_behavior'       => 'allow_incomplete',
					'default_payment_method' => $default_source_id,
				);

				$args['default_payment_method'] = $default_source_id;

				$subscription                      = \Stripe\Subscription::create( $args, give_stripe_get_connected_account_options() );
				$this->subscriptions['profile_id'] = $subscription->id;

				// Verify the initial payment with invoice created during subscription.
				$invoice = $this->invoice->retrieve( $subscription->latest_invoice );

				// Set Payment Intent ID.
				give_insert_payment_note( $this->payment_id, 'Stripe Payment Intent ID: ' . $invoice->payment_intent );
				give_set_payment_transaction_id( $this->payment_id, $invoice->payment_intent );

				return $subscription;
			} catch ( \Stripe\Error\Base $e ) {

				// There was an issue subscribing the Stripe customer to a plan.
				Give_Stripe_Logger::log_error( $e, $this->id );
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

	/**
	 * Gets a stripe plan if it exists otherwise creates a new one.
	 *
	 * @param  array  $subscription The subscription array set at process_checkout before creating payment profiles.
	 * @param  string $return       if value 'id' is passed it returns plan ID instead of Stripe_Plan.
	 *
	 * @since  1.10.1
	 * @access public
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
	 * @param array $args List of arguments.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return bool|\Stripe\Plan
	 */
	private function create_stripe_plan( $args = array() ) {

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
			give_send_back_to_checkout( '?payment-mode=stripe' );
		}

		return $stripe_plan;
	}

	/**
	 * Refund subscription charges and cancels the subscription if the parent donation triggered when refunding in wp-admin donation details.
	 *
	 * @param $payment Give_Payment Give Payment.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return void
	 */
	public function process_refund( $payment ) {

		if ( empty( $_POST['give_refund_in_stripe'] ) ) {
			return;
		}

		$statuses = array( 'give_subscription', 'publish' );
		$transaction_id = $payment->transaction_id;

		if ( ! in_array( $payment->old_status, $statuses ) ) {
			return;
		}

		if ( 'stripe_becs' !== $payment->gateway ) {
			return;
		}

		switch ( $payment->old_status ) {

			case 'give_subscription' :

				// Refund renewal payment
				if ( empty( $transaction_id ) || $transaction_id == $payment->ID ) {

					// No valid charge ID
					return;
				}

				try {

					$args = [
						'charge' => $transaction_id,
					];

					// If the donation is processed with payment intent then refund using payment intent.
					if ( give_stripe_is_source_type( $transaction_id, 'pi' ) ) {
						$args = [
							'payment_intent' => $transaction_id,
						];
					}

					$refund = \Stripe\Refund::create( $args );

					$payment->add_note( sprintf( __( 'Charge %1$s refunded in Stripe. Refund ID: %1$s', 'give-recurring' ), $payment->transaction_id, $refund->id ) );

				} catch ( Exception $e ) {

					// some sort of other error
					$body = $e->getJsonBody();
					$err  = $body['error'];

					if ( isset( $err['message'] ) ) {
						$error = $err['message'];
					} else {
						$error = __( 'Something went wrong while refunding the charge in Stripe.', 'give-recurring' );
					}

					wp_die( $error, __( 'Error', 'give-recurring' ), array(
						'response' => 400,
					) );

				}

				break;

			case 'publish' :

				// Refund & cancel initial subscription donation.
				$db   = new Give_Subscriptions_DB();
				$subs = $db->get_subscriptions( array(
					'parent_payment_id' => $payment->ID,
					'number'            => 100,
				) );

				if ( empty( $subs ) ) {
					return;
				}

				foreach ( $subs as $subscription ) {

					try {

						$refund = \Stripe\Refund::create( array(
							'charge' => $subscription->transaction_id,
						) );

						$payment->add_note( sprintf( __( 'Charge %s refunded in Stripe.', 'give-recurring' ), $subscription->transaction_id ) );
						$payment->add_note( sprintf( __( 'Charge %1$s refunded in Stripe. Refund ID: %1$s', 'give-recurring' ), $subscription->transaction_id, $refund->id ) );

					} catch ( Exception $e ) {

						// some sort of other error
						$body = $e->getJsonBody();
						$err  = $body['error'];

						if ( isset( $err['message'] ) ) {
							$error = $err['message'];
						} else {
							$error = __( 'Something went wrong while refunding the charge in Stripe.', 'give-recurring' );
						}

						$payment->add_note( sprintf( __( 'Charge %1$s could not be refunded in Stripe. Error: %1$s', 'give-recurring' ), $subscription->transaction_id, $error ) );

					}

					// Cancel subscription.
					$this->cancel( $subscription, false );
					$subscription->cancel();
					$payment->add_note( sprintf( __( 'Subscription %d cancelled.', 'give-recurring' ), $subscription->id ) );

				}

				break;

		}// End switch().

	}

	/**
	 * Generates source dictionary, used for testing purpose only.
	 *
	 * @param array $card_info Card Information.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return array
	 */
	public function generate_source_dictionary( $card_info = array() ) {

		if ( empty( $card_info ) ) {
			$card_info = $this->purchase_data['card_info'];
		}

		$card_info = array_map( 'trim', $card_info );
		$card_info = array_map( 'strip_tags', $card_info );

		return array(
			'object'    => 'card',
			'exp_month' => $card_info['card_exp_month'],
			'exp_year'  => $card_info['card_exp_year'],
			'number'    => $card_info['card_number'],
			'cvc'       => $card_info['card_cvc'],
			'name'      => $card_info['card_name'],
		);
	}

	/**
	 * Can update subscription CC details.
	 *
	 * @param bool   $ret
	 * @param object $subscription
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return bool
	 */
	public function can_update( $ret, $subscription ) {

		if (
			'stripe_becs' === $subscription->gateway &&
			! empty( $subscription->profile_id ) &&
			in_array( $subscription->status, [ 'active', 'failing' ], true )
		) {
			return true;
		}

		return $ret;
	}

	/**
	 * Stripe Recurring Customer ID.
	 *
	 * The Give Stripe gateway stores it's own customer_id so this method first checks for that, if it exists.
	 * If it does it will return that value. If it does not it will return the recurring gateway value.
	 *
	 * @param string $user_email Donor Email.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return string The donor's Stripe customer ID.
	 */
	public function get_stripe_recurring_customer_id( $user_email ) {

		// First check user meta to see if they have made a previous donation
		// w/ Stripe via non-recurring donation so we don't create a duplicate Stripe customer for recurring.
		$customer_id = give_stripe_get_customer_id( $user_email );

		// If no data found check the subscribers profile to see if there's a recurring ID already.
		if ( empty( $customer_id ) ) {

			$subscriber = new Give_Recurring_Subscriber( $user_email );

			$customer_id = $subscriber->get_recurring_donor_id( $this->id );
		}

		return $customer_id;

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
	 * @since  1.10.1
	 * @access public
	 *
	 * @return string
	 */
	public function generate_stripe_plan_id( $subscription_name, $recurring_amount, $period, $frequency ) {
		$subscription_name = sanitize_title( $subscription_name );

		return sanitize_key( $subscription_name . '_' . $recurring_amount . '_' . $period . '_' . $frequency );
	}

	/**
	 * Get Stripe Subscription.
	 *
	 * @param string $stripe_subscription_id Stripe Subscription ID.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return mixed
	 */
	public function get_stripe_subscription( $stripe_subscription_id ) {

		$stripe_subscription = \Stripe\Subscription::retrieve( $stripe_subscription_id );

		return $stripe_subscription;
	}

	/**
	 * Get gateway subscription.
	 *
	 * @param $subscription
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return bool|mixed
	 */
	public function get_gateway_subscription( $subscription ) {

		if ( $subscription instanceof Give_Subscription ) {

			$stripe_subscription_id = $subscription->profile_id;

			$stripe_subscription = $this->get_stripe_subscription( $stripe_subscription_id );

			return $stripe_subscription;
		}

		return false;
	}

	/**
	 * Get subscription details.
	 *
	 * @param Give_Subscription $subscription
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return array|bool
	 */
	public function get_subscription_details( $subscription ) {

		$stripe_subscription = $this->get_gateway_subscription( $subscription );
		if ( false !== $stripe_subscription ) {

			$subscription_details = array(
				'status'         => $stripe_subscription->status,
				'created'        => $stripe_subscription->created,
				'billing_period' => $stripe_subscription->plan->interval,
				'frequency'      => $stripe_subscription->plan->interval_count,
			);

			return $subscription_details;
		}

		return false;
	}

	/**
	 * Get transactions.
	 *
	 * @param Give_Subscription $subscription
	 * @param string            $date
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return array
	 */
	public function get_gateway_transactions( $subscription, $date = '' ) {

		$subscription_invoices = $this->get_invoices_for_give_subscription( $subscription, $date = '' );
		$transactions          = array();

		foreach ( $subscription_invoices as $invoice ) {

			$transactions[] = array(
				'amount'         => give_stripe_cents_to_dollars( $invoice->amount_due ),
				'date'           => $invoice->created,
				'transaction_id' => $invoice->charge,
			);
		}

		return $transactions;
	}

	/**
	 * Get invoices for a Give subscription.
	 *
	 * @param Give_Subscription $subscription
	 * @param string            $date
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return array
	 */
	private function get_invoices_for_give_subscription( $subscription, $date = '' ) {
		$subscription_invoices = array();

		if ( $subscription instanceof Give_Subscription ) {

			$stripe_subscription_id = $subscription->profile_id;
			$stripe_customer_id     = $this->get_stripe_recurring_customer_id( $subscription->donor->email );
			$subscription_invoices  = $this->get_invoices_for_subscription( $stripe_customer_id, $stripe_subscription_id, $date );
		}

		return $subscription_invoices;
	}

	/**
	 * Get invoices for subscription.
	 *
	 * @param string $stripe_customer_id     Stripe Customer ID.
	 * @param string $stripe_subscription_id Stripe Subscription ID.
	 * @param string $date                   Date.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return array
	 */
	public function get_invoices_for_subscription( $stripe_customer_id, $stripe_subscription_id, $date ) {
		$subscription_invoices = array();
		$invoices              = $this->get_invoices_for_customer( $stripe_customer_id, $date );

		foreach ( $invoices as $invoice ) {
			if ( $invoice->subscription == $stripe_subscription_id ) {
				$subscription_invoices[] = $invoice;
			}
		}

		return $subscription_invoices;
	}

	/**
	 * Get invoices for Stripe customer.
	 *
	 * @param string $stripe_customer_id
	 * @param string $date
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return array|bool
	 */
	private function get_invoices_for_customer( $stripe_customer_id = '', $date = '' ) {
		$args     = array(
			'limit' => 100,
			'status' => 'paid'
		);
		$has_more = true;
		$invoices = array();

		if ( ! empty( $date ) ) {
			$date_timestamp = strtotime( $date );
			$args['date']   = array(
				'gte' => $date_timestamp,
			);
		}

		if ( ! empty( $stripe_customer_id ) ) {
			$args['customer'] = $stripe_customer_id;
		}

		while ( $has_more ) {
			try {
				$collection             = \Stripe\Invoice::all( $args );
				$invoices               = array_merge( $invoices, $collection->data );
				$has_more               = $collection->has_more;
				$last_obj               = end( $invoices );
				$args['starting_after'] = $last_obj->id;

			} catch ( \Stripe\Error\Base $e ) {

				Give_Stripe_Logger::log_error( $e, $this->id );

				return false;

			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error( __( 'Stripe Error', 'give-recurring' ), sprintf( __( 'The Stripe Gateway returned an error while getting invoices a Stripe customer. Details: %s', 'give-recurring' ), $e->getMessage() ) );

				return false;

			}
		}

		return $invoices;
	}

	/**
	 * Process the update payment form.
	 *
	 * @param  Give_Recurring_Subscriber $subscriber   Give_Recurring_Subscriber
	 * @param  Give_Subscription         $subscription Give_Subscription
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return void
	 */
	public function update_subscription( $subscriber, $subscription ) {
		// Sanitize the values submitted with donation form.
		$postedData = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

		// Get update renewal amount.
		$renewalAmount = $this->getNewRenewalAmount();

		// Is errors?
		$errors = give_get_errors();

		if ( empty( $errors ) ) {
			$this->update_subscription_plan( $subscription, $renewalAmount );
		}
	}

	/**
	 * Can update subscription details.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @param bool   $ret
	 * @param object $subscription
	 *
	 * @return bool
	 */
	public function can_update_subscription( $ret, $subscription ) {

		if (
			'stripe_becs' === $subscription->gateway &&
			! empty( $subscription->profile_id ) &&
			in_array( $subscription->status, [ 'active' ], true )
		) {
			return true;
		}

		return $ret;
	}

	/**
	 * Outputs the payment method update form.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @param  Give_Subscription $subscription The subscription object.
	 *
	 * @return void
	 */
	public function update_payment_method_form( $subscription ) {

		// Bailout, if not BECS Direct Debit gateway.
		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		$form_id          = ( isset( $subscription->form_id ) && ! empty( $subscription->form_id ) ) ? absint( $subscription->form_id ) : 0;
		$id_prefix        = "{$form_id}-1";
		$args             = [
            'id_prefix' => $id_prefix,
            'form_id' => $form_id,
        ];
		$publishable_key  = give_stripe_get_publishable_key( $form_id );
		$secret_key       = give_stripe_get_secret_key( $form_id );
		$donation_details = give_get_payment_by( 'id', $subscription->parent_payment_id );
		$first_name       = $donation_details->first_name;
		$last_name        = $donation_details->last_name;
		$email            = $donation_details->email;

		ob_start();
		?>
		<fieldset id="give_cc_fields" class="give-do-validate">
				<legend>
					<?php esc_attr_e( 'Bank Account Info', 'give-recurring' ); ?>
				</legend>

				<?php
				if ( is_ssl() ) {
					?>
					<div id="give_secure_site_wrapper">
						<span class="give-icon padlock"></span>
						<span><?php esc_attr_e( 'This is a secure SSL encrypted payment.', 'give-recurring' ); ?></span>
					</div>
					<?php
				}

				if (
					! is_ssl() &&
					! give_is_test_mode() &&
					(
						empty( $publishable_key ) ||
						empty( $secret_key )
					)
				) {
					Give()->notices->print_frontend_notice(
						sprintf(
							'<strong>%1$s</strong> %2$s',
							esc_html__( 'Notice:', 'give-recurring' ),
							esc_html__( 'Mandate form fields are disabled because Stripe is not connected and your site is not running securely over HTTPS.', 'give-recurring' )
						)
					);
				} elseif (
					empty( $publishable_key ) ||
					empty( $secret_key )
				) {
					Give()->notices->print_frontend_notice(
						sprintf(
							'<strong>%1$s</strong> %2$s',
							esc_html__( 'Notice:', 'give-recurring' ),
							esc_html__( 'Mandate form fields are disabled because Stripe is not connected.', 'give-recurring' )
						)
					);
				} elseif ( ! is_ssl() && ! give_is_test_mode() ) {
					Give()->notices->print_frontend_notice(
						sprintf(
							'<strong>%1$s</strong> %2$s',
							esc_html__( 'Notice:', 'give-recurring' ),
							esc_html__( 'Mandate form fields are disabled because your site is not running securely over HTTPS.', 'give-recurring' )
						)
					);
				} else {
					?>
					<div id="give-bank-account-number-wrap" class="form-row form-row-responsive give-stripe-cc-field-wrap">
						<label for="give-bank-account-number-field-<?php echo $id_prefix; ?>" class="give-label">
							<?php echo __( 'Bank Account', 'give-recurring' ); ?>
							<span class="give-required-indicator">*</span>
							<span class="give-tooltip give-icon give-icon-question" data-tooltip="The (typically) 16 digits on the front of your credit card."></span>
						</label>
						<div
							id="give-stripe-becs-fields-<?php echo $id_prefix; ?>"
							class="give-stripe-becs-bank-account-field give-stripe-cc-field"
							data-hide_icon="<?php echo give_stripe_becs_hide_icon( $form_id ); ?>"
							data-icon_style="<?php echo give_stripe_get_becs_icon_style( $form_id ); ?>"
						></div>
						<input type="hidden" name="give_first" value="<?php echo $first_name; ?>"/>
						<input type="hidden" name="give_last" value="<?php echo $last_name; ?>"/>
						<input type="hidden" name="give_email" value="<?php echo $email; ?>"/>
					</div>
					<div class="form-row form-row-responsive give-stripe-becs-mandate-acceptance-text">
						<?php
						if ( give_is_setting_enabled( give_get_option( 'stripe_becs_mandate_acceptance_option', 'enabled' ) ) ) {
							echo give_stripe_get_mandate_acceptance_text( 'becs' );
						}
						?>
					</div>
					<?php
					/**
					 * This action hook is used to display content after the Credit Card expiration field.
					 *
					 * Note: Kept this hook as it is.
					 *
					 * @param int   $form_id Donation Form ID.
					 * @param array $args    List of additional arguments.
					 *
					 * @since 2.5.0
					 */
					do_action( 'give_after_cc_expiration', $form_id, $args );

					/**
					 * This action hook is used to display content after the Credit Card expiration field.
					 *
					 * @param int   $form_id Donation Form ID.
					 * @param array $args    List of additional arguments.
					 *
					 * @since 2.5.0
					 */
					do_action( 'give_stripe_after_cc_expiration', $form_id, $args );
				}
				?>
			</fieldset>
			<?php
		echo ob_get_clean();
	}

	/**
	 * Process the update payment form
	 *
     * @param  Give_Recurring_Subscriber $subscriber   Give_Recurring_Subscriber.
	 * @param  Give_Subscription         $subscription Give_Subscription.
     *
	 * @since  1.10.1
     * @access public
	 *
	 * @return void
	 */
	public function update_payment_method( $subscriber, $subscription ) {

		// Check for any existing errors.
		$errors    = give_get_errors();
		$post_data = give_clean( $_POST );
		$form_id   = ! empty( $subscription->form_id ) ? $subscription->form_id : false;

		// Set App info.
		give_stripe_set_app_info( $form_id );

		// If not errors present, then proceed.
		if ( empty( $errors ) ) {

			$source_id   = ! empty( $post_data['give_stripe_payment_method'] ) ? $post_data['give_stripe_payment_method'] : 0;
			$customer_id = Give()->donor_meta->get_meta( $subscriber->id, give_stripe_get_customer_key(), true );

			// We were unable to retrieve the customer ID from meta so let's pull it from the API
			try {
				$stripe_subscription = \Stripe\Subscription::retrieve( $subscription->profile_id );
			} catch ( Exception $e ) {
				give_set_error( 'give_recurring_stripe_error', $e->getMessage() );
				return;
			}

			// If customer id doesn't exist, take the customer id from subscription.
			if ( empty( $customer_id ) ) {
				$customer_id = $stripe_subscription->customer;
			}

			try {
				$stripe_customer = \Stripe\Customer::retrieve( $customer_id );
			} catch ( Exception $e ) {
				give_set_error( 'give-recurring-stripe-customer-retrieval-error', $e->getMessage() );
				return;
			}

			// No errors while processing Stripe BECS, continue on through processing.
			try {

				// Fetch payment method details.
				$stripe_payment_method = new Give_Stripe_Payment_Method();

				if ( $source_id ) {
					if ( give_stripe_is_source_type( $source_id, 'pm' ) ) {

						$payment_method = $stripe_payment_method->retrieve( $source_id );

						// Set Card ID as default payment method to customer and subscription.
						$payment_method->attach( array(
							'customer' => $stripe_customer->id,
						) );

						// Set default payment method for subscription.
						\Stripe\Subscription::update(
							$subscription->profile_id,
							array(
								'default_payment_method' => $source_id,
							)
						);
					} else {
						$card = $stripe_customer->sources->create( array( 'source' => $source_id ) );
						$stripe_customer->default_source = $card->id;

						// Set default source for subscription.
						\Stripe\Subscription::update(
							$subscription->profile_id,
							array(
								'default_source' => $source_id,
							)
						);
					}

				} elseif ( ! empty( $post_data['give_stripe_existing_card'] ) ) {
					if ( give_stripe_is_source_type( $post_data['give_stripe_existing_card'], 'pm' ) ) {

						$payment_method = $stripe_payment_method->retrieve( $post_data['give_stripe_existing_card'] );
						$payment_method->attach( array(
							'customer' => $stripe_customer->id,
						) );

						// Set default payment method for subscription.
						\Stripe\Subscription::update(
							$subscription->profile_id,
							array(
								'default_payment_method' => $post_data['give_stripe_existing_card'],
							)
						);
					} else {
						$stripe_customer->default_source     = $post_data['give_stripe_existing_card'];

						// Set default source for subscription.
						\Stripe\Subscription::update(
							$subscription->profile_id,
							array(
								'default_source' => $post_data['give_stripe_existing_card'],
							)
						);
					}
				}

				// Save the updated subscription details.
				$stripe_subscription->save();

				// Save the updated customer details.
				$stripe_customer->save();

			} catch ( \Stripe\Error\Card $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if ( isset( $err['message'] ) ) {
					give_set_error( 'payment_error', $err['message'] );
				} else {
					give_set_error( 'payment_error', __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'give-recurring' ) );
				}

			} catch ( \Stripe\Error\ApiConnection $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if ( isset( $err['message'] ) ) {
					give_set_error( 'payment_error', $err['message'] );
				} else {
					give_set_error( 'payment_error', __( 'There was an error processing your payment (Stripe\'s API is down), please try again', 'give-recurring' ) );
				}

			} catch ( \Stripe\Error\InvalidRequest $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				// Bad Request of some sort. Maybe Christoff was here ;)
				if ( isset( $err['message'] ) ) {
					give_set_error( 'request_error', $err['message'] );
				} else {
					give_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'give-recurring' ) );
				}

			} catch ( \Stripe\Error\Api $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if ( isset( $err['message'] ) ) {
					give_set_error( 'request_error', $err['message'] );
				} else {
					give_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'give-recurring' ) );
				}

			} catch ( \Stripe\Error\Authentication $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				// Authentication error. Stripe keys in settings are bad.
				if ( isset( $err['message'] ) ) {
					give_set_error( 'request_error', $err['message'] );
				} else {
					give_set_error( 'api_error', __( 'The API keys entered in settings are incorrect', 'give-recurring' ) );
				}

			} catch ( Exception $e ) {
				give_set_error( 'update_error', __( 'There was an error with this payment method. Please try with another card.', 'give-recurring' ) );
			}

		}

	}

	/**
	 * Update Stripe Subscription plan.
	 *
	 * @param \Give_Subscription $subscription
	 * @param int                $renewal_amount
	 *
	 * @since  1.10.1
	 * @access public
	 */
	private function update_subscription_plan( $subscription, $renewal_amount ) {
		$stripe_plan_name = give_recurring_generate_subscription_name( $subscription->form_id, $subscription->price_id );
		$stripe_plan_id   = $this->generate_stripe_plan_id( $stripe_plan_name, $renewal_amount, $subscription->period, $subscription->frequency );

		try {
			$stripe_plan = $this->plan->retrieve( $stripe_plan_id );

			// If Plan not found, then create one.
			if ( empty( $stripe_plan ) ) {
				// The plan does not exist, please create a new plan.
				$args = array(
					'amount'         => give_stripe_dollars_to_cents( $renewal_amount ),
					'interval'       => $subscription->period,
					'interval_count' => $subscription->frequency,
					'currency'       => give_get_currency(),
					'id'             => $stripe_plan_id,
				);

				// Create a Subscription Product Object and Pass plan parameters as per the latest version of stripe api.
				$args['product'] = \Stripe\Product::create( array(
					'name'                 => $stripe_plan_name,
					'statement_descriptor' => give_stripe_get_statement_descriptor( $subscription ),
					'type'                 => 'service',
				) );

				$stripe_plan = $this->plan->create( $args );
			}

			if ( ! empty( $stripe_plan->id ) ) {
				$stripe_subscription = $this->subscription->retrieve( $subscription->profile_id );

				if (
					isset( $stripe_subscription->items->data[0]->id )
					&& isset( $stripe_plan->id )
				) {
					$stripe_subscription->update( $subscription->profile_id, array(
							'items'   => array(
								array(
									'id'   => $stripe_subscription->items->data[0]->id,
									'plan' => $stripe_plan->id
								)
							),
							'prorate' => false,
						)
					);

					$stripe_subscription->save();
				} else {
					give_set_error(
                        'give_recurring_stripe_update_subscription',
                        esc_html__( 'The Stripe gateway returned an error while updating the subscription.', 'give-recurring' )
                    );
				}
			}
		} catch ( Exception $e ) {
			give_set_error(
                'give_recurring_stripe_retrieving_plan',
                esc_html__( 'The Stripe gateway returned an error while retrieving the plan.', 'give-recurring' )
            );
		}
	}

	/**
	 * Can Cancel.
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if (
			'stripe_becs' === $subscription->gateway &&
			! empty( $subscription->profile_id ) &&
			'active' === $subscription->status
		) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Can Sync.
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return bool
	 */
	public function can_sync( $ret, $subscription ) {

		if (
			$subscription->gateway === $this->id
			&& ! empty( $subscription->profile_id )
		) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Cancels a Stripe Subscription.
	 *
	 * @param  Give_Subscription $subscription
	 * @param  bool              $valid
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return bool
	 */
	public function cancel( $subscription, $valid ) {

		if ( empty( $valid ) ) {
			return false;
		}

		try {

			// Get the Stripe customer ID.
			$stripe_customer_id = $this->get_stripe_recurring_customer_id( $subscription->donor->email );

			// Must have a Stripe customer ID.
			if ( ! empty( $stripe_customer_id ) ) {

				$subscription = \Stripe\Subscription::retrieve( $subscription->profile_id );
				$subscription->cancel();

				return true;
			}

			return false;

		} catch ( \Stripe\Error\Base $e ) {

			// There was an issue cancelling the subscription w/ Stripe :(
			give_record_gateway_error( __( 'Stripe Error', 'give-recurring' ), sprintf( __( 'The Stripe Gateway returned an error while cancelling a subscription. Details: %s', 'give-recurring' ), $e->getMessage() ) );
			give_set_error( 'Stripe Error', __( 'An error occurred while cancelling the donation. Please try again.', 'give-recurring' ) );

			return false;

		} catch ( Exception $e ) {

			// Something went wrong outside of Stripe.
			give_record_gateway_error( __( 'Stripe Error', 'give-recurring' ), sprintf( __( 'The Stripe Gateway returned an error while cancelling a subscription. Details: %s', 'give-recurring' ), $e->getMessage() ) );
			give_set_error( 'Stripe Error', __( 'An error occurred while cancelling the donation. Please try again.', 'give-recurring' ) );

			return false;

		}

	}

	/**
	 * Link the recurring profile in Stripe.
	 *
	 * @param  string $profile_id   The recurring profile id.
	 * @param  object $subscription The Subscription object.
	 *
	 * @since  1.10.1
	 * @access public
	 *
	 * @return string The link to return or just the profile id.
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
}

new Give_Recurring_Stripe_Becs();
