<?php
/**
 * This file adds the Front Page to the No Sidebar Pro Theme.
 *
 * @author StudioPress
 * @package No Sidebar
 * @subpackage Customizations
 */
 
 add_action( 'genesis_meta', 'ns_front_page_genesis_meta' );
/**
 * Setup homepage posts grid.
 *
 */
function ns_front_page_genesis_meta() {

	if ( 'posts' == get_option( 'show_on_front' ) ) {

		//* Remove breadcrumbs
		remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

		//* Remove entry content elements
		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
		remove_action( 'genesis_entry_content', 'genesis_do_post_content_nav', 12 );
		remove_action( 'genesis_entry_content', 'genesis_do_post_permalink', 14 );

		//* Remove the entry meta in the entry footer
		remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
		remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
		remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );
		
		//* Remove No Sidebar featured image
		remove_action( 'genesis_entry_header', 'ns_featured_image', 1 );
		
		//* Add post classes
		add_filter( 'post_class', 'ns_post_class' );
		
		//* Add first-page body class
		add_filter( 'body_class', 'ns_body_class' );
		
		//* Add featured image above the entry content
		add_action( 'genesis_entry_header', 'ns_front_featured_image', 4 );

	}

}

//* Enqueue full screen script
add_action( 'wp_enqueue_scripts', 'ns_full_screen_script' );
function ns_full_screen_script() {

	if ( is_active_sidebar( 'welcome-message' ) ) {

		wp_enqueue_script( 'ns-full-screen', get_stylesheet_directory_uri() . '/js/full-screen.js', array( 'jquery' ), '1.0.0' );

	}

}

//* Hook welcome message widget area after site header
add_action( 'genesis_after_header', 'ns_welcome_message' );
function ns_welcome_message() {

	if ( get_query_var( 'paged' ) >= 2 )
		return;

	echo '<div class="full-screen"><div class="widget-area">';

	echo '<h2 class="screen-reader-text">' . __( 'Welcome Content', 'no-sidebar' ) . '</h2>';

	genesis_widget_area( 'welcome-message', array(
		'before' => '<div class="welcome-message"><div class="wrap">',
		'after'  => '</div></div>',
	) );

	echo '</div></div>';

}

function ns_post_class( $classes ) {

	global $wp_query;

	$current_page = is_paged() ? get_query_var('paged') : 1;

	$post_counter = $wp_query->current_post;

	if ( 0 == $post_counter && 1 == $current_page ) {
		$classes[] = 'first-featured';
	}

	if ( ( $post_counter & 1 ) && 1 == $current_page ) {
		$classes[] = 'row';
	} elseif ( ( $post_counter % 2 == 0 ) && 1 !== $current_page ) {
		$classes[] = 'row';
	}

	if ( ( $wp_query->current_post + 1 ) == $wp_query->post_count ) {
		$classes[] = 'last';
	}

	return $classes;

}

function ns_body_class( $classes ) {

	$current_page = is_paged() ? get_query_var('paged') : 1;

	if ( 1 == $current_page ) {
		$classes[] = 'first-page';
	}

	$classes[] = 'front-page';

	return $classes;

}

function ns_front_featured_image() {

	if ( $image = genesis_get_image( array( 'format' => 'url', 'size' => 'ns-featured', ) ) ) {

		printf( '<a class="ns-featured-image" href="' . get_permalink() . '" style="background-image: url(%s)"></a>', $image );

	}

}

//* Run the Genesis function
genesis();
