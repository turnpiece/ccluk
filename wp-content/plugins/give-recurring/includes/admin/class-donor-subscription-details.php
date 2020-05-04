<?php
/**
 * Class Donor_Subscription_Details
 *
 * Displays a tabular view of subscription details under
 * Donations > Donors
 *
 * @since 1.6
 *
 */

if ( ! class_exists( 'Donor_Subscription_Details' ) ) {

	class Donor_Subscription_Details {

		/**
		 * The donor object.
		 *
		 * @var object
		 */
		private $donor = '';


		/**
		 * Boolean set to true if subscriptions exists for a donor.
		 *
		 * @var boolean
		 */
		private $any_subscriptions = false;


		/**
		 * List of column names for the table.
		 *
		 * @var array
		 */
		private $table_columns = array();


		/**
		 * Constructor.
		 *
		 * @since 1.6
		 */
		public function __construct() {
			$this->table_columns = array(
				__( 'Form', 'give-recurring' ),
				__( 'Amount', 'give-recurring' ),
				__( 'Status', 'give-recurring' ),
				__( 'Actions', 'give-recurring' ),
			);

			add_action( 'give_donor_card_bottom', array( $this, 'donor_subscription_details' ) );
		}


		/**
		 * Responsible for generating the entire view of
		 * subscriptions for a particular donor.
		 *
		 * @param object $donor The donor object.
		 * @since 1.6
		 */
		public function donor_subscription_details( $donor ) {

			$this->donor = $donor;

			ob_start();
			$this->set_title();
			$this->generate_table();
			$output = ob_get_contents();
			ob_end_clean();

			if ( $this->any_subscriptions ) {
				printf( $output );
			}
		}


		/**
		 * Set title for the Subscriptions view.
		 *
		 * @since 1.6
		 */
		private function set_title() {
			printf( '<h3>%s</h3>', __( 'Subscriptions', 'give-recurring' ) );
		}


		/**
		 * Generates the table for listing subscriptions.
		 *
		 * @since 1.6
		 */
		private function generate_table() {
		?>
			<table class="wp-list-table widefat striped donations">
				<thead>
					<tr>
						<?php $this->set_table_columns(); ?>
					</tr>
				</thead>
				<tbody>
					<?php $this->get_subscriptions_for_donor(); ?>
				</tbody>
			</table>
		<?php
		}


		/**
		 * Sets column names for the table.
		 *
		 * @since 1.6
		 */
		private function set_table_columns() {
			return array_map( function( $column_name ) {
				return printf( '<th scope="col">%s</th>', $column_name );
			}, $this->table_columns );
		}


		/**
		 * Gets all the payments of a particular donor and
		 * filters subscriptions.
		 *
		 * @since 1.6
		 */
		private function get_subscriptions_for_donor() {
			$args = array(
				'donor' => $this->donor->id,
			);

			// Get all the payments of a donor.
			$payments = give_get_payments( $args );

			foreach ( $payments as $payment ) {
				$subscription = give_recurring_get_subscription_by( 'payment', $payment->ID );

				if ( is_object( $subscription ) ) {
					$this->any_subscriptions = true;
					$this->generate_table_rows( $subscription );
				}
			}
		}


		/**
		 * Generates subscription data for the particular
		 * donor and adds it row-by-row.
		 *
		 * @param object $subscription Give_Subscription object.
		 * @since 1.6
		 */
		private function generate_table_rows( $subscription ) {
		?>
			<tr>
				<td><a href="<?php printf( get_the_permalink( $subscription->form_id ) ); ?>"><?php printf( get_the_title( $subscription->form_id ) ); ?></a></td>
				<td>
				<?php

				$interval  = ! empty( $subscription->frequency ) ? $subscription->frequency : 1;
				$frequency = give_recurring_pretty_subscription_frequency( $subscription->period, false, false, $interval );

					printf(
						'%1$s %2$s',
						give_currency_filter( give_format_amount( $subscription->recurring_amount ) ),
						$frequency );
				?>
				</td>
				<td><?php printf( ucfirst( $subscription->status ) ); ?></td>
				<td>
				<?php
					$subscription_url = add_query_arg(
						array(
							'post_type' => 'give_forms',
							'page'      => 'give-subscriptions',
							'id'        => $subscription->id,
						),
						admin_url() . 'edit.php?'
					);

					printf( '<a href="%1$s">%2$s</a>', esc_url( $subscription_url ), __( 'View details', 'give-recurring' ) );
				?>
				</td>
			</tr>
		<?php
		}
	}

	new Donor_Subscription_Details();
}
