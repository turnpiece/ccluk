<?php
/**
 * PayPal Payments Pro (Payflow) Recurring Gateway.
 *
 * @see:
 * https://github.com/ebtc/civicrm-payflowpro-final/blob/master/wp-content/plugins/civicrm/civicrm/CRM/Core/Payment/PayflowPro.php
 * https://codeseekah.com/2012/02/11/how-to-setup-multiple-ipn-receivers-in-paypal/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Give_Recurring_PayPal_Pro_Payflow
 */
class Give_Recurring_PayPal_Pro_Payflow extends Give_Recurring_Gateway {

	/**
	 * Main gateway class object.
	 *
	 * @var $give_payflow Give_PayPal_Pro_Payflow
	 */
	protected $give_payflow;

	/**
	 * The gateway ID.
	 *
	 * @var $id
	 */
	public $id;

	/**
	 * Get things rollin'.
	 *
	 * @since 1.2
	 */
	public function init() {

		$this->id = 'paypalpro_payflow';

		if ( ! class_exists( 'Give_PayPal_Pro_Payflow' ) ) {
			return false;
		}

		$this->give_payflow = new Give_PayPal_Pro_Payflow();

		// Cancellation action.
		add_action( 'give_recurring_cancel_' . $this->id . '_subscription', array( $this, 'cancel' ), 10, 2 );

		// IPN should point to https://mywebsite.com/?give-listener=IPN
		add_action( 'give_paypal_web_accept', array( $this, 'process_web_accept_ipn' ), 10, 2 );

		add_filter( 'give_recurring_gateway_factory_get_gateway', array( $this, 'sync_get_gateway' ), 10, 3 );

	}

	/**
	 * Process Payflow renewals.
	 *
	 * PayPal + Payflow sends renewals in via normal IPN "web_accept" transactions.
	 *
	 * @param array $ipn_data   Encoded data.
	 * @param int   $payment_id Payment id. Note: This is not brought over for Payflow transactions.
	 *
	 * @return array|bool
	 */
	public function process_web_accept_ipn( $ipn_data, $payment_id ) {

		// Only process renewals for completed payments.
		if ( 'completed' !== strtolower( $ipn_data['payment_status'] ) ) {
			return false;
		}

		// Get this donor via email.
		$subscriber    = new Give_Recurring_Subscriber( $ipn_data['payer_email'] );
		$subscriptions = $subscriber->get_subscriptions();
		$ipn_time      = strtotime( $ipn_data['payment_date'] ); // Always PDT.
		$ipn_amount    = isset( $ipn_data['mc_gross'] ) ? $ipn_data['mc_gross'] : '';

		// Need subscriptions to continue.
		if ( empty( $subscriptions ) ) {
			return false;
		}

		// Loop through the donor's Give subscriptions.
		foreach ( $subscriptions as $subscription ) {

			// We only want payflow gateway subscriptions.
			if ( $subscription->gateway !== $this->id ) {
				continue;
			}

			// We need a profile ID. If none, skip this iteration.
			if ( empty( $subscription->profile_id ) ) {
				continue;
			}

			// Lookup this subscription in Payflow via API request.
			$response = $this->get_payflow_transactions( $subscription );

			// Check the Payflow subscriptions' payment amount and date match for a renewal payment match.
			$counter           = 1;
			$pp_ref            = '';
			$payment_timestamp = '';
			$payment_amount    = '';

			foreach ( $response as $renewal ) {

				// Ensure PDT like IPN.
				$renewal_timestamp = strtotime( $renewal[ "P_TRANSTIME{$counter}" ] . ' PDT' );
				$payment_amount    = $renewal[ "P_AMT{$counter}" ];

				// Match timestamp within 8 hours and amount equals.
				// Payment amounts must match as well.
				if (
					abs( $renewal_timestamp - $ipn_time ) <= apply_filters( 'give_recurring_payflow_ipn_window_timeframe', 28800 )
					&& $payment_amount === $ipn_amount
				) {
					$payment_timestamp = $renewal_timestamp;
					$pp_ref            = $renewal[ "P_PNREF{$counter}" ];
				}

				$counter ++;

			}

			// Add new renewal subscription payment if match made.
			if (
				! empty( $pp_ref )
				&& ! empty( $payment_timestamp )
			) {

				// Ensure we set the proper `post_date` format.
				// To do this we convert the payment timestamp back to PDT.
				// Then format it accordingly in the `post_date` arg when adding payment.
				$post_date = new DateTime( '@' . $payment_timestamp );  // will be UTC because of the "@timezone" syntax
				$post_date->setTimezone( new DateTimeZone( 'America/Los_Angeles' ) );

				$sub_added = $subscription->add_payment(
					array(
						'amount'         => $payment_amount,
						'transaction_id' => $pp_ref,
						'post_date'      => $post_date->format( 'Y-m-d H:i:s' ),
					)
				);

				if ( $sub_added ) {
					$subscription->renew();
				}
			}
		} // End foreach().

		return false;

	}

