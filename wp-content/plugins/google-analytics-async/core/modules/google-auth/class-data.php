<?php
/**
 * The Google general data class.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Auth
 */

namespace Beehive\Core\Modules\Google_Auth;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Exception;
use Beehive\Google\Service\Exception as Google_Exception;
use Beehive\Core\Utils\Abstracts\Google_API;
use Beehive\Google\Service\PeopleService as Google_People;

/**
 * Class Data
 *
 * @package Beehive\Core\Modules\Google_Auth
 */
class Data extends Google_API {

	/**
	 * Get the default credentials for the Beehive app.
	 *
	 * In order to load-balance the API request limit, we need
	 * to use multiple API projects in Beehive.
	 * Weight is required to priortize the selection. Higher weighted
	 * key pair gets higher priority.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function credentials() {
		// Default credentials.
		$creds = array(
			'640050123521-r5bp4142nh6dkh8bn0e6sn3pv852v3fm.apps.googleusercontent.com' => array(
				'secret' => 'wWEelqN4DvE2DJjUPp-4KSka',
				'weight' => 1,
			),
			'600314239770-5huksonskhpspttt9euamsd2vfv3m0gr.apps.googleusercontent.com' => array(
				'secret' => 'z02i4rsfhTLNC0hYjLJgn5P_',
				'weight' => 5,
			),
			'928518476274-818pcuoanph73nduovspp3g9gvs8u3ho.apps.googleusercontent.com' => array(
				'secret' => 'iG7m1aeBsZ7S1WWcrC_YUzNN',
				'weight' => 5,
			),
		);

		/**
		 * Filter to add/remove default API credentials.
		 *
		 * @param array $creds Credentials.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_google_default_credentials', $creds );
	}

	/**
	 * Get authenticated user's name and email address.
	 *
	 * @param bool $network Is network wide?.
	 * @param bool $force   Should skip cache?.
	 *
	 * @since 3.2.0
	 *
	 * @return array $user { 'name', 'email' }.
	 */
	public function user( $network = false, $force = false ) {
		// Get user data from db.
		$user = beehive_analytics()->settings->get_options( 'google_login', $network );

		// Not found in db.
		if ( empty( $user['name'] ) || $force ) {
			// Make sure we don't break.
			try {
				// Setup required things.
				Helper::instance()->setup_auth( $network );

				// Google People instance.
				$people = new Google_People( Auth::instance()->client() );

				// Get name and email address.
				$person = $people->people->get(
					'people/me',
					array( 'personFields' => 'names,emailAddresses,photos' )
				);

				// Could not find.
				if ( empty( $person ) ) {
					return array();
				}

				// Get names.
				$names = $person->getNames();
				// Get emails.
				$emails = $person->getEmailAddresses();
				// Get photos.
				$photos = $person->getPhotos();

				// Set data.
				$user = array(
					'name'  => isset( $names[0] ) ? $names[0]->getDisplayName() : '',
					'email' => isset( $emails[0] ) ? $emails[0]->getValue() : '',
					'photo' => isset( $photos[0] ) ? $photos[0]->getUrl() : '',
				);

				// Get existing values.
				$google_login = beehive_analytics()->settings->get_options( 'google_login', $network );

				// Update settings.
				$google_login['name']  = $user['name'];
				$google_login['email'] = $user['email'];
				$google_login['photo'] = $user['photo'];

				// Update the settings.
				beehive_analytics()->settings->update_group( $google_login, 'google_login', $network );

				/**
				 * Action hook to execute after fetching Google profiles list.
				 *
				 * @param array $user User data.
				 *
				 * @since 3.2.0
				 */
				do_action( 'beehive_after_google_user_fetch', $user );
			} catch ( Google_Exception $e ) {
				$user = array();

				// Process the exception.
				$this->error( $e );
			} catch ( Exception $e ) {
				$user = array();

				// Process the exception.
				$this->error( $e );
			}
		}

		/**
		 * Filter hook to modify user data.
		 *
		 * @param array $user    User data array (this includes Google login data also).
		 * @param bool  $network Is network level.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_user', $user, $network );
	}
}