<?php
/**
 * The data formatter class for Google stats.
 *
 * This class is little complex. Google Analytics Reporting API
 * response is really complex. We need to properly format the response.
 * Modify with caution.
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

use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Format
 *
 * @package Beehive\Core\Modules\Google_Analytics\Stats
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
		$final_stats = array();

		foreach ( $data as $type => $reports ) {
			// Format stats.
			$stats = array();

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
			case 'summary':
				$stats = $this->dashboard_summary( $final_stats );
				break;
			default:
				$stats = array();
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
	private function setup( $data, $dheaders, $mheaders, &$stats = array() ) {
		// Initialize the data array.
		$dimension_data = array();
		$metric_data    = array();

		$data_count = count( $data );

		// Loop through each item and get the data.
		for ( $i = 0; $i < $data_count; $i++ ) {
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
			for ( $j = 0; $j < $dheaders_count && $j < $dimensions_count; $j++ ) {
				$dimension_data[ $dheaders[ $j ] ] = $this->format_value( $dheaders[ $j ], $dimensions[ $j ] );
			}

			$metrics_count = count( $metrics );

			// Setup metrics.
			for ( $k = 0; $k < $metrics_count; $k++ ) {
				// Metric values.
				$values = $metrics[ $k ]->getValues();
				// No. of values.
				$value_count = count( $values );
				// Setup the values to array.
				for ( $l = 0; $l < $value_count; $l++ ) {
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

			// Year and week.
			case 'ga:yearWeek':
				// Get year from the string.
				$year = substr( $value, 0, 4 );
				// Get week number from the string.
				$week = substr( $value, 4, 2 );

				try {
					$dto = new \DateTime();
					// Setup date.
					$dto->setISODate( $year, $week );
					// Week start date.
					$start = $dto->format( 'j M' );
					$dto->modify( '+6 days' );
					// Week end date.
					$end = $dto->format( 'j M' );

					// Return formatted value.
					$value = $start . ' - ' . $end;
				} catch ( \Exception $e ) {
					// Return formatted value.
					$value = '-';
				}
				break;

			// Hour.
			case 'ga:dateHour':
				// Create a DateTime object from the Ym format.
				$date = \DateTime::createFromFormat( 'YmdH', $value );
				// Use the d/m/Y format.
				$value = $date->format( 'ga, D, M j, Y' );
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
		$value = 0;

		if ( ! empty( $previous ) ) {
			switch ( $type ) {
				case 'bounceRate':
				case 'pageviewsPerSession':
				case 'pageviews':
				case 'sessions':
				case 'users':
				case 'newUsers':
					$current  = (float) $current;
					$previous = (float) $previous;
					// When previous value is empty, trend is 100%.
					if ( empty( $previous ) && ! empty( $current ) ) {
						$trend = 100;
					} elseif ( ! empty( $previous ) && empty( $current ) ) {
						// When current value is 0 and previous value is not, trend is -100.
						$trend = -100;
					} else {
						if ( $current === $previous ) {
							$trend = 0;
						} else {
							$diff  = $current - $previous;
							$trend = ( $diff / $previous ) * 100;
						}
					}

					$value = round( $trend );
					break;
				// Time difference.
				case 'avgSessionDuration':
					// Convert to seconds.
					$current  = strtotime( $current ) - strtotime( '00:00:00' );
					$previous = strtotime( $previous ) - strtotime( '00:00:00' );

					// Now it's int, get the value.
					$value = $this->trend_value( 'sessions', $current, $previous );
					break;
				default:
					$value = 0;
			}
		}

		return $value;
	}

	/**
	 * Create a anchor tag link from the given values.
	 *
	 * We need to make sure the anchor link is generated
	 * only when the host name is valid.
	 *
	 * @param string $host      Host name.
	 * @param string $path      Page path.
	 * @param string $title     Page title.
	 * @param bool   $use_title Use title instead of link as anchor text.
	 *
	 * @since 3.2.0
	 * @since 3.2.4 Added $user_title param.
	 *
	 * @return string
	 */
	private function get_anchor( $host, $path, $title, $use_title = false ) {
		// Generate url from the data.
		$url = $this->get_link( $host, $path );

		// Only if url is generated.
		if ( empty( $url ) ) {
			return '';
		}

		$text = $use_title ? $title : $path;

		return "<a href=\"{$url}\" target=\"_blank\" title=\"$title ($url)\">{$text}</a>";
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
			return array(
				(int) gmdate( 'H', $value ),
				(int) gmdate( 'i', $value ),
				(int) gmdate( 's', $value ),
			);
		}

		return gmdate( 'H:i:s', $value );
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
		$stats = array();

		// Oi, we need data.
		if ( empty( $data ) ) {
			return $stats;
		}

		// Format summary data.
		if ( isset( $data['multiple']['summary'] ) ) {
			$stats['summary'] = $this->summary( $data );

			// Format countries list.
			if ( isset( $data['current']['countries'] ) ) {
				$country_count = 0;

				foreach ( $data['current']['countries'] as $country ) {
					// Top countries full details.
					$stats['countries'][] = array(
						$country['ga:country'],
						$country['ga:countryIsoCode'],
						$country['pageviews'],
					);

					// Add top country to summary.
					if ( 0 === $country_count ) {
						$stats['summary']['country'] = array(
							'value'     => $country['ga:country'],
							'code'      => $country['ga:countryIsoCode'],
							'pageviews' => $country['pageviews'],
						);
					}

					$country_count++;
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['mediums'] ) ) {
				$medium_count = 0;

				foreach ( $data['current']['mediums'] as $medium ) {
					// Medium data.
					$stats['mediums'][] = array( $medium['ga:channelGrouping'], $medium['sessions'] );

					// Add top medium to summary.
					if ( 0 === $medium_count ) {
						$stats['summary']['medium'] = array(
							'value'    => $medium['ga:channelGrouping'],
							'sessions' => $medium['sessions'],
						);
					}

					$medium_count++;
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['search_engines'] ) ) {
				$search_engine_count = 0;

				foreach ( $data['current']['search_engines'] as $search_engine ) {
					// Search engine data.
					$stats['search_engines'][] = array(
						ucfirst( $search_engine['ga:source'] ),
						$search_engine['sessions'],
					);

					// Add top search engine to summary.
					if ( 0 === $search_engine_count ) {
						$stats['summary']['search_engine'] = array(
							'value'    => ucfirst( $search_engine['ga:source'] ),
							'sessions' => $search_engine['sessions'],
						);
					}

					$search_engine_count++;
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['social_networks'] ) ) {
				$social_network_count = 0;

				foreach ( $data['current']['social_networks'] as $social_network ) {
					// Social network data.
					$stats['social_networks'][] = array( $social_network['ga:socialNetwork'], $social_network['sessions'] );

					// Add top social network to summary.
					if ( 0 === $social_network_count ) {
						$stats['summary']['social_network'] = array(
							'value'    => $social_network['ga:socialNetwork'],
							'sessions' => $social_network['sessions'],
						);
					}

					$social_network_count++;
				}
			}

			// Format pages list.
			if ( isset( $data['multiple']['pages'] ) ) {
				$page_count = 0;

				foreach ( $data['multiple']['pages'] as $page ) {
					// Top countries full details.
					$stats['pages'][] = array(
						$this->get_anchor( $page['ga:hostname'], $page['ga:pagePath'], $page['ga:pageTitle'] ),
						$this->get_time( $page['avgSessionDuration'][0], 'string' ),
						$page['pageviews'][0],
						$this->trend_value(
							'pageviews',
							$page['pageviews'][0], // Current period value.
							$page['pageviews'][1] // Previous period value.
						),
					);

					// Add top page to summary.
					if ( 0 === $page_count ) {
						$stats['summary']['page'] = array(
							'value'     => $page['ga:pageTitle'],
							'html'      => $this->get_anchor( $page['ga:hostname'], $page['ga:pagePath'], $page['ga:pageTitle'], true ),
							'pageviews' => $page['pageviews'][0],
						);
					}

					$page_count++;
				}
			}

			// Format sessions list.
			if ( isset( $data['current']['sessions'] ) ) {
				foreach ( $data['current']['sessions'] as $session ) {
					$date = $this->get_period_value( $session );
					// Top countries full details.
					$stats['sessions'][] = array( $date, $session['sessions'] );
				}
			}

			// Format users list.
			if ( isset( $data['current']['users'] ) ) {
				foreach ( $data['current']['users'] as $user ) {
					$date = $this->get_period_value( $user );
					// Top countries full details.
					$stats['users'][] = array( $date, $user['users'] );
				}
			}

			// Format page views list.
			if ( isset( $data['current']['pageviews'] ) ) {
				foreach ( $data['current']['pageviews'] as $pageview ) {
					$date = $this->get_period_value( $pageview );
					// Top countries full details.
					$stats['pageviews'][] = array( $date, $pageview['pageviews'] );
				}
			}

			// Format pages per sessions list.
			if ( isset( $data['current']['page_sessions'] ) ) {
				foreach ( $data['current']['page_sessions'] as $session ) {
					$date = $this->get_period_value( $session );
					// Top countries full details.
					$stats['page_sessions'][] = array( $date, $session['pageviewsPerSession'] );
				}
			}

			// Format average sessions list.
			if ( isset( $data['current']['average_sessions'] ) ) {
				foreach ( $data['current']['average_sessions'] as $session ) {
					$date = $this->get_period_value( $session );
					// Top countries full details.
					$stats['average_sessions'][] = array( $date, $session['avgSessionDuration'] );
				}
			}

			// Format bounce rates list.
			if ( isset( $data['current']['bounce_rates'] ) ) {
				foreach ( $data['current']['bounce_rates'] as $rate ) {
					$date = $this->get_period_value( $rate );
					// Top countries full details.
					$stats['bounce_rates'][] = array( $date, $rate['bounceRate'] );
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
		$stats = array();

		// Format pages list.
		if ( isset( $data['current']['pages'] ) ) {
			foreach ( $data['current']['pages'] as $page ) {
				// Top pages list.
				$stats[] = $this->get_link( $page['ga:hostname'], $page['ga:pagePath'] );
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
			return array();
		} else {
			// Format summary data.
			return $this->summary( $data );
		}
	}

	/**
	 * Format the data for the dashboard summary page.
	 *
	 * Format the data array into the format of dashboard widget.
	 *
	 * @param array $data Stats data from Google.
	 *
	 * @since 3.2.4
	 *
	 * @return array $stats
	 */
	private function dashboard_summary( $data ) {
		$stats = array();

		// Oi, we need data.
		if ( empty( $data ) ) {
			return $stats;
		}

		// Format summary data.
		if ( isset( $data['multiple']['summary'] ) ) {
			$stats['summary'] = $this->summary( $data );

			// Format sources and mediums list.
			if ( isset( $data['current']['mediums'][0] ) ) {
				// Medium data.
				$stats['medium'] = array(
					'name'     => $data['current']['mediums'][0]['ga:channelGrouping'],
					'sessions' => $data['current']['mediums'][0]['sessions'],
				);
			} else {
				$stats['medium'] = array();
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['search_engines'][0] ) ) {
				// Search engine data.
				$stats['search_engine'] = array(
					'name'     => ucfirst( $data['current']['search_engines'][0]['ga:source'] ),
					'sessions' => $data['current']['search_engines'][0]['sessions'],
				);
			} else {
				$stats['search_engine'] = array();
			}

			// Format pages list.
			if ( isset( $data['current']['pages'][0] ) ) {
				// Top page details.
				$stats['page'] = array(
					'anchor'    => $this->get_anchor(
						$data['current']['pages'][0]['ga:hostname'],
						$data['current']['pages'][0]['ga:pagePath'],
						$data['current']['pages'][0]['ga:pageTitle'],
						true
					),
					'title'     => $data['current']['pages'][0]['ga:pageTitle'],
					'pageviews' => $data['current']['pages'][0]['pageviews'],
				);
			} else {
				$stats['page'] = array();
			}

			// Format countries list.
			if ( isset( $data['current']['countries'][0] ) ) {
				// Top country details.
				$stats['country'] = array(
					$data['current']['countries'][0]['ga:country'],
					$data['current']['countries'][0]['ga:countryIsoCode'],
					$data['current']['countries'][0]['pageviews'],
				);
			} else {
				$stats['country'] = array();
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
	private function stats_page( $data ) {
		$stats = array();

		// Return early when don't get the data we deserve.
		if ( empty( $data ) ) {
			return $stats;
		}

		// Format summary data.
		if ( isset( $data['multiple']['summary'] ) ) {
			$stats['summary'] = $this->summary( $data );

			// Format countries list.
			if ( isset( $data['current']['countries'] ) ) {
				$country_count = 0;

				foreach ( $data['current']['countries'] as $country ) {
					// Top countries full details.
					$stats['countries'][] = array(
						$country['ga:country'],
						$country['ga:countryIsoCode'],
						$country['pageviews'],
					);

					// Add top country to summary.
					if ( 0 === $country_count ) {
						$stats['summary']['country'] = array(
							'value'     => $country['ga:country'],
							'code'      => $country['ga:countryIsoCode'],
							'pageviews' => $country['pageviews'],
						);
					}

					$country_count++;
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['mediums'] ) ) {
				$medium_count = 0;

				foreach ( $data['current']['mediums'] as $medium ) {
					// Medium data.
					$stats['mediums'][] = array( $medium['ga:channelGrouping'], $medium['sessions'] );

					// Add top medium to summary.
					if ( 0 === $medium_count ) {
						$stats['summary']['medium'] = array(
							'value'    => $medium['ga:channelGrouping'],
							'sessions' => $medium['sessions'],
						);
					}

					$medium_count++;
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['search_engines'] ) ) {
				$search_engine_count = 0;

				foreach ( $data['current']['search_engines'] as $search_engine ) {
					// Search engine data.
					$stats['search_engines'][] = array( $search_engine['ga:source'], $search_engine['sessions'] );

					// Add top search engine to summary.
					if ( 0 === $search_engine_count ) {
						$stats['summary']['search_engine'] = array(
							'value'    => $search_engine['ga:source'],
							'sessions' => $search_engine['sessions'],
						);
					}

					$search_engine_count++;
				}
			}

			// Format sources and mediums list.
			if ( isset( $data['current']['social_networks'] ) ) {
				$social_network_count = 0;

				foreach ( $data['current']['social_networks'] as $social_network ) {
					// Social network data.
					$stats['social_networks'][] = array( $social_network['ga:socialNetwork'], $social_network['sessions'] );

					// Add top social network to summary.
					if ( 0 === $social_network_count ) {
						$stats['summary']['social_network'] = array(
							'value'    => $social_network['ga:socialNetwork'],
							'sessions' => $social_network['sessions'],
						);
					}

					$social_network_count++;
				}
			}

			// Format pages list.
			if ( isset( $data['multiple']['pages'] ) ) {
				$page_count = 0;

				foreach ( $data['multiple']['pages'] as $page ) {
					// Top countries full details.
					$stats['pages'][] = array(
						$this->get_anchor( $page['ga:hostname'], $page['ga:pagePath'], $page['ga:pageTitle'] ),
						$this->get_time( $page['avgSessionDuration'][0], 'string' ),
						$page['pageviews'][0],
						$this->trend_value(
							'pageviews',
							$page['pageviews'][0], // Current period value.
							$page['pageviews'][1] // Previous period value.
						),
					);

					// Add top page to summary.
					if ( 0 === $page_count ) {
						$stats['summary']['page'] = array(
							'value'     => $page['ga:pageTitle'],
							'title'     => $this->get_anchor( $page['ga:hostname'], $page['ga:pagePath'], $page['ga:pageTitle'], true ),
							'pageviews' => $page['pageviews'][0],
						);
					}

					$page_count++;
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
		$stats = array();

		// Total no. of items.
		$total_count = count( $current );

		// Loop through all items.
		for ( $i = 0; $i < $total_count; $i++ ) {
			// Setup the period values.
			$current_date = $this->get_period_value( $current[ $i ] );

			if ( isset( $previous[ $i ] ) ) {
				$previous_data = $previous[ $i ][ $type ];
				$previous_date = $this->get_period_value( $previous[ $i ] );
			} else {
				$previous_data = '';
				$previous_date = '';
			}

			// Current period data.
			$stats['current'][ $i ] = array(
				$current_date,
				$this->format_periodic_value( $type, $current[ $i ][ $type ], 'string' ),
				$this->trend_value(
					$type,
					$this->format_periodic_value( $type, $current[ $i ][ $type ], 'string' ),
					$this->format_periodic_value( $type, $previous_data, 'string' )
				),
			);

			// Previous period data.
			$stats['previous'][ $i ] = array(
				$previous_date,
				$this->format_periodic_value( $type, $previous_data ),
			);
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
		/**
		 * Avg session duration should be handled differently.
		 * if ( 'avgSessionDuration' === $type ) {
		 * $value = $this->get_time( $value, $time_type );
		 * }
		 */

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
		$summary = isset( $data['multiple']['summary'][0] ) ? $data['multiple']['summary'][0] : array();
		// Format summary data.
		$summary = array(
			'sessions'         => array(
				'value'    => isset( $summary['sessions'][0] ) ? $summary['sessions'][0] : 0,
				'previous' => isset( $summary['sessions'][1] ) ? $summary['sessions'][1] : 0,
			),
			'users'            => array(
				'value'    => isset( $summary['users'][0] ) ? $summary['users'][0] : 0,
				'previous' => isset( $summary['users'][1] ) ? $summary['users'][1] : 0,
			),
			'pageviews'        => array(
				'value'    => isset( $summary['pageviews'][0] ) ? $summary['pageviews'][0] : 0,
				'previous' => isset( $summary['pageviews'][1] ) ? $summary['pageviews'][1] : 0,
			),
			'page_sessions'    => array(
				'value'    => isset( $summary['pageviewsPerSession'][0] ) ? $summary['pageviewsPerSession'][0] : 0,
				'previous' => isset( $summary['pageviewsPerSession'][1] ) ? $summary['pageviewsPerSession'][1] : 0,
			),
			'average_sessions' => array(
				'value'    => isset( $summary['avgSessionDuration'][0] ) ? $this->get_time( $summary['avgSessionDuration'][0], 'string' ) : '00:00:00',
				'previous' => isset( $summary['avgSessionDuration'][1] ) ? $this->get_time( $summary['avgSessionDuration'][1], 'string' ) : '00:00:00',
			),
			'bounce_rates'     => array(
				'value'    => isset( $summary['bounceRate'][0] ) ? $summary['bounceRate'][0] : 0,
				'previous' => isset( $summary['bounceRate'][1] ) ? $summary['bounceRate'][1] : 0,
			),
			'user_sessions'    => array(
				'new'       => isset( $summary['percentNewSessions'][0] ) ? (float) $summary['percentNewSessions'][0] : 0,
				'returning' => isset( $summary['percentNewSessions'][1] ) ? (float) ( 100 - $summary['percentNewSessions'][1] ) : 0,
			),
			'new_users'        => array(
				'value'    => isset( $summary['newUsers'][0] ) ? $summary['newUsers'][0] : 0,
				'previous' => isset( $summary['newUsers'][1] ) ? $summary['newUsers'][1] : 0,
			),
		);

		// Now set the trends.
		$summary['users']['trend']            = $this->trend_value( 'users', $summary['users']['value'], $summary['users']['previous'] );
		$summary['sessions']['trend']         = $this->trend_value( 'sessions', $summary['sessions']['value'], $summary['sessions']['previous'] );
		$summary['pageviews']['trend']        = $this->trend_value( 'pageviews', $summary['pageviews']['value'], $summary['pageviews']['previous'] );
		$summary['new_users']['trend']        = $this->trend_value( 'newUsers', $summary['new_users']['value'], $summary['new_users']['previous'] );
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
		} elseif ( isset( $data['ga:yearWeek'] ) ) {
			$dimension = 'ga:yearWeek';
		} elseif ( isset( $data['ga:dateHour'] ) ) {
			$dimension = 'ga:dateHour';
		} else {
			$dimension = 'ga:date';
		}

		return isset( $data[ $dimension ] ) ? $data[ $dimension ] : '';
	}
}