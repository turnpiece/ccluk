<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Gateway
 */
class Give_Recurring_Gateway {

	/**
	 * The Gateway ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Array of subscriptions.
	 *
	 * @var array
	 */
	public $subscriptions = array();

	/**
	 * Array of donation data.
	 *
	 * @var array
	 */
	public $purchase_data = array();

	/**
	 * Whether the gateway is offsite or onsite.
	 *
	 * @var bool
	 */
	public $offsite = false;

	/**
	 * @var int
	 */
	public $email = 0;

	/**
	 * The donor's ID.
	 *
	 * @var int
	 */
	public $customer_id = 0;

	/**
	 * The user ID.
	 *
	 * @var int
	 */
	public $user_id = 0;

	/**
	 * The donation payment ID.
	 *
	 * @var int
	 */
	public $payment_id = 0;

	/**
	 * CC form loaded.
	 *
	 * @var int
	 */
	public static $cc_form = 0;

	/**
	 * Get things started.
	 *
	 * @access      public
	 * @since       1.0
	 */
	public function __construct() {

		$this->init();

		add_action( 'give_checkout_error_checks', array( $this, 'checkout_errors' ), 0, 1 );
		add_action( 'give_gateway_' . $this->id, array( $this, 'process_checkout' ), 0 );
		add_action( 'init', array( $this, 'process_webhooks' ), 9 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 10 );
		add_action( 'give_cancel_subscription', array( $this, 'process_cancellation' ) );
		add_filter( 'give_subscription_can_cancel', array( $this, 'can_cancel' ), 10, 2 );
		add_filter( 'give_subscription_can_sync', array( $this, 'can_sync' ), 10, 2 );
		add_filter( 'give_subscription_can_update', array( $this, 'can_update' ), 10, 2 );
		add_filter( 'give_subscription_can_update_subscription', array( $this, 'can_update_subscription' ), 10, 2 );
		add_filter( 'give_subscription_can_cancel_' . $this->id . '_subscription', array(
			$this,
			'can_cancel',
		), 10, 2 );
		add_action( 'give_recurring_update_payment_form', array( $this, 'update_payment_method_form' ), 10, 1 );
		add_action( 'give_recurring_update_subscription_payment_method', array(
			$this,
			'process_payment_method_update',
		), 10, 3 );
		add_filter( 'give_subscription_profile_link_' . $this->id, array( $this, 'link_profile_id' ), 10, 2 );
		add_action( "give_recurring_update_{$this->id}_subscription", array( $this, 'update_payment_method' ), 10, 2 );

		// Process update renewal subscription information.
		add_action( 'give_recurring_update_renewal_subscription', array( $this, 'process_renewal_subscription_update', ), 10, 3 );
		add_action( "give_recurring_update_renewal_{$this->id}_subscription", array( $this, 'update_subscription' ), 10, 2 );
	}

	/**
	 * Setup gateway ID and possibly load API libraries.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function init() {

		$this->id = '';

	}

	/**
	 * Enqueue necessary scripts.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function scripts() {
	}

	/**
	 * Validate checkout fields.
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @param array $data   List of valid data.
	 * @param array $posted List of posted variables.
	 *
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {
		// check if user is login or not and form id is present or not.
		if ( ! is_user_logged_in() && ! empty( $posted['give-form-id'] ) ) {
			$form_id = absint( $posted['give-form-id'] );

			// Only required if email access not on & recurring enabled.
			if ( give_is_form_recurring( $form_id ) && ! give_is_setting_enabled( give_get_option( 'email_access' ) ) ) {
				// check if form is recurring and create account checkbox should be checked.
				if ( ! empty( $posted['_give_is_donation_recurring'] ) && empty( $posted['give_create_account'] ) ) {
					give_set_error( 'recurring_create_account', __( 'Please tick the create account button if you want to create a subscription donation', 'give-recurring' ) );
				}
			}
		}
	}

	/**
	 * Creates subscription payment profiles and sets the IDs so they can be stored.
	 *
	 * @access      public
	 * @since       1.0
	 */
	public function create_payment_profiles() {

		// Creates a payment profile and then sets the profile ID.
		$this->subscriptions['profile_id'] = '1234';

	}

