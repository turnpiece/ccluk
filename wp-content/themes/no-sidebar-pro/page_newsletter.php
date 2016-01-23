<?php

/**
 * This file adds the Newsletter page template to the No Sidebar Pro Theme.
 *
 * @author StudioPress
 * @package No Sidebar Pro Theme
 * @subpackage Customizations
 */
	
/*
Template Name: Newsletter
*/

//* Enqueue full screen script
add_action( 'wp_enqueue_scripts', 'ns_full_screen_script' );
function ns_full_screen_script() {

	wp_enqueue_script( 'ns-full-screen', get_bloginfo( 'stylesheet_directory' ) . '/js/full-screen.js', array( 'jquery' ), '1.0.0' );

}

//* Add newsletter body class to the head
add_filter( 'body_class', 'ns_add_body_class' );
function ns_add_body_class( $classes ) {

	$classes[] = 'ns-newsletter';
	return $classes;

}

//* Hook newsletter widget area after site header
add_action( 'genesis_loop', 'ns_newsletter_page_widgets' );
function ns_newsletter_page_widgets() {

	echo '<div class="full-screen"><div class="widget-area">';

	genesis_widget_area( 'newsletter-signup', array(
		'before' => '<div class="newsletter-signup"><div class="wrap">',
		'after'  => '</div></div>',
	) );

	echo '</div></div>';

}

//* Remove the default Genesis loop
remove_action( 'genesis_loop', 'genesis_do_loop' );

//* Run the Genesis function
genesis();