	/**
	 * Create payment profiles.
	 *
	 * @since 1.2
	 */
	public function create_payment_profiles() {

		if ( ! class_exists( 'Give_PayPal_Pro_Payflow' ) ) {
			return false;
		}

		$this->give_payflow = new Give_PayPal_Pro_Payflow();

		$payment_data = $this->give_payflow->format_payment_data( $this->purchase_data );

		if ( ! $this->confirm_recurring_enabled() ) {
			return false;
		}

		// First we need a successful initial charge.
		$payflow_transaction_id = $this->initial_charge( $payment_data );

		$frequency = ! empty( $this->subscriptions['frequency'] ) ? intval( $this->subscriptions['frequency'] ) : 1;

		// Must have a transaction ID to continue.
		if ( ! empty( $payflow_transaction_id ) ) {

			$payflow_query_array = array(
				'USER'         => $this->give_payflow->paypal_user,
				'VENDOR'       => $this->give_payflow->paypal_vendor,
				'PARTNER'      => $this->give_payflow->paypal_partner,
				'PWD'          => $this->give_payflow->paypal_password,
				'ORIGID'       => $payflow_transaction_id,
				// C - Direct Payment using credit card.
				'TENDER'       => 'C',
				'ACCT'         => rawurlencode( $payment_data['card_number'] ),
				'CVV2'         => $payment_data['card_cvc'],
				'EXPDATE'      => rawurlencode( $payment_data['card_exp'] ),
				'AMT'          => rawurlencode( give_maybe_sanitize_amount( $this->subscriptions['recurring_amount'] ) ),
				'CURRENCY'     => rawurlencode( give_get_currency() ),
				// credit card name
				'FIRSTNAME'    => $this->strip_special_characters( sanitize_text_field( $this->purchase_data['user_info']['first_name'] ) ),
				'LASTNAME'     => $this->strip_special_characters( sanitize_text_field( $this->purchase_data['user_info']['last_name'] ) ),
				'EMAIL'        => $this->purchase_data['post_data']['give_email'],
				'CUSTIP'       => rawurlencode( $this->give_payflow->get_user_ip() ),
				// START.tho.270313
				'COMMENT1'     => sprintf( __( 'Initial donation ID: %1$s / Donation made from: %2$s', 'give-recurring' ), $payflow_transaction_id, get_bloginfo( 'url' ) ),
				'BUTTONSOURCE' => 'givewp_SP',
			);

			// Send billing fields if enabled.
			if ( $this->give_payflow->billing_fields ) {
				$payflow_query_array['STREET']  = $this->purchase_data['card_info']['card_address'] . ' ' . $this->purchase_data['card_info']['card_address_2'];
				$payflow_query_array['CITY']    = rawurlencode( $this->purchase_data['card_info']['card_city'] );
				$payflow_query_array['STATE']   = rawurlencode( $this->purchase_data['card_info']['card_state'] );
				$payflow_query_array['ZIP']     = rawurlencode( $this->purchase_data['card_info']['card_zip'] );
				$payflow_query_array['COUNTRY'] = rawurlencode( $this->purchase_data['card_info']['card_country'] );
			}

			// Subscription
			$payflow_query_array['TRXTYPE'] = 'R'; // Recurring transaction type.
			$payflow_query_array['ACTION']  = 'A'; // Add action.

			$profile_name                       = substr( ( give_recurring_generate_subscription_name( $this->subscriptions['id'], $this->subscriptions['price_id'] ) ), 0, 127 );
			$payflow_query_array['PROFILENAME'] = preg_replace( '/[^ \w]+/', '', $profile_name );

			$payflow_query_array['TERM']         = $this->subscriptions['bill_times'] > 1 ? $this->subscriptions['bill_times'] - 1 : 0; // Subtract 1 from TOTALBILLINGCYCLES because donors are charged an initial payment by PayPal to begin the subscription
			$payflow_query_array['START']        = $this->format_start( $frequency );
			$payflow_query_array['PAYPERIOD']    = $this->format_period();
			$payflow_query_array['CREATIONDATE'] = date( 'mdY', strtotime( date( 'Y-m-d', strtotime( date( 'Y-m-d' ) ) ) ) );
			$payflow_query_array['FREQUENCY']    = $frequency;

			/**
			 * Allows other plugins to modify data before sending to PayPal.
			 *
			 * @param array $payflow_query_array                 PayPal query data.
			 * @param Give_Recurring_PayPal_Pro_Payflow $gateway Gateway object.
			 *
			 * @return array                                     PayPal query data.
			 */
			$payflow_query_array = apply_filters( 'give_recurring_payflow_query_array', $payflow_query_array, $this );

			// Hit PP API with Query.
			$response = $this->api_request( $payflow_query_array );

			// Parse response code.
			$response_code = isset( $response['RESULT'] ) ? $response['RESULT'] : '';

			// Check if subscription was successfully created in Payflow.
			switch ( $response_code ) {

				// Successful or 127- Under Review by Fraud Service.
				case '0':
				case '126':
				case '127':
					$this->subscriptions['profile_id']        = isset( $response['PROFILEID'] ) ? $response['PROFILEID'] : '';
					$this->subscriptions['parent_payment_id'] = $this->payment_id;
					$this->subscriptions['status']            = 'active';

					break;

				default:
					// There was an error
					give_set_error( 'payflow_error', __( 'There was a problem creating the recurring subscription.', 'give-recurring' ) );
					give_record_gateway_error( 'Payflow Error', 'Code:' . $response_code . '. Error: ' . $response['RESPMSG'] );

			}
		}

	}

