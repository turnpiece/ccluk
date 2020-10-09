<?php
/**
 * The Google general data class.
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

use Exception;
use Beehive\Core\Helpers\Cache;
use Beehive\Core\Helpers\General;
use Beehive\Google_Service_Exception;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\Google_API;
use Beehive\Google_Service_Analytics_Profile;
use Beehive\Google_Service_Analytics as Google_Analytics;

/**
 * Class Data
 *
 * @package Beehive\Core\Modules\Google_Analytics
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
					Cache::set_transient( 'google_profiles', $profiles, $network );
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
				$profiles = array();

				// Process the exception.
				$this->error( $e );
			} catch ( Exception $e ) {
				$profiles = array();

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

	/**
	 * Get available profiles from current GA account.
	 *
	 * This is a wrapper function to display dropdowns in plugin
	 * admin pages.
	 *
	 * @param bool $network Is network wide?.
	 * @param bool $force   Should skip cache?.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Moved to this class.
	 *
	 * @return array
	 */
	public function profiles_list( $network = false, $force = false ) {
		$list   = array();
		$update = false;

		// Make sure the autoloader is ready.
		General::vendor_autoload();

		// Get available profiles.
		$profiles = $this->profiles( $network, $force );

		// Get settings.
		$settings = beehive_analytics()->settings->get_options( false, $network, $force );

		// Current website url.
		$current_url = untrailingslashit( get_site_url() );

		// Remove protocols.
		$current_url = str_replace( array( 'http://', 'https://' ), '', $current_url );

		foreach ( $profiles as $profile ) {
			// Get profile website url.
			$website_url = untrailingslashit( $profile->getWebsiteUrl() );

			// Remove protocols.
			$website_url = str_replace( array( 'http://', 'https://' ), '', $website_url );

			// Perform some extra actions if website url is matching.
			if ( $current_url === $website_url ) {
				// Set tracking ID.
				$settings['misc']['auto_track'] = $profile->getWebPropertyId();

				// Update account id if website url is matched.
				if ( empty( $settings['google']['account_id'] ) ) {
					$settings['google']['account_id'] = $profile->getId();
				}

				// Should update settings.
				$update = true;
			}

			// If no account is matched, select first one.
			if ( empty( $settings['google']['account_id'] ) ) {
				$settings['google']['account_id'] = $profiles[0]->getId();

				// Should update settings.
				$update = true;
			}

			// Update settings.
			if ( $update ) {
				beehive_analytics()->settings->update_options( $settings, $network );
			}

			$list[] = array(
				'id'       => $profile->getId(),
				'url'      => $profile->getWebsiteUrl(),
				'name'     => $profile->getName(),
				'property' => $profile->getWebPropertyId(),
			);
		}

		/**
		 * Filter hook to modify available profiles dropdown.
		 *
		 * @param array $profiles Profiles list.
		 * @param bool  $network  Is network level.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_profiles_list', $list, $network );
	}
}