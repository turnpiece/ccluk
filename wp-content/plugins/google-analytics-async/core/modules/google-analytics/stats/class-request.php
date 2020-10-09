<?php
/**
 * The Google API request setup class.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Analytics\Stats
 */

namespace Beehive\Core\Modules\Google_Analytics\Stats;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Exception;
use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Analytics\Data;
use Beehive\Core\Modules\Google_Analytics\Helper;
use Beehive\Google_Service_AnalyticsReporting_Metric;
use Beehive\Google_Service_AnalyticsReporting_OrderBy;
use Beehive\Google_Service_AnalyticsReporting_Dimension;
use Beehive\Google_Service_AnalyticsReporting_DateRange;
use Beehive\Google_Service_AnalyticsReporting_ReportRequest;
use Beehive\Google_Service_AnalyticsReporting_DimensionFilter;
use Beehive\Google_Service_AnalyticsReporting_MetricFilterClause;
use Beehive\Google_Service_AnalyticsReporting_DimensionFilterClause;

/**
 * Class Request
 *
 * @package Beehive\Core\Modules\Google_Analytics\Stats
 */
class Request extends Base {

	/**
	 * GA account id to get reports for.
	 *
	 * @var string
	 *
	 * @since 3.2.0
	 */
	private $account;

	/**
	 * Get dimension based on the period.
	 *
	 * @var string $period_dimension
	 *
	 * @since 3.2.0
	 */
	private $period_dimension = 'date';

	/**
	 * Current period's date range object.
	 *
	 * @var Google_Service_AnalyticsReporting_DateRange $current_period
	 *
	 * @since 3.2.0
	 */
	private $current_period;

	/**
	 * Previous period's date range object.
	 *
	 * @var Google_Service_AnalyticsReporting_DateRange $previous_period
	 *
	 * @since 3.2.0
	 */
	private $previous_period;

	/**
	 * Network flag for the request.
	 *
	 * @var bool $network
	 *
	 * @since 3.2.0
	 */
	private $network = false;

	/**
	 * API class instance.
	 *
	 * @var API $api
	 *
	 * @since 3.2.0
	 */
	private $api;

	/**
	 * Request constructor.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		// Make sure the autoloader is ready.
		General::vendor_autoload();

		// Setup API object.
		$this->api = API::instance();
	}

	/**
	 * Set API requests based on the stats type.
	 *
	 * Different stat types required different data.
	 *
	 * @param string $type    Stats type (stats, dashboard, front).
	 * @param string $from    Start date.
	 * @param string $to      End date.
	 * @param bool   $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return array $requests
	 */
	public function get( $type, $from, $to, $network = false ) {
		$requests = array();

		$this->network         = $network;
		$this->current_period  = $this->get_period( $from, $to );
		$this->previous_period = $this->get_previous_period( $from, $to );

		switch ( $type ) {
			// Stats page.
			case 'stats':
				$requests = array(
					// These stats will have multiple date range values.
					'multiple' => array(
						$this->summary(),
						$this->top_pages(),
					),
					// Current date range stats.
					'current'  => array(
						$this->top_countries(),
						$this->mediums(),
						$this->search_engines(),
						$this->social_networks(),
						$this->sessions(),
						$this->users(),
						$this->pageviews(),
						$this->page_sessions(),
						$this->average_sessions(),
						$this->bounce_rates(),
					),
					// Previous date range stats.
					'previous' => array(
						$this->sessions( false ),
						$this->users( false ),
						$this->pageviews( false ),
						$this->page_sessions( false ),
						$this->average_sessions( false ),
						$this->bounce_rates( false ),
					),
				);
				break;
			// Dashboard widget.
			case 'dashboard':
				$requests = array(
					// These stats will have multiple date range values.
					'multiple' => array(
						$this->summary(),
						$this->top_pages( 5 ),
					),
					// Current date range stats.
					'current'  => array(
						$this->top_countries( true, 5 ),
						$this->mediums(),
						$this->search_engines(),
						$this->social_networks(),
						$this->sessions(),
						$this->users(),
						$this->pageviews(),
						$this->page_sessions(),
						$this->average_sessions(),
						$this->bounce_rates(),
					),
				);
				break;
			// Popular posts widget.
			case 'popular_widget':
				$requests = array(
					'current' => array( $this->popular_pages() ),
				);
				break;

			case 'summary':
				$requests = array(
					'multiple' => array( $this->summary() ),
					'current'  => array(
						$this->popular_pages( 1 ),
						$this->search_engines( true, 1 ),
						$this->mediums( true, 1 ),
					),
				);
		}

		return $requests;
	}

