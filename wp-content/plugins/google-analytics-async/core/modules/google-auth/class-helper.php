<?php
/**
 * The Google helper class.
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

use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Helpers\General;

/**
 * Class Helper
 *
 * @package Beehive\Core\Modules\Google_Auth
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
	 * @return string
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
	 * Check if current subsite is using network login.
	 *
	 * When subsites can reuse network API project credentials.
	 *
	 * @since 3.3.9
	 *
	 * @return bool
	 */
	public function using_network_login() {
		$network_login = false;

		// Only in multisite.
		if ( is_multisite() ) {
			// If logged in using API method in network.
			$network_login = 'network_connect' === $this->login_method() || ( $this->is_logged_in( true ) && 'api' === $this->login_method( true ) );
		}

		/**
		 * Filter hook to modify using network login flag.
		 *
		 * @param bool $network_login Login method.
		 *
		 * @since 3.3.9
		 */
		return apply_filters( 'beehive_google_using_network_login', $network_login );
	}

	/**
	 * Get authentication redirect url.
	 *
	 * @param bool  $network          Network flag.
	 * @param bool  $default          Should setup using default keys?.
	 * @param bool  $redirect_current Redirect to current site?.
	 * @param array $data             Custom data.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function auth_url( $network = false, $default = false, $redirect_current = false, $data = array() ) {
		$core = Auth::instance();

		// Setup credentials.
		$default ? $core->setup_default( $network ) : $core->setup( $network );

		// Set state.
		if ( $redirect_current && $network ) {
			self::instance()->set_state( false, $default, $data );
		} else {
			self::instance()->set_state( $network, $default, $data );
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
	 * Setup redirect url for the current client instance.
	 *
	 * We need to set our callback url as redirect url so that
	 * we can process the authentication callback from Google
	 * and generate access token.
	 *
	 * @param string $url Redirect url.
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

			// Use the sub-site's url when not networkwide active on multisite.
			if ( is_multisite() && ! General::is_networkwide() && ! $this->is_network() ) {
				// Using trailingslashit() for similarity since network_site_url() contains a trailing slash.
				$url = trailingslashit( site_url() );
			}
		}

		/**
		 * Filter hook to modify the return url
		 *
		 * @param string $url The default return url
		 *
		 * @since 3.2.5
		 */
		$url = apply_filters( 'beehive_google_set_redirect_url', $url );

		// Setup redirect url.
		Auth::instance()->client()->setRedirectUri( $url );
	}

	/**
	 * Setup state for the current client instance.
	 *
	 * This is where we store our custom data to identify
	 * the callback from Google authentication request.
	 *
	 * @param bool  $network Network flag.
	 * @param bool  $default Are we using default keys?.
	 * @param array $data    Custom data.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function set_state( $network = false, $default = false, $data = array() ) {
		// Custom data.
		$data = array(
			// Nonce for verification.
			'beehive_nonce' => wp_create_nonce( 'beehive_nonce' ),
			// Network flag.
			'origin'        => $network ? 'network' : get_current_blog_id(),
			'default'       => $default ? 1 : 0,
			// To identify if it is from modal.
			'modal'         => empty( $data['is_modal'] ) ? 0 : 1,
			'page'          => empty( $data['page'] ) ? 'settings' : $data['page'],
		);

		// Encode the data for url.
		$data = wp_json_encode( $data );
		$data = rawurlencode( $data );

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
				$network = $this->using_network_login();
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
				$network = $this->using_network_login();
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