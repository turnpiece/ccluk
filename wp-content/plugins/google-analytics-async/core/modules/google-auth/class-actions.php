<?php

namespace Beehive\Core\Modules\Google_Auth;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\Template;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * The Google authentication class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
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
		add_action( 'init', [ $this, 'handle_callback' ] );

		// Handle Google auth callback.
		add_action( 'admin_init', [ $this, 'exchange_code' ] );
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
		if ( ! isset( $_GET['state'], $_GET['code'] ) && ! isset( $_GET['state'], $_GET['error'] ) ) {
			return;
		}

		// Decode the state data.
		$state = json_decode( urldecode( $_GET['state'] ), true );

		// Continue only after security check.
		if ( isset( $state['beehive_nonce'], $state['origin'], $state['default'] )
		     && wp_verify_nonce( $state['beehive_nonce'], 'beehive_nonce' )
		) {
			// Setup the redirect url.
			if ( 'network' === $state['origin'] ) {
				$url = Template::settings_page( 'general', true );
			} else {
				// Get the redirect url base.
				$url = add_query_arg( [
					'page' => 'beehive-settings',
					'tab'  => 'general',
				], get_admin_url( $state['origin'], 'admin.php' ) );
			}

			// Setup redirect url.
			$url = add_query_arg( [
				'gnonce'  => wp_create_nonce( 'beehive_nonce' ), // Nonce.
				'gcode'   => isset( $_GET['code'] ) ? $_GET['code'] : 0,
				'default' => $state['default'],
				'modal'   => empty( $state['modal'] ) ? 0 : 1,
			], $url );

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
		if ( ! isset( $_GET['gcode'], $_GET['gnonce'], $_GET['page'], $_GET['default'] ) ) {
			return;
		}

		// Security check.
		if ( ! wp_verify_nonce( $_GET['gnonce'], 'beehive_nonce' ) ) {
			return;
		}

		// Check if the authentication is using default credentials.
		$default = ! empty( $_GET['default'] );

		// Get modal flag.
		$modal = ! empty( $_GET['modal'] );

		// Continue only if valid code found.
		if ( ! empty( $_GET['gcode'] ) ) {
			// Network flag.
			$network = is_network_admin();

			// Setup client instance.
			$default ? $core->setup_default( $network ) : $core->setup( $network );

			// Exchange access code and get access token.
			$token = $core->client()->fetchAccessTokenWithAuthCode( $_GET['gcode'] );

			// Save access and refresh tokens.
			if ( isset( $token['access_token'], $token['refresh_token'] ) ) {
				// Get granted scopes.
				$scopes = empty( $token['scope'] ) ? '' : $token['scope'];
				// Get scopes in array format.
				$scopes = explode( ' ', $scopes );

				// Update the login data.
				$this->save_settings( 'google_login', [
					'access_token' => json_encode( $token ), // For backward compatibility.
					'scopes'       => (array) $scopes,
					'logged_in'    => 2, // Logged in flag.
					'method'       => 'api', // Login method.
					'name'         => '', // Clear old name.
					'email'        => '', // Clear old email.
					'photo'        => '', // Clear old photo.
				], $network );

				// Success flag.
				$success = true;
			}
		}

		/**
		 * Hook to execute after authentication.
		 *
		 * @param bool $success Is success or fail?.
		 * @param bool $default Did we connect using default credentials?.
		 * @param bool $modal   Is it is from a modal?.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_google_auth_completed', $success, $default, $modal );
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
	public function save_settings( $type = 'google', $data = [], $network = false ) {
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
						$options[ $key ] = array_map( 'sanitize_text_field', $data[ $key ] );
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