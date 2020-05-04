<?php

/**
 * This class is responsible for exporting all the subscriptions
 * related donation data.
 */
class Give_Export_Subscriptions_History {


	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_action( 'give_export_donation_fields', array( $this, 'add_subscriptions_only_radios' ), 9, 1 );
		add_action( 'give_export_donations_form_data', array( $this, 'check_subscription_only_radio' ) );
	}


	/**
	 * Checks if 'subscription only' radio button is checked.
	 *
	 * @param array $form_data Contains the submitted form data.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function check_subscription_only_radio( $form_data ) {
		if ( 'enabled' === $form_data['subscription-payments-only'] ) {
			add_filter( 'give_export_donations_donation_query_args', array( $this, 'get_subscription_related_payments' ) );
		}
	}


	/**
	 * Adds 'Subscription Only' radio button.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function add_subscriptions_only_radios() {
		?>
		<tr>
			<td scope="row" class="row-title">
				<label
					for="give-export-donations-status"><?php _e( 'Subscriptions related payments only:', 'give-recurring' ); ?></label>
			</td>
			<td>
				<label for="subscription-payments-only-enabled" class="subscription-payments-only">
					<input type="radio" id="subscription-payments-only-enabled" name="subscription-payments-only" value="enabled">
					<?php _e( 'Enabled', 'give-recurring' ); ?>
				</label>

				<label for="subscription-payments-only-disabled" class="subscription-payments-only">
					<input type="radio" id="subscription-payments-only-disabled" name="subscription-payments-only" value="disabled" checked>
					<?php _e( 'Disabled', 'give-recurring' ); ?>
				</label>
			</td>
		</tr>
		<?php
	}


	/**
	 * Uses meta query to get all the subscription-related payments.
	 *
	 * @param array $args Payment Query arguments.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function get_subscription_related_payments( $args ) {
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key' => 'subscription_id',
			),
			array(
				'key'     => '_give_subscription_payment',
				'value'   => 1,
				'compare' => 'LIKE',
			),
		);

		return $args;
	}
}

new Give_Export_Subscriptions_History();
