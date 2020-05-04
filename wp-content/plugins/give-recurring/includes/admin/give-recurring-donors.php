<?php
/**
 * Recurring Donor subscription list.
 *
 * @param $customer
 */
function give_recurring_donor_subscriptions_list( $customer ) {

	$subscriber    = new Give_Recurring_Subscriber( $customer->id );
	$subscriptions = $subscriber->get_subscriptions();

	if ( ! $subscriptions ) {
		return;
	}
	?>
	<h3><?php _e( 'Subscriptions', 'give-recurring' ); ?></h3>
	<table class="wp-list-table widefat striped donations">
		<thead>
		<tr>
			<th><?php _e( 'Form', 'give-recurring' ); ?></th>
			<th><?php _e( 'Amount', 'give-recurring' ); ?></th>
			<th><?php _e( 'Actions', 'give-recurring' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $subscriptions as $subscription ) : ?>
			<tr>
				<td>
					<a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $subscription->form_id ) ); ?>"><?php echo get_the_title( $subscription->form_id ); ?></a>
				</td>
				<td>
					<?php
					printf(
					/* translators: %s: donation amount with currency symbol (i.e. $10) 2: subscription period (i.e. month) */
						__( '%1$s every %2$s', 'give-recurring' ),
						give_currency_filter( give_sanitize_amount( $subscription->amount ) ),
						$subscription->period
					);
					?>
				</td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $subscription->id ) ); ?>"><?php _e( 'View Details', 'give-recurring' ); ?></a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

add_action( 'give_customer_after_tables', 'give_recurring_donor_subscriptions_list' );


/**
 * Customizes the Donor's "Completed Donations" text.
 *
 * When you view a single donor's profile there is a stat that displays "Completed Donations";
 * this adjusts that using a filter to include the total number of subscription donations as well.
 *
 * @param $text
 * @param $donor \Give_Donor
 *
 * @return bool|mixed
 */
function give_recurring_display_donors_subscriptions( $text, $donor ) {

	$subscriber = new Give_Recurring_Subscriber( $donor->email );

	// Sanity check: Check if this donor is a subscriber & $subscriber->payment_ids
	if ( ! $subscriber->has_subscription() || empty( $subscriber->payment_ids ) ) {
		echo $text;

		return false;
	}

	$count = 0;

	foreach ( $subscriber->get_subscriptions() as $sub ) {
		$payments = $sub->get_child_payments();
		$count    += count( $payments );
	}

	if ( ! empty( $count ) ) {
		$text = $text . ', ' . $count . ' ' . _n( 'Renewal Donation', 'Renewal Donations', $count, 'give-recurring' );
		echo apply_filters( 'give_recurring_display_donors_subscriptions', $text );
	} else {
		echo $text;
	}


}

add_filter( 'give_donor_completed_donations', 'give_recurring_display_donors_subscriptions', 10, 2 );

/**
 * Add Subscription to "Donations" columns
 *
 * Within the Donations > Donors list table there is a "Donations" column that needs to properly count
 * `give_subscription` status payments
 *
 * @param $value
 * @param $item_id
 *
 * @return mixed|string
 */
function give_recurring_add_subscriptions_to_donations_column( $value, $item_id ) {

	$subscriber = new Give_Recurring_Subscriber( $item_id, true );

	//Sanity check: Non-subscriber
	if ( $subscriber->id == 0 ) {
		return $value;
	}

	$subscription_payments = count( $subscriber->get_subscriptions() );
	$donor                 = new Give_Donor( $item_id, true );

	$value = '<a href="' .
	         admin_url( '/edit.php?post_type=give_forms&page=give-payment-history&user=' . urlencode( $donor->email )
	         ) . '">' . ( $donor->purchase_count + $subscription_payments ) . '</a>';

	return apply_filters( 'add_subscriptions_num_purchases', $value );

}

add_filter( 'give_report_column_num_purchases', 'give_recurring_add_subscriptions_to_donations_column', 10, 2 );

/**
 * Cancels subscriptions and deletes them when a donor is deleted.
 *
 * @since  1.2
 *
 * @param  int  $customer_id ID of the donor being deleted.
 * @param  bool $confirm     Whether site admin has confirmed they wish to delete the donor.
 * @param  bool $remove_data Whether associated data should be deleted.
 *
 * @return void
 */
