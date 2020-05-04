<?php

/**
 * Engine that synchronizes subscriptions in our system and gateway
 *
 * @see https://github.com/impres-org/give-recurring-donations/issues/118
 *
 * Class Give_Subscription_Synchronizer
 */
class Give_Subscription_Synchronizer {

	/**
	 * The subscription that needs to be synced
	 *
	 * @var Give_Subscription
	 */
	private $subscription = null;

	/**
	 * Gateway of the subscription that needs to be synced.
	 *
	 * @var Give_Recurring_Gateway
	 */
	private $gateway = null;

	/**
	 * All payments made for the subscription.
	 *
	 * @var array
	 */
	private $payments = array();

	/**
	 * [$log_id description]
	 *
	 * @var null
	 */
	public $log_id = null;

	/**
	 * Messages to output on the status of the sync.
	 *
	 * @var array
	 */
	public $messages = array();

	/**
	 * Maybe create log.
	 *
	 * @param string $post_content The post content.
	 */
	private function maybe_create_log( $post_content = '' ) {

		if ( ! isset( $this->log_id ) ) {


			$log_title = sprintf( __( '#%1$s Sync Subscription #%2$s with %3$s', 'give-recurring' ), $this->log_id, $this->subscription->id, give_get_gateway_admin_label( $this->subscription->gateway ) );

			$log = array(
				'post_title'   => $log_title,
				'post_type'    => 'give_recur_sync_log',
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'post_content' => $post_content,
				'log_type'     => false,
			);

			$this->log_id = wp_insert_post( $log );

			// Save custom meta w/ gateway and sub ID.
			update_post_meta( $this->log_id, '__give_recurring_sync_log_gateway', give_get_gateway_admin_label( $this->subscription->gateway ) );
			update_post_meta( $this->log_id, '__give_recurring_sync_log_subscription_id', $this->subscription->id );
		}
	}

	/**
	 * Do sync log.
	 *
	 * @return array|null|WP_Post
	 */
	private function do_sync_log() {

		$log = array();

		$new_log_content = $this->generate_log_content();

		if ( isset( $this->log_id ) ) {

			$log               = get_post( $this->log_id );
			$log->post_content = $log->post_content . $new_log_content;

			wp_update_post( $log );

		} else {

			$this->maybe_create_log( $new_log_content );
		}

		return $log;
	}

	/**
	 * Print notice.
	 *
	 * @param string $message     The message that appears.
	 * @param string $notice_type The type of message.
	 */
	public function print_notice( $message, $notice_type = 'normal' ) {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			switch ( $notice_type ) {
				case 'title':
					$message = '<h3>' . $message . '</h3>';
					break;
				case 'subtitle':
					$message = '<h4>' . $message . '</h4>';
					break;
				default:
					$message = '<div class="give-recurring-sync-notice give-recurring-sync-notice-' . esc_attr( $notice_type ) . '">' . $message . '</div>';
			}

		}

