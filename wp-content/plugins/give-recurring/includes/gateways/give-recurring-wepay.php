<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_WePay
 */
class Give_Recurring_WePay extends Give_Recurring_Gateway {

	private $creds;
	private $wepay;
	private $name;
	private $plan_id;
	private $plan;
	private $iframe_url;

	public function init() {

		$this->id = 'wepay';

		//@TODO: REWRITE TO SUPPORT NEW API: THIS IS TEMPORARY
		return false;

		// Sanity Check for WePay
		if ( ! class_exists( 'WePay' ) || ! defined( 'GIVE_WEPAY_DIR' ) ) {
			return false;
		}

		//@TODO: AJAXify offsite payments so iframe displays better
		//Offsite WePay Payments requires JS for the iFrame
		if ( $this->onsite_payments() !== 'onsite' ) {

			//Tell parent Subscriptions class it's offsite
			$this->offsite = true;
			//Hook our completion method
			add_action( 'wp_loaded', array(
				$this,
				'process_offsite_wepay_completion'
			) );
		}

		$this->creds = $this->get_api_credentials();

		try {
			if ( give_is_test_mode() ) {
				WePay::useStaging( $this->creds['client_id'], $this->creds['client_secret'], GIVE_WEPAY_API_VERSION );
			} else {
				WePay::useProduction( $this->creds['client_id'], $this->creds['client_secret'], GIVE_WEPAY_API_VERSION );
			}
		} catch ( RuntimeException $e ) {
			// WePay env already been setup
		}

		$this->wepay = new WePay( $this->creds['access_token'] );

		//Cancellation action
		add_action( 'give_recurring_cancel_wepay_subscription', array( $this, 'cancel' ), 10, 2 );

		//Validate WePay Times Serverside
		add_action( 'save_post', array( $this, 'validate_recurring_period' ) );

	}

	/**
	 * Get API Credentials
	 *
	 * @return mixed|void
	 */
	public function get_api_credentials() {

		$give_options = give_get_settings();

		$creds                  = array();
		$creds['client_id']     = isset( $give_options['wepay_client_id'] ) ? trim( $give_options['wepay_client_id'] ) : '';
		$creds['client_secret'] = isset( $give_options['wepay_client_secret'] ) ? trim( $give_options['wepay_client_secret'] ) : '';
		$creds['access_token']  = isset( $give_options['wepay_access_token'] ) ? trim( $give_options['wepay_access_token'] ) : '';
		$creds['account_id']    = isset( $give_options['wepay_account_id'] ) ? trim( $give_options['wepay_account_id'] ) : '';

		return apply_filters( 'give_wepay_get_api_creds', $creds );
	}


