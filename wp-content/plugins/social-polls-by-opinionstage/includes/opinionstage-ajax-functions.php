<?php

// block direct access to plugin PHP files:
defined( 'ABSPATH' ) or die();

add_action( 'wp_ajax_opinionstage_ajax_toggle_flyout', 'opinionstage_ajax_toggle_flyout' );
add_action( 'wp_ajax_opinionstage_ajax_toggle_article_placement', 'opinionstage_ajax_toggle_article_placement' );
add_action( 'wp_ajax_opinionstage_ajax_toggle_sidebar_placement', 'opinionstage_ajax_toggle_sidebar_placement' );

// Toggle the flyout placement activation flag
function opinionstage_ajax_toggle_flyout() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	$os_options['fly_out_active'] = $_POST['activate'];

	update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
	wp_die('1');
}
// Toggle the article placement activation flag
function opinionstage_ajax_toggle_article_placement() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	$os_options['article_placement_active'] = $_POST['activate'];

	update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
	wp_die('1');
}
// Toggle the sidebar placement activation flag
function opinionstage_ajax_toggle_sidebar_placement() {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	$os_options['sidebar_placement_active'] = $_POST['activate'];

	update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
	wp_die('1');
}

?>
