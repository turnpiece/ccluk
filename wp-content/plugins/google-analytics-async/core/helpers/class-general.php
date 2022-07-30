<?php
/**
 * Defines general helper functionality of the plugin.
 *
 * @link    http://wpmudev.com
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
	 * @var array $dashboard_pages
	 */
	public static $dashboard_pages = array(
		'toplevel_page_beehive',
		'toplevel_page_beehive-network',
	);

	/**
	 * List of plugin settings pages.
	 *
	 * @since 3.2.4
	 * @var array $settings_pages
	 */
	public static $settings_pages = array(
		'dashboard_page_beehive-settings',
		'dashboard_page_beehive-settings-network',
	);

	/**
	 * List of plugin accounts pages.
	 *
	 * @since 3.3.7
	 * @var array $accounts_pages
	 */
	public static $accounts_pages = array(
		'dashboard_page_beehive-accounts',
		'dashboard_page_beehive-accounts-network',
	);

	/**
	 * List of plugin tutorials pages.
	 *
	 * @since 3.3.7
	 * @var array $accounts_pages
	 */
	public static $tutorials_pages = array(
		'dashboard_page_beehive-tutorials',
		'dashboard_page_beehive-tutorials-network',
	);

	/**
	 * List of dashboard pages.
	 *
	 * @since  3.3.7
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
		 * @since 3.2.0
		 *
		 * @param string $name Plugin name.
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
	 * Check if current page is plugin tutorials page.
	 *
	 * @since 3.3.7
	 *
	 * @return bool
	 */
	public static function is_plugin_tutorials() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin page.
		// Using strpos to support translation - https://incsub.atlassian.net/browse/BEE-15.
		return isset( $current_screen->id ) && strpos( $current_screen->id, 'page_beehive-tutorials' );
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
		$pages = array_merge(
			self::$dashboard_pages,
			self::$settings_pages,
			self::$accounts_pages,
			self::$tutorials_pages
		);

		/**
		 * Filter the list of Beehive admin page ids.
		 *
		 * @since 3.2.4
		 *
		 * @param array $pages Page IDs.
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
				$status = WPMUDEV_Dashboard::$api->get_membership_type();
				// Get available projects.
				$projects = WPMUDEV_Dashboard::$api->get_membership_projects();

				// Beehive single plan.
				if ( ( 'unit' === $status && ! in_array( 51, $projects, true ) ) || ( 'single' === $status && 51 !== $projects ) ) {
					$status = 'upgrade';
				} elseif ( 'free' === $status && WPMUDEV_Dashboard::$api->has_key() ) {
					// Check if API key is available but status is free, then it's expired.
					$status = 'expired';
				}
			} else {
				$status = 'free';
			}
		}

		/**
		 * Filter to modify WPMUDEV membership status or user.
		 *
		 * @since 3.2.0
		 *
		 * @param string $status Status.
		 */
		return apply_filters( 'beehive_wpmudev_membership_status', $status );
	}

	/**
	 * Sanitize a simple array input using array_map.
	 *
	 * If the input is not an array it will not process.
	 *
	 * @since 3.2.2
	 *
	 * @param array  $data     Data to sanitize.
	 * @param string $function Function name to use.
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
	 * @since 3.2.4
	 *
	 * @param string $date   Date string to check.
	 * @param string $format Date format to check.
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
			// Temporary fix for composer conflict.
			if ( ! empty( $GLOBALS['__composer_autoload_files'] ) ) {
				foreach ( $GLOBALS['__composer_autoload_files'] as $identifier => $loaded ) {
					$GLOBALS['__composer_autoload_files'][ $identifier ] = false;
				}
			}

			if ( file_exists( plugin_dir_path( BEEHIVE_PLUGIN_FILE ) . '/dependencies/vendor/scoper-autoload.php' ) ) {
				require_once plugin_dir_path( BEEHIVE_PLUGIN_FILE ) . '/dependencies/vendor/scoper-autoload.php';
			} elseif ( BEEHIVE_VERSION && version_compare( BEEHIVE_VERSION, '3.2.4', '>' ) ) {
				// We need autoload.
				wp_die( esc_html__( 'Autoloader is missing. Please run `composer install` if you are on development version.', 'ga_trans' ) );
			}

			$loaded = true;
		}
	}

	/**
	 * Check if a plugin is installed in WP.
	 *
	 * @since 3.3.3
	 *
	 * @param string $plugin Plugin file.
	 *
	 * @return bool
	 */
	public static function is_plugin_installed( $plugin ) {
		return file_exists( trailingslashit( WP_PLUGIN_DIR ) . $plugin );
	}
}