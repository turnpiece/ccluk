<?php
/**
 * Give - Razorpay | Recurring Donations Support.
 *
 * @since 1.9.5
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class exists check ensure that Recurring donations add-on is installed and the Give_Recurring_RazorPay class not exists.
 *
 * @since 1.9.5
 */
if ( ! class_exists( 'Give_Recurring_RazorPay' ) ) {

	/**
	 * Class Give_Recurring_RazorPay
	 *
	 * @since 1.9.5
	 */
	class Give_Recurring_RazorPay extends Give_Recurring_Gateway {

		/**
		 * RazorPay API.
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @var $razorpay_api
		 */
		public $razorpay_api;

		/**
		 * Give_Recurring_RazorPay constructor.
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return void
		 */
		public function init() {

			$this->id = 'razorpay';

			// Setup RazorPay API.
			$this->razorpay_api = give_razorpay_get_api();

			add_action( "give_recurring_cancel_{$this->id}_subscription", array( $this, 'cancel' ), 10, 2 );
		}

		/**
		 * Process required update related to subscriptions.
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return void
		 */
		public function create_payment_profiles() {

			$razorpay_response = json_decode( urldecode( $_POST['give_razorpay_response'] ), true );
			$form_id           = absint( $this->purchase_data['post_data']['give-form-id'] );

			// Capture Razorpay payment.
			try {
				if ( ! give_razorpay_validate_payment( $form_id, $razorpay_response ) ) {
					throw new Exception( __( 'Invalid donation', 'give-recurring' ) );
				}
			} catch ( Exception $e ) {

				give_record_gateway_error(
					__( 'Razorpay Error', 'give-recurring' ),
					__( 'Transaction Failed.', 'give-recurring' )
					. '<br><br>' . sprintf( esc_attr__( 'Error Detail: %s', 'give-recurring' ), '<br>' . print_r( $e->getMessage(), true ) )
					. '<br><br>' . sprintf( esc_attr__( 'Razorpay Response: %s', 'give-recurring' ), '<br>' . print_r( $razorpay_response, true ) )
				);

				give_set_error( 'give-razorpay', __( 'An error occurred while processing your payment. Please try again.', 'give-recurring' ) );

				// Problems? Send back.
				give_send_back_to_checkout();
			}

			// Setup session data.
			$subscription_session = Give()->session->get( 'razorpay_subscription' );

			// Store Customer ID.
			if ( ! empty( $subscription_session['customer_id'] ) ) {
				give_insert_payment_note( $this->payment_id, sprintf(
					'%1$s: %2$s',
					__( 'Customer ID', 'give-recurring' ),
					$subscription_session['customer_id']
				) );
				give_razorpay_save_customer_id( $this->payment_id, $subscription_session['customer_id'] );
			}

			// Set Subscription ID in DB.
			$this->subscriptions['profile_id']     = $razorpay_response['razorpay_subscription_id'];
			give_insert_payment_note( $this->payment_id, sprintf(
				'%1$s: %2$s',
				__( 'Subscription ID', 'give-recurring' ),
				$razorpay_response['razorpay_subscription_id']
			) );

			// Set First Transaction ID in DB.
			$this->subscriptions['transaction_id'] = $razorpay_response['razorpay_payment_id'];
			give_insert_payment_note( $this->payment_id, sprintf(
				'%1$s: %2$s',
				__( 'Payment ID', 'give-recurring' ),
				$razorpay_response['razorpay_payment_id']
			) );

			give_set_payment_transaction_id( $this->payment_id, $this->subscriptions['transaction_id'] );

			// Reset the subscription session.
			Give()->session->set( 'razorpay_subscription', false );
		}

		/**
		 * Can Cancel.
		 *
		 * @param $ret
		 * @param $subscription
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return bool
		 */
		public function can_cancel( $ret, $subscription ) {

			if (
				$subscription->gateway === $this->id &&
				! empty( $subscription->profile_id ) &&
				'active' === $subscription->status
			) {
				$ret = true;
			}

			return $ret;
		}

		/**
		 * Cancels a Subscription.
		 *
		 * @param  Give_Subscription $subscription
		 * @param  bool              $valid
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return bool
		 */
		public function cancel( $subscription, $valid ) {

			// Bailout, if no access cancel subscription.
			if ( empty( $valid ) ) {
				return false;
			}

			$razorpay_subscription = new Give_RazorPay_Subscriptions();

			return $razorpay_subscription->cancel_subscription( $subscription->profile_id );
		}

		/**
		 * Link the recurring profile in Stripe.
		 *
		 * @param string $profile_id   The recurring profile id.
		 * @param object $subscription The Subscription object.
		 *
		 * @since  1.9.5
		 * @access public
		 *
		 * @return string
		 */
		public function link_profile_id( $profile_id, $subscription ) {

			if ( ! empty( $profile_id ) ) {
				$html       = '<a href="%s" target="_blank">' . $profile_id . '</a>';
				$base_url   = 'https://dashboard.razorpay.com/#/app/';
				$link       = esc_url( $base_url . 'subscriptions/' . $profile_id );
				$profile_id = sprintf( $html, $link );
			}

			return $profile_id;

		}
	}

	new Give_Recurring_RazorPay();
}