<?php

/**
 * Class WP_Hummingbird_Admin
 *
 * Manage the admin core functionality
 */
class WP_Hummingbird_Admin {

	public $pages = array();

	/**
	 * @var WP_Hummingbird_Admin_Notices
	 */
	public $admin_notices;

	public function __construct() {
		$this->includes();

		$this->admin_notices = WP_Hummingbird_Admin_Notices::get_instance();

		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'network_admin_menu', array( $this, 'add_network_menu_pages' ) );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new WP_Hummingbird_Admin_AJAX();
		}

		add_action( 'admin_footer', array( $this, 'maybe_check_files' ) );
		add_action( 'admin_footer', array( $this, 'maybe_check_report' ) );
		add_action( 'admin_footer', array( $this, 'maybe_show_quick_setup' ) );

		// Make sure plugin name is correct for adding plugin action links.
		$plugin_name = 'wp-hummingbird';
		if ( defined( 'WPHB_WPORG' ) && WPHB_WPORG && 'wp-hummingbird/wp-hummingbird.php' !== plugin_basename( __FILE__ ) ) {
			$plugin_name = 'hummingbird-performance';
		}
		add_filter( 'network_admin_plugin_action_links_' . $plugin_name . '/wp-hummingbird.php', array( $this, 'add_plugin_action_links' ) );
		add_filter( 'plugin_action_links_' . $plugin_name . '/wp-hummingbird.php', array( $this, 'add_plugin_action_links' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_icon_styles' ) );

		/**
		 * Triggered when Hummingbird Admin is loaded
		 */
		do_action( 'wphb_admin_loaded' );
	}

	public function enqueue_icon_styles() {
		wp_enqueue_style( 'wphb-fonts', WPHB_DIR_URL . 'admin/assets/css/wphb-font.css', array() );
	}

	public function add_plugin_action_links( $actions ) {
		// Settings link.
		if ( current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			if ( is_multisite() && ! is_network_admin() ) {
				$url = network_admin_url( 'admin.php?page=wphb' );
			} else {
				$url = WP_Hummingbird_Utils::get_admin_menu_url();
			}
			$actions['dashboard'] = '<a href="' . $url . '" aria-label="' . esc_attr( __( 'Go to Hummingbird Dashboard', 'wphb' ) ) . '">' . esc_html__( 'Settings', 'wphb' ) . '</a>';
		}

		// Documentation link.
		$actions['docs'] = '<a href="' . WP_Hummingbird_Utils::get_link( 'docs' ) . '" aria-label="' . esc_attr( __( 'View Hummingbird Documentation', 'wphb' ) ) . '" target="_blank">' . esc_html__( 'Docs', 'wphb' ) . '</a>';

		// Upgrade link.
		if ( ! WP_Hummingbird_Utils::is_member() ) {
			$actions['upgrade'] = '<a href="' . WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_pluginlist_upgrade' ) . '" aria-label="' . esc_attr( __( 'Upgrade to Hummingbird Pro', 'wphb' ) ) . '" target="_blank" style="color: #1ABC9C;">' . esc_html__( 'Upgrade', 'wphb' ) . '</a>';
		}

		return $actions;
	}

	private function includes() {
		include_once 'abstract-class-admin-page.php';
		include_once 'class-dashboard-page.php';
		include_once 'class-performance-page.php';
		include_once 'class-minification-page.php';
		include_once 'class-caching-page.php';
		include_once 'class-gzip-page.php';
		include_once 'class-advanced-page.php';
		include_once 'class-uptime-page.php';
		include_once 'class-admin-notices.php';

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			include_once 'class-admin-ajax.php';
		}
	}

	/**
	 * Add all the menu pages in admin for the plugin
	 */
	public function add_menu_pages() {
		if ( WP_Hummingbird_Utils::is_member() && get_site_option( 'wphb-pro' ) ) {
			$hb_title = __( 'Hummingbird Pro', 'wphb' );
		} else {
			$hb_title = __( 'Hummingbird', 'wphb' );
		}

		if ( ! is_multisite() ) {
			$this->pages['wphb'] = new WP_Hummingbird_Dashboard_Page( 'wphb', $hb_title, $hb_title, false, false );
			$this->pages['wphb-dashboard'] = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Dashboard', 'wphb' ), __( 'Dashboard', 'wphb' ), 'wphb' );
			$this->pages['wphb-performance'] = new WP_Hummingbird_Performance_Report_Page( 'wphb-performance', __( 'Performance Test', 'wphb' ), __( 'Performance Test', 'wphb' ), 'wphb' );
			$this->pages['wphb-caching']     = new WP_Hummingbird_Caching_Page( 'wphb-caching', __( 'Caching', 'wphb' ), __( 'Caching', 'wphb' ), 'wphb' );
			$this->pages['wphb-gzip']        = new WP_Hummingbird_GZIP_Page( 'wphb-gzip', __( 'Gzip Compression', 'wphb' ), __( 'Gzip Compression', 'wphb' ), 'wphb' );

			if ( WP_Hummingbird_Utils::can_execute_php() ) {
				$this->pages['wphb-minification'] = new WP_Hummingbird_Minification_Page( 'wphb-minification', __( 'Asset Optimization', 'wphb' ), __( 'Asset Optimization', 'wphb' ), 'wphb' );
			}

			$this->pages['wphb-advanced'] = new WP_Hummingbird_Advanced_Tools_Page( 'wphb-advanced', __( 'Advanced Tools', 'wphb' ), __( 'Advanced Tools', 'wphb' ), 'wphb' );
			$this->pages['wphb-uptime'] = new WP_Hummingbird_Uptime_Page( 'wphb-uptime', __( 'Uptime', 'wphb' ), __( 'Uptime', 'wphb' ), 'wphb' );
		} else {
			$minify = WP_Hummingbird_Settings::get_setting( 'enabled', 'minify' );
			$subsite_tests = false;
			if ( is_super_admin() || WP_Hummingbird_Settings::get_setting( 'subsite_tests', 'performance' ) ) {
				$subsite_tests = true;
			}

			/* @var WP_Hummingbird_Module_Page_Cache $page_cache_module */
			$page_cache_module = WP_Hummingbird_Utils::get_module( 'page_cache' );
			$options = $page_cache_module->get_options();
			$subsite_page_caching = false;
			if ( $options['enabled'] && ( 'blog-admins' === $options['enabled'] || is_super_admin() ) ) {
				$subsite_page_caching = true;
			}

			// Temp until we do the dashboard in 1.8 or 1.9
			if ( $subsite_tests ) {
				$slug = 'performance';
			} elseif ( true === $minify ) {
				$slug = 'minification';
			} elseif ( $subsite_page_caching ) {
				$slug = 'caching';
			} else {
				return;
			}

			$this->pages['wphb'] = new WP_Hummingbird_Dashboard_Page( "wphb-{$slug}", $hb_title, $hb_title, false, false );

			if ( $subsite_tests ) {
				$this->pages['wphb-performance'] = new WP_Hummingbird_Performance_Report_Page( 'wphb-performance', __( 'Performance Test', 'wphb' ), __( 'Performance Test', 'wphb' ), "wphb-{$slug}" );
			} elseif ( isset( $_GET['page'] ) && 'wphb-performance' === $_GET['page'] ) {
				// Subsite performance reporting is off, and is a network, let's redirect to network admin
				$url = network_admin_url( 'admin.php?page=wphb-performance' );
				$url = add_query_arg( 'view', 'settings', $url );
				wp_safe_redirect( $url );
				exit;
			}

			if ( $minify && WP_Hummingbird_Utils::can_execute_php() ) {
				if ( ( 'super-admins' === $minify && is_super_admin() ) || ( true === $minify ) ) {
					$this->pages['wphb-minification'] = new WP_Hummingbird_Minification_Page( 'wphb-minification', __( 'Asset Optimization', 'wphb' ), __( 'Asset Optimization', 'wphb' ), "wphb-{$slug}" );
				} elseif ( isset( $_GET['page'] ) && 'wphb-minification' === $_GET['page'] ) {
					// Asset optimization is off, and is a network, let's redirect to network admin
					$url = network_admin_url( 'admin.php?page=wphb#wphb-box-dashboard-minification-network-module' );
					$url = add_query_arg( 'minify-instructions', 'true', $url );
					wp_safe_redirect( $url );
					exit;
				}
			}

			if ( $subsite_page_caching ) {
				$this->pages['wphb-caching'] = new WP_Hummingbird_Caching_Page( 'wphb-caching', __( 'Caching', 'wphb' ), __( 'Caching', 'wphb' ), "wphb-{$slug}" );
			}

		} // End if().
	}

	public function add_network_menu_pages() {
		if ( WP_Hummingbird_Utils::is_member() && get_site_option( 'wphb-pro' ) ) {
			$hb_title = __( 'Hummingbird Pro', 'wphb' );
		} else {
			$hb_title = __( 'Hummingbird', 'wphb' );
		}

		$this->pages['wphb']             = new WP_Hummingbird_Dashboard_Page( 'wphb', $hb_title, $hb_title, false, false );
		$this->pages['wphb-dashboard']   = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Dashboard', 'wphb' ), __( 'Dashboard', 'wphb' ), 'wphb' );
		$this->pages['wphb-performance'] = new WP_Hummingbird_Performance_Report_Page( 'wphb-performance', __( 'Performance Test', 'wphb' ), __( 'Performance Test', 'wphb' ), 'wphb' );
		$this->pages['wphb-caching']     = new WP_Hummingbird_Caching_Page( 'wphb-caching', __( 'Caching', 'wphb' ), __( 'Caching', 'wphb' ), 'wphb' );
		$this->pages['wphb-gzip']        = new WP_Hummingbird_GZIP_Page( 'wphb-gzip', __( 'Gzip Compression', 'wphb' ), __( 'Gzip Compression', 'wphb' ), 'wphb' );
		$this->pages['wphb-uptime']      = new WP_Hummingbird_Uptime_Page( 'wphb-uptime', __( 'Uptime', 'wphb' ), __( 'Uptime', 'wphb' ), 'wphb' );
	}

	/**
	 * Return an instannce of a WP Hummingbird Admin Page
	 *
	 * @param string $page_slug
	 *
	 * @return bool|WP_Hummingbird_Admin_Page
	 */
	public function get_admin_page( $page_slug ) {
		if ( isset( $this->pages[ $page_slug ] ) ) {
			return $this->pages[ $page_slug ];
		}

		return false;
	}

	public function maybe_check_files() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$checking_files = false;

		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		if ( WP_Hummingbird_Utils::can_execute_php() ) {
			$checking_files = $minify_module->is_scanning();
		}

		// If we are checking files, continue with it
		if ( ! $checking_files ) {
			return;
		}

		$enqueued = wp_script_is( 'wphb-admin', 'enqueued' );

		if ( ! $enqueued ) {
			WP_Hummingbird_Utils::enqueue_admin_scripts( WPHB_VERSION );
		}

		// If we are in minification page, we should redirect when checking files is finished.
		$screen = get_current_screen();
		$minification_screen_id = isset( $this->pages['wphb-minification']->page_id ) ? $this->pages['wphb-minification']->page_id : false;

		// The minification screen will do it for us.
		if ( $screen->id === $minification_screen_id ) {
			return;
		}

		?>
		<script>
			jQuery( document ).ready( function() {
				var module = window.WPHB_Admin.getModule( 'minification' );
				module.scanner.scan();
				module.minificationStarted = true;
			});
		</script>
		<?php
	}

	public function maybe_check_report() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$doing_report = WP_Hummingbird_Module_Performance::is_doing_report();

		// If we are checking files, continue with it.
		if ( ! $doing_report ) {
			return;
		}

		if ( WP_Hummingbird_Module_Performance::stopped_report() ) {
			return;
		}

		$enqueued = wp_script_is( 'wphb-admin', 'enqueued' );

		if ( ! $enqueued ) {
			WP_Hummingbird_Utils::enqueue_admin_scripts( WPHB_VERSION );
		}

		// If we are in performance page, we should redirect when checking files is finished.
		$screen = get_current_screen();
		$performance_screen_id = isset( $this->pages['wphb-performance'] ) && isset( $this->pages['wphb-performance']->page_id ) ? $this->pages['wphb-performance']->page_id : false;
		$dashboard_screen_id = isset( $this->pages['wphb'] ) && isset( $this->pages['wphb']->page_id ) ? $this->pages['wphb']->page_id : false;

		$redirect = '';
		if ( $screen->id === $performance_screen_id || $screen->id === $performance_screen_id . '-network' ) {
			$redirect = WP_Hummingbird_Utils::get_admin_menu_url( 'performance' );
		}

		if ( $screen->id === $dashboard_screen_id || $screen->id === $dashboard_screen_id . '-network' ) {
			$redirect = WP_Hummingbird_Utils::get_admin_menu_url();
		}

		?>
		<script>
			jQuery( document ).ready( function() {
				var module = window.WPHB_Admin.getModule( 'performance' );
				module.performanceTest( '<?php echo $redirect; ?>' );
			});
		</script>
		<?php
	}

	/**
	 * Show quick setup modal.
	 *
	 * @since 1.5.0
	 */
	public function maybe_show_quick_setup() {
		// Only run on HB pages.
		$hb_pages = array(
			'toplevel_page_wphb',
			'hummingbird_page_wphb-performance',
			'hummingbird_page_wphb-minification',
			'hummingbird_page_wphb-caching',
			'hummingbird_page_wphb-gzip',
			'hummingbird_page_wphb-uptime',
			'toplevel_page_wphb-network',
			'hummingbird_page_wphb-performance-network',
			'hummingbird_page_wphb-minification-network',
			'hummingbird_page_wphb-caching-network',
			'hummingbird_page_wphb-gzip-network',
			'hummingbird_page_wphb-uptime-network',
		);

		if ( ! in_array( get_current_screen()->id, $hb_pages, true ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		// If setup has already ran - exit.
		$quick_setup = get_option( 'wphb-quick-setup' );
		if ( true === $quick_setup['finished'] ) {
			return;
		}

		$enqueued = wp_script_is( 'wphb-admin', 'enqueued' );

		if ( ! $enqueued ) {
			WP_Hummingbird_Utils::enqueue_admin_scripts( WPHB_VERSION );
		}

		WP_Hummingbird_Utils::get_modal( 'quick-setup' );
		WP_Hummingbird_Utils::get_modal( 'check-performance' );
		?>
		<script>
			jQuery(document).ready( function() {
				if ( window.WPHB_Admin ) {
					var module = window.WPHB_Admin.getModule('dashboard');
					module.startQuickSetup();
				}
			});
		</script>
		<?php
	}

}