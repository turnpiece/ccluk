<?php
/**
 * Jetpack Compatibility File
 *
 * @package politics
 */

/**
 * Add theme support for Infinite Scroll.
 * See: https://jetpack.me/support/infinite-scroll/
 */
function politics_jetpack_setup() {
	add_theme_support( 'infinite-scroll', array(
		'container' => 'main',
		'render'    => 'politics_infinite_scroll_render',
		'footer'    => 'page'
	) );
} // end function politics_jetpack_setup
add_action( 'after_setup_theme', 'politics_jetpack_setup' );

/**
 * Custom render function for Infinite Scroll.
 */
function politics_infinite_scroll_render() {

	$politics_blog_style = get_theme_mod('politics_blog_style', customizer_library_get_default( 'politics_blog_style' ) );

	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/content', $politics_blog_style );
	}
} // end function politics_infinite_scroll_render

/**
 * Removing the default sharing icons so that we can
 * customize the location
 */
function politics_remove_jetpack_sharing() {
    if ( is_singular( 'post' ) || is_page() || is_home() || is_search() && function_exists( 'sharing_display' ) ) {
        remove_filter( 'the_content', 'sharing_display', 19 );
        remove_filter( 'the_excerpt', 'sharing_display', 19 );
    }
}
add_action( 'loop_start', 'politics_remove_jetpack_sharing' );
