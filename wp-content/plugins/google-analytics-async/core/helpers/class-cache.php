<?php

namespace Beehive\Core\Helpers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Define the cache helper.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Cache {

	/**
	 * Cache group name.
	 *
	 * @since 3.2.0
	 */
	const CACHE_GROUP = 'beehive_cache';

	/**
	 * Cache key for cache version.
	 *
	 * @since 3.2.0
	 */
	const CACHE_VERSION_KEY = 'beehive_cache_version';

	/**
	 * Transient expiry time for cache.
	 *
	 * By default it's 24 hours.
	 *
	 * @since 3.2.0
	 */
	const CACHE_EXPIRY = 86400;

	/**
	 * Generate cache key for object cache.
	 *
	 * Used to set different key for network level data.
	 *
	 * @param string $name    Name
	 * @param bool   $network Is network level?.
	 *
	 * @since 3.2.0
	 *
	 * @return string $name
	 */
	public static function cache_key( $name, $network = false ) {
		if ( $network ) {
			// Create unique string from args.
			$name = $name . '_network';
		}

		return md5( json_encode( $name ) );
	}

	/**
	 * Wrapper for wp_cache_set in Beehive.
	 *
	 * Set cache using this method so that we can delete them without
	 * flushing the object cache as whole. This cache can be deleted
	 * using normal wp_cache_delete.
	 *
	 * @param int|string $key       The cache key to use for retrieval later.
	 * @param mixed      $data      The contents to store in the cache.
	 * @param bool       $network   Network flag (useful for transient).
	 * @param bool       $transient Should set to transient also?.
	 * @param string     $group     Optional. Where to group the cache contents.
	 *                              Enables the same key to be used across groups.
	 * @param int        $expire    Optional. When to expire the cache contents, in seconds.
	 *                              Default 0 (no expiration).
	 *
	 * @since 3.2.0
	 *
	 * @return bool False on failure, true on success.
	 */
	public static function set_cache( $key, $data, $network = false, $transient = true, $group = self::CACHE_GROUP, $expire = 0 ) {
		// Check if caching disabled.
		if ( ! self::can_cache() ) {
			return false;
		}

		// Get the current version.
		$version = wp_cache_get( self::CACHE_VERSION_KEY );

		// Get the expiry time.
		$expire = self::expiry( $expire, $key );

		// In case version is not set, set now.
		if ( empty( $version ) ) {
			// In case version is not set, use default 1.
			$version = 1;

			// Set cache version.
			wp_cache_set( self::CACHE_VERSION_KEY, $version );
		}

		// Set to transient.
		if ( $transient && self::can_transient() ) {
			self::set_transient( $key, $data, $version, $network, $expire );
		}

		// Add to cache array with version.
		$data = array(
			'data'    => $data,
			'version' => $version,
		);

		// Set to WP cache.
		return wp_cache_set( self::cache_key( $key, $network ), $data, $group, $expire );
	}

	/**
	 * Wrapper for get_transient function in Beehive.
	 *
	 * Use this to get the cache values set using set_cache method.
	 *
	 * @param string $key           Transient name. Expected to not be SQL-escaped. Must be
	 *                              172 characters or fewer in length.
	 * @param mixed  $data          Transient value. Must be serializable if non-scalar.
	 *                              Expected to not be SQL-escaped.
	 * @param int    $version       Transient version.
	 * @param bool   $network       Network flag.
	 * @param int    $expire        Optional. When to expire the cache contents, in seconds.
	 *                              Default 0 (no expiration).
	 *
	 * @since 3.2.0
	 *
	 * @return bool|mixed False on failure or the transient content.
	 */
	public static function set_transient( $key, $data, $version, $network = false, $expire = 0 ) {
		// Check if caching disabled.
		if ( ! self::can_transient() ) {
			return false;
		}

		// Add to cache array with version.
		$data = array(
			'data'    => $data,
			'version' => $version,
		);

		if ( $network ) {
			// Set transient version.
			set_site_transient( self::CACHE_VERSION_KEY, $version, $expire );

			// Set transient data.
			return set_site_transient( self::cache_key( $key ), $data, $expire );
		} else {
			// Set transient version.
			set_transient( self::CACHE_VERSION_KEY, $version, $expire );

			// Set transient data.
			return set_transient( self::cache_key( $key ), $data, $expire );
		}
	}

	/**
	 * Wrapper for wp_cache_get function in Beehive.
	 *
	 * Use this to get the cache values set using set_cache method.
	 *
	 * @param int|string $key       The key under which the cache contents are stored.
	 * @param bool       $network   Network flag (useful for transient).
	 * @param bool       $transient Should try transient also?.
	 * @param string     $group     Optional. Where the cache contents are grouped.
	 * @param bool       $force     Optional. Whether to force an update of the local
	 *                              cache from the persistent cache. Default false.
	 * @param bool       $found     Optional. Whether the key was found in the cache (passed by reference).
	 *                              Disambiguate a return of false, a storable value. Default null.
	 *
	 * @since 3.2.0
	 *
	 * @return bool|mixed False on failure to retrieve contents or the cache
	 *                      contents on success
	 */
	public static function get_cache( $key, $network = false, $transient = true, $group = self::CACHE_GROUP, $force = false, &$found = null ) {
		// Check if caching disabled.
		if ( ! self::can_cache() ) {
			return false;
		}

		// Get the current version.
		$version = wp_cache_get( self::CACHE_VERSION_KEY );

		// Do not continue if version is not set.
		if ( ! empty( $version ) ) {
			// Get the cache value.
			$data = wp_cache_get( self::cache_key( $key, $network ), $group, $force, $found );

			// Return only data.
			if ( isset( $data['version'] ) && $version === $data['version'] && ! empty( $data['data'] ) ) {
				return $data['data'];
			}
		}

		// Try to get from transient.
		if ( $transient && self::can_transient() ) {
			return self::get_transient( $key, $network );
		}

		return false;
	}