		$this->messages[] = $message;
	}

	/**
	 * Clear notices.
	 */
	public function clear_notices() {
		unset( $this->messages );
	}

	/**
	 * Generate log content.
	 *
	 * @return string
	 */
	private function generate_log_content() {

		$log_content = '';

		if ( isset( $this->messages ) ) {
			$log_content = implode( ' ', $this->messages );
		}

		return $log_content;
	}

	/**
	 * Sync subscription.
	 *
	 * @param $subscription
	 */
	public function sync_subscription( $subscription ) {

		$can_sync = $this->setup_subscription_and_gateway( $subscription );

		if ( $can_sync ) {
			$this->sync_subscription_details( $subscription );
		}
	}

	/**
	 * Sync subscription details.
	 *
	 * @param      $subscription
	 * @param bool $should_log Whether to log the sync report or not. Defaults to true.
	 *
	 * @return array|bool
	 */
	public function sync_subscription_details( $subscription, $should_log = true ) {

		$can_sync = $this->setup_subscription_and_gateway( $subscription );

		if ( $can_sync ) {

			$this->print_notice( __( 'Retrieving Subscription Details from Gateway...', 'give-recurring' ), 'subtitle' );

			$subscription_details = $this->get_details();
			$gateway_subscription = $this->gateway->get_subscription_details( $this->subscription );

			// Error check.
			if ( false == $gateway_subscription ) {
				$this->print_notice( __( 'There was an error connecting to the gateway. Please check that you have configured the gateway properly in Give.', 'give-recurring' ), 'error' );

				return array( 'html' => $this->generate_log_content(), 'error' => true );
			}

			$details = array(
				'status'         => array(
					'label' => __( 'Subscription Status', 'give-recurring' ),
				),
				'billing_period' => array(
					'label' => __( 'Billing Period', 'give-recurring' ),
				),
				'created'        => array(
					'label' => __( 'Date Created', 'give-recurring' ),
				),
			);

			foreach ( $details as $key => $detail ) {

				switch ( $key ) {
					case 'billing_period':
						$frequency         = ! empty( $subscription_details['frequency'] ) ? intval( $subscription_details['frequency'] ) : 1;
						$gateway_frequency = ! empty( $gateway_subscription['frequency'] ) ? intval( $gateway_subscription['frequency'] ) : 1;
						$current_detail    = give_recurring_pretty_subscription_frequency( $subscription_details[ $key ], false, false, $frequency );
						$gateway_detail    = give_recurring_pretty_subscription_frequency( $this->normalize_gateway_period( $gateway_subscription[ $key ] ), false, false, $gateway_frequency );
						break;
					case 'created':
						$current_detail = date_i18n( give_date_format(), $subscription_details['created'] );
						$gateway_detail = date_i18n( give_date_format(), $gateway_subscription['created'] );
						break;
					case 'status':
						$current_detail = $this->normalize_gateway_stati( $subscription_details['status'] );
						$gateway_detail = $this->normalize_gateway_stati( $gateway_subscription['status'] );
						break;
					default:
						$current_detail = $subscription_details[ $key ];
						$gateway_detail = $gateway_subscription[ $key ];
				}

				// What are we checking?
				$checking_what = $detail['label'];

				// Output information.
				$this->print_notice( sprintf( __( 'Checking %s', 'give-recurring' ), $checking_what ), 'subtitle' );
				$this->print_notice( sprintf( __( 'Current %1$s: %2$s', 'give-recurring' ), $checking_what, $current_detail ) );
				$this->print_notice( sprintf( __( 'Gateway %1$s: %2$s', 'give-recurring' ), $checking_what, ( ! empty( $gateway_detail ) ? $gateway_detail : '---' ) ) );

				// Can't compare an empty value.
				if ( empty( $gateway_detail ) ) {
					$this->print_notice( sprintf( __( 'Gateway failed to return a value for %s.', 'give-recurring' ), $checking_what ), 'error' );
					continue;
				}

				// Mismatch has been detected.
				if ( $current_detail != $gateway_detail ) {

					$this->print_notice( sprintf( __( 'Mismatch Detected: %s', 'give-recurring' ), $checking_what ), 'error' );
					$this->print_notice( __( 'Fixing Mismatch...', 'give-recurring' ) );

					// Sync the mismatch!
					$sync_result = $this->sync_subscription_detail_mismatch( $gateway_subscription, $gateway_detail, $key );

					// Print message if mismatch has been fixed.
					if ( true === $sync_result ) {
						$this->print_notice( sprintf( __( 'Mismatch Fixed: %s', 'give-recurring' ), $checking_what ), 'success' );
					} else {
						// Error happened with the mismatch.
						$this->print_notice( sprintf( __( 'Mismatch could not be fixed: %s', 'give-recurring' ), $checking_what ), 'error' );
					}

				} else {
					// This subscription's is already in sync.
					$this->print_notice( sprintf( __( '%s is in sync.', 'give-recurring' ), $checking_what ), 'success' );
				}
			}
		} else {
			$this->print_notice( __( 'The Subscription or Gateway could not be setup', 'give-recurring' ), 'error' );
		}

		// Should this sync be logged?
		if ( $should_log ) {
			$this->do_sync_log();
		}

		$out = array(
			'html'   => $this->generate_log_content(),
			'log_id' => $this->log_id
		);

		$this->clear_notices();

		return $out;
	}

	/**
	 * Sync subscription detail mismatches.
	 *
	 * @param $gateway_data
	 * @param $gateway_detail
	 * @param $key
	 *
	 * @return bool $updated
	 */
	public function sync_subscription_detail_mismatch( $gateway_data, $gateway_detail, $key ) {

		$updated = false;
		switch ( $key ) {
			case 'billing_period':
				// Sync a billing_period mismatch.
				$updated = $this->subscription->update( array(
					'period' => $this->normalize_gateway_period( $gateway_data['billing_period'] )
				) );
				break;
			case 'status':
				// Sync a status mismatch.
				$new_status = $this->normalize_gateway_stati( $gateway_detail );
				$updated    = $this->subscription->update( array(
					'status' => $new_status
				) );
				break;
			case 'created':
				// Sync a date created mismatch.
				$updated = $this->subscription->update( array(
					'created' => date( 'Y-m-d H:i:s', $gateway_data['created'] ),
				) );
				break;
		}

		return $updated;

	}

	/**
	 * Normalize gateway stati.
	 *
	 * There are different status for the various payment gateways recurring supports.
	 * This function normalizes those various status for Give's consumption.
	 *
	 * @param $gateway_status
	 *
	 * @return string
	 */
	function normalize_gateway_stati( $gateway_status ) {

		$cancelled_array = array(
			'cancelled',
			'canceled',
			'unpaid',
			'terminated',
			'vendor inactive',
			'deactivated by merchant'
		);
		$expired_array   = array( 'expired', 'too many failures' );
		$pending_array   = array( 'pending', 'past_due' );
		$suspended_array = array( 'suspended' );
		$completed_array = array( 'complete', 'completed' );

		switch ( true ) {
			case in_array( $gateway_status, $cancelled_array ) :
				$normalized_status = 'cancelled';
				break;
			case in_array( $gateway_status, $suspended_array ) :
				$normalized_status = 'suspended';
				break;
			case in_array( $gateway_status, $expired_array ) :
				$normalized_status = 'expired';
				break;
			case in_array( $gateway_status, $pending_array ) :
				$normalized_status = 'pending';
				break;
			case in_array( $gateway_status, $completed_array ) :
				$normalized_status = 'completed';
				break;
			default:
				$normalized_status = 'active';
		}


		return apply_filters( 'normalize_gateway_stati', $normalized_status, $gateway_status );

	}

	/**
	 * Normalize gateway period.
	 *
	 * There are different time periods per gateway; PayPal for instance has "days" instead of "daily".
	 * This method normalizes them for Give.
	 *
	 * @param $gateway_period string
	 *
	 * @return string
	 */
	function normalize_gateway_period( $gateway_period ) {

		$daily_array   = array( 'days', 'day', 'daily' );
		$weekly_array  = array( 'week', 'weekly' );
		$monthly_array = array( 'month', 'mont', 'monthly' );
		$yearly_array  = array( 'year', 'yearly', 'annually', 'annual' );

		switch ( true ) {
			case in_array( $gateway_period, $daily_array ) :
				$normalized_status = 'day';
				break;
			case in_array( $gateway_period, $weekly_array ) :
				$normalized_status = 'week';
				break;
			case in_array( $gateway_period, $monthly_array ) :
				$normalized_status = 'month';
				break;
			case in_array( $gateway_period, $yearly_array ) :
				$normalized_status = 'year';
				break;
			default:
				$normalized_status = '';
		}


		return apply_filters( 'normalize_gateway_period', $normalized_status, $gateway_period );

	}

	/**
	 * Sync subscription transactions.
	 *
	 * @param      $subscription
	 * @param bool $should_log
	 *
	 * @return array|bool
	 */
	public function sync_subscription_transactions( $subscription, $should_log = true ) {

		// Prevent payment completion emails from sending normally.
		add_filter( 'give_recurring_should_send_subscription_received_email', '__return_false' );

		$can_sync = $this->setup_subscription_and_gateway( $subscription );

		if ( isset( $_POST['log_id'] ) ) {
			$this->log_id = $_POST['log_id'];
		}

		if ( ! $can_sync ) {
			$this->print_notice( __( 'This subscription cannot be synced.', 'give-recurring' ), 'error' );
			return false;
		}

		$this->print_notice( __( 'Checking subscription payments', 'give-recurring' ), 'subtitle' );

		$gateway_transactions  = $this->gateway->get_gateway_transactions( $this->subscription, '-6 months' );
		$gateway_transactions  = array_reverse( $gateway_transactions );
		$parent_transaction_id = give_get_meta( $this->subscription->parent_payment_id, '_give_payment_transaction_id', true );
		$parent_donation_date  = get_the_date( 'd-m-Y', $this->subscription->parent_payment_id );

		// Loop through transactions returned by the gateway.
		foreach ( $gateway_transactions as $key => $gateway_transaction ) {

			$transaction_date = date_i18n( give_date_format(), $gateway_transaction['date'] );

			// Verify Parent Donation and update transaction id, if mismatch found.
			if (
				0 === $key
				&& $parent_transaction_id !== $gateway_transaction['transaction_id']
				&& date_i18n( 'd-m-Y', $gateway_transaction['date'] ) === $parent_donation_date
			) {
				give_update_meta( $this->subscription->parent_payment_id, '_give_payment_transaction_id', $gateway_transaction['transaction_id'] );
			}

			// If the transaction doesn't exist, add it.
			if ( $this->can_sync_transaction( $gateway_transaction ) ) {

				$this->print_notice(
					sprintf(
					/* translators: %s Transaction Date. */
						__( 'A donation made on %s is missing and has been added.', 'give-recurring' ),
						$transaction_date
					),
					'item_missing'
				);

				// Add missing payment.
				$this->subscription->add_payment( array(
					'amount'         => $gateway_transaction['amount'],
					'transaction_id' => $gateway_transaction['transaction_id'],
					'post_date'      => date( 'Y-m-d H:i:s', $gateway_transaction['date'] ),
				) );

			} else {

				// This payment exists.
				$this->print_notice(
					sprintf(
					/* translators: %s Transaction Date. */
						__( 'The donation made on %s is already recorded.', 'give-recurring' ),
						$transaction_date
					),
					'item_present'
				);

			}
		} // End foreach().

		// If child payments.
		$child_payments = count( $this->subscription->get_child_payments() );
		if ( $child_payments > 0 ) {
			$this->print_notice(
				sprintf(
				/* translators: %s Child Payments. */
					__( '%s renewal donations are in sync.', 'give-recurring' ),
					$child_payments
				),
				'success'
			);
		}

		if ( $should_log ) {
			$this->do_sync_log();
		}

		$out = array(
			'html'   => $this->generate_log_content(),
			'log_id' => $this->log_id,
		);

		return $out;
	}

	/**
	 * Can sync transactions conditional check.
	 *
	 * Can be filtered by gateways to add their own conditions.
	 *
	 * @param array $gateway_transaction Invoice List of specific subscription from Stripe.
	 *
	 * @return mixed
	 */
	private function can_sync_transaction( $gateway_transaction ) {

		$can_sync   = false;
		$date_match = false;

		foreach ( $this->payments as $payment ) {
			$payment_date = date( 'Y-m-d', strtotime( $payment->post_date ) );
			$gateway_date = date( 'Y-m-d', $gateway_transaction['date'] );

			if ( $payment_date === $gateway_date ) {
				$date_match = true;
			}
		}

		/**
		 * Return true if
		 * a: the payment does NOT exist via transaction_id check
		 * b: there has NOT been a payment made on the date yet
		 */
		if (
			! $this->subscription->payment_exists( $gateway_transaction['transaction_id'] )
			&& false === $date_match
		) {
			$can_sync = true;
		}

		return apply_filters( 'can_sync_transaction', $can_sync, $gateway_transaction );

	}

	/**
	 * Setup subscription and gateway.
	 *
	 * @param      $subscription
	 * @param null $gateway
	 *
	 * @return bool
	 */
	public function setup_subscription_and_gateway( $subscription, $gateway = null ) {

		if ( $subscription instanceof Give_Subscription ) {

			$gateway_id = $subscription->gateway;

		} elseif ( is_numeric( $subscription ) ) {

			$subscription = new Give_Subscription( $subscription );
			$gateway_id   = $subscription->gateway;

		} else {
			return false;
		}

		if ( null === $gateway ) {
			$gateway = give_recurring_get_gateway( $gateway_id, $subscription );
		}

		$this->subscription = $subscription;
		$this->gateway      = $gateway;

		$initial_payment = $this->subscription->get_initial_payment();
		$this->payments  = $this->subscription->get_child_payments();
		array_push( $this->payments, $initial_payment );

		return true;


	}


	/**
	 * Get subscription details.
	 *
	 * @return array
	 */
	public function get_details() {

		$details = array(
			'status'         => $this->subscription->status,
			'created'        => strtotime( $this->subscription->created ),
			'expiration'     => strtotime( $this->subscription->expiration ),
			'billing_period' => $this->subscription->period,
			'bill_times'     => $this->subscription->bill_times,
			'frequency'      => ! empty( $this->subscription->frequency ) ? intval( $this->subscription->frequency ) : 1,
		);

		return $details;
	}
}