	/**
	 * Initial Charge.
	 *
	 * When donating via credit card, we need to run a transaction first, grab the PNREF of the transaction,
	 * then use that to create the recurring billing profile.
	 *
	 * @param array $payment_data
	 *
	 * @return bool|string
	 */
	public function initial_charge( $payment_data ) {

		// Send request to paypal.
		try {

			$url = give_is_test_mode() ? $this->give_payflow->testurl : $this->give_payflow->liveurl;

			$post_data            = $this->give_payflow->get_post_data( $this->purchase_data );
			$post_data['ACCT']    = $payment_data['card_number']; // Credit Card
			$post_data['EXPDATE'] = $payment_data['card_exp']; // MMYY
			$post_data['CVV2']    = $payment_data['card_cvc']; // CVV code

			$response = wp_remote_post(
				$url, array(
					'method'      => 'POST',
					'body'        => urldecode( http_build_query( apply_filters( 'give_recurring_payflow_initial_request', $post_data, $this->purchase_data ), null, '&' ) ),
					'timeout'     => 70,
					'user-agent'  => 'GiveWP',
					'httpversion' => '1.1',
				)
			);

			// Get response body.
			$response_message = wp_remote_retrieve_body( $response );

			if ( empty( $response_message ) ) {
				give_set_error( 'payflow_error', __( 'There was a problem connecting to the payment gateway.', 'give-recurring' ) );
				give_record_gateway_error( __( 'Payflow Error', 'give-recurring' ), sprintf( __( 'Error %s', 'give-recurring' ), print_r( $response->get_error_message(), true ) ) );

				return false;
			}

			parse_str( $response_message, $parsed_response );

			if (
				isset( $parsed_response['RESULT'] )
				&& in_array( $parsed_response['RESULT'], array( 0, 126, 127 ) )
			) {
				$txn_id = ! empty( $parsed_response['PNREF'] ) ? $parsed_response['PNREF'] : '';
				give_set_payment_transaction_id( $this->payment_id, $txn_id );

				switch ( $parsed_response['RESULT'] ) {

					// Approved or screening service was down.
					case 0:
					case 127:
						// Add note & update status.
						give_insert_payment_note( $this->payment_id, sprintf( __( 'PayPal Pro (Payflow) initial payment completed (PNREF: %s)', 'give-recurring' ), $txn_id ) );

						// Set subscription_payment.
						give_update_payment_meta( $this->payment_id, '_give_subscription_payment', true );

						return $txn_id;

					// Under Review by Fraud Service. Payment remains pending.
					case 126:
						give_insert_payment_note( $this->payment_id, sprintf( __( 'The payment was flagged by a fraud filter. Please check your PayPal Manager account to review and accept or deny the payment and then mark this donation complete or cancelled. Message from PayPal: %s', 'give-recurring' ), $parsed_response['PREFPSMSG'] ) );

						return $txn_id;

				}
			} else {

				// Payment failed :(
				give_record_gateway_error( 'Payflow Error', __( 'PayPal Pro (Payflow) payment failed. Payment was rejected due to an error: ', 'give-recurring' ) . '(' . $parsed_response['RESULT'] . ') ' . '"' . $parsed_response['RESPMSG'] . '"' );
				give_set_error( 'give_recurring_payflow_failed', __( 'Payment error:', 'give-recurring' ) . ' ' . $parsed_response['RESPMSG'] );

				return false;

			}// End if().
		} catch ( Exception $e ) {

			give_set_error( __( 'Connection error:', 'give-recurring' ) . ': "' . $e->getMessage() . '"', 'error' );

			return false;
		}// End try().

		return false;

	}

