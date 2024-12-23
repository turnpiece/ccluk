<?php
/**
 *  Update Subscription Payment method template
 *
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.7
 */
$subscriber = Give_Recurring_Subscriber::getSubscriber();
$subscription  = new Give_Subscription( absint( $_GET['subscription_id'] ) );

// If payment method has been updated.
if ( isset( $_GET['updated'] ) && '1' === give_clean( $_GET['updated'] ) ) {
	echo sprintf(
		'<div class="give_notices give_errors"><p class="give_error give_notice give_success" data-dismissible="" data-dismiss-interval="5000" data-dismiss-type=""><strong>%1$s</strong> %2$s</p></div>',
		__( 'Success:', 'give-recurring' ),
		__( 'Your payment method has been updated.', 'give-recurring' )
	);
}

// Bail out if subscription can not be updated or gateway deactivated.
if ( ! $subscription->can_update() ) {
	Give_Notices::print_frontend_notice( __( 'Subscription can not be updated.', 'give-recurring' ), true, 'warning' );

	return false;
}

$action_url = remove_query_arg( array( 'subscription_id', 'updated' ), give_get_current_page_url() );
$form_title = give_get_meta( $subscription->parent_payment_id, '_give_payment_form_title', true );
$form_id    = absint( $subscription->form_id );

// Get Current CC details.
$card_details = give_recurring_get_card_details( $subscriber, $subscription );
$last_digit   = ! empty( $card_details['last_digit'] ) ? $card_details['last_digit'] : '';
$exp_month    = ! empty( $card_details['exp_month'] ) ? $card_details['exp_month'] : '';
$exp_year     = ! empty( $card_details['exp_year'] ) ? $card_details['exp_year'] : '';
$cc_type      = ! empty( $card_details['cc_type'] ) ? $card_details['cc_type'] : '';

// Set form html tags.
$form_html_tags = array(
	'data-gateway' => esc_attr( $subscription->gateway ),
	'data-id'      => esc_attr( $form_id ) . '-1'
);
$form_html_tags = apply_filters( "give_recurring_update_subscription_form_tags", (array) $form_html_tags, $subscription );
?>
<a href="<?php echo esc_url( $action_url ); ?>">&larr;&nbsp;<?php _e( 'Back', 'give-recurring' ); ?></a>
<div class="give-recurring-donation-main give-form-wrap" id="give_purchase_form_wrap">
	<h3 class="give-recurring-donation-title"><?php printf( __( 'Update Payment Method for <em>%s</em>', 'give-recurring' ), $form_title ); ?></h3>
	<form
        name="give-recurring-form"
        action="<?php echo esc_url( $action_url ); ?>"
        class="give-form give-form-<?php echo esc_attr( $form_id ) . '-1'; ?> give-recurring-form"
        method="POST"
        id="give-form"
		<?php echo give_get_attribute_str( $form_html_tags ); ?>>
		<input name="give-recurring-update-gateway" type="hidden" value="<?php echo esc_attr( $subscription->gateway ); ?>" />
		<input type="hidden" name="give-form-id" value="<?php echo absint( $form_id ); ?>" />
		<input type="hidden" name="give-form-id-prefix" value="<?php echo esc_attr( $form_id ) . '-1'; ?>" />
		<input type="hidden" name="give_action" value="recurring_update_payment" />
		<input type="hidden" name="subscription_id" value="<?php echo absint( $subscription->id ); ?>" />
		<input type="hidden" name="give_stripe_payment_method" value="" />
		<?php echo wp_nonce_field( "update-payment-{$subscription->id}", 'give_recurring_update_nonce', true, false ); ?>

		<ul id="give-gateway-radio-list" style="display:none;">
			<li class="give-gateway-option-selected">
				<input type="radio" name="payment-mode" class="give-gateway"
				       id="give-gateway-<?php echo esc_attr( $subscription->gateway ) . '-' . absint( $form_id ); ?>"
				       value="<?php echo esc_attr( $subscription->gateway ); ?>" checked>
			</li>
		</ul>

		<div id="give_checkout_form_wrap">

			<?php if ( ! empty( $cc_type ) && ! empty( $last_digit ) && ! empty( $exp_month ) && ! empty( $exp_year ) ): ?>
				<div class="give-recurring-current-method">
					<h3><?php echo __( 'Current Payment method', 'give-recurring' ); ?></h3>
					<div class="give-recurring-show-cc">
						<div class="give-recurring-cc-left">
							<?php echo sprintf( __( '<span class="give-recurring-updated-card-type %1$s"></span><span class="give-recurring-cc-type-name">%2$s ending in</span><span class="give-recurring-cc-last4">%3$s</span>', 'give-recurring' ),
								strtolower( $cc_type ),
								ucwords( $cc_type ),
								$last_digit
							);
							?>
						</div>
						<?php
						echo sprintf( __( '<div class="give-recurring-cc-right">%1$s<span class="give-recurring-card-expiration">%2$s / %3$s</span>', 'give-recurring' ),
							__( 'Expiration:', 'give-recurring' ),
							$exp_month,
							$exp_year
						);
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="give-recurring-updated-method">
				<h3><?php echo __( 'Update Payment method', 'give-recurring' ); ?></h3>
				<?php
				/**
				 *  Give Recurring before Update Payment method Form.
				 *
				 * @since 1.7
				 */
				do_action( 'give_recurring_before_update', $subscription->id );

				/**
				 *  Give Recurring Payment method Form.
				 *
				 * @param object $subscription
				 *
				 * @since 1.7
				 */
				do_action( 'give_recurring_update_payment_form', $subscription );

				/**
				 *  Give Recurring after Update Payment method Form.
				 *
				 * @since 1.7
				 */
				do_action( 'give_recurring_after_update', $subscription->id );
				?>

                <div id="give-stripe-payment-errors-<?php echo esc_attr( $form_id ); ?>"></div>
			</div>
		</div>

		<div id="give_purchase_submit" class="give-submit-button-wrap give-clearfix">
			<input type="submit" name="give-recurring-update-submit" class="give-submit give-btn" id="give-recurring-update-submit"
			       value="<?php echo esc_attr( __( 'Update Payment Method', 'give-recurring' ) ); ?>" />
			<span class="give-loading-animation"></span>
		</div>
	</form>
</div>