	/**
	 * Initial field validation before ever creating profiles or customers
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {

		//@TODO: Validation that doesn't butt heads with non-recurring donations
		//if ( ! class_exists( 'WePay' ) ) {

			//give_set_error( 'give_recurring_wepay_missing', __( 'The WePay payment gateway does not appear to be activated.', 'give-recurring' ) );

		//}
	}

	/**
	 * Create Payment Profiles
	 *
	 * @return bool|string
	 */
	public function create_payment_profiles() {

		// No plan exists yet so create the subscription plan
		// Format a name for this subscription
		$this->name = give_recurring_generate_subscription_name( $this->subscriptions['form_id'], $this->subscriptions['price_id'] );

		// Format a plan ID
		$this->plan_id = $this->name . '_' . give_maybe_sanitize_amount( $this->subscriptions['recurring_amount'] ) . '_' . $this->subscriptions['period'];
		$this->plan_id = substr( sanitize_key( $this->plan_id ), 0, 254 );

		// See if a wepay subscription plan already exists
		$this->plan = $this->get_wepay_plan( $this->subscriptions );

		// No subscription plan exists
		if ( empty( $this->plan ) || ! is_object( $this->plan ) ) {
			// let's create one then...
			$this->plan = $this->create_wepay_plan( $this->subscriptions );
		}

		// Check that we have a WePay CC token
		if ( ! isset( $_POST['give_wepay_card'] ) && $this->onsite_payments() == 'onsite' ) {
			give_set_error( 'give_recurring_wepay_error', __( 'There was a problem tokenizing this credit card, please try again.', 'give-recurring' ) );

			return false;
		}

		// Get the success url
		$return_url = add_query_arg( array(
			'payment-confirmation' => $this->id
		), give_get_success_page_uri() );

		try {

			$request = array(
				'subscription_plan_id' => $this->plan->subscription_plan_id,
				'redirect_uri'         => $return_url,
				'callback_uri'         => home_url( 'index.php?give-listener=' . $this->id ),
				'mode'                 => $this->onsite_payments() == 'onsite' ? 'regular' : 'iframe',
				'reference_id'         => $this->plan_id
			);

			//@TODO: Determine fee_payer - WePay doesn't yet handle this in Subscriptions API:
			//Fee Payer pending API support
			//			$fee_payer = give_get_option( 'wepay_fee_payer' );
			//			if ( $fee_payer == 'donor' ) {
			//				$request['fee'] = array(
			//					'fee_payer' => 'payer'
			//				);
			//			} else {
			//				$request['fee'] = array(
			//					'fee_payer' => 'payee'
			//				);
			//			}

			//Add CC token for onsite
			if ( $this->onsite_payments() == 'onsite' ) {
				$request['payment_method_id']   = $_POST['give_wepay_card'];
				$request['payment_method_type'] = 'credit_card';
			} else {
				$request['prefill_info'] = array(
					'name'  => $this->purchase_data['post_data']['give_first'] . ' ' . $this->purchase_data['post_data']['give_last'],
					'email' => $this->purchase_data['post_data']['give_email']
				);
			}

			//Create the subscription w/ WePay
			$subscription = $this->wepay->request( 'subscription/create', $request );

			//Set subscription profile ID: muy importante
			$this->subscriptions['profile_id'] = $subscription->subscription_id;

			//Offsite goes to WePay to Pay
			if ( $this->onsite_payments() !== 'onsite' && isset( $subscription->subscription_uri ) ) {

				//Save the iframe URL for complete_signup() method
				$this->iframe_url = $subscription->subscription_uri;

			} else {

				//On-site
				return $subscription;

			}
		} catch ( WePayException $e ) {

			give_set_error( 'give_recurring_wepay_error', $e->getMessage() );

		}

		return false;
	}

	/**
	 * Process payments on-site or off?
	 *
	 * @access      public
	 * @since       1.0
	 * @return      string
	 */

	public static function onsite_payments() {

		$give_options = give_get_settings();

		return isset( $give_options['wepay_onsite_payments'] ) && $give_options['wepay_onsite_payments'] == 'onsite';
	}


	/**
	 * Complete checkout
	 */
	public function complete_signup() {

		//Redirect for onsite
		if ( $this->onsite_payments() == 'onsite' ) {
			wp_redirect( give_get_success_page_url() );
			exit;
		} else {
			//iframe output for offsite
			echo '<div id="wepay_subscription_container" style="margin: 50px auto; text-align:center;"></div><script type="text/javascript" src="https://www.wepay.com/min/js/iframe.wepay.js"></script><script type="text/javascript">WePay.iframe_checkout("wepay_subscription_container", "' . $this->iframe_url . '");</script>';
		}

	}

	/**
	 * Process an Offsite WePay Donation
	 *
	 */
	public function process_offsite_wepay_completion() {

		if ( isset( $_GET['subscription_id'] ) && $_GET['subscription_id'] ) {

			$db      = new Give_Subscriptions_DB();
			$payment = $db->get_subscriptions( array( 'profile_id' => $_GET['subscription_id'] ) );
			$payment = $payment[0];

			try {

				//Lookup subscription info
				$wepay_subscription = $this->wepay->request( 'subscription_charge/find', array(
					'subscription_id' => $_GET['subscription_id']
				) );

				//There's only ever 1 charge with Give so bump it up
				$wepay_subscription = isset( $wepay_subscription[0] ) ? $wepay_subscription[0] : '';

				//Check if the payment has been captured
				if ( isset( $wepay_subscription->state ) && $wepay_subscription->state == 'captured' ) {

					//Success: it has been captured
					give_update_payment_status( $payment->parent_payment_id, 'publish' );

					// Record transaction ID
					give_insert_payment_note( $payment->parent_payment_id, sprintf( __( 'WePay Subscription ID: %s', 'give-recurring' ), $wepay_subscription->subscription_id ) );
					give_set_payment_transaction_id( $payment->parent_payment_id, $wepay_subscription->subscription_charge_id );

					$donor_id = give_get_payment_donor_id( $payment->parent_payment_id );
					$subscriber  = new Give_Recurring_Subscriber( $donor_id );

					// Retrieve pending subscription from database and update it's status to active and set proper profile ID
					$subscription = $subscriber->get_subscription_by_profile_id( $wepay_subscription->subscription_id );
					$subscription->update( array(
						'profile_id' => $wepay_subscription->subscription_id,
						'status'     => 'active'
					) );

					//Remove query arg and redirect to updated page
					$refresh_url = remove_query_arg( 'subscription_id', give_get_success_page_uri() );
					wp_redirect( $refresh_url );
					exit;

				} else {

					add_action( 'give_payment_receipt_before_table', array(
						$this,
						'output_pending_wepay_transaction_message'
					) );

				}
			} catch ( WePayException $e ) {

				give_set_error( 'give_recurring_wepay_error', $e->getMessage() );
			}

		} else {

			give_record_gateway_error( 'give_recurring_wepay_offsite', __( 'The subscription ID for this WePay transaction is missing. Please contact support.', 'give-recurring' ) );

		}
	}

