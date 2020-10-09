<?php
/**
 * The Google authentication class.
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

use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Template;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Actions
 *
 * @package Beehive\Core\Modules\Google_Auth
 */
class Actions extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Handle Google auth callback.
		add_action( 'init', array( $this, 'handle_callback' ) );

		// Handle Google auth callback.
		add_action( 'admin_init', array( $this, 'exchange_code' ) );
	}

	/**
	 * Handle Google authentication callback.
	 *
	 * Check if current request has the Google callback params.
	 * If so, validate the request and redirect to our plugin
	 * page to process the access code.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function handle_callback() {
		// Make sure this is Google callback.
		// phpcs:ignore
		if ( ! isset( $_GET['state'], $_GET['code'] ) && ! isset( $_GET['state'], $_GET['error'] ) ) {
			return;
		}

		// Decode the state data.
		// phpcs:ignore
		$state = json_decode( rawurldecode( $_GET['state'] ), true );

		// Continue only after security check.
		if ( isset( $state['beehive_nonce'], $state['origin'], $state['default'], $state['page'] ) ) {
			// Setup the redirect url base.
			if ( 'network' === $state['origin'] ) {
				// If from dashboard.
				if ( 'dashboard' === $state['page'] ) {
					$url = Template::dashboard_url( true );
				} else {
					$url = Template::settings_url( 'permissions', true );
				}
			} else {
				// If from dashboard.
				if ( 'dashboard' === $state['page'] ) {
					$url = Template::dashboard_url();
				} else {
					$url = Template::accounts_url( 'google', false, $state['origin'] );
				}
			}

			// Setup redirect url.
			$url = add_query_arg(
				array(
					// phpcs:ignore
					'gcode'         => isset( $_GET['code'] ) ? $_GET['code'] : 0,
					'default'       => $state['default'],
					'beehive_nonce' => $state['beehive_nonce'], // Nonce retained for verification in subsite.
				),
				$url
			);

			/**
			 * Action hook to execute after redirected from Google auth.
			 *
			 * @param array  $state State values.
			 * @param string $url   Redirect url.
			 *
			 * @since 3.2.0
			 */
			do_action( 'beehive_google_callback', $state, $url );

			// Redirect to our page.
			// phpcs:ignore
			wp_redirect( esc_url_raw( $url ) );
			exit;
		}
	}

	/**
	 * Handle Google authentication callback redirect.
	 *
	 * Handle the redirect with Google access code from
	 * authentication callback. This is not the callback
	 * from Google. We have been redirected to here from
	 * handle_callback() method using access code.
	 * We need to exchange the access code with Google and
	 * get the access token.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function exchange_code() {
		$success = false;

		$core = Auth::instance();

		// Continue only when required data is set.
		// phpcs:ignore
		if ( ! isset( $_GET['gcode'], $_GET['beehive_nonce'], $_GET['page'], $_GET['default'] ) ) {
			return;
		}

		// Security check.
		// phpcs:ignore
		if ( ! wp_verify_nonce( $_GET['beehive_nonce'], 'beehive_nonce' ) ) {
			return;
		}

		// Check if the authentication is using default credentials.
		// phpcs:ignore
		$default = ! empty( $_GET['default'] );

		// Network flag.
		$network = is_network_admin();

		// Continue only if valid code found.
		// phpcs:ignore
		if ( ! empty( $_GET['gcode'] ) ) {
			// Setup client instance.
			$default ? $core->setup_default( $network ) : $core->setup( $network );

			// Sanitize the code.
			// phpcs:ignore
			$g_code = sanitize_text_field( $_GET['gcode'] );

			// Exchange access code and get access token.
			$token = $core->client()->fetchAccessTokenWithAuthCode( $g_code );

			// Save access and refresh tokens.
			if ( isset( $token['access_token'], $token['refresh_token'] ) ) {
				// We don't need scope. It may get blocked by WAFs.
				if ( isset( $token['scope'] ) ) {
					unset( $token['scope'] );
				}

				// When we are re-using the network API creds.
				if ( ! $network && General::is_networkwide() && Helper::instance()->is_logged_in( true ) && 'api' === Helper::instance()->login_method( true ) ) {
					$method = 'network_connect';
				} else {
					$method = 'api';
				}

				// Update the login data.
				$this->save_settings(
					'google_login',
					array(
						'access_token' => wp_json_encode( $token ), // For backward compatibility.
						'logged_in'    => 2, // Logged in flag.
						'method'       => $method, // Login method.
						'name'         => '', // Clear old name.
						'email'        => '', // Clear old email.
						'photo'        => '', // Clear old photo.
					),
					$network
				);

				// Setup user data.
				Data::instance()->user( $network );

				// Success flag.
				$success = true;
			}

			// Flag to show notice.
			beehive_analytics()->settings->update(
				'google_auth_redirect_success',
				$success ? 'success' : 'error',
				'misc',
				$network
			);
		}

		/**
		 * Hook to execute after authentication.
		 *
		 * @param bool $success Is success or fail?.
		 * @param bool $default Did we connect using default credentials?.
		 * @param bool $network Network flag.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_google_auth_completed', $success, $default, $network );
	}

	/**
	 * Save Google API credentials to db.
	 *
	 * We need to save only the given items and keep
	 * the existing items intact.
	 *
	 * @param string $type    Settings type.
	 * @param array  $data    Credentials data.
	 * @param bool   $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function save_settings( $type = 'google', $data = array(), $network = false ) {
		// Get available keys.
		$fields = beehive_analytics()->settings->default_settings( $network );

		// Only if valid.
		if ( isset( $fields[ $type ] ) ) {
			// Get all values first.
			$options = beehive_analytics()->settings->get_options( $type, $network );

			// Loop through each items.
			foreach ( $fields[ $type ] as $key => $value ) {
				// Make sure only allowed items are saved.
				if ( isset( $data[ $key ] ) ) {
					// Sanitize.
					if ( is_array( $data[ $key ] ) ) {
						$options[ $key ] = General::sanitize_array( $data[ $key ] );
					} else {
						$options[ $key ] = sanitize_text_field( $data[ $key ] );
					}
				}
			}

			// Update Google data.
			beehive_analytics()->settings->update_group( $options, $type, $network );
		}
	}
}