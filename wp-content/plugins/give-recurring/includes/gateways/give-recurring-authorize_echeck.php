<?php
/**
 * Give Authorize.net eCheck Recurring Gateway Integration
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $give_recurring_authorize_echeck;

/**
 * Class Give_Recurring_Authorize_eCheck
 *
 * @since 1.8
 */
class Give_Recurring_Authorize_eCheck extends Give_Recurring_Gateway {

	/**
	 * API Login ID.
	 *
	 * @since 1.8
	 * @var string
	 */
	private $api_login_id;

	/**
	 * Transaction Key.
	 *
	 * @since 1.8
	 * @var string
	 */
	private $transaction_key;

	/**
	 * Sandbox mode.
	 *
	 * @since 1.8
	 * @var bool
	 */
	private $is_sandbox_mode;

	/**
	 * Whether the live webhooks have been setup.
	 *
	 * @since 1.8
	 * @var bool
	 */
	var $live_webhooks_setup;

	/**
	 * Whether the sandbox webhooks have been setup.
	 *
	 * @since 1.8
	 * @var bool
	 */
	var $sandbox_webhooks_setup;

	/**
	 * Get eCheck Authorize started.
	 *
	 * @since 1.8
	 */
	public function init() {

		$this->id                     = 'authorize_echeck';
		$this->live_webhooks_setup    = give_get_option( 'give_authorize_live_webhooks_setup' );
		$this->sandbox_webhooks_setup = give_get_option( 'give_authorize_sandbox_webhooks_setup' );

		// Load Authorize SDK and define its constants.
		$this->load_authnetxml_library();
		$this->define_authorize_values();

		// Cancellation support.
		add_action( 'give_recurring_cancel_authorize_echeck_subscription', array( $this, 'cancel_subscription' ), 10, 2 );

		// Webhook support.
		add_action( 'give_authorize_event_net.authorize.customer.subscription.cancelled', array(
			$this,
			'process_webhook_cancel'
		), 10, 2 );
		add_action( 'give_authorize_event_net.authorize.payment.authcapture.created', array(
			$this,
			'process_webhook_renewal'
		), 1, 2 );
		add_action( 'give_authorize_event_net.authorize.customer.subscription.suspended', array(
			$this,
			'process_webhook_subscription_suspended'
		), 10, 2 );
		add_action( 'give_authorize_event_net.authorize.customer.subscription.terminated', array(
			$this,
			'process_webhook_subscription_terminated'
		), 10, 2 );
		add_action( 'give_authorize_event_net.authorize.customer.subscription.expiring', array(
			$this,
			'process_webhook_subscription_expiring'
		), 10, 2 );
		
		// Require last name.
		add_filter( 'give_donation_form_before_personal_info', array( $this, 'maybe_require_last_name' ) );
	}


