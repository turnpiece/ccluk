<?php
/**
 * The Google auth class.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Auth
 */

namespace Beehive\Core\Modules\Google_Auth;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Google_Client;
use Beehive\Core\Helpers\Cache;
use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Google_Service_PeopleService;

/**
 * Class Auth
 *
 * @package Beehive\Core\Modules\Google_Auth
 */
class Auth extends Base {

	/**
	 * Google client instance.
	 *
	 * @var Google_Client
	 *
	 * @since 3.2.0
	 */
	private $client;

	/**
	 * Initialize all sub classes.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Init child class.
		Actions::instance()->init();

		// Views.
		Views\Admin::instance()->init();

		// Register endpoints.
		Endpoints\Auth::instance();
	}

	/**
	 * Getter method for Google_Client instance.
	 *
	 * It will always return the existing client instance.
	 * If you need new instance set $new param as true.
	 *
	 * @param bool $new To get new instance.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Client
	 */
	public function client( $new = false ) {
		// Make sure the autoloader is ready.
		General::vendor_autoload();

		// If requested for new instance.
		if ( $new || ! $this->client instanceof Google_Client ) {
			// Set new instance.
			$this->client = new Google_Client();

			// Set our application name.
			$this->client->setApplicationName( General::plugin_name() );
		}

		return $this->client;
	}

	/**
	 * Setup the Google Client instance.
	 *
	 * Setup the access token, client id and client secret to
	 * the current Google Client instance.
	 * Setup the access type as `offline`.
	 * Setup the scope to Analytics Readonly mode.
	 *
	 * @param bool        $network Network flag.
	 * @param bool|string $client  Client ID.
	 * @param bool|string $secret  Client secret.
	 * @param bool|string $token   Access token.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function setup( $network = false, $client = false, $secret = false, $token = false ) {
		$helper = Helper::instance();

		// Set client id.
		$helper->set_client( $client, $network );

		// Set client secret.
		$helper->set_secret( $secret, $network );

		// Set access token.
		$helper->set_token( $token, $network );

		// Offline access.
		$this->client()->setAccessType( 'offline' );

		// Ask for Approval prompt.
		$this->client()->setApprovalPrompt( 'force' );

		// Incremental auth.
		$this->client()->setIncludeGrantedScopes( true );

		// Set redirect url.
		$helper->set_redirect_url();

		/**
		 * Filter hook to add or remove Google auth scopes.
		 *
		 * See all Google oauth scopes here: https://developers.google.com/identity/protocols/googlescopes
		 *
		 * @param array $scopes Default required scopes.
		 *
		 * @since 3.2.0
		 */
		$scopes = (array) apply_filters( 'beehive_google_auth_scopes', array() );

		// These are always required.
		$required_scopes = array(
			Google_Service_PeopleService::USERINFO_PROFILE,
			Google_Service_PeopleService::USERINFO_EMAIL,
		);

		// Merge all scopes.
		$scopes = array_unique( array_merge( $required_scopes, $scopes ) );

		// Set scopes for the Auth request.
		$this->client()->addScope( $scopes );

