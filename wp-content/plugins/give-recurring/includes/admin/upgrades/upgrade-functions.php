<?php
/**
 * Upgrade Functions
 *
 * @package     Give-Recurring-Donations
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 *
 * NOTICE: When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
 * /includes/install.php @ Appox Line 156
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display Upgrade Notices
 *
 * @param Give_Updates $give_updates Object of Give Updates Class.
 *
 * @since 1.4
 *
 * @return void
 */
function give_recurring_show_upgrade_notices( $give_updates ) {

	// v1.2 Upgrades.
	$give_updates->register(
		array(
			'id'       => 'give_recurring_v12_upgraded',
			'version'  => '1.2',
			'callback' => 'give_recurring_v12_upgraded_callback',
		)
	);
	// v1.5.3 Upgrades.
	$give_updates->register(
		array(
			'id'       => 'give_recurring_v153_update_donor_count',
			'version'  => '1.5.3',
			'callback' => 'give_recurring_v153_update_donor_count_callback',
		)
	);

	$give_updates->register(
		array(
			'id'       => 'give_recurring_v153_add_db_notes_column',
			'version'  => '1.5.3',
			'callback' => 'give_recurring_v153_add_db_notes_column_callback',
		)
	);

	$give_updates->register(
		array(
			'id'       => 'give_recurring_v153_create_log_type_metadata',
			'version'  => '1.5.4',
			'callback' => 'give_recurring_v153_create_log_type_metadata_callback',
		)
	);

	$give_updates->register(
		array(
			'id'       => 'give_recurring_v160_add_db_frequency_column',
			'version'  => '1.6',
			'callback' => 'give_recurring_v160_add_db_frequency_column_callback',
		)
	);

	$give_updates->register(
		array(
			'id'       => 'give_recurring_v170_sanitize_db_amount',
			'version'  => '1.7',
			'callback' => 'give_recurring_v170_sanitize_db_amount_callback',
		)
	);

	$give_updates->register(
		array(
			'id'       => 'give_recurring_v172_renewal_payment_level',
			'version'  => '1.7.2',
			'callback' => 'give_recurring_v172_renewal_payment_level_callback',
		)
	);

}

add_action( 'give_register_updates', 'give_recurring_show_upgrade_notices' );


/**
 * Perform automatic database upgrades when necessary.
 *
 * @since 1.5.3
 * @return void
 */
function give_recurring_do_automatic_upgrades() {
	$give_recurring_version = preg_replace( '/[^0-9.].*/', '', get_option( 'give_recurring_version' ) );

	// Is Fresh install?
	if ( ! $give_recurring_version ) {
		$give_recurring_version = '1.0.0';
	}

	if ( version_compare( $give_recurring_version, GIVE_RECURRING_VERSION, '<' ) ) {
		update_option(
			'give_recurring_version',
			preg_replace( '/[^0-9.].*/', '',
				GIVE_RECURRING_VERSION
			)
		);
	}

	switch ( true ) {

		case version_compare( $give_recurring_version, '1.5.3', '<' ) :
			give_recurring_v153_add_db_notes_column_callback();
			break;

		case version_compare( $give_recurring_version, '1.6', '<' ) :
			give_recurring_v160_add_db_frequency_column_callback();

		case version_compare( $give_recurring_version, '1.8.2', '<' ) :
			give_recurring_v182_alter_amount_column_type_callback();

		case version_compare( $give_recurring_version, '1.8.4', '<' ) :
		case version_compare( $give_recurring_version, '1.8.7', '<' ) :
			give_recurring_v184_alter_amount_column_type_callback();
	}
}

add_action( 'admin_init', 'give_recurring_do_automatic_upgrades', 0 );
add_action( 'give_recurring_install_complete', 'give_recurring_do_automatic_upgrades', 0 );


/**
 * Add the notes column to give_subscriptions db.
 *
 * @since 1.2
 */
function give_recurring_v12_upgraded_callback() {

	global $wpdb;

	$column = $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
		DB_NAME, $wpdb->prefix . 'give_subscriptions', 'transaction_id'
	) );

	if ( empty( $column ) ) {

		// Add missing column.
		$wpdb->query( $wpdb->prepare(
			'ALTER TABLE %1$s ADD %2$s varchar(60) NOT NULL', $wpdb->prefix . 'give_subscriptions', 'transaction_id'
		) );

	} else {
		// The Update Ran.
		give_update_option( 'recurring_v12_upgraded', true ); // Legacy
		give_set_upgrade_complete( 'give_recurring_v12_upgraded' );
	}

}


