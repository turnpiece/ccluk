<?php
/**
 * The GTM helper class.
 *
 * @link    http://wpmudev.com
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager
 */

namespace Beehive\Core\Modules\Google_Tag_Manager;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;

/**
 * Class Helper
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager
 */
class Helper {

	/**
	 * Check if GTM is setup and ready to work.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function is_ready( $network = false ) {
		// Is it active?.
		$active = beehive_analytics()->settings->get( 'active', 'gtm', $network );
		// Get container ID.
		$container = beehive_analytics()->settings->get( 'container', 'gtm', $network );

		// Is ready?.
		$ready = $active && ! empty( $container );

		/**
		 * Filter to modify GTM ready status.
		 *
		 * @param bool $ready   Is ready?.
		 * @param bool $network Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_gtm_is_ready', $ready, $network );
	}

	/**
	 * Check if GTM script can be output to the front end.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function can_output_script( $network = false ) {
		/**
		 * Filter hook to disable the GTM script completely.
		 *
		 * @param bool $enabled Should enable?.
		 * @param bool $network Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_gtm_can_output_script', true, $network );
	}

	/**
	 * Check if a specific variable/integration is enabled.
	 *
	 * @param string $item    Item name.
	 * @param bool   $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function is_enabled( $item, $network = false ) {
		// Get enabled items.
		$enabled = beehive_analytics()->settings->get( 'enabled', 'gtm', $network );

		// Is enabled?.
		$enabled = self::is_ready( $network ) && in_array( $item, $enabled, true );

		/**
		 * Filter to modify item enabled status.
		 *
		 * @param bool $enabled Is ready?.
		 * @param bool $network Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_gtm_is_enabled', $enabled, $network );
	}

	/**
	 * Check if current page is plugin stats page.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function is_gtm_settings() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin stats page.
		// Using strpos to support translation - https://incsub.atlassian.net/browse/BEE-15.
		return isset( $current_screen->id ) && strpos( $current_screen->id, 'page_beehive-google-tag-manager' );
	}

	/**
	 * Get the GTM settings url.
	 *
	 * @param string $tab     Tab.
	 * @param bool   $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	public static function settings_url( $tab = 'account', $network = false ) {
		// Get base url.
		$url = $network ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' );

		/**
		 * Filter to modify main url used to build settings url
		 *
		 * @param bool $network Network flag.
		 *
		 * @since 3.2.2
		 */
		$url = apply_filters( 'beehive_gtm_settings_url', $url, $network );

		// Get page.
		$url = add_query_arg(
			array(
				'page' => 'beehive-google-tag-manager',
			),
			$url
		);

		return $url . '#/' . $tab;
	}

	/**
	 * Get the datalayer name for the site.
	 *
	 * If multisite, there are chances that GTM setup in network
	 * as well as subsite. So, we need to make sure the data layer
	 * variable name is in conflict.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function get_datalyer_name( $network = false ) {
		// Few conditions require general name.
		if ( $network || ! General::is_networkwide() || ! is_multisite() || ! self::is_ready( true ) ) {
			$name = 'dataLayer';
		} else {
			// Append blog id to data layer variable.
			$name = 'dataLayer' . get_current_blog_id();
		}

		/**
		 * Filter to modify the data layer variable name.
		 *
		 * @param bool $name    Data layer name.
		 * @param bool $network Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_gtm_get_datalyer_name', $name, $network );
	}

	/**
	 * Check if GTM network level script can be used.
	 *
	 * If current site's GTM container and network container is
	 * same, we can't output network container because GTM doesn't
	 * support multiple data layer for the same container.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function can_output_network_script() {
		$can = false;

		// Only if active network wide.
		if ( General::is_networkwide() && self::is_ready( true ) ) {
			// If subsite is ready.
			if ( self::is_ready() ) {
				// Get container IDs.
				$subsite_container = beehive_analytics()->settings->get( 'container', 'gtm' );
				$network_container = beehive_analytics()->settings->get( 'container', 'gtm', true );

				// Subsite and network containers should not be same.
				$can = $subsite_container !== $network_container;
			} else {
				// Subsite is not ready, so we can output.
				$can = true;
			}
		}

		/**
		 * Filter hook to alter the check.
		 *
		 * @param bool $can Can output?.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_gtm_can_output_network_script', $can );
	}
}