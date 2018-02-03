<?php

/**
 * Class WP_Hummingbird_Admin_AJAX
 *
 * Handle all AJAX actions in admin side
 */
class WP_Hummingbird_Admin_AJAX {

	/**
	 * WP_Hummingbird_Admin_AJAX constructor.
	 */
	public function __construct() {
		// Run performance test.
		add_action( 'wp_ajax_wphb_performance_run_test', array( $this, 'performance_run_test' ) );
		// Set expiration for browser caching.
		add_action( 'wp_ajax_wphb_caching_set_expiration', array( $this, 'caching_set_expiration' ) );
		// Set server type.
		add_action( 'wp_ajax_wphb_caching_set_server_type', array( $this, 'caching_set_server_type' ) );
		// Reload snippet.
		add_action( 'wp_ajax_wphb_caching_reload_snippet', array( $this, 'caching_reload_snippet' ) );
		// Cloudflare connect.
		add_action( 'wp_ajax_wphb_cloudflare_connect', array( $this, 'cloudflare_connect' ) );
		// Cloudflare expirtion cache.
		add_action( 'wp_ajax_wphb_cloudflare_set_expiry', array( $this, 'cloudflare_set_expiry' ) );
		// Cloudflare purge cache.
		add_action( 'wp_ajax_wphb_cloudflare_purge_cache', array( $this, 'cloudflare_purge_cache' ) );
		// Activate network minification.
		add_action( 'wp_ajax_wphb_dash_toggle_network_minification', array( $this, 'dash_toggle_network_minification' ) );
		// Skip quick setup.
		add_action( 'wp_ajax_wphb_dash_skip_setup', array( $this, 'dashboard_skip_setup' ) );
		// Toggle CDN.
		add_action( 'wp_ajax_wphb_minification_toggle_cdn', array( $this, 'minification_toggle_cdn' ) );
		// Toggle minification.
		add_action( 'wp_ajax_wphb_minification_toggle_minification', array( $this, 'minification_toggle_minification' ) );
		// Toggle advanced minification view.
		add_action( 'wp_ajax_wphb_minification_toggle_view', array( $this, 'minification_toggle_view' ) );
		// Start scan.
		add_action( 'wp_ajax_wphb_minification_start_check', array( $this, 'minification_start_check' ) );
		// Scan check step.
		add_action( 'wp_ajax_wphb_minification_check_step', array( $this, 'minification_check_step' ) );
		// Cancel scan.
		add_action( 'wp_ajax_wphb_minification_cancel_scan', array( $this, 'minification_cancel_scan' ) );
		// Delete scan
		add_action( 'wp_ajax_wphb_minification_finish_scan', array( $this, 'minification_finish_scan' ) );
		// Dismiss notice.
		add_action( 'wp_ajax_wphb_notice_dismiss', array( $this, 'notice_dismiss' ) );
		// Dismiss notice.
		add_action( 'wp_ajax_wphb_cf_notice_dismiss', array( $this, 'cf_notice_dismiss' ) );
	}

	/**
	 * Run performance test.
	 */
	public function performance_run_test() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		// Remove quick setup.
		wphb_remove_quick_setup();

		if ( wphb_performance_stopped_report() ) {
			wp_send_json_success( array(
				'finished' => true,
			));
		}

		$started_at = wphb_performance_is_doing_report();

		if ( ! $started_at ) {
			/* @var WP_Hummingbird_Module_Performance $perf_module */
			$perf_module = wphb_get_module( 'performance' );
			$perf_module->init_scan();

			wp_send_json_success( array(
				'finished' => false,
			));
		}

		$now = current_time( 'timestamp' );
		if ( $now >= ( $started_at + 10 ) ) {
			// The report should be finished by this time, let's get the results.
			wphb_performance_refresh_report();
			wp_send_json_success( array(
				'finished' => true,
			));
		}

