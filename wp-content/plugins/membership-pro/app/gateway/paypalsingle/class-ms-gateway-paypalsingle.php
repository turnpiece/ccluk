<?php
/**
 * Gateway: Paypal Single
 *
 * Officially: PayPal Payments Standard
 * https://developer.paypal.com/docs/classic/paypal-payments-standard/gs_PayPalPaymentsStandard/
 *
 * Process single paypal purchases/payments.
 *
 * Persisted by parent class MS_Model_Option. Singleton.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage Model
 */
class MS_Gateway_Paypalsingle extends MS_Gateway {

	const ID = 'paypalsingle';

	/**
	 * Gateway singleton instance.
	 *
	 * @since  1.0.0
	 * @var string $instance
	 */
	public static $instance;

	/**
	 * Paypal merchant/seller's email.
	 *
	 * @since  1.0.0
	 * @var bool $paypal_email
	 */
	protected $paypal_email;

	/**
	 * Paypal country site.
	 *
	 * @since  1.0.0
	 * @var bool $paypal_site
	 */
	protected $paypal_site;


	/**
	 * Hook to add custom transaction status.
	 * This is called by the MS_Factory
	 *
	 * @since  1.0.0
	 */
	public function after_load() {
		parent::after_load();

		$this->id 				= self::ID;
		$this->name 			= __( 'PayPal Single Gateway', 'membership2' );
		$this->group 			= 'PayPal';
		$this->manual_payment 	= true; // Recurring billed/paid manually
		$this->pro_rate 		= true;
	}

