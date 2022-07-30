<?php
/**
 * Shipper modal template partials: recipient add modal
 *
 * @package shipper
 */

?>

<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="shipper-add-recipient"
		class="sui-modal-content sui-content-fade-in"
		aria-modal="true"
		aria-labelledby="shipper-add-recipient-title"
		aria-describedby="shipper-add-recipient-desc">
			<div class="sui-box">
				<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
					<button class="sui-button-icon sui-button-float--right shipper-cancel">
						<i class="sui-icon-close sui-md" aria-hidden="true"></i>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'shipper' ); ?></span>
					</button>

					<h3 id="shipper-add-recipient-title" class="sui-box-title sui-lg">
						<?php esc_html_e( 'Add Recipient', 'shipper' ); ?>
					</h3>
				</div>

				<div class="sui-box-body">
					<div class="sui-form-field">
						<label for="shipper-add-recipient-first-name" id="shipper-add-recipient-first-name-label" class="sui-label"><?php esc_html_e( 'First name', 'shipper' ); ?></label>
						<input
							type="text"
							placeholder="<?php esc_attr_e( 'E.g. John', 'shipper' ); ?>"
							id="shipper-add-recipient-first-name"
							class="sui-form-control"
							aria-labelledby="shipper-add-recipient-first-name-label"
						/>
					</div>

					<div class="sui-form-field">
						<label for="shipper-add-recipient-email" id="shipper-add-recipient-email-label" class="sui-label"><?php esc_html_e( 'Email address', 'shipper' ); ?></label>
						<input
							type="email"
							placeholder="<?php esc_attr_e( 'E.g. John@doe.com', 'shipper' ); ?>"
							id="shipper-add-recipient-email"
							class="sui-form-control"
							aria-labelledby="shipper-add-recipient-email-label"
						/>
					</div>
				</div>

				<div class="shipper-actions sui-box-footer sui-content-separated">
					<button class="sui-button sui-button-ghost shipper-cancel"><?php esc_html_e( 'Cancel', 'shipper' ); ?></button>
					<button
						class="sui-button shipper-add"
						data-add="<?php echo esc_attr( wp_create_nonce( 'shipper_email_notifications_add' ) ); ?>"
					>
						<?php esc_html_e( 'Add', 'shipper' ); ?>
					</button>
				</div>
			</div>
	</div>
</div>