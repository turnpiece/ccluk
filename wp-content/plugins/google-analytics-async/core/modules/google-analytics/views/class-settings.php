<?php

namespace Beehive\Core\Modules\Google_Analytics\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\View;

/**
 * The Google analytics module settings view.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Settings extends View {

	/**
	 * Get the reports tree for the dashboard widget.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function dashboard_tree() {
		$tree = [
			'general'  => [
				'label' => __( 'General Stats', 'ga_trans' ),
				'items' => [
					'summary'       => __( 'Summary', 'ga_trans' ),
					'top_pages'     => __( 'Top Pages', 'ga_trans' ),
					'top_countries' => __( 'Top Countries', 'ga_trans' ),
				],
			],
			'audience' => [
				'label' => __( 'Audience', 'ga_trans' ),
				'items' => [
					'sessions'         => __( 'Sessions', 'ga_trans' ),
					'users'            => __( 'Users', 'ga_trans' ),
					'pageviews'        => __( 'Pageviews', 'ga_trans' ),
					'page_sessions'    => __( 'Pages/Session', 'ga_trans' ),
					'average_sessions' => __( 'Avg. time', 'ga_trans' ),
					'bounce_rates'     => __( 'Bounce Rates', 'ga_trans' ),
				],
			],
			'pages'    => [
				'label' => __( 'Top Pages & Views', 'ga_trans' ),
			],
			'traffic'  => [
				'label' => __( 'Traffic', 'ga_trans' ),
				'items' => [
					'countries'       => __( 'Top Countries', 'ga_trans' ),
					'mediums'         => __( 'Mediums', 'ga_trans' ),
					'search_engines'  => __( 'Search Engines', 'ga_trans' ),
					'social_networks' => __( 'Social Networks', 'ga_trans' ),
				],
			],
		];

		/**
		 * Filter hook to add/remove items to reports tree.
		 *
		 * @param array $tree Tree structure.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_report_dashboard_tree', $tree );
	}

	/**
	 * Get the reports tree for the statistics page.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function statistics_tree() {
		$tree = [
			'visitors'  => __( 'Visitors', 'ga_trans' ),
			'pages'     => __( 'Top Pages', 'ga_trans' ),
			'countries' => __( 'Top Countries', 'ga_trans' ),
			'referrals' => __( 'Referrals', 'ga_trans' ),
		];

		/**
		 * Filter hook to add/remove items from statistics reports tree.
		 *
		 * @param array $tree Tree structure.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_report_statistics_tree', $tree );
	}
}