<?php
/**
 * Main plugin entry point
 *
 * @package shipper
 */

/**
 * Plugin Name: Shipper Pro
 * Plugin URI: https://wpmudev.com/project/shipper/
 * Description: Migrate WordPress websites from host to host, local to production, development to live with just a few clicks.
 * Version: 1.2.12
 * Network: true
 * Text Domain: shipper
 * Author: WPMU DEV
 * Author URI: https://wpmudev.com
 * WDP ID: 2175128
 */

/*
* Copyright 2010-2011 Incsub (http://incsub.com/)
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'SHIPPER_VERSION', '1.2.12' );
define( 'SHIPPER_PLUGIN_FILE', __FILE__ );

if ( ! defined( 'SHIPPER_IS_TEST_ENV' ) ) {
	define( 'SHIPPER_IS_TEST_ENV', false );
}

require_once dirname( __FILE__ ) . '/lib/functions.php';
require_once dirname( __FILE__ ) . '/lib/exceptions.php';
require_once dirname( __FILE__ ) . '/lib/loader.php';
require_once dirname( __FILE__ ) . '/lib/upgrader.php';

add_action(
	'init',
	function() {
		load_plugin_textdomain(
			'shipper',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}
);

register_activation_hook(
	__FILE__,
	array( 'Shipper_Controller_Setup_Activate', 'activate' )
);
register_deactivation_hook(
	__FILE__,
	array( 'Shipper_Controller_Setup_Deactivate', 'deactivate' )
);
register_uninstall_hook(
	__FILE__,
	array( 'Shipper_Controller_Setup_Uninstall', 'uninstall' )
);

Shipper_Main::get()->boot();