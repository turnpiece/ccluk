<?php

namespace Beehive\Core\Helpers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Defines template helper functionality of the plugin.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Template {

	/**
	 * Get tabs of settings page.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public static function tabs() {
		// Available tabs.
		$tabs = [
			'general'     => __( 'Settings', 'ga_trans' ),
			'tracking'    => __( 'Tracking ID', 'ga_trans' ),
			'permissions' => __( 'Permissions', 'ga_trans' ),
		];

		// Remove permission settings if sub site admins can't change permissions.
		if ( ! Permission::can_overwrite() && ! is_network_admin() && General::is_networkwide() ) {
			unset( $tabs['permissions'] );
		}

		// Network admin doesn't have any restriction.
		if ( ! is_network_admin() ) {
			$tabs['reports'] = __( 'Reports', 'ga_trans' );
		}

		/**
		 * Filter hook to modify settings tabs.
		 *
		 * @param array $args Tabs.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_view_settings_tabs', $tabs );
	}

	/**
	 * Get the current settings tab.
	 *
	 * @param string $default Default tab.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public static function current_tab( $default = 'general' ) {
		// Get current tab.
		$tab = isset( $_GET['tab'] ) ? wp_unslash( $_GET['tab'] ) : $default; // Input var ok.

		// Get available tabs.
		$tabs = self::tabs();

		// Return only if a valid tab.
		if ( in_array( $tab, array_keys( $tabs ), true ) ) {
			return $tab;
		}

		return $default;
	}

	/**
	 * Get the settings page url.
	 *
	 * @param string   $tab     Tab.
	 * @param bool     $network Network flag.
	 * @param int|bool $blog_id Blog ID.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public static function settings_page( $tab = 'general', $network = false, $blog_id = false ) {
		// Get current blog id if empty.
		if ( ! $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		// Get base url.
		$url = $network ? network_admin_url( 'admin.php' ) : get_admin_url( $blog_id, 'admin.php' );

		/**
		 * Filter to modify main url used to build settings url
		 *
		 * @param bool $network Network flag.
		 *
		 * @since 3.2.2
		 */
		$url = apply_filters( 'beehive_settings_main_url', $url, $network, $blog_id );

		return add_query_arg( [
			'page' => 'beehive-settings',
			'tab'  => $tab,
		], $url );
	}

	/**
	 * Get the all statistics page url.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public static function statistics_page( $network = false ) {
		// Get base url.
		$url = $network ? network_admin_url( 'index.php' ) : admin_url( 'index.php' );

		return add_query_arg( [
			'page' => 'beehive-statistics',
		], $url );
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
	 * Check if a notice is dismissed by the user.
	 *
	 * @param string $type Notice type.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function notice_dismissed( $type ) {
		$dismissed = false;

		// Notice meta key.
		$key = 'beehive_dismissed_notice_' . sanitize_key( $type );

		// Check if dismissed.
		if ( get_user_meta( get_current_user_id(), $key, true ) ) {
			$dismissed = true;
		}

		/**
		 * Filter the notice dismissal flag.
		 *
		 * @param bool $dismissed Flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_template_notice_dismissed', $dismissed );
	}
}