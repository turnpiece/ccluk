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
		// Parse clear cache click from frontend admin bar.
		add_action( 'wp_ajax_wphb_front_clear_cache', array( $this, 'clear_frontend_cache' ) );

		// Parse clear full cache from admin notice.
		add_action( 'wp_ajax_wphb_global_clear_cache', array( $this, 'clear_global_cache' ) );

		/**
		 * DASHBOARD AJAX ACTIONS
		 */

		// Skip quick setup.
		add_action( 'wp_ajax_wphb_dash_skip_setup', array( $this, 'dashboard_skip_setup' ) );
		// Dismiss notice.
		add_action( 'wp_ajax_wphb_notice_dismiss', array( $this, 'notice_dismiss' ) );
		// Dismiss notice.
		add_action( 'wp_ajax_wphb_cf_notice_dismiss', array( $this, 'cf_notice_dismiss' ) );

		/**
		 * PERFORMANCE TEST AJAX ACTIONS
		 */

		// Run performance test.
		add_action( 'wp_ajax_wphb_performance_run_test', array( $this, 'performance_run_test' ) );
		// Save performance settings.
		add_action( 'wp_ajax_wphb_performance_save_settings', array( $this, 'performance_save_settings' ) );
		// Show one-off performance modal.
		add_action( 'wp_ajax_wphb_show_report_modal', array( $this, 'show_report_modal' ) );

		/**
		 * CACHING MODULE AJAX ACTIONS
		 */

		// Clear cache.
		add_action( 'wp_ajax_wphb_clear_module_cache', array( $this, 'clear_module_cache' ) );

		/* PAGE CACHING */

		// Save page caching settings.
		add_action( 'wp_ajax_wphb_page_cache_save_settings', array( $this, 'page_cache_save_settings' ) );
		// Gutenberg clear cache for post.
		add_action( 'wp_ajax_wphb_gutenberg_clear_post_cache', array( $this, 'gutenberg_clear_post_cache' ) );

		/* BROWSER CACHING */

		// Activate browser caching.
		add_action( 'wp_ajax_wphb_caching_activate', array( $this, 'caching_activate' ) );
		// Re-check expiry.
		add_action( 'wp_ajax_wphb_caching_recheck_expiry', array( $this, 'caching_recheck_expiry' ) );
		// Set expiration for browser caching.
		add_action( 'wp_ajax_wphb_caching_set_expiration', array( $this, 'caching_set_expiration' ) );
		// Set server type.
		add_action( 'wp_ajax_wphb_caching_set_server_type', array( $this, 'caching_set_server_type' ) );
		// Reload snippet.
		add_action( 'wp_ajax_wphb_caching_reload_snippet', array( $this, 'caching_reload_snippet' ) );
		// Updat htaccess file.
		add_action( 'wp_ajax_wphb_caching_update_htaccess', array( $this, 'caching_update_htaccess' ) );
		// Cloudflare connect.
		add_action( 'wp_ajax_wphb_cloudflare_connect', array( $this, 'cloudflare_connect' ) );
		// Cloudflare expirtion cache.
		add_action( 'wp_ajax_wphb_cloudflare_set_expiry', array( $this, 'cloudflare_set_expiry' ) );
		// Cloudflare purge cache.
		add_action( 'wp_ajax_wphb_cloudflare_purge_cache', array( $this, 'cloudflare_purge_cache' ) );
		// Cloudflare recheck zones.
		add_action( 'wp_ajax_wphb_cloudflare_recheck_zones', array( $this, 'cloudflare_recheck_zones' ) );

		/* GRAVATAR CACHING */

		/* RSS CACHING */

		// Save settings for rss caching module.
		add_action( 'wp_ajax_wphb_rss_save_settings', array( $this, 'rss_save_settings' ) );

		/* CACHE SETTINGS */

		// Parse settings form.
		add_action( 'wp_ajax_wphb_other_cache_save_settings', array( $this, 'save_other_cache_settings' ) );

		/**
		 * ASSET OPTIMIZATION AJAX ACTIONS
		 */

		// Toggle CDN.
		add_action( 'wp_ajax_wphb_minification_toggle_cdn', array( $this, 'minification_toggle_cdn' ) );
		// Toggle logs.
		add_action( 'wp_ajax_wphb_minification_toggle_log', array( $this, 'minification_toggle_log' ) );
		// Toggle advanced minification view.
		add_action( 'wp_ajax_wphb_minification_toggle_view', array( $this, 'minification_toggle_view' ) );
		// Start scan.
		add_action( 'wp_ajax_wphb_minification_start_check', array( $this, 'minification_start_check' ) );
		// Scan check step.
		add_action( 'wp_ajax_wphb_minification_check_step', array( $this, 'minification_check_step' ) );
		// Cancel scan.
		add_action( 'wp_ajax_wphb_minification_cancel_scan', array( $this, 'minification_cancel_scan' ) );
		// Delete scan.
		add_action( 'wp_ajax_wphb_minification_finish_scan', array( $this, 'minification_finish_scan' ) );
		// Save critical css file.
		add_action( 'wp_ajax_wphb_minification_save_critical_css', array( $this, 'minification_save_critical_css' ) );
		// Update custom asset path.
		add_action( 'wp_ajax_wphb_minification_update_asset_path', array( $this, 'minification_update_asset_path' ) );
		// Reset individual file.
		add_action( 'wp_ajax_wphb_minification_reset_asset', array( $this, 'minification_reset_asset' ) );
		// Update settings in network admin.
		add_action( 'wp_ajax_wphb_minification_update_network_settings', array( $this, 'minification_update_network_settings' ) );

		/**
		 * ADVANCED TOOLS AJAX ACTIONS
		 */

		// Clean database.
		add_action( 'wp_ajax_wphb_advanced_db_delete_data', array( $this, 'advanced_db_delete_data' ) );
		// Save settings in advanced tools module.
		add_action( 'wp_ajax_wphb_advanced_save_settings', array( $this, 'advanced_save_settings' ) );

		/**
		 * LOGGER MODULE AJAX ACTIONS
		 */
		add_action( 'wp_ajax_wphb_logger_clear', array( $this, 'logger_clear' ) );

		/**
		 * SETTINGS MODULE AJAX ACTIONS
		 */

		add_action( 'wp_ajax_wphb_admin_settings_save_settings', array( $this, 'admin_settings_save_settings' ) );
		// Reset settings.
		add_action( 'wp_ajax_wphb_reset_settings', array( $this, 'reset_settings' ) );
	}

	/**
	 * Handle clear cache button click from the frontend top admin bar.
	 *
	 * @since 1.9.3
	 */
	public function clear_frontend_cache() {
		$pc_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$status    = $pc_module->clear_cache();

		if ( ! $status ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Handle clear cache button click from the frontend top admin bar.
	 *
	 * @since 1.9.3
	 */
	public function clear_global_cache() {
		$modules = WP_Hummingbird_Utils::get_active_cache_modules();

		foreach ( $modules as $module => $name ) {
			/* @var WP_Hummingbird_Module|WP_Hummingbird_Module_Minify $mod */
			$mod = WP_Hummingbird_Utils::get_module( $module );

			if ( ! $mod->is_active() ) {
				continue;
			}

			if ( 'minify' === $module ) {
				$mod->clear_files();
			} else {
				$mod->clear_cache();
			}
		}

		// Remove notice.
		delete_option( 'wphb-notice-cache-cleaned-show' );

		wp_send_json_success();
	}

	/**
	 * *************************
	 * DASHBOARD AJAX ACTIONS
	 ***************************/

	/**
	 * Skip quick setup and go straight to dashboard.
	 *
	 * @since 1.5.0
	 */
	public function dashboard_skip_setup() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		WP_Hummingbird_Utils::remove_quick_setup();

		wp_send_json_success(
			array(
				'finished' => false,
			)
		);
	}

	/**
	 * Dismiss notice.
	 *
	 * @since 1.6.1
	 */
	public function notice_dismiss() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['id'] ) ) { // Input var okay.
			die();
		}

		$notice_id = sanitize_text_field( wp_unslash( $_POST['id'] ) ); // Input var ok.

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

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		update_site_option( 'wphb-cloudflare-dash-notice', 'dismissed' );

		wp_send_json_success();
	}

	/**
	 * *************************
	 * PERFORMANCE TEST AJAX ACTIONS
	 ***************************/

	/**
	 * Run performance test.
	 *
	 * Ajax will trigger this method every 3 seconds, until 'finished' = true.
	 * Logic behind this:
	 * - Remove quick setup (if not removed) and init performance scan (if not running)
	 * - Running < 15 seconds  - return control to ajax
	 * - Running 15-89 seconds - check if report is on the server, if not - return to ajax
	 * - Running 90+ seconds   - stop performance test
	 */
	public function performance_run_test() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		// This comes from performance report modal on page scans.
		$name  = filter_input( INPUT_POST, 'user', FILTER_SANITIZE_STRING );
		$email = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );
		$url   = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL, FILTER_NULL_ON_FAILURE );

		// Remove quick setup only when running regular performance report.
		if ( ! $name && ! $email && ! $url ) {
			$quick_setup = get_option( 'wphb-quick-setup' );
			if ( ! isset( $quick_setup['finished'] ) ) {
				WP_Hummingbird_Utils::remove_quick_setup();
			}
		}

		$started_at = WP_Hummingbird_Module_Performance::is_doing_report();
		if ( ! $started_at ) {
			WP_Hummingbird_Utils::get_module( 'performance' )->init_scan( $url );
			wp_send_json_success( array( 'finished' => false ) );
		}

		$now = current_time( 'timestamp' );
		if ( $now >= ( $started_at + 15 ) ) {
			// If we're over 1 minute - timeout.
			if ( $now >= ( $started_at + 90 ) ) {
				WP_Hummingbird_Module_Performance::set_doing_report( false );
				wp_send_json_success( array( 'finished' => true ) );
			}

			// The report should be finished by this time, let's get the results.
			$report = WP_Hummingbird_Module_Performance::refresh_report( $url );

			if ( ! $name && ! $email && ! $url ) {
				$report = WP_Hummingbird_Module_Performance::get_last_report();
			}

			// Do not cancel the scan if the report is not ready. We might still have some time to wait.
			if ( is_wp_error( $report ) ) {
				// Check if the report is still not available on the server.
				$error = $report->get_error_data( 'performance-error' );
				if ( isset( $error['details'] ) && 'Performance Results not found' === $error['details'] ) {
					WP_Hummingbird_Settings::delete( 'wphb-stop-report' );
					wp_send_json_success( array( 'finished' => false ) );
				}
			}

			if ( isset( $name ) && ! empty( $name ) && isset( $email ) && ! empty( $email ) && isset( $url ) && ! empty( $url ) ) {
				$recipients = array(
					'name'  => $name,
					'email' => $email,
				);

				WP_Hummingbird_Utils::get_pro_module( 'reporting-cron' )->send_email_report( $report, $recipients );
			}

			wp_send_json_success( array( 'finished' => true ) );
		}

		// Just do nothing until the report is finished.
		wp_send_json_success( array( 'finished' => false ) );
	}

	/**
	 * Process scan settings.
	 *
	 * @since 1.7.1
	 */
	public function performance_save_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['data'] ) ) { // Input var okay.
			die();
		}

		$performance = WP_Hummingbird_Utils::get_module( 'performance' );
		$options     = $performance->get_options();

		// Get the data from ajax.
		parse_str( sanitize_text_field( wp_unslash( $_POST['data'] ) ), $data ); // Input var ok.

		// This option can only be updated on network admin.
		if ( ! is_multisite() || ( is_multisite() && isset( $data['network_admin'] ) && $data['network_admin'] ) ) {
			// I don't like the way this is duplicated in three different modules. This needs to be extracted.
			$options['subsite_tests'] = isset( $data['subsite-tests'] ) && 'super-admins' !== $data['subsite-tests'] ? (bool) $data['subsite-tests'] : 'super-admins';

			if ( WP_Hummingbird_Utils::is_member() ) {
				$options['hub']['show_metrics']  = isset( $data['hub-metrics'] ) ? (bool) $data['hub-metrics'] : false;
				$options['hub']['show_audits']   = isset( $data['hub-audits'] ) ? (bool) $data['hub-audits'] : false;
				$options['hub']['show_historic'] = isset( $data['hub-field-data'] ) ? (bool) $data['hub-field-data'] : false;
			}
		}
		$options['widget']['desktop']       = isset( $data['desktop-report'] ) ? (bool) $data['desktop-report'] : false;
		$options['widget']['show_metrics']  = isset( $data['metrics'] ) ? (bool) $data['metrics'] : false;
		$options['widget']['show_audits']   = isset( $data['audits'] ) ? (bool) $data['audits'] : false;
		$options['widget']['show_historic'] = isset( $data['field-data'] ) ? (bool) $data['field-data'] : false;


		$performance->update_options( $options );

		wp_send_json_success();
	}

	/**
	 * Show one-off performance report modal.
	 *
	 * @since 2.0.0
	 */
	public function show_report_modal() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );
	}

	/**
	 * *************************
	 * CACHING MODULE AJAX ACTIONS
	 ***************************/

	/**
	 * Purge cache for selected module.
	 *
	 * @since 1.9.0
	 */
	public function clear_module_cache() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['module'] ) ) { // Input var okay.
			die();
		}

		$modules = array( 'page_cache', 'gravatar' );
		$module  = sanitize_text_field( wp_unslash( $_POST['module'] ) ); // Input var ok.

		// Works only for supported modules.
		if ( ! in_array( $module, $modules, true ) ) {
			wp_send_json_success(
				array(
					'success' => false,
				)
			);
		}

		$status = WP_Hummingbird_Utils::get_module( $module )->clear_cache();
		wp_send_json_success(
			array(
				'success' => $status,
			)
		);
	}

	/**
	 * Save page caching settings.
	 *
	 * @since 1.9.0
	 */
	public function page_cache_save_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['data'] ) ) { // Input var okay.
			die();
		}

		parse_str( wp_unslash( $_POST['data'] ), $data ); // Input var ok.

		$admins_can_disable_pc = false;
		$page_types            = array();
		if ( isset( $data['page_types'] ) && is_array( $data['page_types'] ) ) { // Input var ok.
			$page_types = array_keys( wp_unslash( $data['page_types'] ) ); // Input var ok.
		}

		$custom_post_types = array();
		if ( isset( $data['custom_post_types'] ) && is_array( $data['custom_post_types'] ) ) { // Input var ok.
			$custom_post_types_data = wp_unslash( $data['custom_post_types'] ); // Input var ok.
			foreach ( $custom_post_types_data as $custom_post_type => $value ) {
				if ( $value ) {
					$custom_post_types[] = $custom_post_type;
				}
			}
		}

		$cache_settings = array(
			'logged_in'        => 0,
			'url_queries'      => 0,
			'cache_404'        => 0,
			'clear_update'     => 0,
			'debug_log'        => 0,
			'cache_identifier' => 0,
		);

		if ( isset( $data['settings'] ) ) { // Input var ok.
			$form_data                          = $data['settings']; // Input var ok.
			$cache_settings['logged_in']        = isset( $form_data['logged-in'] ) ? absint( $form_data['logged-in'] ) : 0;
			$cache_settings['url_queries']      = isset( $form_data['url-queries'] ) ? absint( $form_data['url-queries'] ) : 0;
			$cache_settings['cache_404']        = isset( $form_data['cache-404'] ) ? absint( $form_data['cache-404'] ) : 0;
			$cache_settings['clear_update']     = isset( $form_data['clear-update'] ) ? absint( $form_data['clear-update'] ) : 0;
			$cache_settings['debug_log']        = isset( $form_data['debug-log'] ) ? absint( $form_data['debug-log'] ) : 0;
			$cache_settings['cache_identifier'] = isset( $form_data['cache-identifier'] ) ? absint( $form_data['cache-identifier'] ) : 0;

			if ( isset( $form_data['admins_disable_caching'] ) && 1 === absint( $form_data['admins_disable_caching'] ) ) {
				$admins_can_disable_pc = true;
			}
		}

		$url_strings = '';
		if ( isset( $data['url_strings'] ) ) { // Input var ok.
			$url_strings = sanitize_textarea_field( wp_unslash( $data['url_strings'] ) ); // Input var okay.
			$url_strings = preg_split( '/[\r\n\t ]+/', $url_strings );

			foreach ( $url_strings as $id => $string ) {
				$string             = str_replace( '\\', '', $string );
				$string             = str_replace( '/', '\/', $string );
				$string             = preg_replace( '/.php$/', '\\.php', $string );
				$url_strings[ $id ] = $string;
			}
		}

		$user_agents = '';
		if ( isset( $data['user_agents'] ) ) { // Input var ok.
			$user_agents = sanitize_textarea_field( wp_unslash( $data['user_agents'] ) ); // Input var okay.
			$user_agents = preg_split( '/[\r\n\t]+/', $user_agents );
		}

		$settings['page_types']             = $page_types;
		$settings['custom_post_types']      = $custom_post_types;
		$settings['settings']               = $cache_settings;
		$settings['exclude']['url_strings'] = $url_strings;
		$settings['exclude']['user_agents'] = $user_agents;

		/* @var WP_Hummingbird_Module_Page_Cache $module */
		$module  = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$options = $module->get_options();

		if ( $admins_can_disable_pc ) {
			$options['enabled'] = 'blog-admins';
		} elseif ( $module->is_active() ) {
			$options['enabled'] = true;
		}

		$module->update_options( $options );
		$module->save_settings( $settings );

		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * Clear cache for selected page, when 'clear cache' button is clicked from Gutenberg post edit screen.
	 *
	 * @since 1.9.4
	 */
	public function gutenberg_clear_post_cache() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) || ! isset( $_POST['postId'] ) ) { // Input var okay.
			die();
		}

		$id = absint( wp_unslash( $_POST['postId'] ) );

		WP_Hummingbird_Utils::get_module( 'page_cache' )->clear_cache_action( $id );

		wp_send_json_success();
	}

	/**
	 * Activate browser caching.
	 *
	 * @since 1.9.0
	 */
	public function caching_activate() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		// Enable caching in .htaccess (only for apache servers).
		$result = WP_Hummingbird_Module_Server::save_htaccess( 'caching' );
		if ( $result ) {
			// Clear saved status.
			WP_Hummingbird_Utils::get_module( 'caching' )->clear_cache();
			wp_send_json_success(
				array(
					'success' => true,
				)
			);
		}

		wp_send_json_error();
	}

	/**
	 * Re-check browser expiry button clicked.
	 *
	 * @since 1.9.0
	 */
	public function caching_recheck_expiry() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		$status = WP_Hummingbird_Utils::get_status( 'caching', true );

		$expiry_values = array_map( array( 'WP_Hummingbird_Utils', 'human_read_time_diff' ), $status );

		wp_send_json_success(
			array(
				'success'       => true,
				'expiry_values' => $expiry_values,
			)
		);
	}

	/**
	 * Set expiration for browser caching.
	 */
	public function caching_set_expiration() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['expiry_times'] ) ) { // Input var okay.
			die();
		}

		$sanitized_expiry_times                      = array();
		$sanitized_expiry_times['expiry_javascript'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_javascript'] ) ); // Input var ok.
		$sanitized_expiry_times['expiry_css']        = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_css'] ) ); // Input var ok.
		$sanitized_expiry_times['expiry_media']      = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_media'] ) ); // Input var ok.
		$sanitized_expiry_times['expiry_images']     = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_images'] ) ); // Input var ok.

		$frequencies = WP_Hummingbird_Utils::get_caching_frequencies();

		foreach ( $sanitized_expiry_times as $value ) {
			if ( ! isset( $frequencies[ $value ] ) ) {
				die();
			}
		}

		/* @var WP_Hummingbird_Module_Caching $caching */
		$caching                      = WP_Hummingbird_Utils::get_module( 'caching' );
		$options                      = $caching->get_options();
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
		do_action(
			'wphb_caching_set_expiration',
			array(
				'expiry_times' => $sanitized_expiry_times,
			)
		);

		wp_send_json_success();
	}

	/**
	 * Set server type.
	 */
	public function caching_set_server_type() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var okay.

		if ( ! array_key_exists( $value, WP_Hummingbird_Module_Server::get_servers() ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Reload snippet after new expiration interval has been selected.
	 */
	public function caching_reload_snippet() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		if ( ! isset( $_POST['type'] ) || ! isset( $_POST['expiry_times'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['type'] ) ); // Input var okay.
		// Check if Clouflare value (array won't exist).
		if ( ! strpos( wp_unslash( $_POST['expiry_times']['expiry_javascript'] ), '/A' ) ) { // Input var ok.
			// Convert to readable value.
			$frequency                                   = WP_Hummingbird_Utils::convert_cloudflare_frequency( (int) $_POST['expiry_times']['expiry_javascript'] ); // Input var ok.
			$sanitized_expiry_times                      = array();
			$sanitized_expiry_times['expiry_javascript'] = $frequency;
			$sanitized_expiry_times['expiry_css']        = $frequency;
			$sanitized_expiry_times['expiry_media']      = $frequency;
			$sanitized_expiry_times['expiry_images']     = $frequency;
		} else {
			$sanitized_expiry_times                      = array();
			$sanitized_expiry_times['expiry_javascript'] = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_javascript'] ) ); // Input var ok.
			$sanitized_expiry_times['expiry_css']        = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_css'] ) ); // Input var ok.
			$sanitized_expiry_times['expiry_media']      = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_media'] ) ); // Input var ok.
			$sanitized_expiry_times['expiry_images']     = sanitize_text_field( wp_unslash( $_POST['expiry_times']['expiry_images'] ) ); // Input var ok.
		}

		$code = WP_Hummingbird_Module_Server::get_code_snippet( 'caching', $type, $sanitized_expiry_times );

		wp_send_json_success(
			array(
				'type' => $type,
				'code' => $code,
			)
		);
	}

	/**
	 * Update htaccess file.
	 *
	 * @since 1.9.0
	 */
	public function caching_update_htaccess() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		WP_Hummingbird_Module_Server::unsave_htaccess( 'caching' );

		wp_send_json_success(
			array(
				'success' => WP_Hummingbird_Module_Server::save_htaccess( 'caching' ),
			)
		);
	}

	/**
	 * Connect to Cloudflare.
	 */
	public function cloudflare_connect() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		if ( ! isset( $_POST['formData'] ) || ! isset( $_POST['step'] ) ) { // Input var okay.
			die();
		}

		$form_data = wp_unslash( $_POST['formData'] ); // Input var okay.
		$form_data = wp_parse_args(
			$form_data,
			array(
				'cloudflare-email'   => '',
				'cloudflare-api-key' => '',
				'cloudflare-zone'    => '',
			)
		);

		$step    = sanitize_text_field( wp_unslash( $_POST['step'] ) ); // Input var okay.
		$cf_data = wp_unslash( $_POST['cfData'] ); // Input var okay.

		/* @var WP_Hummingbird_Module_Cloudflare $cloudflare */
		$cloudflare = WP_Hummingbird_Utils::get_module( 'cloudflare' );

		$options = $cloudflare->get_options();

		switch ( $step ) {
			case 'credentials':
			default: {
				$options['email']     = sanitize_email( $form_data['cloudflare-email'] );
				$options['api_key']   = sanitize_text_field( $form_data['cloudflare-api-key'] );
				$options['zone']      = sanitize_text_field( $form_data['cloudflare-zone'] );
				$options['zone_name'] = isset( $form_data['cloudflare-zone-name'] ) ? sanitize_text_field( $form_data['cloudflare-zone-name'] ) : '';

				$cloudflare->update_options( $options );

				$zones = $cloudflare->get_zones_list();

				if ( is_wp_error( $zones ) ) {
					wp_send_json_error(
						array(
							'message' => sprintf( '<strong>%s</strong> [%s]', $zones->get_error_message(), $zones->get_error_code() ),
						)
					);
				}

				$cf_data['email']  = $options['email'];
				$cf_data['apiKey'] = $options['api_key'];
				$cf_data['zones']  = $zones;

				$options['enabled'] = true;
				$cloudflare->update_options( $options );

				// Try to auto select domain.
				$site_url      = network_site_url();
				$site_url      = rtrim( preg_replace( '/^https?:\/\//', '', $site_url ), '/' );
				$plucked_zones = wp_list_pluck( $zones, 'label' );
				$found         = preg_grep( '/.*' . $site_url . '.*/', $plucked_zones );
				if ( is_array( $found ) && count( $found ) === 1 && isset( $zones[ key( $found ) ]['value'] ) ) {
					// Select the domain and cheat this function.
					$zone_found        = $zones[ key( $found ) ]['value'];
					$_POST['formData'] = array(
						'cloudflare-zone' => $zone_found,
					);
					$_POST['step']     = 'zone';
					$_POST['cfData']   = $cf_data;
					$this->cloudflare_connect();
				}

				wp_send_json_success(
					array(
						'nextStep' => 'zone',
						'newData'  => $cf_data,
					)
				);
				break;
			} // End case().
			case 'zone': {
				$options['zone'] = sanitize_text_field( $form_data['cloudflare-zone'] );

				if ( empty( $options['zone'] ) ) {
					wp_send_json_error(
						array(
							'message' => __( 'Please, select a Cloudflare zone. Normally, this is your website', 'wphb' ),
						)
					);
				}

				// Check that the zone exists.
				$zones = $cloudflare->get_zones_list();
				if ( is_wp_error( $zones ) ) {
					wp_send_json_error(
						array(
							'message' => sprintf( '<strong>%s</strong> [%s]', $zones->get_error_message(), $zones->get_error_code() ),
						)
					);
				} else {
					$filtered = array_values(
						wp_list_filter(
							$zones,
							array(
								'value' => $options['zone'],
							)
						)
					);
					if ( ! $filtered ) {
						wp_send_json_error(
							array(
								'message' => __( 'The selected zone is not valid', 'wphb' ),
							)
						);
					}
					$options['zone_name'] = $filtered[0]['label'];
					$options['plan']      = $filtered[0]['plan'];
				}

				$options['enabled']   = true;
				$options['connected'] = true;

				$cloudflare->update_options( $options );
				$cf_data['zone']     = $options['zone'];
				$cf_data['zoneName'] = $options['zone_name'];
				$cf_data['plan']     = $options['plan'];

				// And set the new CF setting.
				$cloudflare->set_caching_expiration( 691200 );

				$redirect = WP_Hummingbird_Utils::get_admin_menu_url( 'caching' );
				wp_send_json_success(
					array(
						'nextStep' => 'final',
						'newData'  => $cf_data,
						'redirect' => $redirect,
					)
				);
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

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = absint( $_POST['value'] ); // Input var ok.

		$result = WP_Hummingbird_Utils::get_module( 'cloudflare' )->set_caching_expiration( $value );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error();
			return;
		}

		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * Purge Cloudflare cache.
	 */
	public function cloudflare_purge_cache() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		/* @var WP_Hummingbird_Module_Cloudflare $cf */
		$cf = WP_Hummingbird_Utils::get_module( 'cloudflare' );
		$cf->clear_cache();

		wp_send_json_success();
	}

	/**
	 * Recheck Cloudflare zones.
	 */
	public function cloudflare_recheck_zones() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		/* @var WP_Hummingbird_Module_Cloudflare $cf */
		$cf    = WP_Hummingbird_Utils::get_module( 'cloudflare' );
		$zones = $cf->get_zones_list();
		foreach ( $zones as $zone ) {
			if ( strpos( get_site_url(), $zone['label'] ) ) {
				wp_send_json_success(
					array(
						'zones' => $zones,
					)
				);
			}
		}
		wp_send_json_error(
			array(
				'message' => __( 'Zone not found matching this domain. Please check your CloudFlare account.', 'wphb' ),
			)
		);
	}

	/**
	 * Save rss settings.
	 *
	 * @since 1.8
	 */
	public function rss_save_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['data'] ) ) { // Input var okay.
			die();
		}

		parse_str( sanitize_text_field( wp_unslash( $_POST['data'] ) ), $data ); // Input var ok.

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$rss_module = WP_Hummingbird_Utils::get_module( 'rss' );
		$options    = $rss_module->get_options();

		$options['duration'] = isset( $data['rss-expiry-time'] ) ? absint( $data['rss-expiry-time'] ) : 0;

		$rss_module->update_options( $options );
		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * Parse save cache settings form.
	 *
	 * @since 1.8.1
	 */
	public function save_other_cache_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['data'] ) ) { // Input var okay.
			die();
		}

		parse_str( sanitize_text_field( wp_unslash( $_POST['data'] ) ), $data ); // Input var ok.

		/* @var WP_Hummingbird_Module_Page_Cache $pc_module */
		$pc_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$options   = $pc_module->get_options();

		$options['control']   = ( isset( $data['cc_button'] ) && 'on' === $data['cc_button'] ) ? true : false;
		$options['detection'] = isset( $data['detection'] ) ? sanitize_text_field( $data['detection'] ) : 'manual';

		// Remove notice if File Change Detection is set to 'auto' or 'none'.
		if ( 'auto' === $options['detection'] || 'none' === $options['detection'] ) {
			delete_option( 'wphb-notice-cache-cleaned-show' );
		}

		$pc_module->update_options( $options );
		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * *************************
	 * ASSET OPTIMIZATION AJAX ACTIONS
	 ***************************/

	/**
	 * Toggle CDN.
	 *
	 * Used on dashboard page in minification meta box and in the minification module.
	 * Clear files function at the end clears all cache and on first home page reload, all the files will
	 * be either moved to CDN or stored local.
	 */
	public function minification_toggle_cdn() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['value'] ) ) { // Input var okay.
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
	 * Toggle logs.
	 *
	 * @since 1.7.2
	 */
	public function minification_toggle_log() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$value = rest_sanitize_boolean( wp_unslash( $_POST['value'] ) ); // Input var okay.

		/* @var WP_Hummingbird_Module_Minify $minify */
		$minify         = WP_Hummingbird_Utils::get_module( 'minify' );
		$options        = $minify->get_options();
		$options['log'] = $value;
		$minify->update_options( $options );

		wp_send_json_success();
	}

	/**
	 * Toggle minification advanced view.
	 *
	 * @since 1.7.1
	 */
	public function minification_toggle_view() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['value'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var okay.

		$available_types = array( 'basic', 'advanced' );

		if ( ! in_array( $type, $available_types, true ) ) {
			wp_send_json_error();
		}

		WP_Hummingbird_Settings::update_setting( 'view', $type, 'minify' );

		if ( 'basic' === $type ) {
			WP_Hummingbird_Utils::get_module( 'minify' )->reset( false );
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

		wp_send_json_success(
			array(
				'steps' => $minify_module->scanner->get_scan_steps(),
			)
		);
	}

	/**
	 * Process step during minification scan.
	 */
	public function minification_check_step() {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );

		$urls         = $minify_module->scanner->get_scan_urls();
		$current_step = absint( $_POST['step'] ); // Input var ok.

		$minify_module->scanner->update_current_step( $current_step );

		if ( isset( $urls[ $current_step ] ) ) {
			$minify_module->scanner->scan_url( $urls[ $current_step ] );
		}

		wp_send_json_success();
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
	 * Finish minification scan.
	 */
	public function minification_finish_scan() {
		delete_transient( 'wphb-minification-files-scanning' );
		update_option( 'wphb-minification-files-scanned', true );
		wp_send_json_success(
			array(
				'assets_msg' => sprintf(
					/* translators: %s - number of assets */
					esc_html__( '%s assets found!', 'wphb' ),
					WP_Hummingbird_Utils::minified_files_count()
				),
			)
		);
	}

	/**
	 * Save critical css on minification tools window.
	 *
	 * @since 1.8
	 */
	public function minification_save_critical_css() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['form'] ) ) { // Input var okay.
			die();
		}

		parse_str( wp_unslash( $_POST['form'] ), $form ); // Input var ok.

		$status = WP_Hummingbird_Module_Minify::save_css( $form['critical_css'] );

		wp_send_json_success(
			array(
				'success' => $status['success'],
				'message' => $status['message'],
			)
		);
	}

	/**
	 * Parse custom asset path directory.
	 *
	 * @since 1.9
	 */
	public function minification_update_asset_path() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['value'] ) ) { // Input var ok.
			die();
		}

		$path = sanitize_text_field( wp_unslash( $_POST['value'] ) ); // Input var ok.

		// Get current setting value.
		$current_path = WP_Hummingbird_Settings::get_setting( 'file_path', 'minify' );

		WP_Hummingbird_Utils::get_module( 'minify' )->clear_cache( false );

		if ( isset( $current_path ) && ! empty( $current_path ) ) {
			WP_Hummingbird_Filesystem::instance()->purge( $current_path, true );
		}

		// Update to new setting value.
		WP_Hummingbird_Settings::update_setting( 'file_path', $path, 'minify' );

		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * Reset individual file.
	 *
	 * @since 1.9.2
	 */
	public function minification_reset_asset() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['value'] ) ) { // Input var ok.
			die();
		}

		$files = explode( ' ', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // Input var ok.

		$type = $handle = '';
		foreach ( $files as $item ) {
			if ( 'css' === strtolower( $item ) ) {
				$type = 'styles';
				continue;
			}

			if ( 'js' === strtolower( $item ) ) {
				$type = 'scripts';
				continue;
			}

			$handle = $item;
		}

		if ( ! $handle || ! $type ) {
			wp_send_json_error(
				array(
					'message' => __( 'Error removing asset file.', 'wphb' ),
				)
			);
		}

		WP_Hummingbird_Utils::get_module( 'minify' )->clear_file( $handle, $type );

		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * Update network settings.
	 *
	 * @since 2.0.0
	 */
	public function minification_update_network_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['settings'] ) ) { // Input var okay.
			die();
		}

		wp_parse_str( sanitize_text_field( wp_unslash( $_POST['settings'] ) ), $form );

		if ( isset( $form['enabled'] ) && 'super-admins' !== $form['enabled'] ) {
			$form['enabled'] = (bool) $form['enabled'];
		}

		$minify  = WP_Hummingbird_Utils::get_module( 'minify' );
		$options = $minify->get_options();

		$options['use_cdn'] = isset( $form['use_cdn'] ) ? (bool) $form['use_cdn'] : false;
		$options['log']     = isset( $form['log'] ) ? (bool) $form['log'] : false;

		$minify->update_options( $options );
		if ( ! isset( $form['network'] ) ) {
			$minify->toggle_service( false, true );
		} else {
			$minify->toggle_service( $form['enabled'], true );
		}

		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * *************************
	 * ADVANCED TOOLS AJAX ACTIONS
	 ***************************/

	/**
	 * Cleanup selected data type from db.
	 *
	 * @since 1.8
	 */
	public function advanced_db_delete_data() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['data'] ) ) { // Input var okay.
			die();
		}

		$available_types = array( 'revisions', 'drafts', 'trash', 'spam', 'trash_comment', 'expired_transients', 'transients', 'all' );
		$type            = sanitize_text_field( wp_unslash( $_POST['data'] ) ); // Input var ok.

		if ( ! in_array( $type, $available_types, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid type specified.', 'wphb' ),
				)
			);
		}

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$removed    = $adv_module->delete_db_data( $type );

		if ( ! is_array( $removed ) || ( 0 === $removed['items'] && 0 > $removed['left'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Error deleting data.', 'wphb' ),
				)
			);
		}

		wp_send_json_success(
			array(
				/* translators: %d: number of database entries */
				'message' => sprintf( __( '<strong>%d database entries</strong> were deleted successfully.', 'wphb' ), $removed['items'] ),
				'left'    => $removed['left'],
			)
		);
	}

	/**
	 * Update settings for advanced tools.
	 *
	 * @since 1.8
	 */
	public function advanced_save_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['form'] ) ) { // Input var okay.
			die();
		}

		$form = sanitize_text_field( wp_unslash( $_POST['form'] ) ); // Input var ok.
		parse_str( wp_unslash( $_POST['data'] ), $data ); // Input var ok.

		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options    = $adv_module->get_options();

		// General settings tab.
		if ( 'advanced-general-settings' === $form ) {
			$options['query_string'] = ( isset( $data['query_strings'] ) && 'on' === $data['query_strings'] ) ? true : false;
			$options['emoji']        = ( isset( $data['emojis'] ) && 'on' === $data['emojis'] ) ? true : false;
			$options['prefetch']     = array();
			if ( isset( $data['url_strings'] ) && ! empty( $data['url_strings'] ) ) {
				$options['prefetch'] = preg_split( '/[\r\n\t ]+/', $data['url_strings'] );
			}
		}

		// Database cleanup settings tab.
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
				$options['db_cleanups'] = true;
			}

			$options['db_frequency'] = absint( $data['cleanup_frequency'] );
			$options['db_tables']    = $tables;
		}

		$adv_module->update_options( $options );
		wp_send_json_success(
			array(
				'success' => true,
			)
		);
	}

	/**
	 * *************************
	 * LOGGER MODULE AJAX ACTIONS
	 ***************************/

	/**
	 * Clear logs.
	 *
	 * @since 1.9.2
	 */
	public function logger_clear() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['module'] ) ) { // Input var okay.
			die();
		}

		$slug = sanitize_text_field( wp_unslash( $_POST['module'] ) ); // Input var ok.

		$module = WP_Hummingbird_Utils::get_module( $slug );

		if ( ! $module ) {
			wp_send_json_success(
				array(
					'success' => false,
					'message' => __( 'Module not found', 'wphb' ),
				)
			);
		}

		$status = WP_Hummingbird::get_instance()->core->logger->clear( $slug );

		if ( ! $status ) {
			wp_send_json_success(
				array(
					'success' => false,
					'message' => __( 'Log file not found or empty', 'wphb' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'success' => true,
				'message' => __( 'Log file purged', 'wphb' ),
			)
		);
	}

	/**
	 * *************************
	 * HUMMINGBIRD ADMIN SETTINGS AJAX ACTIONS
	 ***************************/

	/**
	 * Save Admin settings.
	 *
	 * @since 1.9.3
	 */
	public function admin_settings_save_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) || ! isset( $_POST['form_data'] ) ) { // Input var okay.
			die();
		}
		parse_str( sanitize_text_field( wp_unslash( $_POST['form_data'] ) ), $data ); // Input var ok.

		$settings = WP_Hummingbird_Settings::get_settings( 'settings' );

		foreach ( $data as $setting => $value ) {
			if ( ! isset( $settings[ $setting ] ) ) {
				continue;
			}

			$settings[ $setting ] = (bool) $value;
		}

		WP_Hummingbird_Settings::update_settings( $settings, 'settings' );

		wp_send_json_success();
	}

	/**
	 * Reset plugin settings.
	 *
	 * @since 2.0.0
	 */
	public function reset_settings() {
		check_ajax_referer( 'wphb-fetch', 'nonce' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			die();
		}

		wp_send_json_success();
	}

}
