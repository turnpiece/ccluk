<?php
/*
Plugin Name: Poll, Survey, Form & Quiz Maker by OpinionStage (Deprecated)
Plugin URI: https://www.opinionstage.com
Description: Add a highly engaging poll, survey, quiz or contact form builder to your site. You can add the poll, survey, quiz or form to any post/page or to the sidebar.
Version: 19.6.5
Author: OpinionStage.com
Author URI: https://www.opinionstage.com
Text Domain: social-polls-by-opinionstage
*/

// block direct access to plugin PHP files:
defined( 'ABSPATH' ) or die();

// Check if active plugin file is plugin.php on plugin activate hook
function opinionstage_plugin_activate() {
	// update in database
	$plugins = get_option('active_plugins');
	
	$plugins = array_diff($plugins, array("social-polls-by-opinionstage/opinionstage-polls.php"));
	$plugins[] = "social-polls-by-opinionstage/plugin.php";
	
	update_option('active_plugins', $plugins);
}
register_activation_hook( __FILE__, 'opinionstage_plugin_activate' );

function opinionstage_shutdown(){
	// update in database
	$plugins = get_option('active_plugins');
	
	$plugins = array_diff($plugins, array("social-polls-by-opinionstage/opinionstage-polls.php"));
	$plugins[] = "social-polls-by-opinionstage/plugin.php";
	
	update_option('active_plugins', $plugins);

	deactivate_plugins( plugin_basename( __FILE__ ) );
}
add_action('shutdown', 'opinionstage_shutdown');
?>
