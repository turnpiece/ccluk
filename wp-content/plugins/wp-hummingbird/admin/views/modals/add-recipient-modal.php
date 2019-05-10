<?php
/**
 * Add recipient modal.
 *
 * @package Hummingbird
 */

?>

<div class="dialog sui-dialog sui-dialog-sm" aria-hidden="true" id="wphb-add-recipient-modal">

	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" aria-labelledby="addRecipient" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="addRecipient">
					<?php esc_html_e( 'Add Recipient', 'wphb' ); ?>
				</h3>
				<button data-a11y-dialog-hide class="sui-dialog-close" aria-label="Close this dialog window"></button>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( 'Add as many recipients as you like, they will receive email notifications as per your settings.', 'wphb' ); ?></p>
				<div class="sui-form-field">
					<label for="reporting-first-name" class="sui-label"><?php esc_html_e( 'First name', 'wphb' ); ?></label>
					<input type="text" id="reporting-first-name" class="sui-form-control" placeholder="<?php esc_attr_e( 'E.g John', 'wphb' ); ?>">
				</div>
				<div class="sui-form-field">
					<label for="reporting-email" class="sui-label"><?php esc_html_e( 'Email address', 'wphb' ); ?></label>
					<input type="text" id="reporting-email" class="sui-form-control" placeholder="<?php esc_attr_e( 'E.g john@doe.com', 'wphb' ); ?>">
				</div>
			</div>
			<div class="sui-box-footer">
				<button class="close sui-button sui-button-ghost" data-a11y-dialog-hide="wphb-add-recipient-modal"><?php esc_html_e( 'Go back', 'wphb' ); ?></button>
				<a class="sui-button" id="add-recipient">
					<?php esc_html_e( 'Add', 'wphb' ); ?>
				</a>
			</div>

		</div>

	</div>

</div>
