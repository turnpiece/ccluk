<?php
// block direct access to plugin PHP files:
defined( 'ABSPATH' ) or die();

function opinionstage_admin_page_assets() {

	if ( isset($_REQUEST['page']) && ($_REQUEST['page'] == OPINIONSTAGE_MENU_SLUG ||  $_REQUEST['page'] == OPINIONSTAGE_PLACEMENT_SLUG || $_REQUEST['page'] == OPINIONSTAGE_GETTING_STARTED_SLUG)) {

		opinionstage_register_css_asset( 'menu-page', 'menu-page.css' );
		opinionstage_register_css_asset( 'icon-font', 'icon-font.css' );
		opinionstage_register_javascript_asset( 'menu-page', 'menu-page.js', array('jquery') );
			
		opinionstage_enqueue_css_asset('menu-page');
		opinionstage_enqueue_css_asset('icon-font');
		opinionstage_enqueue_js_asset('menu-page');

	}
}
	
add_action( 'admin_enqueue_scripts', 'opinionstage_admin_page_assets' );
?>
