<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     Give
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Checks if Guest checkout is enabled for a particular donation form
 *
 * @since      1.0
 * @deprecated 1.4.1
 *
 * @param int $form_id
 *
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function give_no_guest_checkout( $form_id ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.4.1', null, $backtrace );

	$ret = give_get_meta( $form_id, '_give_logged_in_only', true );

	return (bool) apply_filters( 'give_no_guest_checkout', give_is_setting_enabled( $ret ) );
}


/**
 * Default Log Views
 *
 * @since      1.0
 * @deprecated 1.8
 * @return array $views Log Views
 */
function give_log_default_views() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8', null, $backtrace );

	$views = array(
		'sales'          => __( 'Donations', 'give' ),
		'gateway_errors' => __( 'Payment Errors', 'give' ),
		'api_requests'   => __( 'API Requests', 'give' ),
	);

	$views = apply_filters( 'give_log_views', $views );

	return $views;
}

/**
 * Donation form validate agree to "Terms and Conditions".
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_agree_to_terms() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_agree_to_terms', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_agree_to_terms();

}

/**
 * Donation Form Validate Logged In User.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_logged_in_user() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_logged_in_user', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_logged_in_user();

}

/**
 * Donation Form Validate Logged In User.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_gateway() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_gateway', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_gateway();

}

/**
 * Donation Form Validate Fields.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_fields() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_fields', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_fields();

}

/**
 * Validates the credit card info.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_cc() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_cc', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_cc();

}

/**
 * Validates the credit card info.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_get_purchase_cc_info() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_get_donation_cc_info', $backtrace );

	// Call new renamed function.
	give_get_donation_cc_info();

}


/**
 * Validates the credit card info.
 *
 * @since      1.0
 * @deprecated 1.8.8
 *
 * @param int    $zip
 * @param string $country_code
 */
function give_purchase_form_validate_cc_zip( $zip = 0, $country_code = '' ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_cc_zip', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_cc_zip( $zip, $country_code );

}

/**
 * Donation form validate user login.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_user_login() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_user_login', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_user_login();

}

/**
 * Donation Form Validate Guest User
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_guest_user() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_guest_user', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_guest_user();

}

/**
 * Donate Form Validate New User
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_new_user() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_new_user', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_new_user();

}


/**
 * Get Donation Form User
 *
 * @since      1.0
 * @deprecated 1.8.8
 *
 * @param array $valid_data
 */
function give_get_purchase_form_user( $valid_data = array() ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_get_donation_form_user', $backtrace );

	// Call new renamed function.
	give_get_donation_form_user( $valid_data );

}

/**
 * Give Checkout Button.
 *
 * Renders the button on the Checkout.
 *
 * @since      1.0
 * @deprecated 1.8.8
 *
 * @param  int $form_id The form ID.
 *
 * @return string
 */
function give_checkout_button_purchase( $form_id ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_get_donation_form_submit_button', $backtrace );

	return give_get_donation_form_submit_button( $form_id );

}

/**
 * Get the donor ID associated with a payment.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return int $customer_id Customer ID.
 */
function give_get_payment_customer_id( $payment_id ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_payment_donor_id', $backtrace );

	return give_get_payment_donor_id( $payment_id );
}


/**
 * Get Total Donations.
 *
 * @since  1.0
 *
 * @return int $count Total sales.
 */
function give_get_total_sales() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_total_donations', $backtrace );

	return give_get_total_donations();
}


/**
 * Count number of donations of a donor.
 *
 * Returns total number of donations a donor has made.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor.
 *
 * @return      int The total number of donations
 */
function give_count_purchases_of_customer( $user = null ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_count_donations_of_donor', $backtrace );

	return give_count_donations_of_donor( $user );
}


/**
 * Get Donation Status for User.
 *
 * Retrieves the donation count and the total amount spent for a specific user.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor to retrieve stats for.
 *
 * @return      array
 */
function give_get_purchase_stats_by_user( $user = '' ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_donation_stats_by_user', $backtrace );

	return give_get_donation_stats_by_user( $user );

}

/**
 * Get Users Donations
 *
 * Retrieves a list of all donations by a specific user.
 *
 * @since  1.0
 *
 * @param int    $user   User ID or email address
 * @param int    $number Number of donations to retrieve
 * @param bool   $pagination
 * @param string $status
 *
 * @return bool|object List of all user donations
 */
function give_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_users_donations', $backtrace );

	return give_get_users_donations( $user, $number, $pagination, $status );

}


