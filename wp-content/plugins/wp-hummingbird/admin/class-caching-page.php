<?php
/**
 * Caching pages: page caching, browser caching, gravatar caching, rss caching, settings for page caching.
 *
 * @package Hummingbird
 *
 * @since 1.9.0  Refactored to run admin page actions in order (first - register_meta_boxes, second - on_load, etc).
 */

/**
 * Class WP_Hummingbird_Caching_Page
 *
 * @property array tabs
 */
class WP_Hummingbird_Caching_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Current report.
	 *
	 * @since  1.5.3
	 * @var    array $report
	 * @access private
	 */
	private $report;

	/**
	 * Number of issues.
	 *
	 * If Cloudflare is enabled will calculate number of issues for it, if not - number of local issues.
	 *
	 * @since 1.5.3
	 * @var   int $issues  Default 0.
	 */
	private $issues = 0;

	/**
	 * Settings expiration values.
	 *
	 * @since 1.5.3
	 * @var   array $expires
	 */
	private $expires;

	/**
	 * Cloudflare module status.
	 *
	 * @since  1.5.3
	 * @var    bool $cloudflare  Default false.
	 * @access private
	 */
	private $cloudflare = false;

	/**
	 * If site is using Cloudflare.
	 *
	 * @since 1.7.1
	 * @var   bool $cf_server
	 */
	private $cf_server = false;

	/**
	 * Cloudflare expiration value.
	 *
	 * @since  1.5.3
	 * @var    int $expiration Default 0.
	 * @access private
	 */
	private $expiration = 0;

	/**
	 * If .htaccess is written by the module.
	 *
	 * @var bool
	 */
	private $htaccess_written = false;

	/**
	 * Register meta boxes for the page.
	 */
	public function register_meta_boxes() {
		/**
		 * PAGE CACHING META BOXES.
		 */

		if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
			/**
			 * Main site
			 */
			$this->add_meta_box(
				'caching/summary',
				null,
				array( $this, 'caching_summary' ),
				null,
				null,
				'summary',
				array(
					'box_class' => 'sui-box sui-summary',
					'box_content_class' => false,
				)
			);


			if ( WP_Hummingbird_Utils::get_module( 'page_cache' )->is_active() ) {
				$this->add_meta_box(
					'caching/page-caching',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_metabox' ),
					null,
					null,
					'main'
				);

				/**
				 * SETTINGS META BOX
				 */
				$this->add_meta_box(
					'caching/other-settings',
					__( 'Settings', 'wphb' ),
					array( $this, 'settings_metabox' ),
					null,
					null,
					'settings'
				);
			} else {
				$this->add_meta_box(
					'page-caching-disabled',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_disabled_metabox' ),
					null,
					null,
					'main',
					array(
						'box_content_class' => 'sui-box-body sui-block-content-center',
					)
				);
			}
		} elseif ( is_super_admin() || 'blog-admins' === WP_Hummingbird_Settings::get_setting( 'enabled', 'page_cache' ) ) {
			/**
			 * Subsites
			 */
			if ( WP_Hummingbird_Utils::get_module( 'page_cache' )->is_active() ) {
				$this->add_meta_box(
					'page-caching',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_subsite_metabox' ),
					array( $this, 'page_caching_subsite_metabox_header' ),
					null,
					'main'
				);
			} else {
				$this->add_meta_box(
					'page-caching-disabled',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_disabled_metabox' ),
					null,
					null,
					'main',
					array(
						'box_content_class' => 'sui-box-body sui-block-content-center',
					)
				);
			}
		} // End if().

		// Do not continue on subsites.
		if ( is_multisite() && ! is_network_admin() ) {
			return;
		}

		/**
		 * BROWSER CACHING META BOXES.
		 */

		if ( is_multisite() && is_network_admin() || ! is_multisite() ) {
			$this->add_meta_box(
				'caching-status',
				__( 'Browser Caching', 'wphb' ),
				array( $this, 'caching_summary_metabox' ),
				array( $this, 'caching_summary_metabox_header' ),
				null,
				'caching'
			);

			$this->add_meta_box(
				'caching-settings',
				__( 'Configure', 'wphb' ),
				array( $this, 'caching_settings_metabox' ),
				array( $this, 'caching_settings_metabox_header' ),
				null,
				'caching'
			);
		}

		/**
		 * GRAVATAR CACHING META BOXES.
		 */

		if ( WP_Hummingbird_Utils::get_module( 'gravatar' )->is_active() ) {
			$this->add_meta_box(
				'caching/gravatar',
				__( 'Gravatar Caching', 'wphb' ),
				array( $this, 'caching_gravatar_metabox' ),
				null,
				null,
				'gravatar'
			);
		} else {
			$this->add_meta_box(
				'gravatar-disabled',
				__( 'Gravatar Caching', 'wphb' ),
				array( $this, 'caching_gravatar_disabled_metabox' ),
				null,
				null,
				'gravatar',
				array(
					'box_content_class' => 'sui-box-body sui-block-content-center',
				)
			);
		}

		/**
		 * RSS CACHING META BOXES.
		 */

		if ( WP_Hummingbird_Utils::get_module( 'rss' )->is_active() ) {
			$this->add_meta_box(
				'caching/rss',
				__( 'RSS Caching', 'wphb' ),
				array( $this, 'caching_rss_metabox' ),
				null,
				array( $this, 'caching_rss_footer' ),
				'rss'
			);
		} else {
			$this->add_meta_box(
				'caching/rss-disabled',
				__( 'RSS Caching', 'wphb' ),
				array( $this, 'caching_rss_metabox' ),
				null,
				null,
				'rss'
			);
		}
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 *
	 * @since 1.7.0
	 * @since 1.9.0  Moved here from init().
	 */
	public function on_load() {
		$this->tabs = array(
			'main'     => __( 'Page Caching', 'wphb' ),
			'caching'  => __( 'Browser Caching', 'wphb' ),
			'gravatar' => __( 'Gravatar Caching', 'wphb' ),
			'rss'      => __( 'RSS Caching', 'wphb' ),
			'settings' => __( 'Settings', 'wphb' ),
		);

		// Remove modules that are not used on subsites in a network.
		if ( is_multisite() && ! is_network_admin() ) {
			unset( $this->tabs['caching'] );
			unset( $this->tabs['gravatar'] );
			unset( $this->tabs['rss'] );
			unset( $this->tabs['settings'] );

			// Don't run anything else.
			return;
		}

		// Remove settings menu point.
		if ( ! WP_Hummingbird_Utils::get_module( 'page_cache' )->is_active() && isset( $this->tabs['settings'] ) ) {
			unset( $this->tabs['settings'] );
		}

		// We need to update the status on all pages, for the menu icons to function properly.
		$this->update_cache_status();
	}

	/**
	 * Trigger an action before this screen is loaded
	 *
	 * @since 1.9.0  Moved here from on_load().
	 */
	public function trigger_load_action() {
		parent::trigger_load_action();

		/**
		 * Execute an action for specified module.
		 *
		 * Action will execute if:
		 * - Both action and module vars are defined;
		 * - Action is available as a methods in a selected module.
		 *
		 * Currently used actions: enable, disable, disconnect, download_logs.
		 * Currently supported modules: page_cache, caching, cloudflare, gravatar, rss.
		 */
		if ( isset( $_GET['action'] ) && isset( $_GET['module'] ) ) { // Input var ok.
			check_admin_referer( 'wphb-caching-actions' );
			$action = sanitize_text_field( wp_unslash( $_GET['action'] ) ); // Input var ok.
			$module = sanitize_text_field( wp_unslash( $_GET['module'] ) ); // Input var ok.

			// If unsupported module - exit.
			if ( ! $mod = WP_Hummingbird_Utils::get_module( $module ) ) {
				return;
			}

			if ( method_exists( $mod, $action ) ) {
				call_user_func( array( $mod, $action ) );
			}

			// Cloudflare module is located on caching page.
			if ( 'cloudflare' === $module ) {
				$module = 'caching';
			}

			$redirect_url = add_query_arg( array(
				'view' => $module,
			), WP_Hummingbird_Utils::get_admin_menu_url( 'caching' ) );


			if ( 'clear_cache' === $action && 'page_cache' === $module ) {
				$redirect_url = add_query_arg( array(
					'cleared' => true,
				), $redirect_url );
			} elseif ( 'enable' === $action && 'caching' === $module ) {
				$redirect_url = add_query_arg( array(
					'enabled' => true,
				), $redirect_url );
			} elseif ( 'disable' === $action && 'caching' === $module ) {
				$redirect_url = add_query_arg( array(
					'disabled' => true,
				), $redirect_url );
			}
			wp_safe_redirect( $redirect_url );
		} // End if().
	}

	/**
	 * Hooks for caching pages.
	 *
	 * @since 1.9.0
	 */
	public function add_screen_hooks() {
		parent::add_screen_hooks();

		// Icons in the submenu.
		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );
	}

	/**
	 * Overwrites parent class render_header method.
	 *
	 * Renders the template header that is repeated on every page.
	 * From WPMU DEV Dashboard
	 */
	public function render_header() {
		if ( isset( $_GET['enabled'] ) ) { // Input var ok.
			$this->admin_notices->show( 'updated', __( 'Browser cache enabled. Your .htaccess file has been updated', 'wphb' ), 'success' );
		} elseif ( isset( $_GET['disabled'] ) ) { // Input var ok.
			$this->admin_notices->show( 'updated', __( 'Browser cache disabled. Your .htaccess file has been updated', 'wphb' ), 'success' );
		} elseif ( isset( $_GET['cleared'] ) ) { // Input var ok.
			$this->admin_notices->show( 'purged', __( 'Page cache purged', 'wphb' ), 'success' );
		}
		?>
		<div class="sui-header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div class="sui-actions-right">
				<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_documentation_url( $this->slug, $this->get_current_tab() ) ); ?>" target="_blank" class="sui-button sui-button-ghost">
					<i class="sui-icon-academy" aria-hidden="true"></i>
					<?php esc_html_e( 'View Documentation', 'wphb' ); ?>
				</a>
			</div>
		</div><!-- end header -->
		<?php
	}

	/**
	 * Init browser cache settings.
	 *
	 * @since 1.8.1
	 */
	private function update_cache_status() {
		$options = WP_Hummingbird_Settings::get_settings( 'caching' );

		$this->expires = array(
			'css'        => $options['expiry_css'],
			'javascript' => $options['expiry_javascript'],
			'media'      => $options['expiry_media'],
			'images'     => $options['expiry_images'],
		);

		/**
		 * Check Cloudflare status.
		 *
		 * If Cloudflare is active, we store the values of CLoudFlare caching settings to the report variable.
		 * Else - we store the local setting in the report variable. That way we don't have to query and check
		 * later on what report to show to the user.
		 *
		 * @var WP_Hummingbird_Module_Cloudflare $cf_module
		 */
		$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );

		$this->cf_server = $cf_module->has_cloudflare();
		$this->cloudflare = $cf_module->is_connected() && $cf_module->is_zone_selected();

		if ( $this->cloudflare ) {
			$this->expiration = $cf_module->get_caching_expiration();
			// Fill the report with values from Cloudflare.
			$this->report = array_fill_keys( array_keys( $this->expires ), $this->expiration );
			// Save status.
			$this->cf_server = $cf_module->has_cloudflare();
			// Get number of issues.
			if ( 691200 > $this->expiration ) {
				$this->issues = count( $this->report );
			}
			return;
		}

		/*
		 * Remove no-background-image class on the metabox.
		 * We do it here, because register_metx_boxes() is fired before this code and there's no way to get CF status.
		 */
		$cf_notice = get_site_option( 'wphb-cloudflare-dash-notice' );
		if ( ! $cf_notice && 'dismissed' !== $cf_notice ) {
			$this->meta_boxes[ $this->get_slug() ]['caching']['caching-status']['args']['box_content_class'] = 'sui-box-body sui-upsell-items';
		}

		// Get latest local report.
		$this->report = WP_Hummingbird_Utils::get_status( 'caching' );

		// Get number of issues.
		$this->htaccess_written = WP_Hummingbird_Module_Server::is_htaccess_written( 'caching' );
		$this->issues = WP_Hummingbird_Utils::get_number_of_issues( 'caching', $this->report );
	}

	/**
	 * We need to insert an extra label to the tabs sometimes
	 *
	 * @param string $tab Current tab.
	 */
	public function after_tab( $tab ) {
		// For easier module access later on.
		if ( 'main' === $tab ) {
			$tab = 'page_cache';
		}

		if ( 'caching' === $tab ) {
			$issues = 0;
			if ( ! $this->cloudflare ) {
				$issues = WP_Hummingbird_Utils::get_number_of_issues( 'caching', $this->report );
			} elseif ( 691200 > $this->expiration ) {
				$issues = count( $this->report );
				// Add an issue for the CloudFlare type.
				$issues++;
			}

			if ( 0 !== $issues ) {
				echo '<span class="sui-tag">' . absint( $issues ) . '</span>';
				return;
			}
			echo '<i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>';
			return;
		}

		// Available modules.
		$modules = array( 'gravatar', 'page_cache', 'rss' );
		if ( ! in_array( $tab, $modules, true ) ) {
			return;
		}

		/* @var WP_Hummingbird_Module_Gravatar|WP_Hummingbird_Module_Page_Cache $module */
		$module = WP_Hummingbird_Utils::get_module( $tab );

		if ( $module->is_active() && ( ! isset( $module->error ) || ! is_wp_error( $module->error ) ) ) {
			echo '<i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>';
		} elseif ( isset( $module->error ) && is_wp_error( $module->error ) ) {
			echo '<i class="sui-icon-warning-alert sui-warning" aria-hidden="true"></i>';
		}
	}

	/**
	 * Check to see if caching is fully enabled.
	 *
	 * @access private
	 * @return bool
	 */
	private function is_caching_fully_enabled() {
		$result_sum  = 0;
		$recommended = WP_Hummingbird_Utils::get_recommended_caching_values();

		foreach ( $this->report as $key => $result ) {
			if ( $result >= $recommended[ $key ]['value'] ) {
				$result_sum++;
			}
		}

		return count( $this->report ) === $result_sum;
	}

	/**
	 * *************************
	 * CACHING SUMMARY
	 *
	 * @since 1.9.1
	 ***************************/

	/**
	 * Caching summary meta box.
	 */
	public function caching_summary() {
		global $wpdb;

		$pages = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE ( post_type = 'page' OR post_type = 'post' ) AND post_status = 'publish'" ); // db call ok; no-cache ok.

		$this->view( 'caching/summary-meta-box', array(
			'pc_active' => WP_Hummingbird_Utils::get_module( 'page_cache' )->is_active(),
			'pages'     => (int) $pages,
			'cached'    => WP_Hummingbird_Settings::get_setting( 'pages_cached', 'page_cache' ),
			'issues'    => $this->issues,
			'gravatar'  => WP_Hummingbird_Utils::get_module( 'gravatar' )->is_active(),
			'rss'       => WP_Hummingbird_Settings::get_setting( 'duration', 'rss' ),
		) );
	}

	/**
	 * *************************
	 * PAGE CACHING
	 *
	 * @since 1.7.0
	 ***************************/

	/**
	 * Disabled page caching meta box.
	 */
	public function page_caching_disabled_metabox() {
		$this->view( 'caching/disabled-page-caching-meta-box', array(
			'activate_url' => wp_nonce_url( add_query_arg( array(
				'action' => 'enable',
				'module' => 'page_cache',
			)), 'wphb-caching-actions' ),
		));
	}

	/**
	 * Page caching meta box.
	 */
	public function page_caching_metabox() {
		/* @var WP_Hummingbird_Module_Page_Cache $module */
		$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$options = $module->get_options();

		$custom_post_types = array();
		$settings = $module->get_settings();
		if ( isset( $settings['custom_post_types'] ) ) {
			$custom_post_types = $settings['custom_post_types'];
		}
		$settings['custom_post_types'] = $custom_post_types;


		$this->view( 'caching/page-caching-meta-box', array(
			'error'              => $module->error,
			'settings'           => $settings,
			'admins_can_disable' => ( 'blog-admins' === $options['enabled'] ) ? true : false,
			'blog_is_frontpage'  => ( 'posts' === get_option( 'show_on_front' ) && ! is_multisite() ) ? true : false,
			'pages'              => WP_Hummingbird_Module_Page_Cache::get_page_types(),
			'custom_post_types'  => get_post_types( array(
				'public'   => true,
				'_builtin' => false,
			), 'objects','and' ),
			'download_url'       => wp_nonce_url( add_query_arg( array(
				'action' => 'download_logs',
				'module' => 'page_cache',
			)), 'wphb-caching-actions' ),
			'deactivate_url'     => wp_nonce_url( add_query_arg( array(
				'action' => 'disable',
				'module' => 'page_cache',
			)), 'wphb-caching-actions' ),
		));
	}

	/**
	 * Page caching subsite meta box.
	 *
	 * @since 1.8.0
	 */
	public function page_caching_subsite_metabox() {
		/* @var WP_Hummingbird_Module_Page_Cache $page_cache_module */
		$page_cache_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$options = $page_cache_module->get_options();
		$can_deactivate = false;
		if ( 'blog-admins' === $options['enabled'] ) {
			$can_deactivate = true;
		}
		$this->view( 'caching/page-caching-subsite-meta-box', array(
			'error'          => WP_Hummingbird_Utils::get_module( 'page_cache' )->error,
			'can_deactivate' => $can_deactivate,
			'deactivate_url' => wp_nonce_url( add_query_arg( array(
				'action' => 'disable',
				'module' => 'page_cache',
			)), 'wphb-caching-actions' ),
		));
	}

	/**
	 * Page caching subsite meta box header.
	 *
	 * @since 1.8.0
	 */
	public function page_caching_subsite_metabox_header() {
		$this->view( 'caching/page-caching-meta-box-header', array(
			'title'      => __( 'Page Caching', 'wphb' ),
		));
	}

	/**
	 * *************************
	 * BROWSER CACHING
	 *
	 * @since forever
	 ***************************/

	/**
	 * Display header for caching summary meta box.
	 */
	public function caching_summary_metabox_header() {
		$issues = 0;
		if ( ! $this->cloudflare ) {
			$issues = WP_Hummingbird_Utils::get_number_of_issues( 'caching', $this->report );
		} elseif ( 691200 > $this->expiration ) {
			// Add an issue for the CloudFlare type.
			$issues = count( $this->report ) + 1;
		}

		$this->view( 'caching/browser-caching-meta-box-header', array(
			'title'      => __( 'Browser Caching', 'wphb' ),
			'issues'     => $issues,
		));
	}

	/**
	 * Render caching meta box.
	 */
	public function caching_summary_metabox() {
		// Defaults.
		$htaccess_issue = false;
		$show_cf_notice = false;

		// Check if .htaccess file has rules included.
		if ( $this->htaccess_written && in_array( false, $this->report, true ) ) {
			$htaccess_issue = true;
		}

		/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
		$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
		if ( ! $cf_module->is_connected() && ( ! get_site_option( 'wphb-cloudflare-dash-notice' ) && 'dismissed' !== get_site_option( 'wphb-cloudflare-dash-notice' ) ) ) {
			$show_cf_notice = true;
		}
		$cf_notice = $this->cf_server ? __( 'Ahoi, we’ve detected you’re using CloudFlare!', 'wphb' ) : __( 'Using CloudFlare?', 'wphb' );

		$this->view( 'caching/browser-caching-meta-box', array(
			'htaccess_issue'         => $htaccess_issue,
			'results'                => $this->report,
			'issues'                 => $this->issues,
			'human_results'          => array_map( array( 'WP_Hummingbird_Utils', 'human_read_time_diff' ), $this->report ),
			'recommended'            => WP_Hummingbird_Utils::get_recommended_caching_values(),
			'show_cf_notice'         => $show_cf_notice,
			'cf_notice'              => $cf_notice,
			'cf_server'              => $this->cf_server,
			'cf_active'              => $this->cloudflare,
			'caching_type_tooltips'  => WP_Hummingbird_Utils::get_browser_caching_types(),
		));
	}

	/**
	 * Display browser caching settings header meta box.
	 */
	public function caching_settings_metabox_header() {
		$this->view( 'caching/browser-caching-configure-meta-box-header', array(
			'title'     => __( 'Configure', 'wphb' ),
			'cf_active' => $this->cloudflare,
		));
	}

	/**
	 * Display browser caching settings meta box.
	 */
	public function caching_settings_metabox() {
		$show_cf_notice    = false;
		$htaccess_writable = WP_Hummingbird_Module_Server::is_htaccess_writable();
		$server_type       = WP_Hummingbird_Module_Server::get_server_type();

		// Server code snippets.
		$snippets = array(
			'apache'    => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'apache' ),
			'litespeed' => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'LiteSpeed' ),
			'nginx'     => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'nginx' ),
			'iis'       => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'iis' ),
		);

		// Default to show Cloudflare or Apache if set up.
		if ( $this->cloudflare ) {
			$server_type = 'cloudflare';
			// Clear cached status.
			WP_Hummingbird_Utils::get_module( 'caching' )->clear_cache();
		} elseif ( $this->cf_server ) {
			$server_type = 'cloudflare';
			/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
			$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
			if ( ! ( $cf_module->is_active() && $cf_module->is_connected() && $cf_module->is_zone_selected() ) ) {
				if ( get_site_option( 'wphb-cloudflare-dash-notice' ) && 'dismissed' === get_site_option( 'wphb-cloudflare-dash-notice' ) ) {
					$show_cf_notice = true;
				}
			}
		} elseif ( $htaccess_writable && $this->htaccess_written && 'LiteSpeed' !== $server_type ) {
			$server_type = 'apache';
		}

		$labels = array(
			'javascript' => 'JavaScript',
			'images'     => 'Images',
			'css'        => 'CSS',
			'media'      => 'Media',
		);

		$this->view( 'caching/browser-caching-configure-meta-box', array(
			'results'             => $this->report,
			'labels'              => $labels,
			'human_results'       => array_map( array( 'WP_Hummingbird_Utils', 'human_read_time_diff' ), $this->report ),
			'expires'             => $this->expires,
			'different_expiry'    => ( 1 >= count( array_unique( array_values( $this->expires ) ) ) ) ? true : false,
			'server_type'         => $server_type,
			'snippets'            => $snippets,
			'htaccess_written'    => $this->htaccess_written,
			'htaccess_writable'   => $htaccess_writable,
			'already_enabled'     => $this->is_caching_fully_enabled() && ! $this->htaccess_written,
			'cf_active'           => $this->cloudflare,
			'cf_server'           => $this->cf_server,
			'cf_current'          => $this->expiration,
			'all_expiry'          => count( array_unique( $this->expires ) ) === 1,
			'show_cf_notice'      => $show_cf_notice,
			'recheck_expiry_url'  => add_query_arg( 'run', 'true' ),
			'cf_disable_url'      => wp_nonce_url( add_query_arg( array(
				'action' => 'disconnect',
				'module' => 'cloudflare',
			)), 'wphb-caching-actions' ),
			'enable_link'         => wp_nonce_url( add_query_arg( array(
				'action' => 'enable',
				'module' => 'caching',
			)), 'wphb-caching-actions' ),
			'disable_link'        => wp_nonce_url( add_query_arg( array(
				'action' => 'disable',
				'module' => 'caching',
			)), 'wphb-caching-actions' ),
		));
	}

	/**
	 * *************************
	 * GRAVATAR CACHING
	 *
	 * @since 1.5.0
	 ***************************/

	/**
	 * Disabled Gravatar caching meta box.
	 *
	 * @since 1.5.3
	 */
	public function caching_gravatar_disabled_metabox() {
		$this->view( 'caching/disabled-gravatar-meta-box', array(
			'activate_url' => wp_nonce_url( add_query_arg( array(
				'action' => 'enable',
				'module' => 'gravatar',
			)), 'wphb-caching-actions' ),
		));
	}

	/**
	 * Gravatar meta box.
	 */
	public function caching_gravatar_metabox() {
		/* @var WP_Hummingbird_Module_Gravatar $module */
		$module = WP_Hummingbird_Utils::get_module( 'gravatar' );

		$this->view( 'caching/gravatar-meta-box', array(
			'module_active'    => $module->is_active(),
			'error'            => $module->error,
			'deactivate_url'   => wp_nonce_url( add_query_arg( array(
				'action' => 'disable',
				'module' => 'gravatar',
			)), 'wphb-caching-actions' ),
		));
	}

	/**
	 * *************************
	 * RSS CACHING
	 *
	 * @since 1.8
	 ***************************/

	/**
	 * Display Rss caching meta box.
	 */
	public function caching_rss_metabox() {
		$active = WP_Hummingbird_Utils::get_module( 'rss' )->is_active();

		$args = array(
			'url' => wp_nonce_url( add_query_arg( array(
				'action' => $active ? 'disable' : 'enable',
				'module' => 'rss',
			)), 'wphb-caching-actions' ),
		);

		$meta_box = 'caching/rss-disabled-meta-box';
		if ( $active ) {
			$meta_box = 'caching/rss-meta-box';
			$args['duration'] = WP_Hummingbird_Settings::get_setting( 'duration', 'rss' );
		}

		$this->view( $meta_box, $args );
	}

	/**
	 * *************************
	 * SETTINGS
	 *
	 * @since 1.8.1
	 ***************************/

	/**
	 * Display settings meta box.
	 */
	public function settings_metabox() {
		$this->view( 'caching/other-settings-meta-box', array(
			'control'   => WP_Hummingbird_Settings::get_setting( 'control', 'page_cache' ),
			'detection' => WP_Hummingbird_Settings::get_setting( 'detection', 'page_cache' ),
		));
	}

}