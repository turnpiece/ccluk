<?php

namespace Beehive\Core\Modules\Google_Analytics\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Template;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\View;
use Beehive\Core\Modules\Google_Analytics;

/**
 * The stats view functionality for the analytics
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
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
		// Setup required vars.
		add_filter( 'beehive_google_dashboard_stats_localize_vars', [ $this, 'dashboard_vars' ] );

		if ( Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() ) ) {
			// Setup vars for the all stats page.
			add_filter( 'beehive_google_stats_page_localize_vars', [ $this, 'all_stats_vars' ] );

			// Setup vars for the post edit page.
			add_filter( 'beehive_google_post_stats_localize_vars', [ $this, 'post_vars' ] );
		}
	}

	/**
	 * Render admin dashboard analytics widget.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		// Render widget template.
		$this->view( 'stats/google/dashboard-widget/widget', [
			'logged_in'       => Google_Auth\Helper::instance()->is_logged_in( $this->is_network() ),
			'can_get_stats'   => Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() ),
			'network'         => $this->is_network(),
			'periods'         => $this->periods(),
			'selected_period' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			'settings_url'    => Template::settings_page( 'general', $this->is_network() ),
			'statistics_url'  => Template::statistics_page( $this->is_network() ),
			'delay_notice'    => false,
		] );
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
	public function popular_widget_content( $args = [] ) {
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
	public function popular_widget_form( $args = [] ) {
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
		// Render stats metabox template.
		$this->view( 'stats/google/post-metabox/metabox', [
			'stats'      => $this->post_stats(),
			'start_date' => date( 'M j', strtotime( '-30 days' ) ),
			'end_date'   => date( 'M j', strtotime( '-1 days' ) ),
		] );

		// Enqueue scripts.
		wp_enqueue_style( 'beehive_post_stats' );
		wp_enqueue_script( 'beehive_post_stats' );
	}

	/**
	 * Render stats page content for the dashboard.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function stats_page() {
		// Render stats main page.
		$this->view( 'stats/google/stats-page/stats', [
			'logged_in'       => Google_Auth\Helper::instance()->is_logged_in( $this->is_network() ),
			'can_get_stats'   => Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() ),
			'network'         => $this->is_network(),
			'periods'         => $this->periods(),
			'selected_period' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			'delay_notice'    => false,
		] );
	}

	/**
	 * Setup script vars for the dashboard stats script.
	 *
	 * @param array $vars Localized vars.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed
	 */
	public function dashboard_vars( $vars ) {
		// Network flag.
		$vars['network'] = $this->is_network() ? 1 : 0;
		// Add labels.
		$vars['labels'] = $this->labels();

		// Can access stats?.
		$can_access = Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() );

		// Stats widget.
		if ( General::is_plugin_dashboard_widget() && $can_access ) {
			// Setup stats data.
			$vars['stats'] = $this->dashboard_stats();

			// If cache stats are empty, set a flag, we will load stats using ajax.
			if ( empty( $vars['stats'] ) ) {
				$vars['async_load_dashboard_stats'] = true;
			}
		}

		return $vars;
	}

	/**
	 * Setup script vars for the post stats script.
	 *
	 * @param array $vars Localized vars.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed
	 */
	public function post_vars( $vars ) {
		global $pagenow, $post;

		// Network flag.
		$vars['network'] = 0;
		// Add labels.
		$vars['labels'] = $this->labels();

		// Can access stats?.
		$can_access = Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() );

		// Allowed post type.
		$allowed_post = in_array( get_post_type(), Google_Analytics\Helper::instance()->post_types(), true );

		// Post stats.
		if ( 'post.php' === $pagenow && $allowed_post && $can_access ) {
			// Setup stats data.
			$vars['stats'] = $this->post_stats();
			// Post ID.
			$vars['post'] = $post->ID;

			// Set a flag to load stats via ajax.
			if ( empty( $vars['stats'] ) ) {
				$vars['async_load_post_stats'] = true;
			}
		}

		return $vars;
	}

	/**
	 * Setup script vars for the all stats script.
	 *
	 * @param array $vars Localized vars.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed
	 */
	public function all_stats_vars( $vars ) {
		// Network flag.
		$vars['network'] = $this->is_network() ? 1 : 0;
		// Add labels.
		$vars['labels'] = $this->labels();

		// Can access stats?.
		$can_access = Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() );

		// Stats widget.
		if ( General::is_plugin_stats() && $can_access ) {
			// Get stats data.
			$vars['stats'] = $this->all_stats();

			// If stats are empty, we need to load them using ajax.
			if ( empty( $vars['stats'] ) ) {
				$vars['async_load_all_stats'] = true;
			}
		}

		return $vars;
	}

	/**
	 * Get translatable labels for Google stats charts.
	 *
	 * @since 3.2.0
	 *
	 * @return array $labels
	 */
	private function labels() {
		$labels = [
			'no_info'            => __( 'No information', 'ga_trans' ),
			'top_pages'          => __( 'Top Pages', 'ga_trans' ),
			'visits'             => __( 'Visits', 'ga_trans' ),
			'views'              => __( 'Views', 'ga_trans' ),
			'top_countries'      => __( 'Top Countries', 'ga_trans' ),
			'sessions'           => __( 'Sessions', 'ga_trans' ),
			'trend'              => __( 'Trend', 'ga_trans' ),
			'average_sessions'   => __( 'Avg. time', 'ga_trans' ),
			'pageviews'          => __( 'Pageviews', 'ga_trans' ),
			'returning_visitors' => __( 'Returning visitors', 'ga_trans' ),
			'new_visitors'       => __( 'New visitors', 'ga_trans' ),
			'users'              => __( 'Users', 'ga_trans' ),
			'page_session'       => __( 'Pages/Session', 'ga_trans' ),
			'bounce_rate'        => __( 'Bounce Rate', 'ga_trans' ),
			'bounce_rates'       => __( 'Bounce Rates', 'ga_trans' ),
			'country'            => __( 'Country', 'ga_trans' ),
			'mediums'            => __( 'Mediums', 'ga_trans' ),
			'social_networks'    => __( 'Social Networks', 'ga_trans' ),
			'search_engines'     => __( 'Search Engines', 'ga_trans' ),
			'select_option'      => __( 'Select an option below to print chart data.', 'ga_trans' ),
			'has'                => __( 'has', 'ga_trans' ),
		];

		/**
		 * Filter to modify labels of Google stats charts.
		 *
		 * @param array $labels Labels.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_stats_labels', $labels );
	}

	/**
	 * Get periods for the date range filter dropdown.
	 *
	 * Create an array of date data to show as dropdown in
	 * stats dashboard widget.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function periods() {
		// Today's date.
		$today = date( 'Y-m-d' );
		// Yesterday's date.
		$yesterday = date( 'Y-m-d', strtotime( '-1 days' ) );

		// Dates array.
		$dates = [
			$today                                   => [
				'label' => __( 'Today', 'ga_trans' ),
				'end'   => $today,
			],
			$yesterday                               => [
				'label' => __( 'Yesterday', 'ga_trans' ),
				'end'   => $yesterday,
			],
			date( 'Y-m-d', strtotime( '-7 days' ) )  => [
				'label' => __( 'Last 7 days', 'ga_trans' ),
				'end'   => $yesterday,
			],
			date( 'Y-m-d', strtotime( '-30 days' ) ) => [
				'label' => __( 'Last 30 days', 'ga_trans' ),
				'end'   => $yesterday,
			],
			date( 'Y-m-d', strtotime( '-90 days' ) ) => [
				'label' => __( 'Last 90 days', 'ga_trans' ),
				'end'   => $yesterday,
			],
			date( 'Y-m-d', strtotime( '-1 years' ) ) => [
				'label' => __( 'Last year', 'ga_trans' ),
				'end'   => $yesterday,
			],
			date( 'Y-m-d', strtotime( '-3 years' ) ) => [
				'label' => __( 'Last 3 years', 'ga_trans' ),
				'end'   => $yesterday,
			],
		];

		/**
		 * Filter to add or remove periods from date filter.
		 *
		 * The key of the item should be the start date, and the value
		 * array should contain label and end date.
		 *
		 * @param array $dates Dates array.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_analytics_periods', $dates );
	}

	/**
	 * Get all stats reports data from Google.
	 *
	 * We will try to get the data from cache first.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function dashboard_stats() {
		// Stats instance.
		$stats = Google_Analytics\Stats::instance();

		// Get stats.
		$stats = $stats->stats(
			date( 'Y-m-d', strtotime( '-30 days' ) ),
			date( 'Y-m-d', strtotime( '-1 days' ) ),
			'dashboard',
			$this->is_network(),
			false,
			true
		);

		/**
		 * Filter to alter stats default data.
		 *
		 * @param array $stats Default stats.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_dashboard_stats', $stats );
	}

	/**
	 * Get all stats reports data from Google.
	 *
	 * We will get the data from cache only. If not found in cache,
	 * we will set a flag so that the data will be loaded using ajax.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function all_stats() {
		// Stats instance.
		$stats = Google_Analytics\Stats::instance();

		// Get stats.
		$stats = $stats->stats(
			date( 'Y-m-d', strtotime( '-30 days' ) ),
			date( 'Y-m-d', strtotime( '-1 days' ) ),
			'stats',
			$this->is_network(),
			false,
			true
		);

		/**
		 * Filter to alter stats default data.
		 *
		 * @param array $stats Default stats.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_all_stats', $stats );
	}

	/**
	 * Get stats data for the current post.
	 *
	 * We will try to get the data from cache first.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function post_stats() {
		global $post;

		$stats = [];

		// Only when valid post id found.
		if ( ! empty( $post->ID ) ) {
			// Stats instance.
			$stats = Google_Analytics\Stats::instance();

			// Get stats.
			$stats = $stats->post_stats(
				$post->ID,
				date( 'Y-m-d', strtotime( '-30 days' ) ),
				date( 'Y-m-d', strtotime( '-1 days' ) ),
				false,
				true
			);
		}

		/**
		 * Filter to alter stats default data.
		 *
		 * @param array $stats Default stats.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_post_stats', $stats );
	}
}