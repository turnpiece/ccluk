<?php

/*
Plugin Name: Virtue / Pinnacle ToolKit
Description: Custom Portfolio and Shortcode functionality for Virtue and Pinnacle Wordpress Theme
Version: 3.2
Author: Kadence Themes
Author URI: http://kadencethemes.com/
License: GPLv2 or later
*/

function virtue_toolkit_activation() {
	kad_portfolio_post_init();
	flush_rewrite_rules();
	get_option('kadence_toolkit_flushpermalinks', '1');
}
register_activation_hook(__FILE__, 'virtue_toolkit_activation');

function virtue_toolkit_deactivation() {
}
register_deactivation_hook(__FILE__, 'virtue_toolkit_deactivation');

add_filter( 'kadence_theme_options_args', 'kadencetoolkit_redux_args_new');
function kadencetoolkit_redux_args_new( $args ) {
            $args['customizer_only'] = false;
            return $args;
}
require_once('post-types.php');
require_once('gallery.php');
require_once('shortcodes.php');
require_once('shortcode_ajax.php');
require_once('pagetemplater.php');
require_once('metaboxes.php');

if(!defined('VIRTUE_TOOLKIT_PATH')){
	define('VIRTUE_TOOLKIT_PATH', realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR );
}
if(!defined('VIRTUE_TOOLKIT_URL')){
	define('VIRTUE_TOOLKIT_URL', plugin_dir_url(__FILE__) );
}

function kadencetoolkit_textdomain() {
  load_plugin_textdomain( 'virtue-toolkit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
add_action( 'plugins_loaded', 'kadencetoolkit_textdomain' );


function kadencetoolkit_admin_scripts() {
  wp_register_style('kadencetoolkit_adminstyles', VIRTUE_TOOLKIT_URL . '/assets/toolkit_admin.css', false, 23);
  wp_enqueue_style('kadencetoolkit_adminstyles');

}

add_action('admin_enqueue_scripts', 'kadencetoolkit_admin_scripts');

function kadence_toolkit_flushpermalinks() {
	$hasflushed = get_option('kadence_toolkit_flushpermalinks', '0');
	if($hasflushed != '1') {
		flush_rewrite_rules();
		update_option('kadence_toolkit_flushpermalinks', '1');
	}
}
add_action('init', 'kadence_toolkit_flushpermalinks');