	/**
	 * Set API requests for post meta box.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $from    Start date.
	 * @param string $to      End date.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest[]
	 */
	public function post( $post_id, $from, $to ) {
		$requests = array();

		// Get page permalink.
		$url = get_permalink( $post_id );

		// Only when valid post id is found.
		if ( ! empty( $url ) ) {
			// Setup dates.
			$periods = array(
				$this->get_period( $from, $to ),
				$this->get_previous_period( $from, $to ),
			);

			// If the current page is home page.
			if ( trailingslashit( network_site_url() ) === trailingslashit( $url ) ) {
				$url = '/';
			} else {
				// Get the url part without domain name.
				$url = str_replace( untrailingslashit( network_site_url() ), '', $url );
			}

			// Setup account.
			$this->setup_account();

			// Filter based on the post id.
			$dimension_filters[] = $this->get_dimension_filter(
				array(
					'pagePath' => array(
						'value'    => $url,
						'operator' => 'EXACT',
					),
				)
			);

			// Set summary request.
			$metrics = $this->get_metrics(
				array(
					'sessions',
					'pageviews',
					'users',
					'pageviewsPerSession',
					'avgSessionDuration',
					'bounceRate',
				),
				'summary'
			);

			$requests['multiple'] = array( $this->get_request( $periods, $metrics, array(), array(), $dimension_filters ) );
		}

		return $requests;
	}

