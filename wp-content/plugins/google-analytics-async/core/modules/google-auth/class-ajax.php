<?php

namespace Beehive\Core\Modules\Google_Auth;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\Admin_Ajax;

/**
 * The ajax functions class for the module.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Ajax extends Admin_Ajax {

	/**
	 * Initialize the class by registering all ajax calls.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Get Google auth url.
		add_action( 'wp_ajax_beehive_google_auth_url', [ $this, 'google_auth_url' ] );

		// Exchange access code and get access token.
		add_action( 'wp_ajax_beehive_google_exchange_code', [ $this, 'google_exchange_code' ] );

		// Google account action.
		add_action( 'wp_ajax_beehive_google_account_action', [ $this, 'google_account_action' ] );
	}

	/**
	 * Get authentication url to redirect the user.
	 *
	 * If authentication is required, we will generate a
	 * authentication url for the user. We will store our
	 * custom data in state data to identify the callback
	 * request from Google.
	 * We will save the client id and secret first.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function google_auth_url() {
		// Security check.
		$this->security_check( true, 'settings' );

		// Continue only if client id, client secret and API key is found.
		$this->required_check( [ 'client_id', 'client_secret' ] );

		// Save credentials.
		Google_Auth\Actions::instance()->save_settings( 'google', $_POST, $this->is_network() );

		// Send success response.
		wp_send_json_success( [
			'url' => Google_Auth\Helper::instance()->auth_url( $this->is_network() ),
		] );
	}

	/**
	 * Get access token by exchanging access code.
	 *
	 * We need to send an API request to exchange the access code.
	 * If valid, we will get an access token and refresh token.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function google_exchange_code() {
		// Security check.
		$this->security_check( true, 'settings' );

		// Continue only if client id, client secret and API key is found.
		$this->required_check( [ 'access_code' ] );

		// Setup client instance.
		Google_Auth\Auth::instance()->setup_default( $this->is_network() );

		// Exchange access code and get access token.
		$token = Google_Auth\Auth::instance()->client()->fetchAccessTokenWithAuthCode( $_REQUEST['access_code'] );

		// Save access and refresh tokens if success.
		if ( isset( $token['access_token'], $token['refresh_token'] ) ) {
			// Save the token.
			Google_Auth\Actions::instance()->save_settings( 'google_login', [
				'access_token' => wp_json_encode( $token ), // For backward compatibility.
				'logged_in'    => 2, // Logged in flag.
				'method'       => 'connect', // Login method.
				'name'         => '', // Clear old name.
				'email'        => '', // Clear old email.
				'photo'        => '', // Clear old photo.
			], $this->is_network() );

			// Send success response.
			wp_send_json_success();
		}

		// Send error response.
		wp_send_json_error( [
			'error' => sprintf(
				__( 'It appears the access code you used was invalid. Please get your access code again by clicking the “Connect with Google” button below and pasting it again. If you run into further issues you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'ga_trans' ),
				'https://premium.wpmudev.org/get-support/'
			),
		] );
	}

	/**
	 * Perform Google account actions.
	 *
	 * For now, both logout and switch profile does same
	 * thing (logging out from Google). Keeping both for UX.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function google_account_action() {
		// Security check.
		$this->security_check( true, 'settings' );

		// Continue only if action is set.
		$this->required_check( [ 'account_action' ] );

		switch ( $_REQUEST['account_action'] ) {
			case 'logout':
			case 'switch':
				// Logout access code.
				Google_Auth\Auth::instance()->logout( $this->is_network() );
				break;
			default:
				// Nothing.
				break;
		}

		// Send success response.
		wp_send_json_success();
	}
}