/**
 * Update Donation Count and Amount of all donors.
 *
 * @since   1.4
 * @updated 1.5.3 - Due to not running correctly for existing installs.
 *
 * @return void
 */
function give_recurring_v153_update_donor_count_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();
	$offset       = 1 === $give_updates->step ? 0 : $give_updates->step * 20;

	// Form Query.
	$donors = Give()->donors->get_donors( array(
			'number' => 20,
			'offset' => $offset,
		)
	);

	if ( ! empty( $donors ) ) {
		$give_updates->set_percentage( Give()->donors->count(), ( $give_updates->step * 20 ) );

		/* @var Object $donor */
		foreach ( $donors as $donor ) {

			// Reset Purchase Count and Value.
			$purchase_count = $purchase_value = 0;

			// Split Donation Ids of particular donor.
			$payment_ids = explode( ',', $donor->payment_ids );

			// Loop through payment ids.
			foreach ( $payment_ids as $payment_id ) {

				// Get Payment Details.
				$payment = new Give_Payment( $payment_id );

				// Proceed only if payment is completed or renewal.
				if ( 'publish' === $payment->post_status || 'give_subscription' === $payment->post_status ) {
					$purchase_count ++;
					$purchase_value += $payment->total;
				}

			}

			// Update Purchase Count and Value for specific donors.
			$args = array(
				'purchase_count' => $purchase_count,
				'purchase_value' => give_sanitize_amount_for_db( $purchase_value ),
			);
			Give()->donors->update( $donor->id, $args );
		}
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'give_recurring_v153_update_donor_count' );
	}
}

/**
 * Add the notes column to give_subscriptions db.
 *
 * @since   1.4
 *
 * @updated 1.5.3 - Due to not running correctly for existing installs.
 * See: https://github.com/impres-org/give-recurring-donations/issues/484
 */
function give_recurring_v153_add_db_notes_column_callback() {

	$completed = give_has_upgrade_completed( 'give_recurring_v153_add_db_notes_column' );

	if ( $completed ) {
		return;
	}

	global $wpdb;

	$column = $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
		DB_NAME, $wpdb->prefix . 'give_subscriptions', 'notes'
	) );

	if ( empty( $column ) ) {

		// Add missing column.
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}give_subscriptions ADD notes longtext NOT NULL" );

	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'give_recurring_v153_add_db_notes_column' );
	}

}

/**
 * Add the frequency column to give_subscriptions db.
 *
 * @since 1.6.0
 *
 */
function give_recurring_v160_add_db_frequency_column_callback() {

	$completed = give_has_upgrade_completed( 'give_recurring_v160_add_db_frequency_column' );

	if ( $completed ) {
		return;
	}

	global $wpdb;

	$column = $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
		DB_NAME, $wpdb->prefix . 'give_subscriptions', 'frequency'
	) );

	if ( empty( $column ) ) {

		// Add frequency column.
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}give_subscriptions ADD frequency bigint(20) DEFAULT 1 NOT NULL AFTER period" );

	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'give_recurring_v160_add_db_frequency_column' );
	}

}

/**
 * Remove give_log_type taxonomy from log and create log meta.
 *
 * @since 1.5.4
 */
function give_recurring_v153_create_log_type_metadata_callback() {
	$give_updates = Give_Updates::get_instance();

	// form query
	$logs = new WP_Query( array(
			'paged'          => $give_updates->step,
			'order'          => 'DESC',
			'post_type'      => 'give_recur_email_log',
			'post_status'    => 'any',
			'posts_per_page' => 100,
		)
	);

	if ( $logs->have_posts() ) {
		$give_updates->set_percentage( $logs->found_posts, $give_updates->step * 100 );

		while ( $logs->have_posts() ) {
			$logs->the_post();

			$term      = get_the_terms( get_the_ID(), 'give_log_type' );
			$term      = ! is_wp_error( $term ) && ! empty( $term ) ? $term[0] : array();
			$term_name = ! empty( $term ) ? $term->slug : '';

			if ( empty( $term_name ) ) {
				continue;
			}

			give_update_meta( get_the_ID(), '_log_type', $term_name );
			wp_remove_object_terms( get_the_ID(), $term_name, 'give_log_type' );
		}// End while().

		wp_reset_postdata();
	} else {
		// No more forms found, finish up.
		give_set_upgrade_complete( 'give_recurring_v153_create_log_type_metadata' );
	}
}

