<?php
/**
 * Shipper modal template partials: recipient add modal
 *
 * @package shipper
 */

?>

<div class="sui-dialog sui-dialog-sm" id="shipper-add-recipient" aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="sui-box-header">
				<div class="shipper-close">
					<a href="#close">
						<i class="sui-icon-close" aria-hidden="true"></i>
						<span><?php esc_html_e( 'Close', 'shipper' ); ?></span>
					</a>
				</div>
				<h3 class="sui-dialog-title"><?php esc_html_e( 'Add Recipient', 'shipper' ); ?></h3>
			</div>
			<div class="sui-box-body">
<!--
				<p>
					<?php esc_html_e( 'Add as many recipients as you like.', 'shipper' ); ?>
					<?php esc_html_e( 'They will receive email reports as per the schedule you set.', 'shipper' ); ?>
				</p>
-->


				<div class="sui-form-field">
						<label for="" class="sui-label">
							<?php esc_html_e( 'First name', 'shipper' ); ?>
						</label>
						<input
							class="sui-form-control"
							placeholder="<?php esc_attr_e( 'E.g. John', 'shipper' ); ?>"
							type="text" />
				</div>

				<div class="sui-form-field">
						<label for="" class="sui-label">
							<?php esc_html_e( 'Email address', 'shipper' ); ?>
						</label>
						<input
							class="sui-form-control"
							placeholder="<?php esc_attr_e( 'E.g. john@doe.com', 'shipper' ); ?>"
							type="email" />
				</div>

				<div class="shipper-actions">
					<button
						class="sui-button sui-button-ghost shipper-cancel">
						<?php esc_html_e( 'Cancel', 'shipper' ); ?>
					</button>

					<button
						class="sui-button shipper-add"
						data-add="<?php echo esc_attr(
							wp_create_nonce( 'shipper_email_notifications_add' )
						); ?>"
						type="button">
						<span><?php esc_html_e( 'Add', 'shipper' ); ?></span>
					</button>
				</div>

			</div><?php // .sui-box-body ?>
		</div><?php // .sui-box ?>

	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>
