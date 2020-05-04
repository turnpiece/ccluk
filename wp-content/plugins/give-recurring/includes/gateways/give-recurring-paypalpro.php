<?php
/**
 * PayPal Websites Payments Pro Recurring Gateway
 *
 * Relevant Links (PayPal makes it tough to find them)
 *
 * CreateRecurringPaymentsProfile API Operation (NVP) - https://developer.paypal.com/docs/classic/api/merchant/CreateRecurringPaymentsProfile_API_Operation_NVP/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_PayPal_Website_Payments_Pro
 */
class Give_Recurring_PayPal_Website_Payments_Pro extends Give_Recurring_Gateway {

	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	private $api_endpoint;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * Get things rollin'
	 *
	 * @since 1.0
	 */
	public function init() {

		$this->id = 'paypalpro';

		if ( give_is_test_mode() ) {
			$this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			$this->api_endpoint = 'https://api-3t.paypal.com/nvp';
		}

		$creds = $this->get_paypal_api_credentials();

		$this->username  = $creds['username'];
		$this->password  = $creds['password'];
		$this->signature = $creds['signature'];

		// Cancellation action
		add_action( 'give_recurring_cancel_' . $this->id . '_subscription', array( $this, 'cancel' ), 10, 2 );

	}

	/**
	 * Retrieve PayPal API credentials
	 *
	 * @access      public
	 * @since       1.0
	 */
	public function get_paypal_api_credentials() {

		$prefix = 'live_';

		if ( give_is_test_mode() ) {
			$prefix = 'test_';
		}

		$creds = array(
			'username'  => give_get_option( $prefix . 'paypal_api_username' ),
			'password'  => give_get_option( $prefix . 'paypal_api_password' ),
			'signature' => give_get_option( $prefix . 'paypal_api_signature' ),
		);

		return apply_filters( 'give_recurring_get_paypal_api_credentials', $creds );
	}

	/**
	 * Validate Fields.
	 *
	 * Validate additional fields during checkout submission.
	 *
	 * @since      1.0
	 *
	 * @param $data
	 * @param $posted
	 */
	public function validate_fields( $data, $posted ) {

		if ( empty( $this->username ) || empty( $this->password ) || empty( $this->signature ) ) {
			give_set_error( 'give_recurring_no_paypal_api', __( 'It appears that you have not configured PayPal API access. Please configure it in Give &rarr; Settings', 'give-recurring' ) );
		}
	}


