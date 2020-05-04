<?php
/**
 * Admin Subscription Functions
 */

/**
 * Render the Subscriptions List table.
 *
 * @access      public
 * @package     Give
 * @since       1.0
 * @return      void
 */
function give_subscriptions_page() {

	if ( ! empty( $_GET['id'] ) ) {
		give_recurring_subscription_details();

		return;
	}
	?>
	<div class="wrap">
		<h1 id="give-subscription-list-h1" class="wp-heading-inline"><?php esc_html_e( 'Subscriptions', 'give-recurring' ); ?></h1>
		<hr class="wp-header-end">
		<?php
		$subscribers_table = new Give_Subscription_Reports_Table();
		$subscribers_table->prepare_items();
		?>

		<form id="subscribers-filter" method="get">
			<input type="hidden" name="post_type" value="give_forms" />
			<input type="hidden" name="page" value="give-subscriptions" />
			<?php $subscribers_table->views() ?>
			<?php $subscribers_table->advanced_filters() ?>
			<?php $subscribers_table->display() ?>
		</form>

		<?php
		/**
		 * Fires in subscription history screen, at the bottom of the page.
		 *
		 * @since 1.9.9
		 */
		do_action( 'give_recurring_subscriptions_page_bottom' );
		?>
	</div>
	<?php
}


/**
 * Handles manual subscription updating within WP-admin.
 *
 * @access      public
 * @since       1.2
 * @return      void
 */
function give_recurring_process_subscription_update() {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	// Need these to continue.
	if ( empty( $post_data['sub_id'] ) || empty( $post_data['give_update_subscription'] ) || ! current_user_can( 'edit_give_payments' ) ) {
		return;
	}

	// Security check.
	if ( ! wp_verify_nonce( $post_data['give-recurring-update-nonce'], 'give-recurring-update' ) ) {
		wp_die(
			esc_html__( 'Nonce verification failed.', 'give-recurring' ),
			esc_html__( 'Error', 'give-recurring' ),
			array(
				'response' => 403,
			)
		);
	}

	$expiration             = date( 'Y-m-d 23:59:59', strtotime( $post_data['expiration'] ) );
	$profile_id             = $post_data['profile_id'];
	$transaction_id         = $post_data['transaction_id'];
	$subscription           = new Give_Subscription( absint( $post_data['sub_id'] ) );
	$initial_transaction_id = give_get_payment_transaction_id( $subscription->parent_payment_id );

	$subscription->update( array(
		'status'         => $post_data['status'],
		'expiration'     => $expiration,
		'profile_id'     => $profile_id,
		'transaction_id' => $transaction_id,
	) );

	// If initial transaction id and the newly added transaction id doesn't match, then add to the donation.
	if ( $initial_transaction_id !== $transaction_id ) {

		// Set the new transaction id to the initial donation.
		give_update_payment_meta( $subscription->parent_payment_id, '_give_payment_transaction_id', $transaction_id );
	}

	$redirect_to = sprintf(
		admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&give-message=updated&id=%1$s' ),
		$subscription->id
	);

	wp_safe_redirect( esc_url_raw( $redirect_to ) );
	exit;

}

add_action( 'admin_init', 'give_recurring_process_subscription_update', 1 );

/**
 * Handles subscription deletion.
 *
 * @access      public
 * @return      void
 */
function give_recurring_process_subscription_deletion() {

	if ( empty( $_POST['sub_id'] ) ) {
		return;
	}

	if ( empty( $_POST['give_delete_subscription'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_give_payments' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['give-recurring-update-nonce'], 'give-recurring-update' ) ) {
		wp_die( __( 'Nonce verification failed.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array(
			'response' => 403,
		) );
	}

	$subscription = new Give_Subscription( absint( $_POST['sub_id'] ) );

	delete_post_meta( $subscription->parent_payment_id, '_give_subscription_payment' );

	$subscription->delete();

	wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&give-message=deleted' ) );
	exit;

}

add_action( 'admin_init', 'give_recurring_process_subscription_deletion', 2 );


/**
 * Handles adding a manual renewal payment.
 *
 * @access      public
 * @since       1.2
 * @return      void
 */
function give_recurring_process_add_renewal_payment() {

	// Sanity checks.
	if ( empty( $_POST['sub_id'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_give_payments' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'give-recurring-add-renewal-payment' ) ) {
		wp_die( __( 'Nonce verification failed.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array(
			'response' => 403,
		) );
	}

	// Set vars from $_POST.
	$amount    = isset( $_POST['amount'] ) ? give_sanitize_amount( $_POST['amount'] ) : '0.00';
	$txn_id    = isset( $_POST['txn_id'] ) ? sanitize_text_field( $_POST['txn_id'] ) : md5( strtotime( 'NOW' ) );
	$post_date = isset( $_POST['give-payment-date'] ) ? strtotime($_POST['give-payment-date']) : 0;
	$sub_id    = isset( $_POST['sub_id'] ) ? absint( $_POST['sub_id'] ) : 0;

	// Create subscription.
	$sub = new Give_Subscription( $sub_id );

	$payment = $sub->add_payment( array(
		'amount'         => $amount,
		'transaction_id' => $txn_id,
		'post_date'      => date( 'Y-m-d H:i:s', $post_date ),
	) );

	if ( isset( $_POST['update_renewal_date'] ) ) {
		$sub->renew();
	}

	if ( $payment ) {
		$message = 'renewal-added';
	} else {
		$message = 'renewal-not-added';
	}

	wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&give-message=' . $message . '&id=' . $sub->id ) );
	exit;

}

add_action( 'give_add_renewal_payment', 'give_recurring_process_add_renewal_payment', 1 );
