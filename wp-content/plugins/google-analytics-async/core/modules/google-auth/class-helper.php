<?php

namespace Beehive\Core\Modules\Google_Auth;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * The Google helper class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Helper extends Base {

	/**
	 * Check if current site has logged into Google.
	 *
	 * Statuses:
	 * 0 - Not logged in.
	 * 1 - Logged in, but re-authentication required.
	 * 2 - Logged in.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function is_logged_in( $network = false ) {
		// Try to get the logged in status.
		$logged_in = (int) beehive_analytics()->settings->get( 'logged_in', 'google_login', $network );

		/**
		 * Filter hook to modify logged in status.
		 *
		 * @param bool $logged_in Login status.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_is_logged_in', 2 === $logged_in );
	}

	/**
	 * Check if current site has setup Google API credentials.
	 *
	 * This will return true only if API credentials are setup and
	 * also logged in.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function is_setup( $network = false ) {
		// Get the API creds.
		$id     = beehive_analytics()->settings->get( 'client_id', 'google', $network );
		$secret = beehive_analytics()->settings->get( 'client_secret', 'google', $network );

		// Check if both keys are found.
		$setup = ( ! empty( $id ) && ! empty( $secret ) && $this->is_logged_in( $network ) );

		/**
		 * Filter hook to modify setup in status.
		 *
		 * @param bool $logged_in Setup status.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_is_setup', $setup );
	}

	/**
	 * Get the current login method for Google login.
	 *
	 * - connect : Logged in using access code.
	 * - api - Logged in using API credentials.
	 * - network_connect - Logged in using Network API credentials.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function login_method( $network = false ) {
		$method = '';

		// Only when logged in.
		if ( $this->is_logged_in( $network ) ) {
			// Get the API creds.
			$method = beehive_analytics()->settings->get( 'method', 'google_login', $network );
		}

		/**
		 * Filter hook to modify login method.
		 *
		 * @param bool $method Login method.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_login_method', $method );
	}

	/**
	 * Get authentication redirect url.
	 *
	 * @param bool $network          Network flag.
	 * @param bool $default          Should setup using default keys?.
	 * @param bool $redirect_current Redirect to current site?.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function auth_url( $network = false, $default = false, $redirect_current = false ) {
		$core = Auth::instance();

		// Setup credentials.
		$default ? $core->setup_default( $network ) : $core->setup( $network );

		// Set state.
		if ( $redirect_current && $network ) {
			Helper::instance()->set_state( false, $default );
		} else {
			Helper::instance()->set_state( $network, $default );
		}

		// Return auth url.
		$url = $core->client()->createAuthUrl();

		/**
		 * Filter hook to modify Google Auth url.
		 *
		 * @param string $url Auth URL.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_auth_url', $url );
	}

	/**
	 * Check if current site needs to reauthenticate with Google.
	 *
	 * Re-authentication is required when the logged in flag is 1.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function reauth_required( $network = false ) {
		// Try to get the login status.
		$logged_in = (int) beehive_analytics()->settings->get( 'logged_in', 'google_login', $network );

		/**
		 * 2 - Logged in.
		 * 1 - Re-auth required.
		 * 0 - Not logged in.
		 */
		$required = ( 1 === $logged_in );

