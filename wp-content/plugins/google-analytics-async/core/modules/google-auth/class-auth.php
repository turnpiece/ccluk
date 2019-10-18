<?php

namespace Beehive\Core\Modules\Google_Auth;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Google_Client;
use Beehive\Core\Helpers\Cache;
use Beehive\Core\Helpers\General;
use Google_Service_PeopleService;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Auth\Views\Settings;

/**
 * The Google auth class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
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
	 * Initialize the class and register hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	protected function __construct() {
		// Initialize new client.
		$this->client( true );
	}

	/**
	 * Initialize all sub classes.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Init child class.
		Ajax::instance()->init();
		Actions::instance()->init();
		Settings::instance()->init();
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
		// If requested for new instance.
		if ( $new ) {
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
		$scopes = (array) apply_filters( 'beehive_google_auth_scopes', [] );

		// These are always required.
		$required_scopes = [
			Google_Service_PeopleService::USERINFO_PROFILE,
			Google_Service_PeopleService::USERINFO_EMAIL,
		];

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
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function setup_default( $network = false ) {
		/**
		 * Filter to change default client ID.
		 *
		 * @param string $default_client_id Default client ID.
		 *
		 * @deprecated 3.2.0
		 */
		$default_client_id = apply_filters_deprecated(
			'ga_project_client_id',
			[ '640050123521-r5bp4142nh6dkh8bn0e6sn3pv852v3fm.apps.googleusercontent.com' ],
			'3.2.0',
			'beehive_google_default_client_id'
		);

		/**
		 * Filter to change default client secret.
		 *
		 * @param string $default_client_secret Default client secret.
		 *
		 * @deprecated 3.2.0
		 */
		$default_client_secret = apply_filters_deprecated(
			'ga_project_client_secret',
			[ 'wWEelqN4DvE2DJjUPp-4KSka' ],
			'3.2.0',
			'beehive_google_default_client_secret'
		);

		/**
		 * Filter to change default client ID.
		 *
		 * @param string $default_client_id Default client ID.
		 *
		 * @since 3.0.0
		 */
		$default_client_id = apply_filters( 'beehive_google_default_client_id', $default_client_id );

		/**
		 * Filter to change default client secret.
		 *
		 * @param string $default_client_secret Default client secret.
		 *
		 * @since 3.0.0
		 */
		$default_client_secret = apply_filters( 'beehive_google_default_client_secret', $default_client_secret );

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
		$updated = beehive_analytics()->settings->update_group( [], 'google_login', $network );

		// Delete profiles from cache.
		Cache::delete_cache( 'google_profiles', true, $network );

		// Refresh caches.
		Cache::refresh_cache();

		/**
		 * Hook to execute after logout.
		 *
		 * @param bool $success Is success or fail?.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_google_auth_logout', $updated );

		return $updated;
	}
}