	/**
	 * Confirm that recurring is enabled in Payflow prior to initial charge.
	 *
	 * With Payflow we charge an initial one-time donation to begin the subscription since the API does not support
	 * charging an initial subscription charge. This function prevents the initial one-time charge from going through
	 * if the specific API key being used does not have recurring enabled.
	 *
	 * @see   : https://github.com/impress-org/give-recurring-donations/issues/288
	 * @since 1.2.2
	 *
	 * @return bool
	 */
	public function confirm_recurring_enabled() {

		try {
			// Inquire about a false record to see if we get a response back.
			$payflow_query_array = array(
				'TRXTYPE'       => 'R', // Specifies a recurring profile request.
				'USER'          => $this->give_payflow->paypal_user,
				'VENDOR'        => $this->give_payflow->paypal_vendor,
				'PARTNER'       => $this->give_payflow->paypal_partner,
				'PWD'           => $this->give_payflow->paypal_password,
				'ACTION'        => 'I',
				'ORIGPROFILEID' => 'RP123412341234', // Some made up profile ID.
			);

			// Hit PP API with Query.
			$response = $this->api_request( $payflow_query_array );

			if ( is_wp_error( $response ) ) {

				$error = sprintf(
					'<p>%1$s</p><p>%2$s</p>',
					__( 'An unidentified error occurred.', 'give-recurring' ),
					print_r( $response, true )
				);

				give_set_error( 'recurring_generic_paypalpro_error', $error );

				return false;
			}

			// Parse response code.
			$response_code = isset( $response['RESULT'] ) ? $response['RESULT'] : '';
			$response_msg  = isset( $response['RESPMSG'] ) ? $response['RESPMSG'] : '';

			// Check API response for an invalid profile response, if not... error:
			if ( '1' === $response_code && 'User authentication failed: Recurring Billing' === $response_msg ) {

				give_set_error( 'payflow_error', __( 'There was a problem creating the recurring subscription.', 'give-recurring' ) . ' ' . __( 'It does not appear that this Payflow account has recurring enabled.', 'give-recurring' ) );
				give_record_gateway_error( 'Payflow Error', 'Code:' . $response_code . '. Error: It does not appear that this Payflow account has recurring enabled. Here is the response from gateway during confirming recurring is enabled check: ' . $response['RESPMSG'] );

				return false;

			} else {
				// Set a transient for 1 month between checks after it passes.
				set_transient( 'give_payflow_recurring_check', true, 4 * WEEK_IN_SECONDS );

				return true;
			}
		} catch ( Exception $e ) {

			give_set_error( __( 'Connection error:', 'give-recurring' ) . ': "' . $e->getMessage() . '"', 'error' );

			return false;
		}

	}

