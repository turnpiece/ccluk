<?php
/**
 * The stats view functionality for the analytics
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Analytics\Views
 */

namespace Beehive\Core\Modules\Google_Analytics\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers;
use Beehive\Core\Controllers\Assets;
use Beehive\Core\Utils\Abstracts\View;
use Beehive\Core\Modules\Google_Analytics;

/**
 * Class Stats
 *
 * @package Beehive\Core\Modules\Google_Analytics\Views
 */
class Stats extends View {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Setup vars for the all stats page.
		add_filter( 'beehive_google_stats_page_localize_vars', array( $this, 'all_stats_vars' ) );

		// Common vars.
		add_filter( 'beehive_assets_scripts_common_localize_vars', array( $this, 'common_vars' ) );

		// Setup vars for the scripts.
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-post-statistics', array( $this, 'post_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-post-statistics', array( $this, 'stats_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-statistics-page', array( $this, 'stats_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-dashboard-widget', array( $this, 'stats_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-dashboard', array( $this, 'stats_vars' ) );
	}

	/**
	 * Render admin dashboard analytics widget.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		echo '<div id="beehive-dashboard-statistics-app"></div>';

		// Enqueue assets.
		Assets::instance()->enqueue_style( 'beehive-dashboard-widget' );
		Assets::instance()->enqueue_script( 'beehive-dashboard-widget' );
	}

	/**
	 * Render admin dashboard analytics widget.
	 *
	 * @param array $args View template arguments.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function popular_widget_content( $args = array() ) {
		// Render popular widget form template.
		$this->view( 'stats/google/popular-widget/front', $args );
	}

	/**
	 * Render admin dashboard analytics widget.
	 *
	 * @param array $args View template arguments.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function popular_widget_form( $args = array() ) {
		// Render popular widget form template.
		$this->view( 'stats/google/popular-widget/admin', $args );
	}

	/**
	 * Render stats widget for the post edit page.
	 *
	 * Stats loaded as a meta box within post edit page.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function post_widget() {
		echo '<div id="beehive-post-statistics-app"></div>';

		// Enqueue assets.
		Assets::instance()->enqueue_style( 'beehive-post-statistics' );
		Assets::instance()->enqueue_script( 'beehive-post-statistics' );
	}

	/**
	 * Render stats page content for the dashboard.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function stats_page() {
		echo '<div id="beehive-statistics-app"></div>';

		// Enqueue assets.
		Assets::instance()->enqueue_style( 'beehive-statistics-page' );
		Assets::instance()->enqueue_script( 'beehive-statistics-page' );
	}

	/**
	 * Render settings page content for the Analytics.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function settings_page() {
		echo '<div id="beehive-ga-settings-app"></div>';

		// Enqueue assets.
		Assets::instance()->enqueue_style( 'beehive-ga-settings' );
		Assets::instance()->enqueue_script( 'beehive-ga-settings' );
	}

	/**
	 * Setup script vars for the post stats script.
	 *
	 * @param array $vars Localized vars.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Removed unwanted items.
	 *
	 * @return array
	 */
	public function post_vars( $vars ) {
		global $pagenow, $post;

		if ( 'post.php' === $pagenow && $post->ID > 0 ) {
			$vars['post'] = $post->ID;
		}

		// Periods.
		$vars['dates'] = array(
			'start_date' => gmdate( 'M j', strtotime( '-30 days' ) ),
			'end_date'   => gmdate( 'M j', strtotime( '-1 days' ) ),
		);

		return $vars;
	}

	/**
	 * Setup script vars for the all stats and dashboard widget script.
	 *
	 * @param array $vars Localized vars.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function stats_vars( $vars ) {
		$vars['can_get_stats']     = Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() );
		$vars['stats_permissions'] = $this->stats_permissions();

		return $vars;
	}

	/**
	 * Get the list of permitted stats item from the settings.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function stats_permissions() {
		// Network admin is Super Man.
		if ( $this->is_network() ) {
			return array();
		}

		$network = false;

		// Check if network options should be considered.
		if ( Helpers\General::is_networkwide() ) {
			// Can sub sites override.
			$network = ! (bool) beehive_analytics()->settings->get( 'overwrite_cap', 'permissions', true );
		}

		// Get the custom capability.
		$custom_cap = beehive_analytics()->settings->get( 'custom_cap', 'permissions', $network );

		return array(
			'has_custom_cap' => ! empty( $custom_cap ) && current_user_can( $custom_cap ) ? 1 : 0,
			'dashboard'      => Helpers\Permission::user_report_caps( 'dashboard', $network ),
			'statistics'     => Helpers\Permission::user_report_caps( 'statistics', $network ),
		);
	}

	/**
	 * Commons vars added from GA modules.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function common_vars( $vars ) {
		// Setup URLs.
		$vars['urls']['statistics']  = Google_Analytics\Helper::statistics_url( $this->is_network() );
		$vars['urls']['ga_account']  = Google_Analytics\Helper::settings_url( 'account', $this->is_network() );
		$vars['urls']['ga_settings'] = Google_Analytics\Helper::settings_url( 'settings', $this->is_network() );

		return $vars;
	}

	/**
	 * Add items for the report section settings.
	 *
	 * Add sections from dashboard widget and all statistics page.
	 *
	 * @param array $items Report items.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function report_items( $items ) {
		// Dashboard widget.
		$items['dashboard'] = array(
			'name'     => 'dashboard',
			'title'    => __( 'Dashboard Widget', 'ga_trans' ),
			'children' => array(
				array(
					'name'     => 'general',
					'title'    => __( 'General Stats', 'ga_trans' ),
					'children' => array(
						array(
							'name'  => 'summary',
							'title' => __( 'General Stats', 'ga_trans' ),

						),
					),
				),
				array(
					'name'     => 'audience',
					'title'    => __( 'Audiences', 'ga_trans' ),
					'children' => array(
						array(
							'name'  => 'sessions',
							'title' => __( 'Sessions', 'ga_trans' ),

						),
						array(
							'name'  => 'users',
							'title' => __( 'Users', 'ga_trans' ),

						),
						array(
							'name'  => 'pageviews',
							'title' => __( 'Pageviews', 'ga_trans' ),

						),
						array(
							'name'  => 'page_sessions',
							'title' => __( 'Pages/Sessions', 'ga_trans' ),

						),
						array(
							'name'  => 'average_sessions',
							'title' => __( 'Avg. Time', 'ga_trans' ),

						),
						array(
							'name'  => 'bounce_rates',
							'title' => __( 'Bounce Rates', 'ga_trans' ),

						),
					),
				),
				array(
					'name'  => 'pages',
					'title' => __( 'Top Pages & Views', 'ga_trans' ),
				),
				array(
					'name'     => 'traffic',
					'title'    => __( 'Traffic', 'ga_trans' ),
					'children' => array(
						array(
							'name'  => 'countries',
							'title' => __( 'Top Countries', 'ga_trans' ),

						),
						array(
							'name'  => 'mediums',
							'title' => __( 'Top Medium', 'ga_trans' ),

						),
						array(
							'name'  => 'search_engines',
							'title' => __( 'Top Search Engine', 'ga_trans' ),

						),
						array(
							'name'  => 'social_networks',
							'title' => __( 'Top Social Network', 'ga_trans' ),

						),
					),
				),
			),
		);

		// All statistics page.
		$items['statistics'] = array(
			'name'     => 'statistics',
			'title'    => __( 'Statistics/Google Analytics', 'ga_trans' ),
			'children' => array(
				array(
					'name'  => 'visitors',
					'title' => __( 'Visitors', 'ga_trans' ),
				),
				array(
					'name'  => 'pages',
					'title' => __( 'Top Pages', 'ga_trans' ),
				),
				array(
					'name'  => 'countries',
					'title' => __( 'Top Countries', 'ga_trans' ),
				),
				array(
					'name'  => 'mediums',
					'title' => __( 'Top Medium', 'ga_trans' ),
				),
				array(
					'name'  => 'social_networks',
					'title' => __( 'Top Social Network', 'ga_trans' ),
				),
				array(
					'name'  => 'search_engines',
					'title' => __( 'Top Search Engine', 'ga_trans' ),
				),
			),
		);

		return $items;
	}
}