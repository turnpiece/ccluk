<?php
/**
 * Gateway Actions
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes gateway select on checkout. Only for users without ajax / javascript
 *
 * @since 1.0
 *
 * @param $data
 */
function give_process_gateway_select( $data ) {
	if ( isset( $_POST['gateway_submit'] ) ) {
		wp_redirect( esc_url( add_query_arg( 'payment-mode', $_POST['payment-mode'] ) ) );
		exit;
	}
}

add_action( 'give_gateway_select', 'give_process_gateway_select' );

/**
 * Loads a payment gateway via AJAX.
 *
 * @since 1.0
 *
 * @return void
 */
function give_load_ajax_gateway() {
	if ( isset( $_POST['give_payment_mode'] ) ) {
		/**
		 * Fire to render donation form.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form', $_POST['give_form_id'] );

		exit();
	}
}

add_action( 'wp_ajax_give_load_gateway', 'give_load_ajax_gateway' );
add_action( 'wp_ajax_nopriv_give_load_gateway', 'give_load_ajax_gateway' );

/**
 * Sets an error within the donation form if no gateways are enabled.
 *
 * @since 1.0
 *
 * @return void
 */
function give_no_gateway_error() {
	$gateways = give_get_enabled_payment_gateways();

	if ( empty( $gateways ) ) {
		give_set_error( 'no_gateways', esc_html__( 'You must enable a payment gateway to use Give.', 'give' ) );
	} else {
		give_unset_error( 'no_gateways' );
	}
}

add_action( 'init', 'give_no_gateway_error' );
