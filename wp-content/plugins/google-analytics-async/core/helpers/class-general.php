<?php
/**
 * Defines general helper functionality of the plugin.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Helpers
 */

namespace Beehive\Core\Helpers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Dashboard;

/**
 * Class General
 *
 * @package Beehive\Core\Helpers
 */
class General {

	/**
	 * List of plugin dashboard pages.
	 *
	 * @since 3.2.4
	 *
	 * @var array $pages
	 */
	public static $dashboard_pages = array(
		'toplevel_page_beehive',
		'toplevel_page_beehive-network',
	);

	/**
	 * List of plugin settings pages.
	 *
	 * @since 3.2.4
	 *
	 * @var array $pages
	 */
	public static $settings_pages = array(
		'dashboard_page_beehive-settings',
		'dashboard_page_beehive-settings-network',
	);

	/**
	 * List of plugin integration pages.
	 *
	 * @var array $pages
	 *
	 * @since 3.3.0
	 */
	public static $integrations_pages = array(
		'dashboard_page_beehive-integrations',
		'dashboard_page_beehive-integrations-network',
	);

	/**
	 * List of dashboard pages.
	 *
	 * @var array $dashboard_pages
	 */
	public static $wp_dashboard_pages = array(
		'dashboard',
		'dashboard-network',
	);

	/**
	 * Get the plugin name based on the Pro status.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function plugin_name() {
		$name = beehive_analytics()->is_pro() ? __( 'Beehive Pro', 'ga_trans' ) : __( 'Beehive', 'ga_trans' );

		/**
		 * Filter to modify Beehive plugin name.
		 *
		 * @param string $name Plugin name.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_plugin_name', $name );
	}

	/**
	 * Check if the plugin is active network wide.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed|void
	 */
	public static function is_networkwide() {
		static $active = null;

		// Do not check if already did.
		if ( null === $active ) {
			if ( is_multisite() ) {
				// Makes sure the plugin is defined before trying to use it.
				if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
					require_once ABSPATH . '/wp-admin/includes/plugin.php';
				}

				$active = is_plugin_active_for_network( plugin_basename( BEEHIVE_PLUGIN_FILE ) );
			} else {
				$active = false;
			}
		}

		return $active;
	}

	/**
	 * Check if current page is plugin dashboard page.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public static function is_plugin_dashboard() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin page.
		return isset( $current_screen->id ) && in_array( $current_screen->id, self::$dashboard_pages, true );
	}

	/**
	 * Check if current page is plugin settings page.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public static function is_plugin_settings() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin page.
		// Using strpos to support translation - https://incsub.atlassian.net/browse/BEE-15.
		return isset( $current_screen->id ) && strpos( $current_screen->id, 'page_beehive-settings' );
	}

	/**
	 * Check if current page is plugin accounts page.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function is_plugin_accounts() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin page.
		// Using strpos to support translation - https://incsub.atlassian.net/browse/BEE-15.
		return isset( $current_screen->id ) && strpos( $current_screen->id, 'page_beehive-accounts' );
	}

	/**
	 * Check if current page is plugin dashboard widget page.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function is_wp_dashboard_page() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is dashboard page.
		return isset( $current_screen->id ) && in_array( $current_screen->id, self::$wp_dashboard_pages, true );
	}

	/**
	 * Get list of page ids of plugin admin pages.
	 *
	 * PLEASE NOTE: Settings subpages maynot be correct when you
	 * run a different locale.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public static function get_plugin_admin_pages() {
		// Merge dashboard and settings pages.
		$pages = array_merge( self::$dashboard_pages, self::$settings_pages );

		/**
		 * Filter the list of Beehive admin page ids.
		 *
		 * @param array $pages
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_admin_pages_list', $pages );
	}

	/**
	 * Returns current user name to be displayed.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public static function get_user_name() {
		// Get current user.
		$current_user = wp_get_current_user();

		// If first name is ready get that, or get the display name.
		$name = empty( $current_user->first_name ) ? $current_user->display_name : $current_user->first_name;

		// Fallback to unknown name.
		if ( empty( $name ) ) {
			$name = __( 'User', 'ga_trans' );
		}

		return ucfirst( $name );
	}

	/**
	 * Get the current membership status using Dash plugin.
	 *
	 * We will get the status using WPMUDEV Dashboard plugin.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function membership_status() {
		static $status = null;

		// Get the status.
		if ( is_null( $status ) ) {
			// Dashboard is active.
			if ( class_exists( 'WPMUDEV_Dashboard' ) ) {
				// Get membership type.
				$status = WPMUDEV_Dashboard::$api->get_membership_type( $project_id );
				// Check if API key is available.
				if ( 'free' === $status && WPMUDEV_Dashboard::$api->has_key() ) {
					$status = 'expired';
				}
			} else {
				$status = 'free';
			}
		}

		/**
		 * Filter to modify WPMUDEV membership status or user.
		 *
		 * @param string $status Status.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_wpmudev_membership_status', $status );
	}

	/**
	 * Sanitize a simple array input using array_map.
	 *
	 * If the input is not an array it will not process.
	 *
	 * @param array  $data     Data to sanitize.
	 * @param string $function Function name to use.
	 *
	 * @since 3.2.2
	 *
	 * @return array
	 */
	public static function sanitize_array( $data, $function = 'sanitize_text_field' ) {
		if ( is_array( $data ) && function_exists( $function ) ) {
			$data = array_map( $function, $data );
		}

		return $data;
	}

	/**
	 * Check if a given date string is in required format.
	 *
	 * @param string $date   Date string to check.
	 * @param string $format Date format to check.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public static function check_date_format( $date, $format = 'Y-m-d' ) {
		$created = \DateTime::createFromFormat( $format, $date );

		return $created && $created->format( $format ) === $date;
	}

	/**
	 * Autoload vendor autoload class if required.
	 *
	 * This was introduced to avoid loading the composer autloader
	 * in all pages. We need to load the autoloader only when it's
	 * really required. Otherwise other plugins/themes using composer
	 * will load our prefixed Google lib and throw fatal error.
	 *
	 * @since 3.2.7
	 *
	 * @return void
	 */
	public static function vendor_autoload() {
		static $loaded = null;

		if ( ! $loaded ) {
			if ( file_exists( plugin_dir_path( BEEHIVE_PLUGIN_FILE ) . '/dependencies/vendor/scoper-autoload.php' ) ) {
				require_once plugin_dir_path( BEEHIVE_PLUGIN_FILE ) . '/dependencies/vendor/scoper-autoload.php';
			} elseif ( BEEHIVE_VERSION && version_compare( BEEHIVE_VERSION, '3.2.4', '>' ) ) {
				// We need autoload.
				wp_die( esc_html__( 'Autoloader is missing. Please run composer install if you are on development version.', 'ga_trans' ) );
			}

			$loaded = true;
		}
	}
}