	/**
	 * Loads AuthorizeNet PHP SDK.
	 *
	 * @since 1.8
	 * @return void
	 */
	public function load_authnetxml_library() {
		if ( file_exists( GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/authorize/AuthnetXML/AuthnetXML.class.php' ) ) {
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/authorize/AuthnetXML/AuthnetXML.class.php';
		}
	}

	/**
	 * Set API Login ID, Transaction Key and Mode.
	 *
	 * @since 1.8
	 * @return void
	 */
	public function define_authorize_values() {

		// Live keys
		if ( ! give_is_test_mode() ) {
			$this->api_login_id    = give_get_option( 'give_api_login' );
			$this->transaction_key = give_get_option( 'give_transaction_key' );
			$this->is_sandbox_mode = false;
		} else {
			// Sandbox keys
			$this->api_login_id    = give_get_option( 'give_authorize_sandbox_api_login' );
			$this->transaction_key = give_get_option( 'give_authorize_sandbox_transaction_key' );
			$this->is_sandbox_mode = true;
		}

	}

	/**
	 * Check that the necessary credentials are set.
	 *
	 * @since 1.8
	 * @return bool
	 */
	private function check_credentials() {
		// Check credentials
		if (
			empty( $this->api_login_id )
			|| empty( $this->transaction_key )
		) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Validates the form data.
	 *
	 * @since 1.8
	 *
	 * @param $data
	 * @param $posted
	 */
	public function validate_fields( $data, $posted ) {

		if ( ! class_exists( 'AuthnetXML' ) && ! class_exists( 'Give_Authorize' ) ) {
			give_set_error( 'give_recurring_authorize_missing', __( 'The Authorize.net gateway is not activated.', 'give-recurring' ) );
		}

		if ( empty( $this->api_login_id ) || empty( $this->transaction_key ) ) {
			give_set_error( 'give_recurring_authorize_settings_missing', __( 'The API Login ID or Transaction key is missing.', 'give-recurring' ) );
		}
	}

	/**
	 * Creates subscription payment profiles and sets the IDs so they can be stored.
	 * @since 1.8
	 *
	 * @return bool true on success and false on failure.
	 */
	public function create_payment_profiles() {

		$subscription = $this->subscriptions;
		$bank_details = $this->purchase_data['bank_details'];
		$user_info    = $this->purchase_data['user_info'];

		$response = $this->create_authorize_net_echeck_subscription( $subscription, $bank_details, $user_info );

		if ( $response->isSuccessful() ) {

			$sub_id_to_json  = wp_json_encode( $response->subscriptionId );
			$sub_id_to_array = json_decode( $sub_id_to_json, true );

			// Set sub profile ID from Authorize.net response.
			$this->subscriptions['profile_id'] = isset( $sub_id_to_array[0] ) ? $sub_id_to_array[0] : '';

			// If no sub ID mention it in logs.
			if ( isset( $this->subscriptions['profile_id'] ) && empty( $this->subscriptions['profile_id'] ) ) {
				give_record_gateway_error( 'Authorize.net eCheck Error', __( 'Could not generate a subscription ID from the API response. Please contact GiveWP support.', 'give-recurring' ) );
			}

			$is_success = true;

		} else {

			give_set_error( 'give_recurring_authorize_echeck_error', "{$response->messages->message->code} - {$response->messages->message->text}" );
			give_record_gateway_error( 'Authorize.net eCheck Error', sprintf( __( 'Gateway Error %1$s: %2$s', 'give-recurring' ), $response->messages->message->code,
				$response->messages->message->text ) );

			$is_success = false;

		}

		return $is_success;
	}

	/**
	 * Creates a new Automated Recurring Billing (ARB) subscription.
	 *
	 * @since 1.8
	 *
	 * @param  array $subscription
	 * @param  array $bank_details
	 * @param  array $user_info
	 *
	 * @return AuthnetXML
	 */
	public function create_authorize_net_echeck_subscription( $subscription, $bank_details, $user_info ) {

		$args = $this->generate_create_subscription_request_args( $subscription, $bank_details, $user_info );
		
		// Use AuthnetXML library to create a new subscription request.
		$authnet_xml = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );
		$authnet_xml->ARBCreateSubscriptionRequest( $args );
		
		return $authnet_xml;
	}

	/**
	 * Generates args for making a ARB create subscription request.
	 *
	 * @since 1.8
	 *
	 * @param  array $subscription
	 * @param  array $bank_details
	 * @param  array $user_info
	 *
	 * @return array
	 */
	public function generate_create_subscription_request_args( $subscription, $bank_details, $user_info ) {

		// Set date to same timezone as Authorize's servers (Mountain Time) to prevent conflicts.
		date_default_timezone_set( 'America/Denver' );
		$today = date( 'Y-m-d' );

		// Calculate totalOccurrences.
		$total_occurrences = ( 0 === $subscription['bill_times'] ) ? 9999 : $subscription['bill_times'];

		$address = isset( $user_info['address']['line1'] ) ? $user_info['address']['line1'] : '';
		$address .= isset( $user_info['address']['line2'] ) ? ' ' . $user_info['address']['line2'] : '';

		$name = mb_substr( give_recurring_generate_subscription_name( $subscription['form_id'], $subscription['price_id'] ), 0, 49 );

		$args = array(
			'subscription' => array(
				'name'            => $name,
				'paymentSchedule' => array(
					'interval'         => array(
						'length' => $subscription['frequency'],
						'unit'   => $subscription['period'],
					),
					'startDate'        => $today,
					'totalOccurrences' => $total_occurrences,
				),
				'amount'          => $subscription['recurring_amount'],
				'payment'         => array(
					'bankAccount' => array(
						'accountType'   => $bank_details['account-type'],
						'routingNumber' => $bank_details['routing-number'],
						'accountNumber' => $bank_details['account-number'],
						'nameOnAccount' => $bank_details['name-on-account'],
					),
				),
				'order'           => array(
					'invoiceNumber' => substr( $this->purchase_data['purchase_key'], 0, 19 ),
					'description'   => apply_filters( 'give_authorize_echeck_recurring_payment_description', give_payment_gateway_donation_summary( $this->purchase_data, false ),
						$this->purchase_data, $subscription ),
				),
				'customer'        => array(
					'email' => $user_info['email'],
				),
				'billTo'          => array(
					'firstName' => $user_info['first_name'],
					'lastName'  => $user_info['last_name'],
					'address'   => mb_substr( $address, 0, 59 ),
					'city'      => isset( $user_info['address']['city'] ) ? mb_substr( $user_info['address']['city'], 0, 39 ) : '',
					'state'     => isset( $user_info['address']['state'] ) ? $user_info['address']['state'] : '',
					'zip'       => isset( $user_info['address']['zip'] ) ? mb_substr( $user_info['address']['zip'], 0, 19 ) : '',
					'country'   => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
				),
			),
		);

		// Use `CCD` echeckType when account type is `businessChecking`.
		if ( isset( $bank_details['account-type'] ) && 'businessChecking' === $bank_details['account-type'] ){
			$args['subscription']['payment']['bankAccount']['echeckType'] = 'CCD';
		}

		/**
		 * Update authorize_echeck subscription request api args.
		 *
		 * @since 1.8
		 *
		 * @param array $args         eCheck request args.
		 * @param Give_Recurring_Authorize_eCheck eCheck recurring object.
		 * @param array $subscription Subscription details.
		 * @param array $bank_details Bank details.
		 */
		return apply_filters( 'give_recurring_authorize_echeck_create_subscription_request_args', $args, $this, $subscription, $bank_details );

	}

	/**
	 * Gets interval length for Authorize.net based on Give subscription period.
	 *
	 * @param  string $subscription_period
	 * @param  int    $subscription_interval
	 * 
	 * @since 1.8
	 *
	 * @return array
	 */
	public static function get_interval( $subscription_period, $subscription_interval ) {

		$unit = $subscription_period;

		switch ( $subscription_period ) {

			case 'day':
				$unit = 'days';
				break;
			case 'week':
				$unit   = 'days';
				break;
			case 'quarter':
				$unit   = 'months';
				break;
			case 'month':
				$unit   = 'months';
				break;
			case 'year':
				$unit   = 'months';
				break;
		}

		return $unit;
	}

	/**
	 * Gets interval unit for Authorize.net based on Give subscription period.
	 *
	 * @param  string $subscription_period
	 * @param  int    $subscription_interval
	 * 
	 * @since 1.9.0
	 *
	 * @return array
	 */
	public static function get_interval_count( $subscription_period, $subscription_interval ) {

		$length = $subscription_interval;

		switch ( $subscription_period ) {

			case 'week':
				$length = 7 * $subscription_interval;
				break;
			case 'quarter':
				$length = 3 * $subscription_interval;
				break;
			case 'month':
				$length = 1 * $subscription_interval;
				break;
			case 'year':
				$length = 12 * $subscription_interval;
				break;
		}

		return $length;
	}

	/**
	 * Determines if the subscription can be cancelled.
	 *
	 * @since 1.8
	 *
	 * @param  bool              $ret
	 * @param  Give_Subscription $subscription
	 *
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if (
			$subscription->gateway === $this->id
			&& ! empty( $subscription->profile_id )
			&& 'active' === $subscription->status
			&& $this->check_credentials()
		) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Determines if the subscription can be cancelled.
	 *
	 * @since 1.8
	 *
	 * @param  bool              $ret
	 * @param  Give_Subscription $subscription
	 *
	 * @return bool
	 */
	public function can_sync( $ret, $subscription ) {

		if (
			$subscription->gateway === $this->id
			&& $this->check_credentials()
			&& ! empty( $subscription->profile_id )
		) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Cancels a subscription.
	 *
	 * @since 1.8
	 *
	 * @param  Give_Subscription $subscription
	 * @param  bool              $valid
	 *
	 * @return bool|AuthnetXML
	 */
	public function cancel_subscription( $subscription, $valid ) {

		if ( empty( $valid ) ) {
			return false;
		}

		$response = $this->cancel_authorize_net_echeck_subscription( $subscription->profile_id );

		return $response;
	}

	/**
	 * Cancel a ARB subscription based for a given subscription id.
	 *
	 * @since 1.8
	 *
	 * @param  string $anet_subscription_id
	 *
	 * @return bool
	 */
	public function cancel_authorize_net_echeck_subscription( $anet_subscription_id ) {

		// Use AuthnetXML library to create a new subscription request,
		$authnet_xml = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );
		$authnet_xml->ARBCancelSubscriptionRequest( array(
			'subscriptionId' => $anet_subscription_id,
		) );

		return $authnet_xml->isSuccessful();
	}

	/**
	 * Process the subscription terminated webhook.
	 *
	 * Processes the net.authorize.payment.authcapture.subscription.terminated webhook for subscription termination.
	 *
	 * @since 1.9.0
	 *
	 * @param object $event_json Webhook data sent over from Authorize.net
	 *
	 * @return bool|\Give_Subscription
	 */
	public function process_webhook_subscription_terminated( $event_json ) {

		// Must be using latest Authorize with webhook support.
		if ( ! method_exists( Give_Authorize()->payments, 'setup_api_request' ) ) {
			give_record_gateway_error( 'Authorize.net Webhook Error', __( 'You are using an outdated version of the Authorize.net gateway. Please update to accept subscription terminated webhooks.', 'give-recurring' ) );

			return false;
		}

		$transaction_id = isset( $event_json->payload->id ) ? $event_json->payload->id : '';
		
		// Must have the transaction id.
		if ( empty( $transaction_id ) ) {
			return false;
		}

		$subscription = new Give_Subscription( $transaction_id, true );

		// Check for subscription ID.
		if ( 0 === $subscription->id ) {
			return false;
		}
		
		// Set subscription status to cancelled.
		$subscription->cancel();
	}

	/**
	 * Process the subscription expiring webhook.
	 *
	 * Processes the net.authorize.payment.authcapture.subscription.expiring webhook for subscription expiration.
	 *
	 * @since 1.9.0
	 *
	 * @param object $event_json Webhook data sent over from Authorize.net
	 *
	 * @return bool|\Give_Subscription
	 */
	public function process_webhook_subscription_expiring( $event_json ) {

		// Must be using latest Authorize with webhook support.
		if ( ! method_exists( Give_Authorize()->payments, 'setup_api_request' ) ) {
			give_record_gateway_error( 'Authorize.net Webhook Error', __( 'You are using an outdated version of the Authorize.net gateway. Please update to accept subscription suspended webhooks.', 'give-recurring' ) );

			return false;
		}
		
		$transaction_id = isset( $event_json->payload->id ) ? $event_json->payload->id : '';
		
		// Must have the transaction id.
		if ( empty( $transaction_id ) ) {
			return false;
		}

		$subscription = new Give_Subscription( $transaction_id, true );

		// Check for subscription ID.
		if ( 0 === $subscription->id ) {
			return false;
		}
		
		// Set subscription status to expired.
		$subscription->update( array(
			'status' => 'expired',
		) );
	}

	/**
	 * Process the subscription suspended webhook.
	 *
	 * Processes the net.authorize.payment.authcapture.subscription.suspended webhook for subscription suspension.
	 *
	 * @since 1.9.0
	 *
	 * @param object $event_json Webhook data sent over from Authorize.net
	 *
	 * @return bool|\Give_Subscription
	 */
	public function process_webhook_subscription_suspended( $event_json ) {

		// Must be using latest Authorize with webhook support.
		if ( ! method_exists( Give_Authorize()->payments, 'setup_api_request' ) ) {
			give_record_gateway_error( 'Authorize.net Webhook Error', __( 'You are using an outdated version of the Authorize.net gateway. Please update to accept subscription suspended webhooks.', 'give-recurring' ) );

			return false;
		}

		$transaction_id = isset( $event_json->payload->id ) ? $event_json->payload->id : '';
		
		// Must have the transaction id.
		if ( empty( $transaction_id ) ) {
			return false;
		}

		$subscription = new Give_Subscription( $transaction_id, true );

		// Check for subscription ID.
		if ( 0 === $subscription->id ) {
			return false;
		}
		
		// Set subscription status to suspended.
		$subscription->update( array(
			'status' => 'suspended',
		) );
	}

	/**
	 * Process the renewal webhook.
	 *
	 * Processes the net.authorize.payment.authcapture.created webhook for subscription renewals.
	 *
	 * @since 1.8
	 *
	 * @param object $event_json Webhook data sent over from Authorize.net
	 *
	 * @return bool|\Give_Subscription
	 */
	public function process_webhook_renewal( $event_json ) {

		// Must be using latest Authorize with webhook support.
		if ( ! method_exists( Give_Authorize()->payments, 'setup_api_request' ) ) {
			give_record_gateway_error( 'Authorize.net eCheck Webhook Error', __( 'You are using an outdated version of the Authorize.net gateway. Please update to accept renewal webhooks.', 'give-recurring' ) );

			return false;
		}

		$transaction_id = isset( $event_json->payload->id ) ? $event_json->payload->id : '';

		// Must have the transaction id.
		if ( empty( $transaction_id ) ) {
			return false;
		}

		// Is this payment already recorded?
		if ( give_get_purchase_id_by_transaction_id( $transaction_id ) ) {

			// Payment already recorded.
			give_record_gateway_error( 'Authorize.net eCheck Webhook Error', sprintf( '%1$s <strong>%2$s</strong><br><br> %3$s',
					__( 'The Authorize.net webhook attempted to add a payment that has already been recorded.', 'give-recurring' ),
					__( 'Webhook:', 'give-recurring' ),
					wp_json_encode( $event_json )
				)
			);

			return false;

		}

		// Ok setup API request.
		$request = Give_Authorize()->payments->setup_api_request();

		// Get transaction details from API.
		$response = $request->getTransactionDetailsRequest( array(
			'transId' => $transaction_id,
		) );

		$subscription_profile_id = isset( $response->transaction->subscription->id ) ? $response->transaction->subscription->id : '';

		// Must have subscription id to continue.
		if ( empty( $subscription_profile_id ) ) {

			give_record_gateway_error( 'Authorize.net eCheck Webhook Error', sprintf( '%1$s <br><br> %2$s <br><br> <strong>%3$s</strong> <br><br> %4$s',
					__( 'The Recurring Authorize.net gateway could not find the subscription profile ID from the webhook data.', 'give-recurring' ),
					wp_json_encode( $event_json ),
					__( 'Response:', 'give-recurring' ),
					wp_json_encode( $response->transaction )
				)
			);

			return false;
		}

		$subscription = new Give_Subscription( $subscription_profile_id, true );

		// Check for subscription ID.
		if ( 0 === $subscription->id ) {

			give_record_gateway_error( 'Authorize.net eCheck Webhook Error', sprintf( '%1$s <br><br> %2$s <br><br> <strong>%3$s</strong> <br><br> %4$s',
					__( 'Give could not find the donor\'s subscription within the database from the response provided by the Authorize.net webhook.', 'give-recurring' ),
					wp_json_encode( $event_json ),
					__( 'Response:', 'give-recurring' ),
					wp_json_encode( $response->transaction )
				)
			);

			return false;
		}

		// Need the subscription payment number.
		if ( ! isset( $response->transaction->subscription->payNum ) ) {

			give_record_gateway_error( 'Authorize.net eCheck Webhook Error', sprintf( '%1$s <br><br> <strong>%2$s</strong> <br><br> %3$s',
					__( 'The Recurring Authorize.net gateway could not find the subscription payment number from the webhook data provided.', 'give-recurring' ),
					__( 'Response:', 'give-recurring' ),
					wp_json_encode( $event_json )
				)
			);

			return false;
		}

		$payment_number = intval( $response->transaction->subscription->payNum );

		// Is this the first payment for this subscription? If so, update the transaction ID.
		if ( 1 === $payment_number ) {

			$subscription->set_transaction_id( $transaction_id );

		} elseif ( $payment_number > 1 ) {

			// This is a renewal.
			$args = array(
				'amount'         => $response->transaction->authAmount,
				'transaction_id' => $transaction_id,
				'gateway'        => $subscription->gateway,
			);

			// We have a renewal.
			$subscription->add_payment( $args );
			$subscription->renew();

		}

		return $subscription;

	}

	/**
	 * Process the subscription cancelled webhook.
	 *
	 * Processes the net.authorize.customer.subscription.cancelled webhook for subscription cancellations.
	 *
	 * @since 1.8
	 *
	 * @param object $event_json Webhook data sent over from Authorize.net
	 *
	 * @return bool|\Give_Subscription
	 */
	public function process_webhook_cancel( $event_json ) {

		// Must be using latest Authorize with webhook support.
		if ( ! method_exists( Give_Authorize()->payments, 'setup_api_request' ) ) {
			return false;
		}

		$subscription_profile_id = isset( $event_json->payload->id ) ? $event_json->payload->id : '';

		// Must have subscription id to continue.
		if ( empty( $subscription_profile_id ) ) {
			return false;
		}

		$subscription = new Give_Subscription( $subscription_profile_id, true );

		// Check for subscription ID.
		if ( 0 === $subscription->id ) {
			return false;
		}

		$times_billed = $subscription->get_total_payments();

		// Is the subscription completed? Complete subscription if applicable.
		if ( $subscription->bill_times > 0 && $times_billed >= $subscription->bill_times ) {
			return false;
		}

		$subscription->cancel();

	}

	/**
	 * Link the recurring profile in Authorize.net.
	 *
	 * @since  1.8
	 *
	 * @param  string $profile_id   The recurring profile id
	 * @param  object $subscription The Subscription object
	 *
	 * @return string               The link to return or just the profile id.
	 */
	public function link_profile_id( $profile_id, $subscription ) {

		if ( ! empty( $profile_id ) ) {
			$html = '<a href="%s" target="_blank">' . $profile_id . '</a>';

			$payment  = new Give_Payment( $subscription->parent_payment_id );
			$base_url = 'live' === $payment->mode ? 'https://authorize.net/' : 'https://sandbox.authorize.net/';
			$link     = esc_url( $base_url . 'ui/themes/sandbox/ARB/SubscriptionDetail.aspx?SubscrID=' . $profile_id );

			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

	/**
	 * Require last name if authorize recurring donation.
	 *
	 * @since  1.8
	 *
	 * @param $form_id
	 */
	public function maybe_require_last_name( $form_id ) {

		$gateway   = isset( $_POST['give_payment_mode'] ) ? give_clean( $_POST['give_payment_mode'] ) : '';
		$recurring = give_is_form_recurring( $form_id );

		// On gateway change:
		// If authorize gateway require last name.
		if ( $this->id === $gateway && $recurring ) {
			add_filter( 'give_donation_form_required_fields', array( $this, 'require_last_name' ), 10, 2 );
		}

		// On page load:
		$default_gateway = give_get_default_gateway( $form_id );
		if (
			empty( $gateway )
			&& $this->id === $default_gateway
			&& give_is_gateway_active( $this->id )
			&& $recurring
		) {
			add_filter( 'give_donation_form_required_fields', array( $this, 'require_last_name' ), 10, 2 );
		}
	}

	/**
	 * Require Last Name.
	 *
	 * Authorize requires the last name field be completed and passed when creating subscriptions.
	 *
	 * @since 1.8
	 *
	 * @param $required_fields
	 * @param $form_id
	 *
	 * @return mixed
	 */
	function require_last_name( $required_fields, $form_id ) {

		$required_fields['give_last'] = array(
			'error_id'      => 'invalid_last_name',
			'error_message' => __( 'Please enter your last name.', 'give-recurring' ),
		);

		return $required_fields;
	}

	/**
	 * Get gateway subscription.
	 *
	 * @since 1.8
	 *
	 * @see   https://github.com/DevinWalker/Authorize.Net-XML/blob/master/examples/arb/ARBGetSubscriptionStatusRequest.php
	 *
	 * @param $subscription Give_Subscription
	 *
	 * @return bool|mixed
	 */
	public function get_subscription_details( $subscription ) {

		$authnet_xml = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );

		$authnet_xml->ARBGetSubscriptionRequest( array(
			'subscriptionId' => $subscription->profile_id,
		) );

		// Check for error.
		if ( 'error' === strtolower( $authnet_xml->messages->resultCode ) ) {
			return false;
		}

		$billing_period = $this->sync_format_billing_period( $authnet_xml );
		$frequency      = $authnet_xml->subscription->paymentSchedule->interval->length->__toString();

		// Get Frequency for the Week.
		if ( 'week' === $billing_period ) {
			$frequency = $frequency / 7;
		}

		$subscription_details = array(
			'status'         => $authnet_xml->subscription->status->__toString(),
			'created'        => strtotime( $authnet_xml->subscription->paymentSchedule->startDate->__toString() ),
			'billing_period' => $billing_period,
			'frequency'      => $frequency,
		);

		return $subscription_details;
	}

	/**
	 * Format the billing period for sync.
	 *
	 * @since 1.8
	 *
	 * @param $authnet_xml
	 *
	 * @return string
	 */
	public function sync_format_billing_period( $authnet_xml ) {

		$length         = $authnet_xml->subscription->paymentSchedule->interval->length->__toString();
		$unit           = $authnet_xml->subscription->paymentSchedule->interval->unit->__toString();
		$billing_period = '';

		switch ( true ) {
			case ( $length >= 7 && 'days' === $unit ) :
				$billing_period = 'week';
				break;
			case ( $length < 12 && 'months' === $unit ) :
				$billing_period = 'month';
				break;
			case ( $length >= 12 && 'months' === $unit ) :
				$billing_period = 'year';
				break;
		}

		return $billing_period;
	}

	/**
	 * Format the billing period for sync.
	 *
	 * @since 1.8
	 *
	 * @param $authnet_xml
	 *
	 * @return string
	 */
	public function sync_format_expiration( $authnet_xml ) {

		$expiration = $authnet_xml->subscription->paymentSchedule->startDate->__toString();
		$length     = $authnet_xml->subscription->paymentSchedule->interval->length->__toString();
		$unit       = $authnet_xml->subscription->paymentSchedule->interval->unit->__toString();

		$string = strtotime( $expiration . ' +' . $length . ' ' . $unit );

		return $string;
	}


	/**
	 * Get transactions.
	 *
	 * @since 1.8
	 * @see   https://community.developer.authorize.net/t5/Integration-and-Testing/Getting-transaction-details/td-p/14198
	 *
	 * @param \Give_Subscription $subscription
	 * @param string             $date
	 *
	 * @return array|bool
	 * @throws Exception     Throws an error message.
	 */
	public function get_gateway_transactions( $subscription, $date = '' ) {

		$start                 = new DateTime( '6 months ago' );
		$end                   = new DateTime();
		$interval              = new DateInterval( 'P1M' );
		$date_range            = new DatePeriod( $start, $interval, $end );
		$subscription_invoices = array();
		$transactions          = array();

		// Loop through the last 12-months.
		foreach ( $date_range as $date ) {

			$period_start     = $date->format( 'Y-m-d\TH:i:s' );
			$period_end       = $date->modify( '+1 month' )->format( 'Y-m-d\TH:i:s' );
			$authnet_invoices = $this->get_invoices_for_give_subscription( $subscription, $period_start, $period_end );

			// Authorize.net reporting Transactions API isn't enabled. Show error.
			if ( isset( $authnet_invoices[0] ) && 'E00011' === $authnet_invoices[0] ) {
				$error_message = sprintf( '%1$s <a href="%2$s" target="_blank">%3$s</a>.',
					__( 'Access denied. You do not have permissions to call the Authorize.net Transaction Details API. Please', 'give-recurring' ),
					'https://community.developer.authorize.net/t5/Integration-and-Testing/E00011-Access-denied-You-do-not-have-permission-to-call-the/m-p/28676#M15095',
					__( 'enable the Transactions Detail API', 'give-recurring' )
				);
				Give_Recurring()->synchronizer->print_notice( $error_message, 'error' );
				break;
			}

			// Grab invoices for each month.
			if ( ! empty( $authnet_invoices ) ) {
				array_push( $subscription_invoices, $authnet_invoices );
			}
		}

		// Bundle all invoices into one array formatted for synchronize.
		foreach ( $subscription_invoices as $invoice_set ) {

			foreach ( $invoice_set as $invoice ) {

				$transactions[ $invoice['transId'] ] = array(
					'amount'         => give_sanitize_amount( $invoice['settleAmount'] ),
					'date'           => strtotime( $invoice['submitTimeLocal'] ),
					'transaction_id' => $invoice['transId'],
				);

			}
		}

		return $transactions;
	}

	/**
	 * Get invoices for Authorize.net subscription.
	 *
	 * @since 1.8
	 *
	 * @param $subscription
	 * @param $start_date
	 * @param $end_date
	 *
	 * @return array|bool
	 */
	public function get_invoices_for_give_subscription( $subscription, $start_date, $end_date ) {

		$auth = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );

		// Get batches from Authorize.net.
		$args = array(
			'includeStatistics'   => true,
			'firstSettlementDate' => $start_date,
			'lastSettlementDate'  => $end_date,
		);
		$auth->getSettledBatchListRequest( $args );
		// Check for error.
		if ( 'error' === strtolower( $auth->messages->resultCode ) ) {
			return json_decode( wp_json_encode( (array) $auth->messages->message->code ), true );
		}

		// Create PHP array out of SimpleXML.
		$batches = json_decode( wp_json_encode( (array) $auth->batchList ), true );

		// Need batch to continue.
		if (
			! isset( $batches['batch'] )
			|| ! is_array( $batches['batch'] )
		) {
			return false;
		}

		// Some batches come in in single array without iterator.
		// Here we prepare those batches for our loop below.
		if ( ! isset( $batches['batch'][0] ) ) {
			$batches['batch'] = array( $batches['batch'] );
		}

		$transactions = array();

		// Loop through this batch and pick out subscription's transactions.
		foreach ( $batches['batch'] as $batch ) {

			// Need to get transactions for this specific batch.
			$auth2 = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );
			$auth2->getTransactionListRequest( array(
				'batchId' => $batch['batchId'],
			) );
			$batch_transactions = json_decode( wp_json_encode( (array) $auth2->transactions ), true );

			// Is this a multi-dimensional array of transactions?
			if (
				isset( $batch_transactions['transaction'][0] )
				&& is_array( $batch_transactions['transaction'][0] )
			) {
				// Loop through transactions in batch and check if any are for this subscription.
				foreach ( $batch_transactions['transaction'] as $transaction ) {
					$transactions = $this->setup_batch_transaction_array( $transaction, $transactions, $subscription );
				}
			} else {
				$transactions = $this->setup_batch_transaction_array( $batch_transactions['transaction'], $transactions, $subscription );
			}
		} // End foreach().

		return $transactions;
	}

