<?php
/**
 * Shipper modal template partials: reset settings cancel modal
 *
 * @package shipper
 */

?>

<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="shipper-settings-reset-dialog"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="shipper-settings-reset-dialog-title"
		aria-describedby="shipper-settings-reset-dialog-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close="">
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text">
						<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>
					</span>
				</button>

				<h3 id="shipper-settings-reset-dialog-title" class="sui-box-title sui-lg">
					<?php esc_html_e( 'Reset Settings', 'shipper' ); ?>
				</h3>
				<p id="shipper-settings-reset-dialog-description" class="shipper-description">
					<?php esc_html_e( 'Are you sure you want to reset Shipper’s settings back to the factory defaults?', 'shipper' ); ?>
				</p>
			</div>
			<div class="sui-box-footer sui-flatten sui-content-center">
				<button class="sui-button sui-button-ghost" data-modal-close="">
					<?php esc_html_e( 'Cancel', 'shipper' ); ?>
				</button>
				<button class="sui-button sui-button-red sui-button-ghost shipper-reset">
					<?php esc_html_e( 'Reset', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>