<?php
/**
 * Admin Export Donation
 *
 * @package     Give_Recurring
 * @copyright   Copyright (c) 2015, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add recurring fields in standard columns
 *
 * @since 1.6
 */
function give_recurring_export_donation_standard_payment_fields() {
	?>
	<li>
		<label for="give-export-payment-type">
			<input type="checkbox" checked
			       name="give_give_donations_export_option[payment_type]"
			       id="give-export-payment-type"><?php _e( 'Payment Type', 'give-recurring' ); ?>
		</label>
	</li>

	<li>
		<label for="give-export-payment-give_period">
			<input type="checkbox" checked
			       name="give_give_donations_export_option[give_period]"
			       id="give-export-payment-give_period"><?php _e( 'Subscription Billing Period', 'give-recurring' ); ?>
		</label>
	</li>

	<li>
		<label for="give-export-payment-give_times">
			<input type="checkbox" checked
			       name="give_give_donations_export_option[give_times]"
			       id="give-export-payment-give_times"><?php _e( 'Subscription Billing Times', 'give-recurring' ); ?>
		</label>
	</li>

	<li>
		<label for="give-export-payment-give_period_interval">
			<input type="checkbox" checked
			       name="give_give_donations_export_option[give_period_interval]"
			       id="give-export-payment-give_period_interval"><?php _e( 'Subscription Billing Frequency', 'give-recurring' ); ?>
		</label>
	</li>
	<?php
}

add_action( 'give_export_donation_standard_payment_fields', 'give_recurring_export_donation_standard_payment_fields' );

/**
 * Add recurring columns name in CSV heading
 *
 * @since 1.6
 *
 * @param array $cols columns name for CSV
 * @param array $columns Total number of columns name for CSV
 *
 * @return  array $cols columns name for CSV
 */
function give_recurring_export_donation_get_columns_name( $cols, $columns ) {
	foreach ( $columns as $key => $value ) {
		switch ( $key ) {
			case 'payment_type' :
				$cols['payment_type'] = __( 'Payment Type', 'give-recurring' );
				break;
			case 'give_period' :
				$cols['give_period'] = __( 'Subscription Billing Period', 'give-recurring' );
				break;
			case 'give_times' :
				$cols['give_times'] = __( 'Subscription Billing Times', 'give-recurring' );
				break;
			case 'give_period_interval' :
				$cols['give_period_interval'] = __( 'Subscription Billing Frequency', 'give-recurring' );
				break;
		}
	}

	return $cols;
}

add_filter( 'give_export_donation_get_columns_name', 'give_recurring_export_donation_get_columns_name', 10, 2 );

/**
 * Filter to add donation recurring data in CSV columns
 *
 * @since 1.6
 *
 * @param array $data Donation Data for CSV
 * @param Give_Payment $payment Instance of Give_Payment
 * @param array $columns Donation columns
 * @param Give_Export_Donations_CSV $instance Instance of Give_Export_Donations_CSV
 *
 * @return  array $data Donation Data for CSV
 */
function give_recurring_give_export_donation_data( $data, $payment, $columns, $instance ) {
	// update donation payment type
	$subscription_payment = $payment->get_meta( '_give_subscription_payment' );
	if ( ! empty( $columns['payment_type'] ) ) {
		if ( $subscription_payment ) {
			$data['payment_type'] = __( 'subscription', 'give-recurring' );
		} elseif ( 'give_subscription' === $payment->status ) {
			$data['payment_type'] = __( 'renewal', 'give-recurring' );
		} else {
			$data['payment_type'] = __( 'one time', 'give-recurring' );
		}
	}

	$data['give_period']          = '';
	$data['give_times']           = '';
	$data['give_period_interval'] = '';

	if ( ! empty( $subscription_payment ) ) {
		$subscription                 = give_recurring_get_subscription_by( 'payment', $payment->ID );
		$data['give_period']          = $subscription->period;
		$data['give_times']           = ( $subscription->bill_times == 0 ) ? __( 'Ongoing', 'give-recurring' ) : $subscription->bill_times;
		$data['give_period_interval'] = $subscription->frequency;
	}

	return $data;
}

add_filter( 'give_export_donation_data', 'give_recurring_give_export_donation_data', 20, 4 );

/*
 * Remove recurring fields from hidden fields
 *
 * @since 1.6
 *
 * @param array $ignore_hidden_keys Hidden fields that are not going to get display in hidden fields columns
 *
 * @return array $ignore_hidden_keys Hidden fields that are not going to get display in hidden fields columns
 */
function give_recurring_export_donations_ignore_hidden_keys( $ignore_hidden_keys ) {
	$ignore_hidden_keys[] = '_give_subscription_payment';
	$ignore_hidden_keys[] = '_give_is_donation_recurring';

	return $ignore_hidden_keys;
}

add_filter( 'give_export_donations_ignore_hidden_keys', 'give_recurring_export_donations_ignore_hidden_keys' );