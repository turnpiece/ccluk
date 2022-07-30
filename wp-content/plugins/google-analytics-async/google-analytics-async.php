<?php
/**
 * Main plugin header.
 *
 * @package Beehive
 *
 * Plugin Name: Beehive Pro
 * Plugin URI:  https://wpmudev.com/project/beehive-analytics-pro/
 * Description: Enables Google Analytics for your site with statistics inside WordPress admin panel. Single and multi site compatible!
 * Author:      WPMU DEV
 * Author URI:  https://wpmudev.com
 * Version:     3.4.0
 * License:     GNU General Public License (Version 2 - GPLv2)
 * Text Domain: ga_trans
 * Domain Path: /languages
 * WDP ID:      51
 *
 * Beehive is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Beehive is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Beehive. If not, see <http://www.gnu.org/licenses/>.
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

// Flag to check if it's Pro or Free.
if ( ! defined( 'BEEHIVE_PRO' ) ) {
	define( 'BEEHIVE_PRO', true );
}

// Define BEEHIVE_PLUGIN_FILE.
if ( ! defined( 'BEEHIVE_PLUGIN_FILE' ) ) {
	define( 'BEEHIVE_PLUGIN_FILE', __FILE__ );
}

// Plugin version.
if ( ! defined( 'BEEHIVE_VERSION' ) ) {
	define( 'BEEHIVE_VERSION', '3.4.0' );
}

// Auto load classes.
require_once plugin_dir_path( __FILE__ ) . '/core/utils/autoloader.php';

/**
 * Run plugin activation hook to setup plugin.
 *
 * @since 3.2.0
 */
register_activation_hook( __FILE__, array( \Beehive\Core\Controllers\Installer::instance(), 'activate' ) );

// Make sure beehive is not already defined.
if ( ! function_exists( 'beehive_analytics' ) ) {
	/**
	 * Main instance of plugin.
	 *
	 * Returns the main instance of Beehive to prevent the need to use globals
	 * and to maintain a single copy of the plugin object.
	 * You can simply call beehive_analytics() to access the object.
	 *
	 * @since  1.0.0
	 *
	 * @return Beehive\Core\Beehive
	 */
	function beehive_analytics() {
		return Beehive\Core\Beehive::instance();
	}
}

// Init the plugin and load the plugin instance for the first time.
add_action( 'plugins_loaded', 'beehive_analytics' );