<?php
/**
 * The Google analytics helper class.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Analytics
 */

namespace Beehive\Core\Modules\Google_Analytics;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Auth\Helper as Auth_Helper;

/**
 * Class Helper
 *
 * @package Beehive\Core\Modules\Google_Analytics
 */
class Helper extends Base {

	/**
	 * List of plugin stats pages.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Moved to module.
	 *
	 * @var array $plugin_pages
	 */
	public static $stats_pages = array(
		'statistics_page_beehive-google-analytics',
		'statistics_page_beehive-google-analytics-network',
	);

	/**
	 * List of ga settings pages.
	 *
	 * @since 3.3.0
	 *
	 * @var array $settings_pages
	 */
	public static $settings_pages = array(
		'dashboard_page_beehive-google-analytics',
		'dashboard_page_beehive-google-analytics-network',
	);

	/**
	 * Get the post types where we can show analytics meta box.
	 *
	 * Use `beehive_google_analytics_post_types` filter to add support
	 * for another custom post type.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function post_types() {
		$post_types = array( 'post', 'page' );

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
	 * @param bool            $network   Network flag.
	 * @param \Exception|bool $exception Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function can_get_stats( $network = false, &$exception = false ) {
		// Get current source.
		$source = $this->login_source( $network );

		// Network flag.
		$network = 'network' === $source;

		// Try to get the logged in status.
		$can = Auth_Helper::instance()->is_logged_in( $network );

		/**
		 * Filter hook to modify the stats cap flag.
		 *
		 * @param bool $can Can get stats.
		 *
		 * @since 3.2.0
		 */
		$can = apply_filters( 'beehive_google_can_get_stats', $can );

		// Setup an exception for not logged in status.
		if ( ! $can ) {
			$exception = new \Exception( __( 'You need to authenticate with your Google account to enable access to statistics.', 'ga_trans' ) );
		}

		return $can;
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
		// Using strpos to support translation - https://incsub.atlassian.net/browse/BEE-15.
		return isset( $current_screen->id ) && strpos( $current_screen->id, 'page_beehive-google-analytics' );
	}

	/**
	 * Check if current page is Google Analytics settings page.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public static function is_ga_settings() {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin ga settings page.
		// Using strpos to support translation - https://incsub.atlassian.net/browse/BEE-15.
		return isset( $current_screen->id ) && strpos( $current_screen->id, 'page_beehive-ga-settings' );
	}

	/**
	 * Get the previous date data from the current period.
	 *
	 * @param string $from   From date.
	 * @param string $to     To date.
	 * @param string $format Date format.
	 *
	 * @since 3.2.7 Moved to helper.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public static function get_previous_period( $from, $to, $format = 'Y-m-d' ) {
		try {
			// Make sure the dates are in proper format.
			$from = gmdate( $format, strtotime( $from ) );
			$to   = gmdate( $format, strtotime( $to ) );

			// Create date objects from the periods.
			$date_from = date_create( $from );
			$date_to   = date_create( $to );
			// Get the difference between periods.
			$days = (int) date_diff( $date_from, $date_to )->days;

			if ( $days > 0 ) {
				$previous_from = gmdate( $format, strtotime( $from . ' -' . ( $days + 1 ) . ' days' ) );
				$previous_to   = gmdate( $format, strtotime( $from . ' -1 days' ) );
			} else {
				$previous_from = gmdate( $format, strtotime( $from . ' -1 days' ) );
				$previous_to   = $previous_from;
			}
		} catch ( \Exception $e ) {
			$previous_from = false;
			$previous_to   = false;
		}

		return array(
			'from' => $previous_from,
			'to'   => $previous_to,
		);
	}

	/**
	 * Get the all statistics page url.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	public static function statistics_url( $network = false ) {
		// Get base url.
		$url = $network ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' );

		$url = add_query_arg(
			array(
				'page' => 'beehive-google-analytics',
			),
			$url
		);

		/**
		 * Filter to modify GA statistics url
		 *
		 * @param string $url     Statistics url.
		 * @param bool   $network Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_ga_statistics_url', $url, $network );
	}

	/**
	 * Get the GA settings url.
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

		// Get page.
		$url = add_query_arg(
			array(
				'page' => 'beehive-ga-settings',
			),
			$url
		);

		// Append tab.
		$url = $url . '#/' . $tab;

		/**
		 * Filter to modify main url used to build GA settings url
		 *
		 * @param string $url     Settings URL.
		 * @param bool   $network Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_ga_settings_url', $url, $network );
	}
}