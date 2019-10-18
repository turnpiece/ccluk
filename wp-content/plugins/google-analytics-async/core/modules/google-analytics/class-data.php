<?php

namespace Beehive\Core\Modules\Google_Analytics;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Exception;
use Google_Service_Exception;
use Beehive\Core\Helpers\Cache;
use Google_Service_Analytics_Profile;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\Google_API;
use Google_Service_Analytics as Google_Analytics;

/**
 * The Google general data class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Data extends Google_API {

	/**
	 * Get available profiles from current GA account.
	 *
	 * We need to get all available profiles for the authorized
	 * user. So the account and property ids are given as `~all`.
	 *
	 * @param bool $network Is network wide?.
	 * @param bool $force   Should skip cache?.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_Analytics_Profile[] $profiles.
	 */
	public function profiles( $network = false, $force = false ) {
		// Check cache.
		$profiles = $this->cache( 'google_profiles', $network, $force );

		// Nothing in cache.
		if ( empty( $profiles ) ) {
			// Make sure we don't break.
			try {
				// Set auth data.
				Google_Auth\Helper::instance()->setup_auth( $network );

				// Analytics V3 instance.
				$analytics = new Google_Analytics( Google_Auth\Auth::instance()->client() );

				// Get the list of views (profiles) for the authorized user.
				$profiles = $analytics->management_profiles->listManagementProfiles( '~all', '~all' );

				/**
				 * Google profiles list.
				 *
				 * @param array $profiles Profiles array.
				 *
				 * @since 3.2.0
				 */
				$profiles = apply_filters( 'beehive_google_profiles', $profiles->getItems() );

				// Set the results to cache.
				if ( ! empty( $profiles ) ) {
					Cache::set_cache( 'google_profiles', $profiles, $network );
				}

				/**
				 * Action hook to execute after fetching Google profiles list.
				 *
				 * @param array $profiles Profiles list.
				 *
				 * @since 3.2.0
				 */
				do_action( 'beehive_after_google_profiles', $profiles );
			} catch ( Google_Service_Exception $e ) {
				$profiles = [];

				// Process the exception.
				$this->error( $e );
			} catch ( Exception $e ) {
				$profiles = [];

				// Process the exception.
				$this->error( $e );
			}
		}

		/**
		 * Filter hook to modify available profiles.
		 *
		 * @param array $profiles Profiles array.
		 * @param bool  $network  Is network level.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_analytics_profiles', $profiles, $network );
	}
}