<?php

namespace Beehive\Core\Utils\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Exception;
use Google_Service_Exception;
use Beehive\Core\Helpers\Cache;
use Beehive\Core\Views\Settings;

/**
 * The Google API class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
abstract class Google_API extends Base {

	/**
	 * Check cache if not forced to get from API.
	 *
	 * This is a helper function to avoid duplicating cache checks
	 * within other methods.
	 *
	 * @param string $name      Cache key.
	 * @param bool   $network   Network flag.
	 * @param bool   $force     Force from API.
	 * @param bool   $transient Should check transient.
	 *
	 * @since 3.2.0
	 *
	 * @return array|bool|mixed
	 */
	protected function cache( $name, $network, $force = false, $transient = true ) {
		// If not forced, try cache.
		if ( ! $force ) {
			// Try to get from cache.
			return Cache::get_cache( $name, $network, $transient );
		}

		return [];
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

		// Get errors array.
		$errors = method_exists( $error, 'getErrors' ) ? $error->getErrors() : [];

		// Add error notification.
		if ( ! empty( $errors[0]['message'] ) ) {
			$message = $errors[0]['message'];

			// Do not show multiple errors.
			if ( ! has_action( 'beehive_google_account_notice' ) && 'User does not have any Google Analytics account.' !== $message ) {
				// Show a notice in Google account settings.
				add_action( 'beehive_google_account_notice', function () use ( $message ) {
					Settings::instance()->notice( $message, 'error', false );
				} );
			}
		}

		// Process the error code.
		switch ( $error->getCode() ) {
			case 401:
				// Set a flag that login is required.
				if ( false === $logged_in || $logged_in > 0 ) {
					beehive_analytics()->settings->update( 'logged_in', 1, 'google_login', $this->is_network() );
				}
				break;
			case 403:
				// User doesn't have analytics accounts.
				break;
		}

		return true;
	}
}