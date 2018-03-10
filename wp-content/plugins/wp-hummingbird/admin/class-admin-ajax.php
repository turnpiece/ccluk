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
		// Save performance settings.
		add_action( 'wp_ajax_wphb_performance_save_settings', array( $this, 'performance_save_settings' ) );
		// Set expiration for browser caching.
		add_action( 'wp_ajax_wphb_caching_set_expiration', array( $this, 'caching_set_expiration' ) );
		// Set server type.
		add_action( 'wp_ajax_wphb_caching_set_server_type', array( $this, 'caching_set_server_type' ) );
		// Reload snippet.
		add_action( 'wp_ajax_wphb_caching_reload_snippet', array( $this, 'caching_reload_snippet' ) );
		// Toggle ability for subsite admins to turn off page caching.
		add_action( 'wp_ajax_wphb_caching_toggle_admin_subsite_page_caching', array( $this, 'caching_toggle_admin_subsite_page_caching' ) );
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
		// Toggle logs.
		add_action( 'wp_ajax_wphb_minification_toggle_log', array( $this, 'minification_toggle_log' ) );
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
		// Save critical css file
		add_action( 'wp_ajax_wphb_minification_save_critical_css', array( $this, 'minification_save_critical_css' ) );
		// Dismiss notice.
		add_action( 'wp_ajax_wphb_notice_dismiss', array( $this, 'notice_dismiss' ) );
		// Dismiss notice.
		add_action( 'wp_ajax_wphb_cf_notice_dismiss', array( $this, 'cf_notice_dismiss' ) );
		// Clean database
		add_action( 'wp_ajax_wphb_advanced_db_delete_data', array( $this, 'advanced_db_delete_data' ) );
		// Save settings in advanced tools module.
		add_action( 'wp_ajax_wphb_advanced_save_settings', array( $this, 'advanced_save_settings' ) );
		// Save settings for rss caching module.
		add_action( 'wp_ajax_wphb_caching_save_settings', array( $this, 'rss_caching_save_settings' ) );
	}

	/**
	 * Check nonce and permissions.
	 *
	 * @since 1.7.2
	 */
	private function check_permission() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}
	}

	/**
	 * Run performance test.
	 */
	public function performance_run_test() {
		$this->check_permission();

		// Remove quick setup.
		WP_Hummingbird_Utils::remove_quick_setup();

		if ( WP_Hummingbird_Module_Performance::stopped_report() ) {
			wp_send_json_success( array(
				'finished' => true,
			));
		}

		$started_at = WP_Hummingbird_Module_Performance::is_doing_report();

		if ( ! $started_at ) {
			/* @var WP_Hummingbird_Module_Performance $perf_module */
			$perf_module = WP_Hummingbird_Utils::get_module( 'performance' );
			$perf_module->init_scan();

			wp_send_json_success( array(
				'finished' => false,
			));
		}

		$now = current_time( 'timestamp' );
		if ( $now >= ( $started_at + 15 ) ) {
			// The report should be finished by this time, let's get the results.
			WP_Hummingbird_Module_Performance::refresh_report();
			wp_send_json_success( array(
				'finished' => true,
			));
		}

		// Just do nothing until the report is finished.
		wp_send_json_success( array(
			'finished' => false,
		));
	}

	/**
	 * Process scan settings.
	 *
	 * @since 1.7.1
	 */
	public function performance_save_settings() {
		$this->check_permission();

		/* @var WP_Hummingbird_Module_Performance $performance */
		$performance = WP_Hummingbird_Utils::get_module( 'performance' );
		$options = $performance->get_options();

		// Get the data from ajax.
		parse_str( $_POST['data'], $data );

		$options['subsite_tests'] = (bool) $data['subsite-tests'];

		$performance->update_options( $options );

		wp_send_json_success();
	}

	public function uptime_toggle_uptime( $data ) {
		if ( ! isset( $data['value'] ) ) {
			die();
		}

		$value = 'false' == $data['value'] ? false : true;

		$uptime = WP_Hummingbird_Utils::get_module( 'uptime' );
		$options = $uptime->get_options();
		$options['enabled'] = $value;
		$uptime->update_options( $options );

		die();
	}

	/**
	 * Set expiration for browser caching.
	 */
	public function caching_set_expiration() {
		$this->check_permission();

		if ( ! isset( $_POST['type'] ) || ! isset( $_POST['expiry_times'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['type'] ) ); // Input var okay.

		$sanitized_expiry_times = array();
		$sanitized_expiry_times['expiry_javascript'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_javascript'] ) );
		$sanitized_expiry_times['expiry_css']        = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_css'] ) );
		$sanitized_expiry_times['expiry_media']      = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_media'] ) );
		$sanitized_expiry_times['expiry_images']     = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_images'] ) );

		$frequencies = WP_Hummingbird_Utils::get_caching_frequencies();

		foreach ( $sanitized_expiry_times as $value ) {
			if ( ! isset( $frequencies[ $value ] ) ) {
				die();
			}
		}

		$caching = WP_Hummingbird_Utils::get_module( 'caching' );
		$options = $caching->get_options();

		$options['expiry_css']        = $sanitized_expiry_times['expiry_css'];
		$options['expiry_javascript'] = $sanitized_expiry_times['expiry_javascript'];
		$options['expiry_media']      = $sanitized_expiry_times['expiry_media'];
		$options['expiry_images']     = $sanitized_expiry_times['expiry_images'];

		$caching->update_options( $options );

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
		$this->check_permission();

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var okay.

		if ( ! array_key_exists( $value, WP_Hummingbird_Module_Server::get_servers() ) ) {
			wp_send_json_error();
		}

		update_user_meta( get_current_user_id(), 'wphb-server-type', $value );

		wp_send_json_success();
	}

	/**
	 * Reload snippet after new expiration interval has been selected.
	 */
	public function caching_reload_snippet() {
		$this->check_permission();

		if ( ! isset( $_POST['type'] ) || ! isset( $_POST['expiry_times'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['type'] ) ); // Input var okay.
		// Check if Clouflare value (array won't exist).
		if ( ! strpos( $_POST['expiry_times']['expiry_javascript'], "/A" ) ) {
			// Convert to readable value.
			$frequency = WP_Hummingbird_Utils::convert_cloudflare_frequency( (int) $_POST['expiry_times']['expiry_javascript'] );
			$sanitized_expiry_times = array();
			$sanitized_expiry_times['expiry_javascript'] = $frequency;
			$sanitized_expiry_times['expiry_css']        = $frequency;
			$sanitized_expiry_times['expiry_media']      = $frequency;
			$sanitized_expiry_times['expiry_images']     = $frequency;
		} else {
			$sanitized_expiry_times = array();
			$sanitized_expiry_times['expiry_javascript'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_javascript'] ) );
			$sanitized_expiry_times['expiry_css']        = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_css'] ) );
			$sanitized_expiry_times['expiry_media']      = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_media'] ) );
			$sanitized_expiry_times['expiry_images']     = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_images'] ) );
		}

		$code = WP_Hummingbird_Module_Server::get_code_snippet( 'caching', $type, $sanitized_expiry_times );

		wp_send_json_success( array(
			'type' => $type,
			'code' => $code,
		));
	}

	/**
	 * Toggle settings for network minification in multisite installs.
	 */
	public function dash_toggle_network_minification() {
		$this->check_permission();

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

		/* @var WP_Hummingbird_Module_Minify $minify */
		$minify = WP_Hummingbird_Utils::get_module( 'minify' );
		$minify->toggle_service( $value, true );

		wp_send_json_success();
	}

	/**
	 * Skip quick setup and go straight to dashboard.
	 *
	 * @since 1.5.0
	 */
	public function dashboard_skip_setup() {
		$this->check_permission();

		WP_Hummingbird_Utils::remove_quick_setup();

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
		$this->check_permission();

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = rest_sanitize_boolean( wp_unslash( $_POST['value'] ) ); // Input var okay.

		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		$minify_module->toggle_cdn( $value );
		$minify_module->clear_files();

		wp_send_json_success();
	}

	/**
	 * Toggle Subsite Admin able to turn off page caching.
	 *
	 * Used on multisite install. Allows subsite admin to turn off page caching.
	 *
	 * @since 1.8.0
	 */
	public function caching_toggle_admin_subsite_page_caching() {
		$this->check_permission();

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$post_value = rest_sanitize_boolean( wp_unslash( $_POST['value'] ) ); // Input var okay.

		$value = true;
		if ( $post_value ) {
			$value = 'blog-admins';
		}

		/* @var WP_Hummingbird_Module_Page_Cache $page_cache_module */
		$page_cache_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$page_cache_module->toggle_service( $value, true );

		wp_send_json_success();
	}

	/**
	 * Toggle logs.
	 *
	 * @since 1.7.2
	 */
	public function minification_toggle_log() {
		$this->check_permission();

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = rest_sanitize_boolean( wp_unslash( $_POST['value'] ) ); // Input var okay.

		/* @var WP_Hummingbird_Module_Minify $minify */
		$minify = WP_Hummingbird_Utils::get_module( 'minify' );
		$options = $minify->get_options();
		$options['log'] = $value;
		$minify->update_options( $options );

		wp_send_json_success();
	}

	/**
	 * Toggle minification on per site basis.
	 */
	public function minification_toggle_minification() {
		$this->check_permission();

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = rest_sanitize_boolean( wp_unslash( $_POST['value'] ) ); // Input var okay.

		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		$minify_module->toggle_service( $value );

		wp_send_json_success();
	}

	/**
	 * Toggle minification advanced view.
	 *
	 * @since 1.7.1
	 */
	public function minification_toggle_view() {
		$this->check_permission();

		if ( ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var okay.

		$available_types = array( 'basic', 'advanced' );

		if ( ! in_array( $type, $available_types, true ) ) {
			wp_send_json_error();
		}

		WP_Hummingbird_Settings::update_setting( 'view', $type, 'minify' );

		if ( 'basic' === $type ) {
			/* @var WP_Hummingbird_Module_Minify $minify_module */
			$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
			$minify_module->reset();
		}

		wp_send_json_success();
	}

	/**
	 * Start minification scan.
	 *
	 * Set a flag that marks the minification check files as started.
	 */
	public function minification_start_check() {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		$minify_module->init_scan();

		wp_send_json_success( array(
			'steps'    => $minify_module->scanner->get_scan_steps(),
		));
	}

	/**
	 * Process step during minification scan.
	 */
	public function minification_check_step() {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );

		$urls = $minify_module->scanner->get_scan_urls();
		$current_step = absint( $_POST['step'] );

		$minify_module->scanner->update_current_step( $current_step );

		if ( isset( $urls[ $current_step ] ) ) {
			$minify_module->scanner->scan_url( $urls[ $current_step ] );
		}

		wp_send_json_success();
	}

	public function minification_finish_scan() {
		delete_transient( 'wphb-minification-files-scanning' );
		update_option( 'wphb-minification-files-scanned', true );
		wp_send_json_success();
	}

	/**
	 * Save critical css on minification tools window.
	 *
	 * @since 1.8
	 */
	public function minification_save_critical_css() {
		$this->check_permission();

		parse_str( wp_unslash( $_POST['form'] ), $form );

		$status = WP_Hummingbird_Module_Minify::save_css( $form['critical_css'] );

		wp_send_json_success( array(
			'success' => $status['success'],
			'message' => $status['message'],
		));
	}

	/**
	 * Cancel minification file check if cancel button pressed.
	 *
	 * @since 1.4.5
	 */
	public function minification_cancel_scan() {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		$minify_module->toggle_service( false );
		$minify_module->clear_cache();

		wp_send_json_success();
	}

	/**
	 * Connect to Cloudflare.
	 */
	public function cloudflare_connect() {
		$this->check_permission();

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
		$cloudflare = WP_Hummingbird_Utils::get_module( 'cloudflare' );

		$options = $cloudflare->get_options();

		switch ( $step ) {
			case 'credentials': {
				$options['email'] = sanitize_email( $form_data['cloudflare-email'] );
				$options['api_key'] = sanitize_text_field( $form_data['cloudflare-api-key'] );
				$options['zone'] = sanitize_text_field( $form_data['cloudflare-zone'] );
				$options['zone_name'] = isset( $form_data['cloudflare-zone-name'] ) ? sanitize_text_field( $form_data['cloudflare-zone-name'] ) : '';

				$cloudflare->update_options( $options );

				$zones = $cloudflare->get_zones_list();

				if ( is_wp_error( $zones ) ) {
					wp_send_json_error( array(
						'message' => sprintf( '<strong>%s</strong> [%s]', $zones->get_error_message(), $zones->get_error_code() ),
					));
				}

				$cfData['email']  = $options['email'];
				$cfData['apiKey'] = $options['api_key'];
				$cfData['zones']  = $zones;

				$options['enabled'] = true;
				$cloudflare->update_options( $options );

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
				$options['zone'] = sanitize_text_field( $form_data['cloudflare-zone'] );

				if ( empty( $options['zone'] ) ) {
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
						'value' => $options['zone'],
					));
					if ( ! $filtered ) {
						wp_send_json_error( array(
							'message' => __( 'The selected zone is not valid', 'wphb' ),
						));
					}
					$options['zone_name'] = $filtered[0]['label'];
					$options['plan'] = $filtered[0]['plan'];
				}

				$options['enabled'] = true;
				$options['connected'] = true;

				$cloudflare->update_options( $options );
				$cfData['zone'] = $options['zone'];
				$cfData['zoneName'] = $options['zone_name'];
				$cfData['plan'] = $options['plan'];

				// And set the new CF setting.
				$cloudflare->set_caching_expiration( 691200 );

				$redirect = WP_Hummingbird_Utils::get_admin_menu_url( 'caching' );
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
		$this->check_permission();

		$value = absint( $_POST['value'] );

		/** @var WP_Hummingbird_Module_Cloudflare $cf */
		$cf = WP_Hummingbird_Utils::get_module( 'cloudflare' );

		$cf->set_caching_expiration( $value );

		wp_send_json_success();
	}

	/**
	 * Purge Cloudflare cache.
	 */
	public function cloudflare_purge_cache() {
		$this->check_permission();

		/** @var WP_Hummingbird_Module_Cloudflare $cf */
		$cf = WP_Hummingbird_Utils::get_module( 'cloudflare' );
		$cf->clear_cache();

		wp_send_json_success();
	}

	/**
	 * Dismiss notice.
	 *
	 * @since 1.6.1
	 */
	public function notice_dismiss() {
		$this->check_permission();

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
		$this->check_permission();

		update_site_option( 'wphb-cloudflare-dash-notice', 'dismissed' );

		wp_send_json_success();
	}

	/**
	 * Cleanup selected data type from db.
	 *
	 * @since 1.8
	 */
	public function advanced_db_delete_data() {
		$this->check_permission();

		$available_types = array( 'revisions', 'drafts', 'trash', 'spam', 'trash_comment', 'expired_transients', 'transients', 'all' );
		$type = sanitize_text_field( wp_unslash( $_POST['data'] ) );

		if ( ! in_array( $type, $available_types ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid type specified.', 'wphb' ),
			));
		}

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$removed = $adv_module->delete_db_data( $type );

		if ( ! is_array( $removed ) || ( 0 === $removed['items'] && 0 > $removed['left'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Error deleting data.', 'wphb' ),
			));
		}

		wp_send_json_success( array(
			/* translators: %d: number of database entries */
			'message' => sprintf( __( '<strong>%d database entries</strong> were deleted successfully.', 'wphb' ), $removed['items'] ),
			'left'    => $removed['left'],
		));
	}

	/**
	 * Update settings for advanced tools.
	 *
	 * @since 1.8
	 */
	public function advanced_save_settings() {
		$this->check_permission();

		$form = sanitize_text_field( wp_unslash( $_POST['form'] ) );
		parse_str( wp_unslash( $_POST['data'] ), $data );

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options = $adv_module->get_options();

		// General settings tab
		if ( 'advanced-general-settings' === $form ) {
			$options['query_string'] = rest_sanitize_boolean( $data['query_strings'] );
			$options['emoji']        = rest_sanitize_boolean( $data['emojis'] );
			$options['prefetch']     = preg_split( '/[\r\n\t ]+/', $data['url_strings'] );
		}

		// Database cleanup settings tab
		if ( 'advanced-db-settings' === $form ) {
			$tables = array(
				'revisions'          => ( isset( $data['revisions'] ) && 'on' === $data['revisions'] ) ? true : false,
				'drafts'             => ( isset( $data['drafts'] ) && 'on' === $data['drafts'] ) ? true : false,
				'trash'              => ( isset( $data['trash'] ) && 'on' === $data['trash'] ) ? true : false,
				'spam'               => ( isset( $data['spam'] ) && 'on' === $data['spam'] ) ? true : false,
				'trash_comment'      => ( isset( $data['trash_comment'] ) && 'on' === $data['trash_comment'] ) ? true : false,
				'expired_transients' => ( isset( $data['expired_transients'] ) && 'on' === $data['expired_transients'] ) ? true : false,
				'transients'         => ( isset( $data['transients'] ) && 'on' === $data['transients'] ) ? true : false,

			);

			$options['db_cleanups'] = false;
			if ( isset( $data['scheduled_cleanup'] ) && 'on' === $data['scheduled_cleanup'] ) {
				$options['db_cleanups']  = true;
			}

			$options['db_frequency'] = absint( $data['cleanup_frequency'] );
			$options['db_tables']    = $tables;
		}

		$adv_module->update_options( $options );
		wp_send_json_success( array( 'success' => true ) );
	}

	/**
	 * Save rss settings.
	 *
	 * @since 1.8
	 */
	public function rss_caching_save_settings() {
		$this->check_permission();

		parse_str( wp_unslash( $_POST['data'] ), $data );

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$rss_module = WP_Hummingbird_Utils::get_module( 'rss' );
		$options = $rss_module->get_options();

		$options['duration'] = isset( $data['rss-expiry-time'] ) ? absint( $data['rss-expiry-time'] ) : 0;

		$rss_module->update_options( $options );
		wp_send_json_success( array( 'success' => true ) );
	}

}