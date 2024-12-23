<?php
/**
 * Give Template File for [give_subscriptions] shortcode.
 *
 * Place this template file within your theme directory under /my-theme/give/
 * For more information see: https://givewp.com/documentation/
 *
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0
 */
global $give_subscription_args;

/* @var Give_Recurring_Subscriber $subscriber */
$subscriber = Give_Recurring_Subscriber::getSubscriber();

// If cancelled Show message
if ( isset( $_GET['give-message'] ) && $_GET['give-message'] == 'cancelled' ) {
	echo '<div class="give_error give_success" id="give_error_test_mode"><p><strong>' . __( 'Notice', 'give-recurring' ) . '</strong>: ' . apply_filters( 'give_recurring_subscription_cancelled_message', __( 'Your subscription has been cancelled.', 'give-recurring' ) ) . '</p></div>';
}

/**
 * Filter the subscription status
 * These are the subscription statuses that will display
 *
 * Note: this filter is deprecated and can be remove in future, use give_recurring_get_subscription_statuses_key filter
 * with appropriate conditions.
 *
 * @deprecated 1.5.8
 */

// Get current page number.
$page = isset( $_GET['s_page'] ) ? give_clean( intval( $_GET['s_page'] ) ) : 1;


// Get total number of subscriptions.
$gsdb                = new Give_Subscriptions_DB();
$subscriptions_count = $gsdb->count(['donor_id' => $subscriber->id]);


// Get number of subscriptions to show per page. Default: 30
$subscriptions_per_page = $give_subscription_args['subscriptions_per_page'];

// Show limited subscription if donor only has donation session.
if( 'DonorSession' === Give_Recurring_Subscriber::getAccessType() ){
	$subscriptions_count = $subscriptions_per_page = give_get_limit_display_donations();
}

/**
 * Maximum number of pages that is possible given the
 * number of subscriptions and subscriptions per page.
 */
$max_page_number = ceil( $subscriptions_count / $subscriptions_per_page );


// Previous page URL link.
$previous_page_url = home_url( add_query_arg( array( 's_page' => $page - 1 ), $wp->request ) );


// Next page URL link.
$next_page_url = home_url( add_query_arg( array( 's_page' => $page + 1 ), $wp->request ) );


// Offset of subscriptions depending on the page number.
$offset = ( 1 === $page ) ? 0 : ( ( $page - 1 ) * $subscriptions_per_page );


// Array of display statuses.
$display_statuses = apply_filters( 'give_subscriptions_display_statuses', give_recurring_get_subscription_statuses_key() );


// Pagination type. Default: Next and Previous
$pagination_type = $give_subscription_args['pagination_type'];


// Fetch all subscriptions based on the above parameters.
$subscriptions = $subscriber->get_subscriptions(
	0, array(
		'status' => $display_statuses,
		'number' => $subscriptions_per_page,
		'offset' => $offset,
	)
);