		/**
		 * Action hook to execute after setting up Google client.
		 *
		 * @param bool        $network Network flag.
		 * @param bool|string $client  Client ID.
		 * @param bool|string $secret  Client secret.
		 * @param bool|string $token   Access token.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_google_setup', $network, $client, $secret, $token );
	}

	/**
	 * Setup the Google Client instance using default API keys.
	 *
	 * This is not a recommeded method. But if user would like to
	 * connect with Google without API keys, we could let them using
	 * the default hardcoded API keys.
	 * If client ID is specificed, we will use that client ID and it's
	 * client secret pair.
	 *
	 * @param bool   $network Network flag.
	 * @param string $client_id Client ID.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Added client ID param.
	 *
	 * @return void
	 */
	public function setup_default( $network = false, $client_id = '' ) {
		$credential = array(
			'client_id'     => '',
			'client_secret' => '',
		);

		// Get default credentials.
		if ( empty( $client_id ) ) {
			$credential = $this->get_default_credential( $network );
		} else {
			$default_creds = Data::instance()->credentials();
			// Check if the client id exist in default list.
			if ( isset( $default_creds[ $client_id ] ) ) {
				$credential = array(
					'client_id'     => $client_id,
					'client_secret' => $default_creds[ $client_id ]['secret'],
				);
			}
		}

		/**
		 * Filter to change default client ID.
		 *
		 * @param string $default_client_id Default client ID.
		 *
		 * @since 3.0.0
		 */
		$default_client_id = apply_filters( 'beehive_google_default_client_id', $credential['client_id'] );

		/**
		 * Filter to change default client secret.
		 *
		 * @param string $default_client_secret Default client secret.
		 *
		 * @since 3.0.0
		 */
		$default_client_secret = apply_filters( 'beehive_google_default_client_secret', $credential['client_secret'] );

		// Setup using default credentials.
		$this->setup( $network, $default_client_id, $default_client_secret );

		/**
		 * Filter hook to change default redirect url.
		 *
		 * This url is the Google Access code prompt screen url.
		 *
		 * @since 3.2.0
		 */
		$url = apply_filters( 'beehive_google_default_redirect_url', 'urn:ietf:wg:oauth:2.0:oob' );

		// Override redirect url.
		Helper::instance()->set_redirect_url( $url );

		/**
		 * Action hook to execute after setting up Google client using default credentials.
		 *
		 * @param bool $network Network flag.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_google_setup_default', $network );
	}

	/**
	 * Get the default API credentials to load balance.
	 *
	 * We use multiple API keys to load balance the request
	 * limit set by Google. If user is already logged in, get
	 * the keys from the db. Otherwise get a random pair.
	 * Before 3.3.0, we had only one API key pair. So it will take
	 * some time to eventually
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function get_default_credential( $network = false ) {
		static $credentials = null;

		// Only if empty.
		if ( is_null( $credentials ) ) {
			// Check if already logged in.
			$loggedin = Helper::instance()->is_logged_in( $network );

			if ( $loggedin ) {
				// Get the pair from the db.
				$credentials = array(
					'client_id'     => beehive_analytics()->settings->get( 'client_id', 'google_login', $network, '' ),
					'client_secret' => beehive_analytics()->settings->get( 'client_secret', 'google_login', $network, '' ),
				);

				// Backward compatibility for 3.2.6 and below.
				if ( empty( $credentials['client_id'] ) || empty( $credentials['client_secret'] ) ) {
					$credentials = array(
						'client_id'     => '640050123521-r5bp4142nh6dkh8bn0e6sn3pv852v3fm.apps.googleusercontent.com',
						'client_secret' => 'wWEelqN4DvE2DJjUPp-4KSka',
					);
				}
			} else {
				// Get random credentials.
				$credentials = $this->get_random_creds();
			}
		}

		/**
		 * Filter to modify default credentials before processing.
		 *
		 * @param array $credentials Client ID and Client secret.
		 *
		 * @since 3.2.7
		 */
		return apply_filters( 'beehive_google_auth_get_default_credential', $credentials );
	}

	/**
	 * Logout current Google authentication code.
	 *
	 * Logging out will not remove API credentials.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function logout( $network = false ) {
		// Make sure the current user has permission.
		if ( ! Permission::user_can( 'settings', $network ) ) {
			return false;
		}

		// Remove Google login data.
		$logout = beehive_analytics()->settings->update_group( array(), 'google_login', $network );

		// Remove the account id and tracking id.
		if ( $logout ) {
			beehive_analytics()->settings->update( 'account_id', '', 'google', $network );
			beehive_analytics()->settings->update( 'auto_track', '', 'misc', $network );
		}

		// Delete profiles from cache.
		Cache::delete_transient( 'google_profiles', $network );

		// Refresh caches.
		Cache::refresh_transient();
		Cache::refresh_cache();

		/**
		 * Hook to execute after logout.
		 *
		 * @param bool $success Is success or fail?.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_google_auth_logout', $logout );

		return $logout;
	}

	/**
	 * Get a random credential pair for authentication.
	 *
	 * @since 3.2.7
	 *
	 * @return array
	 */
	private function get_random_creds() {
		// Get the available pair of default keys.
		$default_creds = Data::instance()->credentials();

		// Take only client IDs.
		$client_ids = array_keys( $default_creds );

		$keys = array();

		// Prepare for the random selection.
		foreach ( $client_ids as $key => $client_id ) {
			if ( isset( $default_creds[ $client_id ]['weight'] ) ) {
				for ( $i = 0; $i < $default_creds[ $client_id ]['weight']; $i++ ) {
					$keys[] = $key;
				}
			}
		}

		// Get random client ID.
		$random_client = $client_ids[ $keys[ wp_rand( 0, count( $keys ) - 1 ) ] ];

		// Set the client id and secret of random pair.
		$credentials = array(
			'client_id'     => $random_client,
			'client_secret' => $default_creds[ $random_client ]['secret'],
		);

		/**
		 * Filter to modify random credentials before processing.
		 *
		 * @param array $credentials Client ID and Client secret.
		 *
		 * @since 3.2.7
		 */
		return apply_filters( 'beehive_google_auth_get_random_creds', $credentials );
	}
}