function give_recurring_delete_donor_and_subscriptions( $customer_id, $confirm, $remove_data ) {

	if ( empty( $customer_id ) || ! $customer_id > 0 ) {
		return;
	}

	$subscriber       = new Give_Recurring_Subscriber( $customer_id );
	$subscriptions    = $subscriber->get_subscriptions();
	$subscriptions_db = new Give_Subscriptions_DB();

	if ( ! is_array( $subscriptions ) ) {
		return;
	}

	foreach ( $subscriptions as $sub ) {

		if ( $sub->can_cancel() ) {

			// Attempt to cancel the subscription in the gateway.
			$gateway = Give_Recurring()->get_gateway_class( $sub->gateway );

			if ( $gateway ) {

				$gateway_obj = new $gateway;
				$gateway_obj->cancel( $sub, true );

			}

		}

		if ( $remove_data ) {

			// Delete the subscription from the database.
			$subscriptions_db->delete( $sub->id );

		}

	}

}


/**
 * Change action name from give_pre_delete_customer to give_pre_delete_donor
 * As this action is being deprecated in give core plugin since version 1.7
 *
 * @since 1.3.2
 */
add_action( 'give_pre_delete_donor', 'give_recurring_delete_donor_and_subscriptions', 10, 3 );


/**
 * Adds a "Subscriptions" column to the Donors listing table.
 *
 * @param $columns
 *
 * @return mixed
 */
function give_recurring_donors_subscriptions_column( $columns ) {

	$columns['subscriptions'] = esc_html__( 'Subscriptions', 'give-recurring' );

	return $columns;
}

add_filter( 'give_report_donor_columns', 'give_recurring_donors_subscriptions_column', 10, 1 );

/**
 * Makes the "Subscriptions" column sortable.
 *
 * @param $columns
 *
 * @return mixed
 */
function give_recurring_donors_sortable_subscriptions_column( $columns ) {

	$columns['subscriptions'] = array( 'subscriptions', true );


	return $columns;
}

add_filter( 'give_report_sortable_donor_columns', 'give_recurring_donors_sortable_subscriptions_column', 10, 1 );

/**
 * Outputs the column data for "Subscriptions" found under WP-Admin > Donations > Donors.
 *
 * @param $value
 * @param $id
 */
function give_recurring_donors_subscriptions_column_data( $value, $id ) {

	$subscriber = new Give_Recurring_Subscriber( $id );

	if ( $subscriber->has_subscription() ) {

		$subscriptions_db   = new Give_Subscriptions_DB();
		$subscription_count = $subscriptions_db->count( array( 'customer_id' => $id ) );
		$customer           = new Give_Donor( $id );

		echo '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&s=' ) . urlencode( $customer->email ) . '">' . $subscription_count . '</a>';

	} else {
		echo '0';
	}

}

add_filter( 'give_report_column_subscriptions', 'give_recurring_donors_subscriptions_column_data', 10, 2 );


/**
 * Add the sortable functionality to the "Subscriptions" column.
 *
 * @param $data
 *
 * @return array
 */
function give_recurring_donors_subscriptions_column_sorting( $data ) {

	// Sorting the Subscriptions column.
	if (
		isset( $_GET['orderby'] )
		&& 'subscriptions' === $_GET['orderby']
	) {

		$data = array();

		// Get donor query.
		$customers        = Give()->customers->get_customers();
		$subscriptions_db = new Give_Subscriptions_DB();

		if ( $customers ) {

			foreach ( $customers as $customer ) {

				$user_id = ! empty( $customer->user_id ) ? intval( $customer->user_id ) : 0;

				$data[] = array(
					'id'            => $customer->id,
					'user_id'       => $user_id,
					'name'          => $customer->name,
					'email'         => $customer->email,
					'num_donations' => $customer->purchase_count,
					'amount_spent'  => $customer->purchase_value,
					'date_created'  => $customer->date_created,
					'subscriptions' => $subscriptions_db->count( array( 'customer_id' => $customer->id ) ),
				);

			}


		}

		usort( $data, 'give_recurring_donors_subscriptions_column_usort' );

		return $data;


	} // endif.

	// Always return data.
	return $data;
}

add_filter( 'give_donors_column_query_data', 'give_recurring_donors_subscriptions_column_sorting', 10, 1 );

/**
 * usort the "Subscriptions" column data.
 *
 * Used only by give_recurring_donors_subscriptions_column_sorting() function above.
 *
 * @param $a
 * @param $b
 *
 * @return mixed
 */
function give_recurring_donors_subscriptions_column_usort( $a, $b ) {

	// Asc or descending?
	if (
		isset( $_GET['order'] )
		&& 'asc' === $_GET['order']
	) {
		return $a['subscriptions'] - $b['subscriptions'];
	}

	return $b['subscriptions'] - $a['subscriptions'];

}