<?php

namespace Beehive\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Helpers\Template;
use Beehive\Core\Helpers\General;

/**
 * The assets controller class of the plugin.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Assets extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Register scripts and styles for admin.
		add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_assets' ] );

		// Register scripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
	}

	/**
	 * Register scripts and styles for the front end.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function register_assets() {

		// Register assets.
		$this->register_front();

		// Enqueue front assets.
		$this->enqueue_front();

		/**
		 * Action hook to execute after registering plugin assets.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_register_assets' );
	}

	/**
	 * Register scripts and styles for the admin.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function register_admin_assets() {

		// Register admin assets.
		$this->register_settings_assets();
		$this->register_google_charts();
		$this->register_stats_assets();
		$this->register_dashboard_assets();
		$this->register_post_assets();
		$this->register_notice_assets();

		// Enqueue admin assets.
		$this->enqueue_admin();

		/**
		 * Action hook to execute after registering plugin admin assets.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_register_admin_assets' );
	}

	/**
	 * Register scripts and styles for the plugin settings page.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function register_settings_assets() {

		// Shared UI scripts.
		wp_register_script(
			'beehive_sui',
			BEEHIVE_URL . 'app/assets/js/sui.min.js',
			[ 'jquery' ],
			BEEHIVE_VERSION,
			true
		);

		// Admin scripts.
		wp_register_script(
			'beehive_admin',
			BEEHIVE_URL . 'app/assets/js/admin.min.js',
			[ 'jquery', 'beehive_sui' ],
			BEEHIVE_VERSION,
			true
		);

		// Admin styles.
		wp_register_style(
			'beehive_admin',
			BEEHIVE_URL . 'app/assets/css/admin.min.css'
		);

		// Localize admin variables.
		wp_localize_script(
			'beehive_admin',
			'beehive_vars',
			/**
			 * Filter hook to modify script vars.
			 *
			 * @since 3.2.0
			 */
			apply_filters(
				'beehive_settings_localize_vars',
				[
					'network'                => $this->is_network() ? 1 : 0,
					'onboarding_done'        => (bool) beehive_analytics()->settings->get( 'onboarding_done', 'misc', $this->is_network() ),
					'required'               => __( 'This field is required.', 'ga_trans' ),
					'required_client_id'     => __( 'Please input valid Client ID', 'ga_trans' ),
					'required_client_secret' => __( 'Please input valid Client Secret', 'ga_trans' ),
					'required_access_code'   => __( 'Please input valid Access Code', 'ga_trans' ),
					'invalid_code'           => __( 'Please input valid Tracking ID', 'ga_trans' ),
					'invalid_tracking_code'  => sprintf( __( 'Whoops, looks like that\'s an invalid tracking ID. Double check you have your <a href="%s" target="_blank">Google tracking ID</a> and try again.', 'ga_trans' ), 'https://support.google.com/analytics/answer/1032385?rd=1' ),
					'settings_url'           => Template::settings_page(
						Template::current_tab(),
						$this->is_network()
					),
				]
			)
		);
	}

	/**
	 * Register Google Charts library.
	 *
	 * We are loading the library directly from Google to avoid conflicts
	 * and other issues.
	 *
	 * @see   https://developers.google.com/chart/interactive/docs/basic_load_libs
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function register_google_charts() {
		// Charts lib.
		if ( ! wp_script_is( 'googlecharts', 'registered' ) ) {
			wp_register_script(
				'googlecharts',
				'https://www.gstatic.com/charts/loader.js'
			);
		}
	}

	/**
	 * Register scripts and styles for the dashboard widget page.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function register_dashboard_assets() {
		// Do not continue if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return;
		}

		// Stats widget new style.
		wp_register_style(
			'beehive_dashboard_widget',
			BEEHIVE_URL . 'app/assets/css/dashboard-widget.min.css',
			[],
			BEEHIVE_VERSION
		);

		// Stats widget new script.
		wp_register_script(
			'beehive_dashboard_widget',
			BEEHIVE_URL . 'app/assets/js/dashboard-widget.min.js',
			[ 'googlecharts', 'jquery', 'jquery-ui-datepicker' ],
			BEEHIVE_VERSION,
			true
		);

		// Localize stats variables.
		wp_localize_script(
			'beehive_dashboard_widget',
			'beehive_vars',
			/**
			 * Filter hook to modify script vars.
			 *
			 * @since 3.2.0
			 */
			apply_filters( 'beehive_google_dashboard_stats_localize_vars', [] )
		);
	}

	/**
	 * Register scripts and styles for the post stats metabox.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function register_post_assets() {
		// Stats widget new style.
		wp_register_style(
			'beehive_post_stats',
			BEEHIVE_URL . 'app/assets/css/post-stats.min.css',
			[],
			BEEHIVE_VERSION
		);

		// Stats widget new script.
		wp_register_script(
			'beehive_post_stats',
			BEEHIVE_URL . 'app/assets/js/post-stats.min.js',
			[ 'jquery' ],
			BEEHIVE_VERSION,
			true
		);

		// Localize stats variables.
		wp_localize_script( 'beehive_post_stats', 'beehive_vars',
			/**
			 * Filter hook to modify script vars.
			 *
			 * @since 3.2.0
			 */
			apply_filters( 'beehive_google_post_stats_localize_vars', [
				'is_network' => $this->is_network(),
			] )
		);
	}

	/**
	 * Register scripts and styles for the admin.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function register_stats_assets() {
		// Stats page style.
		wp_register_style(
			'beehive_statistics',
			BEEHIVE_URL . 'app/assets/css/statistics.min.css',
			[],
			BEEHIVE_VERSION
		);

		// Stats page script.
		wp_register_script(
			'beehive_statistics',
			BEEHIVE_URL . 'app/assets/js/statistics.min.js',
			[ 'googlecharts', 'jquery', 'jquery-ui-datepicker' ],
			BEEHIVE_VERSION,
			true
		);

		// Localize stats variables.
		wp_localize_script( 'beehive_statistics', 'beehive_vars',
			/**
			 * Filter hook to modify script vars.
			 *
			 * @since 3.2.0
			 */
			apply_filters( 'beehive_google_stats_page_localize_vars', [
				'is_network' => $this->is_network(),
			] )
		);
	}

	/**
	 * Register scripts and styles for the plugin frontend.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function register_front() {
		// Widgets scripts.
		wp_register_script(
			'beehive_popular_widget',
			BEEHIVE_URL . 'app/assets/js/popular-widget.min.js',
			[ 'jquery' ],
			BEEHIVE_SUI_VERSION,
			true
		);

		// Localize variables.
		wp_localize_script( 'beehive_popular_widget', 'beehive_vars',
			/**
			 * Filter hook to modify script vars.
			 *
			 * @since 3.2.0
			 */
			apply_filters( 'beehive_google_popular_widget_localize_vars', [
				'is_network' => $this->is_network(),
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			] )
		);
	}

	/**
	 * Register the admin notices script.
	 *
	 * @since 3.2.0
	 */
	private function register_notice_assets() {
		// Notice scripts.
		wp_register_script(
			'beehive_admin_notice',
			BEEHIVE_URL . 'app/assets/js/admin-notice.min.js',
			[ 'jquery' ],
			BEEHIVE_SUI_VERSION,
			true
		);

		// Localize admin variables.
		wp_localize_script(
			'beehive_admin_notice',
			'beehive_notice_vars',
			/**
			 * Filter hook to modify script vars.
			 *
			 * @since 3.2.0
			 */
			apply_filters( 'beehive_admin_notice_localize_vars', [
				'network' => $this->is_network() ? 1 : 0,
				'nonce'   => wp_create_nonce( 'beehive_admin_nonce' ),
			] )
		);
	}

	/**
	 * Enqueue required assets for the front pages.
	 *
	 * Enqueue only if it is required on the current page.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function enqueue_front() {
		// Front scripts.
	}

	/**
	 * Enqueue required assets for the admin pages.
	 *
	 * We need to check if it is really our admin page before
	 * enqueuing assets.
	 *
	 * @since  3.2.0
	 * @global $pagenow
	 *
	 * @return void
	 */
	private function enqueue_admin() {

		// Enqueue only when we are in plugin page.
		if ( General::is_plugin_admin() ) {
			// Settings page.
			wp_enqueue_style( 'beehive_admin' );
			// Shared UI.
			wp_enqueue_script( 'beehive_sui' );
			// Settings page.
			wp_enqueue_script( 'beehive_admin' );
		}

		// Enqueue stats page assets.
		if ( General::is_plugin_stats() ) {
			// Stats page widget.
			wp_enqueue_style( 'beehive_statistics' );
			wp_enqueue_script( 'beehive_statistics' );
		}

		// Enqueue stats widget assets.
		if ( General::is_plugin_dashboard_widget() ) {
			wp_enqueue_style( 'beehive_dashboard_widget' );
			wp_enqueue_script( 'beehive_dashboard_widget' );
		}
	}
}