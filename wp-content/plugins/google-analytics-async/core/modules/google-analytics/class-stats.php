<?php
/**
 * The Google analytics stats class.
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

use Beehive\Core\Helpers\Cache;
use Beehive\Core\Utils\Abstracts\Google_API;
use Beehive\Core\Modules\Google_Analytics\Stats\API;
use Beehive\Core\Modules\Google_Analytics\Stats\Format;
use Beehive\Core\Modules\Google_Analytics\Stats\Request;

/**
 * Class Stats
 *
 * @package Beehive\Core\Modules\Google_Analytics
 */
class Stats extends Google_API {

	/**
	 * Get all stats data.
	 *
	 * We will get data from cache first. If not found in cache,
	 * we will get it from Google API.
	 * Setting Google API request is done before calling this method.
	 *
	 * @param string          $from       Start date.
	 * @param string          $to         End date.
	 * @param string          $type       Stats type (stats, dashboard, front).
	 * @param bool            $network    Network flag.
	 * @param bool            $force      Should skip cache?.
	 * @param bool            $cache_only Only from cache, do not load if cache is empty.
	 * @param \Exception|bool $exception  Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function stats( $from, $to, $type = 'stats', $network = false, $force = false, $cache_only = false, &$exception = false ) {
		$stats = array();

		// Check if logged in.
		if ( Helper::instance()->can_get_stats( $network, $exception ) ) {
			// Cache key.
			$cache_key = $this->cache_key( $from, $to, $type, $network );

			// Try to get the cache value first.
			$stats = $this->cache( $cache_key, $network, $force );

			// If cache data is empty set request.
			if ( empty( $stats ) && ! $cache_only ) {
				$stats = $this->get( $from, $to, $type, $network, 0, $exception );

				// Set to cache.
				Cache::set_transient( $cache_key, $stats, $network );
			}
		}

		/**
		 * Alter the post stats data.
		 *
		 * @param array  $stats   Stats data.
		 * @param string $from    Start date.
		 * @param string $to      End date.
		 * @param bool   $network Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_stats', $stats, $from, $to, $network );
	}

	/**
	 * Get stats data for a post.
	 *
	 * We will get data from cache first. If not found in cache,
	 * we will get it from Google API.
	 * Setting Google API request is done before calling this method.
	 *
	 * @param int             $post_id    Post ID.
	 * @param string          $from       From date.
	 * @param string          $to         To date.
	 * @param bool            $force      Should skip cache?.
	 * @param bool            $cache_only Only from cache, do not load if cache is empty.
	 * @param \Exception|bool $exception  Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function post_stats( $post_id, $from, $to, $force = false, $cache_only = false, &$exception = false ) {
		// Try to get the current post id.
		if ( empty( $post_id ) ) {
			global $post;

			$post_id = $post->ID;
		}

		// Only when post id is set.
		if ( ! empty( $post_id ) && Helper::instance()->can_get_stats( false, $exception ) ) {
			// Cache key.
			$cache_key = $this->cache_key( $from, $to, 'post' ) . '_' . $post_id;

			// Try to get the cache value first.
			$stats = $this->cache( $cache_key, false, $force );

			// If cache data is empty set request.
			if ( empty( $stats ) && ! $cache_only ) {
				// Get post stats.
				$stats = $this->get( $from, $to, 'post', false, $post_id, $exception );

				// Set to cache.
				Cache::set_transient( $cache_key, $stats, false );
			}
		} else {
			$stats = array();
		}

		/**
		 * Alter the post stats data.
		 *
		 * @param array  $stats   Stats data.
		 * @param int    $post_id Post ID.
		 * @param string $from    Start date.
		 * @param string $to      End date.
		 * @param bool   $network Network flag.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_stats_post_stats', $stats, $post_id, $from, $to, false );
	}

	/**
	 * Get the stats reports data from Google.
	 *
	 * Setup required requests for the API request and then
	 * get the data from Google API.
	 * Format the data returned from Google.
	 *
	 * @param string          $from      Start date.
	 * @param string          $to        End date.
	 * @param string          $type      Stats type (stats, dashboard, front, post).
	 * @param bool            $network   Network flag.
	 * @param int             $post_id   Post ID. Applicable only for post stats.
	 * @param \Exception|bool $exception Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function get( $from, $to, $type = 'stats', $network = false, $post_id = 0, &$exception = false ) {
		// Request instance.
		$request = Request::instance();

		// Post stats.
		if ( 'post' === $type ) {
			// Post stats.
			$requests = $request->post( $post_id, $from, $to );
		} else {
			// Set stats request.
			$requests = $request->get( $type, $from, $to, $network );
		}

		// Ok, get from Google.
		$data = API::instance()->process_request_types( $requests, $network, $exception );

		// Set to cache for later use.
		if ( ! empty( $data ) ) {
			/**
			 * Format the stats data for the requested type.
			 *
			 * We need to format the result data into required format.
			 * Google API request response is in raw format.
			 */
			$stats = Format::instance()->format( $data, $type );
		} else {
			$stats = array();
		}

		/**
		 * Filter the Google stats data.
		 *
		 * @param array  $stats   Stats data.
		 * @param string $type    Stats type.
		 * @param bool   $network Network flag.
		 *
		 * @since 3.2.0
		 * @since 3.3.3 Added network param.
		 */
		return apply_filters( 'beehive_google_stats_formatted', $stats, $type, $network );
	}

	/**
	 * Generate custom cache key for the data.
	 *
	 * Make sure the data is unique and accurate using available unique
	 * keys for the current request.
	 *
	 * @param string $from    Start date.
	 * @param string $to      End date.
	 * @param string $type    Stats type (stats, dashboard, front).
	 * @param bool   $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function cache_key( $from, $to, $type = 'stats', $network = false ) {
		// Key items.
		$keys = array(
			'google_analytics_stats', // Base.
			$type, // Stats type.
			$from, // Start date.
			$to, // End date.
			get_current_blog_id(), // Blog ID.
			$network ? 1 : 0, // Network flag.
			beehive_analytics()->settings->get( 'account_id', 'google', $network, '' ), // GA Account.
		);

		// Generate a string from array of keys.
		$key = implode( '_', $keys );

		/**
		 * Filter hook to modify cache key.
		 *
		 * @param string $key Cache key.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_stats_cache_key', $key );
	}
}