	/**
	 * Processes gateway IPN return.
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Transactionlog $log Optional. A transaction log item
	 *         that will be updated instead of creating a new log entry.
	 */
	public function handle_return( $log = false ) {
		$success 			= false;
		$exit 				= false;
		$redirect 			= false;
		$notes 				= '';
		$status 			= null;
		$invoice_id 		= 0;
		$subscription_id 	= 0;
		$amount 			= 0;

		do_action(
			'ms_gateway_paypalsingle_handle_return_before',
			$this
		);

		mslib3()->array->strip_slashes( $_POST, 'pending_reason' );

		if ( ( isset($_POST['payment_status'] ) || isset( $_POST['txn_type'] ) )
			&& ! empty( $_POST['invoice'] )
		) {
			if ( $this->is_live_mode() ) {
				$domain = 'https://ipnpb.paypal.com';
			} else {
				$domain = 'https://ipnpb.sandbox.paypal.com';
			}
			// Ask PayPal to validate our $_POST data.
			$ipn_data 			= (array) stripslashes_deep( $_POST );
			$ipn_data['cmd'] 	= '_notify-validate';
			$response 			= wp_remote_post(
				$domain . '/cgi-bin/webscr',
				array(
					'timeout' 		=> 60,
					'sslverify' 	=> false,
					'httpversion' 	=> '1.1',
					'body' 			=> $ipn_data,
				)
			);

			$invoice_id 	= intval( $_POST['invoice'] );
			$external_id 	= $_POST['txn_id'];
			$amount 		= (float) $_POST['mc_gross'];
			$currency 		= $_POST['mc_currency'];
			$invoice 		= MS_Factory::load( 'MS_Model_Invoice', $invoice_id );

			if ( ! is_wp_error( $response )
				&& ! MS_Model_Transactionlog::was_processed( self::ID, $external_id )
				&& 200 == $response['response']['code']
				&& ! empty( $response['body'] )
				&& 'VERIFIED' == $response['body']
				&& $invoice->id == $invoice_id
			) {
				$new_status 		= false;
				$subscription 		= $invoice->get_subscription();
				$membership 		= $subscription->get_membership();
				$member 			= $subscription->get_member();
				$subscription_id 	= $subscription->id;

				// Process PayPal response
				switch ( $_POST['payment_status'] ) {
					// Successful payment
					case 'Completed':
					case 'Processed':
						$success 	= true;
						if ( $amount == $invoice->total ) {
							$notes .= __( 'Payment successful', 'membership2' );
						} else {
							$notes .= __( 'Payment registered, though amount differs from invoice.', 'membership2' );
						}
						$status = MS_Model_Invoice::STATUS_PAID;
						break;

					case 'Reversed':
						$notes 	= __( 'Last transaction has been reversed. Reason: Payment has been reversed (charge back). ', 'membership2' );
						$status = MS_Model_Invoice::STATUS_DENIED;
						break;

					case 'Refunded':
						$notes 	= __( 'Last transaction has been reversed. Reason: Payment has been refunded', 'membership2' );
						$status = MS_Model_Invoice::STATUS_DENIED;
						break;

					case 'Denied':
						$notes 	= __( 'Last transaction has been reversed. Reason: Payment Denied', 'membership2' );
						$status = MS_Model_Invoice::STATUS_DENIED;
						break;

					case 'Pending':
						$pending_str = array(
							'address' 			=> __( 'Customer did not include a confirmed shipping address', 'membership2' ),
							'authorization' 	=> __( 'Funds not captured yet', 'membership2' ),
							'echeck' 			=> __( 'eCheck that has not cleared yet', 'membership2' ),
							'intl' 				=> __( 'Payment waiting for aproval by service provider', 'membership2' ),
							'multi-currency' 	=> __( 'Payment waiting for service provider to handle multi-currency process', 'membership2' ),
							'unilateral' 		=> __( 'Customer did not register or confirm his/her email yet', 'membership2' ),
							'upgrade' 			=> __( 'Waiting for service provider to upgrade the PayPal account', 'membership2' ),
							'verify' 			=> __( 'Waiting for service provider to verify his/her PayPal account', 'membership2' ),
							'*' 				=> '',
						);

						$reason = $_POST['pending_reason'];
						$notes 	= __( 'Last transaction is pending. Reason: ', 'membership2' ) .
									( isset($pending_str[$reason] ) ? $pending_str[$reason] : $pending_str['*'] );
						$status = MS_Model_Invoice::STATUS_PENDING;
						break;

					default:
					case 'Partially-Refunded':
					case 'In-Progress':
						$success = null;
						break;
				}

				if ( 'new_case' == $_POST['txn_type']
					&& 'dispute' == $_POST['case_type']
				) {
					// Status: Dispute
					$status = MS_Model_Invoice::STATUS_DENIED;
					$notes 	= __( 'Dispute about this payment', 'membership2' );
				}

				if ( ! empty( $notes ) ) { $invoice->add_notes( $notes ); }

				if ( $success ) {
					$invoice->pay_it( self::ID, $external_id );
				} elseif ( ! empty( $status ) ) {
					$invoice->status = $status;
					$invoice->save();
					$invoice->changed();
				}

				do_action(
					'ms_gateway_paypalsingle_payment_processed_' . $status,
					$invoice,
					$subscription
				);
			} else {
				$reason = 'Unexpected transaction response';
				switch ( true ) {
					case is_wp_error( $response ):
						$reason = 'Response is error';
						break;

					case 200 != $response['response']['code']:
						$reason = 'Response code is ' . $response['response']['code'];
						break;

					case empty( $response['body'] ):
						$reason = 'Response is empty';
						break;

					case 'VERIFIED' != $response['body']:
						$reason = sprintf(
							'Expected response "%s" but got "%s"',
							'VERIFIED',
							(string) $response['body']
						);
						break;

					case $invoice->id != $invoice_id:
						$reason = sprintf(
							'Expected invoice_id "%s" but got "%s"',
							$invoice->id,
							$invoice_id
						);
						break;

					case MS_Model_Transactionlog::was_processed( self::ID, $external_id ):
						$reason = 'Duplicate: Already processed that transaction.';
						break;
				}

				$notes = 'Response Error: ' . $reason;
				$exit = true;
			}
			$invoice->gateway_id = self::ID;
			$invoice->save();
		} else {
			// Did not find expected POST variables. Possible access attempt from a non PayPal site.

			$u_agent = $_SERVER['HTTP_USER_AGENT'];
			if ( false === strpos( $u_agent, 'PayPal' ) ) {
				// Very likely someone tried to open the URL manually. Redirect to home page
				$notes 		= 'Error: Missing POST variables. Redirect user to Home-URL.';
				$redirect 	= MS_Helper_Utility::home_url( '/' );
			} else {
				$notes = 'Error: Missing POST variables. Identification is not possible.';
			}
			$exit = true;
		}

		if ( ! $log ) {
			do_action(
				'ms_gateway_transaction_log',
				self::ID, // gateway ID
				'handle', // request|process|handle
				$success, // success flag
				$subscription_id, // subscription ID
				$invoice_id, // invoice ID
				$amount, // charged amount
				$notes, // Descriptive text
				$external_id // External ID
			);

			if ( $redirect ) {
				wp_safe_redirect( $redirect );
				exit;
			}
			if ( $exit ) {
				exit;
			}
		} else {
			$log->invoice_id 		= $invoice_id;
			$log->subscription_id 	= $subscription_id;
			$log->amount 			= $amount;
			$log->description 		= $notes;
			$log->external_id 		= $external_id;
			if ( $success ) {
				$log->manual_state( 'ok' );
			}
			$log->save();
		}

		do_action(
			'ms_gateway_paypalsingle_handle_return_after',
			$this,
			$log
		);

		if ( $log ) {
			return $log;
		}
	}

	/**
	 * Get paypal country sites list.
	 *
	 * @see MS_Gateway::get_country_codes()
	 * @since  1.0.0
	 * @return array
	 */
	public function get_paypal_sites() {
		return apply_filters(
			'ms_gateway_paylpaysingle_get_paypal_sites',
			self::get_country_codes()
		);
	}

	/**
	 * Verify required fields.
	 *
	 * @since  1.0.0
	 *
	 * @return boolean
	 */
	public function is_configured() {
		$is_configured 	= true;
		$required 		= array( 'paypal_email', 'paypal_site' );

		foreach ( $required as $field ) {
			$value = $this->$field;
			if ( empty( $value ) ) {
				$is_configured = false;
				break;
			}
		}

		return apply_filters(
			'ms_gateway_paypalsingle_is_configured',
			$is_configured
		);
	}

	/**
	 * Validate specific property before set.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @param string $name The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'paypal_site':
					if ( array_key_exists( $value, self::get_paypal_sites() ) ) {
						$this->$property = $value;
					}
					break;

				default:
					parent::__set( $property, $value );
					break;
			}
		}

		do_action(
			'ms_gateway_paypalsingle__set_after',
			$property,
			$value,
			$this
		);
	}

}