	/**
	 * Output Pending WePay Transaction Message
	 *
	 * This message displays when the user has completed an offsite transaction and has been redirected to the thank you page while the transaction is processing at WePay.
	 */
	public function output_pending_wepay_transaction_message() {

		Give()->notices->print_frontend_notice( __( 'We are still confirming the initial subscription charge with WePay. This can take a few minutes to complete.', 'give-recurring' ), true, 'warning' );

	}


	/**
	 * Create WePay Plan
	 *
	 * @param array $subscription
	 *
	 * @return string
	 *
	 */
	public function get_wepay_plan( $subscription = array() ) {

		//Lookup to see if this plan exists first
		$plan = $this->wepay->request( 'subscription_plan/find', array(
			'account_id'   => $this->creds['account_id'],
			'reference_id' => $this->plan_id
		) );

		return isset( $plan[0] ) ? $plan[0] : '';

	}

	/**
	 * Create WePay Plan
	 *
	 * @param array $subscription
	 *
	 * @return bool
	 */
	public function create_wepay_plan( $subscription = array() ) {

		// Format a description
		$description  = get_post_field( 'post_excerpt', $subscription['form_id'] );
		$form_content = give_get_meta( $subscription['form_id'], '_give_form_content', true );
		if ( ! empty( $form_content ) ) {
			$description = $form_content;
		} elseif ( empty( $description ) ) {
			$description = get_post_field( 'post_title', $subscription['form_id'] );
		}

		try {

			$plan = $this->wepay->request( 'subscription_plan/create', array(
				'account_id'        => $this->creds['account_id'],
				'name'              => substr( $this->name, 0, 254 ),
				'short_description' => substr( $description, 0, 2046 ), //limit imposed by WePay
				'period'            => $this->get_wepay_format_period( $subscription['period'] ),
				'amount'            => give_maybe_sanitize_amount( $subscription['recurring_amount'] ),
				'currency'          => give_get_currency(),
				'reference_id'      => $this->plan_id,
				'callback_uri'      => home_url( 'index.php?give-listener=' . $this->id )
			) );

		} catch ( WePayException $e ) {

			give_set_error( 'give_recurring_wepay_plan_error', __( 'The subscription plan in WePay could not be created, please try again.', 'give-recurring' ) );
			give_insert_payment_note( $this->subscriptions['id'], 'WePay Checkout Error: ' . $e->getMessage() );
			do_action( 'give_wepay_subscription_creation_failed', $e );

			return false;

		}

		return $plan;
	}


	/**
	 * Process WePay Webhooks
	 *
	 * @see: https://www.wepay.com/developer/reference/ipn
	 */
	public function process_webhooks() {

		//Check for this webhook
		if ( empty( $_GET['give-listener'] ) || $this->id !== $_GET['give-listener'] ) {
			return;
		}

		// retrieve the request's body and parse it as JSON
		$body = @file_get_contents( 'php://input' );

		//@TODO: this is a temporary option: remove prior to launch
		//$ipn_log = get_option( 'give_webpay_ipn_log' );
		//update_option( 'give_webpay_ipn_log', $ipn_log . ' ----  ' . $body );

		//WePay sends IPN like: subscription_charge_id=762241324
		$response = explode( '=', $body );

		if ( isset( $response[0] ) ) {

			switch ( $response[0] ) {

				// These are subscription charges; ie payments, refunds, etc.
				case 'subscription_charge_id':

					$this->process_subscription_charge_id( $response );
					break;

				// These are subscription changes; ie new sub created, cancelled, etc.
				case 'subscription_id':

					$this->process_subscription_id( $response );
					break;
			}
		}
	}


