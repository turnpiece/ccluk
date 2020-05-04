<?php
/**
 * Reset Tool Compatibility
 *
 * Provides compatibility with Give's reset functionality found in 1.5+
 *
 */

//-----------------------------------------
// Donation Forms.
//-----------------------------------------

/**
 * @param array $statuses
 *
 * @return array
 */
function give_recurring_reset_all_form_stats_add_status( $statuses ) {

	if ( ! in_array( 'give_subscription', $statuses ) ) {
		$statuses[] = 'give_subscription';
	}

	return $statuses;
}

add_filter( 'give_recount_accepted_statuses', 'give_recurring_reset_all_form_stats_add_status', 1, 1 );


//-----------------------------------------
//Donors.
//-----------------------------------------

/**
 * Allow the donor recount tool to process a subscription payment.
 *
 * @since  1.2
 *
 * @param  bool   $ret Base status for if the payment should be processed.
 * @param  object $payment WP_Post object of the payment being checked.
 *
 * @return bool             If it's an give_subscription, return true, otherwise return the supplied return.
 */
function give_recurring_should_process_payment( $ret, $payment ) {

	if ( 'give_subscription' === $payment->post_status ) {
		$ret = true;
	}

	return $ret;
}

add_filter( 'give_donor_recount_should_process_donation', 'give_recurring_should_process_payment', 10, 2 );

/**
 * Allow the donor recount tool to include give_subscription payment status.
 *
 * @since  1.2
 *
 * @param  array $payment_statuses Array of post statuses.
 *
 * @return array                   Array of post statuses with give_subscription included.
 */
function give_recurring_donor_recount_status( $payment_statuses ) {

	$payment_statuses[] = 'give_subscription';

	return $payment_statuses;

}

add_filter( 'give_recount_donors_donation_statuses', 'give_recurring_donor_recount_status', 10, 1 );

/**
 * Find any customers with subscription customer IDs
 *
 * @since  1.2
 *
 * @param  array $items Current items to remove from the reset
 *
 * @return array        The items with any subscription customer entires
 */
function give_recurring_reset_delete_sub_customer_ids( $items ) {

	global $wpdb;

	$sql      = "SELECT umeta_id FROM $wpdb->usermeta WHERE meta_key = '_give_recurring_id'";
	$meta_ids = $wpdb->get_col( $sql );

	foreach ( $meta_ids as $id ) {
		$items[] = array(
			'id'   => (int) $id,
			'type' => 'give_subscriber_id',
		);
	}

	return $items;
}

add_filter( 'give_reset_items', 'give_recurring_reset_delete_sub_customer_ids', 10, 1 );

/**
 * Isolate any subscriber Customer IDs to remove from the db on reset
 *
 * @since  1.2
 *
 * @param  string $type The type of item to remove from the initial findings
 * @param  array $item The item to remove
 *
 * @return string       The determine item type
 */
function give_recurring_reset_recurring_customer_ids( $type, $item ) {

	if ( 'give_subscriber_id' === $item['type'] ) {
		$type = $item['type'];
	}

	return $type;

}

add_filter( 'give_reset_item_type', 'give_recurring_reset_recurring_customer_ids', 10, 2 );

/**
 * Add an SQL item to the reset process for the usermeta with the given umeta_ids
 *
 * @since  1.2
 *
 * @param  array $sql An Array of SQL statements to run
 * @param  string $ids The IDs to remove for the given item type
 *
 * @return array       Returns the array of SQL statements with statements added
 */
function give_recurring_reset_customer_queries( $sql, $ids ) {

	global $wpdb;
	$sql[] = "DELETE FROM $wpdb->usermeta WHERE umeta_id IN ($ids)";

	return $sql;

}

add_filter( 'give_reset_add_queries_give_subscriber_id', 'give_recurring_reset_customer_queries', 10, 2 );


//-----------------------------------------
// Subscriptions
//-----------------------------------------

/**
 * Find all subscription IDs
 *
 * @since  1.2
 *
 * @param  array $items Current items to remove from the reset
 *
 * @return array        The items with all subscriptions
 */
function give_recurring_reset_delete_subscriptions( $items ) {

	$db = new Give_Subscriptions_DB;

	$args = array(
		'number'  => - 1,
		'orderby' => 'id',
		'order'   => 'ASC',
	);

	$subscriptions = $db->get_subscriptions( $args );

	foreach ( $subscriptions as $subscription ) {
		$items[] = array(
			'id'   => (int) $subscription->id,
			'type' => 'give_subscription',
		);
	}

	return $items;
}

add_filter( 'give_reset_items', 'give_recurring_reset_delete_subscriptions', 10, 1 );

/**
 * Isolate the subscription items during the reset process
 *
 * @since  1.2
 *
 * @param  string $type The type of item to remove from the initial findings
 * @param  array $item The item to remove
 *
 * @return string       The determine item type
 */
function give_recurring_reset_recurring_type( $type, $item ) {

	if ( 'give_subscription' === $item['type'] ) {
		$type = $item['type'];
	}

	return $type;

}

add_filter( 'give_reset_item_type', 'give_recurring_reset_recurring_type', 10, 2 );

/**
 * Add an SQL item to the reset process for the given subscription IDs
 *
 * @since  1.2
 *
 * @param  array $sql An Array of SQL statements to run
 * @param  string $ids The IDs to remove for the given item type
 *
 * @return array       Returns the array of SQL statements with subscription statement added
 */
function give_recurring_reset_queries( $sql, $ids ) {

	global $wpdb;
	$table = $wpdb->prefix . 'give_subscriptions';
	$sql[] = "DELETE FROM $table WHERE id IN ($ids)";

	return $sql;

}

add_filter( 'give_reset_add_queries_give_subscription', 'give_recurring_reset_queries', 10, 2 );

/*
 * Delete all Recurring Donation related to main Donation when main Donations is getting deleted.
 *
 * @since 1.4.1
 *
 * @param int $donation_id Donation id that is being getting deleted.
 */
function give_recurring_payment_delete( $donation_id ) {
	$payment_mode = get_post_meta( $donation_id, '_give_payment_mode', true );

	$donation_id  = intval( $donation_id );
	$payment_mode = give_clean( $payment_mode );

	// check if it's an test donation or not.
	if ( 'test' === $payment_mode && class_exists( 'Give_Subscriptions_DB' ) ) {

		// get the subscriptions from the donation id.
		$subs_db = new Give_Subscriptions_DB();
		$subs    = $subs_db->get_subscriptions( array(
			'parent_payment_id' => $donation_id,
		) );

		// check if the subscriptions data is not empty.
		if ( ! empty( $subs ) && is_object( $subs[0] ) ) {
			$subs[0]->delete();
		}
	}
}

add_action( 'give_payment_delete', 'give_recurring_payment_delete' );
