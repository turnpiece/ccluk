<?php
/**
 * Uninstall Give - Recurring Donations
 *
 * @package     Give_Recurring
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load Give file.
include_once GIVE_PLUGIN_DIR . 'give.php';

global $wpdb;

if ( give_is_setting_enabled( give_get_option( 'uninstall_on_delete' ) ) ) {

	// Delete the Plugin Pages.
	$give_recurring_pages = array( 'subscriptions_page' );
	foreach ( $give_recurring_pages as $p ) {
		$page = give_get_option( $p, false );
		if ( $page ) {
			wp_delete_post( $page, true );
		}
	}

	// Delete the Roles.
	$give_roles = array( 'give_subscriber' );
	foreach ( $give_roles as $role ) {
		remove_role( $role );
	}

	// Remove all database tables.
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}give_subscriptions" );

	// Get all options.
	$give_option_names = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} where option_name LIKE '%%%s%%'",
			'give_recurring'
		)
	);

	if ( ! empty( $give_option_names ) ) {
		// Convert option name to transient or option name.
		$new_give_option_names = array();

		// Delete all the Plugin Options.
		foreach ( $give_option_names as $option ) {
				delete_option( $option );
		}
	}
}
