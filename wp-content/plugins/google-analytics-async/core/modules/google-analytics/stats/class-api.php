<?php
/**
 * The Google API setup class.
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
use Beehive\Google_Service_Exception;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\Google_API;
use Beehive\Google_Service_AnalyticsReporting;
use Beehive\Core\Modules\Google_Analytics\Helper;
use Beehive\Google_Service_AnalyticsReporting_GetReportsRequest;

/**
 * Class API
 *
 * @package Beehive\Core\Modules\Google_Analytics\Stats
 */
class API extends Google_API {

	/**
	 * Google Analytics class instance.
	 *
	 * @var Google_Service_AnalyticsReporting
	 *
	 * @since 3.2.0
	 */
	protected $analytics;

	/**
	 * Get the mulltiple reports from Google Reporting API.
	 *
	 * Multiple Date range requests should be made as different
	 * request. So this method will handle that.
	 *
	 * @param array           $request_types Report request array.
	 * @param bool            $network       Network flag.
	 * @param \Exception|bool $exception     Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function process_request_types( $request_types = array(), $network = false, &$exception = false ) {
		// Decide login source.
		$network = Helper::instance()->login_source( $network ) === 'network';

		// Setup login.
		$this->setup( $network );

		$full_reports = array();

		// Process each request types (different date ranges).
		foreach ( $request_types as $type => $requests ) {
			$full_reports[ $type ] = $this->process_requests( $requests, $exception );
		}

		/**
		 * Filter the Google reports API data.
		 *
		 * @param array $data API data.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_stats_api_full_data', $full_reports );
	}

	/**
	 * Get the reports data from Google Reporting API.
	 *
	 * Use this method only if the requested data is not available
	 * in cache and transient. This method required API request so
	 * it may slow down the page load time and frequent requests may
	 * hit the API request limits.
	 *
	 * @param array           $requests  Request objects.
	 * @param \Exception|bool $exception Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function process_requests( $requests = array(), &$exception = false ) {
		$full_reports = array();

		// Make sure we don't break anything.
		try {
			$request_count = count( $requests );

			// Maximum 5 requests can be processed in a time.
			for ( $i = 0; $i < $request_count; $i += 5 ) {
				// Split requests into batches.
				$request_batch = array_slice( $requests, $i, 5 );

				// Create Google_Service_AnalyticsReporting_GetReportsRequest object.
				$body = new Google_Service_AnalyticsReporting_GetReportsRequest();

				// Set batch requests.
				$body->setReportRequests( $request_batch );

				// Get reports data.
				$reports = $this->analytics->reports->batchGet( $body )->getReports();

				if ( ! empty( $reports ) ) {
					$full_reports = array_merge( $full_reports, $reports );
				}
			}
		} catch ( Google_Service_Exception $e ) {
			// Oh well, failed.
			$full_reports = array();

			// Perform error actions.
			$this->error( $e );

			$exception = $e;
		} catch ( Exception $e ) {
			// Oh well, failed generally.
			$full_reports = array();

			// Perform error actions.
			$this->error( $e );

			$exception = $e;
		}

		/**
		 * Filter the Google reports API data.
		 *
		 * @param array $data API data.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_google_stats_api_data', $full_reports );
	}

	/**
	 * Setup all required things for the API request.
	 *
	 * API request require a valid analytics class instance.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 */
	private function setup( $network = false ) {
		// Set auth data.
		Google_Auth\Helper::instance()->setup_auth( $network );

		// New analytics instance.
		$this->analytics = new Google_Service_AnalyticsReporting(
			Google_Auth\Auth::instance()->client()
		);
	}
}