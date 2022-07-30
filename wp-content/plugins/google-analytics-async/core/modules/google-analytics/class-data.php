<?php
/**
 * The Google general data class.
 *
 * @link    http://wpmudev.com
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
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\Google_API;
use Beehive\Google\Service\Analytics\Profile;
use Beehive\Google\Service\GoogleAnalyticsAdmin;
use Beehive\Google\Service\Analytics as Google_Analytics;
use Beehive\Google\Service\Exception as Google_Exception;

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
	 * @since 3.2.0
	 *
	 * @param bool $network Is network wide?.
	 * @param bool $force   Should skip cache?.
	 *
	 * @return Profile[] $profiles.
	 */
	public function profiles( $network = false, $force = false ) {
		// Check cache.
		$profiles = $this->cache( 'google_profiles_v3313', $network, $force );

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
				 * Filter to modify the list of Google profiles before caching.
				 *
				 * @since 3.2.0
				 *
				 * @param array $profiles Profiles array.
				 */
				$profiles = apply_filters( 'beehive_google_profiles', $profiles->getItems() );

				// Set the results to cache.
				if ( ! empty( $profiles ) ) {
					Cache::set_transient( 'google_profiles_v3313', $profiles, $network );
				}

				/**
				 * Action hook to execute after fetching Google profiles list.
				 *
				 * @since 3.2.0
				 *
				 * @param array $profiles Profiles list.
				 */
				do_action( 'beehive_after_google_profiles', $profiles );
			} catch ( Google_Exception $e ) {
				$profiles = array();

				// Process the exception.
				$this->error( $e, $network, false );
			} catch ( Exception $e ) {
				$profiles = array();

				// Process the exception.
				$this->error( $e, $network, false );
			}
		}

		/**
		 * Filter hook to modify available profiles.
		 *
		 * @since 3.2.0
		 *
		 * @param bool  $network  Is network level.
		 *
		 * @param array $profiles Profiles array.
		 */
		return apply_filters( 'beehive_google_analytics_profiles', $profiles, $network );
	}

	/**
	 * Get available GA4 streams from current GA account.
	 *
	 * @since 3.4.0
	 *
	 * @param bool $network Is network wide?.
	 * @param bool $force   Should skip cache?.
	 *
	 * @return array
	 */
	public function streams( $network = false, $force = false ) {
		// Check cache.
		$streams = $this->cache( 'google_streams_v3400', $network, $force );

		// Nothing in cache.
		if ( empty( $streams ) ) {
			$streams = array();
			// Make sure the autoloader is ready.
			General::vendor_autoload();

			// Make sure we don't break.
			try {
				// Set auth data.
				Google_Auth\Helper::instance()->setup_auth( $network );

				// Analytics V4 instance.
				$analytics_admin = new GoogleAnalyticsAdmin( Google_Auth\Auth::instance()->client() );

				// Get the list of streams (profiles) for the authorized user.
				$accounts = $analytics_admin->accountSummaries->listAccountSummaries()->getAccountSummaries(); // phpcs:ignore

				if ( ! empty( $accounts ) ) {
					foreach ( $accounts as $account ) {
						$properties = $account->getPropertySummaries();
						if ( ! empty( $properties ) ) {
							foreach ( $properties as $property ) {
								// phpcs:ignore
								$data_streams = $analytics_admin->properties_dataStreams
									->listPropertiesDataStreams( $property->getProperty() )
									->getDataStreams();

								if ( ! empty( $data_streams ) ) {
									foreach ( $data_streams as $data_stream ) {
										$web_stream = $data_stream->getWebStreamData();

										if ( 'WEB_DATA_STREAM' === $data_stream->type ) {
											$streams[ $data_stream->getName() ] = array(
												'title'    => $data_stream->getDisplayName(),
												'name'     => $data_stream->getName(),
												'url'      => $web_stream->getDefaultUri(),
												'property' => $property->getProperty(),
												'measurement' => $web_stream->getMeasurementId(),
											);
										}
									}
								}
							}
						}
					}
				}

				/**
				 * Filter to modify the list of Google streams before caching.
				 *
				 * @since 3.4.0
				 *
				 * @param array $profiles Profiles array.
				 */
				$streams = apply_filters( 'beehive_google_streams', $streams );

				// Set the results to cache.
				if ( ! empty( $streams ) ) {
					Cache::set_transient( 'google_streams_v3400', $streams, $network );

					// Sync streams.
					$this->sync_streams( $streams, $network );
				}

				/**
				 * Action hook to execute after fetching Google streams list.
				 *
				 * @since 3.4.0
				 *
				 * @param array $streams Streams list.
				 * @param bool  $network Network flag.
				 */
				do_action( 'beehive_after_google_streams', $streams, $network );
			} catch ( Google_Exception $e ) {
				$streams = array();

				// Process the exception.
				$this->error( $e );
			} catch ( Exception $e ) {
				$streams = array();

				// Process the exception.
				$this->error( $e );
			}
		}

		/**
		 * Filter hook to modify available streams.
		 *
		 * @since 3.4.0
		 *
		 * @param array $streams Streams array.
		 * @param bool  $network Is network level.
		 */
		return apply_filters( 'beehive_google_analytics_streams', $streams, $network );
	}

	/**
	 * Get available profiles from current GA account.
	 *
	 * This is a wrapper function to display dropdowns in plugin
	 * admin pages.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Moved to this class.
	 *
	 * @param bool $network Is network wide?.
	 * @param bool $force   Should skip cache?.
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

		foreach ( $profiles as $profile ) {
			if ( $this->is_url_matching( untrailingslashit( $profile->getWebsiteUrl() ) ) ) {
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
		 * @since 3.2.0
		 *
		 * @param bool  $network  Is network level.
		 *
		 * @param array $profiles Profiles list.
		 */
		return apply_filters( 'beehive_google_profiles_list', $list, $network );
	}

	/**
	 * Check if a URL is matching current site's URL.
	 *
	 * @since 3.4.0
	 *
	 * @param string $url URL to check.
	 *
	 * @return array
	 */
	private function is_url_matching( $url ) {
		// Current website url.
		$current_url = untrailingslashit( get_site_url() );

		// Remove protocols.
		$url         = str_replace( array( 'http://', 'https://' ), '', $url );
		$current_url = str_replace( array( 'http://', 'https://' ), '', $current_url );

		/**
		 * Filter hook to modify current url.
		 *
		 * @since 3.3.8
		 *
		 * @param string $current_url Current URL.
		 */
		$current_url = apply_filters( 'beehive_ga_data_current_url', $current_url );

		// Check if URLs are matching.
		$matching = $current_url === $url;

		/**
		 * Filter to modify url matching logic.
		 *
		 * @since 3.3.8
		 * @since 3.4.0 $network param is deprecated.
		 *
		 * @param string $current_url Current URL.
		 * @param string $website_url URL from API result.
		 * @param bool   $network     @deprecated from 3.4.0
		 *
		 * @param bool   $matching    Is matching?.
		 */
		return apply_filters( 'beehive_ga_data_current_url_matching', $matching, $current_url, $url, false );
	}

	/**
	 * Sync streams to auto update stream ID.
	 *
	 * @since 3.4.0
	 *
	 * @param array $streams Streams.
	 * @param bool  $network Is network wide?.
	 *
	 * @return void
	 */
	private function sync_streams( $streams, $network = false ) {
		$update = false;

		// Get settings.
		$settings = beehive_analytics()->settings->get_options( false, $network );

		foreach ( $streams as $stream ) {
			if ( $this->is_url_matching( untrailingslashit( $stream['url'] ) ) ) {
				// Set tracking ID.
				if ( empty( $settings['misc']['auto_track_ga4'] ) || $stream['measurement'] !== $settings['misc']['auto_track_ga4'] ) {
					$settings['misc']['auto_track_ga4'] = $stream['measurement'];
					// Should update settings.
					$update = true;
				}

				// Update account id if website url is matched.
				if ( empty( $settings['google']['stream'] ) ) {
					$settings['google']['stream'] = $stream['name'];
					// Should update settings.
					$update = true;
				}
			}

			// If no account is matched, select first one.
			if ( empty( $settings['google']['stream'] ) ) {
				$settings['google']['stream'] = $stream['name'];

				// Should update settings.
				$update = true;
			}

			// Update settings.
			if ( $update ) {
				beehive_analytics()->settings->update_options( $settings, $network );
			}
		}
	}
}