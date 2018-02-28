<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Forminator
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

/**
 * The path to the WordPress tests checkout.
 */
if ( file_exists( $_tests_dir ) ) {
	define( 'WP_TESTS_DIR', '/tmp/wordpress-tests-lib/' );
} else { // Without the "wptest" but with "trunk" subfolder...
	define( 'WP_TESTS_DIR', '/srv/www/wordpress-develop/trunk/tests/phpunit/' );
}

// Give access to tests_add_filter() function.
require_once WP_TESTS_DIR . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require 'forminator.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require WP_TESTS_DIR . '/includes/bootstrap.php';