		// Just do nothing until the report is finished.
		wp_send_json_success( array(
			'finished' => false,
		));
	}

	public function uptime_toggle_uptime( $data ) {
		if ( ! isset( $data['value'] ) ) {
			die();
		}

		$value = $data['value'] == 'false' ? false : true;

		$options = wphb_get_settings();
		$options['uptime'] = $value;
		wphb_update_settings( $options );
		die();
	}

	/**
	 * Set expiration for browser caching.
	 */
	public function caching_set_expiration() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['type'] ) || ! isset( $_POST['expiry_times'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['type'] ) ); // Input var okay.

		$sanitized_expiry_times = array();
		$sanitized_expiry_times['caching_expiry_javascript'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_javascript'] ) );
		$sanitized_expiry_times['caching_expiry_css'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_css'] ) );
		$sanitized_expiry_times['caching_expiry_media'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_media'] ) );
		$sanitized_expiry_times['caching_expiry_images'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_images'] ) );

		$frequencies = wphb_get_caching_frequencies();

		foreach ( $sanitized_expiry_times as $value ) {
			if ( ! isset( $frequencies[ $value ] ) ) {
				die();
			}
		}

		$options = wphb_get_settings();

		$options['caching_expiry_css']        = $sanitized_expiry_times['caching_expiry_css'];
		$options['caching_expiry_javascript'] = $sanitized_expiry_times['caching_expiry_javascript'];
		$options['caching_expiry_media']      = $sanitized_expiry_times['caching_expiry_media'];
		$options['caching_expiry_images']     = $sanitized_expiry_times['caching_expiry_images'];

		wphb_update_settings( $options );

		/**
		 * Pass in caching type and value into a custom function.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args {
		 *     Array of selected type and value.
		 *
		 *     @type string $type                   Type of cached data, can be one of following:
		 *                                          `javascript`, `css`, `media` or `images`.
		 *     @type array  $sanitized_expiry_times Set expiry values (for example, 1h/A3600), first part can be:
		 *                                          `[n]h` for [n] hours (for example, 1h, 4h, 11h, etc),
		 *                                          `[n]d` for [n] days (for example, 1d, 4d, 11d, etc),
		 *                                          `[n]M` for [n] months (for example, 1M, 4M, 11M, etc),
		 *                                          `[n]y` for [n] years (for example, 1y, 4y, 11y, etc),
		 *                                          second part is the first part in seconds ( 1 hour = 3600 sec).
		 * }
		 */
		do_action( 'wphb_caching_set_expiration', array(
			'type'         => $type,
			'expiry_times' => $sanitized_expiry_times,
		));

		wp_send_json_success();
	}

	/**
	 * Set server type.
	 */
	public function caching_set_server_type() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var okay.

		if ( ! array_key_exists( $value, wphb_get_servers() ) ) {
			wp_send_json_error();
		}

		update_user_meta( get_current_user_id(), 'wphb-server-type', $value );

		wp_send_json_success();
	}

	/**
	 * Reload snippet after new expiration interval has been selected.
	 */
	public function caching_reload_snippet() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['type'] ) || ! isset( $_POST['expiry_times'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['type'] ) ); // Input var okay.
		// Check if Clouflare value (array won't exist).
		if ( ! strpos( $_POST['expiry_times']['caching_expiry_javascript'], "/A" ) ) {
			// Convert to readable value.
			$frequency = wphb_convert_cloudflare_frequency( (int)$_POST['expiry_times']['caching_expiry_javascript'] );
			$sanitized_expiry_times = array();
			$sanitized_expiry_times['caching_expiry_javascript'] = $frequency;
			$sanitized_expiry_times['caching_expiry_css'] = $frequency;
			$sanitized_expiry_times['caching_expiry_media'] = $frequency;
			$sanitized_expiry_times['caching_expiry_images'] = $frequency;
		} else {
			$sanitized_expiry_times = array();
			$sanitized_expiry_times['caching_expiry_javascript'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_javascript'] ) );
			$sanitized_expiry_times['caching_expiry_css'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_css'] ) );
			$sanitized_expiry_times['caching_expiry_media'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_media'] ) );
			$sanitized_expiry_times['caching_expiry_images'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['caching_expiry_images'] ) );
		}

		$code = wphb_get_code_snippet( 'caching', $type, $sanitized_expiry_times );

		wp_send_json_success( array(
			'type' => $type,
			'code' => $code,
		));
	}

	/**
	 * Toggle settings for network minification in multisite installs.
	 */
	public function dash_toggle_network_minification() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$post_value = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var okay.

		switch ( $post_value ) {
			case 'false': {
				$value = false;
				break;
			}
			case 'super-admins': {
				$value = 'super-admins';
				break;
			}
			default: {
				$value = true;
				break;
			}
		}

		wphb_toggle_minification( $value, true );
		wp_send_json_success();
	}

	/**
	 * Skip quick setup and go straight to dashboard.
	 *
	 * @since 1.5.0
	 */
	public function dashboard_skip_setup() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		wphb_remove_quick_setup();

		wp_send_json_success();
	}

	/**
	 * Toggle CDN.
	 *
	 * Used on dashboard page in minification meta box and in the minification module.
	 * Clear files function at the end clears all cache and on first home page reload, all the files will
	 * be either moved to CDN or stored local.
	 */
	public function minification_toggle_cdn() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = rest_sanitize_boolean( wp_unslash( $_POST['value'] ) ); // Input var okay.

		wphb_toggle_cdn( $value );

		// Clear the files.
		wphb_minification_clear_files();
		wp_send_json_success();
	}

	/**
	 * Toggle minification on per site basis.
	 */
	public function minification_toggle_minification() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = rest_sanitize_boolean( wp_unslash( $_POST['value'] ) ); // Input var okay.

		wphb_toggle_minification( $value );

		wp_send_json_success();
	}

	/**
	 * Toggle minification advanced view.
	 *
	 * @since 1.7.1
	 */
	public function minification_toggle_view() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var okay.

		if ( 'advanced' === $type ) {
			add_site_option( 'wphb-minification-view', true );
		}

		if ( 'basic' === $type ) {
			delete_site_option( 'wphb-minification-view' );

			/* @var WP_Hummingbird_Module_Minify $module */
			$module = wphb_get_module( 'minify' );
			$module->reset();
		}

		wp_send_json_success();
	}

	/**
	 * Start minification scan.
	 *
	 * Set a flag that marks the minification check files as started.
	 */
	public function minification_start_check() {
		wphb_minification_init_scan();

		wp_send_json_success( array(
			'steps'    => wphb_minification_get_scan_steps_number(),
		));
	}

	/**
	 * Process step during minification scan.
	 */
	public function minification_check_step() {
		$urls = wphb_minification_get_scan_urls();
		$total_steps = count( $urls );
		$current_step = absint( $_POST['step'] );

		wphb_minification_update_scan_step( $current_step );

		if ( isset( $urls[ $current_step ] ) ) {
			wphb_minification_scan_url( $urls[ $current_step ] );
		}


		wp_send_json_success();
	}

	public function minification_finish_scan() {
		delete_transient( 'wphb-minification-files-scanning' );
		update_option( 'wphb-minification-files-scanned', true );
		wp_send_json_success();
	}

	/**
	 * Cancel minification file check if cancel button pressed.
	 *
	 * @since 1.4.5
	 */
	public function minification_cancel_scan() {
		wphb_toggle_minification( false );

		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = wphb_get_module( 'minify' );
		$minify_module->clear_cache();

		wp_send_json_success();
	}

	/**
	 * Connect to Cloudflare.
	 */
	public function cloudflare_connect() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['formData'] ) || ! isset( $_POST['step'] ) ) { // Input var okay.
			die();
		}

		$form_data = wp_unslash( $_POST['formData'] ); // Input var okay.
		$form_data = wp_parse_args( $form_data, array(
			'cloudflare-email'   => '',
			'cloudflare-api-key' => '',
			'cloudflare-zone'    => '',
		));

		$step = sanitize_text_field( wp_unslash( $_POST['step'] ) ); // Input var okay.
		$cfData = wp_unslash( $_POST['cfData'] ); // Input var okay.

		/* @var WP_Hummingbird_Module_Cloudflare $cloudflare */
		$cloudflare = wphb_get_module( 'cloudflare' );

		$settings = wphb_get_settings();

		switch ( $step ) {
			case 'credentials': {
				$settings['cloudflare-email'] = sanitize_email( $form_data['cloudflare-email'] );
				$settings['cloudflare-api-key'] = sanitize_text_field( $form_data['cloudflare-api-key'] );
				$settings['cloudflare-zone'] = sanitize_text_field( $form_data['cloudflare-zone'] );
				$settings['cloudflare-zone-name'] = isset( $form_data['cloudflare-zone-name'] ) ? sanitize_text_field( $form_data['cloudflare-zone-name'] ) : '';
				wphb_update_settings( $settings );

				$zones = $cloudflare->get_zones_list();

				if ( is_wp_error( $zones ) ) {
					wp_send_json_error( array(
						'message' => sprintf( '<strong>%s</strong> [%s]', $zones->get_error_message(), $zones->get_error_code() ),
					));
				}

				$cfData['email']  = $settings['cloudflare-email'];
				$cfData['apiKey'] = $settings['cloudflare-api-key'];
				$cfData['zones']  = $zones;

				$settings['cloudflare-connected'] = true;
				wphb_update_settings( $settings );

				// Try to auto select domain.
				$site_url = network_site_url();
				$site_url = rtrim( preg_replace( '/^https?:\/\//', '', $site_url ), '/' );
				$plucked_zones = wp_list_pluck( $zones, 'label' );
				$found = preg_grep( '/.*' . $site_url . '.*/', $plucked_zones );
				if ( is_array( $found ) && count( $found ) === 1 && isset( $zones[ key( $found ) ]['value'] ) ) {
					// Select the domain and cheat this function.
					$zone_found = $zones[ key( $found ) ]['value'];
					$_POST['formData'] = array(
						'cloudflare-zone' => $zone_found,
					);
					$_POST['step'] = 'zone';
					$_POST['cfData'] = $cfData;
					$this->cloudflare_connect();
				}

				wp_send_json_success( array(
					'nextStep' => 'zone',
					'newData'  => $cfData,
				));
				break;
			} // End case().
			case 'zone': {
				$settings['cloudflare-zone'] = sanitize_text_field( $form_data['cloudflare-zone'] );

				if ( empty( $settings['cloudflare-zone'] ) ) {
					wp_send_json_error( array(
						'message' => __( 'Please, select a Cloudflare zone. Normally, this is your website', 'wphb' ),
					));
				}

				// Check that the zone exists.
				$zones = $cloudflare->get_zones_list();
				if ( is_wp_error( $zones ) ) {
					wp_send_json_error( array(
						'message' => sprintf( '<strong>%s</strong> [%s]', $zones->get_error_message(), $zones->get_error_code() ),
					));
				} else {
					$filtered = wp_list_filter( $zones, array(
						'value' => $settings['cloudflare-zone'],
					));
					if ( ! $filtered ) {
						wp_send_json_error( array(
							'message' => __( 'The selected zone is not valid', 'wphb' ),
						));
					}
					$settings['cloudflare-zone-name'] = $filtered[0]['label'];
					$settings['cloudflare-plan'] = $filtered[0]['plan'];
				}

				$settings['cloudflare-connected'] = true;

				wphb_update_settings( $settings );
				$cfData['zone'] = $settings['cloudflare-zone'];
				$cfData['zoneName'] = $settings['cloudflare-zone-name'];
				$cfData['plan'] = $settings['cloudflare-plan'];

				update_site_option( 'wphb-is-cloudflare', 1 );

				// And set the new CF setting.
				$cloudflare->set_caching_expiration( 691200 );

				$redirect = wphb_get_admin_menu_url( 'caching' );
				wp_send_json_success( array(
					'nextStep' => 'final',
					'newData'  => $cfData,
					'redirect' => $redirect,
				));
				break;
			} // End case().
		} // End switch().

		wp_send_json_error();
	}

	/**
	 * Set expiration for Cloudflare cache.
	 */
	public function cloudflare_set_expiry() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		$value = absint( $_POST['value'] );

		/** @var WP_Hummingbird_Module_Cloudflare $cf */
		$cf = wphb_get_module( 'cloudflare' );

		$cf->set_caching_expiration( $value );

		wp_send_json_success();
	}

	/**
	 * Purge Cloudflare cache.
	 */
	public function cloudflare_purge_cache() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		/** @var WP_Hummingbird_Module_Cloudflare $cf */
		$cf = wphb_get_module( 'cloudflare' );
		$cf->clear_cache();

		wp_send_json_success();
	}

	/**
	 * Dismiss notice.
	 *
	 * @since 1.6.1
	 */
	public function notice_dismiss() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		$notice_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );

		delete_option( 'wphb-notice-' . $notice_id . '-show' );

		wp_send_json_success();
	}

	/**
	 * Dismiss CloudFlare dash notice.
	 *
	 * @since 1.7.0
	 */
	public function cf_notice_dismiss() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		update_site_option( 'wphb-cloudflare-dash-notice', 'dismissed' );

		wp_send_json_success();
	}
}