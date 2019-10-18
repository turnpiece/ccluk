<?php

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Admin_Ajax;

/**
 * The ajax functions class.
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
		// Skip onboarding screen.
		add_action( 'wp_ajax_beehive_skip_onboarding', [ $this, 'skip_onboarding' ] );

		// Finish onboarding setup.
		add_action( 'wp_ajax_beehive_finish_onboarding', [ $this, 'finish_onboarding' ] );

		// Dismiss notices.
		add_action( 'wp_ajax_beehive_dismiss_admin_notice', [ $this, 'dismiss_admin_notice' ] );
	}

	/**
	 * Skip onboarding setup screen.
	 *
	 * Set a flag in db that the onboarding has been
	 * completed/skipped. So it won't be shown again.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function skip_onboarding() {
		// Security check.
		$this->security_check( false );

		// Set the flag.
		beehive_analytics()->settings->update( 'onboarding_done', 1, 'misc', $this->is_network() );

		// Send success response.
		wp_send_json_success();
	}

	/**
	 * Finish onboarding setup.
	 *
	 * Set the options to db and also set a flag that the onboarding has
	 * been completed/skipped. So it won't be shown again.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function finish_onboarding() {
		// Security check.
		$this->security_check( true, 'settings' );

		// Continue only if action is set.
		$this->required_check( [ 'auto_track' ] );

		// Network flag.
		$network = $this->is_network();

		// Get settings.
		$options = beehive_analytics()->settings->get_options( false, $network );

		// Set values.
		$options['google']['account_id']                = empty( $_POST['google_account'] ) ? '' : sanitize_text_field( $_POST['google_account'] );
		$options['google']['auto_track']                = empty( $_POST['auto_track'] ) ? 0 : 1;
		$options['general']['track_admin']              = empty( $_POST['track_admin'] ) ? 0 : 1;
		$options['general']['prosites_analytics_level'] = empty( $_POST['ps_analytics'] ) ? [] : $_POST['ps_analytics'];
		$options['general']['prosites_settings_level']  = empty( $_POST['ps_settings'] ) ? [] : $_POST['ps_settings'];
		$options['permissions']['roles']                = empty( $_POST['roles'] ) ? [] : $_POST['roles'];
		$options['permissions']['overwrite_cap']        = empty( $_POST['roles_overwrite'] ) ? 0 : 1;
		// Save tracking code.
		if ( ! empty( $_POST['tracking'] ) ) {
			$options['tracking']['code'] = $_POST['tracking'];
		}

		// Set the flag.
		$options['misc']['onboarding_done'] = 1;

		// Update the options.
		$updated = beehive_analytics()->settings->update_options( $options, $network );

		// Send success response.
		if ( $updated ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( [
				'error' => __( 'Could not finish onboarding due to an unknown error. Please try again.', 'ga_trans' ),
			] );
		}
	}

	/**
	 * Ajax handler to dismiss the notice and save the flag.
	 *
	 * Save a flag so that we don't show the notice again.
	 *
	 * @since 3.2.0
	 */
	public function dismiss_admin_notice() {
		// Only allowed user's can do this.
		$this->security_check( true, 'settings' );

		// Continue only if notice type is set.
		$this->required_check( [ 'notice' ] );

		// Meta key.
		$key = 'beehive_dismissed_notice_' . sanitize_key( $_POST['notice'] );

		// Update the meta with flag.
		update_user_meta( get_current_user_id(), $key, true );

		wp_send_json_success();
	}
}