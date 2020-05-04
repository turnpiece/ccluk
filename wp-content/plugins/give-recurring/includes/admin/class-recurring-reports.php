<?php
/**
 * Class Give_Recurring_Reports
 *
 * @since 1.0
 *
 */
if ( ! class_exists( 'Give_Recurring_Reports' ) ) :
	/**
	 * Give_Recurring_Reports.
	 *
	 * @since 1.8
	 */
	class Give_Recurring_Reports extends Give_Settings_Page {

		/**
		 * Setting page id.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @since 1.8
		 * @var   string
		 */
		protected $label = '';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'subscriptions';
			$this->label = esc_html__( 'Renewal Donations', 'give-recurring' );

			add_filter( 'give-reports_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( "give-reports_settings_{$this->id}_page", array( $this, 'output' ) );
			add_action( 'give_admin_field_report_subscriptions', array(
				$this,
				'display_subscriptions_report',
			), 10, 2 );

			// Do not use main form for this tab.
			if ( give_get_current_setting_tab() === $this->id ) {
				add_action( 'give-reports_open_form', '__return_empty_string' );
				add_action( 'give-reports_close_form', '__return_empty_string' );
			}
		}

		/**
		 * Add this page to settings.
		 *
		 * @since  1.8
		 *
		 * @param  array $pages Lst of pages.
		 *
		 * @return array
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			// Hide save button.
			$GLOBALS['give_hide_save_button'] = true;

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters(
				'give_get_settings_' . $this->id,
				array(
					array(
						'id'         => 'give_reports_subscriptions',
						'type'       => 'title',
						'table_html' => false,
					),
					array(
						'id'   => 'subscriptions',
						'name' => esc_html__( 'Gateways', 'give-recurring' ),
						'type' => 'report_subscriptions',
					),
					array(
						'id'         => 'give_reports_subscriptions',
						'type'       => 'sectionend',
						'table_html' => false,
					),
				)
			);

			// Output.
			return $settings;
		}

		/**
		 * Output the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public function output() {
			$settings = $this->get_settings();

			Give_Admin_Settings::output_fields( $settings, 'give_settings' );
		}


		/**
		 * Get Subscription by Date
		 *
		 * Helper function for reports
		 *
		 * @since      1.0
		 *
		 * @param null $day
		 * @param null $month
		 * @param null $year
		 * @param null $hour
		 *
		 * @return array
		 */
		public function get_subscriptions_by_date( $day = null, $month = null, $year = null, $hour = null ) {

			$args = apply_filters( 'give_get_subscriptions_by_date', array(
				'nopaging'    => true,
				'post_type'   => 'give_payment',
				'post_status' => array( 'give_subscription' ),
				'year'        => $year,
				'monthnum'    => $month,
				'fields'      => 'ids',
			), $day, $month, $year );

			if ( ! empty( $day ) ) {
				$args['day'] = $day;
			}

			if ( ! empty( $hour ) ) {
				$args['hour'] = $hour;
			}

			$subscriptions = get_posts( $args );

			$return           = array();
			$return['income'] = 0;
			$return['count']  = count( $subscriptions );
			if ( $subscriptions ) {
				foreach ( $subscriptions as $renewal ) {
					$return['income'] += give_donation_amount( $renewal );
				}
			}

			return $return;
		}

		/**
		 * Show subscription donation earnings
		 *
		 * @access      public
		 * @since       1.0
		 *
		 * @param array  $field
		 * @param string $field_value
		 *
		 * @return      void
		 */
		public function display_subscriptions_report( $field, $field_value ) {

			if ( ! current_user_can( 'view_give_reports' ) ) {
				wp_die( __( 'You do not have permission to view this data.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array( 'response' => 401 ) );
			}

			// Retrieve the queried dates
			$dates = give_get_report_dates();

			// Determine graph options
			switch ( $dates['range'] ) :
				case 'today' :
				case 'yesterday' :
					$day_by_day = true;
					break;
				case 'last_year' :
				case 'this_year' :
				case 'last_quarter' :
				case 'this_quarter' :
					$day_by_day = false;
					break;
				case 'other' :
					if ( $dates['m_end'] - $dates['m_start'] >= 2 || $dates['year_end'] > $dates['year'] && ( $dates['m_start'] != '12' && $dates['m_end'] != '1' ) ) {
						$day_by_day = false;
					} else {
						$day_by_day = true;
					}
					break;
				default:
					$day_by_day = true;
					break;
			endswitch;

			$earnings_totals      = 0.00; // Total earnings for time period shown
			$subscriptions_totals = 0;    // Total sales for time period shown
			$earnings_data        = array();
			$subscription_count   = array();

			if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
				// Hour by hour
				$hour  = 1;
				$month = $dates['m_start'];
				while ( $hour <= 23 ) :

					$subscriptions = $this->get_subscriptions_by_date( $dates['day'], $month, $dates['year'], $hour );

					$earnings_totals += $subscriptions['income'];
					$subscriptions_totals += $subscriptions['count'];

					$date                 = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;
					$subscription_count[] = array( $date, $subscriptions['count'] );
					$earnings_data[]      = array( $date, $subscriptions['income'] );

					$hour ++;
				endwhile;

			} elseif ( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {

				// Day by day
				$day     = $dates['day'];
				$day_end = $dates['day_end'];
				$month   = $dates['m_start'];
				while ( $day <= $day_end ) :

					$subscriptions = $this->get_subscriptions_by_date( $day, $month, $dates['year'] );

					$earnings_totals += $subscriptions['income'];
					$subscriptions_totals += $subscriptions['count'];

					$date                 = mktime( 0, 0, 0, $month, $day, $dates['year'] ) * 1000;
					$subscription_count[] = array( $date, $subscriptions['count'] );
					$earnings_data[]      = array( $date, $subscriptions['income'] );
					$day ++;
				endwhile;

			} else {

				$y = $dates['year'];

				while ( $y <= $dates['year_end'] ) :

					$last_year = false;

					if ( $dates['year'] == $dates['year_end'] ) {
						$month_start = $dates['m_start'];
						$month_end   = $dates['m_end'];
						$last_year   = true;
					} elseif ( $y == $dates['year'] ) {
						$month_start = $dates['m_start'];
						$month_end   = 12;
					} elseif ( $y == $dates['year_end'] ) {
						$month_start = 1;
						$month_end   = $dates['m_end'];
					} else {
						$month_start = 1;
						$month_end   = 12;
					}

					$i = $month_start;
					while ( $i <= $month_end ) :

						if ( $day_by_day ) :

							if ( $i == $month_end ) {

								$num_of_days = $dates['day_end'];

							} else {

								$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

							}

							$d = $dates['day'];

							while ( $d <= $num_of_days ) :

								$subscriptions = $this->get_subscriptions_by_date( $d, $i, $y );

								$earnings_totals += $subscriptions['income'];
								$subscriptions_totals += $subscriptions['count'];

								$date                 = mktime( 0, 0, 0, $i, $d, $y ) * 1000;
								$subscription_count[] = array( $date, $subscriptions['count'] );
								$earnings_data[]      = array( $date, $subscriptions['income'] );
								$d ++;

							endwhile;

						else :

							$subscriptions = $this->get_subscriptions_by_date( null, $i, $y );

							$earnings_totals += $subscriptions['income'];
							$subscriptions_totals += $subscriptions['count'];

							if ( $i == $month_end && $last_year ) {

								$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

							} else {

								$num_of_days = 1;

							}

							$date                 = mktime( 0, 0, 0, $i, $num_of_days, $y ) * 1000;
							$subscription_count[] = array( $date, $subscriptions['count'] );
							$earnings_data[]      = array( $date, $subscriptions['income'] );

						endif;

						$i ++;

					endwhile;

					$y ++;
				endwhile;

			}

			$data = array(
				__( 'Income', 'give-recurring' )   => $earnings_data,
				__( 'Renewals', 'give-recurring' ) => $subscription_count,
			);

			ob_start();

			?>

			<div class="tablenav top reports-table-nav">
				<h2 class="alignleft reports-earnings-title screen-reader-text">
					<span><?php _e( 'Renewal Donations Report', 'give-recurring' ); ?></span>
				</h2>
			</div>

			<div id="give-dashboard-widgets-wrap" style="padding-top: 0;">
				<div class="metabox-holder" style="padding-top: 0;">
					<div class="postbox">

						<div class="inside">
							<?php
							do_action( 'give_subscription_reports_graph_before' );

							give_reports_graph_controls();
							$graph = new Give_Graph( $data, array( 'dataType' => array( 'amount', 'count' ) ) );
							$graph->set( 'x_mode', 'time' );
							$graph->set( 'multiple_y_axes', true );
							$graph->display();

							do_action( 'give_subscription_reports_graph_after' ); ?>
						</div>

					</div>
				</div>
				<table class="widefat reports-table alignleft" style="max-width:450px">
					<tbody>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Total earnings for period shown: ', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo give_currency_filter( give_format_amount( $earnings_totals ) ); ?></td>
						</tr>
						<tr class="alternate">
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Total renewal donations for period shown: ', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo give_format_amount( $subscriptions_totals, false );; ?></td>
						</tr>
						<?php do_action( 'give_subscription_reports_graph_additional_stats' ); ?>
					</tbody>
				</table>
			</div>

			<?php
			// get output buffer contents and end our own buffer
			$output = ob_get_contents();
			ob_end_clean();

			echo $output;

		}
	}

endif;

return new Give_Recurring_Reports();
