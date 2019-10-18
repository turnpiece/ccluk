<?php

namespace Beehive\Core\Modules\Google_Analytics;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Auth\Helper as Auth_Helper;

/**
 * The Google analytics helper class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Helper extends Base {

	/**
	 * Get the post types where we can show analytics meta box.
	 *
	 * Use `beehive_google_analytics_post_types` filter to add support
	 * for anoher custom post type.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function post_types() {
		$post_types = [ 'post', 'page' ];

		/**
		 * Filter to add/remove custom post types from analytics meta box.
		 *
		 * Use this filter to show stats data in a custom post type edit screen.
		 *
		 * @param array $post_types Post types.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_analytics_post_types', $post_types );
	}

	/**
	 * Check if current site can see analytics data.
	 *
	 * If current site is not logged in, we can access the stats
	 * data using network creds. Only in multisite.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function can_get_stats( $network = false ) {
		// Get current source.
		$source = $this->login_source( $network );

		// Network flag.
		$network = ( 'network' === $source ? true : false );

		// Try to get the logged in status.
		$can = Auth_Helper::instance()->is_logged_in( $network );

		/**
		 * Filter hook to modify the stats cap flag.
		 *
		 * @param bool $can Can get stats.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_can_get_stats', $can );
	}

	/**
	 * Check if current site can see analytics data.
	 *
	 * If current site is not logged in, we can access the stats
	 * data using network creds. Only in multisite.
	 * When network admin is already logged in and subsite admin not,
	 * we can use network admin's login to get stats for the subsite.
	 * But subsite admins can see only their site's stats.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function login_source( $network = false ) {
		// Default source is single site.
		$source = 'single';

		// Only valid if mutisite.
		if ( is_multisite() ) {
			// Network admin always require network credentials.
			if ( $network ) {
				$source = 'network';
			} else {
				// Is plugin active network wide.
				$network_wide = General::is_networkwide();

				// Login flag for single site.
				$loggedin = Auth_Helper::instance()->is_logged_in();
				// Login status network wide.
				$network_loggedin = Auth_Helper::instance()->is_logged_in( true );

				// Network admin logged in, subsite admin not.
				if ( ! $loggedin && $network_loggedin && $network_wide ) {
					$source = 'network';
				}
			}
		}

		/**
		 * Filter the login source for analytics report.
		 *
		 * @param string $source Source (network or single).
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_analytics_login_source', $source );
	}
}