	/**
	 * Make PayPal API Request.
	 *
	 * @param $args
	 *
	 * @return bool|array
	 */
	public function api_request( $args ) {

		$url = give_is_test_mode() ? $this->give_payflow->testurl : $this->give_payflow->liveurl;

		$response = wp_remote_post(
			$url, array(
				'timeout'     => 500,
				'sslverify'   => false,
				'body'        => urldecode( http_build_query( apply_filters( 'give_recurring_payflow_api_request', $args ), null, '&' ) ),
				'httpversion' => '1.1',
			)
		);

		if ( is_wp_error( $response ) ) {

			// Its a WP_Error
			give_set_error( 'give_recurring_payflow_generic_error', __( 'An error occurred, please try again. Error:' . $response->get_error_message(), 'give-recurring' ) );
			give_record_gateway_error( 'Payflow Error', 'Error ' . print_r( $response->get_error_message(), true ) );

			return false;

		} elseif ( 200 == $response['response']['code'] && 'OK' == $response['response']['message'] ) {

			// Ok, we have a paypal OK
			parse_str( $response['body'], $data );

			return $data;

		} else {

			// We don't know what the error is.
			give_set_error( 'give_recurring_payflow_generic_error', __( 'Something has gone wrong, please try again', 'give-recurring' ) );
			give_record_gateway_error( 'Payflow Error', 'An error occurred when connecting to PayPal.' );

			return false;

		}

	}

	/**
	 * Overriding recurring gateway's record_signup
	 *
	 * We handle subscription sign up in initial_charge()
	 */
	function record_signup() {

		// Now create the subscription record.
		$subscriber = new Give_Recurring_Subscriber( $this->customer_id );

		$frequency = ! empty( $this->subscriptions['frequency'] ) ? intval( $this->subscriptions['frequency'] ) : 1;

		$args = array(
			'form_id'           => $this->subscriptions['id'],
			'parent_payment_id' => $this->payment_id,
			'status'            => 'active',
			'period'            => $this->subscriptions['period'],
			'frequency'         => $frequency,
			'initial_amount'    => $this->subscriptions['initial_amount'],
			'recurring_amount'  => $this->subscriptions['recurring_amount'],
			'bill_times'        => $this->subscriptions['bill_times'],
			'expiration'        => $subscriber->get_new_expiration( $this->subscriptions['id'], $this->subscriptions['price_id'], $frequency, $this->subscriptions['period'] ),
			'profile_id'        => $this->subscriptions['profile_id'],
		);

		// Support user_id if it is present in purchase_data.
		if ( isset( $this->purchase_data['user_info']['id'] ) ) {
			$args['user_id'] = $this->purchase_data['user_info']['id'];
		}

		$subscription = $subscriber->add_subscription( $args );

		// Update Donation Status, if recurring subscription created.
		if ( $subscription->id ) {
			give_update_payment_status( $this->payment_id, 'publish' );
		}

	}

