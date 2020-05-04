<?php
/**
 * Recurring Subscription Details
 *
 * Outputs the subscriber details
 */
function give_recurring_subscription_details() {

	$render = true;
	if ( ! current_user_can( 'view_give_reports' ) ) {
		give_set_error( 'give-no-access', __( 'You are not permitted to view this data.', 'give-recurring' ) );
		$render = false;
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		give_set_error( 'give-invalid_subscription', __( 'Invalid subscription ID.', 'give-recurring' ) );
		$render = false;
	}

	$sub_id            = (int) $_GET['id'];
	$sub               = new Give_Subscription( $sub_id );
	$donation_currency = give_get_meta( $sub->parent_payment_id, '_give_payment_currency', true );

	if ( empty( $sub ) ) {
		give_set_error( 'give-invalid_subscription', __( 'Invalid subscription ID.', 'give-recurring' ) );
		$render = false;
	}
	?>
	<div class="wrap">
	<h1 id="give-subscription-details-h1" class="wp-heading-inline"><?php _e( 'Subscription Details', 'give-recurring' ); ?> -
		<?php echo "{$sub_id} {$sub->donor->name}"; ?></h1>
	<hr class="wp-header-end">
	<?php if ( give_get_errors() ) : ?>
		<div class="error settings-error">
			<?php
			/**
			 * Print Errors
			 *
			 * Prints all stored errors. Ensures errors show up on the appropriate form;
			 * For use during donation process. If errors exist, they are returned.
			 *
			 * @since 1.0
			 *
			 * @uses  give_get_errors()
			 * @uses  give_clear_errors()
			 *
			 * @param int $form_id Form ID.
			 *
			 * @return void
			 */
			do_action( 'give_frontend_notices', 0 );
			?>
		</div>
	<?php endif; ?>

	<?php if ( $sub && $render ) : ?>

	<div id="give-subscriber-wrapper">

		<?php do_action( 'give_subscription_card_top', $sub ); ?>

		<div class="info-wrapper item-section">

			<form id="edit-item-info" method="post"
			      action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $sub->id ); ?>">

				<div class="item-info">

					<table class="widefat">
						<tbody>

						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Donor:', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $sub->donor->id ) ) . '">' . $sub->donor->name . '</a>'; ?></td>
						</tr>

						<tr class="alternate">
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Donation Form Title:', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo get_the_title( $sub->form_id ); ?></td>
						</tr>

						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Initial Donation ID:', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo '<a href="' . add_query_arg( 'id', $sub->parent_payment_id, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details' ) ) . '">' . $sub->parent_payment_id . '</a>'; ?></td>
						</tr>

						<tr class="alternate">
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Billing Period:', 'give-recurring' ); ?></label>
							</td>
							<td><?php
								$interval = ! empty( $sub->frequency ) ? $sub->frequency : 1;
								echo give_recurring_pretty_subscription_frequency( $sub->period, false, false, $interval ); ?></td>
						</tr>

						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Times Billed:', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo get_times_billed_text( $sub ); ?></td>
						</tr>

						<tr class="alternate">
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Donation Form ID:', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo '<a href="' . add_query_arg( array(
										'post'   => $sub->form_id,
										'action' => 'edit',
									), admin_url( 'post.php' ) ) . '">' . $sub->form_id . '</a>'; ?></td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Gateway:', 'give-recurring' ); ?></label>
							</td>
							<td>
								<?php echo give_get_gateway_admin_label( $sub->gateway ); ?>
							</td>
						</tr>
						<tr class="alternate">
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Profile ID:', 'give-recurring' ); ?></label>
							</td>
							<td>
								<span class="give-sub-profile-id">
									<?php echo apply_filters( 'give_subscription_profile_link_' . $sub->gateway, $sub->profile_id, $sub ); ?>
								</span>
								<input type="text" name="profile_id" class="hidden give-sub-profile-id"
								       value="<?php echo esc_attr( $sub->profile_id ); ?>" />
								<span>&nbsp;&ndash;&nbsp;</span>
								<a href="#"
								   class="give-edit-sub-profile-id"><?php _e( 'Edit', 'give-recurring' ); ?></a>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Transaction ID:', 'give-recurring' ); ?></label>
							</td>
							<td>
								<?php
								$transaction_id = $sub->get_transaction_id();
								?>
								<span
									class="give-sub-transaction-id"><?php echo ! empty( $transaction_id ) ? $transaction_id : '<em>' . __( 'None found', 'give-recurring' ) . '</em>'; ?></span>
								<input type="text" name="transaction_id" class="hidden give-sub-transaction-id"
								       value="<?php echo esc_attr( $transaction_id ); ?>"/>
								<span>&nbsp;&ndash;&nbsp;</span>
								<a href="#"
								   class="give-edit-sub-transaction-id"><?php _e( 'Edit', 'give-recurring' ); ?></a>
							</td>
						</tr>
						<tr class="alternate">
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Date Created:', 'give-recurring' ); ?></label>
							</td>
							<td><?php echo date_i18n( give_date_format(), strtotime( $sub->created ) ); ?></td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Renewal Date:', 'give-recurring' ); ?></label>
							</td>
							<td>
								<span class="give-sub-expiration"><?php echo $sub->get_renewal_date(); ?></span>
								<input type="text" name="expiration" class="give_datepicker hidden give-sub-expiration"
								       value="<?php echo esc_attr( $sub->expiration ); ?>" />
								<span>&nbsp;&ndash;&nbsp;</span>
								<a href="#"
								   class="give-edit-sub-expiration"><?php _e( 'Edit', 'give-recurring' ); ?></a>
							</td>
						</tr>
						<tr class="alternate">
							<td class="row-title">
								<label
									for="subscription_status"><?php _e( 'Subscription Status:', 'give-recurring' ); ?></label>
							</td>
							<td>
								<select id="subscription_status" name="status">
									<?php
									foreach ( give_recurring_get_subscription_statuses() as $status_key => $status ) {
										?>
										<option
											value="<?php echo $status_key; ?>"<?php selected( $status_key, $sub->status ); ?> > <?php echo $status; ?>
										</option>
										<?php
									}
									?>
								</select>

								<span class="give-donation-status status-<?php echo sanitize_title( $sub->status ); ?>"><span
										class="give-donation-status-icon"></span></span>

							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div id="give-sub-notices">
					<div class="notice notice-info inline hidden" id="give-sub-expiration-update-notice">
						<p><?php _e( 'Changing the expiration date will not affect when renewal payments are processed.', 'give-recurring' ); ?></p>
					</div>
					<div class="notice notice-warning inline hidden" id="give-sub-profile-id-update-notice">
						<p><?php _e( 'Changing the profile ID can result in renewals not being processed. Do this with caution.', 'give-recurring' ); ?></p>
					</div>
				</div>
				<div id="item-edit-actions" class="edit-item" style="float:right; margin: 10px 0 0; display: block;">

					<?php wp_nonce_field( 'give-recurring-update', 'give-recurring-update-nonce', false, true ); ?>

					<input type="hidden" name="sub_id" value="<?php echo absint( $sub->id ); ?>" />

					<div class="update-wrap">
						<?php
						// Sync button.
						if ( $sub->can_sync() ) : ?>
							<input type="button"
							       name="give_sync_subscription" id="give_sync_subscription"
							       class="button button-primary"
							       value="<?php _e( 'Sync Subscription', 'give-recurring' ); ?>" />
							<script type='text/javascript'>
															/* <![CDATA[ */
															var Give_Sync_Vars = <?php echo json_encode( $sub ); ?>;
															/* ]]> */
							</script>
						<?php endif; ?>

						<input type="submit" name="give_update_subscription" id="give_update_subscription"
						       class="button button-primary"
						       value="<?php _e( 'Update Subscription', 'give-recurring' ); ?>" />

					</div>

					<div class="additional-actions">
						<?php if ( $sub->can_cancel() ) : ?>
							<a href="<?php echo $sub->get_cancel_url(); ?>"
							   class="button button-small give-subscription-admin-cancel"><?php _e( 'Cancel Subscription', 'give-recurring' ); ?></a>
						<?php endif; ?>
						&nbsp;<input type="submit" name="give_delete_subscription"
						             class="give-delete-subscription button  button-small"
						             value="<?php _e( 'Delete Subscription', 'give-recurring' ); ?>" />
					</div>
				</div>
			</form>

			<div id="sync-subscription-modal" class="modal-sync-subscription modal fade" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title"><?php echo sprintf( esc_html__( 'Syncing Subscription %1$s with %2$s', 'give-recurring' ), $sub_id,
									give_get_gateway_admin_label( $sub->gateway ) ); ?></h4>
						</div>
						<div class="modal-body"></div>
						<div class="modal-footer">
							<span class="give-active-sync-message"><?php esc_html_e( 'Syncing Subscription...', 'give-recurring' ); ?></span>
							<button class="button give-close-sync-modal" type="button"
							        data-dismiss="modal"><?php esc_html_e( 'Close', 'give-recurring' ); ?></button>
							<button class="button button-primary give-resync-button" disabled
							        type="button"><?php esc_html_e( 'Resync', 'give-recurring' ); ?></button>
						</div>
					</div>
				</div>
			</div>

			<?php do_action( 'give_subscription_before_stats', $sub ); ?>

			<div id="item-stats-wrapper" class="item-section" style="margin:25px 0; font-size: 20px;">
				<ul>
					<li>
						<span class="dashicons dashicons-chart-area"></span>
						<?php
						printf(
								'%1$s %2$s',
							give_currency_filter(
									give_format_amount(
											$sub->get_lifetime_value(),
											array( 'currency' => $donation_currency )
									),
									array( 'currency_code' => $donation_currency )
							),
							__( 'Subscription Value', 'give-recurring' )
						);
						?>
					</li>
					<?php do_action( 'give_subscription_stats_list', $sub ); ?>
				</ul>
			</div>

			<?php do_action( 'give_subscription_before_tables_wrapper', $sub ); ?>


			<div id="item-tables-wrapper" class="item-section">

				<?php do_action( 'give_subscription_before_tables', $sub ); ?>

				<h3><?php _e( 'Renewals', 'give-recurring' ); ?>
					<span class="give-add-renewal page-title-action"><?php _e( 'Add Renewal', 'give-recurring' ); ?></span>
				</h3>

				<div class="give-manual-add-renewal">
					<form id="give-sub-add-renewal" method="post">

						<?php wp_nonce_field( 'give-recurring-add-renewal-payment', '_wpnonce', false, true ); ?>
						<input type="hidden" name="sub_id" value="<?php echo absint( $sub->id ); ?>" />
						<input type="hidden" name="give_action" value="add_renewal_payment" />

						<p class="give-sub-add-renewal-desc"><?php _e( 'Manually record a renewal donation below. Please note that this will not charge the donor nor create the renewal at the gateway.', 'give-recurring' ); ?></p>
						<p>
							<label>
								<span class="give-recurring-manual-renewal-label"><?php _e( 'Amount', 'give-recurring' ); ?><span class="give-required-indicator">*</span></span>
								<input type="text" class="regular-text give-recurring-manual-renewal-input give-sub-renew-required-field"
								       name="amount" value="<?php echo give_format_amount( $sub->recurring_amount ); ?>" placeholder="0.00" />
							</label>
						</p>

						<p>
							<label for="give-payment-date" class="">
								<span
									class="give-recurring-manual-renewal-label"><?php esc_html_e( 'Date', 'give-recurring' ); ?>
									<span
										class="give-required-indicator">*</span></span>
								<input type="text" id="give-payment-date" name="give-payment-date"
								       value="<?php echo date_i18n( give_date_format(), strtotime( $sub->expiration ) ); ?>"
								       class="medium-text give_datepicker give-recurring-manual-renewal-input give-sub-renew-required-field"
								       readonly="true"/>
							</label>
						</p>

						<p>
							<label>
								<span class="give-recurring-manual-renewal-label"><?php _e( 'Transaction ID', 'give-recurring' ); ?></span>
								<input type="text" class="regular-text give-recurring-manual-renewal-input"
								       name="txn_id" value="" placeholder="" />
							</label>
						</p>

						<div class="give-recurring-manual-renewal-submit-wrap">
							<input type="submit" class="button"
							       value="<?php esc_attr_e( 'Add Renewal', 'give-recurring' ); ?>" />
							<p class="update-renewal-date-p">
								<label for="update_renewal_date">
									<input type="checkbox" value="" id="update_renewal_date" name="update_renewal_date" readonly="true">
									<?php _e( 'Update Renewal Date', 'give-recurring' ); ?>
								</label>
							</p>
						</div>

					</form>
				</div>

				<?php $payments = $sub->get_child_payments(); ?>
				<table class="wp-list-table widefat striped renewal-payments">
					<thead>
					<tr>
						<th><?php _e( 'ID', 'give-recurring' ); ?></th>
						<th><?php _e( 'Amount', 'give-recurring' ); ?></th>
						<th><?php _e( 'Date', 'give-recurring' ); ?></th>
						<th><?php _e( 'Status', 'give-recurring' ); ?></th>
						<th><?php _e( 'Actions', 'give-recurring' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php if ( ! empty( $payments ) ) : ?>
						<?php /* @var  $payment Give_Payment */?>
						<?php foreach ( $payments as $payment ) : ?>
							<tr>
								<td><?php echo Give()->seq_donation_number->get_serial_code( $payment ); ?></td>
								<td><?php echo give_donation_amount( $payment->ID, array( 'currency' => true, 'amount' => true ) ); ?></td>
								<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?></td>
								<td><?php echo give_get_payment_status( $payment, true ); ?></td>
								<td>
									<a title="<?php printf( __( 'View Donation %s Details', 'give-recurring' ), $payment->ID ); ?>"
									   href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $payment->ID ); ?>">
										<?php _e( 'View Details', 'give-recurring' ); ?>
									</a>
									<?php do_action( 'give_subscription_payments_actions', $sub, $payment ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="5">
								<p class="give-recurring-description"><?php _e( 'No renewal transactions found. When this subscription renews you will see the renewal transactions display in this section.', 'give-recurring' ); ?></p>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>

				<h4 class="inital-donation-heading"><?php _e( 'Initial Donation:', 'give-recurring' ) ?></h4>

				<table class="wp-list-table widefat striped payments initial-donation">

					<tbody>
					<?php
					$parent_payment = give_get_payment_by( 'id', $sub->parent_payment_id );
					if ( ! empty( $sub->parent_payment_id ) ) : ?>

						<tr>
							<td><?php echo Give()->seq_donation_number->get_serial_code( $sub->parent_payment_id ); ?></td>
							<td><?php echo give_donation_amount( $sub->parent_payment_id, array( 'currency' => true, 'amount' => true ) ); ?></td>
							<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $parent_payment->post_date ) ); ?></td>
							<td><?php echo give_get_payment_status( $parent_payment, true ); ?></td>
							<td>
								<a title="<?php printf( __( 'View Donation %s Details', 'give-recurring' ), $sub->parent_payment_id ); ?>"
								   href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $sub->parent_payment_id ); ?>">
									<?php _e( 'View Details', 'give-recurring' ); ?>
								</a>
								<?php do_action( 'give_subscription_parent_payments_actions', $sub, $parent_payment ); ?>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>

				<?php do_action( 'give_subscription_after_tables', $sub ); ?>

			</div>

			<?php do_action( 'give_subscription_card_bottom', $sub ); ?>
		</div>

		<?php endif; ?>

		<div id="give-subscription-notes" class="postbox">
			<h3 class="hndle"><?php esc_html_e( 'Subscription Notes', 'give-recurring' ); ?></h3>

			<div class="inside">
				<div id="give-subscription-notes-inner">
					<div id="give-subscription-notes-error-wrap">
					</div>
					<?php
					$notes = give_get_subscription_notes( $sub_id );
					if ( ! empty( $notes ) ) {
						$no_notes_display = ' style="display:none;"';
						foreach ( $notes as $note ) :

							echo give_get_subscription_note_html( $note, $sub_id );

						endforeach;
					} else {
						$no_notes_display = '';
					}

					echo '<p class="give-no-subscription-notes"' . wp_kses_post( $no_notes_display ) . '>' . esc_html__( 'No subscription notes.', 'give-recurring' ) . '</p>';
					?>
				</div>
				<textarea name="give-subscription-note" id="give-subscription-note" class="large-text"></textarea>

				<div class="give-clearfix">
					<button id="give-add-subscription-note" class="button button-secondary button-small" data-subscription-id="<?php echo absint( $sub_id ); ?>"><?php esc_html_e( 'Add Note', 'give-recurring' ); ?></button>
				</div>

			</div>
			<!-- /.inside -->
		</div>
		<!-- /#give-subscription-notes -->
	</div>
	<?php
}
