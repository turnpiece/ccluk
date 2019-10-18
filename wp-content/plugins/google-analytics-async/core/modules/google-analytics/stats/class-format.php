<?php

namespace Beehive\Core\Modules\Google_Analytics\Stats;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;

/**
 * The data formatter class for Google stats.
 *
 * This class is little complex. Google Analytics Reporting API
 * response is really complex. We need to properly format the response.
 * Modify with caution.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Format extends Base {

	/**
	 * Format the API request response data.
	 *
	 * We are getting all the stats data in a single or a few API request(s).
	 * So, we need to format the request response and get individual
	 * data based on the response.
	 *
	 * @param array  $data       Google_Service_AnalyticsReporting_Report.
	 * @param string $stats_type Stats type (general or post).
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function format( $data, $stats_type = 'stats' ) {
		$final_stats = [];

		foreach ( $data as $type => $reports ) {
			// Format stats.
			$stats = [];

			if ( ! empty( $reports ) ) {
				foreach ( $reports as $report ) {
					// Get header of report.
					$header = $report->getColumnHeader();
					// Dimension headers.
					$dimensions = (array) $header->getDimensions();
					// Metric headers.
					$metrics = $header->getMetricHeader()->getMetricHeaderEntries();
					// Report data.
					$rows = $report->getData()->getRows();

					// Format.
					$this->setup( $rows, $dimensions, $metrics, $stats );
				}

				// Final stats.
				$final_stats[ $type ] = $stats;
			}
		}

		// Format the data for different usage.
		switch ( $stats_type ) {
			case 'stats':
				$stats = $this->stats_page( $final_stats );
				break;
			case 'dashboard':
				// Format the dashboard widget data.
				$stats = $this->dashboard_widget( $final_stats );
				break;
			case 'popular_widget':
				$stats = $this->popular_widget( $final_stats );
				break;
			case 'post':
				$stats = $this->post( $final_stats );
				break;
			default:
				$stats = [];
				break;
		}

		return $stats;
	}

	/**
	 * Format the response object to array.
	 *
	 * Get the metrics and dimension values into an array.
	 *
	 * @param array $data     Stats data from Google.
	 * @param array $dheaders Dimension headers.
	 * @param array $mheaders Metric headers.
	 * @param array $stats    Stats data.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	private function setup( $data, $dheaders, $mheaders, &$stats = [] ) {
		// Initialize the data array.
		$dimension_data = $metric_data = [];

		$data_count = count( $data );

		// Loop through each item and get the data.
		for ( $i = 0; $i < $data_count; $i ++ ) {
			// Single item.
			$row = $data[ $i ];
			// Dimensions of current item.
			$dimensions = (array) $row->getDimensions();
			// Metrics of current item.
			$metrics = $row->getMetrics();
			// DHeaders count.
			$dheaders_count   = count( $dheaders );
			$dimensions_count = count( $dimensions );
			// Setup dimensions.
			for ( $j = 0; $j < $dheaders_count && $j < $dimensions_count; $j ++ ) {
				$dimension_data[ $dheaders[ $j ] ] = $this->format_value( $dheaders[ $j ], $dimensions[ $j ] );
			}

			$metrics_count = count( $metrics );

			// Setup metrics.
			for ( $k = 0; $k < $metrics_count; $k ++ ) {
				// Metric values.
				$values = $metrics[ $k ]->getValues();
				// No. of values.
				$value_count = count( $values );
				// Setup the values to array.
				for ( $l = 0; $l < $value_count; $l ++ ) {
					// Single item.
					$entry = $mheaders[ $l ];
					// Names.
					$names = $this->get_type( $entry->getName() );
					// All metric items has same type.
					if ( empty( $type ) ) {
						$type = $names[0];
					}

					// Format the value.
					$value = $this->format_value( $names[1], $values[ $l ] );

					// Set to array. $names[1] will be the metric name.
					if ( $value_count > 1 ) {
						$metric_data[ $names[1] ][ $k ] = $value;
					} else {
						$metric_data[ $names[1] ] = $value;
					}
				}
			}

			// Set both values to array.
			if ( ! empty( $type ) ) {
				$stats[ $type ][ $i ] = array_merge( $metric_data, $dimension_data );
			}
		}
	}

	/**
	 * Get report type from metric name.
	 *
	 * We have set custom alias for report metric. The alias is in the
	 * form of type:metrics. Now get the first part which is the type
	 * name and the second part which is metric name.
	 *
	 * @param string $name Metric alias name.
	 *
	 * @since 3.2.0
	 *
	 * @return array Type and metric.
	 */
	private function get_type( $name ) {
		// Split the name.
		return explode( ':', $name );
	}

	/**
	 * Format different values returned from API.
	 *
	 * @param string $name  Metric of Dimension name.
	 * @param mixed  $value Field value.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed
	 */
	private function format_value( $name, $value ) {
		switch ( $name ) {
			// Normal numbers.
			case 'sessions':
			case 'users':
			case 'pageviews':
				$value = (int) $value;
				break;

			// With decimals.
			case 'percentNewSessions':
			case 'bounceRate':
			case 'pageviewsPerSession':
				$value = (float) number_format( $value, 2 );
				break;

			// Year and month.
			case 'ga:yearMonth':
				// Create a DateTime object from the Ym format.
				$date = \DateTime::createFromFormat( 'Ym', $value );
				// Use the d/m/Y format.
				$value = $date->format( 'M Y' );
				break;

			// Hour.
			case 'ga:hour':
				// Create a DateTime object from the Ym format.
				$date = \DateTime::createFromFormat( 'H', $value );
				// Use the d/m/Y format.
				$value = $date->format( 'h A' );
				break;

			// Date format.
			case 'ga:date':
				// Create a DateTime object from the Ymd format.
				$date = \DateTime::createFromFormat( 'Ymd', $value );
				// Use the d/m/Y format.
				$value = $date->format( 'M j' );
				break;

			// Group others.
			case 'ga:socialNetwork':
			case 'ga:channelGrouping':
				// Make it as 'others'.
				if ( '(not set)' === $value ) {
					$value = __( 'Other', 'ga_trans' );
				}
				break;

			// Link data.
			case 'ga:hostname':
			case 'ga:pageTitle':
			case 'ga:pagePath':
				// Value not found.
				if ( '(not set)' === $value ) {
					$value = '';
				}
				break;
		}

		return $value;
	}

	/**
	 * Get the trend by calculating difference with previous period.
	 *
	 * @param string $type     Value type.
	 * @param mixed  $current  Current value.
	 * @param mixed  $previous Previous period value.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed
	 */
	private function trend_value( $type, $current, $previous ) {
		switch ( $type ) {
			case 'bounceRate':
			case 'pageviewsPerSession':
			case 'pageviews':
			case 'sessions':
			case 'users':
				$current  = (float) $current;
				$previous = (float) $previous;
				// When previous value is empty, trend is 100%.
				if ( empty( $previous ) && ! empty( $current ) ) {
					$trend = 100;
				} elseif ( ! empty( $previous ) && empty( $current ) ) {
					// When current value is 0 and previous value is not, trend is -100.
					$trend = - 100;
				} else {
					if ( $current === $previous ) {
						$trend = 0;
					} else {
						$diff  = $current - $previous;
						$trend = ( $diff / $previous ) * 100;
					}
				}

				return round( $trend );
			// Time difference.
			case 'avgSessionDuration':
				// Convert to seconds.
				$current  = strtotime( $current ) - strtotime( '00:00:00' );
				$previous = strtotime( $previous ) - strtotime( '00:00:00' );

				// Now it's int, get the value.
				return $this->trend_value( 'sessions', $current, $previous );
		}

		return 0;
	}

	/**
	 * Create a anchor tag link from the given values.
	 *
	 * We need to make sure the anchor link is generated
	 * only when the host name is valid.
	 *
	 * @param string $host  Host name.
	 * @param string $path  Page path.
	 * @param string $title Page title.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function get_anchor( $host, $path, $title ) {
		// Generate url from the data.
		$url = $this->get_link( $host, $path );

		// Only if url is generated.
		if ( empty( $url ) ) {
			return '';
		}

		return "<a href=\"{$url}\" target=\"_blank\" title=\"$title ($url)\">{$path}</a>";
	}

	/**
	 * Create a link from the given values.
	 *
	 * @param string $host Host name.
	 * @param string $path Page path.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function get_link( $host, $path ) {
		// We need valid host and title.
		if ( empty( $host ) || empty( $path ) ) {
			return '';
		}

		// Generate url from the data.
		return esc_url( 'http://' . $host . $path );
	}

	/**
	 * Format the time string to new format.
	 *
	 * We need time in string format as well as array of ints.
	 *
	 * @param string $value Time string.
	 * @param string $type  Time type.
	 *
	 * @since 3.2.0
	 *
	 * @return array|string
	 */
	private function get_time( $value, $type = 'int' ) {
		// Get 3 value array of hour, minutes and seconds.
		if ( 'int' === $type ) {
			return [
				(int) date( 'H', $value ),
				(int) date( 'i', $value ),
				(int) date( 's', $value ),
			];
		}

		return date( 'H:i:s', $value );
	}

	/**
	 * Format the data for dashboard widget.
	 *
	 * Format the data array into the format of dashboard widget.
	 *
	 * @param array $data Stats data from Google.
	 *
	 * @since 3.2.0
	 *
	 * @return array $stats
	 */
	private function dashboard_widget( $data ) {
		$stats = [];

		// Oi, we need data.
		if ( empty( $data ) ) {
			return $stats;
		}

		// Format summary data.
		if ( isset( $data['multiple']['summary'] ) ) {
			$stats['summary'] = $this->summary( $data );

			// Format countries list.
			if ( isset( $data['current']['countries'] ) ) {
				foreach ( $data['current']['countries'] as $country ) {
					// Top countries full details.
					$stats['countries'][] = [
						$country['ga:country'],
						$country['ga:countryIsoCode'],
						$country['pageviews'],
					];
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['mediums'] ) ) {
				foreach ( $data['current']['mediums'] as $medium ) {
					// Medium data.
					$stats['mediums'][] = [ $medium['ga:channelGrouping'], $medium['sessions'] ];
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['search_engines'] ) ) {
				foreach ( $data['current']['search_engines'] as $search_engine ) {
					// Search engine data.
					$stats['search_engines'][] = [ ucfirst( $search_engine['ga:source'] ), $search_engine['sessions'] ];
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['social_networks'] ) ) {
				foreach ( $data['current']['social_networks'] as $social_network ) {
					// Social network data.
					$stats['social_networks'][] = [ $social_network['ga:socialNetwork'], $social_network['sessions'] ];
				}
			}

			// Format pages list.
			if ( isset( $data['multiple']['pages'] ) ) {
				foreach ( $data['multiple']['pages'] as $page ) {
					// Top countries full details.
					$stats['pages'][] = [
						$this->get_anchor( $page['ga:hostname'], $page['ga:pagePath'], $page['ga:pageTitle'] ),
						$this->get_time( $page['avgSessionDuration'][0], 'string' ),
						$page['pageviews'][0],
						$this->trend_value(
							'pageviews',
							$page['pageviews'][0], // Current period value.
							$page['pageviews'][1] // Previous period value.
						),
					];
				}

				// We need top 5 for general section.
				if ( ! isset( $stats['top_pages'] ) || count( $stats['top_pages'] ) < 5 ) {
					$stats['top_pages'] = array_slice( $stats['pages'], 0, 5, true );
				}
			}

			// Format sessions list.
			if ( isset( $data['current']['sessions'] ) ) {
				foreach ( $data['current']['sessions'] as $session ) {
					$date = $this->get_period_value( $session );
					// Top countries full details.
					$stats['sessions'][] = [ $date, $session['sessions'] ];
				}
			}

			// Format users list.
			if ( isset( $data['current']['users'] ) ) {
				foreach ( $data['current']['users'] as $user ) {
					$date = $this->get_period_value( $user );
					// Top countries full details.
					$stats['users'][] = [ $date, $user['users'] ];
				}
			}

			// Format page views list.
			if ( isset( $data['current']['pageviews'] ) ) {
				foreach ( $data['current']['pageviews'] as $pageview ) {
					$date = $this->get_period_value( $pageview );
					// Top countries full details.
					$stats['pageviews'][] = [ $date, $pageview['pageviews'] ];
				}
			}

			// Format pages per sessions list.
			if ( isset( $data['current']['page_sessions'] ) ) {
				foreach ( $data['current']['page_sessions'] as $session ) {
					$date = $this->get_period_value( $session );
					// Top countries full details.
					$stats['page_sessions'][] = [ $date, $session['pageviewsPerSession'] ];
				}
			}

			// Format average sessions list.
			if ( isset( $data['current']['average_sessions'] ) ) {
				foreach ( $data['current']['average_sessions'] as $session ) {
					$date = $this->get_period_value( $session );
					// Top countries full details.
					$stats['average_sessions'][] = [ $date, $this->get_time( $session['avgSessionDuration'] ) ];
				}
			}

			// Format bounce rates list.
			if ( isset( $data['current']['bounce_rates'] ) ) {
				foreach ( $data['current']['bounce_rates'] as $rate ) {
					$date = $this->get_period_value( $rate );
					// Top countries full details.
					$stats['bounce_rates'][] = [ $date, $rate['bounceRate'] ];
				}
			}
		}

		return $stats;
	}

	/**
	 * Format the data for front end widget.
	 *
	 * Get the required data from Google response and format.
	 *
	 * @param array $data Stats data from Google.
	 *
	 * @since 3.2.0
	 *
	 * @return array $stats
	 */
	private function popular_widget( $data ) {
		$stats = [];

		// Format pages list.
		if ( isset( $data['current']['pages'] ) ) {
			foreach ( $data['current']['pages'] as $page ) {
				// Top pages list.
				$stats['pages'][] = $this->get_link( $page['ga:hostname'], $page['ga:pagePath'] );
			}
		}

		return $stats;
	}

	/**
	 * Format the data for all stats page.
	 *
	 * Get the required data from Google response and format.
	 *
	 * @param array $data Stats data from Google.
	 *
	 * @since 3.2.0
	 *
	 * @return array $stats
	 */
	private function post( $data ) {
		// Oy hello, we need data.
		if ( empty( $data ) ) {
			return [];
		} else {
			// Format summary data.
			return $this->summary( $data );
		}
	}

	/**
	 * Format the data for all stats page.
	 *
	 * Get the required data from Google response and format.
	 *
	 * @param array $data Stats data from Google.
	 *
	 * @since 3.2.0
	 *
	 * @return array $stats
	 */
	private function stats_page( $data ) {
		$stats = [];

		// Return early when don't get the data we deserve.
		if ( empty( $data ) ) {
			return $stats;
		}

		// Format summary data.
		if ( isset( $data['multiple']['summary'] ) ) {
			$stats['summary'] = $this->summary( $data );

			// Format countries list.
			if ( isset( $data['current']['countries'] ) ) {
				foreach ( $data['current']['countries'] as $country ) {
					// Top countries full details.
					$stats['countries'][] = [
						$country['ga:country'],
						$country['ga:countryIsoCode'],
						$country['pageviews'],
					];
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['mediums'] ) ) {
				foreach ( $data['current']['mediums'] as $medium ) {
					// Medium data.
					$stats['mediums'][] = [ $medium['ga:channelGrouping'], $medium['sessions'] ];
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['search_engines'] ) ) {
				foreach ( $data['current']['search_engines'] as $search_engine ) {
					// Search engine data.
					$stats['search_engines'][] = [ $search_engine['ga:source'], $search_engine['sessions'] ];
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['social_networks'] ) ) {
				foreach ( $data['current']['social_networks'] as $social_network ) {
					// Social network data.
					$stats['social_networks'][] = [ $social_network['ga:socialNetwork'], $social_network['sessions'] ];
				}
			}

			// Format pages list.
			if ( isset( $data['multiple']['pages'] ) ) {
				foreach ( $data['multiple']['pages'] as $page ) {
					// Top countries full details.
					$stats['pages'][] = [
						$this->get_anchor( $page['ga:hostname'], $page['ga:pagePath'], $page['ga:pageTitle'] ),
						$this->get_time( $page['avgSessionDuration'][0], 'string' ),
						$page['pageviews'][0],
						$this->trend_value(
							'pageviews',
							$page['pageviews'][0], // Current period value.
							$page['pageviews'][1] // Previous period value.
						),
					];
				}
			}

			// Format sessions list.
			if ( isset( $data['current']['sessions'], $data['previous']['sessions'] ) ) {
				$stats['sessions'] = $this->setup_periodic_values( $data['current']['sessions'], $data['previous']['sessions'], 'sessions' );
			}

			// Format users list.
			if ( isset( $data['current']['users'], $data['previous']['users'] ) ) {
				$stats['users'] = $this->setup_periodic_values( $data['current']['users'], $data['previous']['users'], 'users' );
			}

			// Format page views list.
			if ( isset( $data['current']['pageviews'], $data['previous']['pageviews'] ) ) {
				$stats['pageviews'] = $this->setup_periodic_values( $data['current']['pageviews'], $data['previous']['pageviews'], 'pageviews' );
			}

			// Format pages per sessions list.
			if ( isset( $data['current']['page_sessions'], $data['previous']['page_sessions'] ) ) {
				$stats['page_sessions'] = $this->setup_periodic_values( $data['current']['page_sessions'], $data['previous']['page_sessions'], 'pageviewsPerSession' );
			}

			// Format average sessions list.
			if ( isset( $data['current']['average_sessions'], $data['previous']['average_sessions'] ) ) {
				$stats['average_sessions'] = $this->setup_periodic_values( $data['current']['average_sessions'], $data['previous']['average_sessions'], 'avgSessionDuration' );
			}

			// Format bounce rates list.
			if ( isset( $data['current']['bounce_rates'], $data['previous']['bounce_rates'] ) ) {
				$stats['bounce_rates'] = $this->setup_periodic_values( $data['current']['bounce_rates'], $data['previous']['bounce_rates'], 'bounceRate' );
			}
		}

		return $stats;
	}

	/**
	 * Format the data to get the previous period data separately.
	 *
	 * GA API will return date comparison data in weird format. We need
	 * to separate them for our convenience.
	 * Please note, the count of current and previous data should be same.
	 *
	 * @param array  $current  Current period data.
	 * @param array  $previous Previous period data.
	 * @param string $type     Metrics type.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function setup_periodic_values( $current, $previous, $type ) {
		$stats = [];

		// Total no. of items.
		$total_count = count( $current );

		// Loop through all items.
		for ( $i = 0; $i < $total_count; $i ++ ) {
			// Setup the period values.
			$current_date  = $this->get_period_value( $current[ $i ] );
			$previous_date = $this->get_period_value( $previous[ $i ] );

			// Current period data.
			$stats['current'][ $i ] = [
				$current_date,
				$this->format_periodic_value( $type, $current[ $i ][ $type ] ),
				$this->trend_value(
					$type,
					$this->format_periodic_value( $type, $current[ $i ][ $type ], 'string' ),
					$this->format_periodic_value( $type, $previous[ $i ][ $type ], 'string' )
				),
			];

			// Previous period data.
			$stats['previous'][ $i ] = [
				$previous_date,
				$this->format_periodic_value( $type, $previous[ $i ][ $type ] ),
			];
		}

		return $stats;
	}

	/**
	 * Format different values returned from API.
	 *
	 * @param string $type      Metric type.
	 * @param mixed  $value     Field value.
	 * @param string $time_type Optional (Only for avg time).
	 *
	 * @since 3.2.0
	 *
	 * @return mixed
	 */
	private function format_periodic_value( $type, $value, $time_type = 'int' ) {
		// Avg session duration should be handled differently.
		if ( 'avgSessionDuration' === $type ) {
			$value = $this->get_time( $value, $time_type );
		}

		return $value;
	}

	/**
	 * Format the summary data to required format.
	 *
	 * @param array $data Report data from Google.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function summary( $data ) {
		// Summary data should be single array.
		$summary = isset( $data['multiple']['summary'][0] ) ? $data['multiple']['summary'][0] : [];
		// Format summary data.
		$summary = [
			'sessions'         => [
				'value'    => isset( $summary['sessions'][0] ) ? $summary['sessions'][0] : 0,
				'previous' => isset( $summary['sessions'][1] ) ? $summary['sessions'][1] : 0,
				'type'     => 'number',
			],
			'users'            => [
				'value'    => isset( $summary['users'][0] ) ? $summary['users'][0] : 0,
				'previous' => isset( $summary['users'][1] ) ? $summary['users'][1] : 0,
				'type'     => 'number',
			],
			'pageviews'        => [
				'value'    => isset( $summary['pageviews'][0] ) ? $summary['pageviews'][0] : 0,
				'previous' => isset( $summary['pageviews'][1] ) ? $summary['pageviews'][1] : 0,
				'type'     => 'number',
			],
			'page_sessions'    => [
				'value'    => isset( $summary['pageviewsPerSession'][0] ) ? $summary['pageviewsPerSession'][0] : 0,
				'previous' => isset( $summary['pageviewsPerSession'][1] ) ? $summary['pageviewsPerSession'][1] : 0,
				'type'     => 'number',
			],
			'average_sessions' => [
				'value'    => isset( $summary['avgSessionDuration'][0] ) ? $this->get_time( $summary['avgSessionDuration'][0], 'string' ) : '00:00:00',
				'previous' => isset( $summary['avgSessionDuration'][1] ) ? $this->get_time( $summary['avgSessionDuration'][1], 'string' ) : '00:00:00',
				'type'     => 'timeofday',
			],
			'bounce_rates'     => [
				'value'    => isset( $summary['bounceRate'][0] ) ? $summary['bounceRate'][0] : 0,
				'previous' => isset( $summary['bounceRate'][1] ) ? $summary['bounceRate'][1] : 0,
				'type'     => 'number',
			],
			'user_sessions'    => [
				'new'       => isset( $summary['percentNewSessions'][0] ) ? (float) $summary['percentNewSessions'][0] : 0,
				'returning' => isset( $summary['percentNewSessions'][1] ) ? (float) ( 100 - $summary['percentNewSessions'][1] ) : 0,
			],
		];

		// Now set the trends.
		$summary['users']['trend']            = $this->trend_value( 'users', $summary['users']['value'], $summary['users']['previous'] );
		$summary['sessions']['trend']         = $this->trend_value( 'sessions', $summary['sessions']['value'], $summary['sessions']['previous'] );
		$summary['pageviews']['trend']        = $this->trend_value( 'pageviews', $summary['pageviews']['value'], $summary['pageviews']['previous'] );
		$summary['bounce_rates']['trend']     = $this->trend_value( 'bounceRate', $summary['bounce_rates']['value'], $summary['bounce_rates']['previous'] );
		$summary['page_sessions']['trend']    = $this->trend_value( 'pageviewsPerSession', $summary['page_sessions']['value'], $summary['page_sessions']['previous'] );
		$summary['average_sessions']['trend'] = $this->trend_value( 'avgSessionDuration', $summary['average_sessions']['value'], $summary['average_sessions']['previous'] );

		return $summary;
	}

	/**
	 * Get the proper period data value.
	 *
	 * Period dimension keys can be different based on
	 * the period selected by the user. Check all possible
	 * keys and return the value.
	 *
	 * @param array $data Data.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function get_period_value( $data ) {
		// Get the dimension type.
		if ( isset( $data['ga:yearMonth'] ) ) {
			$dimension = 'ga:yearMonth';
		} elseif ( isset( $data['ga:hour'] ) ) {
			$dimension = 'ga:hour';
		} else {
			$dimension = 'ga:date';
		}

		return isset( $data[ $dimension ] ) ? $data[ $dimension ] : '';
	}
}