	/**
	 * Refund Recurring Payment
	 *
	 * @param $payment
	 * @param $parent_payment_id
	 * @param $amount
	 * @param $transaction_id
	 */
	public function refund_recurring_payment( $payment, $parent_payment_id, $amount, $transaction_id ) {

		//Refunded this payment
		give_update_payment_status( $payment->id, 'refunded' );

	}


	/**
	 * Get Total WePay Subscription Charges
	 *
	 * The best way to determine the number of times a subscriber has been charges is to make a /subscription API call and record the period and create_time parameters from the response. Then make a /subscription_charge call and record the create_time in the response. Then compare the create_time of the subscription to the create time of the subscription_charge and use the period to determine how many subscription charges have occurred for the subscription.
	 *
	 * @param $charge       object
	 * @param $subscription object
	 *
	 * @return int $times
	 */
	public function get_total_wepay_subscription_charges( $charge, $subscription ) {

		$sub_create_time    = intval( $subscription->create_time );
		$charge_create_time = intval( $charge->create_time );
		$times              = 0;

		switch ( $subscription->period ) {

			case 'week':
				//Seconds in a week = 604800
				$times = round( $charge_create_time - $sub_create_time / 604800 );
				break;
			case 'monthly':
				//Seconds in a month = 2592000
				$times = round( $charge_create_time - $sub_create_time / 2592000 );
				break;
			case 'year':
				//Seconds in a month = 31556952
				$times = round( $charge_create_time - $sub_create_time / 31556952 );
				break;

		}

		return $times;

	}

	/**
	 * Get WePay Formatted Period
	 *
	 * WePay supports a period for each subscription. Either "weekly", "monthly", "yearly", or "quarterly".
	 *
	 * @see https://www.wepay.com/developer/reference/subscription#create
	 *
	 * @param $period
	 *
	 * @return string
	 */
	public function get_wepay_format_period( $period ) {

		switch ( $period ) {
			case 'week':
				$period = 'weekly';
				break;
			case 'month':
				$period = 'monthly';
				break;
			case 'year':
				$period = 'yearly';
				break;
		}

		return $period;
	}

	/**
	 * Cancels a subscription
	 *
	 * @param $subscription
	 * @param $valid
	 *
	 * @return bool
	 */
	public function cancel( $subscription, $valid ) {

		if ( empty( $valid ) ) {
			return false;
		}

		//Cancel with WePay
		//@TODO: WePay allows for a "reason" and it would be cool to ask why the donor is unsubscribing
		$wepay_subscription = $this->wepay->request( 'subscription/cancel', array(
			'subscription_id' => $subscription->profile_id
		) );

		if ( isset( $wepay_subscription->state ) && strtolower( $wepay_subscription->state ) == 'cancelled' ) {
			return true;
		} else {
			return false;
		}


	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @access      public
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if (
			$subscription->gateway === 'wepay'
			&& ! empty( $subscription->profile_id )
			&& 'active' === $subscription->status
		) {
			$ret = true;
		}

		return $ret;

	}


	/**
	 * Get Give WePay Subscription
	 *
	 * @param $wepay_subscription
	 *
	 * @return bool|Give_Subscription|void
	 */
	public function get_give_wepay_subscription( $wepay_subscription ) {
		$user       = get_user_by( 'email', $wepay_subscription->payer_email );
		$user_id    = $user ? $user->ID : false;
		$subscriber = new Give_Recurring_Subscriber( $user_id, true );

		if ( $subscriber->id < 1 ) {
			return false;
		}

		$subscription = $subscriber->get_subscription_by_profile_id( $wepay_subscription->subscription_id );
		if ( empty( $subscription ) ) {
			return false;
		}

		return $subscription;
	}