/**
 * Upgrade for sanitize db amount for the initial_amount,recurring_amount.
 *
 * @since 1.7
 */
function give_recurring_v170_sanitize_db_amount_callback() {
	global $wpdb;

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	$subscription_table_name = $wpdb->prefix . 'give_subscriptions';

	$subscriptions = $wpdb->get_results(
		$wpdb->prepare( "
		SELECT id, initial_amount, recurring_amount
		FROM {$subscription_table_name}
		LIMIT 20
		OFFSET %d
		", array(
			$give_updates->get_offset( 20 ),
		) )
	);

	$subscription_db = new Give_Subscriptions_DB();

	if ( ! empty( $subscriptions ) ) {
		foreach ( $subscriptions as $subscription ) {
			$subscription_id  = $subscription->id;
			$initial_amount   = give_sanitize_amount_for_db( $subscription->initial_amount );
			$recurring_amount = give_sanitize_amount_for_db( $subscription->recurring_amount );

			$subscription_db->update(
				$subscription_id,
				array(
					'initial_amount'   => $initial_amount,
					'recurring_amount' => $recurring_amount,
				)
			);
		}
	} else {

		// Update completed.
		give_set_upgrade_complete( 'give_recurring_v170_sanitize_db_amount' );
	}

}

/**
 * Update renewal payment's payment level.
 *
 * @since  1.7.2
 * @return void
 */
function give_recurring_v172_renewal_payment_level_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// Subscription query.
	$subscriptions = new WP_Query( array(
			'paged'          => $give_updates->step,
			'post_status'    => array( 'give_subscription' ),
			'order'          => 'ASC',
			'post_type'      => 'give_payment',
			'posts_per_page' => 20,
		)
	);

	if ( $subscriptions->have_posts() ) {
		$give_updates->set_percentage( $subscriptions->found_posts, ( $give_updates->step * 20 ) );

		while ( $subscriptions->have_posts() ) {
			$subscriptions->the_post();

			$price_id = give_get_meta( get_the_ID(), '_give_payment_price_id', true );

			if ( empty( $price_id ) ) {
				$parent_id = wp_get_post_parent_id( get_the_ID() );
				$price_id  = give_get_meta( $parent_id, '_give_payment_price_id', true );

				give_update_meta( get_the_ID(), '_give_payment_price_id', $price_id );
			}

		}// End while().

		wp_reset_postdata();

	} else {
		// No more forms found, finish up.
		give_set_upgrade_complete( 'give_recurring_v172_renewal_payment_level' );
	}
}

/**
 * Fix the "recurring_amount" and "initial_amount" columns from incorrectly formatting EUR and other currencies.
 *
 * https://github.com/impress-org/give-recurring-donations/issues/803
 *
 * @since 1.8.2
 */
function give_recurring_v182_alter_amount_column_type_callback() {

	$completed = give_has_upgrade_completed( 'give_recurring_v182_alter_amount_column_type' );

	if ( $completed ) {
		return;
	}

	global $wpdb;

	$column = $wpdb->get_row( $wpdb->prepare(
		'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
		DB_NAME, $wpdb->prefix . 'give_subscriptions', 'recurring_amount'
	) );

	// If column is not decimal proceed with changing.
	if ( 'decimal' !== strtolower( $column->DATA_TYPE ) ) {

		// Alter columns.
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}give_subscriptions CHANGE `recurring_amount` `recurring_amount` DECIMAL(18,8) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}give_subscriptions CHANGE `initial_amount` `initial_amount` DECIMAL(18,8) NOT NULL" );

	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'give_recurring_v182_alter_amount_column_type' );
	}

}


/**
 * Update the "recurring_amount" and "initial_amount" columns to be DECIMAL type for better sorting.
 * @see https://github.com/impress-org/give-recurring-donations/issues/803
 *
 * @since 1.8.4
 */
function give_recurring_v184_alter_amount_column_type_callback() {
	global $wpdb;

	$completed = give_has_upgrade_completed( 'give_recurring_v184_alter_amount_column_type' );

	if ( $completed ) {
		return;
	}

	// Alter columns.
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}give_subscriptions CHANGE `recurring_amount` `recurring_amount` DECIMAL(18,10) NOT NULL" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}give_subscriptions CHANGE `initial_amount` `initial_amount` DECIMAL(18,10) NOT NULL" );

	// The Update Ran.
	give_set_upgrade_complete( 'give_recurring_v184_alter_amount_column_type' );

}