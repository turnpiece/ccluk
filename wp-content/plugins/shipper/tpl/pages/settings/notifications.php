<?php
/**
 * Shipper settings: Notifications subpage template
 *
 * @package shipper
 */

$model        = new Shipper_Model_Stored_Options();
$do_send      = $model->get( Shipper_Model_Stored_Options::KEY_SEND );
$failure_send = $model->get( Shipper_Model_Stored_Options::KEY_SEND_FAIL );
$emails       = $model->get_emails();
?>
<div class="sui-box shipper-page-settings-notifications">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Notifications', 'shipper' ); ?></h2>
	</div>

	<div class="sui-box-body">
		<p>
			<?php esc_html_e( 'Get notified when migrations complete, or if they fail.', 'shipper' ); ?>
		</p>


		<?php if ( ! empty( $do_send ) ) { ?>
			<div class="sui-notice sui-notice-success shipper-notifications-status-notice">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
						<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: number of users. */
								_n(
									'Migration notification emails are enabled for %d recipient',
									'Migration notification emails are enabled for %d recipients',
									count( $emails ),
									'shipper'
								),
								count( $emails )
							)
						);
						?>
						</p>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<div class="sui-notice">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
						<p>
							<?php esc_html_e( 'Migration notification emails are currently disabled', 'shipper' ); ?>
						</p>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="sui-box-settings-row shipper-no-separator">

			<div class="sui-box-settings-col-1">
				<label class="sui-settings-label"><?php esc_html_e( 'Email Notifications', 'shipper' ); ?></label>
				<p class="shipper-description">
					<?php esc_html_e( 'Choose who should receive an email when migration completes.', 'shipper' ); ?>
				</p>
			</div>

			<div class="sui-box-settings-col-2">
				<div class="shipper-form-item">
					<label class="sui-toggle">
					<input
						type="checkbox" <?php checked( $do_send ); ?>
						id="shipper-email-notifications"
						value="<?php echo esc_attr( wp_create_nonce( 'shipper_email_notifications_toggle' ) ); ?>">
						<span class="sui-toggle-slider"></span>
					</label>
					<label for="shipper-email-notifications">
						<?php esc_html_e( 'Send an email when migration completes', 'shipper' ); ?>
					</label>
				</div>

				<div class="<?php echo $do_send ? 'wrapper-active' : ''; ?> shipper-notifications-wrapper sui-border-frame sui-toggle-content">

					<div class="shipper-form-item shipper-notifications-list">
						<label class="sui-label"><?php esc_html_e( 'Recipients', 'shipper' ); ?></label>
					<?php if ( empty( $emails ) ) { ?>
						<div class="sui-notice sui-notice-warning">
							<div class="sui-notice-content">
								<div class="sui-notice-message">
									<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
									<p>
										<?php echo esc_html( __( 'You haven\'t added any recipients yet', 'shipper' ) ); ?>
									</p>
								</div>
							</div>
						</div>
					<?php } else { ?>
						<div class="sui-recipients">
						<?php foreach ( $emails as $email => $name ) { ?>
							<?php
							if ( empty( $email ) ) {
								continue;
							}
							?>
						<div class="shipper-notification-item sui-recipient">
							<span class="shipper-user sui-recipient-name">
								<?php echo esc_html( $name ); ?>
							</span>
							<span class="shipper-email sui-recipient-email" data-email="<?php echo esc_attr( $email ); ?>">
								<?php echo esc_html( $email ); ?>
							</span>

							<span class="shipper-actions">
								<a
									href="#remove" type="button"
									data-rmv="<?php echo esc_attr( wp_create_nonce( 'shipper_email_notifications_rmv' ) ); ?>"
									class="sui-button-icon shipper-rmv shipper-work-activator">
									<i class="sui-icon-trash" aria-hidden="true"></i>
								</a>
							</span>
						</div>
					<?php } ?>
							</div>
<?php } ?>
					</div>

					<button
						class="sui-button sui-button-ghost shipper-reveal-add"
						type="button">
						<i class="sui-icon-plus" aria-hidden="true"></i>
						<?php esc_html_e( 'Add Recipient', 'shipper' ); ?>
					</button>

					<div class="shipper-notification-options">
						<label class="sui-label">
							<?php esc_html_e( 'Options', 'shipper' ); ?>
						</label>
						<label for="email-migration-checkbox" class="sui-checkbox">
							<input
								id="email-migration-checkbox"
								value="<?php echo esc_attr( wp_create_nonce( 'shipper_email_fail_only' ) ); ?>"
								<?php checked( $failure_send ); ?>
								type="checkbox"
								aria-labelledby="email-migration-checkbox-label"
							/>
							<span aria-hidden="true"></span>
							<span id="email-migration-checkbox-label">
								<?php esc_html_e( 'Only send an email if a migration fails', 'shipper' ); ?>
							</span>
						</label>
					</div>

				</div><?php // .shipper-notifications-wrapper ?>

			</div> <?php // .sui-col ?>

		</div><?php // .sui-row ?>

		<?php $this->render( 'modals/email-add' ); ?>

	</div>

	<div class="sui-box-footer shipper-page-footer">
		<div class="sui-col">
			<div class="shipper-actions">
				<button class="sui-button sui-button-primary shipper-notifications-save">
					<?php esc_html_e( 'Save changes', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div class="sui-floating-notices"> <!-- Start: sui-notifications -->
		<div
			role="alert"
			id="shipper-recipient-add-notice"
			class="sui-notice"
			aria-live="assertive"
		>
		</div>

		<div class="sui-notice-content">
			<div class="sui-notice-message" />
		</div>
	</div> <!-- End: sui-notifications -->
</div>