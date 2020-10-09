<?php
/**
 * The Google API class.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Utils\Abstracts
 */

namespace Beehive\Core\Utils\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Exception;
use Google_Service_Exception;
use Beehive\Core\Helpers\Cache;

/**
 * Class Google_API
 *
 * @package Beehive\Core\Utils\Abstracts
 */
abstract class Google_API extends Base {

	/**
	 * Check cache if not forced to get from API.
	 *
	 * This is a helper function to avoid duplicating cache checks
	 * within other methods.
	 *
	 * @param string $name              Cache key.
	 * @param bool   $network           Network flag.
	 * @param bool   $force             Force from API.
	 * @param bool   $object_cache_only Should check only object cache (not transient).
	 *
	 * @since 3.2.0
	 *
	 * @return array|bool|mixed
	 */
	protected function cache( $name, $network, $force = false, $object_cache_only = false ) {
		// If not forced, try transient.
		if ( ! $force ) {
			// Try to get from transient.
			if ( $object_cache_only ) {
				return Cache::get_cache( $name, $network );
			} else {
				return Cache::get_transient( $name, $network );
			}
		}

		return array();
	}

	/**
	 * Process the Google API error.
	 *
	 * @param Google_Service_Exception|Exception $error Error instance.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	protected function error( $error ) {
		// Get logged in status.
		$logged_in = beehive_analytics()->settings->get( 'logged_in', 'google_login', $this->is_network() );

		// Log the error to php error log.
		if ( apply_filters( 'beehive_google_api_error_log', false ) ) {
			// phpcs:ignore
			error_log( print_r( $error->getMessage(), true ) );
		}

		// Process the error code.
		switch ( $error->getCode() ) {
			case 401:
			case 403:
				// User doesn't have analytics accounts.
				break;
		}

		return true;
	}
}