	/**
	 * Can cancel.
	 *
	 * Determines if the subscription can be cancelled.
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if (
			$subscription->gateway === $this->id
			&& ! empty( $subscription->profile_id )
			&& 'active' === $subscription->status
		) {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Cancels a subscription.
	 *
	 * @param $subscription Give_Subscription
	 * @param $valid
	 *
	 * @return bool
	 */
	public function cancel( $subscription, $valid ) {

		if ( empty( $valid ) || false === $this->can_cancel( false, $subscription ) ) {
			return false;
		}

		$post_data                  = array();
		$post_data['USER']          = $this->give_payflow->paypal_user;
		$post_data['VENDOR']        = $this->give_payflow->paypal_vendor;
		$post_data['PARTNER']       = $this->give_payflow->paypal_partner;
		$post_data['PWD']           = $this->give_payflow->paypal_password;
		$post_data['TRXTYPE']       = 'R'; // R for recurring.
		$post_data['ACTION']        = 'C'; // C for cancel.
		$post_data['ORIGPROFILEID'] = $subscription->profile_id; // C for cancel.

		$response = $this->api_request( $post_data );

		// Parse response code.
		$response_code = isset( $response['RESULT'] ) ? $response['RESULT'] : '';

		// Check if subscription was successfully created in Payflow.
		if ( $response_code !== '0' ) {

			// @TODO: Provide better cancellation error handling.
			$response_msg = isset( $response['RESPMSG'] ) ? $response['RESPMSG'] : __( 'No response message from PayPal provided', 'give-recurring' );

			// Something went wrong outside of Stripe.
			give_record_gateway_error( __( 'Stripe Error', 'give-recurring' ), sprintf( __( 'The Stripe Gateway returned an error while cancelling a subscription. Details: %s', 'give-recurring' ), $response_msg ) );
			give_set_error( 'Stripe Error', __( 'An error occurred while cancelling the donation. Please try again.', 'give-recurring' ) );

		}

	}


	/**
	 * Gets transactions from Payflow using API.
	 *
	 *  Example Payflow Recurring transaction inquiry response:
	 *    array(
	 *          'RESULT'       => '0',
	 *          'RPREF'        => 'RUX5EB55650F',
	 *          'PROFILEID'    => 'RP0000000002',
	 *          'P_PNREF1'     => 'BS0PE9D5BD08',
	 *          'P_TRANSTIME1' => '01-Sep-16  04:39 AM',
	 *          'P_RESULT1'    => '0',
	 *          'P_TENDER1'    => 'C',
	 *          'P_AMT1'       => '1.00',
	 *          'P_TRANSTATE1' => '8',
	 *          'P_PNREF2'     => 'BS0PZ9D5BD03',
	 *          'P_TRANSTIME2' => '01-Sep-17  05:39 AM',
	 *          'P_RESULT2'    => '0',
	 *          'P_TENDER2'    => 'C',
	 *          'P_AMT2'       => '1.00',
	 *          'P_TRANSTATE2' => '8',
	 *          'P_PNREF3'     => 'BS0PZ9D5BD03',
	 *          'P_TRANSTIME3' => '01-Sep-18  02:39 AM',
	 *          'P_RESULT3'    => '0',
	 *          'P_TENDER3'    => 'C',
	 *          'P_AMT3'       => '1.00',
	 *          'P_TRANSTATE3' => '8'
	 *      );
	 *
	 * @see   : https://developer.paypal.com/docs/classic/payflow/recurring-billing/#using-the-inquiry-action-to-view-information-for-a-profile
	 *
	 * @since 1.3
	 *
	 * @param $subscription
	 *
	 * @return array
	 */
	private function get_payflow_transactions( $subscription ) {

		$payflow_query_array = array(
			'USER'           => $this->give_payflow->paypal_user,
			'VENDOR'         => $this->give_payflow->paypal_vendor,
			'PARTNER'        => $this->give_payflow->paypal_partner,
			'PWD'            => $this->give_payflow->paypal_password,
			'ORIGPROFILEID'  => $subscription->profile_id,
			'TRXTYPE'        => 'R',
			'ACTION'         => 'I',
			'PAYMENTHISTORY' => 'Y',
		);
		$response            = $this->api_request( $payflow_query_array );

		// Chunk flat array into workable data.
		unset( $response['RESULT'] );
		unset( $response['RPREF'] );
		unset( $response['PROFILEID'] );

		return array_chunk( $response, 6, true );

	}