if ( $subscriptions ) {
	do_action( 'give_before_purchase_history' ); ?>
	<table id="give_user_history" class="give-table">
		<thead>
		<tr class="give_purchase_row">
			<?php do_action( 'give_recurring_history_header_before' ); ?>
			<th><?php _e( 'Subscription', 'give-recurring' ); ?></th>
			<?php if ( $give_subscription_args['show_status'] == true ) { ?>
				<th><?php _e( 'Status', 'give-recurring' ); ?></th>
			<?php } ?>
			<?php if ( $give_subscription_args['show_renewal_date'] == true ) { ?>
				<th><?php _e( 'Renewal Date', 'give-recurring' ); ?></th>
			<?php } ?>
			<?php if ( $give_subscription_args['show_progress'] == true ) { ?>
				<th><?php _e( 'Progress', 'give-recurring' ); ?></th>
			<?php } ?>
			<?php if ( $give_subscription_args['show_start_date'] == true ) { ?>
				<th><?php _e( 'Start Date', 'give-recurring' ); ?></th>
			<?php } ?>
			<?php if ( $give_subscription_args['show_end_date'] == true ) { ?>
				<th><?php _e( 'End Date', 'give-recurring' ); ?></th>
			<?php } ?>
			<th><?php _e( 'Actions', 'give-recurring' ); ?></th>
			<?php do_action( 'give_recurring_history_header_after' ); ?>
		</tr>
		</thead>
		<?php
		foreach ( $subscriptions as $subscription ) :

			$interval     = ! empty( $subscription->frequency ) ? $subscription->frequency : 1;
			$frequency    = give_recurring_pretty_subscription_frequency( $subscription->period, false, false, $interval );
			$renewal_date = ! empty( $subscription->expiration ) ? date_i18n( get_option( 'date_format' ), strtotime( $subscription->expiration ) ) : __( 'N/A', 'give-recurring' );
			?>
			<tr>
				<?php do_action( 'give_recurring_history_row_start', $subscription ); ?>
				<td>
					<span class="give-subscription-name"><?php echo get_the_title( $subscription->form_id ); ?></span><br/>
					<span class="give-subscription-billing-cycle">
						<?php
						$args = array(
							'currency_code' => give_get_payment_currency_code( $subscription->parent_payment_id ),
						);
						echo give_currency_filter( give_format_amount( $subscription->recurring_amount ), $args ) . ' / ' . $frequency;
						?>
					</span>
				</td>
				<?php
				// Subscription Status.
				if ( $give_subscription_args['show_status'] == true ) {
				?>
					<td>
						<span class="give-subscription-status"><?php echo give_recurring_get_pretty_subscription_status( $subscription->status ); ?></span>
					</td>
				<?php } ?>
				<?php
				// Subscription Status.
				if ( $give_subscription_args['show_renewal_date'] == true ) {
				?>
					<td>
						<span class="give-subscription-renewal-date">
							<?php
							echo __( 'Auto renew on ', 'give-recurring' );
							echo $renewal_date;
							?>
						</span>
					</td>
				<?php } ?>
				<?php
				// Subscription Progress.
				if ( $give_subscription_args['show_progress'] == true ) {
				?>
					<td>
						<span class="give-subscription-times-billed"><?php echo get_times_billed_text( $subscription ); ?></span>
					</td>
				<?php } ?>
				<?php
				// Subscription Start Date.
				if ( $give_subscription_args['show_start_date'] == true ) {
				?>
					<td>
						<?php echo date_i18n( get_option( 'date_format' ), strtotime( $subscription->created ) ); ?>
					</td>
				<?php } ?>
				<?php
				// Subscription End Date.
				if ( $give_subscription_args['show_end_date'] == true ) {
				?>
					<td>
						<?php
						if ( 0 === intval( $subscription->bill_times ) ) {
							_e( 'Ongoing', 'give-recurring' );
						} else {
							echo date_i18n( get_option( 'date_format' ), $subscription->get_subscription_end_time() );
						};
						?>
					</td>
				<?php } ?>
				<td>
					<a href="<?php echo give_get_receipt_url( $subscription->parent_payment_id ); ?>">
						<?php esc_html_e( 'View Receipt', 'give-recurring' ); ?>
					</a>
					<?php
					// Updating the subscription CC.
					if ( $subscription->can_update() ) :
					?>
						&nbsp;|&nbsp;
						<a href="<?php echo esc_url( $subscription->get_update_url() ); ?>"><?php _e( 'Update Payment Info', 'give-recurring' ); ?></a>
					<?php endif; ?>
					<?php if ( give_get_option( 'subscriptions_page', 0 ) && $subscription->can_update_subscription() ): ?>
						&nbsp;|&nbsp;<a class="give-recurring-edit-amount" href="<?php echo esc_url( $subscription->get_edit_subscription_url() ); ?>"><?php _e( 'Edit Amount', 'give-recurring' ); ?></a>
					<?php endif; ?>
					<?php
					// Cancelling the subscription.
					if ( $subscription->can_cancel() ) :
					?>
						&nbsp;|&nbsp;
						<a href="<?php echo esc_url( $subscription->get_cancel_url() ); ?>"
						   class="give-cancel-subscription"><?php echo apply_filters( 'give_recurring_cancel_subscription_text', __( 'Cancel', 'give-recurring' ) ); ?></a>
					<?php endif; ?>

				</td>
				<?php do_action( 'give_recurring_history_row_end', $subscription ); ?>

			</tr>
		<?php endforeach; ?>
	</table>

	<?php

	/**
	 * This section sets up the pagination for
	 * subscription listing. By default, 30 are displayed
	 * at maximum unless the user has set any other value.
	 */
	switch ( $pagination_type ) {

		// Displays 'Previous' and 'Next' buttons.
		case 'next_and_previous':
			// The 'previous' link.
			if ( $page > 1 ) {
				printf(
					'<a href="%1$s" class="previous-subscriptions">%2$s</a>',
					esc_url( $previous_page_url ),
					esc_html( apply_filters( 'give_change_subscriptions_previous_label', '‹ Previous' ) )
				);
			}

			// The 'next' link.
			if ( $page < $max_page_number ) {
				printf(
					'<a href="%1$s" class="next-subscriptions alignright">%2$s</a>',
					esc_url( $next_page_url ),
					esc_html( apply_filters( 'give_change_subscriptions_next_label', 'Next ›' ) )
				);
			}

			break;

		// Displays numbered links to page.
		case 'numbered':
			// Arguments needed to set up numbered pagination.
			$pagination_args = array(
				'base'      => home_url( add_query_arg( array(), $wp->request ) ) . '%_%',
				'format'    => '?s_page=%#%',
				'total'     => $max_page_number,
				'current'   => $page,
				'end_size'  => 1,
				'mid_size'  => 2,
				'prev_next' => true,
				'prev_text' => esc_html( apply_filters( 'give_change_subscriptions_previous_label', '‹ Previous' ) ),
				'next_text' => esc_html( apply_filters( 'give_change_subscriptions_next_label', 'Next ›' ) ),
				'type'      => 'plain',
			);

			// Display the numbered pagination.
			echo paginate_links( $pagination_args );

			break;

		default:
			break;
	}

	do_action( 'give_after_recurring_history' );
	?>

<?php } else {
	Give_Notices::print_frontend_notice( __( 'You have not made any subscription donations.', 'give-recurring' ), true, 'warning' );
} ?>