	/**
	 * Validate WePay Recurring Donation Period
	 *
	 * Additional server side validation for Standard recurring
	 *
	 * @param int $form_id
	 *
	 * @return mixed
	 */
	function validate_recurring_period( $form_id = 0 ) {

		global $post;
		$recurring_option = isset( $_REQUEST['_give_recurring'] ) ? $_REQUEST['_give_recurring'] : 'no';
		$set_or_multi     = isset( $_REQUEST['_give_price_option'] ) ? $_REQUEST['_give_price_option'] : '';

		//Sanity Checks
		if ( ! class_exists( 'Give_Recurring' ) ) {
			return $form_id;
		}
		if ( $recurring_option == 'no' ) {
			return $form_id;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return $form_id;
		}
		if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
			return $form_id;
		}
		if ( ! isset( $post->post_type ) || $post->post_type != 'give_forms' ) {
			return $form_id;
		}
		if ( ! current_user_can( 'edit_give_forms', $form_id ) ) {
			return $form_id;
		}

		//Is this gateway active
		if ( ! give_is_gateway_active( $this->id ) ) {
			return $form_id;
		}

		$message = __( 'WePay does not allow for daily recurring donations. Please select a period other than daily.', 'give-recurring' );

		if ( $set_or_multi === 'multi' && $recurring_option == 'yes_admin' ) {


			$prices = isset( $_REQUEST['_give_donation_levels'] ) ? $_REQUEST['_give_donation_levels'] : array( '' );
			foreach ( $prices as $price_id => $price ) {
				$period = isset( $price['_give_period'] ) ? $price['_give_period'] : 0;

				if ( $period === 'day' ) {
					wp_die( $message, __( 'Error', 'give-recurring' ), array( 'response' => 400 ) );
				}

			}

		} elseif ( Give_Recurring()->is_recurring( $form_id ) ) {

			$period = isset( $_REQUEST['_give_period'] ) ? $_REQUEST['_give_period'] : 0;

			if ( $period === 'day' ) {
				wp_die( $message, __( 'Error', 'give-recurring' ), array( 'response' => 400 ) );
			}
		}

		return $form_id;

	}


	/**
	 * Process Subscription Charge ID Callback
	 *
	 * @param $response
	 */
	public function process_subscription_charge_id( $response ) {

		// Lookup charge information
		$wepay_charge = $this->wepay->request( 'subscription_charge', array(
			'subscription_charge_id' => $response[1],
		) );

		// Lookup subscription info
		$wepay_subscription = $this->wepay->request( 'subscription', array(
			'subscription_id' => $wepay_charge->subscription_id
		) );

		// Get subscription
		$subscription = $this->get_give_wepay_subscription( $wepay_subscription );

		// Determine if this is the first payment or not
		$times_charged = $this->get_total_wepay_subscription_charges( $wepay_charge, $wepay_subscription );

		// This is a new payment & times charges = 0
		if ( $wepay_charge->state == 'captured' && $times_charged == 0 ) {

			// First time charge went through fine, it has already been marked as complete

		} elseif ( $wepay_charge->state == 'failed' && $times_charged == 0 ) {

			// First transaction failed, mark as such
			give_update_payment_status( $subscription->parent_payment_id, 'failed' );


		} elseif ( $wepay_charge->state == 'refunded' && $times_charged == 0 ) {

			// WePay admin has refunded first transaction
			give_update_payment_status( $subscription->parent_payment_id, 'refunded' );


		} elseif ( $wepay_charge->state == 'refunded' && $times_charged >= 1 ) {

			// WePay admin has refunded a recurring transaction
			give_update_payment_status( $subscription->parent_payment_id, 'refunded' );

			// Add a payment that will be refunded later
			$subscription->add_payment( array(
				'amount'         => $wepay_charge->amount,
				'transaction_id' => $wepay_charge->subscription_charge_id
			) );
			// Hook in after the payment above is created and refund the payment:
			add_action( 'give_recurring_record_payment', array(
				$this,
				'refund_recurring_transaction'
			), 10, 4 );

		} elseif ( $wepay_charge->state == 'captured' && $times_charged >= 1 ) {

			// Renewal when a user makes a recurring payment
			$subscription->add_payment( array(
				'amount'         => $wepay_charge->amount,
				'transaction_id' => $wepay_charge->subscription_charge_id
			) );
			$subscription->renew();

		}

	}


	/**
	 * Process Subscription ID
	 *
	 * @param $response
	 */
	public function process_subscription_id( $response ) {

		$wepay_subscription = $this->wepay->request( 'subscription', array(
			'subscription_id' => $response[1]
		) );

		if ( $wepay_subscription->state == 'cancelled' ) {
			$subscription = $this->get_give_wepay_subscription( $wepay_subscription );
			$subscription->cancel();
		}

	}

}

new Give_Recurring_WePay;