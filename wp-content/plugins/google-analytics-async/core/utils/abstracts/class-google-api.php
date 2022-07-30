<?php
/**
 * The Google API class.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Utils\Abstracts
 */

namespace Beehive\Core\Utils\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Exception;
use Beehive\Google\Service\Exception as Google_Exception;
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
	 * @since 3.2.0
	 *
	 * @param string $name              Cache key.
	 * @param bool   $network           Network flag.
	 * @param bool   $force             Force from API.
	 * @param bool   $object_cache_only Should check only object cache (not transient).
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
	 * @since 3.2.0
	 * @since 3.4.0 Logging error to db.
	 *
	 * @param Google_Exception|Exception $error   Error instance.
	 * @param bool                       $network Network flag.
	 * @param bool                       $log     Should log.
	 *
	 * @return bool
	 */
	protected function error( $error, $network = false, $log = true ) {
		// Log the error to php error log.
		if ( apply_filters( 'beehive_google_api_error_log', false ) ) {
			// phpcs:ignore
			error_log( print_r( $error->getMessage(), true ) );
		}

		// Process the error code.
		if ( $log ) {
			switch ( $error->getCode() ) {
				case 401:
				case 403:
					beehive_analytics()->settings->update( 'api_error', self::get_message( $error ), 'google', $network );
					break;
			}
		}

		return true;
	}

	/**
	 * Get error message from API error.
	 *
	 * @since 3.4.0
	 *
	 * @param Google_Exception|Exception $exception Exception instance.
	 *
	 * @return string
	 */
	public static function get_message( $exception ) {
		$error_message = __( 'Unknown error occurred.', 'ga_trans' );

		if ( $exception instanceof Google_Exception ) {
			$message = $exception->getMessage();
			// Try decoding.
			$message = json_decode( $message, true );
			if ( isset( $message['error']['message'] ) ) {
				$error_message = $message['error']['message'];
			} else {
				$error = $exception->getErrors();
				if ( ! empty( $error[0]['message'] ) ) {
					$error_message = $error[0]['message'];
				}
			}
		} elseif ( $exception instanceof \Exception ) {
			$error_message = $exception->getMessage();
		}

		return $error_message;
	}
}