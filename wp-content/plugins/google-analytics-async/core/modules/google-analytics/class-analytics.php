<?php

namespace Beehive\Core\Modules\Google_Analytics;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Google_Service_Analytics;
use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Template;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Controllers\Capability;
use Beehive\Core\Modules\Google_Analytics\Views\Stats;
use Beehive\Core\Modules\Google_Analytics\Views\Settings;
use Beehive\Core\Modules\Google_Analytics\Views\Tracking;

/**
 * The Google core class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Analytics extends Base {

	/**
	 * Register all the hooks related to module.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Dashoard stats widget.
		add_action( 'wp_dashboard_setup', [ $this, 'dashboard_widget' ] );

		// Dashboard stats widget for network.
		add_action( 'wp_network_dashboard_setup', [ $this, 'dashboard_widget' ] );

		// Add Google Analytics auth scopes.
		add_filter( 'beehive_google_auth_scopes', [ $this, 'auth_scopes' ] );

		// Stats menu is required only when logged in.
		if ( Helper::instance()->can_get_stats( $this->is_network() ) ) {
			// Setup widgets.
			add_action( 'widgets_init', [ $this, 'widgets' ] );

			// Stats metabox for Post/Page edit screen.
			add_action( 'add_meta_boxes', [ $this, 'post_widget' ] );

			// Stats menu in dashboard.
			add_action( 'admin_menu', [ $this, 'statistics_menu' ] );

			// Stats menu in dashboard.
			add_action( 'network_admin_menu', [ $this, 'statistics_menu' ] );
		}

		// Initialize sub classes.
		Ajax::instance()->init();
		Stats::instance()->init();
		Settings::instance()->init();
		Tracking::instance()->init();
	}

	/**
	 * Add Google Analytics scope for authentication.
	 *
	 * @param array $scopes Auth scopes.
	 *
	 * @since 3.2.0
	 *
	 * @return array $scopes
	 */
	public function auth_scopes( $scopes = [] ) {
		// Add Google Analytics auth scope.
		//$scopes[] = Google_Service_Analytics::ANALYTICS;
		$scopes[] = Google_Service_Analytics::ANALYTICS_READONLY;

		return $scopes;
	}

	/**
	 * Setup widgets for Google Analytics.
	 *
	 * Register all widgets with WordPress.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function widgets() {
		// Make sure the user has capability.
		if ( Permission::user_can( 'analytics' ) || ! is_admin() ) {
			// Most popular contents.
			register_widget( Widgets\Popular::instance() );
		}
	}

	/**
	 * Add stats overview dashboard widget.
	 *
	 * Contents of this widget is loaded via Ajax.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		// Do not continue if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return;
		}

		// Make sure the user has capability.
		if ( Permission::user_can( 'analytics', $this->is_network() ) ) {
			// Register widget.
			wp_add_dashboard_widget(
				'beehive_dashboard',
				__( 'Visitors', 'ga_trans' ),
				[ Stats::instance(), 'dashboard_widget' ]
			);
		}
	}

	/**
	 * Stats metabox for the post edit screen.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function post_widget() {
		// Make sure the user has capability (Only for subsite/single site).
		$capable = Permission::user_can( 'analytics' );

		if ( Helper::instance()->can_get_stats() && $capable ) {
			// Register metabox.
			add_meta_box(
				'beehive_analytics_stats',
				__( 'Statistics for last 30 days', 'ga_trans' ),
				[ Stats::instance(), 'post_widget' ],
				Helper::instance()->post_types(),
				'normal'
			);
		}
	}

	/**
	 * Register admin menu for the stats page.
	 *
	 * This is for dashboard submenu.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function statistics_menu() {
		// Do not continue if not active network wide.
		if ( $this->is_network() && ! General::is_networkwide() ) {
			return;
		}

		// Should be able to manage settings.
		if ( ! Permission::can_view_analytics( $this->is_network() ) ) {
			return;
		}

		// Dashboard stats page.
		add_dashboard_page(
			__( 'All statistics', 'ga_trans' ),
			__( 'Statistics', 'ga_trans' ),
			Capability::ANALYTICS_CAP,
			'beehive-statistics',
			[ Views\Stats::instance(), 'stats_page' ]
		);

		global $submenu;

		// Add a linked menu item to dashboard stats page.
		$submenu['beehive-settings'][1] = [
			__( 'All statistics', 'ga_trans' ),
			Capability::ANALYTICS_CAP,
			Template::statistics_page( $this->is_network() ),
		];
	}
}