/**
 * Has donations
 *
 * Checks to see if a user has donated to at least one form.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int $user_id The ID of the user to check.
 *
 * @return      bool True if has donated, false other wise.
 */
function give_has_purchases( $user_id = null ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_has_donations', $backtrace );

	return give_has_donations( $user_id );
}

/**
 * Counts the total number of donors.
 *
 * @access        public
 * @since         1.0
 *
 * @return        int The total number of donors.
 */
function give_count_total_customers() {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_count_total_donors', $backtrace );

	return give_count_total_donors();
}

/**
 * Calculates the total amount spent by a user.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor.
 *
 * @return      float The total amount the user has spent
 */
function give_purchase_total_of_user( $user = null ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_donation_total_of_user', $backtrace );

	return give_donation_total_of_user( $user );
}

/**
 * Deletes a Donation
 *
 * @since  1.0
 * @global      $give_logs
 *
 * @param  int  $payment_id      Payment ID (default: 0).
 * @param  bool $update_customer If we should update the customer stats (default:true).
 *
 * @return void
 */
function give_delete_purchase( $payment_id = 0, $update_customer = true ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_delete_donation', $backtrace );

	give_delete_donation( $payment_id, $update_customer );

}


/**
 * Undo Donation
 *
 * Undoes a donation, including the decrease of donations and earning stats.
 * Used for when refunding or deleting a donation.
 *
 * @since  1.0
 *
 * @param  int|bool $form_id    Form ID (default: false).
 * @param  int      $payment_id Payment ID.
 *
 * @return void
 */
function give_undo_purchase( $form_id = false, $payment_id ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_undo_donation', $backtrace );

	give_undo_donation( $payment_id );
}


/**
 * Trigger a Donation Deletion.
 *
 * @since 1.0
 *
 * @param array $data Arguments passed.
 *
 * @return void
 */
function give_trigger_purchase_delete( $data ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_trigger_donation_delete', $backtrace );

	give_trigger_donation_delete( $data );
}


/**
 * Increases the donation total count of a donation form.
 *
 * @since 1.0
 *
 * @param int $form_id  Give Form ID
 * @param int $quantity Quantity to increase donation count by
 *
 * @return bool|int
 */
function give_increase_purchase_count( $form_id = 0, $quantity = 1 ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_increase_donation_count', $backtrace );

	give_increase_donation_count( $form_id, $quantity );
}


/**
 * Record Donation In Log
 *
 * Stores log information for a donation.
 *
 * @since 1.0
 * @global            $give_logs Give_Logging
 *
 * @param int         $give_form_id Give Form ID.
 * @param int         $payment_id   Payment ID.
 * @param bool|int    $price_id     Price ID, if any.
 * @param string|null $sale_date    The date of the sale.
 *
 * @return void
 */
function give_record_sale_in_log( $give_form_id = 0, $payment_id, $price_id = false, $sale_date = null ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_record_donation_in_log', $backtrace );

	give_record_donation_in_log( $give_form_id, $payment_id, $price_id, $sale_date );
}

/**
 * Print Errors
 *
 * Prints all stored errors. Ensures errors show up on the appropriate form;
 * For use during donation process. If errors exist, they are returned.
 *
 * @since 1.0
 * @uses  give_get_errors()
 * @uses  give_clear_errors()
 *
 * @param int $form_id Form ID.
 *
 * @return void
 */
function give_print_errors( $form_id ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'Give_Notice::print_frontend_errors', $backtrace );

	do_action( 'give_frontend_notices', $form_id );
}

/**
 * Give Output Error
 *
 * Helper function to easily output an error message properly wrapped; used commonly with shortcodes
 *
 * @since      1.3
 *
 * @param string $message  Message to store with the error.
 * @param bool   $echo     Flag to print or return output.
 * @param string $error_id ID of the error being set.
 *
 * @return   string  $error
 */
function give_output_error( $message, $echo = true, $error_id = 'warning' ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'Give_Notice::print_frontend_notice', $backtrace );

	Give()->notices->print_frontend_notice( $message, $echo, $error_id );
}


/**
 * Get Donation Summary
 *
 * Retrieves the donation summary.
 *
 * @since       1.0
 *
 * @param array $purchase_data
 * @param bool  $email
 *
 * @return string
 */
function give_get_purchase_summary( $purchase_data, $email = true ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.12', 'give_payment_gateway_donation_summary', $backtrace );

	give_payment_gateway_donation_summary($purchase_data, $email);

}