	/**
	 * Determines if the subscription can be cancelled.
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
	 * Check that the necessary credentials are set.
	 *
	 * @since 1.3
	 * @return bool
	 */
	private function check_credentials() {
		// Check credentials.
		if ( empty( $this->give_payflow->paypal_password ) ) {
			return false;
		} else {
			return true;
		}
	}


	/**
	 * Get subscription details.
	 *
	 * @param Give_Subscription $subscription
	 *
	 * @return array|bool
	 */
	public function get_subscription_details( $subscription ) {

		// Lookup this subscription in Payflow via API request.
		$payflow_query_array = array(
			'USER'          => $this->give_payflow->paypal_user,
			'VENDOR'        => $this->give_payflow->paypal_vendor,
			'PARTNER'       => $this->give_payflow->paypal_partner,
			'PWD'           => $this->give_payflow->paypal_password,
			'ORIGPROFILEID' => $subscription->profile_id,
			'TRXTYPE'       => 'R',
			'ACTION'        => 'I',
		);
		$response            = $this->api_request( $payflow_query_array );

		// Form created timestamp from PP's format of MMDDYYY
		$month          = substr( $response['CREATIONDATE'], 0, 2 );
		$day            = substr( $response['CREATIONDATE'], 2, 2 );
		$year           = substr( $response['CREATIONDATE'], 4, 4 );
		$created_time   = mktime( 0, 0, 0, $month, $day, $year );
		$status         = strtolower( $response['STATUS'] );
		$billing_period = strtolower( $response['PAYPERIOD'] );
		$frequency      = ! empty( $response['FREQUENCY'] ) ? $response['FREQUENCY'] : 1;

		// Payflow doesn't return billing_period if the subscription status is not active.
		// Therefore we default to what the subscription is to prevent errors + support.
		if ( 'active' !== $status ) {
			$billing_period = $subscription->period;
		}

		// Ensure that completed subscriptions are not expired unnecessarily due to Payflow weird way of handing statuses.
		if ( intval( $subscription->get_total_payments() ) === intval( $subscription->bill_times ) ) {
			$status = 'completed';
		}

		$subscription_details = array(
			'status'         => $status,
			'billing_period' => $billing_period,
			'frequency'      => $frequency,
			'created'        => $created_time,
		);

		return $subscription_details;

	}


	/**
	 * Get transactions for synchronizer.
	 *
	 * @param        $subscription
	 * @param string       $date
	 *
	 * @return array
	 */
	public function get_gateway_transactions( $subscription, $date = '' ) {

		$subscription_invoices = $this->get_payflow_transactions( $subscription );
		$counter               = 1;
		$transactions          = array();

		foreach ( $subscription_invoices as $renewal ) {

			// Only sync completed payments
			// 8 = settlement completed  successfully.
			if ( 8 !== intval( $renewal[ "P_TRANSTATE{$counter}" ] ) ) {
				$counter ++; // Still increment even if skipped.
				continue; // skip.
			}

			$transactions[ $renewal[ "P_PNREF{$counter}" ] ] = array(
				'amount'         => $renewal[ "P_AMT{$counter}" ],
				'date'           => strtotime( $renewal[ "P_TRANSTIME{$counter}" ] ),
				'transaction_id' => $renewal[ "P_PNREF{$counter}" ],
			);

			$counter ++;

		}

		return $transactions;

	}

	/**
	 * Get the Give_Recurring_PayPal_Pro_Payflow for Synchronizer.
	 *
	 * For synchronizer to properly initialize the class b/c the ID and class name differ.
	 *
	 * @since 1.3
	 *
	 * @param                   $ret
	 * @param                   $gateway
	 * @param Give_Subscription $subscription
	 *
	 * @return Give_Recurring_PayPal_Pro_Payflow $this
	 */
	function sync_get_gateway( $ret, $gateway, $subscription ) {

		// Return this class if gateway matches.
		if ( $subscription->gateway === $this->id ) {
			return $this;
		}

		// Always return original filter value.
		return $ret;

	}

