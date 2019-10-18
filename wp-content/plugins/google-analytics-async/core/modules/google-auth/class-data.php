<?php

namespace Beehive\Core\Modules\Google_Auth;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Exception;
use Google_Service_Exception;
use Beehive\Core\Utils\Abstracts\Google_API;
use Google_Service_PeopleService as Google_People;

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
					[ 'personFields' => 'names,emailAddresses,photos' ]
				);

				// Could not find.
				if ( empty( $person ) ) {
					return [];
				}

				// Get names.
				$names = $person->getNames();
				// Get emails.
				$emails = $person->getEmailAddresses();
				// Get photos.
				$photos = $person->getPhotos();

				// Set data.
				$user = [
					'name'  => isset( $names[0] ) ? $names[0]->getDisplayName() : '',
					'email' => isset( $emails[0] ) ? $emails[0]->getValue() : '',
					'photo' => isset( $photos[0] ) ? $photos[0]->getUrl() : '',
				];

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
			} catch ( Google_Service_Exception $e ) {
				$user = [];

				// Process the exception.
				$this->error( $e );
			} catch ( Exception $e ) {
				$user = [];

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