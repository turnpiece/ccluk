<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
include_once( get_stylesheet_directory() . '/lib/theme-defaults.php' );

//* Set Localization (do not remove)
load_child_theme_textdomain( 'no-sidebar', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'no-sidebar' ) );

//* Add Image upload and Color select to WordPress Theme Customizer
require_once( get_stylesheet_directory() . '/lib/customize.php' );

//* Include Customizer CSS
include_once( get_stylesheet_directory() . '/lib/output.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'No Sidebar Pro' );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/no-sidebar/' );
define( 'CHILD_THEME_VERSION', '1.0.1' );

//* Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'ns_scripts_styles' );
function ns_scripts_styles() {

	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Lato:400,400italic,700|Oswald:300|Playfair+Display:400,400italic,700', array(), CHILD_THEME_VERSION );
	wp_enqueue_style( 'ionicons', '//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css', array(), CHILD_THEME_VERSION );

	wp_enqueue_script( 'ns-responsive-menu', get_stylesheet_directory_uri() . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0', true );
	$output = array(
		'mainMenu' => __( 'Menu', 'no-sidebar' ),
		'subMenu'  => __( 'Menu', 'no-sidebar' ),
	);
	wp_enqueue_script( 'ns-search-box', get_stylesheet_directory_uri() . '/js/search-box.js', array( 'jquery' ), '1.0.0', true );
	wp_localize_script( 'ns-responsive-menu', 'NoSidebarL10n', $output );

}

//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

//* Add accessibility support
add_theme_support( 'genesis-accessibility', array( '404-page', 'drop-down-menu', 'headings', 'search-form', 'skip-links' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'width'           => 600,
	'height'          => 140,
	'header-selector' => '.site-title a',
	'header-text'     => false,
	'flex-height'     => true,
) );

//* Add support for custom background
add_theme_support( 'custom-background' );

//* Rename primary and secondary navigation menus
add_theme_support ( 'genesis-menus' , array ( 'primary' => __( 'Header Menu', 'no-sidebar' ), 'secondary' => __( 'Footer Menu', 'no-sidebar' ) ) );

//* Remove output of primary navigation right extras
remove_filter( 'genesis_nav_items', 'genesis_nav_right', 10, 2 );
remove_filter( 'wp_nav_menu_items', 'genesis_nav_right', 10, 2 );

//* Remove navigation meta box
add_action( 'genesis_theme_settings_metaboxes', 'ns_remove_genesis_metaboxes' );
function ns_remove_genesis_metaboxes( $_genesis_theme_settings_pagehook ) {

	remove_meta_box( 'genesis-theme-settings-nav', $_genesis_theme_settings_pagehook, 'main' );

}

//* Amend content archive toggles
add_filter( 'genesis_toggles', 'ns_amend_toggles' );
function ns_amend_toggles( $toggles ) {

	$toggles['content_archive_thumbnail'][2] = array_merge( (array) $toggles['content_archive_thumbnail'][2], array( '' ) );
	
	return $toggles;

}

//* Remove header right widget area
unregister_sidebar( 'header-right' );

//* Reposition primary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_header', 'genesis_do_nav', 12 );

//* Reposition the secondary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_footer', 'genesis_do_subnav', 12 );

//* Reduce the secondary navigation menu to one level depth
add_filter( 'wp_nav_menu_args', 'ns_secondary_menu_args' );
function ns_secondary_menu_args( $args ){

	if( 'secondary' != $args['theme_location'] )
	return $args;

	$args['depth'] = 1;
	return $args;

}

//* Add search form to site header
add_action( 'genesis_header', 'ns_search', 13 );
function ns_search() {

	get_search_form();

}

//* Customize search form input box text
add_filter( 'genesis_search_text', 'ns_search_input_text' );
function ns_search_input_text( $text ) {

	return esc_attr( 'Search' );

}

//* Add author gravatar before post meta
add_action( 'genesis_entry_header', 'ns_entry_gravatar' , 11 );
function ns_entry_gravatar() {

	if ( ! is_page() ) {

		echo get_avatar( get_the_author_meta( 'email' ), 96 );

	}

}

//* Customize the entry meta in the entry header
add_filter( 'genesis_post_info', 'ns_entry_meta_header' );
function ns_entry_meta_header($post_info) {

	$post_info = '<span class="by">by</span> [post_author_posts_link] &middot; [post_date format="M j, Y"] [post_edit]';
	return $post_info;

}

//* Add featured images
add_image_size( 'ns-featured', 1024, 576, TRUE );
add_image_size( 'ns-single', 1024, 400, TRUE );

//* Remove sidebars
unregister_sidebar( 'sidebar' );
unregister_sidebar( 'sidebar-alt' );

//* Remove site layouts
genesis_unregister_layout( 'content-sidebar' );
genesis_unregister_layout( 'sidebar-content' );
genesis_unregister_layout( 'content-sidebar-sidebar' );
genesis_unregister_layout( 'sidebar-content-sidebar' );
genesis_unregister_layout( 'sidebar-sidebar-content' );

//* Force full-width-content layout setting
add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );

//* Unregister Genesis widgets
add_action( 'widgets_init', 'ns_unregister_genesis_widgets', 20 );
function ns_unregister_genesis_widgets() {

	unregister_widget( 'Genesis_Featured_Page' );
	unregister_widget( 'Genesis_Featured_Post' );
	unregister_widget( 'Genesis_User_Profile_Widget' );

}

//* Remove layout section from Theme Customizer
add_action( 'customize_register', 'ns_customize_register', 16 );
function ns_customize_register( $wp_customize ) {

	$wp_customize->remove_control( 'genesis_image_alignment' );
	$wp_customize->remove_control( 'genesis_image_size' );
	$wp_customize->remove_section( 'genesis_layout' );

}

//* Remove default post image
remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );

//* Add featured image before loop
add_action( 'genesis_entry_header', 'ns_featured_image', 1 );
function ns_featured_image() {

	if ( is_singular() || ! genesis_get_option( 'content_archive_thumbnail' ) ) {
		return;
	}
	
	if ( $image = genesis_get_image( array( 'format' => 'url', 'size' => 'ns-single' ) ) ) {
		printf( '<div class="featured-image"><a href="%s" rel="bookmark"><img src="%s" alt="%s" /></a></div>', get_permalink(), $image, the_title_attribute( 'echo=0' ) );
	}
	
}

//* Add screen reader class to archive description
add_filter( 'genesis_attr_author-archive-description', 'genesis_attributes_screen_reader_class' );

//* Customize the content limit more markup
add_filter( 'get_the_content_limit', 'ns_content_limit_read_more_markup', 10, 3 );
function ns_content_limit_read_more_markup( $output, $content, $link ) {	
	
	$output = sprintf( '<p>%s &#x02026;</p><p class="more-link-wrap">%s</p>', $content, str_replace( '&#x02026;', '', $link ) );

	return $output;

}

//* Modify size of the Gravatar in the author box
add_filter( 'genesis_author_box_gravatar_size', 'ns_author_box_gravatar' );
function ns_author_box_gravatar( $size ) {

	return 160;

}

//* Modify size of the Gravatar in the entry comments
add_filter( 'genesis_comment_list_args', 'ns_comments_gravatar' );
function ns_comments_gravatar( $args ) {

	$args['avatar_size'] = 108;
	return $args;

}

//* Register widget areas
genesis_register_sidebar( array(
	'id'          => 'welcome-message',
	'name'        => __( 'Welcome Message', 'no-sidebar' ),
	'description' => __( 'Widgets in this section will display above post entries on the front page.', 'no-sidebar' ),
) );
genesis_register_sidebar( array(
	'id'          => 'after-entry',
	'name'        => __( 'After Entry', 'no-sidebar' ),
	'description' => __( 'Widgets in this section will display after single post entries.', 'no-sidebar' ),
) );
genesis_register_sidebar( array(
	'id'          => 'newsletter-signup',
	'name'        => __( 'Newsletter Signup', 'no-sidebar' ),
	'description' => __( 'Widgets in this section will display on the newsletter page template.', 'no-sidebar' ),
) );
