<?php

namespace Beehive\Core\Modules\Google_Analytics\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\View;

/**
 * The tracking view class for Google.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Tracking extends View {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Site tracking id.
		add_action( 'wp_head', [ $this, 'tracking' ] );

		// Add admin tracking id if required.
		add_action( 'admin_head', [ $this, 'admin_tracking' ] );
	}

	/**
	 * Render tracking code output.
	 *
	 * Both network tracking code and single site tracking
	 * code will be rendered if multisite.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function tracking() {
		/**
		 * Filter hook to disable the tracking script completely.
		 *
		 * @param bool $enabled Should enable?.
		 *
		 * @since 3.2.0
		 */
		$tracking = apply_filters( 'beehive_google_enable_tracking', true );

		// No need to continue on ajax and preview mode.
		if ( ! $tracking || is_preview() || wp_doing_ajax() ) {
			return;
		}

		// Tracking codes.
		$single_code                = beehive_analytics()->settings->get( 'code', 'tracking' );
		$network_code               = beehive_analytics()->settings->get( 'code', 'tracking', true );
		$single_auto_tracking       = beehive_analytics()->settings->get( 'auto_track', 'google' );
		$network_auto_tracking      = beehive_analytics()->settings->get( 'auto_track', 'google', true );
		$single_auto_tracking_code  = beehive_analytics()->settings->get( 'auto_track', 'misc' );
		$network_auto_tracking_code = beehive_analytics()->settings->get( 'auto_track', 'misc', true );

		// Use auto tracking code if required.
		if ( empty( $single_code ) && ! empty( $single_auto_tracking ) && ! empty( $single_auto_tracking_code ) ) {
			$single_code = $single_auto_tracking_code;
		}
		if ( empty( $network_code ) && ! empty( $network_auto_tracking ) && ! empty( $network_auto_tracking_code ) ) {
			$network_code = $network_auto_tracking_code;
		}

		// Force anonymize.
		$force_anonymize = beehive_analytics()->settings->get( 'force_anonymize', 'general', true );

		// Render tracking code template.
		$this->view( 'scripts/google/tracking', [
			'network_tracking_code' => $network_code,
			// Only when different tracking code is set for network and single.
			'tracking_code'         => $network_code === $single_code ? '' : $single_code,
			'anonymize'             => $force_anonymize ? true : beehive_analytics()->settings->get( 'anonymize', 'general' ),
			'advertising'           => beehive_analytics()->settings->get( 'advertising', 'general' ),
			'network_anonymize'     => beehive_analytics()->settings->get( 'anonymize', 'general', true ),
			'network_advertising'   => beehive_analytics()->settings->get( 'advertising', 'general', true ),
		] );
	}

	/**
	 * Output Google Analytics code in admin.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function admin_tracking() {
		// Continue only when enabled.
		$admin_tracking = beehive_analytics()->settings->get(
			'track_admin',
			'general',
			$this->is_network()
		);

		/**
		 * Filter hook to enable/disable admin tracking.
		 *
		 * @param bool $admin_tracking Tracking enabled.
		 *
		 * @since 3.2.0
		 */
		if ( apply_filters( 'beehive_google_enable_admin_tracking', $admin_tracking ) ) {
			// Render tracking.
			$this->tracking();
		}
	}
}