		/**
		 * Filter hook to modify reauth required status.
		 *
		 * @param bool $required Is reauth required?.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_reauth_required', $required );
	}

	/**
	 * Check if current site needs to setup with Google.
	 *
	 * This should be true only for the first time. If user logged
	 * in with Google once, this should be false. We can check re-auth
	 * for that.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function setup_required( $network = false ) {
		// Try to get the access token.
		$token = beehive_analytics()->settings->get( 'access_token', 'google_login', $network );

		// If token is empty, not logged in for the first time.
		$required = empty( $token );

		/**
		 * Filter hook to modify setup required status.
		 *
		 * @param bool $required Is setup required?.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_reauth_required', $required );
	}

	/**
	 * Setup redirect url for the current client instance.
	 *
	 * We need to set our callback url as redirect url so that
	 * we can process the authentication callback from Google
	 * and generate access token.
	 *
	 * @param string $url     Redirect url.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function set_redirect_url( $url = '' ) {
		// Set home page url as callback url if empty.
		if ( empty( $url ) ) {
			// Get the home url.
			$url = network_site_url();
		}

		// Setup redirect url.
		Auth::instance()->client()->setRedirectUri( $url );
	}

	/**
	 * Setup state for the current client instance.
	 *
	 * This is where we store our custom data to identify
	 * the callback from Google authentication request.
	 *
	 * @param bool $network Network flag.
	 * @param bool $default Are we using default keys?.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function set_state( $network = false, $default = false ) {
		// Custom data.
		$data = [
			// Nonce for verification.
			'beehive_nonce' => wp_create_nonce( 'beehive_nonce' ),
			// Network flag.
			'origin'        => $network ? 'network' : get_current_blog_id(),
			'default'       => $default ? 1 : 0,
			// To identify if it is from modal.
			'modal'         => empty( $_REQUEST['is_modal'] ) ? 0 : 1,
		];

		// Encode the data for url.
		$data = json_encode( $data );
		$data = urlencode( $data );

		// Set as state.
		Auth::instance()->client()->setState( $data );
	}

	/**
	 * Setup access token to current client instance.
	 *
	 * We will first check if the token is passed as
	 * a parameter. If not try to get from db. If still
	 * not found, we will not set.
	 *
	 * @param bool|string $token   Access token.
	 * @param bool        $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function set_token( $token = false, $network = false ) {
		// Try to get from db if param is empty.
		if ( empty( $token ) ) {
			$token = beehive_analytics()->settings->get( 'access_token', 'google_login', $network );
		}

		// Set only if not empty.
		if ( ! empty( $token ) ) {
			Auth::instance()->client()->setAccessToken( $token );
		}
	}

	/**
	 * Setup client id to current client instance.
	 *
	 * We will first check if the client id is passed as
	 * a parameter. If not try to get from db. If still
	 * not found, we will not set.
	 *
	 * @param bool|string $id      Client ID.
	 * @param bool        $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function set_client( $id = false, $network = false ) {
		// Try to get from db if param is empty.
		if ( empty( $id ) ) {
			// If already logged in by network admin, get the network client id.
			if ( ! $network && is_multisite() ) {
				$network = $this->is_logged_in( true );
			}

			$id = beehive_analytics()->settings->get( 'client_id', 'google', $network );
		}

		// Set only if not empty.
		if ( ! empty( $id ) ) {
			Auth::instance()->client()->setClientId( $id );
		}
	}

	/**
	 * Setup client secret to current client instance.
	 *
	 * We will first check if the secret is passed as
	 * a parameter. If not try to get from db. If still
	 * not found, we will not set.
	 *
	 * @param bool|string $secret  Client secret.
	 * @param bool        $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function set_secret( $secret = false, $network = false ) {
		// Try to get from db if param is empty.
		if ( empty( $secret ) ) {
			// If already logged in by network admin, get the network client secret.
			if ( ! $network && is_multisite() ) {
				$network = $this->is_logged_in( true );
			}

			$secret = beehive_analytics()->settings->get( 'client_secret', 'google', $network );
		}

		// Set only if not empty.
		if ( ! empty( $secret ) ) {
			Auth::instance()->client()->setClientSecret( $secret );
		}
	}

	/**
	 * Setup the Google Auth instance.
	 *
	 * Check login method and setup based on that.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function setup_auth( $network = false ) {
		// Logged in method.
		$login_method = $this->login_method( $network );

		// Setup required things.
		if ( 'connect' === $login_method ) {
			Auth::instance()->setup_default( $network );
		} else {
			Auth::instance()->setup( $network );
		}

		/**
		 * Action hook to execute after setting up Google client.
		 *
		 * @param bool        $network      Network flag.
		 * @param bool|string $login_method Login method.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_after_google_setup_auth', $network, $login_method );
	}
}