	/**
	 * Setup batch transactions.
	 *
	 * @since 1.8
	 *
	 * This function checks conditionally if a transaction is part of the subscription.
	 * If it is then it is added to the subscription transactions' array.
	 *
	 * @param array              $transaction
	 * @param array              $transactions
	 * @param \Give_Subscription $subscription
	 *
	 * @return array
	 */
	function setup_batch_transaction_array( $transaction, $transactions, $subscription ) {

		/**
		 * Add transaction to array if:
		 *
		 * a: There is a subscription ID.
		 * b: If subscription ID's match.
		 * b: This isn't the first payment.
		 */
		if (
			isset( $transaction['subscription']['id'] )
			&& $subscription->profile_id === $transaction['subscription']['id']
			&& $transaction['subscription']['payNum'] !== '1'
		) {
			$transactions[ $transaction['transId'] ] = $transaction;
		}

		return $transactions;

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
			'authorize_echeck' === $subscription->gateway
			&& ! empty( $subscription->profile_id )
			&& in_array( $subscription->status, array(
				'active',
			), true )
		) {
			return true;
		}
		return $ret;
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

		// Set error if Profile id not set for Authorize eCheck.
		if( isset( $subscription->profile_id ) && empty( $subscription->profile_id ) ) {
			give_set_error( 'give_recurring_invalid_echeck_subscription', __( 'Missing Profile ID in Authorize eCheck Subscription.', 'give-recurring' ) );
		}

		// Is errors?
		$errors = give_get_errors();

		if ( ! $errors ) {
			// No errors in Authorize.net, continue on through processing.
			try {

				$authnet_xml = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );

				$args = array(
					'subscriptionId' => $subscription->profile_id,
					'subscription'   => array(
						'amount' => $renewal_amount,
					),
				);

				$authnet_xml->ARBUpdateSubscriptionRequest( $args );

				if ( ! $authnet_xml->isSuccessful() ) {

					if ( isset( $authnet_xml->messages->message ) ) {

						give_set_error( 'give_recurring_authorize_error', $authnet_xml->messages->message->code . ': ' . $authnet_xml->messages->message->text, 'give-recurring' );

					} else {

						give_set_error( 'give_recurring_authorize_error', __( 'There was an error updating your subscription.', 'give-recurring' ) );

					}

				}

			} catch ( Exception $e ) {

				give_set_error( 'give_recurring_authnet', $e );

			}
		}

	}

}


$give_recurring_authorize_echeck = new Give_Recurring_Authorize_eCheck();