	/**
	 * Wrapper for get_transient function in Beehive.
	 *
	 * Use this to get the transient values set using set_transient method.
	 *
	 * @param int|string $key     The key under which the cache contents are stored.
	 * @param bool       $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool|mixed False on failure or the transient content.
	 */
	public static function get_transient( $key, $network = false ) {
		// Check if caching disabled.
		if ( ! self::can_transient() ) {
			return false;
		}

		// Get transient version.
		$version = $network ? get_site_transient( self::CACHE_VERSION_KEY ) : get_transient( self::CACHE_VERSION_KEY );

		if ( ! empty( $version ) ) {
			// Format key.
			$key = self::cache_key( $key );

			// Get transient data.
			$data = $network ? get_site_transient( $key ) : get_transient( $key );

			// Return only data.
			if ( isset( $data['version'] ) && (int) $version === (int) $data['version'] && isset( $data['data'] ) ) {
				return $data['data'];
			}
		}

		return false;
	}

	/**
	 * Delete a single item from the cache.
	 *
	 * This is a wrapper function for wp_cache_delete, but we
	 * also delete the data from transient.
	 *
	 * @param int|string $key       The key under which the cache contents are stored.
	 * @param bool       $transient Should delete transient also?.
	 * @param bool       $network   Network flag.
	 * @param string     $group     Optional. Where the cache contents are grouped.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function delete_cache( $key, $transient = true, $network = false, $group = self::CACHE_GROUP ) {
		// Delete object cache.
		$deleted = wp_cache_delete( self::cache_key( $key, $network ), $group );

		// Delete transient.
		if ( $transient ) {
			// Generate transient key.
			$key = self::cache_key( $key );
			// Delete transient.
			$deleted = $network ? delete_site_transient( $key ) : delete_transient( $key );
		}

		return $deleted;
	}

	/**
	 * Refresh the whole Beehive cache.
	 *
	 * We can not delete the cache by group. So use
	 * this method to refresh the cache using version.
	 *
	 * @param bool $transient Should refresh transient also?.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public static function refresh_cache( $transient = true ) {
		// Check if caching disabled.
		if ( ! self::can_cache() ) {
			return false;
		}

		// Increment the version.
		$inc = wp_cache_incr( self::CACHE_VERSION_KEY );

		if ( $transient && self::can_transient() ) {
			// Transient version.
			$version = (int) get_transient( self::CACHE_VERSION_KEY );

			// Update with new version.
			$inc = set_transient( self::CACHE_VERSION_KEY, $version + 1, self::expiry( self::CACHE_VERSION_KEY ) );
		}

		return $inc ? true : false;
	}

	/**
	 * Cache flushing wrapper.
	 *
	 * This is here because object cache flushes can be prevented.
	 * If in case wp_cache_flush function is disabled we will try
	 * to flush it directly.
	 *
	 * @since 3.2.0
	 */
	public static function flush_cache() {
		global $wp_object_cache;

		// In some cases.
		if ( is_object( $wp_object_cache ) && is_callable( array( $wp_object_cache, 'flush' ) ) ) {
			$wp_object_cache->flush();
		} elseif ( is_callable( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}

	/**
	 * Check if we can cache the objects.
	 *
	 * Object caching can be disabled by returning false to
	 * beehive_enable_cache filter.
	 *
	 * @since 3.2.0
	 *
	 * @return bool $enable_cache
	 */
	private static function can_cache() {
		/**
		 * Make caching controllable.
		 *
		 * By default we can cache.
		 *
		 * @param bool $enable_cache Should cache?.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_enable_cache', true );
	}

	/**
	 * Check if we can cache the objects.
	 *
	 * Object caching can be disabled by returning false to
	 * beehive_enable_cache filter.
	 *
	 * @since 3.2.0
	 *
	 * @return bool $enable_cache
	 */
	private static function can_transient() {
		// Use cache flag.
		$enable = self::can_cache();

		/**
		 * Make transient caching controllable.
		 *
		 * By default we it will be same as cache flag.
		 *
		 * @param bool $enable_cache Should cache using transient?.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_enable_transient', $enable );
	}

	/**
	 * Get transient and object cache expiry time.
	 *
	 * This value can be overriden using beehive_cache_expiry filter.
	 *
	 * @param int    $expire        Optional. When to expire the cache contents, in seconds.
	 *                              Default 0 (no expiration).
	 * @param string $key           Transient key.
	 *
	 * @since 3.2.0
	 *
	 * @return int
	 */
	private static function expiry( $expire = 0, $key = '' ) {
		// Get default expiry if not set.
		$expire = empty( $expire ) ? self::CACHE_EXPIRY : $expire;

		/**
		 * See beehive_cache_expiry.
		 *
		 * @deprecated 3.2.0
		 */
		$expire = apply_filters_deprecated(
			'ga_cache_timeout',
			[ $expire ],
			'3.2.0',
			'beehive_cache_expiry'
		);

		/**
		 * Change cache expiry time.
		 *
		 * @param bool   $expire Expiry time.
		 * @param string $key    Cache key (useful to override specific expiry).
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_cache_expiry', $expire, $key );
	}
}