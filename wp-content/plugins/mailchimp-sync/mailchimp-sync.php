<?php
/*
Plugin Name: MailChimp User Sync
Plugin URI: https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-sync&utm_campaign=plugins-page
Description: Synchronize your WordPress Users with a MailChimp list.
Version: 1.7.3
Author: ibericode
Author URI: https://ibericode.com/
Text Domain: mailchimp-sync
Domain Path: /languages
License: GPL v3

MailChimp Sync
Copyright (C) 2015-2018, Danny van Kooten, hi@dannyvankooten.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/



// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}



/**
 * Load the MailChimp Sync plugin
 *
 * Only runs when PHP is at version 5.3 or higher
 *
 * @ignore
 */
function _load_mailchimp_sync() {

	define( 'MAILCHIMP_SYNC_FILE', __FILE__ );
	define( 'MAILCHIMP_SYNC_DIR', __DIR__ );
	define( 'MAILCHIMP_SYNC_VERSION', '1.7.3' );

	// Test whether dependencies were met
	$ready = include dirname( __FILE__ )  .'/dependencies.php';
	if( ! $ready ) {
		return;
	}

	// Load PHP 5.3+ bootstrapper
	include dirname( __FILE__ ) . '/bootstrap.php';


}

// start with PHP, which should be at least v5.3
if( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	require_once dirname( __FILE__ ) . '/php-backwards-compatibility.php';
} else {
	add_action( 'plugins_loaded', '_load_mailchimp_sync', 30 );
	register_activation_hook( __FILE__, 'mc4wp_sync_setup_schedule');
	register_deactivation_hook( __FILE__, 'mc4wp_sync_clear_schedule' );
}

/**
 * Sets up the schedule to run MailChimp User Sync hourly
 *
 * @hooked plugin activation
 */
function mc4wp_sync_setup_schedule() {
	if( wp_next_scheduled( 'mailchimp_user_sync_run' ) ) {
		return;
	}

	wp_schedule_event( time() + 30, 'hourly', 'mailchimp_user_sync_run' );
}

/**
 * Clears the schedule to run MailChimp User Sync every hour
 *
 * @hooked plugin deactivation
 */
function mc4wp_sync_clear_schedule() {
	wp_clear_scheduled_hook( 'mailchimp_user_sync_run' );
}

