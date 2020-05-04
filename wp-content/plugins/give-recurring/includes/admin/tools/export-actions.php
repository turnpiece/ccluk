<?php
/**
 * Export Actions
 *
 * @package     Give-Recurring
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function will help include the export subscriptions file.
 *
 * @since 1.8.3
 *
 * @param string $class Slug of class to include.
 *
 * @return void
 */
function give_include_subscriptions_batch_processor( $class ) {

	if ( 'Give_Subscriptions_Renewals_Export' === $class ) {
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/tools/class-give-export-subscriptions.php';
	}
}

add_action( 'give_batch_export_class_include', 'give_include_subscriptions_batch_processor', 10, 1 );
