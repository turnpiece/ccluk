<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class AverageDonation extends Endpoint {

	protected $payments;

	public function __construct() {
		$this->endpoint = 'average-donation';
	}

	public function get_report( $request ) {

		// Check if a cached version exists
		$cached_report = $this->get_cached_report( $request );
		if ( $cached_report !== null ) {
			// Bail and return the cached version
			return new \WP_REST_Response(
				[
					'data' => $cached_report,
				]
			);
		}

		$start = date_create( $request['start'] );
		$end   = date_create( $request['end'] );
		$diff  = date_diff( $start, $end );

		$data = [];

		switch ( true ) {
			case ( $diff->days > 12 ):
				$interval = round( $diff->days / 12 );
				$data     = $this->get_data( $start, $end, 'P' . $interval . 'D' );
				break;
			case ( $diff->days > 7 ):
				$data = $this->get_data( $start, $end, 'PT12H' );
				break;
			case ( $diff->days > 2 ):
				$data = $this->get_data( $start, $end, 'PT3H' );
				break;
			case ( $diff->days >= 0 ):
				$data = $this->get_data( $start, $end, 'PT1H' );
				break;
		}

		// Cache the report data
		$result = $this->cache_report( $request, $data );
		$status = $this->get_give_status();

		return new \WP_REST_Response(
			[
				'data'   => $data,
				'status' => $status,
			]
		);
	}

	public function get_data( $start, $end, $intervalStr ) {

		$this->payments = $this->get_payments( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );

		$income   = [];
		$tooltips = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$averageForPeriod = $this->get_average_donation( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );

			if ( $intervalStr == 'PT1H' ) {
				$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
			} else {
				$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$income[] = [
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
				'y' => $averageForPeriod,
			];

			$tooltips[] = [
				'title'  => give_currency_filter( give_format_amount( $averageForPeriod ), [ 'decode_currency' => true ] ),
				'body'   => __( 'Avg Donation', 'give' ),
				'footer' => $periodLabel,
			];

			// Add interval to set up next period
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );
		}

		$averageIncomeForPeriod = $this->get_average_donation( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );
		$trend                  = $this->get_trend( $start, $end, $income );

		$diff = date_diff( $start, $end );
		$info = $diff->days > 1 ? __( 'VS previous', 'give' ) . ' ' . $diff->days . ' ' . __( 'days', 'give' ) : __( 'VS previous day', 'give' );

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'      => $income,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'info'      => $info,
					'highlight' => give_currency_filter( give_format_amount( $averageIncomeForPeriod ), [ 'decode_currency' => true ] ),
				],
			],
		];

		return $data;

	}

	public function get_trend( $start, $end, $income ) {

		$interval = $start->diff( $end );

		$prevStart = clone $start;
		$prevStart = date_sub( $prevStart, $interval );

		$prevEnd = clone $start;

		$prevAverage    = $this->get_prev_average_donation( $prevStart->format( 'Y-m-d H:i:s' ), $prevEnd->format( 'Y-m-d H:i:s' ) );
		$currentAverage = $this->get_average_donation( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

		// Set default trend to 0
		$trend = 0;

		// Check that prev value and current value are > 0 (can't divide by 0)
		if ( $prevAverage > 0 && $currentAverage > 0 ) {

			// Check if it is a percent decreate, or increase
			if ( $prevAverage > $currentAverage ) {
				// Calculate a percent decrease
				$trend = round( ( ( ( $prevAverage - $currentAverage ) / $prevAverage ) * 100 ), 1 ) * -1;
			} elseif ( $currentAverage > $prevAverage ) {
				// Calculate a percent increase
				$trend = round( ( ( ( $currentAverage - $prevAverage ) / $prevAverage ) * 100 ), 1 );
			}
		}

		return $trend;
	}

	public function get_average_donation( $startStr, $endStr ) {

		$earnings     = 0;
		$paymentCount = 0;

		foreach ( $this->payments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				if ( $payment->status == 'publish' || $payment->status == 'give_subscription' ) {
					$earnings     += $payment->total;
					$paymentCount += 1;
				}
			}
		}

		$average = $paymentCount > 0 ? $earnings / $paymentCount : 0;

		return $average;
	}

	public function get_prev_average_donation( $startStr, $endStr ) {

		$stats = new \Give_Payment_Stats();

		$earnings = $stats->get_earnings( 0, $startStr, $endStr );
		$sales    = $stats->get_sales( 0, $startStr, $endStr );

		$average = $sales > 0 ? $earnings / $sales : 0;

		return $average;
	}
}
