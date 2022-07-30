<?php
/**
 * Defines template helper functionality of the plugin.
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

/**
 * Class Template
 *
 * @package Beehive\Core\Helpers
 */
class Template {

	/**
	 * Get the settings page url.
	 *
	 * @param string   $tab     Tab.
	 * @param bool     $network Network flag.
	 * @param int|bool $blog_id Blog ID.
	 * @param string   $page    Admin page slug.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Added admin page slug param.
	 *
	 * @return string
	 */
	public static function settings_url( $tab = 'permissions', $network = false, $blog_id = false, $page = 'beehive-settings' ) {
		// Get current blog id if empty.
		if ( ! $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		// Get base url.
		$url = $network ? network_admin_url( 'admin.php' ) : get_admin_url( $blog_id, 'admin.php' );

		// Get page.
		$url = add_query_arg(
			array(
				'page' => $page,
			),
			$url
		);

		// Append tab.
		$url = $url . '#/' . $tab;

		/**
		 * Filter to modify main url used to build settings url
		 *
		 * @param string $url     URL.
		 * @param bool   $network Network flag.
		 * @param int    $blog_id Blog ID.
		 *
		 * @since 3.2.2
		 */
		return apply_filters( 'beehive_settings_main_url', $url, $network, $blog_id );
	}

	/**
	 * Get the accounts page url.
	 *
	 * @param string   $tab     Tab.
	 * @param bool     $network Network flag.
	 * @param int|bool $blog_id Blog ID.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	public static function accounts_url( $tab = 'google', $network = false, $blog_id = false ) {
		// Get current blog id if empty.
		if ( ! $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		// Get base url.
		$url = $network ? network_admin_url( 'admin.php' ) : get_admin_url( $blog_id, 'admin.php' );

		// Get page.
		$url = add_query_arg(
			array(
				'page' => 'beehive-accounts',
			),
			$url
		);

		// Append tab.
		$url = $url . '#/' . $tab;

		/**
		 * Filter to modify main url used to build accounts url
		 *
		 * @param string $url     URL.
		 * @param bool   $network Network flag.
		 * @param int    $blog_id Blog ID.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_accounts_main_url', $url, $network, $blog_id );
	}

	/**
	 * Get the dashboard page url.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.4
	 *
	 * @return string
	 */
	public static function dashboard_url( $network = false ) {
		// Get base url.
		$url = $network ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' );

		$url = add_query_arg(
			array(
				'page' => 'beehive',
			),
			$url
		);

		/**
		 * Filter to modify dashboard url
		 *
		 * @param bool $network Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_dashboard_url', $url, $network );
	}

	/**
	 * Get assets url of Beehive plugin.
	 *
	 * @param string $url Relative url path.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public static function asset_url( $url = '' ) {
		return BEEHIVE_URL . 'app/assets/' . $url;
	}

	/**
	 * Settings page url helper.
	 *
	 * Keeping this to avoid fatal error when JetPack is active
	 * and we upgrade from 2.8.3
	 * This will be removed soon.
	 *
	 * @deprecated 3.3.0
	 *
	 * @return string
	 */
	public static function settings_page() {
		return '';
	}
}