	/**
	 * Finishes the signup process by redirecting to the success page
	 * or to an off-site payment page.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function complete_signup() {

		wp_redirect( give_get_success_page_uri() );
		exit;

	}

	/**
	 * Processes webhooks from the payment processor.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function process_webhooks() {

		// set webhook URL to: home_url( 'index.php?give-listener=' . $this->id );
		if ( empty( $_GET['give-listener'] ) || $this->id !== $_GET['give-listener'] ) {
			return;
		}

		// process webhooks here
	}

	/**
	 * Determines if a subscription can be cancelled through the gateway.
	 *
	 * @access      public
	 * @since       1.2
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {
		return $ret;
	}

	/**
	 * Cancels a subscription.
	 *
	 * @access      public
	 * @since       1.2
	 * @return      bool
	 */
	public function cancel( $subscription, $valid ) {
		//Handled per gateway.
	}

	/**
	 * Determines if a subscription can be synced through the gateway.
	 *
	 * @access      public
	 * @since       1.3
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @return bool
	 */
	public function can_sync( $ret, $subscription ) {
		return $ret;
	}

	/**
	 * Determines if a subscription can be updated through a gateway.
	 *
	 * @since  1.2
	 *
	 * @param  bool              $ret          Default setting (false)
	 * @param  Give_Subscription $subscription The subscription
	 *
	 * @return bool
	 */
	public function can_update( $ret, $subscription ) {
		return $ret;
	}

	/**
	 * Determines if a subscription can be updated through a gateway.
	 *
	 * @since  1.8
	 *
	 * @param  bool              $ret          Default setting (false)
	 * @param  Give_Subscription $subscription The subscription
	 *
	 * @return bool
	 */
	public function can_update_subscription( $ret, $subscription ) {
		return $ret;
	}

	/**
	 * Process the update payment form.
	 *
	 * @since  1.1.2
	 *
	 * @param  Give_Recurring_Subscriber $subscriber   Give_Recurring_Subscriber
	 * @param  Give_Subscription         $subscription Give_Subscription
	 *
	 * @return void
	 */
	public function update_payment_method( $subscriber, $subscription ) {
	}

	/**
	 * Outputs the payment method update form.
	 *
	 * @since  1.1.2
	 * @since  1.7 Remove CC address fields.
	 *
	 * @param  Give_Subscription $subscription The subscription object.
	 *
	 * @return void
	 */
	public function update_payment_method_form( $subscription ) {

		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		ob_start();

		// Remove Billing address fields.
		if ( has_action( 'give_after_cc_fields', 'give_default_cc_address_fields' ) ) {
			remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields', 10 );
		}

		$form_id = ( isset( $subscription->form_id ) && ! empty( $subscription->form_id ) ) ? absint( $subscription->form_id ) : 0;

		if ( self::$cc_form > 0 ) {
			return;
		}

		give_get_cc_form( $form_id );
		self::$cc_form ++;

		echo ob_get_clean();

	}

	/**
	 * Outputs any information after the Credit Card Fields.
	 *
	 * @since  1.1.2
	 * @return void
	 */
	public function after_cc_fields() {
	}

	/****************************************************************
	 * Below methods should not be extended except in rare cases
	 ***************************************************************/

