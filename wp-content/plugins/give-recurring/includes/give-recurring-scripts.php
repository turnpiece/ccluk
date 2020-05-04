<?php
/**
 * Give Recurring Scripts
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Give Recurring Scripts
 *
 * Enqueues frontend CSS and javascript.
 *
 * @since  1.0
 *
 * @return void
 */
function give_recurring_frontend_scripts() {

	wp_register_style( 'give_recurring_css', GIVE_RECURRING_PLUGIN_URL . 'assets/css/give-recurring.css', array(), GIVE_RECURRING_VERSION );
	wp_enqueue_style( 'give_recurring_css' );

	wp_register_script( 'give_recurring_script', GIVE_RECURRING_PLUGIN_URL . 'assets/js/give-recurring.js', array(), GIVE_RECURRING_VERSION );
	wp_enqueue_script( 'give_recurring_script' );

	$ajax_vars = array(
		'email_access'                 => give_is_setting_enabled( give_get_option( 'email_access' ) ),
		'pretty_intervals'             => give_recurring_get_default_pretty_intervals(),
		'pretty_periods'               => give_recurring_get_default_pretty_periods(),
		'messages'                     => array(
			'daily_forbidden' => __( 'The selected payment method does not support daily recurring giving. Please select another payment method or supported giving frequency.', 'give-recurring' ),
			'confirm_cancel'  => __( 'Are you sure you want to cancel this subscription?', 'give-recurring' ),
		),
		'multi_level_message_pre_text' => __( 'You have chosen to donate', 'give-recurring' ),
	);

	wp_localize_script( 'give_recurring_script', 'Give_Recurring_Vars', $ajax_vars );

}

add_action( 'wp_enqueue_scripts', 'give_recurring_frontend_scripts' );


/**
 * Admin Scripts
 *
 * Enqueues admin CSS and javascript.
 *
 * @since  1.0
 *
 * @param  string $hook The current page hook in wp-admin.
 *
 * @return void
 */
