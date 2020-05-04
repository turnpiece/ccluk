<?php
/**
 *  Update Subscription template
 *
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.8
 */
$subscriber = Give_Recurring_Subscriber::getSubscriber();
$subscription  = new Give_Subscription( absint( $_GET['subscription_id'] ) );

// If payment method has been updated.
$is_updated = filter_input(INPUT_GET,'updated',FILTER_SANITIZE_STRING );
if ( isset( $is_updated ) && '1' === $is_updated ) {
	echo sprintf(
		'<div class="give_notices give_errors"><p class="give_error give_notice give_success" data-dismissible="" data-dismiss-interval="5000" data-dismiss-type=""><strong>%1$s</strong> %2$s</p></div>',
		esc_html__( 'Success:', 'give-recurring' ),
		esc_html__( 'Your subscription amount has been updated.', 'give-recurring' )
	);
}

// Bail out if subscription can not be updated or gateway deactivated.
if ( ! $subscription->can_update_subscription() ) {
	Give_Notices::print_frontend_notice( __( 'Subscription can not be updated.', 'give-recurring' ), true, 'warning' );

	return false;
}

// Bail out and print notice if Subscription ID is not valid.
if ( isset( $subscription ) && empty( $subscription->id ) ) {
	Give_Notices::print_frontend_notice( __( 'Subscription ID is Invalid.', 'give-recurring' ), true, 'warning' );

	return false;
}
$action_url        = remove_query_arg( array( 'subscription_id', 'updated' ), give_get_current_page_url() );
$form_title        = give_get_meta( $subscription->parent_payment_id, '_give_payment_form_title', true );
$form_id           = absint( $subscription->form_id );
$form_currency     = give_get_currency( $form_id );
$currency_settings = give_get_currency_formatting_settings( $form_currency );
$currency_symbol   = give_currency_symbol( give_get_currency( $form_id ) );
$give_options      = give_get_settings();
$currency_position = isset( $give_options['currency_position'] ) ? $give_options['currency_position'] : 'before';

// Get Subscription renewal amount.
$renewal_amount = give_format_amount( $subscription->recurring_amount,
	array(
		'sanitize' => false,
		'currency' => $form_currency,
	)
);

// Check if currency exist.
if ( empty( $currency_settings ) ) {
	$form_html_tags = array();
}

$form_html_tags['data-currency_symbol'] = $currency_symbol;
$form_html_tags['data-currency_code']   = $form_currency;

if ( ! empty( $currency_settings ) ) {
	foreach ( $currency_settings as $key => $value ) {
		$form_html_tags["data-{$key}"] = $value;
	}
}
?>
<a href="<?php echo esc_url( $action_url ); ?>">&larr;&nbsp;<?php esc_html_e( 'Back', 'give-recurring' ); ?></a>
<div class="give-recurring-donation-main" id="give_purchase_form_wrap">
	<h3 class="give-recurring-donation-title">
		<?php
		echo sprintf(
			'%1$s <em>%2$s</em>',
			esc_html__( 'Update Subscription for', 'give-recurring' ),
			esc_html( $form_title )
		);
		?>
	</h3>
	<form name="give-recurring-form" action="<?php echo esc_url( $action_url ); ?>" class="give-form-<?php echo esc_attr( $form_id ) . '-1'; ?> give-recurring-form"
	      method="POST" id="give-form" data-gateway="<?php echo esc_attr( $subscription->gateway ); ?>"
	      data-id="<?php echo absint( $form_id ) . '-1'; ?>" <?php echo wp_kses_post( give_get_attribute_str( $form_html_tags ) ); ?>>
		<input name="give-recurring-update-gateway" type="hidden" value="<?php echo esc_attr( $subscription->gateway ); ?>" />
		<input type="hidden" name="give-form-id" value="<?php echo absint( $form_id ); ?>" />
		<input type="hidden" name="give_action" value="recurring_update_subscription_amount" />
		<input type="hidden" name="subscription_id" value="<?php echo absint( $subscription->id ); ?>" />
		<?php
		// Get the custom option amount.
		$custom_amount = give_get_meta( $form_id, '_give_custom_amount', true );

		// If custom amount enabled.
		if ( give_is_setting_enabled( $custom_amount ) ) {
			?>
			<input type="hidden" name="give-form-minimum"
			       value="<?php echo esc_attr( give_maybe_sanitize_amount( give_get_form_minimum_price( $form_id ) ) ); ?>" />
			<input type="hidden" name="give-form-maximum"
			       value="<?php echo esc_attr( give_maybe_sanitize_amount( give_get_form_maximum_price( $form_id ) ) ); ?>" />
			<?php
		}

		// Crete wp nonce field.
		wp_nonce_field( "update-subscription-{$subscription->id}", 'give_recurring_subscription_update_nonce', true, true );
		?>

		<div id="give_checkout_form_wrap">

			<div class="give-recurring-updated-method give-recurring-updated-subscription">
				<?php
				/**
				 *  Give Recurring before Update Subscription.
				 *
				 * @since 1.8
				 */
				do_action( 'give_recurring_before_subscription_update', $subscription->id );

				$currency_output = '<span class="give-currency-symbol give-currency-position-' . $currency_position . '">' . $currency_symbol . '</span>';
				?>
				<div class="give-total-wrap">
					<div class="give-donation-amount form-row-wide">
						<h3><?php esc_html_e( 'Update subscription amount', 'give-recurring' ); ?></h3>
						<?php if ( 'before' === $currency_position ) {
							echo wp_kses_post( $currency_output );
						} ?>
						<label class="give-hidden" for="give-amount"><?php esc_html_e( 'Renewal Amount:', 'give-recurring' ); ?></label>
						<input
								class="give-recurring-text-input give-amount-top"
								id="give-amount"
								name="give-amount"
								type="tel"
								placeholder=""
								value="<?php echo esc_attr( $renewal_amount ); ?>"
								autocomplete="off"
								data-renewal_amount="<?php echo esc_attr( $renewal_amount ); ?>"
						>
						<?php if ( 'after' === $currency_position ) {
							echo wp_kses_post( $currency_output );
						} ?>
					</div>
				</div>
				<?php
				/**
				 * Give Recurring after Update Subscription.
				 *
				 * @since 1.8
				 */
				do_action( 'give_recurring_after_subscription_update', $subscription->id );
				?>
			</div>
		</div>

		<div id="give_purchase_submit" class="give-submit-button-wrap give-clearfix">
			<input
					type="submit"
					name="give-recurring-update-submit"
					class="give-submit give-btn"
					id="give-recurring-update-submit"
					value="<?php echo esc_attr( __( 'Update', 'give-recurring' ) ); ?>"
					disabled
			/>
			<span class="give-loading-animation"></span>
		</div>
	</form>
</div>
