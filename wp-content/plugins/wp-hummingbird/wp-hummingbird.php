<?php
/**
Plugin Name: Hummingbird Pro
Version:     1.9.1
Plugin URI:  https://premium.wpmudev.org/project/wp-hummingbird/
Description: Hummingbird zips through your site finding new ways to make it load faster, from file compression and minification to browser caching – because when it comes to pagespeed, every millisecond counts.
Author:      WPMU DEV
Author URI:  http://premium.wpmudev.org
Network:     true
Text Domain: wphb
Domain Path: /languages
WDP ID:      1081721

@package Hummingbird
 */

/*
Copyright 2007-2016 Incsub (http://incsub.com)
Author – Ignacio Cruz (igmoweb), Ricardo Freitas (rtbfreitas), Anton Vanyukov (vanyukov)
Contributors –

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 – GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if ( ! defined( 'WPHB_VERSION' ) ) {
	define( 'WPHB_VERSION', '1.9.1' );
}

if ( ! defined( 'WPHB_DIR_PATH' ) ) {
	define( 'WPHB_DIR_PATH', trailingslashit( dirname( __FILE__ ) ) );
}

if ( ! defined( 'WPHB_DIR_URL' ) ) {
	define( 'WPHB_DIR_URL', plugin_dir_url( __FILE__ ) );
}

if ( file_exists( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'free-mods.php' ) ) {
	/* @noinspection PhpIncludeInspection */
	include_once( 'free-mods.php' );
}

if ( defined( 'WPHB_WPORG' ) && WPHB_WPORG && 'wp-hummingbird/wp-hummingbird.php' != plugin_basename( __FILE__ ) ) {
	// Add notice to rate the free version.
	$free_installation = get_site_option( 'wphb-free-install-date' );
	if ( empty( $free_installation ) ) {
		update_site_option( 'wphb-notice-free-rated-show', 'yes' );
		update_site_option( 'wphb-free-install-date', current_time( 'timestamp' ) );
	}

	// This plugin is the free version so if the Pro version is activated we need to deactivate this one.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$pro_installed = false;
	if ( file_exists( WP_PLUGIN_DIR . '/wp-hummingbird/wp-hummingbird.php' ) ) {
		$pro_installed = true;
	}

	// Check if the pro version exists and is activated.
	if ( is_plugin_active( 'wp-hummingbird/wp-hummingbird.php' ) ) {
		// Pro is activated, deactivate this one.
		deactivate_plugins( plugin_basename( __FILE__ ) );
		update_site_option( 'wphb-notice-free-deactivated-show', 'yes' );
		return;
	} elseif ( $pro_installed ) {
		// Pro is installed but not activated, let's activate it.
		deactivate_plugins( plugin_basename( __FILE__ ) );
		activate_plugin( 'wp-hummingbird/wp-hummingbird.php' );
	}
}