	/**
	 * Set API request for summary stats.
	 *
	 * Summary stats request is using 2 date ranges.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function summary() {
		// Setup dates.
		$periods = array(
			// Current period.
			$this->current_period,
			// We need stats for the previous period also.
			$this->previous_period,
		);

		// Setup account.
		$this->setup_account( $this->network );

		// Set summary request.
		$metrics = $this->get_metrics(
			array(
				'sessions',
				'pageviews',
				'users',
				'pageviewsPerSession',
				'avgSessionDuration',
				'bounceRate',
				'percentNewSessions',
				'newUsers',
			),
			'summary'
		);

		return $this->get_request( $periods, $metrics );
	}

	/**
	 * Set API request for popular pages widget stats.
	 *
	 * This is not required in network admin.
	 *
	 * @param int $page_size Page size.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function popular_pages( $page_size = 0 ) {
		// Setup dates.
		$periods = array( $this->current_period );

		// Setup account.
		$this->setup_account( $this->network );

		/**
		 * Filter hook to modify no. of popular page items required.
		 *
		 * @param int  $page_size Page size (default is 0 for all).
		 * @param bool $network   Network flag.
		 *
		 * @since 3.2.0
		 */
		$page_size = apply_filters( 'beehive_google_analytics_request_popular_posts_size', $page_size, $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'pageviews' ), 'pages' );

		$dimensions = $this->get_dimensions( array( 'hostname', 'pageTitle', 'pagePath' ) );

		// Order by pageviews.
		$orders = $this->get_orders( array( 'pageviews' ) );

		return $this->get_request( $periods, $metrics, $dimensions, $orders, array(), array(), $page_size );
	}

	/**
	 * Set API request for top visited pages stats.
	 *
	 * @param int $page_size Page size.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function top_pages( $page_size = 25 ) {
		// Setup dates.
		$periods = array(
			// Current period.
			$this->current_period,
			// We need stats for the previous period also.
			$this->previous_period,
		);

		// Setup account.
		$this->setup_account( $this->network );

		/**
		 * Filter hook to modify no. of pages required.
		 *
		 * @param int  $page_size Page size (default is 0 for all).
		 * @param bool $network   Network flag.
		 *
		 * @since 3.2.4
		 */
		$page_size = apply_filters( 'beehive_google_analytics_request_top_pages_size', $page_size, $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'avgSessionDuration', 'pageviews' ), 'pages' );

		$dimensions = $this->get_dimensions( array( 'hostname', 'pageTitle', 'pagePath' ) );

		// Order by pageviews.
		$orders = $this->get_orders( array( 'pageviews' ) );

		return $this->get_request( $periods, $metrics, $dimensions, $orders, array(), array(), $page_size );
	}

	/**
	 * Set API request for the top visited countries.
	 *
	 * @param bool $current   Is this request is for current period.
	 * @param int  $page_size Page size.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function top_countries( $current = true, $page_size = 25 ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		/**
		 * Filter hook to modify no. of countries returned.
		 *
		 * @param int  $page_size Page size (default is 0 for all).
		 * @param bool $network   Network flag.
		 *
		 * @since 3.2.4
		 */
		$page_size = apply_filters( 'beehive_google_analytics_request_top_countries_size', $page_size, $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'pageviews' ), 'countries' );

		$dimensions = $this->get_dimensions( array( 'country', 'countryIsoCode' ) );

		// Order by pageviews.
		$orders = $this->get_orders( array( 'pageviews' ) );

		return $this->get_request( $periods, $metrics, $dimensions, $orders, array(), array(), $page_size );
	}

	/**
	 * Set API request for the mediums stats.
	 *
	 * @param bool $current   Is this request is for current period.
	 * @param int  $page_size Page size.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function mediums( $current = true, $page_size = 0 ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		/**
		 * Filter hook to modify no. of mediums returned.
		 *
		 * @param int  $page_size Page size (default is 0 for all).
		 * @param bool $network   Network flag.
		 *
		 * @since 3.2.4
		 */
		$page_size = apply_filters( 'beehive_google_analytics_request_mediums_size', $page_size, $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'sessions' ), 'mediums' );

		$dimensions = $this->get_dimensions( array( 'channelGrouping' ) );

		// Order by sessions.
		$orders = $this->get_orders( array( 'sessions' ) );

		return $this->get_request( $periods, $metrics, $dimensions, $orders, array(), array(), $page_size );
	}

	/**
	 * Set API request for the social network stats.
	 *
	 * @param bool $current Is this request is for current period.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function social_networks( $current = true ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'sessions' ), 'social_networks' );

		$dimensions = $this->get_dimensions( array( 'socialNetwork' ) );

		// Order by sessions.
		$orders = $this->get_orders( array( 'sessions' ) );

		return $this->get_request( $periods, $metrics, $dimensions, $orders );
	}

	/**
	 * Set API request for sessions stats.
	 *
	 * @param bool $current Is this request is for current period.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function sessions( $current = true ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'sessions' ), 'sessions' );

		$period_dimension = $this->get_period_dimension();

		$dimensions = $this->get_dimensions( array( $period_dimension ) );

		return $this->get_request( $periods, $metrics, $dimensions );
	}

	/**
	 * Set API request for the search engine traffic stats.
	 *
	 * @param bool $current   Is this request is for current period.
	 * @param int  $page_size Page size.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function search_engines( $current = true, $page_size = 0 ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		/**
		 * Filter hook to modify no. of search engines returned.
		 *
		 * @param int  $page_size Page size (default is 0 for all).
		 * @param bool $network   Network flag.
		 *
		 * @since 3.2.4
		 */
		$page_size = apply_filters( 'beehive_google_analytics_request_search_engines_size', $page_size, $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'sessions' ), 'search_engines' );

		$dimensions = $this->get_dimensions( array( 'medium', 'source' ) );

		$dimension_filters[] = $this->get_dimension_filter(
			array(
				'medium' => array(
					'value'    => 'organic',
					'operator' => 'EXACT',
				),
			)
		);

		// Order by sessions.
		$orders = $this->get_orders( array( 'sessions' ) );

		return $this->get_request( $periods, $metrics, $dimensions, $orders, $dimension_filters, array(), $page_size );
	}

	/**
	 * Set API request for the users list stats.
	 *
	 * @param bool $current Is this request is for current period.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function users( $current = true ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'users' ), 'users' );

		$period_dimension = $this->get_period_dimension();

		$dimensions = $this->get_dimensions( array( $period_dimension ) );

		return $this->get_request( $periods, $metrics, $dimensions );
	}

	/**
	 * Set API request for the pageviews stats list.
	 *
	 * @param bool $current Is this request is for current period.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function pageviews( $current = true ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'pageviews' ), 'pageviews' );

		$period_dimension = $this->get_period_dimension();

		$dimensions = $this->get_dimensions( array( $period_dimension ) );

		return $this->get_request( $periods, $metrics, $dimensions );
	}

	/**
	 * Set API request for the page sessions stats list.
	 *
	 * @param bool $current Is this request is for current period.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function page_sessions( $current = true ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'pageviewsPerSession' ), 'page_sessions' );

		$period_dimension = $this->get_period_dimension();

		$dimensions = $this->get_dimensions( array( $period_dimension ) );

		return $this->get_request( $periods, $metrics, $dimensions );
	}

	/**
	 * Set API request for the average sessions list.
	 *
	 * @param bool $current Is this request is for current period.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function average_sessions( $current = true ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'avgSessionDuration' ), 'average_sessions' );

		$period_dimension = $this->get_period_dimension();

		$dimensions = $this->get_dimensions( array( $period_dimension ) );

		return $this->get_request( $periods, $metrics, $dimensions );
	}

	/**
	 * Set API request for the bounce rates list.
	 *
	 * @param bool $current Is this request is for current period.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function bounce_rates( $current = true ) {
		// Setup dates.
		$periods = array( $current ? $this->current_period : $this->previous_period );

		// Setup account.
		$this->setup_account( $this->network );

		// Set top pages request.
		$metrics = $this->get_metrics( array( 'bounceRate' ), 'bounce_rates' );

		$period_dimension = $this->get_period_dimension();

		$dimensions = $this->get_dimensions( array( $period_dimension ) );

		return $this->get_request( $periods, $metrics, $dimensions );
	}

	/**
	 * Get reports metrics for API request.
	 *
	 * You can query multiple metrics by passing metric name as an array.
	 * Do not append ga: prefix, it will be handled within the method.
	 * To get list of items see:
	 * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
	 *
	 * @param array  $metrics Metric types.
	 * @param string $alias   Custom alias base name.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_Metric[]
	 */
	public function get_metrics( $metrics = array(), $alias = 'beehive' ) {
		// Empty the metrics.
		$metrics_instances = array();

		// Set each metrics.
		foreach ( (array) $metrics as $type ) {
			// Create the Metrics object.
			$metric = new Google_Service_AnalyticsReporting_Metric();
			// Set metrics type.
			$metric->setExpression( "ga:{$type}" );
			// Set custom alias to identify the data.
			$metric->setAlias( "{$alias}:{$type}" );

			// Set to metrics instances.
			$metrics_instances[] = $metric;
		}

		return $metrics_instances;
	}

	/**
	 * Set reports dimensions for API request.
	 *
	 * You can query multiple dimensions by passing metric name as an array.
	 * Do not append ga: prefix, it will be handled within the method.
	 * To get list of items see:
	 * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
	 *
	 * @param array $dimensions Dimension types.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_Dimension[]
	 */
	public function get_dimensions( $dimensions = array() ) {
		// Empty the dimensions.
		$dimension_instances = array();

		// Set each metrics.
		foreach ( (array) $dimensions as $type ) {
			// Create the Metrics object.
			$dimension = new Google_Service_AnalyticsReporting_Dimension();
			// Set dimension type.
			$dimension->setName( "ga:{$type}" );

			// Set to dimension instances.
			$dimension_instances[] = $dimension;
		}

		return $dimension_instances;
	}

	/**
	 * Set reports sorting to filter results.
	 *
	 * You can sort using multiple fields. To get list of items see
	 * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
	 *
	 * @param array $fields Fields to sort based on.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_OrderBy[]
	 */
	public function get_orders( $fields = array() ) {
		// Empty the sortings.
		$orders = array();

		// Only when fields are not empty.
		if ( ! empty( $fields ) ) {
			// Set each metrics.
			foreach ( (array) $fields as $field ) {
				// Create sorting object.
				$sorting = new Google_Service_AnalyticsReporting_OrderBy();
				// Set sorting field.
				$sorting->setFieldName( "ga:{$field}" );
				// Set order.
				$sorting->setSortOrder( 'DESCENDING' );

				// Set to sorting instances.
				$orders[] = $sorting;
			}
		}

		return $orders;
	}

	/**
	 * Set reports dimensions filter.
	 *
	 * Do not append ga: to field names.
	 *
	 * @param array  $filter_params Filter items.
	 * @param string $operator      Operator (OR or AND).
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_DimensionFilterClause
	 */
	public function get_dimension_filter( $filter_params, $operator = 'AND' ) {
		// Create filter clause object.
		$filter_clause = new Google_Service_AnalyticsReporting_DimensionFilterClause();

		// Set each fields.
		foreach ( (array) $filter_params as $field => $data ) {
			// Create filter object.
			$filter = new Google_Service_AnalyticsReporting_DimensionFilter();
			// Set dimension name.
			$filter->setDimensionName( "ga:{$field}" );
			// Set value.
			$filter->setExpressions( array( $data['value'] ) );
			// Set operator.
			if ( isset( $data['operator'] ) ) {
				$filter->setOperator( $data['operator'] );
			}

			// Add to filters array.
			$filters[] = $filter;
		}

		// Set filters.
		$filter_clause->setFilters( $filters );
		// Filter operator.
		$filter_clause->setOperator( $operator );

		return $filter_clause;
	}

	/**
	 * Setup reporting period to get stats data.
	 *
	 * Only allowed periods will be processed. For other periods you
	 * can use custom type and pass the from and to dates.
	 *
	 * @param string $from Start date.
	 * @param string $to   End date.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_DateRange
	 */
	public function get_period( $from, $to ) {
		try {
			// Make sure the dates are in proper format.
			$from = gmdate( 'Y-m-d', strtotime( $from ) );
			$to   = gmdate( 'Y-m-d', strtotime( $to ) );

			// Create date objects from the periods.
			$date_from = date_create( $from );
			$date_to   = date_create( $to );
			// Get the difference between periods.
			$days = (int) date_diff( $date_from, $date_to )->days;
		} catch ( Exception $e ) {
			$days = 0;
		}

		// We need to show date in month format.
		if ( $days >= 364 ) {
			$this->period_dimension = 'yearMonth';
		} elseif ( $days >= 89 ) {
			$this->period_dimension = 'yearWeek';
		} elseif ( $days > 0 ) {
			$this->period_dimension = 'date';
		} else {
			$this->period_dimension = 'dateHour';
		}

		// Create the DateRange object.
		$date = new Google_Service_AnalyticsReporting_DateRange();
		// Set start date.
		$date->setStartDate( $from );
		// Set end date.
		$date->setEndDate( $to );

		return $date;
	}

	/**
	 * Setup reporting period to get stats data.
	 *
	 * Only allowed periods will be processed. For other periods you
	 * can use custom type and pass the from and to dates.
	 *
	 * @param string $from Start date.
	 * @param string $to   End date.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_DateRange
	 */
	public function get_previous_period( $from, $to ) {
		$date = false;

		// Get previous period.
		$period = Helper::get_previous_period( $from, $to );

		// Create the DateRange object.
		if ( ! empty( $period['from'] ) ) {
			$date = new Google_Service_AnalyticsReporting_DateRange();
			// Set start date.
			$date->setStartDate( $period['from'] );
			// Set end date.
			$date->setEndDate( $period['to'] );
		}

		return $date;
	}

	/**
	 * Set reports dimensions for API request.
	 *
	 * You can query multiple dimensions by passing metric name as an array.
	 * Do not append ga: prefix, it will be handled within the method.
	 *
	 * @param Google_Service_AnalyticsReporting_DateRange[]             $periods           Date range periods.
	 * @param Google_Service_AnalyticsReporting_Metric[]                $metrics           Metrics array.
	 * @param Google_Service_AnalyticsReporting_Dimension[]             $dimensions        Dimensions array.
	 * @param Google_Service_AnalyticsReporting_OrderBy[]               $orders            Sorting order array.
	 * @param Google_Service_AnalyticsReporting_DimensionFilterClause[] $dimention_filters Dimension filters.
	 * @param Google_Service_AnalyticsReporting_MetricFilterClause[]    $metrics_filters   Metric filters (currently not used).
	 * @param int                                                       $page_size         Maximum no. of items.
	 *
	 * @since 3.2.0
	 *
	 * @return Google_Service_AnalyticsReporting_ReportRequest
	 */
	public function get_request( $periods, $metrics, $dimensions = array(), $orders = array(), $dimention_filters = array(), $metrics_filters = array(), $page_size = 0 ) {
		// Create the ReportRequest object.
		$request = new Google_Service_AnalyticsReporting_ReportRequest();
		// Set view.
		$request->setViewId( $this->account );
		// Set date range.
		$request->setDateRanges( $periods );
		// Set metrics.
		$request->setMetrics( $metrics );
		// Set dimensions.
		$request->setDimensions( $dimensions );
		// Maximum no. of items.
		if ( ! empty( $page_size ) ) {
			$request->setPageSize( $page_size );
		}
		// Set sampling level.
		$request->setSamplingLevel( 'LARGE' );
		// Set sorting.
		if ( ! empty( $orders ) ) {
			$request->setOrderBys( $orders );
		}

		// Get url filters.
		$basic_filters = $this->get_basic_filters();

		// Include basic filters.
		if ( ! empty( $basic_filters ) ) {
			$dimention_filters = array_merge( $basic_filters, $dimention_filters );
		}

		// Set dimension filters.
		if ( ! empty( $dimention_filters ) ) {
			$request->setDimensionFilterClauses( $dimention_filters );
		}
		// Set metric filters.
		if ( ! empty( $metrics_filters ) ) {
			$request->setMetricFilterClauses( $metrics_filters );
		}
		// Do not exclude empty rows.
		$request->setIncludeEmptyRows( true );

		// Hide unwanted data.
		$request->setHideTotals( true );
		$request->setHideValueRanges( true );

		return $request;
	}

	/**
	 * Set basic filters require for all requests.
	 *
	 * Few filters that are required to make sure only the required
	 * data is being displayed.
	 *
	 * @since 3.2.0
	 */
	private function get_basic_filters() {
		$filters = array();

		// Remove unwanted items.
		// $filters[] = $this->get_dimension_filter( [
		// 'pagePath' => [
		// 'value' => '!@preview=true',
		// ],
		// ] );
		// When subsite data is loaded from network credentials,
		// make sure to show stats only for current site.
		if ( ! $this->network && Helper::instance()->login_source( $this->network ) === 'network' ) {
			$filters[] = $this->get_url_filter();
		}

		return $filters;
	}

	/**
	 * Set url filter for the single site stats.
	 *
	 * When stats are loaded using the login from network
	 * admin, we need to show stats only for the currently viewing
	 * single site.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function get_url_filter() {
		$filters = array();

		// No need for network admin stats.
		if ( $this->is_network() ) {
			return array();
		}

		// Get home url.
		$url = home_url();

		/**
		 * Filter hook to alter the home url before filtering.
		 *
		 * Domain mapping plugins can use this filter to add the support.
		 *
		 * @param string $url Home URL.
		 *
		 * @since 3.2.4
		 */
		$url = apply_filters( 'beehive_google_analytics_request_home_url', $url );

		// Remove the protocols.
		$url_parts = explode( '/', str_replace( array( 'http://', 'https://' ), '', $url ) );

		// Incase it is empty, try site url.
		if ( ! $url_parts ) {
			$url_parts = explode( '/', str_replace( array( 'http://', 'https://' ), '', site_url() ) );
		}

		// Set host filter.
		$filters[] = $this->get_dimension_filter(
			array(
				'hostname' => array(
					'value'    => $url_parts[0],
					'operator' => 'EXACT',
				),
			)
		);

		// If its in subdirectory mode, then set correct beginning for page path.
		if ( count( $url_parts ) > 1 ) {
			unset( $url_parts[0] );
			$pagepath = implode( '/', $url_parts );

			// Set path filter.
			$filters[] = $this->get_dimension_filter(
				array(
					'pagePath' => array(
						'value' => "^/$pagepath/.*",
					),
				)
			);
		}

		return $filters;
	}

	/**
	 * Setup GA account string for the reports data.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function setup_account( $network = false ) {
		// Decide login source.
		$network = Helper::instance()->login_source( $network ) === 'network';

		// Get currently assigned id.
		$account = beehive_analytics()->settings->get( 'account_id', 'google', $network );

		// Some times, account id can be empty when user didn't save the settings. We need to handle this ourselves.
		if ( empty( $account ) ) {
			// Get available profiles.
			$profiles = Data::instance()->profiles( $network );
			if ( ! empty( $profiles ) ) {
				// Set the first item for now.
				$account = $profiles[0]->getId();
				// Update the settings.
				beehive_analytics()->settings->update( 'account_id', $account, 'google', $network );
			}
		}

		$this->account = $account;
	}

	/**
	 * Get dimension based on the period set.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_period_dimension() {
		return $this->period_dimension;
	}
}