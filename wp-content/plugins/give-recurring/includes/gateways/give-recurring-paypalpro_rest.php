<?php
/**
 * PayPal Pro REST Recurring Gateway.
 *
 * @link https://developer.paypal.com/docs/api/payments.billing-plans/
 * @link https://www.sandbox.paypal.com/us/cgi-bin/webscr?cmd=_display-ipns-history  //Sandbox IPN history page
 * @link https://devblog.paypal.com/tutorial-subscriptions-plan/
 * @link https://github.com/paypal/PayPal-PHP-SDK/issues/504
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// check for prerequisite classes.
if ( ! class_exists( 'Give_PayPal_Pro_Rest' ) ) {
	require_once GIVEPP_PLUGIN_DIR . '/includes/Give_PayPal_Pro_Rest.php';
}

//Be sure we have our autoloader.
if ( defined( 'GIVEPP_PLUGIN_DIR' ) && file_exists( GIVEPP_PLUGIN_DIR . '/lib/paypal/autoload.php' ) ) {
	require_once GIVEPP_PLUGIN_DIR . '/lib/paypal/autoload.php';
} else {
	exit;
}

use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Currency;
use PayPal\Api\FundingInstrument;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Plan;
use PayPal\Api\Webhook;
use PayPal\Api\WebhookEventType;
use PayPal\Common\PayPalModel;
use PayPal\Exception\PayPalConnectionException;

if ( class_exists( 'Give_Recurring_Gateway' ) ) {

	/**
	 * Class Give_Recurring_PayPal_Pro_REST
	 */
	class Give_Recurring_PayPal_Pro_REST extends Give_Recurring_Gateway {

		/**
		 * @var $give_pp_rest Give_PayPal_Pro_Rest
		 */
		protected $give_pp_rest;

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

			$this->give_pp_rest = new Give_PayPal_Pro_Rest();
			$this->id           = 'paypalpro_rest';

			// Cancellation action
			add_action( 'give_recurring_cancel_' . $this->id . '_subscription', array( $this, 'cancel' ), 10, 2 );

		}

		/**
		 * Get API Context
		 *
		 * @return \PayPal\Rest\ApiContext
		 */
		public function get_api_context() {
			$give_paypal_pro_rest = $this->give_pp_rest;

			return $give_paypal_pro_rest->get_token();
		}

		/**
		 * Validate Fields.
		 *
		 * Validate additional fields during checkout submission.
		 *
		 * @since      1.2
		 *
		 * @param $data
		 * @param $posted
		 */
		public function validate_fields( $data, $posted ) {

			$creds = $this->give_pp_rest->api_credentials();

			if ( empty( $creds['client_id'] ) || empty( $creds['secret'] ) ) {
				give_set_error( 'give_recurring_no_paypal_rest_api', __( 'It appears that you have not configured PayPal REST API access. Please configure it in Give &rarr; Settings', 'give-recurring' ) );
			}

		}

		/**
		 * Create payment profiles.
		 *
		 * @since 1.2
		 */
		public function create_payment_profiles() {

			$subscription_name = give_recurring_generate_subscription_name( $this->subscriptions['form_id'], $this->subscriptions['price_id'] );

			$plan_details = array(
				'name'              => substr( $subscription_name, 0, 128 ),
				'description'       => substr( 'Recurring donation to ' . $subscription_name, 0, 128 ),
				// Allowed planType values: FIXED, INFINITE.
				'planType'          => ( $this->subscriptions['bill_times'] == 0 ) ? 'INFINITE' : 'FIXED',
				'paymentName'       => 'Recurring Donations',
				// Allowed paymentType values: TRIAL, REGULAR
				'paymentType'       => 'REGULAR',
				'frequency'         => $this->subscriptions['period'],
				'frequencyInterval' => ! empty( $this->subscriptions['frequency'] ) ? intval( $this->subscriptions['frequency'] ) : 1,
				// The number of cycles in this payment definition. For INFINITE type plans, set cycles to 0 for a REGULAR type payment definition.
				'cycles'            => $this->subscriptions['bill_times'],
				'amount'            => give_maybe_sanitize_amount( $this->subscriptions['recurring_amount'] ),
				'currency'          => give_get_currency()
			);

			$plan = $this->create_billing_plan( $plan_details );

			// Bail out, if Plan is empty.
			if ( isset( $plan ) && empty( $plan ) ) {
				return false;
			}

			// Check if any error in plan creation.
			if ( is_wp_error( $plan ) ) {
				give_set_error( $plan->get_error_code(), sprintf('%s', __( $plan->get_error_message(),'give-recurring') ) );
				return false;
			}

			// Activate billing plan.
			if ( is_object( $plan ) && $plan->state !== 'ACTIVE' ) {
				$plan = $this->activate_billing_plan( $plan->id );
			}

			// Check if any error in plan activation.
			if ( is_wp_error( $plan ) ) {
				give_set_error( $plan->get_error_code(), sprintf('%s', __( $plan->get_error_message(),'give-recurring') ) );
				return false;
			}

			// CC info.
			$card_info     = $this->purchase_data['card_info'];
			$card_info     = array_map( 'trim', $card_info );
			$card_info     = array_map( 'strip_tags', $card_info );
			$customer_name = explode( ' ', $card_info['card_name'] );
			$cc_first_name = array_slice( $customer_name, 0, 1 );
			$cc_last_name  = array_slice( $customer_name, - 1, 1 );

			//The renewal is future date according to period in ISO8601 date format.
			$frequency    = ! empty( $this->subscriptions['frequency'] ) ? intval( $this->subscriptions['frequency'] ) : 1;
			$renewal_date = date( 'c', strtotime( date( 'Y-m-d H:i:s' ) . ' +' . $frequency . ' ' . $this->subscriptions['period'] ) );

			$agreement_args = array(
				'name'        => $subscription_name,
				'description' => 'Recurring donation for ' . $subscription_name,
				'startDate'   => $renewal_date,
				'planID'      => $plan->getId(),
				'email'       => $this->purchase_data['user_email'],
				'card'        => array(
					'type'         => givepp_get_card_type( $card_info['card_number'] ),
					'number'       => $card_info['card_number'],
					'expire_month' => $card_info['card_exp_month'],
					'expire_year'  => $card_info['card_exp_year'],
					'cvv2'         => $card_info['card_cvc'],
					'first_name'   => isset( $cc_first_name[0] ) ? $cc_first_name[0] : '',
					'last_name'    => isset( $cc_last_name[0] ) ? $cc_last_name[0] : ''
				)
			);

			$agreement = $this->create_billing_agreement( $agreement_args );

			if ( is_wp_error( $agreement ) ) {

				give_set_error( $agreement->get_error_code(), __( $agreement->get_error_message(), 'give-recurring' ) );

				return false;

			} elseif ( empty( $agreement ) ) {

				return false;

			} else {

				// Creates a billing agreement and then sets the profile ID
				$this->subscriptions['profile_id'] = $agreement->getId();

				return true;
			}


		}

		/**
		 * Generates a plan ID to be used with PayPal API.
		 *
		 * @param  string $subscription_name Name of the subscription generated from give_recurring_generate_subscription_name.
		 *
		 * @return string
		 */
		public function generate_plan_id( $subscription_name ) {

			$subscription_name = sanitize_title( $subscription_name );

			return sanitize_key( $subscription_name . '_' . give_maybe_sanitize_amount( $this->subscriptions['recurring_amount'] ) . '_' . $this->subscriptions['period'] . '_' . $this->subscriptions['bill_times'] );
		}

		/**
		 * Create Billing Plan.
		 *
		 * @param array $args
		 *
		 * @link https://developer.paypal.com/docs/api/payments.billing-plans/#plan_create
		 *
		 * @return \PayPal\Api\Plan|WP_Error
		 */
		public function create_billing_plan( $args = array() ) {

			$plan_name = give_recurring_generate_subscription_name( $this->subscriptions['form_id'], $this->subscriptions['price_id'] );

			$defaults = array(
				'name'              => substr( $plan_name, 0, 128 ),
				'description'       => substr( 'Recurring donation to ' . get_bloginfo( 'name' ), 0, 128 ),
				'planType'          => 'fixed',
				'paymentName'       => 'Regular Donations',
				'paymentType'       => 'REGULAR',
				'frequency'         => 'Month',
				'frequencyInterval' => '2',
				'cycles'            => '12',
				'amount'            => '1',
				'currency'          => give_get_currency()
			);

			$data = wp_parse_args( $args, $defaults );

			$plan = new Plan();

			$plan->setName( $data['name'] )
			     ->setDescription( $data['description'] )
			     ->setType( $data['planType'] );

			$paymentDefinition = new PaymentDefinition();

			$recurring_amount = new Currency( array(
				'value'    => $data['amount'],
				'currency' => $data['currency']
			) );

			$paymentDefinition->setName( $data['paymentName'] )
			                  ->setType( $data['paymentType'] )
			                  ->setFrequency( $data['frequency'] )
			                  ->setFrequencyInterval( $data['frequencyInterval'] )
			                  ->setCycles( $data['cycles'] )
			                  ->setAmount( $recurring_amount );

			$merchantPreferences = new MerchantPreferences();

			$merchantPreferences->setReturnUrl( give_get_success_page_uri() )
			                    ->setCancelUrl( give_get_failed_transaction_uri() )
			                    ->setAutoBillAmount( 'yes' )
			                    ->setInitialFailAmountAction( 'CONTINUE' )
			                    ->setMaxFailAttempts( '0' )
			                    ->setSetupFee( $recurring_amount ); //The initial charge

			$plan->setPaymentDefinitions( array( $paymentDefinition ) );
			$plan->setMerchantPreferences( $merchantPreferences );

			try {

				$apiContext = $this->get_api_context();
				$output     = $plan->create( $apiContext );

			} catch ( PayPal\Exception\PayPalConnectionException $ex ) {

				$output = $this->log_error( $ex );

			} catch ( Exception $ex ) {

				$output = new WP_Error( 'give_recurring_paypal_pro_plan_creation_failed', __( 'Could not create a new billing plan.', 'give-recurring' ) );
			}

			return $output;

		}

		/**
		 * Retrieve Billing Plan.
		 *
		 * @param $planID
		 *
		 * @link https://developer.paypal.com/docs/api/payments.billing-plans/#plan_get
		 *
		 * @return \PayPal\Api\Plan
		 */
		public function retrieve_billing_plan( $planID ) {

			try {

				$apiContext = $this->get_api_context();
				$output     = Plan::get( $planID, $apiContext );
			} catch ( PayPal\Exception\PayPalConnectionException $ex ) {

				$output = $this->log_error( $ex );

			} catch ( Exception $ex ) {

				$output = new WP_Error( 'give_recurring_paypal_retrieve_billing_plan_failed', __( 'Could not retrieve the billing plan.', 'give-recurring' ) );

			}

			return $output;
		}

		/**
		 * Activate Billing Plan.
		 *
		 * @param $planID
		 *
		 * @link https://developer.paypal.com/docs/api/payments.billing-plans/#plan_update
		 *
		 * @return \PayPal\Api\Plan|\WP_Error
		 */
		public function activate_billing_plan( $planID ) {

			try {

				$plan  = $this->retrieve_billing_plan( $planID );

				// Check if billing plan is retrieved.
				if ( is_wp_error( $plan ) ) {
					$output = new WP_Error( 'give_recurring_paypal_retrieve_billing_plan_failed', __( 'Could not retrieve the billing plan.', 'give-recurring' ) );

					return $output;
				}

				$patch = new Patch();
				$value = new PayPalModel( '{ "state":"ACTIVE" }' );

				$patch->setOp( 'replace' )
				      ->setPath( '/' )
				      ->setValue( $value );

				$patchRequest = new PatchRequest();
				$patchRequest->addPatch( $patch );

				$apiContext = $this->get_api_context();
				$plan->update( $patchRequest, $apiContext );

				$apiContext = $this->get_api_context();
				$output     = Plan::get( $planID, $apiContext );

			} catch ( PayPal\Exception\PayPalConnectionException $ex ) {

				$output = $this->log_error( $ex );

			} catch ( Exception $ex ) {

				$output = new WP_Error( 'give_recurring_paypal_pro_plan_activation_failed', __( 'Could not activate the billing plan.', 'give-recurring' ) );

			}

			return $output;
		}

		/*  BILLING AGREEMENTS  */

		/**
		 * Create Billing Plan
		 *
		 * @param array $args
		 *
		 * @link https://developer.paypal.com/docs/api/payments.billing-agreements#agreement_create
		 *
		 * @return \PayPal\Api\Agreement|WP_Error
		 */
		public function create_billing_agreement( $args = array() ) {

			$defaults = array(
				'name'        => '',
				'description' => '',
				'startDate'   => date( 'c' ), //Current time plus a
				'planID'      => '',
				'email'       => '',
				'card'        => array(
					'type'         => '',
					'number'       => '',
					'expire_month' => '',
					'expire_year'  => '',
					'cvv2'         => '',
					'first_name'   => '',
					'last_name'    => ''
				)
			);

			$data = wp_parse_args( $args, $defaults );

			$agreement = new Agreement();
			$agreement->setName( $data['name'] )
			          ->setDescription( substr( $data['description'], 0, 128 ) )
			          ->setStartDate( $data['startDate'] );

			$plan = new Plan();
			$plan->setId( $data['planID'] );
			$agreement->setPlan( $plan );

			// Add Payer
			$payer = new Payer();
			$payer->setPaymentMethod( 'credit_card' )
			      ->setPayerInfo( new PayerInfo( array( 'email' => $data['email'] ) ) );

			// Credit Card
			$creditCard = $this->give_pp_rest->create_card( $data['card'] );

			// Add CC to Funding Instrument
			$fundingInstrument = new FundingInstrument();
			$fundingInstrument->setCreditCard( $creditCard );
			$payer->setFundingInstruments( array( $fundingInstrument ) );

			// Add Payer to Agreement
			$agreement->setPayer( $payer );

			try {

				$apiContext = $this->get_api_context();
				$output     = $agreement->create( $apiContext );

			} catch ( PayPal\Exception\PayPalConnectionException $ex ) {

				$output = $this->log_error( $ex );

			} catch ( Exception $ex ) {

				$output = new WP_Error( 'give_recurring_paypal_pro_plan_agreement_failed', __( 'Could not create a new billing agreement.', 'give-recurring' ) );

			}

			return $output;

		}

		/**
		 * Retrieve Billing Agreement.
		 *
		 * @param $agreementID
		 *
		 * @link https://developer.paypal.com/docs/api/payments.billing-agreements#agreement_get
		 *
		 * @return \PayPal\Api\Agreement
		 */
		public function retrieve_billing_agreement( $agreementID ) {
			try {
				$apiContext = $this->get_api_context();
				$output     = Agreement::get( $agreementID, $apiContext );
			} catch ( PayPal\Exception\PayPalConnectionException $ex ) {

				$output = $this->log_error( $ex );

			} catch ( Exception $ex ) {

				$output = new WP_Error( 'give_recurring_paypalpro_retrieve_billing_agreement_failed', __( 'Could not retrieve billing agreement.', 'give-recurring' ) );

			}

			return $output;
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
				$subscription->gateway === $this->id
				&& ! empty( $subscription->profile_id )
				&& 'active' === $subscription->status
			) {
				$agreement = $this->retrieve_billing_agreement( $subscription->profile_id );
				if ( 'Active' == $agreement->getState() ) {
					$ret = true;
				}
			}

			return $ret;
		}

		/**
		 * Cancels a subscription.
		 *
		 * @param $subscription
		 * @param $valid
		 *
		 * @return bool
		 */
		public function cancel( $subscription, $valid ) {

			if ( empty( $valid ) || false == $this->can_cancel( false, $subscription ) ) {
				return false;
			}

			// Cancel Agreement with PayPal (assuming $subscription is a valid Agreement ID).
			$agreement = $this->cancel_agreement( $subscription->profile_id );

			// Verify successful response. Possible values are:
			// Active, Pending, Expired, Suspend, Reactivate, and Cancel.
			return ( ! is_wp_error( $agreement ) && 'Cancel' == $agreement->getState() );

		}

		/**
		 * Cancel Agreement.
		 *
		 * @param $agreementID
		 *
		 * @link https://developer.paypal.com/docs/api/payments.billing-agreements#agreement_cancel
		 *
		 * @return \PayPal\Api\Agreement|WP_Error
		 */
		public function cancel_agreement( $agreementID ) {

			//Create an Agreement State Descriptor, explaining the reason to cancel.
			$agreementStateDescriptor = new AgreementStateDescriptor();
			$agreementStateDescriptor->setNote( __( 'Recurring donation cancelled by donor via ' . give_get_subscriptions_page_uri(), 'give-recurring' ) );
			$agreement  = $this->retrieve_billing_agreement( $agreementID );
			$apiContext = $this->get_api_context();

			try {
				$agreement->cancel( $agreementStateDescriptor, $apiContext );
				// get the updated Agreement Object
				$output = Agreement::get( $agreement->getId(), $apiContext );

			} catch ( PayPal\Exception\PayPalConnectionException $ex ) {

				$output = $this->log_error( $ex );

			} catch ( Exception $ex ) {

				$output = new WP_Error( 'give_recurring_paypalpro_cancel_agreement_failed', __( 'Could not cancel the billing agreement.', 'give-recurring' ) );
			}

			return $output;

		}

		/**
		 * Log a PayPal REST Error.
		 *
		 * Logs in the Give db the error and also displays the error message to the donor.
		 *
		 * @param $exception PayPal\Exception\PayPalConnectionException
		 *
		 * @return bool
		 */
		private function log_error( $exception ) {

			$gateway_error = array(
				'error_code'    => $exception->getCode(),
				'error_message' => $exception->getData()
			);

			//Log it with DB
			give_record_gateway_error( __( 'PayPal Pro Recurring REST Error', 'give-recurring' ), sprintf( __( 'PayPal Pro REST returned an error while processing a payment. Details: %s', 'give-recurring' ), json_encode( $gateway_error ) ) );

			$error_obj = json_decode( $gateway_error['error_message'] );

			$error_code = 'give_recurring_paypalpro_error';

			//default error message
			if ( isset( $error_obj->details[0]->issue ) ) {
				$error_message = $error_obj->details[0]->issue;
			} elseif ( isset( $error_obj->message ) && ! empty( $error_obj->message ) ) {
				$error_message = $error_obj->message;
			} else {
				$error_message = __( 'Invalid Request', 'give-recurring' );
			}

			//Check if error object isset
			if ( isset( $error_obj->name ) ) {

				//Switch various errors
				switch ( $error_obj->name ) {

					case 'VALIDATION_ERROR':

						$error_code    = 'give_recurring_paypalpro_validation_error';
						break;
				}
			}

			give_set_error( $error_code, __( 'An error occurred with PayPal: ', 'give-recurring' ) . $error_message );

			return false;

		}


	}

	new Give_Recurring_PayPal_Pro_REST();

}