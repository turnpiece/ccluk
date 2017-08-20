<?php
/**
 * Plugin Name: BuddyPress User Blog
 * Plugin URI:  https://www.buddyboss.com
 * Description: Let your BuddyPress users create blog posts from the frontend.
 * Author:      BuddyBoss
 * Author URI:  http://buddyboss.com
 * Version:     1.2.1
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * ========================================================================
 * CONSTANTS
 * ========================================================================
 */
// Codebase version
if ( ! defined( 'BUDDYBOSS_SAP_PLUGIN_VERSION' ) ) {
	define( 'BUDDYBOSS_SAP_PLUGIN_VERSION', '1.2.1' );
}

// Database version
if ( ! defined( 'BUDDYBOSS_SAP_PLUGIN_DB_VERSION' ) ) {
	define( 'BUDDYBOSS_SAP_PLUGIN_DB_VERSION', 1 );
}

// Directory
if ( ! defined( 'BUDDYBOSS_SAP_PLUGIN_DIR' ) ) {
	define( 'BUDDYBOSS_SAP_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url
if ( ! defined( 'BUDDYBOSS_SAP_PLUGIN_URL' ) ) {
	$plugin_url = plugin_dir_url( __FILE__ );

	// If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
	if ( is_ssl() )
		$plugin_url = str_replace( 'http://', 'https://', $plugin_url );

	define( 'BUDDYBOSS_SAP_PLUGIN_URL', $plugin_url );
}

// File
if ( ! defined( 'BUDDYBOSS_SAP_PLUGIN_FILE' ) ) {
	define( 'BUDDYBOSS_SAP_PLUGIN_FILE', __FILE__ );
}

/**
 * ========================================================================
 * MAIN FUNCTIONS
 * ========================================================================
 */

/**
 * Main
 *
 * @return void
 */
add_action( 'plugins_loaded', 'BUDDYBOSS_SAP_init' );

function BUDDYBOSS_SAP_init() {

	global $bp,$BUDDYBOSS_SAP;

        if ( !$bp ) {
		add_action('admin_notices','sap_bp_admin_notice');
		add_action('network_admin_notices','sap_bp_admin_notice');
		return;
	}

	$main_include = BUDDYBOSS_SAP_PLUGIN_DIR . 'includes/main-class.php';

	try {
		if ( file_exists( $main_include ) ) {
			require( $main_include );
		} else {
			$msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'bp-user-blog' ), $main_include );
			throw new Exception( $msg, 404 );
		}
	} catch ( Exception $e ) {
		$msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'bp-user-blog' ), $e->getMessage() );
		echo $msg;
	}

	$BUDDYBOSS_SAP = BuddyBoss_SAP_Plugin::instance();
}

/**
 * Check whether
 * it meets all requirements
 * @return void
 */
function sap_requirements()
{

    global $Plugin_Requirements_Check;

    $requirements_Check_include  = BUDDYBOSS_SAP_PLUGIN_DIR  . 'includes/requirements-class.php';

    try
    {
        if ( file_exists( $requirements_Check_include ) )
        {
            require( $requirements_Check_include );
        }
        else{
            $msg = sprintf( __( "Couldn't load SAP_Plugin_Check class at:<br/>%s", 'bp-user-blog' ), $requirements_Check_include );
            throw new Exception( $msg, 404 );
        }
    }
    catch( Exception $e )
    {
        $msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'bp-user-blog' ), $e->getMessage() );
        echo $msg;
    }

    $Plugin_Requirements_Check = new SAP_Plugin_Requirements_Check();
    $Plugin_Requirements_Check->activation_check();

}
register_activation_hook( __FILE__, 'sap_requirements' );

/**
 * Must be called after hook 'plugins_loaded'
 * @return BuddyBoss Bsp Plugin main controller object
 */
function buddyboss_sap() {

	global $BUDDYBOSS_SAP;
	return $BUDDYBOSS_SAP;

}

function sap_bp_admin_notice() {
	echo "<div class='error'><p>BuddyPress User Blog needs BuddyPress activated</p></div>";
}

/**
 * Register BuddyBoss Menu Page
 */
if ( !function_exists( 'register_buddyboss_menu_page' ) ) {

	function register_buddyboss_menu_page() {
		// Set position with odd number to avoid confict with other plugin/theme.
		add_menu_page( 'BuddyBoss', 'BuddyBoss', 'manage_options', 'buddyboss-settings', '', buddyboss_sap()->assets_url . '/images/logo.svg', 61.000129 );

		// To remove empty parent menu item.
		add_submenu_page( 'buddyboss-settings', 'BuddyBoss', 'BuddyBoss', 'manage_options', 'buddyboss-settings' );
		remove_submenu_page( 'buddyboss-settings', 'buddyboss-settings' );
	}

	add_action( 'admin_menu', 'register_buddyboss_menu_page' );
}

/**
 * Allow automatic updates via the WordPress dashboard
 */
require_once('includes/buddyboss-plugin-updater.php');
//new buddyboss_updater_plugin( 'http://update.buddyboss.com/plugin', plugin_basename(__FILE__), 202);