	/**
	 * Create payment profiles.
	 *
	 * @see   : https://developer.paypal.com/webapps/developer/docs/classic/api/NVPAPIOverview/#id09C2F0K30L7
	 *
	 * @since 1.0
	 */
	public function create_payment_profiles() {

		$street  = isset( $this->purchase_data['card_info']['card_address'] ) ? sanitize_text_field( $this->purchase_data['card_info']['card_address'] ) : '';
		$street2 = isset( $this->purchase_data['card_info']['card_address_2'] ) ? sanitize_text_field( $this->purchase_data['card_info']['card_address_2'] ) : '';
		$city    = isset( $this->purchase_data['card_info']['card_city'] ) ? sanitize_text_field( $this->purchase_data['card_info']['card_city'] ) : '';
		$state   = isset( $this->purchase_data['card_info']['card_state'] ) ? sanitize_text_field( $this->purchase_data['card_info']['card_state'] ) : '';
		$country = isset( $this->purchase_data['card_info']['card_country'] ) ? sanitize_text_field( $this->purchase_data['card_info']['card_country'] ) : '';

		// https://developer.paypal.com/docs/classic/api/merchant/CreateRecurringPaymentsProfile_API_Operation_NVP/
		$args = array(
			'USER'                => $this->username,
			'PWD'                 => $this->password,
			'SIGNATURE'           => $this->signature,
			'VERSION'             => '124',
			// Credit Card Details Fields
			'CREDITCARDTYPE'      => '',
			'ACCT'                => sanitize_text_field( $this->purchase_data['card_info']['card_number'] ),
			'EXPDATE'             => sanitize_text_field( $this->purchase_data['card_info']['card_exp_month'] . $this->purchase_data['card_info']['card_exp_year'] ),
			'EMAIL'               => sanitize_email( $this->purchase_data['user_email'] ),
			'STREET'              => $street,
			'STREET2'             => $street2,
			'CITY'                => $city,
			'STATE'               => $state,
			'COUNTRYCODE'         => $country,
			// needs to be in the format 062019
			'CVV2'                => sanitize_text_field( $this->purchase_data['card_info']['card_cvc'] ),
			'ZIP'                 => sanitize_text_field( $this->purchase_data['card_info']['card_zip'] ),
			'METHOD'              => 'CreateRecurringPaymentsProfile',
			'PROFILESTARTDATE'    => date( 'Y-m-d\Tg:i:s', strtotime( '+' . $this->subscriptions['frequency'] . ' ' . $this->subscriptions['period'], current_time( 'timestamp' ) ) ),
			// Billing Amount & Frequency
			'BILLINGPERIOD'       => ucwords( $this->subscriptions['period'] ),
			'BILLINGFREQUENCY'    => $this->subscriptions['frequency'],
			'AMT'                 => give_maybe_sanitize_amount( $this->subscriptions['recurring_amount'] ),
			'TOTALBILLINGCYCLES'  => $this->subscriptions['bill_times'] > 1 ? $this->subscriptions['bill_times'] - 1 : 0,
			// Subtract 1 from bill time if set because donors are charged an initial payment by PayPal to begin the subscription
			'CURRENCYCODE'        => strtoupper( give_get_currency() ),
			// Donor Details
			'FIRSTNAME'           => sanitize_text_field( $this->purchase_data['user_info']['first_name'] ),
			'LASTNAME'            => sanitize_text_field( $this->purchase_data['user_info']['last_name'] ),
			'INITAMT'             => give_maybe_sanitize_amount( $this->subscriptions['initial_amount'] ),
			'ITEMAMT'             => give_maybe_sanitize_amount( $this->subscriptions['recurring_amount'] ),
			'SHIPPINGAMT'         => 0,
			'TAXAMT'              => 0,
			// Description of the recurring payment.
			'DESC'                => substr( give_recurring_generate_subscription_name( $this->subscriptions['form_id'], $this->subscriptions['price_id'] ), 0, 126 ),
			// Additional params
			'CUSTOM'              => $this->user_id,
			// Used with IPN
			'BUTTONSOURCE'        => 'givewp_SP',
			'FAILEDINITAMTACTION' => 'CancelOnFailure',
			'PROFILEREFERENCE'    => $this->purchase_data['purchase_key'],
		);

		$response = $this->make_paypal_api_request( $args );
		$ret      = false;

		if ( false !== $response ) {

			if ( isset( $response['ACK'] ) ) {

				switch ( strtolower( $response['ACK'] ) ) {
					case 'success':
						// Bingo: Set profile ID
						$this->set_paypal_profile_id( $response );
						$ret = true;
						break;
					case 'successwithwarning':
						$this->set_paypal_profile_id( $response );
						$this->paypal_error( $response, false ); // Passing second param as false to prevent give_set_error which will hault subscription creation
						$ret = true;
						break;
					case 'failure':
						$this->paypal_error( $response );
						break;
					case 'failurewithwarning':
						$this->paypal_error( $response );
						break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Verifies IPN data.
	 *
	 * @return boolean
	 */
	public function verify_ipn() {

		// Set initial post data to empty string
		$post_data = '';

		// Fallback just in case post_max_size is lower than needed
		if ( ini_get( 'allow_url_fopen' ) ) {
			$post_data = file_get_contents( 'php://input' );
		} else {
			// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
			ini_set( 'post_max_size', '12M' );
		}

		// Start the encoded data collection with notification command
		$encoded_data = 'cmd=_notify-validate';

		// Get current arg separator
		$arg_separator = give_get_php_arg_separator_output();

		// Verify there is a post_data
		if ( $post_data || strlen( $post_data ) > 0 ) {

			// Append the data
			$encoded_data .= $arg_separator . $post_data;

		} else {

			// Check if POST is empty
			if ( empty( $_POST ) ) {

				// Nothing to do
				return false;

			} else {

				// Loop through each POST
				foreach ( $_POST as $key => $value ) {

					// Encode the value and append the data
					$encoded_data .= $arg_separator . "$key=" . urlencode( $value );

				}
			}
		}

		// Convert collected post data to an array
		parse_str( $encoded_data, $encoded_data_array );

		// Is the PP verification disabled? If so, skip.
		$verification_option = give_get_option( 'paypal_verification' );

		if ( 'disabled' !== $verification_option && ! give_is_test_mode() ) {

			// Validate the IPN
			$remote_post_vars = array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => array(
					'host'         => 'www.paypal.com',
					'connection'   => 'close',
					'content-type' => 'application/x-www-form-urlencoded',
					'post'         => '/cgi-bin/webscr HTTP/1.1',

				),
				'body'        => $encoded_data_array,
			);

			// Get response
			$api_response = wp_remote_post( give_get_paypal_redirect(), $remote_post_vars );
			$body         = wp_remote_retrieve_body( $api_response );

			if ( is_wp_error( $api_response ) ) {
				give_record_gateway_error( __( 'IPN Error', 'give-recurring' ), sprintf( __( 'Invalid PayPal Pro IPN verification response. IPN data: %s', 'give-recurring' ), json_encode( $api_response ) ) );
				status_header( 401 );

				return false; // Something went wrong
			}

			if ( $body !== 'VERIFIED' ) {
				status_header( 401 );
				give_record_gateway_error( __( 'IPN Error', 'give-recurring' ), sprintf( __( 'Invalid PayPal Pro IPN verification response. IPN data: %s', 'give-recurring' ), json_encode( $api_response ) ) );

				return false; // Response not okay
			}
		}// End if().

		return true;

	}

	/**
	 * Verifies if the currency code is the same as the one set in Give.
	 *
	 * @param  array $data
	 *
	 * @return boolean
	 */
	public function verify_currency_code_from_ipn( $data ) {

		$currency_code = isset( $data['currency_code'] ) ? $data['currency_code'] : '';

		if ( empty( $currency_code ) && isset( $data['mc_currency'] ) ) {
			$currency_code = $data['mc_currency'];
		}

		return ( strtolower( $currency_code ) === strtolower( give_get_currency() ) );
	}

	/**
	 * Generates Payment Data array which is used in logging and in other methods.
	 *
	 * @param  array $data
	 *
	 * @return array
	 */
	private function generate_payment_data_from_ipn( $data ) {

		$payment_data = array();

		// Check if it is a payment transaction
		if ( isset( $data['mc_gross'] ) ) {

			// Setup the payment info in an array for storage
			$amount       = number_format( (float) $data['mc_gross'], 2 );
			$payment_data = array(
				'date'           => date( 'Y-m-d g:i:s', strtotime( $data['payment_date'], current_time( 'timestamp' ) ) ),
				'subscription'   => isset( $data['product_name'] ) ? $data['product_name'] : '',
				'payment_type'   => isset( $data['txn_type'] ) ? $data['txn_type'] : '',
				'amount'         => $amount,
				'user_email'     => isset( $data['payer_email'] ) ? $data['payer_email'] : '',
				'transaction_id' => isset( $data['txn_id'] ) ? $data['txn_id'] : '',
			);
		}

		return $payment_data;
	}

	/**
	 * Get Give_Subscription from ipn
	 *
	 * @param  array $data
	 *
	 * @return Give_Subscription|bool
	 */
	public function get_subscription_from_ipn( $data ) {

		$subscription_profile_id = isset( $data['recurring_payment_id'] ) ? $data['recurring_payment_id'] : '';

		if ( empty( $subscription_profile_id ) ) {
			give_record_gateway_error( __( 'Invalid PayPal IPN', 'give-recurring' ), __( 'The PayPal IPN request received referred to a non-existent subscription profile ID. Please contact support.', 'give-recurring' ) );

			return false;
		}

		$subscription = new Give_Subscription( $subscription_profile_id, true );

		return $subscription;
	}

	/**
	 * Adds a payment to a subscription from IPN and renews it.
	 *
	 * @param  array $data
	 *
	 * @return array|bool
	 */
	public function pay_and_renew_subscription_from_ipn( $data ) {

		$payment_data = $this->generate_payment_data_from_ipn( $data );
		$subscription = $this->get_subscription_from_ipn( $data );

		// Need a subscription to continue.
		if ( empty( $subscription ) ) {
			return false;
		}

		$transaction_type = isset( $data['txn_type'] ) ? $data['txn_type'] : '';

		if ( 'recurring_payment' === $transaction_type ) {

			if ( 0 !== $subscription->id ) {

				$subscription->add_payment( array(
					'amount'         => $payment_data['amount'],
					'transaction_id' => $payment_data['transaction_id'],
				) );
				$subscription->renew();

				$is_success = true;
				$message    = __( 'Subscription donation successful', 'give-recurring' );

			} else {

				$is_success = false;
				$message    = __( 'Subscription could not be renewed due to error', 'give-recurring' );
			}
		} else {

			$is_success = false;
			$message    = __( 'Invalid IPN for pay and renew transaction', 'give-recurring' );
		}

		return compact( 'is_success', 'message' );
	}


	/**
	 * Cancels a subscription from IPN.
	 *
	 * @param  array $data
	 *
	 * @return array|bool
	 */
	public function cancel_subscription_from_ipn( $data ) {

		$subscription     = $this->get_subscription_from_ipn( $data );
		$transaction_type = isset( $data['txn_type'] ) ? $data['txn_type'] : '';
		$payment_status   = isset( $data['payment_status'] ) ? strtolower( $data['payment_status'] ) : '';

		// Need a subscription to continue.
		if ( empty( $subscription ) ) {
			return false;
		}

		if ( 'recurring_payment_profile_cancel' === $transaction_type || ( 'web_accept' === $transaction_type && 'voided' === $payment_status ) ) {

			if ( 0 !== $subscription->id ) {

				$subscription->cancel();
				$is_success = true;
				$message    = __( 'Subscription Cancelled', 'give-recurring' );

			} else {

				$is_success = false;
				$message    = __( 'Subscription could not be cancelled due to error', 'give-recurring' );
			}
		} else {

			$is_success = false;
			$message    = __( 'Invalid IPN to cancel a subscription', 'give-recurring' );
		}

		return compact( 'is_success', 'message' );
	}

	/**
	 * Expires a subscription from IPN
	 *
	 * Note: PayPal IPN calls it "Expire" but we call it "Complete"
	 *
	 * @param  array $data
	 *
	 * @return array|bool
	 */
	public function expire_subscription_from_ipn( $data ) {

		$subscription     = $this->get_subscription_from_ipn( $data );
		$transaction_type = isset( $data['txn_type'] ) ? $data['txn_type'] : '';
		$payment_status   = isset( $data['payment_status'] ) ? strtolower( $data['payment_status'] ) : '';

		// Need a subscription to continue.
		if ( empty( $subscription ) ) {
			return false;
		}

		if ( 'recurring_payment_expired' === $transaction_type || ( 'web_accept' === $transaction_type && 'expired' === $payment_status ) ) {

			if ( 0 !== $subscription->id ) {

				$subscription->complete();
				$is_success = true;
				$message    = __( 'Subscription marked as complete', 'give-recurring' );

			} else {

				$is_success = false;
				$message    = __( 'Subscription could not be completed due to error', 'give-recurring' );
			}
		} else {

			$is_success = false;
			$message    = __( 'Invalid IPN to complete a subscription', 'give-recurring' );
		}

		return compact( 'is_success', 'message' );
	}


	/**
	 * Process webhooks.
	 *
	 * @since 1.0
	 */
	public function process_webhooks() {

		if ( empty( $_GET['give-listener'] ) || $this->id !== $_GET['give-listener'] ) {
			return;
		}

		nocache_headers();

		$posted                    = apply_filters( 'give_recurring_ipn_post', $_POST ); // allow $_POST to be modified
		$verified                  = $this->verify_ipn();
		$is_currency_code_verified = $this->verify_currency_code_from_ipn( $posted );
		$payment_data              = $this->generate_payment_data_from_ipn( $posted );
		$die_status                = '';

		// Must have verified IPN or be in test mode.
		if ( ! $verified ) {
			status_header( 400 );
			give_record_gateway_error( __( 'Invalid PayPal IPN', 'give-recurring' ), __( 'The PayPal IPN request received was invalid. Please contact support.', 'give-recurring' ) );
			give_die( __( 'Invalid IPN', 'give-recurring' ) );
		}

		status_header( 200 );

		if ( $is_currency_code_verified ) {

			// Subscription/Recurring IPN variables
			// @see: https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
			switch ( $posted['txn_type'] ) :

				case 'recurring_payment':
					$result = $this->pay_and_renew_subscription_from_ipn( $posted );
					break;

				case 'recurring_payment_profile_cancel':
					$result = $this->cancel_subscription_from_ipn( $posted );
					break;

				case 'recurring_payment_failed':
					$result = array(
						'is_success' => true,
						'message'    => __( 'Recurring Donation Failed', 'give-recurring' ),
					);
					// @TODO: need to figure out failed payments
					break;

				case 'recurring_payment_expired':
					$result = $this->expire_subscription_from_ipn( $posted );
					break;
				default:
					// Pass off for other gateways (like Payflow)
					do_action( 'give_recurring_paypalpro_ipn', $posted, $posted['txn_type'] );

			endswitch;

			// If not successful and there's payment_data.
			if (
				isset( $result['is_success'] )
				&& ! empty( $payment_data )
				&& ! $posted['txn_type'] !== 'web_accept'
			) {

				$die_status = isset( $result['message'] ) ? $result['message'] : '';
				$message    = $die_status . sprintf( __( 'Payment Data : %s', 'give-recurring' ), json_encode( $payment_data ) );
				give_record_gateway_error( __( 'Error Processing IPN Transaction', 'give-recurring' ), $message );
			}
		} else {

			give_record_gateway_error( __( 'Invalid Currency Code', 'give-recurring' ), sprintf( __( 'The currency code in an IPN request did not match the site currency code. Payment data: %s', 'give-recurring' ), json_encode( $payment_data ) ) );
			$die_status = __( 'Invalid Currency Code', 'give-recurring' );
		}// End if().

		give_die( $die_status );

	}


	/**
	 *  PayPal Error
	 *
	 *  Example error:
	 *  array (size=9)
	 *      'TIMESTAMP' => string '2015-11-25T19:56:04Z' (length=20)
	 *      'CORRELATIONID' => string 'e8d76aea1a5ec' (length=13)
	 *      'ACK' => string 'Failure' (length=7)
	 *      'VERSION' => string '0.000000' (length=8)
	 *      'BUILD' => string '000000' (length=6)
	 *      'L_ERRORCODE0' => string '10002' (length=5)
	 *      'L_SHORTMESSAGE0' => string 'Authentication/Authorization Failed' (length=35)
	 *      'L_LONGMESSAGE0' => string 'You do not have permissions to make this API call' (length=49)
	 *      'L_SEVERITYCODE0' => string 'Error' (length=5)
	 *
	 * @param $data
	 * @param $should_set_give_error
	 */
	public function paypal_error( $data, $should_set_give_error = true ) {

		$error = '<p>' . __( 'There was a warning or error while creating subscription', 'give-recurring' ) . '</p>';

		if ( isset( $data['L_LONGMESSAGE0'] ) && ! empty( $data['L_LONGMESSAGE0'] ) ) {
			$error .= '<p>' . __( 'Error message:', 'give-recurring' ) . ' ' . $data['L_LONGMESSAGE0'] . '</p>';
			$error .= '<p>' . __( 'Error code:', 'give-recurring' ) . ' ' . $data['L_ERRORCODE0'] . '</p>';

			if ( true === $should_set_give_error ) {
				give_set_error( $data['L_ERRORCODE0'], $data['L_LONGMESSAGE0'] );
			}
		}

		give_record_gateway_error( $error, __( 'Error', 'give-recurring' ) );
	}


	/**
	 * Set PayPal Profile ID
	 * Example Response:
	 * array (size=8)
	 * 'PROFILEID' => string 'I-37F7HH6KS9LU' (length=14)
	 * 'PROFILESTATUS' => string 'PendingProfile' (length=14)
	 * 'TRANSACTIONID' => string '37K6289641898890S' (length=17)
	 * 'TIMESTAMP' => string '2015-11-24T22:25:10Z' (length=20)
	 * 'CORRELATIONID' => string 'f28bb3dcdff0d' (length=13)
	 * 'ACK' => string 'Success' (length=7)
	 * 'VERSION' => string '121' (length=3)
	 * 'BUILD' => string '000000' (length=6)
	 *
	 * @param $data
	 */
	public function set_paypal_profile_id( $data ) {

		// Successful subscription
		if ( isset( $data['PROFILEID'] ) && ( 'ActiveProfile' == $data['PROFILESTATUS'] || 'PendingProfile' == $data['PROFILESTATUS'] ) ) {
			// Set subscription profile ID for this subscription
			$this->subscriptions['profile_id'] = $data['PROFILEID'];
		}
	}


	/**
	 * Can Cancel
	 *
	 * Determines if the subscription can be cancelled
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if (
			/**
			 * Conditions to cancel:
			 * a: This is in fact PayPal Pro (NVP).
			 * b. There is a profile ID present.
			 * c. The current status is active.
			 */
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
			'paypalpro' === $subscription->gateway
			&& ! empty( $subscription->profile_id )
			&& in_array( $subscription->status, array(
				'active',
				'failing',
			), true )
		) {
			return true;
		}

		return $ret;
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
			'paypalpro' === $subscription->gateway
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
	 *
	 * @since 1.3
	 *
	 * @return bool
	 */
	private function check_credentials() {
		// Check credentials
		if (
			empty( $this->username )
			|| empty( $this->password )
			|| empty( $this->signature )
		) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Cancels a subscription
	 *
	 * @see: https://developer.paypal.com/docs/classic/api/merchant/ManageRecurringPaymentsProfileStatus_API_Operation_NVP/
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

		// Cancel with PayPal
		$args = array(
			'USER'      => $this->username,
			'PWD'       => $this->password,
			'SIGNATURE' => $this->signature,
			'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
			'PROFILEID' => $subscription->profile_id,
			'VERSION'   => '121',
			'ACTION'    => 'Cancel',
		);

		$response = $this->make_paypal_api_request( $args );
		$ret      = false;

		if ( false !== $response ) {

			switch ( strtolower( $response['ACK'] ) ) {
				case 'success' :
					$ret = true;
					break;
				case 'failure' :
					$error = '<p>' . __( 'PayPal subscription cancellation failed.', 'give-recurring' ) . '</p>';
					if ( isset( $response['L_LONGMESSAGE0'] ) && ! empty( $response['L_LONGMESSAGE0'] ) ) {
						$error .= '<p>' . __( 'Error message:', 'give-recurring' ) . ' ' . $response['L_LONGMESSAGE0'] . '</p>';
						$error .= '<p>' . __( 'Error code:', 'give-recurring' ) . ' ' . $response['L_ERRORCODE0'] . '</p>';
					}
					give_die( __( 'Error: ', 'give-recurring' ) . $error, __( 'Error', 'give-recurring' ), array(
						'response' => 403,
					) );

					break;
			}
		}

		return $ret;
	}

	/**
	 * Make PayPal API Request
	 *
	 * @param $args
	 *
	 * @return bool
	 */
	public function make_paypal_api_request( $args ) {

		$request = wp_remote_post( $this->api_endpoint, array(
			'timeout'     => 500,
			'sslverify'   => false,
			'body'        => $args,
			'httpversion' => '1.1',
		) );

		if ( is_wp_error( $request ) ) {

			// Its a WP_Error
			give_set_error( 'give_recurring_paypal_pro_request_error', __( 'An unidentified error occurred, please try again. Error:' . $request->get_error_message(), 'give-recurring' ) );
			$ret = false;

		} elseif ( 200 == $request['response']['code'] && 'OK' == $request['response']['message'] ) {

			// Ok, we have a paypal OK
			parse_str( $request['body'], $data );
			$ret = $data;

		} else {

			// We don't know what the error is
			give_set_error( 'give_recurring_paypal_pro_generic_error', __( 'Something has gone wrong, please try again', 'give-recurring' ) );
			$ret = false;
		}

		return $ret;
	}

	/**
	 * Determines if the subscription can be synced.
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

		if (
			$subscription->gateway === $this->id
			&& $this->check_credentials()
			&& ! empty( $subscription->profile_id )
		) {
			return true;
		}

		return $ret;

	}

	/**
	 * Get subscription details.
	 *
	 * @param Give_Subscription $subscription
	 *
	 * @return array|bool
	 */
	public function get_subscription_details( $subscription ) {

		$args = array(
			'USER'      => $this->username,
			'PWD'       => $this->password,
			'SIGNATURE' => $this->signature,
			'METHOD'    => 'GetRecurringPaymentsProfileDetails',
			'PROFILEID' => $subscription->profile_id,
			'VERSION'   => '124',
		);

		$request = wp_remote_post( $this->api_endpoint, array(
			'body'        => $args,
			'httpversion' => '1.1',
			'timeout'     => 30,
		) );

		wp_parse_str( wp_remote_retrieve_body( $request ), $response );

		// Check if there was an error.
		if ( isset( $response['L_ERRORCODE0'] ) ) {
			return false;
		}

		// if ( false !== $stripe_subscription ) {
		//
		// $subscription_details = array(
		// 'status'         => $stripe_subscription->status,
		// 'created'        => $stripe_subscription->created,
		// 'expiration'     => $stripe_subscription->current_period_end,
		// 'billing_period' => $stripe_subscription->plan->interval,
		// 'bill_times'     => $stripe_subscription->plan->interval_count,
		// );
		//
		// return $subscription_details;
		// }
		return false;
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

		$card_number    = isset( $_POST['card_number'] ) ? give_clean( str_replace( ' ', '', $_POST['card_number'] ) ) : '';
		$card_exp_month = isset( $_POST['card_exp_month'] ) ? give_clean( $_POST['card_exp_month'] ) : '';
		$card_exp_year  = isset( $_POST['card_exp_year'] ) ? give_clean( $_POST['card_exp_year'] ) : '';
		$card_cvc       = isset( $_POST['card_cvc'] ) ? give_clean( $_POST['card_cvc'] ) : '';

		$card_zip = isset( $_POST['card_zip'] ) ? give_clean( $_POST['card_zip'] ) : '';

		if ( empty( $card_number ) || empty( $card_exp_month ) || empty( $card_exp_year ) || empty( $card_cvc ) ) {
			give_set_error( 'give_recurring_paypalpro', __( 'Please enter all required fields.', 'give-recurring' ) );
		}

		$errors = give_get_errors();
		if ( empty( $errors ) ) {
			$args = array(
				'USER'         => $this->username,
				'PWD'          => $this->password,
				'SIGNATURE'    => $this->signature,
				'VERSION'      => '124',
				'METHOD'       => 'UpdateRecurringPaymentsProfile',
				'PROFILEID'    => $subscription->profile_id,
				'ACCT'         => $card_number,
				'EXPDATE'      => $card_exp_month . $card_exp_year,
				// needs to be in the format 062019
				'CVV2'         => $card_cvc,
				'ZIP'          => $card_zip,
				'BUTTONSOURCE' => 'givewp_SP',
			);

			$request = wp_remote_post( $this->api_endpoint, array(
				'timeout'     => 45,
				'sslverify'   => false,
				'body'        => $args,
				'httpversion' => '1.1',
			) );

			$body    = wp_remote_retrieve_body( $request );
			$code    = wp_remote_retrieve_response_code( $request );
			$message = wp_remote_retrieve_response_message( $request );

			if ( is_wp_error( $request ) ) {

				$error = sprintf(
					'<p>%1$s</p><p>%2$s</p>',
					__( 'An unidentified error occurred.', 'give-recurring' ),
					$request->get_error_message()
				);

				give_set_error( 'recurring_generic_paypalpro_error', $error );

			} elseif ( 200 === $code && 'OK' === $message ) {

				if ( is_string( $body ) ) {
					wp_parse_str( $body, $body );
				}

				if ( 'failure' === strtolower( $body['ACK'] ) ) {

					$error = sprintf(
						'<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
						__( 'PayPal subscription creation failed.', 'give-recurring' ),
						$body['L_LONGMESSAGE0'],
						$body['L_ERRORCODE0']
					);

					give_record_gateway_error( $error, __( 'Error', 'give-recurring' ), array( 'response' => '401' ) );

					give_set_error( $body['L_ERRORCODE0'], $body['L_LONGMESSAGE0'] );

				} else {

					// Request was successful, but verify the profile ID that came back matches
					if ( $subscription->profile_id !== $body['PROFILEID'] ) {
						give_set_error( 'give_recurring_profile_mismatch', __( 'Error updating subscription', 'give-recurring' ) );
					}

				}

			} else {

				give_set_error( 'give_recurring_paypal_pro_generic_error', __( 'Something has gone wrong, please try again', 'give-recurring' ) );

			}

		}

	}

	/**
	 * Get subscription CC details.
	 *
	 * @since 1.7
	 *
	 * @param object $subscription
	 *
	 * @return array
	 */
	public function get_subscription_cc_details( $subscription ) {
		$args = array(
			'USER'      => $this->username,
			'PWD'       => $this->password,
			'SIGNATURE' => $this->signature,
			'METHOD'    => 'GetRecurringPaymentsProfileDetails',
			'PROFILEID' => $subscription->profile_id,
			'VERSION'   => '124',
		);

		$request = wp_remote_post( $this->api_endpoint, array(
			'body'        => $args,
			'httpversion' => '1.1',
			'timeout'     => 30,
		) );

		wp_parse_str( wp_remote_retrieve_body( $request ), $response );

		return $response;
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

		// Is errors?
		$errors = give_get_errors();

		if ( empty( $errors ) ) {
			$args = array(
				'USER'         => $this->username,
				'PWD'          => $this->password,
				'SIGNATURE'    => $this->signature,
				'VERSION'      => '124',
				'METHOD'       => 'UpdateRecurringPaymentsProfile',
				'PROFILEID'    => $subscription->profile_id,
				'AMT'          => $renewal_amount,
				'BUTTONSOURCE' => 'givewp_SP',
			);

			$request = wp_remote_post( $this->api_endpoint, array(
				'timeout'     => 45,
				'sslverify'   => false,
				'body'        => $args,
				'httpversion' => '1.1',
			) );

			$body    = wp_remote_retrieve_body( $request );
			$code    = wp_remote_retrieve_response_code( $request );
			$message = wp_remote_retrieve_response_message( $request );

			if ( is_wp_error( $request ) ) {

				$error = sprintf(
					'<p>%1$s</p><p>%2$s</p>',
					__( 'An unidentified error occurred.', 'give-recurring' ),
					$request->get_error_message()
				);

				give_set_error( 'recurring_generic_paypalpro_error', $error );

			} elseif ( 200 === $code && 'OK' === $message ) {

				if ( is_string( $body ) ) {
					wp_parse_str( $body, $body );
				}

				if ( 'failure' === strtolower( $body['ACK'] ) ) {

					$error = sprintf(
						'<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
						__( 'PayPal subscription update failed.', 'give-recurring' ),
						$body['L_LONGMESSAGE0'],
						$body['L_ERRORCODE0']
					);

					give_record_gateway_error( $error, __( 'Error', 'give-recurring' ), array( 'response' => '401' ) );

					give_set_error( $body['L_ERRORCODE0'], $body['L_LONGMESSAGE0'] );

				} else {

					// Request was successful, but verify the profile ID that came back matches.
					if ( $subscription->profile_id !== $body['PROFILEID'] ) {
						give_set_error( 'give_recurring_profile_mismatch', __( 'Error updating subscription', 'give-recurring' ) );
					}

				}

			} else {

				give_set_error( 'give_recurring_paypal_pro_generic_error', __( 'Something has gone wrong, please try again', 'give-recurring' ) );
			}
		} // End if().
	}
}

new Give_Recurring_PayPal_Website_Payments_Pro();
