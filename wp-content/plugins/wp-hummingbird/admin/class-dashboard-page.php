<?php

/**
 * Class WP_Hummingbird_Dashboard_Page.
 */
class WP_Hummingbird_Dashboard_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Function triggered when the page is loaded
	 * before render any content
	 */
	public function on_load() {
		if ( is_multisite() && ! is_network_admin() ) {
			wphb_minification_maybe_stop_scanning_files();
		}

		if ( isset( $_GET['wphb-clear-files'] ) && current_user_can( wphb_get_admin_capability() ) ) {
			check_admin_referer( 'wphb-clear-files' );

			wphb_minification_clear_files();
			$url = remove_query_arg( array( 'wphb-clear-files', 'updated', '_wpnonce' ) );

			if ( wphb_cloudflare_is_active() ) {
				/* @var WP_Hummingbird_Module_Cloudflare $cf */
				$cf = wphb_get_module( 'cloudflare' );
				$cf->purge_cache();
				wp_safe_redirect( add_query_arg( 'wphb-cache-cleared-with-cloudflare', 'true', $url ) );
			} else {
				wp_safe_redirect( add_query_arg( 'wphb-cache-cleared', 'true', $url ) );
			}
			exit;
		}
	}

	/**
	 * Overwrites parent class render_header method.
	 *
	 * Renders the template header that is repeated on every page.
	 * From WPMU DEV Dashboard
	 */
	public function render_header() {
		$clear_cache_url = add_query_arg( 'wphb-clear-files', 'true' );
		$clear_cache_url = wp_nonce_url( $clear_cache_url, 'wphb-clear-files' );

		if ( isset( $_GET['wphb-cache-cleared'] ) ) {
			$this->show_notice( 'updated', __( 'Your cache has been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ), 'success', true );
		}

		if ( isset( $_GET['wphb-cache-cleared-with-cloudflare'] ) ) {
			$this->show_notice( 'updated', __( 'Your local and Cloudflare caches have been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ), 'success', true );
		}
		?>
		<section id="header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div class="actions">
				<?php if ( true === WP_Hummingbird_Module_Performance::can_run_test() ) : ?>
					<?php
					$scan_link = add_query_arg(
						array(
							'run' => 'true',
							'type' => 'performance',
						),
						wphb_get_admin_menu_url( '' )
					);
					$scan_link = wp_nonce_url( $scan_link, 'wphb-run-dashboard' ) . '#wphb-box-dashboard-performance-running-test';
					?>
					<a href="<?php echo esc_url( $scan_link ); ?>" class="button"><?php esc_html_e( 'Run Test', 'wphb' ); ?></a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $clear_cache_url ); ?>" class="button button-ghost"><?php esc_html_e( 'Clear Cache', 'wphb' ); ?></a>
			</div>
		</section><!-- end header -->
		<?php
	}

	/**
	 * Run Performance, Minification, Uptime...
	 *
	 * @param string $type  Action type.
	 */
	private function run_actions( $type ) {

		check_admin_referer( 'wphb-run-dashboard' );

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		// Check if Uptime is active in the server.
		if ( wphb_is_uptime_remotely_enabled() ) {
			wphb_uptime_enable_locally();
		} else {
			wphb_uptime_disable_locally();
		}

		if ( 'performance' === $type ) {
			// Start performance test.
			wphb_performance_init_scan();
			wp_safe_redirect( remove_query_arg( array( 'run', '_wpnonce' ), wphb_get_admin_menu_url( 'performance' ) ) );
			exit;
		}

		if ( 'minification' === $type ) {
			// Minification scan.
			wphb_minification_init_scan();
			wp_safe_redirect( remove_query_arg( array( 'run', '_wpnonce' ), wphb_get_admin_menu_url( 'minification' ) ) );
			exit;
		}

		if ( 'uptime' === $type ) {
			// Minification scan.
			wphb_uptime_get_last_report( 'week', true );
			wp_safe_redirect( remove_query_arg( array( 'run', '_wpnonce' ) ) );
			exit;
		}

	}

	/**
	 * Register available metaboxes on the Dashboard page.
	 */
	public function register_meta_boxes() {

		if ( isset( $_GET['run'] ) && isset( $_GET['type'] ) ) {
			$this->run_actions( $_GET['type'] );
		}

		$this->add_meta_box( 'dashboard/welcome', null , array( $this, 'dashboard_welcome_metabox' ), null, null, 'main', array(
			'box_class' => 'dev-box content-box content-box-two-cols-image-left',
		));

		/* Performance */
		$last_report = wphb_performance_get_last_report();

		// Check to see if there's a fresh report on the server.
		if ( false === $last_report ) {
			wphb_performance_refresh_report();
			$last_report = wphb_performance_get_last_report();
		}

		if ( wphb_performance_is_doing_report() ) {
			$this->add_meta_box( 'dashboard/performance/running-test', __( 'Performance test in progress', 'wphb' ), null, null, null, 'box-dashboard-left' );
		} elseif ( ! wphb_performance_is_doing_report() && $last_report && ! is_wp_error( $last_report ) ) {
			$this->add_meta_box( 'dashboard-performance-module', __( 'Performance Report', 'wphb' ), array( $this, 'dashboard_performance_module_metabox' ), array( $this, 'dashboard_performance_module_metabox_header' ), null, 'box-dashboard-left', array(
				'box_class'        => 'dev-box content-box content-box-one-col-center',
			));
		} elseif ( is_wp_error( $last_report ) ) {
			$this->add_meta_box( 'dashboard-performance-module-error', __( 'Performance Report', 'wphb' ), array( $this, 'dashboard_performance_module_error_metabox' ), null, null, 'box-dashboard-left', array(
				'box_class' => 'dev-box content-box content-box-one-col-center',
			));
		} else {
			$this->add_meta_box( 'dashboard-performance-disabled', __( 'Performance Report', 'wphb' ), array( $this, 'dashboard_performance_disabled_metabox' ), null, null, 'box-dashboard-left', array(
				'box_class' => 'dev-box content-box content-box-one-col-center',
			));
		}

		/* Browser caching */
		$caching_status = wphb_get_caching_status();
		if ( false === $caching_status ) {
			// Force only when we don't have any data yet.
			$caching_status = wphb_get_caching_status( true );
		}
        $this->add_meta_box( 'dashboard-caching-module', __( 'Browser Caching', 'wphb' ), array( $this, 'dashboard_caching_module_metabox' ), array( $this, 'dashboard_caching_module_metabox_header' ), null, 'box-dashboard-right' );

		/* GZIP */
		$this->add_meta_box( 'dashboard-gzip-module', __( 'GZIP Compression', 'wphb' ), array( $this, 'dashboard_gzip_module_metabox' ), array( $this, 'dashboard_gzip_module_metabox_header' ), null, 'box-dashboard-left' );

		/* Minification */
		if ( ! wphb_can_execute_php() ) {
			$this->add_meta_box( 'dashboard/minification/cant-execute-php', __( 'Minification', 'wphb' ), null, null, null, 'box-dashboard-right', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );
		}
		elseif ( is_multisite() && is_network_admin() ) {
			// Minification metabox is different on network admin
			$this->add_meta_box( 'dashboard/minification/network-module', __( 'Minification', 'wphb' ), array( $this, 'dashboard_minification_network_module_metabox' ), null, null, 'box-dashboard-right', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );
		}
		else {
			$module = wphb_get_module( 'minify' );
			$collection = wphb_minification_get_resources_collection();
			if ( ( ! empty( $collection['styles'] ) || ! empty( $collection['scripts'] ) ) && ( $module->is_active() ) ) {
				$this->add_meta_box( 'dashboard/minification-module', __( 'Minification', 'wphb' ), array( $this, 'dashboard_minification_module_metabox' ), null, null, 'box-dashboard-right' );
			}
			else {
				$this->add_meta_box( 'dashboard/minification-disabled', __( 'Minification', 'wphb' ), array( $this, 'dashboard_minification_disabled_metabox' ), null, null, 'box-dashboard-right', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );
			}
		}

		/* Smush */
		$smush_id = wphb_is_member() ? 'dashboard-smush' : 'dashboard/smush/no-membership';
		$this->add_meta_box( $smush_id, __( 'Image Optimization', 'wphb' ), array( $this, 'dashboard_smush_metabox' ), array( $this, 'dashboard_smush_metabox_header' ), null, 'box-dashboard-left', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );

		/* Become Pro Member Callout */
		if ( ! wphb_is_member() ) {
			$this->add_meta_box( 'dashboard-pro-membership', null, array( $this, 'dashboard_membership_metabox' ), null, null, 'box-dashboard-left', array( 'box_class' => 'dev-box callout-box content-box content-box-one-col-center' ) );
		}

		/* Uptime */
		$uptime_module = wphb_get_module( 'uptime' );
		$is_active = $uptime_module->is_active();
		$report = '';
		if ( $is_active ) {
			$report = wphb_uptime_get_last_report( 'week' );
		}

		if ( ! wphb_is_member() ) {
			$this->add_meta_box( 'dashboard/uptime/no-membership', __( 'Uptime Monitoring', 'wphb' ), null, array( $this, 'dashboard_uptime_module_metabox_header' ), null, 'box-dashboard-right', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );
		}
		elseif ( is_wp_error( $report ) && $is_active ) {
			$this->add_meta_box( 'dashboard-uptime-error', __( 'Uptime', 'wphb' ), array( $this, 'dashboard_uptime_error_metabox' ), null, null, 'box-dashboard-right', null );
		}
		elseif ( ! $is_active ) {
			$this->add_meta_box( 'dashboard-uptime-disabled', __( 'Uptime', 'wphb' ), array( $this, 'dashboard_uptime_disabled_metabox' ), null, null, 'box-dashboard-right', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );
		}
		else {
			$this->add_meta_box( 'dashboard-uptime', __( 'Uptime', 'wphb' ), array( $this, 'dashboard_uptime_metabox' ), array( $this, 'dashboard_uptime_module_metabox_header' ), null, 'box-dashboard-right', null );
		}

		/* Reports */
		if ( ! wphb_is_member() ) {
			$this->add_meta_box( 'dashboard/reports/no-membership', __( 'Reporting', 'wphb' ), null, array( $this, 'dashboard_reports_module_metabox_header' ), null, 'box-dashboard-right', array( 'box_class' => 'dev-box content-box content-box-one-col-center' ) );
		}

	}

	/**
	 * Display dashboard welcome metabox.
	 */
	public function dashboard_welcome_metabox() {
		$caching_status = wphb_get_caching_status();
		if ( false === $caching_status ) {
			// Force only when we don't have any data yet.
			$caching_status = wphb_get_caching_status( true );
		}
		$caching_issues = wphb_get_number_of_issues( 'caching' );

		$gzip_status = wphb_get_gzip_status();
		if ( false === $gzip_status ) {
			// Force only when we don't have any data yet.
			$gzip_status = wphb_get_gzip_status( true );
		}
		$gzip_issues = wphb_get_number_of_issues( 'gzip' );

		$uptime_module = wphb_get_module( 'uptime' );
		$uptime_active = $uptime_module->is_active();
		$uptime_report = '';
		if ( $uptime_active ) {
			$uptime_report = wphb_uptime_get_last_report( 'week' );
		}
		$site_date = '';
		if ( wphb_is_member() && isset( $uptime_report->up_since ) && false !== $uptime_report->up_since ) {
			$gmt_date = date( 'Y-m-d H:i:s', $uptime_report->up_since );
			$site_date = get_date_from_gmt( $gmt_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		}

		$last_report = wphb_performance_get_last_report();
		/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
		$cf_module = wphb_get_module( 'cloudflare' );
		$cf_active = false;

		$cf_current = '';
		if ( $cf_module->is_active() && $cf_module->is_connected() && $cf_module->is_zone_selected() ) {
			$cf_active = true;
			$cf_current = $cf_module->get_caching_expiration();
			if ( is_wp_error( $cf_current ) ) {
				$cf_current = '';
			}
		}

		$args = compact(
			'caching_status',
			'caching_issues',
			'gzip_status',
			'gzip_issues',
			'uptime_active',
			'uptime_report',
			'last_report',
			'cf_active',
			'cf_current',
			'site_date'
		);
		$this->view( 'dashboard/welcome/meta-box', $args );
	}

	/**
	 * Dashboard welcome metabox header.
	 */
	public function dashboard_welcome_metabox_header() {
		$user = wphb_get_current_user_info();
		$this->view( 'dashboard/welcome/meta-box-header', array(
			/* translators: %s: username */
			'title' => sprintf( __( 'Welcome %s', 'wphb' ), $user ),
		));
	}

	/**
	 * Display pro membership metabox
	 *
	 * @since 1.4.5
	 */
	public function dashboard_membership_metabox() {
		$this->view( 'dashboard/welcome/pro-membership-meta-box', array() );
	}

	/*******************
	 * CACHING         *
	 *******************/
	public function dashboard_caching_module_metabox() {
		$caching_status = wphb_get_caching_status();
		$human_results = array_map( 'wphb_human_read_time_diff', $caching_status );
		$recommended = wphb_get_recommended_caching_values();

		/** @var WP_Hummingbird_Module_Cloudflare $cf_module */
		$cf_module = wphb_get_module( 'cloudflare' );
		$cf_active = false;
		$cf_current_human = '';
		$cf_tooltip = '';
		$cf_current = '';
		if ( $cf_module->is_active() && $cf_module->is_connected() && $cf_module->is_zone_selected() ) {
			$cf_active = true;
			$cf_current = $cf_module->get_caching_expiration();
			if ( is_wp_error( $cf_current ) ) {
				$cf_current = '';
			}

			$cf_tooltip = $cf_current == 691200 ? __('Caching is enabled', 'wphb') : __('Caching is enabled but you aren\'t using our recommended value', 'wphb');
			$cf_current_human = wphb_human_read_time_diff( $cf_current );
		}

		$args = array(
			'results'       => $caching_status,
			'recommended'   => $recommended,
			'human_results' => $human_results,
			'cf_tooltip' => $cf_tooltip,
			'cf_current' => $cf_current,
			'cf_current_human' => $cf_current_human,
			'cf_active' => $cf_active,
			'caching_url'   => wphb_get_admin_menu_url( 'caching' ) . '&view=browser',
		);
		$this->view( 'dashboard/caching/module-meta-box', $args );
	}

	public function dashboard_caching_module_metabox_header() {
	    $title = __( 'Browser Caching', 'wphb' );
		$issues = wphb_get_number_of_issues( 'caching' );

		/** @var WP_Hummingbird_Module_Cloudflare $cf_module */
		$cf_module = wphb_get_module( 'cloudflare' );
		$cf_active = false;

		$cf_current = '';
		if ( $cf_module->is_active() && $cf_module->is_connected() && $cf_module->is_zone_selected() ) {
			$cf_active = true;
			$cf_current = $cf_module->get_caching_expiration();
			if ( is_wp_error( $cf_current ) ) {
				$cf_current = '';
			}
		}

		$args = compact( 'title', 'issues', 'cf_active', 'cf_current' );
		$this->view( 'dashboard/caching/module-meta-box-header', $args );
	}

	/*******************
	 * UPTIME          *
	 *******************/
	public function dashboard_uptime_module_metabox_header() {
		$this->view( 'dashboard/uptime/module-meta-box-header', array( 'title' => __( 'Uptime Monitoring', 'wphb' ) ) );
	}
	public function dashboard_uptime_metabox() {
		$uptime_stats = wphb_uptime_get_last_report( 'week' );
		$this->view( 'dashboard/uptime/module-meta-box', array( 'uptime_stats' => $uptime_stats ) );
	}
	public function dashboard_uptime_disabled_metabox() {
		$enable_url = add_query_arg( 'action', 'enable', wphb_get_admin_menu_url( 'uptime' ) );
		$enable_url = wp_nonce_url( $enable_url, 'wphb-toggle-uptime' );
		$this->view( 'dashboard/uptime/disabled-meta-box', array( 'enable_url' => $enable_url ) );
	}
	public function dashboard_uptime_error_metabox() {
		$report = wphb_uptime_get_last_report();
		$retry_url = add_query_arg(
			array(
				'run' => 'true',
				'type' => 'uptime'
			),
			wphb_get_admin_menu_url( '' )
		);
		$retry_url = wp_nonce_url( $retry_url, 'wphb-run-dashboard' ) . '#wphb-box-dashboard-uptime-module';
		$support_url = wphb_support_link();
		$error = $report->get_error_message();

		$this->view( 'dashboard/uptime/error-meta-box', array( 'retry_url' => $retry_url, 'support_url' => $support_url, 'error' => $error ) );
	}

	/*******************
	 * MINIFICATION    *
	 *******************/
	public function dashboard_minification_module_metabox() {
		$collection = wphb_minification_get_resources_collection();
		// Remove those assets that we don't want to display
		foreach ( $collection['styles'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'styles' ) ) {
				unset( $collection['styles'][ $key ] );
			}
		}
		foreach ( $collection['scripts'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'scripts' ) ) {
				unset( $collection['scripts'][ $key ] );
			}
		}

		$enqueued_files = count( $collection['scripts'] ) + count( $collection['styles'] );

		$original_size_styles = array_sum( wp_list_pluck( $collection['styles'], 'original_size' ) );
		$original_size_scripts = array_sum( wp_list_pluck( $collection['scripts'], 'original_size' ) );

		$original_size = $original_size_scripts + $original_size_styles;

		$compressed_size_styles = array_sum( wp_list_pluck( $collection['styles'], 'compressed_size' ) );
		$compressed_size_scripts = array_sum( wp_list_pluck( $collection['scripts'], 'compressed_size' ) );
		$compressed_size = $compressed_size_scripts + $compressed_size_styles;

		if ( ( $original_size_scripts + $original_size_styles ) <= 0 ) {
			$percentage = 0;
		}
		else {
			$percentage = 100 - (int) $compressed_size * 100 / (int) $original_size;
		}
		$percentage = number_format_i18n( $percentage, 1 );

		$compressed_size_styles = number_format( $original_size_styles - $compressed_size_styles, 0 );
		$compressed_size_scripts = number_format( $original_size_scripts - $compressed_size_scripts, 0 );

		$minification_url = wphb_get_admin_menu_url( 'minification' );

		// Internalization numbers.
		$original_size = number_format_i18n( $original_size, 1 );
		$compressed_size = number_format_i18n( $compressed_size, 1 );

		$cdn_status = wphb_get_cdn_status();

		$args = compact( 'enqueued_files', 'original_size', 'compressed_size', 'compressed_size_scripts', 'compressed_size_styles', 'percentage', 'minification_url', 'cdn_status' );
		$this->view( 'dashboard/minification/module-meta-box', $args );
	}

	public function dashboard_minification_network_module_metabox() {
		$minify = wphb_get_setting( 'minify' );
		$args = array(
			'use_cdn' => wphb_get_cdn_status(),
			'use_cdn_disabled' => ! wphb_is_member() || ! $minify,
		);
		$this->view( 'dashboard/minification/network-module-meta-box', $args );
	}

	public function dashboard_minification_disabled_metabox() {
		$minification_url = add_query_arg(
			array(
				'run' => 'true',
				'type' => 'minification'
			),
			wphb_get_admin_menu_url( '' )
		);
		$minification_url = wp_nonce_url( $minification_url, 'wphb-run-dashboard' ) . '#wphb-box-dashboard-minification-checking-files';
		$this->view( 'dashboard/minification/disabled-meta-box', array( 'minification_url' => $minification_url ) );
	}

	/*******************
	 * GZIP            *
	 *******************/
	public function dashboard_gzip_module_metabox() {
		$status = wphb_get_gzip_status();
		$gzip_url = wphb_get_admin_menu_url( 'gzip' );
		$this->view( 'dashboard/gzip/module-meta-box', array( 'status' => $status, 'gzip_url' => $gzip_url ) );
	}

	public function dashboard_gzip_module_metabox_header() {
		$issues = wphb_get_number_of_issues( 'gzip' );
		$this->view( 'dashboard/gzip/module-meta-box-header', array( 'issues' => $issues, 'title' => __( 'GZIP Compression', 'wphb' ) ) );
	}

	/********************
	 * PERFORMANCE      *
	 ********************/
	public function dashboard_performance_disabled_metabox() {
        $run_url = add_query_arg(
            array(
                'run' => 'true',
                'type' => 'performance'
            ),
            wphb_get_admin_menu_url( '' )
        );
        $run_url = wp_nonce_url( $run_url, 'wphb-run-dashboard' ) . '#wphb-box-dashboard-performance-running-test';

		$this->view( 'dashboard/performance/disabled-meta-box', array( 'run_url' => $run_url ) );
	}

	public function dashboard_performance_module_metabox() {
		$report = wphb_performance_get_last_report();
		$report = $report->data;
		$viewreport_link = wphb_get_admin_menu_url( 'performance' );

		$settings = wphb_get_settings();
		$notifications = false;
		if ( wphb_is_member() && isset( $settings['email-notifications'] ) ) {
			$notifications = $settings['email-notifications'];
		}

        $args = compact( 'report', 'viewreport_link', 'notifications' );
		$this->view( 'dashboard/performance/module-meta-box', $args );
	}

	public function dashboard_performance_module_metabox_header() {
		$title =  __( 'Performance Report', 'wphb' );
		$last_report = wphb_performance_get_last_report();
		if ( $last_report && ! is_wp_error( $last_report ) ) {
			$last_report = $last_report->data;
		}

		$this->view( 'dashboard/performance/module-meta-box-header', array( 'title' => $title, 'last_report' => $last_report ) );
	}

	public function dashboard_performance_module_error_metabox() {
		/** @var WP_Error $last_report */
		$last_report = wphb_performance_get_last_report();
		$retry_url = add_query_arg( array(
				'run' => 'true',
				'type' => 'performance'
			),
			wphb_get_admin_menu_url( '' )
		);
		$retry_url = wp_nonce_url( $retry_url, 'wphb-run-dashboard' ) . '#wphb-box-dashboard-performance-running-test';

		$support_url = wphb_support_link();
		$error = $last_report->get_error_message();
		$this->view( 'dashboard/performance/module-error-meta-box', compact( 'error', 'retry_url', 'support_url' ) );
	}

	/*********************************
	/** SMUSH                        *
	 *********************************/
	public function dashboard_smush_metabox() {
		global $wpsmushit_admin, $wpsmush_db;

		$smush_data = array( 'human' => '', 'percent' => 0 );
		$unsmushed_images = 0;
		if ( ( $wpsmushit_admin instanceof WpSmushitAdmin ) && method_exists( $wpsmushit_admin, 'global_stats' ) ) {
			/* @var WpSmushitAdmin $wpsmushit_admin */
			$smush_data = $wpsmushit_admin->global_stats();
		}
		if ( ( $wpsmush_db instanceof WpSmushDB ) && method_exists( $wpsmush_db, 'get_unsmushed_attachments' )) {
			/* @var WpSmushDB $wpsmush_db */
			$unsmushed_images = count( $wpsmush_db->get_unsmushed_attachments() );
		}

		$this->view(
			'dashboard/smush/meta-box',
			array(
				'activate_url' => wp_nonce_url( 'plugins.php?action=activate&amp;plugin=wp-smushit/wp-smush.php', 'activate-plugin_wp-smushit/wp-smush.php' ),
				'activate_pro_url' => wp_nonce_url( 'plugins.php?action=activate&amp;plugin=wp-smush-pro/wp-smush.php', 'activate-plugin_wp-smush-pro/wp-smush.php' ),
				'is_active' => wphb_smush_is_smush_active(),
				'is_installed' => wphb_smush_is_smush_installed(),
				'smush_data' => $smush_data,
				'is_pro' => WP_Hummingbird_Module_Smush::$is_smush_pro,
                'unsmushed' => $unsmushed_images,
			)
		);
	}
	public function dashboard_smush_metabox_header() {
		$title =  __( 'Image Optimization', 'wphb' );
		$this->view( 'dashboard/smush/meta-box-header', array( 'title' => $title ) );
	}

	/*********************************
	/** REPORTS                      *
	 *********************************/
	/**
	 * Reports header meta box
	 *
	 * @since 1.4.5
	 */
	public function dashboard_reports_module_metabox_header() {
		$title = __( 'Reports', 'wphb' );
		$this->view( 'dashboard/reports/meta-box-header', compact( 'title' ) );
	}

}