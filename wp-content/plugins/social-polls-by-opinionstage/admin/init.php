<?php
// block direct access to plugin PHP files:
defined( 'ABSPATH' ) or die();

require( plugin_dir_path( __FILE__ ).'enqueue-scripts.php' );
require( plugin_dir_path( __FILE__ ).'opinionstage-login-callback.php' );
require( plugin_dir_path( __FILE__ ).'opinionstage-disconnect.php' );
require( plugin_dir_path( __FILE__ ).'opinionstage-content-login-callback.php' );
require( plugin_dir_path( __FILE__ ).'menu-page.php' );
require( plugin_dir_path( __FILE__ ).'content-popup.php' );
require( plugin_dir_path( __FILE__ ).'deactivate-feedback.php' );
	
?>
