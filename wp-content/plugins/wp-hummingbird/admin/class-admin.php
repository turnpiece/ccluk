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

		add_filter( 'network_admin_plugin_action_links_wp-hummingbird/wp-hummingbird.php', array( $this, 'add_plugin_action_links' ) );
		add_filter( 'plugin_action_links_wp-hummingbird/wp-hummingbird.php', array( $this, 'add_plugin_action_links' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_icon_styles' ) );

		/**
		 * Triggered when Hummingbird Admin is loaded
		 */
		do_action( 'wphb_admin_loaded' );
	}

	public function enqueue_icon_styles() {
		wp_enqueue_style( 'wphb-fonts', wphb_plugin_url() . 'admin/assets/css/wphb-font.css', array() );
	}

	public function add_plugin_action_links( $actions ) {
		if ( current_user_can( wphb_get_admin_capability() ) ) {
			if ( is_multisite() && ! is_network_admin() ) {
				$url = network_admin_url( 'admin.php?page=wphb' );
			} else {
				$url = wphb_get_admin_menu_url( '' );
			}
			$actions['dashboard'] = '<a href="' . $url . '" aria-label="' . esc_attr( __( 'Go to Hummingbird Dashboard', 'wphb' ) ) . '">' . esc_html__( 'Settings', 'wphb' ) . '</a>';
		}

		return $actions;
	}

	private function includes() {
		include_once( 'abstract-class-admin-page.php' );
		include_once( 'class-dashboard-page.php' );
		include_once( 'class-performance-page.php' );
		include_once( 'class-minification-page.php' );
		include_once( 'class-caching-page.php' );
		include_once( 'class-gzip-page.php' );
		include_once( 'class-uptime-page.php' );
		include_once( 'class-admin-notices.php' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			include_once( 'class-admin-ajax.php' );
		}
	}


	/**
	 * Add all the menu pages in admin for the plugin
	 */
	public function add_menu_pages() {
		if ( ! is_multisite() ) {
			$this->pages['wphb'] = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Hummingbird', 'wphb' ), __( 'Hummingbird', 'wphb' ), false, false );
			$this->pages['wphb-dashboard'] = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Dashboard', 'wphb' ), __( 'Dashboard', 'wphb' ), 'wphb' );
			$this->pages['wphb-performance'] = new WP_Hummingbird_Performance_Report_Page( 'wphb-performance', __( 'Performance Test', 'wphb' ), __( 'Performance Test', 'wphb' ), 'wphb' );
			$this->pages['wphb-caching'] = new WP_Hummingbird_Caching_Page( 'wphb-caching', __( 'Caching', 'wphb' ), __( 'Caching', 'wphb' ), 'wphb' );
			$this->pages['wphb-gzip'] = new WP_Hummingbird_GZIP_Page( 'wphb-gzip', __( 'Gzip Compression', 'wphb' ), __( 'Gzip Compression', 'wphb' ), 'wphb' );

			if ( wphb_can_execute_php() ) {
				$this->pages['wphb-minification'] = new WP_Hummingbird_Minification_Page( 'wphb-minification', __( 'Minification', 'wphb' ), __( 'Minification', 'wphb' ), 'wphb' );
			}
			$this->pages['wphb-uptime'] = new WP_Hummingbird_Uptime_Page( 'wphb-uptime', __( 'Uptime', 'wphb' ), __( 'Uptime', 'wphb' ), 'wphb' );
		} else {
			$minify = wphb_get_setting( 'minify' );

			if ( wphb_can_execute_php() ) {
				if (
					( 'super-admins' === $minify && is_super_admin() )
					|| ( true === $minify )
				) {
					$this->pages['wphb-minification'] = new WP_Hummingbird_Minification_Page( 'wphb-minification', __( 'Minification', 'wphb' ), __( 'Hummingbird', 'wphb' ), false );
				} elseif ( isset( $_GET['page'] ) && 'wphb-minification' === $_GET['page'] ) {
					// Minification is off, and is a network, let's redirect to network admin
					$url = network_admin_url( 'admin.php?page=wphb#wphb-box-dashboard-minification-network-module' );
					$url = add_query_arg( 'minify-instructions', 'true', $url );
					wp_redirect( $url );
					exit;
				}
			}
		}
	}

	public function add_network_menu_pages() {
		$this->pages['wphb'] = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Hummingbird', 'wphb' ), __( 'Hummingbird', 'wphb' ), false, false );
		$this->pages['wphb-dashboard'] = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Dashboard', 'wphb' ), __( 'Dashboard', 'wphb' ), 'wphb' );
		$this->pages['wphb-performance'] = new WP_Hummingbird_Performance_Report_Page( 'wphb-performance', __( 'Performance Test', 'wphb' ), __( 'Performance Test', 'wphb' ), 'wphb' );
		$this->pages['wphb-caching'] = new WP_Hummingbird_Caching_Page( 'wphb-caching', __( 'Caching', 'wphb' ), __( 'Caching', 'wphb' ), 'wphb' );
		$this->pages['wphb-gzip'] = new WP_Hummingbird_GZIP_Page( 'wphb-gzip', __( 'Gzip Compression', 'wphb' ), __( 'Gzip Compression', 'wphb' ), 'wphb' );
		$this->pages['wphb-uptime'] = new WP_Hummingbird_Uptime_Page( 'wphb-uptime', __( 'Uptime', 'wphb' ), __( 'Uptime', 'wphb' ), 'wphb' );
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

		$checking_files = wphb_minification_is_scanning_files();

		// If we are checking files, continue with it
		if ( ! $checking_files ) {
			return;
		}

		$enqueued = wp_script_is( 'wphb-admin', 'enqueued' );

		if ( ! $enqueued ) {
			wphb_enqueue_admin_scripts( WPHB_VERSION );
		}

		// If we are in minification page, we should redirect when checking files is finished
		$screen = get_current_screen();
		$minification_screen_id = isset( $this->pages['wphb-minification']->page_id ) ? $this->pages['wphb-minification']->page_id : false;

		if ( $screen->id === $minification_screen_id ) {
			// The minification screen will do it for us
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

		$doing_report = wphb_performance_is_doing_report();

		// If we are checking files, continue with it.
		if ( ! $doing_report ) {
			return;
		}

		if ( wphb_performance_stopped_report() ) {
			return;
		}

		$enqueued = wp_script_is( 'wphb-admin', 'enqueued' );

		if ( ! $enqueued ) {
			wphb_enqueue_admin_scripts( WPHB_VERSION );
		}

		// If we are in performance page, we should redirect when checking files is finished.
		$screen = get_current_screen();
		$performance_screen_id = isset( $this->pages['wphb-performance'] ) && isset( $this->pages['wphb-performance']->page_id ) ? $this->pages['wphb-performance']->page_id : false;
		$dashboard_screen_id = isset( $this->pages['wphb'] ) && isset( $this->pages['wphb']->page_id ) ? $this->pages['wphb']->page_id : false;


		$redirect = '';
		if ( $screen->id === $performance_screen_id || $screen->id === $performance_screen_id . '-network' ) {
			$redirect = wphb_get_admin_menu_url( 'performance' );
		}

		if ( $screen->id === $dashboard_screen_id || $screen->id === $dashboard_screen_id . '-network' ) {
			$redirect = wphb_get_admin_menu_url( '' );
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
			wphb_enqueue_admin_scripts( WPHB_VERSION );
		}

		wphb_quick_setup_modal();
		wphb_check_performance_modal();
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