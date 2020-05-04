<?php
/**
 * Subscriptions Renewal Export Class
 *
 * This class handles earnings export
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Subscriptions_Renewals_Export' ) ) {


	/**
	 * Give_Subscriptions_Renewals_Export Class
	 *
	 * @since 1.7
	 */
	class Give_Subscriptions_Renewals_Export extends Give_Batch_Export {

		/**
		 * Our export type. Used for export-type specific filters/actions
		 *
		 * @var string
		 * @since 1.7
		 */
		public $export_type = 'susbcriptions_renewal';

		/**
		 * The start date.
		 *
		 * @var string
		 * @since 1.7
		 */
		public $start_date;

		/**
		 * The start date.
		 *
		 * @var string
		 * @since 1.7
		 */
		public $end_date;

		/**
		 * Subscriptions Count.
		 *
		 * @var int
		 * @since 1.8.3
		 */
		private $count = 0;

		/**
		 * Items per page.
		 *
		 * @var int
		 * @since 1.8.3
		 */
		private $items_per_page = 20;

		/**
		 * Export query id.
		 *
		 * @since 1.8.3
		 * @var string
		 */
		private $query_id = '';

		/**
		 * Give_Batch_Export constructor.
		 *
		 * @since 1.8.3
		 *
		 * @param int $_step Steps.
		 */
		public function __construct( $_step = 1 ) {

			parent::__construct( $_step );

			// Filter to change the filename.
			add_filter( 'give_export_filename', array( $this, 'export_filename' ), 10, 2 );
		}

		/**
		 * Function to change the filename
		 *
		 * @since 1.8.3
		 *
		 * @param string $filename File name.
		 * @param string $export_type export type.
		 *
		 * @return string $filename file name.
		 */
		public function export_filename( $filename, $export_type ) {

			if ( $this->export_type !== $export_type ) {
				return $filename;
			}

			$forms = empty( $_GET['forms'] ) ? 0 : absint( $_GET['forms'] );

			if ( $forms ) {
				$slug     = get_post_field( 'post_name', get_post( $forms ) );
				$filename = 'give-export-upcoming-renewals-' . $slug . '-' . date( 'm-d-Y' );
			} else {
				$filename = 'give-export-upcoming-renewals-all-forms-' . date( 'm-d-Y' );
			}

			return $filename;
		}

		/**
		 * Set the properties specific to the subscriptions export.
		 *
		 * @since 1.8.3
		 *
		 * @param array $post_data The Form Data passed into the batch processing.
		 */
		public function set_properties( $post_data ) {

			$this->form       = $post_data;
			$this->start_date = strtotime( $post_data['give_renewal_subscriptions_start_date'] );
			$this->end_date   = strtotime( $post_data['give_renewal_subscriptions_end_date'] );

			// Setup donor ids cache.
			if ( ! empty( $this->form ) ) {
				// Cache donor ids to output unique list of donor.
				$this->query_id = $post_data['give_export_option']['query_id'];
			}
		}

		/**
		 * Set the CSV columns
		 *
		 * @since  1.7
		 *
		 * @return array $cols All the columns
		 */
		public function csv_cols() {

			// These are the column titles for the CSV file.
			$cols = array(
				'subscription_id' => __( 'Subscription ID', 'give-recurring' ),
				'donor_name'      => __( 'Donor Name', 'give-recurring' ),
				'donor_email'     => __( 'Donor Email', 'give-recurring' ),
				'renewal_date'    => __( 'Renewal Date', 'give-recurring' ),
				'renewal_amount'  => __( 'Renewal Amount', 'give-recurring' ),
			);

			return $cols;
		}

		/**
		 * Get the Export Data
		 *
		 * @since  1.7
		 *
		 * @return array $data The data for the CSV file
		 */
		public function get_data() {

			$data    = array();
			$offset  = ( $this->step - 1 ) * $this->items_per_page;
			$form_id = isset( $this->form['subscription_renewal_per_form'] ) ? $this->form['subscription_renewal_per_form'] : 0;

			/**
			 * Note: If the date parameters are not set, then the
			 * default start date will be the current date and the
			 * end date will be the date +1month of the current month.
			 *
			 * Example:
			 * Start date: 2018-05-03
			 * End date: 2018-06-03
			 */

			// The Give_Subscriptions_DB object.
			$sub_db = new Give_Subscriptions_DB();

			// Arguments required to get the subscriptions.
			$sub_db_args  = array(
				'number'  => $this->items_per_page,
				'status'  => 'active',
				'orderby' => 'id',
				'order'   => 'ASC',
				'offset'  => $offset,
				'form_id' => $form_id,
			);

			/**
			 * Get the 'active' subscription in the order
			 * in which will be renewed the soonest.
			 */
			$subscriptions = $sub_db->get_subscriptions( $sub_db_args );

			// Provide valid count for total number of subscriptions.
			$sub_db_args['number'] = -1;
			$this->count = $sub_db->count( $sub_db_args );

			/**
			 * Looping through all the subscriptions as per the
			 * parameters set above. This loop will populate
			 * the $data array which will be used to fill the
			 * rows in the CSV file.
			 */
			foreach ( $subscriptions as $subscription ) {
				$upcoming_renewal_timestamp = strtotime( $subscription->get_renewal_date( false ) );

				if (
					$this->start_date < $upcoming_renewal_timestamp
					&& $this->end_date > $upcoming_renewal_timestamp
				) {

					// This is used to get the currency.
					$amount_format_args['donation_id'] = $subscription->parent_payment_id;

					$currency_format_args = array(
						'currency_code'   => give_get_payment_currency_code( $subscription->parent_payment_id ),
						'decode_currency' => true,
					);

					// Filling data row-wise.
					$data[] = array(
						'subscription_id' => $subscription->id,
						'donor_name'      => $subscription->donor->name,
						'donor_email'     => $subscription->donor->email,
						'renewal_date'    => $subscription->get_renewal_date(),
						'renewal_amount'  => give_currency_filter(
							give_format_amount( $subscription->recurring_amount, $amount_format_args ),
							$currency_format_args
						),
					);

				}
			}

			/**
			 * Filter the data
			 *
			 * @since 1.7
			 */
			$data = apply_filters( "give_export_get_data_{$this->export_type}", $data );

			return $data;
		}

		/**
		 * Return the calculated completion percentage.
		 *
		 * @since 1.8.3
		 *
		 * @return int
		 */
		public function get_percentage_complete() {

			$percentage = 0;

			// We can't count the number when getting them for a specific form.
			if ( is_array( $this->form ) ) {

				$total = $this->count;

				if ( $total > 0 ) {

					$percentage = ( ( $this->items_per_page * $this->step ) / $total ) * 100;

				}
			}

			if ( $percentage > 100 ) {
				$percentage = 100;
			}

			return $percentage;
		}
	}
} // End if().
