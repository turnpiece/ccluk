<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Stripe
 */
class Give_Recurring_Stripe extends Give_Recurring_Gateway {

	public $invoice;

	public $payment_intent;

	/**
	 * Get Stripe Started.
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	public function init() {

		$this->id = 'stripe';

		if (
			defined( 'GIVE_STRIPE_VERSION' ) &&
			version_compare( GIVE_STRIPE_VERSION, '2.2.0', '<' )
		) {
			add_action( 'admin_notices', array( $this, 'old_api_upgrade_notice' ) );

			// No Stripe SDK. Bounce.
			return false;
		}

		add_action( 'template_redirect', array( $this, 'listen_sca_payments' ) );

		// Bailout, if gateway is not active.
		if ( ! give_is_gateway_active( $this->id ) ) {
			return;
		}

		$this->secret_key     = give_stripe_get_secret_key();
		$this->public_key     = give_stripe_get_publishable_key();
		$this->stripe_gateway = new Give_Stripe_Gateway();
		$this->invoice        = new Give_Stripe_Invoice();
		$this->payment_intent = new Give_Stripe_Payment_Intent();


		add_action( 'give_pre_refunded_payment', array( $this, 'process_refund' ) );
		add_action( 'give_recurring_cancel_stripe_subscription', array( $this, 'cancel' ), 10, 2 );
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
	 * @return void
	 */
	public function create_payment_profiles() {

		$source = ! empty( $_POST['give_stripe_payment_method'] ) ? give_clean( $_POST['give_stripe_payment_method'] ) : $this->generate_source_dictionary();
		$email  = $this->purchase_data['user_email'];

		// Convert token to source in case of stripe checkout.
		if ( give_stripe_is_checkout_enabled() ) {

			// In case of Stripe Checkout, $source_object is pretended as Token Object.
			$source_object = $this->stripe_gateway->get_token_details( $source );

			// Add source to donation notes and meta.
			give_insert_payment_note( $this->payment_id, 'Stripe Token ID: ' . $source_object->id );
			give_update_payment_meta( $this->payment_id, '_give_stripe_token_id', $source_object->id );
		} else {

			$source_object = $this->stripe_gateway->payment_method->retrieve( $source );

			// Add source to donation notes and meta.
			give_insert_payment_note( $this->payment_id, 'Stripe Source ID: ' . $source_object->id );
			give_update_payment_meta( $this->payment_id, '_give_stripe_source_id', $source_object->id );
		}

		$this->stripe_customer = new Give_Stripe_Customer( $email, $source_object->id );
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

		$subscription  = $this->subscribe_customer_to_plan( $stripe_customer, $source_object, $plan_id );
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

				$default_source_id = $this->stripe_customer->is_card_exists
					? $this->stripe_customer->customer_data->default_source
					: $source->id;

					// Get metadata.
				$metadata = give_recurring_get_metadata( $this->purchase_data, $this->payment_id );
				$args     = array(
					'customer' => $stripe_customer->id,
					'items'    => array(
						array(
							'plan' => $plan_id,
						),
					),
					'metadata' => $metadata,
				);

				if ( give_stripe_is_checkout_enabled() ) {
					$args['default_source'] = $default_source_id;
				} else {
					$args['default_payment_method'] = $default_source_id;
				}

				$subscription                      = \Stripe\Subscription::create( $args, give_stripe_get_connected_account_options() );
				$this->subscriptions['profile_id'] = $subscription->id;

				// Need additional authentication steps as subscription is still incomplete.
				if ( 'incomplete' ===  $subscription->status ) {

					// Verify the initial payment with invoice created during subscription.
					$invoice = $this->invoice->retrieve( $subscription->latest_invoice );

					// Set Payment Intent ID.
					give_insert_payment_note( $this->payment_id, 'Stripe Payment Intent ID: ' . $invoice->payment_intent );

					// Retrieve payment intent details.
					$intent_details = $this->payment_intent->retrieve( $invoice->payment_intent );

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
	 * @access      public
	 * @since       1.1
	 *
	 * @param $payment Give_Payment
	 *
	 * @return      void
	 */
	public function process_refund( $payment ) {

		if ( empty( $_POST['give_refund_in_stripe'] ) ) {
			return;
		}
		$statuses = array( 'give_subscription', 'publish' );

		if ( ! in_array( $payment->old_status, $statuses ) ) {
			return;
		}

		if ( 'stripe' !== $payment->gateway ) {
			return;
		}

		switch ( $payment->old_status ) {

			case 'give_subscription' :

				// Refund renewal payment
				if ( empty( $payment->transaction_id ) || $payment->transaction_id == $payment->ID ) {

					// No valid charge ID
					return;
				}

				try {

					$refund = \Stripe\Refund::create( array(
						'charge' => $payment->transaction_id,
					) );

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
	 * @param  array $card_info
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
	 * Initial field validation before ever creating profiles or donors.
	 *
	 * Note: Please don't use this function. This function is for internal purposes only and can be removed
	 * anytime without notice.
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @param array $valid_data List of valid data.
	 * @param array $post_data  List of posted variables.
	 *
	 * @return      void
	 */
	public function validate_fields( $valid_data, $post_data ) {

		if (
			isset( $post_data['card_name'] ) &&
			empty( $post_data['card_name'] ) &&
			! isset( $post_data['is_payment_request'] )
		) {
			give_set_error( 'no_card_name', __( 'Please enter a name for the credit card.', 'give-recurring' ) );
		}

	}

	/**
	 * Can update subscription CC details.
	 *
	 * @since 1.7
	 *
	 * @param bool   $ret
	 * @param object $subscription
	 *
	 * @return bool
	 */
	public function can_update( $ret, $subscription ) {

		if (
			'stripe' === $subscription->gateway
			&& ! empty( $subscription->profile_id )
			&& in_array( $subscription->status, array(
				'active',
				'failing',
			), true )
			&& ! give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled' ) )
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
	 * @return string
	 */
	public function generate_stripe_plan_id( $subscription_name, $recurring_amount, $period, $frequency ) {
		$subscription_name = sanitize_title( $subscription_name );

		return sanitize_key( $subscription_name . '_' . $recurring_amount . '_' . $period . '_' . $frequency );
	}

	/**
	 * Get Stripe Subscription.
	 *
	 * @param $stripe_subscription_id
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
	 * @param  Give_Subscription $subscription
	 * @param string             $date
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
	 * @param $stripe_customer_id
	 * @param $stripe_subscription_id
	 * @param $date
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
	 * Outputs the payment method update form
	 *
	 * @since  1.7
	 *
	 * @param  Give_Subscription $subscription The subscription object
	 *
	 * @return void
	 */
	public function update_payment_method_form( $subscription ) {

		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		// give_stripe_credit_card_form() only shows when Stripe Checkout is enabled so we fake it
		add_filter( 'give_get_option_stripe_checkout', '__return_false' );

		// Remove Billing address fields.
		if ( has_action( 'give_after_cc_fields', 'give_default_cc_address_fields' ) ) {
			remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields', 10 );
		}

		$form_id           = ! empty( $subscription->form_id ) ? absint( $subscription->form_id ) : 0;
		$args['id_prefix'] = "$form_id-1";
		give_stripe_credit_card_form( $form_id, $args, $echo = true );

	}

	/**
	 * Process the update payment form
	 *
	 * @since  1.7
	 *
	 * @param  Give_Recurring_Subscriber $subscriber   Give_Recurring_Subscriber
	 * @param  Give_Subscription         $subscription Give_Subscription
	 *
	 * @return void
	 */
	public function update_payment_method( $subscriber, $subscription ) {

		// Check for any existing errors.
		$errors    = give_get_errors();
		$post_data = give_clean( $_POST );

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

			// No errors in stripe, continue on through processing
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
	 * Process the update payment form.
	 *
	 * @since  1.8
	 *
	 * @param  Give_Recurring_Subscriber $subscriber   Give_Recurring_Subscriber
	 * @param  Give_Subscription         $subscription Give_Subscription
	 *
	 * @return void
	 */
	public function update_subscription( $subscriber, $subscription ) {
		// Sanitize the values submitted with donation form.
		$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

		// Get update renewal amount.
		$renewal_amount           = isset( $post_data['give-amount'] ) ? give_maybe_sanitize_amount( $post_data['give-amount'] ) : 0;
		$current_recurring_amount = give_maybe_sanitize_amount( $subscription->recurring_amount );
		$check_amount             = number_format( $renewal_amount, 0 );

		// Set error if renewal amount not valid.
		if (
			empty( $check_amount ) ||
			$renewal_amount === $current_recurring_amount
		) {
			give_set_error( 'give_recurring_invalid_subscription_amount', __( 'Please enter the valid subscription amount.', 'give-recurring' ) );
		}

		// Is errors?
		$errors = give_get_errors();

		if ( empty( $errors ) ) {
			$this->update_subscription_plan( $subscription, $renewal_amount );
		}
	}

	/**
	 * Update Stripe Subscription plan.
	 *
	 * @since 1.8
	 *
	 * @param \Give_Subscription $subscription
	 * @param int                $renewal_amount
	 */
	private function update_subscription_plan( $subscription, $renewal_amount ) {
		$stripe_plan_name = give_recurring_generate_subscription_name( $subscription->form_id, $subscription->price_id );
		$stripe_plan_id   = $this->generate_stripe_plan_id( $stripe_plan_name, $renewal_amount, $subscription->period, $subscription->frequency );

		try {

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

			$stripe_plan = false;

			try {

				$stripe_plan = \Stripe\Plan::create( $args );

			} catch ( \Stripe\Error\Base $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if ( isset( $err['message'] ) ) {
					give_set_error( 'stripe_error', $err['message'] );
				} else {
					give_set_error( 'stripe_error', __( 'There was an issue creating the Stripe plan.', 'give-recurring' ) );
				}

			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_set_error( 'Stripe Error', __( 'An error occurred while processing the donation. Please try again.', 'give-recurring' ) );
			}

			if ( isset( $stripe_plan ) && is_object( $stripe_plan ) ) {
				// get stripe subscription.
				$stripe_subscription = \Stripe\Subscription::retrieve( $subscription->profile_id );

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
					give_set_error( 'give_recurring_stripe_subscription_update', __( 'Problem in Stripe subscription update.', 'give-recurring' ) );
				}
			}
		} catch ( Exception $e ) {
			give_set_error( 'give_recurring_update_subscription_amount', __( 'Problem in update subscription amount.', 'give-recurring' ) );
		}
	}

	/**
	 * This function will record subscriptions processed using Stripe 3D secure payments.
	 *
	 * @todo   add post payment profile action hook if required in future.
	 *
	 * @param int            $donation_id Donation ID.
	 * @param \Stripe\Charge $charge      Stripe Charge Object.
	 * @param string         $customer_id Stripe Customer ID.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function record_3dsecure_signup( $donation_id, $charge, $customer_id ) {

		// Proceed only, if donation is recurring.
		if ( give_get_meta( $donation_id, '_give_is_donation_recurring', true ) ) {

			// Set subscription_payment.
			give_update_meta( $donation_id, '_give_subscription_payment', true );

			// Retrieve temporary data for 3d secure payments.
			$subscription_args = give_get_payment_meta( $donation_id, '_give_recurring_stripe_subscription_args', true );
			$offsite           = give_get_payment_meta( $donation_id, '_give_recurring_stripe_subscription_is_offsite', true );

			// Now create the subscription record.
			$subscriber = new Give_Recurring_Subscriber( $customer_id );

			if ( isset( $subscription_args['status'] ) ) {
				$status = $subscription_args['status'];
			} else {
				$status = $offsite ? 'pending' : 'active';
			}

			// Set Subscription frequency.
			$frequency = ! empty( $subscription_args['frequency'] ) ? intval( $subscription_args['frequency'] ) : 1;

			$args = array(
				'form_id'           => give_get_payment_form_id( $donation_id ),
				'parent_payment_id' => $donation_id,
				'status'            => $status,
				'period'            => $subscription_args['period'],
				'frequency'         => $frequency,
				'initial_amount'    => $subscription_args['initial_amount'],
				'recurring_amount'  => $subscription_args['recurring_amount'],
				'bill_times'        => $subscription_args['bill_times'],
				'expiration'        => $subscriber->get_new_expiration( $subscription_args['id'], $subscription_args['price_id'], $frequency ),
				'profile_id'        => $subscription_args['profile_id'],
				'transaction_id'    => $subscription_args['transaction_id'],
			);

			// Support user_id if it is present is purchase_data.
			if ( isset( $this->purchase_data['user_info']['id'] ) ) {
				$args['user_id'] = '';
			}

			$subscriber->add_subscription( $args );

			if ( ! $offsite ) {
				// Offsite payments get verified via a webhook so are completed in webhooks().
				give_update_payment_status( $donation_id, 'publish' );
			}

			// Delete temporary data required for successful 3d secure payments.
			give_delete_meta( $donation_id, '_give_recurring_stripe_subscription_args', true );
			give_delete_meta( $donation_id, '_give_recurring_stripe_subscription_is_offsite', true );

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
			'stripe' === $subscription->gateway &&
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
	 * @since  1.9.1
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
	 * @since  1.9.1
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
	 * Listen to SCA authenticated payments.
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	public function listen_sca_payments() {

		// Bailout, if accessed from admin.
		if ( is_admin() ) {
			return;
		}

		// Bailout, if not accessed from donation confirmation page.
		if ( ! is_page( give_get_option( 'success_page' ) ) ) {
			return;
		}

		$get_data          = give_clean( filter_input_array( INPUT_GET ) );

		// Bailout, if payment intent id doesn't exists.
		if ( ! isset( $get_data['payment_intent'] ) || empty( $get_data['payment_intent'] ) ) {
			return;
		}

		$payment_intent_id = $get_data['payment_intent'];
		$payment_intent    = $this->payment_intent->retrieve( $payment_intent_id );

		if ( isset( $payment_intent->last_payment_error->code ) && 'payment_intent_authentication_failure' === $payment_intent->last_payment_error->code ) {

			$invoice             = $this->invoice->retrieve( $payment_intent->invoice );
			$stripe_subscription = \Stripe\Subscription::retrieve( $invoice->subscription );

			if ( 'incomplete' === $stripe_subscription->status && 'open' === $invoice->status ) {
				$give_subscription = new Give_Subscription( $stripe_subscription->id, true );
				give_update_payment_status( $give_subscription->parent_payment_id, 'cancelled' );
				give_recurring_subscription_cancel( $give_subscription->id );
			}
		}
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
}

new Give_Recurring_Stripe();