function give_recurring_admin_scripts( $hook ) {

	global $post;

	// Register all scripts (we'll enqueue them conditionally later)
	wp_register_style( 'give_recurring_donation_styles', GIVE_RECURRING_PLUGIN_URL . 'assets/css/give-recurring-admin-donations.css', array(), GIVE_RECURRING_VERSION );
	wp_register_style( 'give_recurring_sync_styles', GIVE_RECURRING_PLUGIN_URL . 'assets/css/give-recurring-admin-synchronizer.css' );
	wp_register_style( 'give_recurring_subscription_styles', GIVE_RECURRING_PLUGIN_URL . 'assets/css/give-recurring-admin-subscriptions.css', array( 'give-admin-styles' ), GIVE_RECURRING_VERSION );
	wp_register_style( 'give_recurring_subscription_sync_modal', GIVE_RECURRING_PLUGIN_URL . 'assets/css/give-recurring-admin-modal.css', array( 'give-admin-styles' ), GIVE_RECURRING_VERSION );
	wp_register_style( 'give_recurring_settings_styles', GIVE_RECURRING_PLUGIN_URL . 'assets/css/give-recurring-admin-settings.css' );

	wp_register_script( 'give_recurring_subscription_sync_modal', GIVE_RECURRING_PLUGIN_URL . 'assets/js/give-recurring-admin-modal.js', array( 'jquery' ), GIVE_RECURRING_VERSION );
	wp_register_script( 'give_admin_recurring_subscriptions', GIVE_RECURRING_PLUGIN_URL . 'assets/js/give-recurring-admin-subscriptions.js', array( 'jquery' ), GIVE_RECURRING_VERSION );
	wp_register_script( 'give_admin_recurring_sync_modal', GIVE_RECURRING_PLUGIN_URL . 'assets/js/give-recurring-admin-modal.js', array( 'jquery' ), GIVE_RECURRING_VERSION );
	wp_register_script( 'give_admin_recurring_synchronizer', GIVE_RECURRING_PLUGIN_URL . 'assets/js/give-recurring-admin-synchronizer.js', array( 'jquery' ), GIVE_RECURRING_VERSION );
	wp_register_script( 'give_recurring_settings_scripts', GIVE_RECURRING_PLUGIN_URL . 'assets/js/give-recurring-admin-settings.js', array( 'jquery' ) );

	// Payment History.
	if ( 'give_forms_page_give-payment-history' === $hook ) {
		wp_enqueue_style( 'give_recurring_donation_styles' );
	}

	// Subscriptions.
	if ( 'give_forms_page_give-subscriptions' === $hook ) {

		// General styles.
		wp_enqueue_style( 'give_recurring_subscription_styles' );

		// Sync styles.
		wp_enqueue_style( 'give_recurring_subscription_sync_modal' );
		wp_enqueue_style( 'give_recurring_sync_styles' );

		// Sync scripts.
		wp_enqueue_script( 'give_recurring_subscription_sync_modal' );
		wp_enqueue_script( 'give_admin_recurring_sync_modal' );
		wp_enqueue_script( 'give_admin_recurring_synchronizer' );

		wp_enqueue_script( 'give_admin_recurring_subscriptions' );

		$ajax_vars = array(
			'adminAjaxNonce'                       => wp_create_nonce( 'give_recurring_admin_ajax_secure_nonce' ),
			'confirm_cancel'                       => __( 'Are you sure you want to cancel this subscription? This will cancel the subscription at the gateway and the donor will no longer be charged.', 'give-recurring' ),
			'delete_subscription'                  => __( 'Are you sure you want to delete this subscription?', 'give-recurring' ),
			'delete_subscription_note'             => __( 'Are you sure you want to delete this subscription note?', 'give-recurring' ),
			'confirm_sync'                         => __( 'Are you sure you want to synchronize this subscription?', 'give-recurring' ),
			'sync_subscription_details_nonce'      => wp_create_nonce( 'sync-subscription-details' ),
			'sync_subscription_details'            => __( 'Synchronizing Subscription Details...', 'give-recurring' ),
			'sync_subscription_transactions_nonce' => wp_create_nonce( 'sync-subscription-transactions' ),
			'sync_subscription_transactions'       => __( 'Synchronizing Subscription Donations...', 'give-recurring' ),
			'give_recurring_ajax_url'              => admin_url( 'admin-ajax.php' ),
			'action_edit'                          => __( 'Edit', 'give-recurring' ),
			'action_cancel'                        => __( 'Cancel', 'give-recurring' ),
			'subscriptions_bulk_action'            => array(
				'delete'        => array(
					'zero'     => __( 'You must choose at least one or more subscriptions to delete.', 'give-recurring' ),
					'single'   => __( 'Are you sure you want to permanently delete this subscription?', 'give-recurring' ),
					'multiple' => __( 'Are you sure you want to permanently delete the selected {subscription_count} subscriptions?', 'give-recurring' ),
				),
				'set-to-status' => array(
					'zero'     => __( 'You must choose at least one or more subscriptions to set status to {status}.', 'give-recurring' ),
					'single'   => __( 'Are you sure you want to set status of this subscription to {status}?', 'give-recurring' ),
					'multiple' => __( 'Are you sure you want to set status of {subscription_count} subscriptions to {status}?', 'give-recurring' ),
				),
			),
		);
		wp_localize_script( 'give_admin_recurring_subscriptions', 'Give_Recurring_Vars', $ajax_vars );

	}

	// Recurring Donations Settings.
	if ( 'give_forms_page_give-settings' === $hook ) {
		wp_enqueue_style( 'give_recurring_settings_styles' );
		wp_enqueue_script( 'give_recurring_settings_scripts' );
	}


	// Tools.
	if ( 'give_forms_page_give-tools' === $hook ) {
		wp_enqueue_style( 'give_recurring_sync_styles' );
	}


	$ajax_vars = array(
		'singular'           => _x( 'time', 'Referring to billing period', 'give-recurring' ),
		'plural'             => _x( 'times', 'Referring to billing period', 'give-recurring' ),
		'enabled_gateways'   => give_get_enabled_payment_gateways(),
		'invalid_time'       => array(
			'paypal' => __( 'PayPal Standard requires recurring times to be more than 1. Please specify a time with a minimum value of 2 and a maximum value of 52.', 'give-recurring' ),
			'stripe' => __( 'Stripe requires that the Times option be set to 0.', 'give-recurring' ),
		),
		'invalid_period'     => array(
			'wepay'      => __( 'WePay does not allow for daily recurring donations. Please select a period other than daily.', 'give-recurring' ),
			'authorize'  => __( 'Authorize.net does not allow for daily recurring donations. Please select a period other than daily.', 'give-recurring' ),
			'gocardless' => __( 'GoCardless does not allow for daily recurring donations. Please select a period other than daily.', 'give-recurring' ),
		),
		'email_access'       => give_is_setting_enabled( give_get_option( 'email_access' ) ),
		'subscriptions_page' => give_recurring_subscriptions_page_id(),
		'messages'           => array(
			'login_required' => '<div class="give-recurring-login-required"><p class="recurring-email-access-message">' . sprintf( __( '<strong>Notice:</strong> If you do not have <a href="%1$s" target="_blank">email access</a> enabled, the donor is required to register or login to complete a subscription donation. Turn on email access to not require registration or login.', 'give-recurring' ), admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=general&section=access-control' ) ) . '</p></div>',
		),
		'billingLimits'      => Give_Recurring()->times(),
		'email_notice_nonce' => wp_create_nonce( 'email-notice-nonce' ),
	);

	wp_localize_script( 'give_recurring_settings_scripts', 'Give_Recurring_Vars', $ajax_vars );

	// Single Give Forms Beyond this Point only.
	if ( ! is_object( $post ) ) {
		return;
	}

	if ( 'give_forms' !== $post->post_type ) {
		return;
	}

	$pages = array( 'post.php', 'post-new.php' );

	if ( ! in_array( $hook, $pages, true ) ) {
		return;
	}

	// Add additional AJAX vars.
	$ajax_vars['recurring_option'] = give_get_meta( $post->ID, '_give_recurring', true );

	$billing_limit                       = give_get_meta( $post->ID, '_give_times', true );
	$billing_limit                       = ! empty( $billing_limit ) ? $billing_limit : 0;
	$ajax_vars['selected_billing_limit'] = $billing_limit;

	$selected_billing_limit_donation_level          = give_recurring_get_billing_times( $post->ID );
	$selected_billing_limit_donation_level          = wp_json_encode( $selected_billing_limit_donation_level );
	$ajax_vars['selected_multilevel_billing_limit'] = $selected_billing_limit_donation_level;

	$custom_amount_billing_limit                       = give_get_meta( $post->ID, '_give_recurring_custom_amount_times', true );
	$custom_amount_billing_limit                       = ! empty( $custom_amount_billing_limit ) ? $custom_amount_billing_limit : 0;
	$ajax_vars['selected_custom_amount_billing_limit'] = $custom_amount_billing_limit;

	wp_register_script( 'give_admin_recurring_forms', GIVE_RECURRING_PLUGIN_URL . 'assets/js/give-recurring-admin-forms.js', array( 'jquery' ) );
	wp_enqueue_script( 'give_admin_recurring_forms' );

	wp_register_style( 'give_admin_recurring_forms_css', GIVE_RECURRING_PLUGIN_URL . 'assets/css/give-recurring-admin-form.css' );
	wp_enqueue_style( 'give_admin_recurring_forms_css' );

	wp_localize_script( 'give_admin_recurring_forms', 'Give_Recurring_Vars', $ajax_vars );

}

add_action( 'admin_enqueue_scripts', 'give_recurring_admin_scripts' );