	/**
	 * Processes the recurring donation form and sends sets up the subscription data for hand-off to the gateway.
	 *
	 * @param $donation_data
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 *
	 */
	public function process_checkout( $donation_data ) {

		// If not a recurring purchase so bail.
		if ( ! Give_Recurring()->is_donation_recurring( $donation_data ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $donation_data['gateway_nonce'], 'give-gateway' ) ) {
			wp_die( __( 'Nonce verification failed.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array( 'response' => 403 ) );
		}

		// Initial validation.
		do_action( 'give_recurring_process_checkout', $donation_data, $this );

		$errors = give_get_errors();

		if ( $errors ) {
			give_send_back_to_checkout( '?payment-mode=' . $this->id );
		}

		$this->purchase_data = apply_filters( 'give_recurring_purchase_data', $donation_data, $this );
		$this->user_id       = $donation_data['user_info']['id'];
		$this->email         = $donation_data['user_info']['email'];

		if ( empty( $this->user_id ) ) {
			$subscriber = new Give_Donor( $this->email );
		} else {
			$subscriber = new Give_Donor( $this->user_id, true );
		}

		if ( empty( $subscriber->id ) ) {

			$name = sprintf(
				'%s %s',
				( ! empty( $donation_data['user_info']['first_name'] ) ? trim( $donation_data['user_info']['first_name'] ) : '' ),
				( ! empty( $donation_data['user_info']['last_name'] ) ? trim( $donation_data['user_info']['last_name'] ) : '' )
			);

			$subscriber_data = array(
				'name'    => trim( $name ),
				'email'   => $donation_data['user_info']['email'],
				'user_id' => $this->user_id,
			);

			$subscriber->create( $subscriber_data );

		}

		$this->customer_id = $subscriber->id;

		// Get billing times.
		$times = ! empty( $this->purchase_data['times'] ) ? intval( $this->purchase_data['times'] ) : 0;
		// Get frequency value.
		$frequency = ! empty( $this->purchase_data['frequency'] ) ? intval( $this->purchase_data['frequency'] ) : 1;

		$payment_data = array(
			'price'           => $this->purchase_data['price'],
			'give_form_title' => $this->purchase_data['post_data']['give-form-title'],
			'give_form_id'    => intval( $this->purchase_data['post_data']['give-form-id'] ),
			'give_price_id'   => $this->get_price_id(),
			'date'            => $this->purchase_data['date'],
			'user_email'      => $this->purchase_data['user_email'],
			'purchase_key'    => $this->purchase_data['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $this->purchase_data['user_info'],
			'status'          => 'pending',
		);

		// Record the pending payment.
		$this->payment_id = give_insert_payment( $payment_data );

		$this->subscriptions = apply_filters( 'give_recurring_subscription_pre_gateway_args', array(
			'name'             => $this->purchase_data['post_data']['give-form-title'],
			'id'               => $this->purchase_data['post_data']['give-form-id'], // @TODO Deprecate w/ backwards compatiblity.
			'form_id'          => $this->purchase_data['post_data']['give-form-id'],
			'price_id'         => $this->get_price_id(),
			'initial_amount'   => give_sanitize_amount_for_db( $this->purchase_data['price'] ), // add fee here in future.
			'recurring_amount' => give_sanitize_amount_for_db( $this->purchase_data['price'] ),
			'period'           => $this->get_interval( $this->purchase_data['period'], $frequency ),
			'frequency'        => $this->get_interval_count( $this->purchase_data['period'], $frequency ), // Passed interval. Example: charge every 3 weeks.
			'bill_times'       => give_recurring_calculate_times( $times, $frequency ),
			'profile_id'       => '', // Profile ID for this subscription - This is set by the payment gateway.
			'transaction_id'   => '', // Transaction ID for this subscription - This is set by the payment gateway.
		) );

		do_action( 'give_recurring_pre_create_payment_profiles', $this );

		// Create subscription payment profiles in the gateway.
		$this->create_payment_profiles();

		do_action( 'give_recurring_post_create_payment_profiles', $this );

		// Look for errors after trying to create payment profiles.
		$errors = give_get_errors();

		if ( $errors ) {
			give_send_back_to_checkout( '?payment-mode=' . $this->id );
		}

		// Record the subscriptions and finish up.
		$this->record_signup();

		// Finish the signup process.
		// Gateways can perform off-site redirects here if necessary.
		$this->complete_signup();

		// Look for any last errors.
		$errors = give_get_errors();

		// We shouldn't usually get here, but just in case a new error was recorded,
		// we need to check for it.
		if ( $errors ) {
			give_send_back_to_checkout( '?payment-mode=' . $this->id );
		}
	}

	/**
	 * Gets interval length and interval unit for Authorize.net based on Give subscription period.
	 *
	 * @param  string $period
	 * @param  int    $frequency
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return array
	 */
	public static function get_interval( $period, $frequency ) {

		$interval = $period;

		switch ( $period ) {

			case 'quarter':
				$interval = 'month';
				break;
		}

		return $interval;
	}

	/**
	 * Gets interval length and interval unit for Authorize.net based on Give subscription period.
	 *
	 * @param  string $period
	 * @param  int    $frequency
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return array
	 */
	public static function get_interval_count( $period, $frequency ) {

		$interval_count = $frequency;

		switch ( $period ) {

			case 'quarter':
				$interval_count = 3 * $frequency;
				break;
		}

		return $interval_count;
	}

	/**
	 * Records subscription donations in the database and creates a give_payment record.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function record_signup() {

		// Set subscription_payment.
		give_update_meta( $this->payment_id, '_give_subscription_payment', true );

		// Now create the subscription record.
		$subscriber = new Give_Recurring_Subscriber( $this->customer_id );

		if ( isset( $this->subscriptions['status'] ) ) {
			$status = $this->subscriptions['status'];
		} else {
			$status = $this->offsite ? 'pending' : 'active';
		}

		// Set Subscription frequency.
		$frequency = ! empty( $this->subscriptions['frequency'] ) ? intval( $this->subscriptions['frequency'] ) : 1;

		$args = array(
			'form_id'           => $this->subscriptions['id'],
			'parent_payment_id' => $this->payment_id,
			'status'            => $status,
			'period'            => $this->subscriptions['period'],
			'frequency'         => $frequency,
			'initial_amount'    => $this->subscriptions['initial_amount'],
			'recurring_amount'  => $this->subscriptions['recurring_amount'],
			'bill_times'        => $this->subscriptions['bill_times'],
			'expiration'        => $subscriber->get_new_expiration( $this->subscriptions['id'], $this->subscriptions['price_id'], $frequency, $this->subscriptions['period'] ),
			'profile_id'        => $this->subscriptions['profile_id'],
			'transaction_id'    => $this->subscriptions['transaction_id'],
		);

		// Support user_id if it is present is purchase_data.
		if ( isset( $this->purchase_data['user_info']['id'] ) ) {
			$args['user_id'] = $this->purchase_data['user_info']['id'];
		}

		$subscriber->add_subscription( $args );

		if ( ! $this->offsite ) {
			// Offsite payments get verified via a webhook so are completed in webhooks().
			give_update_payment_status( $this->payment_id, 'publish' );
		}
	}

	/**
	 * Triggers the validate_fields() method for the gateway during checkout submission
	 * This should not be extended
	 *
	 * @since 1.0
	 *
	 * @param array $valid_data List of valid data.
	 *
	 * @return void
	 */
	public function checkout_errors( $valid_data ) {

		$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

		if ( ! empty( $post_data['give-gateway'] ) && $this->id !== $post_data['give-gateway'] ) {
			return;
		}

		$this->validate_fields( $valid_data, $post_data );
	}

	/**
	 * Process the update payment form.
	 *
	 * @since  1.1.2
	 * @since  1.7 Updated payment method.
	 *
	 * @param  int  $user_id         User ID
	 * @param  int  $subscription_id Subscription ID
	 * @param  bool $verified        Sanity check that the request to update is coming from a verified source
	 *
	 * @return void
	 */
	public function process_payment_method_update( $user_id, $subscription_id, $verified ) {

		$subscription = new Give_Subscription( $subscription_id );

		if ( empty( $subscription->id ) ) {
			give_set_error( 'give_recurring_invalid_subscription_id', __( 'Invalid subscription ID.', 'give-recurring' ) );
		}

		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		if ( ! $subscription->can_update() ) {
			give_set_error( 'give_recurring_subscription_not_updated', __( 'This subscription cannot be updated.', 'give-recurring' ) );
		}

		$subscriber = new Give_Recurring_Subscriber( $subscription->customer_id );
		if ( empty( $subscriber->id ) ) {
			give_set_error( 'give_recurring_invalid_subscriber', __( 'Invalid subscriber.', 'give-recurring' ) );
		}

		// Make sure the User doing the update is the user the subscription belongs to
		if ( $user_id !== $subscriber->id ) {
			give_set_error( 'give_recurring_subscriber_not_match', __( 'User ID and Subscriber do not match.', 'give-recurring' ) );
		}

		do_action( "give_recurring_update_{$subscription->gateway}_subscription", $subscriber, $subscription );

		$errors = give_get_errors();

		if ( empty( $errors ) ) {
			$url = add_query_arg( array( 'action' => 'update', 'updated' => true, 'subscription_id' => $subscription->id ) );
		} else {
			$url = add_query_arg( array( 'action' => 'update', 'subscription_id' => $subscription->id ) );
		}

		if ( 'stripe' === $subscription->gateway && empty( $errors ) ) {
			wp_safe_redirect( $url );
			exit();
		} else {
			echo $url;
			give_die();
		}

	}

	/**
	 * Handles cancellation requests for a subscription.
	 *
	 * This should not be extended.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 *
	 * @param $data
	 */
	public function process_cancellation( $data ) {

		// Need the sub ID to proceed.
		if ( empty( $data['sub_id'] ) ) {
			return;
		}

		/**
		 * Sanity check:
		 *
		 * a) If subscriber is not logged in.
		 * b) email access is not enabled nor active.
		 * c) they don't have an active donation session.
		 */
		if (
			! is_user_logged_in()
			&& Give_Recurring()->subscriber_has_email_access() == false
			&& ! give_get_purchase_session()
		) {
			return;
		}

		// Get subscription id.
		$data['sub_id'] = absint( $data['sub_id'] );

		// Verify the nonce for security.
		if ( ! wp_verify_nonce( $data['_wpnonce'], "give-recurring-cancel-{$data['sub_id']}" ) ) {
			wp_die( __( 'Nonce verification failed.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array( 'response' => 403 ) );
		}

		$subscription = new Give_Subscription( $data['sub_id'] );

		if ( ! $subscription->can_cancel() ) {
			wp_die( __( 'This subscription cannot be cancelled.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array( 'response' => 403 ) );
		}

		try {

			do_action( 'give_recurring_cancel_' . $subscription->gateway . '_subscription', $subscription, true );

			$subscription->cancel();

			if ( is_admin() ) {

				wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&give-message=cancelled&id=' . $subscription->id ) );
				exit;

			} else {

				$args = ! give_get_errors() ? array( 'give-message' => 'cancelled' ) : array();

				wp_redirect(
					remove_query_arg(
						array(
							'_wpnonce',
							'give_action',
							'sub_id',
						),
						add_query_arg( $args )
					)
				);

				exit;

			}

		} catch ( Exception $e ) {
			wp_die( $e->getMessage(), __( 'Error', 'give-recurring' ), array( 'response' => 403 ) );
		}

	}

	/**
	 * Retrieve subscription details.
	 *
	 * This method should be extended by each gateway in order to call the gateway API
	 * to determine the status and expiration of the subscription.
	 *
	 * @access      public
	 *
	 * @param Give_Subscription $subscription
	 *
	 * @since       1.2
	 * @return      array
	 */
	public function get_subscription_details( $subscription ) {

		/**
		 * Return value for valid subscriptions should be an array containing the following keys:
		 *
		 * - status: The status of the subscription (active, cancelled, expired, completed, pending, failing)
		 * - expiration: The expiration / renewal date of the subscription
		 * - error: An instance of WP_Error with error code and message (if any)
		 */
		$ret = array(
			'status'     => '',
			'expiration' => '',
			'error'      => '',
		);

		return $ret;
	}

	/**
	 * Link Profile ID.
	 *
	 * @param $profile_id
	 * @param $subscription
	 *
	 * @return mixed
	 */
	public function link_profile_id( $profile_id, $subscription ) {
		return $profile_id;
	}

	/**
	 * Gets transactions from the gateway's records.
	 *
	 * @param        $subscription
	 * @param string $date
	 *
	 * @return array
	 */
	public function get_gateway_transactions( $subscription, $date = '' ) {

	}

	/**
	 * Get price id
	 *
	 * @since 1.6.2
	 * @access private
	 *
	 * @return string
	 */
	private function get_price_id(){
		return array_key_exists( 'give-price-id', $this->purchase_data['post_data'] )
			? $this->purchase_data['post_data']['give-price-id']
			: '';
	}

	/**
	 * Process the subscription update.
	 * Update renewal amount of subscription.
	 *
	 * @since  1.8 Update renewal subscription.
	 *
	 * @param  int  $user_id         User ID
	 * @param  int  $subscription_id Subscription ID
	 * @param  bool $verified        Sanity check that the request to update is coming from a verified source
	 *
	 * @return void
	 */
	public function process_renewal_subscription_update( $user_id, $subscription_id, $verified ) {
		$subscription = new Give_Subscription( $subscription_id );

		// Bail out, if Gateway not match.
		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		// Set error if Subscription ID empty.
		if ( empty( $subscription->id ) ) {
			give_set_error( 'give_recurring_invalid_subscription_id', __( 'Invalid subscription ID.', 'give-recurring' ) );
		}

		// Set error if Subscription can not be update.
		if ( ! $subscription->can_update_subscription() ) {
			give_set_error( 'give_recurring_renewal_subscription_not_updated', __( 'This subscription cannot be updated.', 'give-recurring' ) );
		}

		// Get Subscriber.
		$subscriber = new Give_Recurring_Subscriber( $subscription->customer_id );

		// Set error if Invalid subscriber.
		if ( empty( $subscriber->id ) ) {
			give_set_error( 'give_recurring_invalid_subscriber', __( 'Invalid subscriber.', 'give-recurring' ) );
		}

		// Make sure the User doing the update is the user the subscription belongs to.
		if ( $user_id !== $subscriber->id ) {
			give_set_error( 'give_recurring_subscriber_not_match', __( 'User ID and Subscriber do not match.', 'give-recurring' ) );
		}

		/**
		 * Update renewal subscription information.
		 * Like renewal amount etc.
		 *
		 * @since 1.8
		 *
		 * @param \Give_Recurring_Subscriber $subscriber
		 * @param \Give_Subscription         $subscription
		 */
		do_action( "give_recurring_update_renewal_{$subscription->gateway}_subscription", $subscriber, $subscription );

		// Is errors?
		$errors = give_get_errors();

		// Build URL based on error got or not.
		if ( empty( $errors ) ) {

			// Update subscription details if don't get any error.
			$this->update_renewal_subscription_details( $subscription );

			$url = add_query_arg(
				array(
					'action'          => 'edit_subscription',
					'updated'         => true,
					'subscription_id' => $subscription->id,
				)
			);
		} else {
			$url = add_query_arg(
				array(
					'action'          => 'edit_subscription',
					'subscription_id' => $subscription->id,
				)
			);
		}

		// If Gateway is Stripe then redirect it else return URL in ajax response.
		echo $url;
		give_die();
	}

	/**
	 * Process the update subscription.
	 *
	 * @since  1.8
	 *
	 * @param  Give_Recurring_Subscriber $subscriber   Give_Recurring_Subscriber
	 * @param  Give_Subscription         $subscription Give_Subscription
	 *
	 * @return void
	 */
	public function update_subscription( $subscriber, $subscription ) {

	}

	/**
	 * Update renewal subscription details on db.
	 *
	 * @since 1.8
	 *
	 * @param \Give_Subscription $subscription
	 */
	private function update_renewal_subscription_details( $subscription ) {

		// Sanitize the values submitted with donation form.
		$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

		// Get update renewal amount.
		$new_recurring_amount = isset( $post_data['give-amount'] ) ? give_sanitize_amount_for_db( $post_data['give-amount'] ) : 0;

		// Subscription id.
		$subscription_id      = $subscription->id;
		$parent_payment_id    = $subscription->parent_payment_id;
		$old_recurring_amount = $subscription->recurring_amount;

		$subscription_db = new Give_Subscriptions_DB();
		$subscription_db->update( $subscription_id, array( 'recurring_amount' => $new_recurring_amount ) );

		// Insert Subscription note, if old and new amount not matched.
		if ( $old_recurring_amount !== $new_recurring_amount ) {

			$interval  = ! empty( $subscription->frequency ) ? $subscription->frequency : 1;
			$frequency = give_recurring_pretty_subscription_frequency( $subscription->period, false, false, $interval );

			// Add Subscription Note.
			give_insert_subscription_note(
				$subscription_id,
				sprintf(
					__( 'Subscription amount updated by donor: Previous amount %1$s, New amount %2$s', 'give-recurring' ),
					give_currency_filter(
						give_format_amount(
							$old_recurring_amount
						), array(
							'currency_code' => give_get_payment_currency_code( $parent_payment_id ),
						)
					) . '/' . $frequency,
					give_currency_filter(
						give_format_amount(
							$new_recurring_amount
						), array(
							'currency_code' => give_get_payment_currency_code( $parent_payment_id ),
						)
					) . '/' . $frequency
				)
			);
		}
	}
}