if ( ! class_exists( 'WP_Hummingbird' ) ) {
	/**
	 * Class WP_Hummingbird
	 *
	 * Main Plugin class. Acts as a loader of everything else and intializes the plugin
	 */
	class WP_Hummingbird {

		/**
		 * Plugin instance
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Admin main class
		 *
		 * @var WP_Hummingbird_Admin
		 */
		public $admin;

		/**
		 * Pro modules
		 *
		 * @since 1.7.2
		 *
		 * @var WP_Hummingbird_Pro
		 */
		public $pro;

		/**
		 * Core
		 *
		 * @var WP_Hummingbird_Core
		 */
		public $core;

		/**
		 * Hummingbird Pro project ID.
		 *
		 * @since  1.7.0
		 * @access private
		 * @var    int $project_id
		 */
		private static $project_id = 1081721;

		/**
		 * Return the plugin instance
		 *
		 * @return WP_Hummingbird
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * WP_Hummingbird constructor.
		 */
		public function __construct() {
			$this->includes();

			$this->init();

			$this->init_pro();

			if ( is_admin() ) {
				add_action( 'admin_init', array( 'WP_Hummingbird_Installer', 'maybe_upgrade' ) );

				if ( is_multisite() ) {
					add_action( 'admin_init', array( 'WP_Hummingbird_Installer', 'maybe_upgrade_blog' ) );
				}
			}

			$this->load_textdomain();

			// Add upgrade schedule.
			add_action( 'wphb_upgrade_to_pro', array( $this, 'upgrade_to_pro' ) );
			// Try to update to pro version is user can do that.
			if ( self::is_free_installed() && self::can_install_pro() ) {
				$running_cron_update = get_site_option( 'wphb_cron_update_running' );
				if ( empty( $running_cron_update ) ) {
					// Schedule upgrade.
					wp_schedule_single_event( time(), 'wphb_upgrade_to_pro' );
					update_site_option( 'wphb_cron_update_running', true );
				}
			}

			add_action( 'init', array( $this, 'maybe_clear_all_cache' ) );
		}

		/**
		 * Initialize the plugin.
		 */
		private function init() {
			// Initialize the plugin core.
			$this->core = new WP_Hummingbird_Core();

			if ( is_admin() ) {
				// Initialize admin core files.
				$this->admin = new WP_Hummingbird_Admin();
			}

			/**
			 * Triggered when WP Hummingbird is totally loaded
			 */
			do_action( 'wp_hummingbird_loaded' );
		}

		/**
		 * Initialize pro modules.
		 *
		 * @since 1.7.2
		 */
		private function init_pro() {
			// Overwriting in wp-config.php file to exclude PRO.
			if ( defined( 'WPHB_LOAD_PRO' ) && false === WPHB_LOAD_PRO ) {
				return;
			}

			$pro_class = WPHB_DIR_PATH . 'core/pro/class-pro.php';
			if ( is_readable( $pro_class ) ) {
				/* @noinspection PhpIncludeInspection */
				include_once( $pro_class );

				$this->pro = WP_Hummingbird_Pro::get_instance();
				$this->pro->init();
			}
		}

		/**
		 * Clear all cache?
		 */
		public function maybe_clear_all_cache() {
			if ( ! isset( $_GET['wphb-clear'] ) || ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
				return;
			}

			self::flush_cache();

			WP_Hummingbird_Settings::update_setting( 'last_score', 0, 'performance' );

			if ( 'all' === $_GET['wphb-clear'] ) {
				WP_Hummingbird_Settings::reset_to_defaults();
				delete_option( 'wphb-quick-setup' );
			}

			wp_safe_redirect( remove_query_arg( 'wphb-clear' ) );
			exit;
		}

		/**
		 * Flush all WP Hummingbird Cache
		 */
		public static function flush_cache() {
			/* @var WP_Hummingbird $hummingbird */
			$hummingbird = WP_Hummingbird::get_instance();
			/* @var WP_Hummingbird_Module $module */
			foreach ( $hummingbird->core->modules as $module ) {
				if ( ! $module->is_active() ) {
					continue;
				}
				$module->clear_cache();
			}

			if ( WP_Hummingbird_Module_Server::is_htaccess_written( 'gzip' ) ) {
				WP_Hummingbird_Module_Server::unsave_htaccess( 'gzip' );
			}

			if ( WP_Hummingbird_Module_Server::is_htaccess_written( 'caching' ) ) {
				WP_Hummingbird_Module_Server::unsave_htaccess( 'caching' );
			}

			/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
			$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
			$cf_module->disconnect();
		}

		/**
		 * Load translations
		 */
		private function load_textdomain() {
			load_plugin_textdomain( 'wphb', false, 'wp-hummingbird/languages/' );
		}

		/**
		 * Load needed files for the plugin
		 */
		private function includes() {
			// Core files.
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/class-installer.php' );
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/class-core.php' );
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/class-filesystem.php' );
			/* @noinspection PhpIncludeInspection */
			include_once( WPHB_DIR_PATH . 'core/integration.php' );

			// Helpers files.
			/* @noinspection PhpIncludeInspection */
			include_once WPHB_DIR_PATH . 'core/class-utils.php';
			/* @noinspection PhpIncludeInspection */
			include_once WPHB_DIR_PATH . 'core/class-settings.php';

			if ( is_admin() ) {
				// Load only admin files.
				/* @noinspection PhpIncludeInspection */
				include_once( WPHB_DIR_PATH . 'admin/class-admin.php' );
			}

		}

		/**
		 * Check if free version is installed.
		 *
		 * @return bool
		 */
		private static function is_free_installed() {
			if ( defined( 'WPHB_WPORG' ) && WPHB_WPORG && 'wp-hummingbird/wp-hummingbird.php' !== plugin_basename( __FILE__ ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if it's possible to install pro version.
		 *
		 * @return bool
		 */
		private static function can_install_pro() {
			// Check that dashboard plugin is installed.
			if ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
				return false;
			}

			if ( ! is_object( WPMUDEV_Dashboard::$api ) ) {
				return false;
			}

			if ( ! method_exists( WPMUDEV_Dashboard::$api, 'has_key' ) ) {
				return false;
			}

			// If user can't install - exit.
			if ( ! WPMUDEV_Dashboard::$upgrader->user_can_install( self::$project_id ) ) {
				return false;
			}

			// Check permissions and configuration.
			if ( ! WPMUDEV_Dashboard::$upgrader->can_auto_install( self::$project_id ) ) {
				return false;
			}

			$plugin = WPMUDEV_Dashboard::$api->get_project_data( self::$project_id );
			if ( version_compare( WPHB_VERSION, $plugin['version'], '>' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Upgrade free version to pro.
		 *
		 * @since 1.7.0
		 */
		public function upgrade_to_pro() {
			/**
			 * If pro is already installed - exit.
			 *
			 * If ( WPMUDEV_Dashboard::$upgrader->is_project_installed( $project_id ) ) {
			 * //return uninstall_plugin( 'hummingbird-performance/wp-hummingbird.php' );
			 * }
			 */

			if ( WPMUDEV_Dashboard::$upgrader->install( self::$project_id ) ) {
				delete_site_option( 'wphb_cron_update_running' );
				activate_plugin( 'wp-hummingbird/wp-hummingbird.php' );
				// Do we need to deactivate?
				deactivate_plugins( 'hummingbird-performance/wp-hummingbird.php', true );
				delete_plugins( array( 'hummingbird-performance/wp-hummingbird.php' ) );
			}
		}
	}
} // End if().

register_activation_hook( 'core/class-installer.php', array( 'WP_Hummingbird_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Hummingbird_Installer', 'deactivate' ) );

// Init the plugin and load the plugin instance for the first time.
add_action( 'plugins_loaded', array( 'WP_Hummingbird', 'get_instance' ) );