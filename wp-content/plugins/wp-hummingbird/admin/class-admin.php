<?php
/**
 * Hummingbird admin class.
 *
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Admin
 *
 * Manage the admin core functionality
 */
class WP_Hummingbird_Admin {

	/**
	 * Plugin pages.
	 *
	 * @var array
	 */
	public $pages = array();

	/**
	 * Admin notices.
	 *
	 * @var WP_Hummingbird_Admin_Notices
	 */
	public $admin_notices;

	/**
	 * Whether we show the quick setup modal.
	 *
	 * @var bool
	 */
	public $show_quick_setup;

	/**
	 * WP_Hummingbird_Admin constructor.
	 */
	public function __construct() {
		$this->includes();

		$this->admin_notices = WP_Hummingbird_Admin_Notices::get_instance();

		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'network_admin_menu', array( $this, 'add_network_menu_pages' ) );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new WP_Hummingbird_Admin_AJAX();
		}

		add_action( 'admin_footer', array( $this, 'maybe_check_files' ) );

		// Check DB to see if quick setup modal is needed and store in public var.
		$this->show_quick_setup = $this->maybe_show_quick_setup();

		// Make sure plugin name is correct for adding plugin action links.
		$plugin_name = 'wp-hummingbird';
		if ( defined( 'WPHB_WPORG' ) && WPHB_WPORG && 'wp-hummingbird/wp-hummingbird.php' !== plugin_basename( __FILE__ ) ) {
			$plugin_name = 'hummingbird-performance';
		}
		add_filter( 'network_admin_plugin_action_links_' . $plugin_name . '/wp-hummingbird.php', array( $this, 'add_plugin_action_links' ) );
		add_filter( 'plugin_action_links_' . $plugin_name . '/wp-hummingbird.php', array( $this, 'add_plugin_action_links' ) );

		// Filter built-in wpmudev branding script.
		add_filter( 'wpmudev_whitelabel_plugin_pages', array( $this, 'builtin_wpmudev_branding' ) );

		/**
		 * Triggered when Hummingbird Admin is loaded
		 */
		do_action( 'wphb_admin_loaded' );
	}

	/**
	 * Plugin action on plugin page.
	 *
	 * @param array $actions  Current actions.
	 *
	 * @return mixed
	 */
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

	/**
	 * File includes.
	 */
	private function includes() {
		include_once 'abstract-class-admin-page.php';
		include_once 'class-dashboard-page.php';
		include_once 'class-performance-page.php';
		include_once 'class-minification-page.php';
		include_once 'class-caching-page.php';
		include_once 'class-gzip-page.php';
		include_once 'class-advanced-page.php';
		include_once 'class-uptime-page.php';
		include_once 'class-settings-page.php';
		include_once 'class-admin-notices.php';
		include_once 'class-upgrade-page.php';

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			include_once 'class-admin-ajax.php';
		}
	}

	/**
	 * Add all the menu pages in admin for the plugin.
	 */
	public function add_menu_pages() {
		$hb_title = __( 'Hummingbird', 'wphb' );
		if ( WP_Hummingbird_Utils::is_member() ) {
			$hb_title = __( 'Hummingbird Pro', 'wphb' );
		}

		$current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		$this->pages['wphb']           = new WP_Hummingbird_Dashboard_Page( 'wphb', $hb_title, $hb_title, false, false );
		$this->pages['wphb-dashboard'] = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Dashboard', 'wphb' ), __( 'Dashboard', 'wphb' ), 'wphb' );

		if ( ! is_multisite() || ( is_super_admin() || true === WP_Hummingbird_Settings::get_setting( 'subsite_tests', 'performance' ) ) ) {
			$this->pages['wphb-performance'] = new WP_Hummingbird_Performance_Report_Page( 'wphb-performance', __( 'Performance Test', 'wphb' ), __( 'Performance Test', 'wphb' ), 'wphb' );
		} elseif ( is_multisite() && isset( $current_page ) && 'wphb-performance' === $current_page ) {
			// Subsite performance reporting is off, and is a network, let's redirect to network admin.
			$url = add_query_arg( 'view', 'settings', network_admin_url( 'admin.php?page=wphb-performance' ) );
			wp_safe_redirect( $url );
			exit;
		}

		$caching = WP_Hummingbird_Settings::get_setting( 'enabled', 'page_cache' );
		if ( ! is_multisite() || ( is_super_admin() && $caching ) || 'blog-admins' === $caching ) {
			$this->pages['wphb-caching'] = new WP_Hummingbird_Caching_Page( 'wphb-caching', __( 'Caching', 'wphb' ), __( 'Caching', 'wphb' ), 'wphb' );
		}

		if ( ! is_multisite() ) {
			$this->pages['wphb-gzip'] = new WP_Hummingbird_GZIP_Page( 'wphb-gzip', __( 'Gzip Compression', 'wphb' ), __( 'Gzip Compression', 'wphb' ), 'wphb' );
		}

		if ( WP_Hummingbird_Utils::can_execute_php() ) {
			$minify = WP_Hummingbird_Settings::get_setting( 'enabled', 'minify' );

			if ( ! is_multisite() || ( ( 'super-admins' === $minify && is_super_admin() ) || ( true === $minify ) ) ) {
				$this->pages['wphb-minification'] = new WP_Hummingbird_Minification_Page( 'wphb-minification', __( 'Asset Optimization', 'wphb' ), __( 'Asset Optimization', 'wphb' ), 'wphb' );
			} elseif ( isset( $current_page ) && 'wphb-minification' === $current_page ) {
				// Asset optimization is off, and is a network, let's redirect to network admin.
				$url = add_query_arg( 'minify-instructions', 'true', network_admin_url( 'admin.php?page=wphb#wphb-box-dashboard-minification-network-module' ) );
				wp_safe_redirect( $url );
				exit;
			}
		}

		$this->pages['wphb-advanced'] = new WP_Hummingbird_Advanced_Tools_Page( 'wphb-advanced', __( 'Advanced Tools', 'wphb' ), __( 'Advanced Tools', 'wphb' ), 'wphb' );

		if ( ! is_multisite() ) {
			$this->pages['wphb-uptime']   = new WP_Hummingbird_Uptime_Page( 'wphb-uptime', __( 'Uptime', 'wphb' ), __( 'Uptime', 'wphb' ), 'wphb' );
			$this->pages['wphb-settings'] = new WP_Hummingbird_Settings_Page( 'wphb-settings', __( 'Settings', 'wphb' ), __( 'Settings', 'wphb' ), 'wphb' );
		}

		if ( ! WP_Hummingbird_Utils::is_member() && ! is_multisite() ) {
			$this->pages['wphb-upgrade'] = new WP_Hummingbird_Upgrade_Page( 'wphb-upgrade', __( 'Hummingbird Pro', 'wphb' ), __( 'Hummingbird Pro', 'wphb' ), 'wphb' );
		}
	}

	/**
	 * Network menu pages.
	 */
	public function add_network_menu_pages() {
		if ( WP_Hummingbird_Utils::is_member() ) {
			$hb_title = __( 'Hummingbird Pro', 'wphb' );
		} else {
			$hb_title = __( 'Hummingbird', 'wphb' );
		}

		$this->pages['wphb']             = new WP_Hummingbird_Dashboard_Page( 'wphb', $hb_title, $hb_title, false, false );
		$this->pages['wphb-dashboard']   = new WP_Hummingbird_Dashboard_Page( 'wphb', __( 'Dashboard', 'wphb' ), __( 'Dashboard', 'wphb' ), 'wphb' );
		$this->pages['wphb-performance'] = new WP_Hummingbird_Performance_Report_Page( 'wphb-performance', __( 'Performance Test', 'wphb' ), __( 'Performance Test', 'wphb' ), 'wphb' );
		$this->pages['wphb-caching']     = new WP_Hummingbird_Caching_Page( 'wphb-caching', __( 'Caching', 'wphb' ), __( 'Caching', 'wphb' ), 'wphb' );
		$this->pages['wphb-gzip']        = new WP_Hummingbird_GZIP_Page( 'wphb-gzip', __( 'Gzip Compression', 'wphb' ), __( 'Gzip Compression', 'wphb' ), 'wphb' );
		if ( WP_Hummingbird_Utils::can_execute_php() ) {
			$this->pages['wphb-minification'] = new WP_Hummingbird_Minification_Page( 'wphb-minification', __( 'Asset Optimization', 'wphb' ), __( 'Asset Optimization', 'wphb' ), 'wphb' );
		}
		$this->pages['wphb-advanced'] = new WP_Hummingbird_Advanced_Tools_Page( 'wphb-advanced', __( 'Advanced Tools', 'wphb' ), __( 'Advanced Tools', 'wphb' ), 'wphb' );
		$this->pages['wphb-uptime']   = new WP_Hummingbird_Uptime_Page( 'wphb-uptime', __( 'Uptime', 'wphb' ), __( 'Uptime', 'wphb' ), 'wphb' );
		$this->pages['wphb-settings'] = new WP_Hummingbird_Settings_Page( 'wphb-settings', __( 'Settings', 'wphb' ), __( 'Settings', 'wphb' ), 'wphb' );

		if ( ! WP_Hummingbird_Utils::is_member() ) {
			$this->pages['wphb-upgrade'] = new WP_Hummingbird_Upgrade_Page( 'wphb-upgrade', __( 'Hummingbird Pro', 'wphb' ), __( 'Hummingbird Pro', 'wphb' ), 'wphb' );
		}
	}

	/**
	 * Return an instance of a WP Hummingbird Admin Page
	 *
	 * @param string $page_slug  Page slug.
	 *
	 * @return bool|WP_Hummingbird_Admin_Page
	 */
	public function get_admin_page( $page_slug ) {
		if ( isset( $this->pages[ $page_slug ] ) ) {
			return $this->pages[ $page_slug ];
		}

		return false;
	}

	/**
	 * This will continue running the minification scan on every page update, even if the user leaves the asset
	 * optimization page.
	 * Uses 3 db queries.
	 */
	public function maybe_check_files() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$checking_files = false;

		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		if ( WP_Hummingbird_Utils::can_execute_php() && $minify_module->is_active() ) {
			$checking_files = $minify_module->is_scanning();
		}

		// If we are checking files, continue with it.
		if ( ! $checking_files ) {
			return;
		}

		$enqueued = wp_script_is( 'wphb-admin', 'enqueued' );

		if ( ! $enqueued ) {
			WP_Hummingbird_Utils::enqueue_admin_scripts( WPHB_VERSION );
		}

		// If we are in minification page, we should redirect when checking files is finished.
		$screen                 = get_current_screen();
		$minification_screen_id = isset( $this->pages['wphb-minification']->page_id ) ? $this->pages['wphb-minification']->page_id : false;

		// The minification screen will do it for us.
		if ( $screen->id === $minification_screen_id ) {
			return;
		}

		?>
		<script>
			jQuery( document ).ready( function() {
				window.WPHB_Admin.getModule( 'minification' ).scanner.scan();
				window.WPHB_Admin.getModule( 'minification' ).minificationStarted = true;
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
		// Only if in admin or user is logged in.
		if ( ! is_admin() || ! is_user_logged_in() ) {
			return false;
		}

		// If setup has already ran - exit.
		$quick_setup = get_option( 'wphb-quick-setup' );
		if ( true === $quick_setup['finished'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Add more pages to builtin wpmudev branding.
	 *
	 * @since 1.9.3
	 *
	 * @param array $plugin_pages  Plugin pages.
	 *
	 * @return array
	 */
	public function builtin_wpmudev_branding( $plugin_pages ) {
		foreach ( $this->pages as $key => $value ) {
			$plugin_pages[ "hummingbird-pro_page_{$key}" ] = array(
				'wpmudev_whitelabel_sui_plugins_branding',
				'wpmudev_whitelabel_sui_plugins_footer',
				'wpmudev_whitelabel_sui_plugins_doc_links',
			);
		}

		return $plugin_pages;
	}

}
