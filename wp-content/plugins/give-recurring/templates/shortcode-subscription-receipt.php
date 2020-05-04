<?php
/**
 *  Give Template File for the Subscriptions section of [give_receipt]
 *
 * Place this template file within your theme directory under /my-theme/give/ - For more information see: https://givewp.com/documentation/
 *
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0
 */

global $give_receipt_args;

$payment = get_post( $give_receipt_args['id'] );
$db      = new Give_Subscriptions_DB();
$args    = array(
	'parent_payment_id' => $payment->ID,
);

$subscriptions = $db->get_subscriptions( $args );

// Sanity check: ensure this is a subscription donation.
if ( empty( $subscriptions ) ) {
	return false;
}
?>
<?php do_action( 'give_subscription_receipt_before_table', $payment ); ?>
	<table id="give-subscription-receipt" class="give-table">

		<thead>
		<?php
		/**
		 * Fires in the payment receipt shortcode, before the receipt first header item.
		 *
		 * Allows you to add new <th> elements before the receipt first header item.
		 *
		 * @since 1.3
		 *
		 * @param object $payment           The payment object.
		 * @param array  $give_receipt_args Receipt_argument.
		 */
		do_action( 'give_subscription_receipt_header_before', $payment, $give_receipt_args );
		?>
		<tr>
			<th scope="colgroup" colspan="2">
				<span class="give-receipt-thead-text"><?php esc_html_e( 'Subscription Details', 'give-recurring' ) ?></span>
			</th>
		</tr>
		<?php
		/**
		 * Fires in the subscription portion of the receipt shortcode, after the receipt last header item.
		 *
		 * Allows you to add new <th> elements after the receipt last header item.
		 *
		 * @since 1.3
		 *
		 * @param object $payment           The payment object.
		 * @param array  $give_receipt_args Receipt_argument.
		 */
		do_action( 'give_subscription_receipt_header_after', $payment, $give_receipt_args );
		?>
		</thead>


		<tbody>
		<?php
		// Loop through subscriptions.
		foreach ( $subscriptions as $subscription ) {

			// Set vars
			$title        = get_the_title( $subscription->form_id );
			$renewal_date = ! empty( $subscription->expiration ) ? date_i18n( get_option( 'date_format' ), strtotime( $subscription->expiration ) ) : __( 'N/A', 'give-recurring' );

			$interval  = ! empty( $subscription->frequency ) ? $subscription->frequency : 1;
			$frequency = give_recurring_pretty_subscription_frequency( $subscription->period, $subscription->bill_times, false, $interval );
			$sub       = new Give_Subscription( $subscription->id ); ?>

			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Subscription:', 'give-recurring' ); ?></strong></td>
				<td>
					<span class="give-subscription-billing-cycle">
						<?php
						$args = array(
							'currency_code' => give_get_payment_currency_code( $payment->ID ),
						);

						echo give_currency_filter( give_format_amount( $subscription->recurring_amount, array( 'donation_id' => $payment->ID ) ), $args ) . ' / ' . $frequency;
						?>
					</span>
					<?php if ( give_get_option( 'subscriptions_page', 0 ) && $subscription->can_update_subscription() ): ?>
						<a class="give-recurring-edit-amount" href="<?php echo esc_url( $subscription->get_edit_subscription_url() ); ?>"><?php _e( 'Edit amount', 'give-recurring' ); ?></a>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Status:', 'give-recurring' ); ?></strong></td>
				<td>
					<span class="give-subscription-status"><?php echo give_recurring_get_pretty_subscription_status( $subscription->status ); ?></span>
				</td>
			</tr>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Renewal Date:', 'give-recurring' ); ?></strong></td>
				<td><span class="give-subscription-renewal-date"><?php echo $renewal_date; ?></span></td>
			</tr>
			<tr>
				<td scope="row"><strong><?php esc_html_e( 'Progress:', 'give-recurring' ); ?></strong></td>
				<td><span class="give-subscription-times-billed"><?php echo get_times_billed_text( $sub ); ?></span>
				</td>
			</tr>

			<?php
		} // End foreach().
		?>
		</tbody>
	</table>
<?php

do_action( 'give_subscription_receipt_after_table', $payment );
