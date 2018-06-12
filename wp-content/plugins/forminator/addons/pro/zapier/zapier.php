<?php

/**
 * Addon Name: Zapier
 * Version: 1.0
 * Plugin URI:  https://premium.wpmudev.org/
 * Description: Integrate Forminator Custom Forms with Zapier to execute various action you like
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 */

define( 'FORMINATOR_ADDON_ZAPIER_VERSION', '1.0' );

function forminator_addon_zapier_url() {
	return trailingslashit( forminator_plugin_url() . 'addons/pro/zapier' );
}

function forminator_addon_zapier_assets_url() {
	return trailingslashit( forminator_addon_zapier_url() . 'assets' );
}

require_once dirname( __FILE__ ) . '/forminator-addon-zapier.php';
require_once dirname( __FILE__ ) . '/forminator-addon-zapier-form-settings.php';
require_once dirname( __FILE__ ) . '/forminator-addon-zapier-form-hooks.php';
//Direct Load
Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Zapier' );