	/**
	 * Format Subscription Start Date for Payflow.
	 *
	 * Beginning date for the recurring billing cycle used to calculate when payments should be made.
	 * Use tomorrow’s date or a date in the future.
	 *
	 * Format: MMDDYYYY. Numeric (eight characters)
	 *
	 * @param int $frequency Recurring frequency.
	 *
	 * @see https://developer.paypal.com/docs/classic/payflow/recurring-billing/#required-parameters-for-the-add-action
	 *
	 * @return false|string
	 */
	private function format_start( $frequency ) {

		switch ( $this->subscriptions['period'] ) {
			case 'day':
				return date( 'mdY', strtotime( date( 'Y-m-d', strtotime( date( 'Y-m-d' ) ) ) . '+' . $frequency . ' day' ) );
			case 'week':
				return date( 'mdY', strtotime( date( 'Y-m-d', strtotime( date( 'Y-m-d' ) ) ) . '+' . $frequency . ' week' ) );
			case 'month':
				return date( 'mdY', strtotime( date( 'Y-m-d', strtotime( date( 'Y-m-d' ) ) ) . '+' . $frequency . ' month' ) );
			case 'year':
				return date( 'mdY', strtotime( date( 'Y-m-d', strtotime( date( 'Y-m-d' ) ) ) . '+' . $frequency . ' year' ) );
		}

		return false;

	}

	/**
	 * Format Period.
	 *
	 * @see https://developer.paypal.com/docs/classic/payflow/recurring-billing/#required-parameters-for-the-add-action
	 */
	private function format_period() {

		switch ( $this->subscriptions['period'] ) {
			case 'day':
				return 'DAYS';
			case 'week':
				return 'WEEK';
			case 'month':
				return 'MONT';
			case 'year':
				return 'YEAR';
			default:
				return ucwords( $this->subscriptions['period'] );
		}

	}

	/**
	 * Link the recurring profile in PayPal Payflow.
	 *
	 * @since  1.4
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
			$base_url   = 'live' === $payment->mode ? 'https://manager.paypal.com/viewProfile.do?subaction=viewRbProfile' : 'https://manager.paypal.com/viewProfile.do?subaction=viewRbProfile&transReportMode=Test';
			$link       = esc_url( $base_url . '&id=' . $profile_id );
			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

	/**
	 * Replaces '&' with the word 'and' and
	 * replaces all other special characters with
	 * empty.
	 *
	 * @param string $text String on which the function to run.
	 *
	 * @return string
	 */
	public function strip_special_characters( $text ) {
		$text = str_replace( '&', 'and', $text );

		return preg_replace( '/[^\p{L}\p{Zs}.]/', '', $text );
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
			'paypalpro_payflow' === $subscription->gateway
			&& ! empty( $subscription->profile_id )
			&& in_array(
				$subscription->status, array(
					'active',
				), true
			)
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

		// Is errors?
		$errors = give_get_errors();

		if ( empty( $errors ) ) {
			// Lookup this subscription in Payflow via API request.
			$payflow_query_array = array(
				'USER'          => $this->give_payflow->paypal_user,
				'VENDOR'        => $this->give_payflow->paypal_vendor,
				'PARTNER'       => $this->give_payflow->paypal_partner,
				'PWD'           => $this->give_payflow->paypal_password,
				'ORIGPROFILEID' => $subscription->profile_id,
				'TRXTYPE'       => 'R',
				'ACTION'        => 'M',
				'TENDER'        => 'C',
				'AMT'           => $renewal_amount,
			);

			// Hit PP API with Query.
			$response = $this->api_request( $payflow_query_array );

			// Parse response code.
			$response_code = isset( $response['RESULT'] ) ? absint( $response['RESULT'] ) : '';

			// Set error if response is not okay.
			if ( 0 !== $response_code ) {
				give_set_error( $response_code, $response['RESPMSG'] );
			}
		}// End if().
	}

}

new Give_Recurring_PayPal_Pro_Payflow();
