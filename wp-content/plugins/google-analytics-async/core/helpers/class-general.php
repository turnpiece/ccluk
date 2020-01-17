<?php

namespace Beehive\Core\Helpers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Dashboard;

/**
 * Defines general helper functionality of the plugin.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class General {

	/**
	 * List of plugin settings pages.
	 *
	 * @var array $plugin_pages
	 */
	public static $pages = array(
		'toplevel_page_beehive-settings',
		'toplevel_page_beehive-settings-network',
	);

	/**
	 * List of plugin dashboard stats pages.
	 *
	 * @var array $plugin_pages
	 */
	public static $dashboard_pages = array(
		'dashboard-network',
		'dashboard',
	);

	/**
	 * List of plugin stats pages.
	 *
	 * @var array $plugin_pages
	 */
	public static $stats_pages = array(
		'dashboard_page_beehive-statistics',
		'index_page_beehive-statistics-network',
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
	 * Check if current page is plugin admin page.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function is_plugin_admin() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin page.
		return isset( $current_screen->id ) && in_array( $current_screen->id, self::$pages, true );
	}

	/**
	 * Check if current page is plugin stats page.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function is_plugin_stats() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin stats page.
		return isset( $current_screen->id ) && in_array( $current_screen->id, self::$stats_pages, true );
	}

	/**
	 * Check if current page is plugin dashboard widget page.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function is_plugin_dashboard_widget() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is dashboard page.
		return isset( $current_screen->id ) && in_array( $current_screen->id, self::$dashboard_pages, true );
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
}